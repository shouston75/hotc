<?php

/**
 * <pre>
 * Invision Power Services
 * IP.Board v3.4.1
 * HTML parsing core
 * Last Updated: $Date: 2012-06-08 09:28:02 +0100 (Fri, 08 Jun 2012) $
 * </pre>
 *
 * @author 		$Author: mmecham $
 * @copyright	(c) 2001 - 2009 Invision Power Services, Inc.
 * @license		http://www.invisionpower.com/company/standards.php#license
 * @package		IP.Board
 * @link		http://www.invisionpower.com
 * @since		9th March 2005 11:03
 * @version		$Revision: 10894 $
 *
 * <code>
 * $html = $editor->process( $_POST['Post'] );
 * 
 * require( IPS_ROOT_PATH . 'sources/classes/text/parser.php' );
 * 
 * $parser = new classes_text_parser();
 * print $parser->HtmlToBBCode( '<strong>Moo!</strong>' );
 * 
 * Prints:
 * [b]Moo[/b]
 * </code>
 *
 */

if ( ! defined( 'IN_IPB' ) )
{
	print "<h1>Incorrect access</h1>You cannot access this file directly. If you have recently upgraded, make sure you upgraded all the relevant files.";
	exit();
}

/**
 * There are three modes
 * html: 		<strong>Moo</strong><br />[sharedmedia:1:string]
 * bbcode: 		[b]moo![/b]\n[sharedmedia:1:string]
 * display: 	<strong>Moo</strong><img src='....' />
 * @author matt
 *
 */
class classes_text_parser
{
	/**
	 * Settings
	 */
	protected static $Perms = array( 'skipBadWords' => false, 'parseBBCode' => true, 'parseHtml' => false, 'parseEmoticons' => true, 'parseArea' => 'posts' );
	private   $_errors;
	
	/**
	 * Used for acroynm replacement
	 */
	private $_currentAcronym = null;
	 
	/**
	 * Legacy method
	 * @todo remove in 4.0
	 */
	public $error = '';

	/**
	 * Force bbcode parser to kick in
	 * @var	bool
	 */
	protected $forceBbcode	= false;
	
	/**
	 * Constructor
	 *
	 * @access	public
	 * @return	@e void
	 */
	public function __construct()
	{
		/* Make object */
		$this->registry    =  ipsRegistry::instance();
		$this->DB	       =  $this->registry->DB();
		$this->settings    =& $this->registry->fetchSettings();
		$this->request     =& $this->registry->fetchRequest();
		$this->cache	   =  $this->registry->cache();
		$this->caches      =& $this->registry->cache()->fetchCaches();
		
		self::$Perms['memberData'] = ( is_array( self::$Perms['memberData'] ) ) ? self::$Perms['memberData'] : ipsRegistry::member()->fetchMemberData();
	}

	/**
	 * Force bbcode mode (used for emails where bbcode isn't used but autolink parsing needs to be done)
	 *
	 * @param	bool
	 * @return	null
	 */
	public function setForceBbcode( $force=false )
	{
		$this->forceBbcode	= $force;
	}
	
	/**
	 * Set multiple settings
	 * @param array $settings
	 */
	public function set( array $settings )
	{
		foreach( $settings as $setting => $value )
		{
			switch( $setting )
			{
				case 'parseBBCode':
					self::$Perms[ $setting ] = (bool) $value;
				break;
				case 'parseHtml':
					self::$Perms[ $setting ] = (bool) $value;
				break;
				case 'parseEmoticons':
					self::$Perms[ $setting ] = (bool) $value;
				break;
				case 'memberData':
					self::$Perms[ $setting ] = $value;
				break;
				case 'parseArea':
					self::$Perms[ $setting ] = $value;
				break;
			}
		}
	}
	
	/**
	 * Returns errors, yo.
	 * @return array
	 */
	public function getErrors()
	{
		return $this->_errors;
	}
	
	/**
	 * Display the HTML to IPB
	 * 
	 * Notes:
	 * CODE: Need to convert _prettyXprint, _linenums _lang- into correct class names
	 * @param	string  HTML
	 * @return	string	Fully parsed HTML
	 */
	public function display( $html )
	{
		require_once( IPS_ROOT_PATH . 'sources/classes/text/parser/bbcode.php' );
		$bbcodeParser = new class_text_parser_bbcode();
		
		require_once( IPS_ROOT_PATH . 'sources/classes/text/parser/html.php' );
		$htmlParser = new class_text_parser_html();
	
		if ( $this->isBBCode( $html ) )
		{
			$html = $bbcodeParser->BBCodeToHtml( $html );
		}
		
		/* Parse display tags */
		$html = $bbcodeParser->toDisplay( $html );
		
		/* Finish off HTML display */
		$html = $htmlParser->toDisplay( $html );
		
		/* Emoticons */
		if ( self::$Perms['parseEmoticons'] )
		{
			$html = $this->parseEmoticons( $html );
		}
		
		/* Badwords */
		if ( ! self::$Perms['skipBadWords'] )
		{
			$html = $this->parseBadWords( $html );
		}
		
		/* SEO stuffs */
		$html = $this->_seoAcronymExpansion( $html );
		
		/* Little secret codes */
		$html = str_ireplace( "(c)" , "&copy;", $html );
		$html = str_ireplace( "(tm)", "&#153;", $html );
		$html = str_ireplace( "(r)" , "&reg;" , $html );
		
		return $html;
	}
	
	/**
	 * Takes content from the DB and processes before it gets to the editor
	 *
	 * @param string $html
	 * @return	string
	 */
	public function htmlToEditor( $html )
	{
		/* Editing an older post? */
		if ( $this->isBBCode( $html ) )
		{
			return $this->BBCodeToHtml( $html );
		}
		
		return $html;
	}
	
	/**
	 * Takes content from the editor and makes it lovely and clean for saving
	 * 
	 * @param string $html
	 * @return	string
	 */
	public function editorToHtml( $editor )
	{
		$editor = $this->emoticonImgtoCode( $editor );
		$editor = $this->_stripEmptyLeadingAndTrailingParagraphTags( $editor );
		
		/* CODE: Fetch paired opening and closing tags */
		$data = $this->getTagPositions( $editor, 'blockquote', array( '<' , '>' ) );
		
		if ( is_array( $data['open'] ) )
		{
			foreach( $data['open'] as $id => $val )
			{
				$o = $data['open'][ $id ];
				$c = $data['close'][ $id ] - $o;
		
				$slice = substr( $editor, $o, $c );
		
				/* Need to bump up lengths of opening and closing */
				$_origLength = strlen( $slice );
				
				$slice = $this->_stripParagraphWrap( $slice );
				
				$_newLength  = strlen( $slice );
		
				$editor = substr_replace( $editor, $slice, $o, $c );
		
				/* Bump! */
				if ( $_newLength != $_origLength )
				{
					foreach( $data['open'] as $_id => $_val )
					{
						$_o = $data['open'][ $_id ];
							
						if ( $_o > $o )
						{
							$data['open'][ $_id ]  += ( $_newLength - $_origLength );
							$data['close'][ $_id ] += ( $_newLength - $_origLength );
						}
					}
				}
			}
		}
			
		return $editor;
	}
	
	/**
	 * Convert HTML to BBCode
	 * @param	string	HTML
	 * @param	string	BBCode
	 */
	public function HtmlToBBCode( $text )
	{
		require_once( IPS_ROOT_PATH . 'sources/classes/text/parser/html.php' );
		$html = new class_text_parser_html();
		
		$text = $html->toBBCode( $text );
		
		return $text;
	}
	
	/**
	 * Convert BBCode to HTML
	 * @param   string $text
	 * @return 	string	$text
	 */
	public function BBCodeToHtml( $text )
	{
		require_once( IPS_ROOT_PATH . 'sources/classes/text/parser/bbcode.php' );
		$bbcode = new class_text_parser_bbcode();
		
		if ( $this->isBBCode( $text ) )
		{ 
			$text = $bbcode->toHtml( $text );
		}
			
		return $text;
	}
	
	/**
	 * Does it need conversion?
	 * @param	string
	 * @return 	boolean
	 * @since	3.4
	 */
	public function isBBCode( $string )
	{
		if ( $this->forceBbcode )
		{
			return true;
		}

		if ( strstr( $string, '[' ) )
		{
			if ( preg_match( '#\[((?:b|i|u|s|font|size|color|background|sup|sub|list|\*|url|img|center|left|right|indent|email|code)(\]|=))|(quote(?:\s|\]))#i', $string ) )
			{
				return true;
			}
		}
			
		return false;
	}
	
	/**
	 * Takes HTML (*not* display) and checks it for built in limits such as quote and IMG
	 * 
	 * @param string $text
	 */
	public function testForParsingLimits( $text, $check=array('img', 'quote', 'emoticons', 'urls') )
	{
		$quoteCount = $this->getQuoteCount($text);
		$imageCount = $this->getImageCount($text);
		$emoCount   = $this->getEmoticonCount($text);
		
			/* IMG CHECK */
		if ( ( is_numeric( $this->settings['max_images'] ) ) && ( $check == 'all' || in_array( 'all', $check ) || in_array( 'img', $check ) ) )
		{
			if ( $imageCount > $this->settings['max_images'] )
			{
				$this->_addParsingError( 'too_many_img' );
			}
		}
		
		/* EMO CHECK */
		if ( ( is_numeric( $this->settings['max_emos'] ) ) && ( $check == 'all' || in_array( 'all', $check ) || in_array( 'emoticons', $check ) ) )
		{
			if ( $emoCount > $this->settings['max_emos'] )
			{
				$this->_addParsingError( 'too_many_emoticons' );
			}
		}
		
		/* IMG EXT CHECK */
		preg_match_all( '#<img([^>]+?)?>#i', $text, $matches );
		foreach( $matches[1] as $id => $match )
		{
			if ( stristr( $match, 'src=' ) && ! stristr( $match, 'class="bbc_emoticon"' ) )
			{
				preg_match( '#src=[\'"]([^\'"]+?)[\'"]#i', $match, $url );
				
				if ( $this->isAllowedImgUrl( $url[1] ) !== true )
				{
					$this->_addParsingError( 'invalid_ext' );
					break;
				}
				
				if ( $this->isAllowedUrl( $url[1] ) !== true )
				{
					$this->_addParsingError( 'domain_not_allowed' );
					break;
				}
			}
		}
		
		/* A HREF CHECK */
		if ( $check == 'all' || in_array( 'all', $check ) || in_array( 'urls', $check ) )
		{
			preg_match_all( '#<a([^>]+?)?>#i', $text, $matches );
			foreach( $matches[1] as $id => $match )
			{
				if ( stristr( $match, 'href=' ) )
				{
					preg_match( '#href=[\'"]([^\'"]+?)[\'"]#i', $match, $url );
			
					if ( $this->isAllowedUrl( $url[1] ) !== true )
					{
						$this->_addParsingError( 'domain_not_allowed' );
						break;
					}
				}
			}
		}
		
		return ( count( $this->_errors ) ) ? false : true;
	}
	
	/**
	 * Get number of quotes
	 * @param	string
	 */
	public function getQuoteCount( $text )
	{
		return substr_count( $text, '<blockquote' );
	}
	
	
	/**
	 * Get the number of images
	 * @param string $text
	 */
	public function getImageCount( $text )
	{
		$count = 0;
		preg_match_all( '#<img([^>]+?)?>#i', $text, $matches );
		
		foreach( $matches[1] as $id => $match )
		{
			if ( ! stristr( $match, 'class="bbc_emoticon"' ) )
			{
				$count++;
			}
		}
		
		return $count;
	}
	
	/**
	 * Get the number of URLs
	 * @param string $text
	 */
	public function getUrlCount( $text )
	{
		$count = 0;
		preg_match_all( '#<a([^>]+?)?>#i', $text, $matches );
	
		foreach( $matches[1] as $id => $match )
		{
			if ( stristr( $match, 'href' ) )
			{
				$count++;
			}
		}
	
		return $count;
	}
	
	/**
	 * Get the number of images
	 * @param string $text
	 * @param	boolean	$parseTest
	 */
	public function getEmoticonCount( $text, $parseTest=false )
	{
		$count = 0;
		preg_match_all( '#<img([^>]+?)?>#i', $text, $matches );
	
		foreach( $matches[1] as $id => $match )
		{
			if ( stristr( $match, 'class="bbc_emoticon"' ) )
			{
				$count++;
			}
		}
		
		/* No count? Try parsing and then recounting */
		if ( $parseTest !== false )
		{
			$testText = $this->parseEmoticons( $text );
			$count    = $this->getEmoticonCount($testText, true );
		}
		
		return $count;
	}
	
	/**
	 * Is an allowed URL type
	 * @param string $url
	 * @return boolean
	 */
	public function isAllowedImgUrl( $url )
	{
		if ( $this->settings['img_ext'] )
		{
			$path	= @parse_url( html_entity_decode( $url ), PHP_URL_PATH );
			$pieces	= explode( '.', $path );
			$ext	= array_pop( $pieces );
			$ext	= strtolower( $ext );
	
			if ( ! in_array( $ext, explode( ',', str_replace( '.', '', strtolower($this->settings['img_ext']) ) ) ) )
			{
				return false;
			}
		}
	
		return true;
	}
	
	/**
	 * Is allowed URL
	 * @param string $url
	 * @return boolean
	 */
	public function isAllowedUrl( $url )
	{
		if ( $this->settings['ipb_use_url_filter'] )
		{
			$list_type = $this->settings['ipb_url_filter_option'] == "black" ? "blacklist" : "whitelist";
				
			if( $this->settings['ipb_url_' . $list_type ] )
			{
				$list_values 	= array();
				$list_values 	= explode( "\n", str_replace( "\r", "", $this->settings['ipb_url_' . $list_type ] ) );
		
				if( $list_type == 'whitelist' )
				{
					$list_values[]	= "http://{$_SERVER['HTTP_HOST']}/*";
				}
		
				if ( count( $list_values ) )
				{
					$good_url = 0;
						
					foreach( $list_values as $my_url )
					{
						if( !trim($my_url) )
						{
							continue;
						}
		
						$my_url = preg_quote( $my_url, '/' );
						$my_url = str_replace( '\*', "(.*?)", $my_url );
		
						if ( $list_type == "blacklist" )
						{
							if( preg_match( '/' . $my_url . '/i', $url ) )
							{
								return false;
							}
						}
						else
						{
							if ( preg_match( '/' . $my_url . '/i', $url ) )
							{
								$good_url = 1;
							}
						}
					}
						
					if ( ! $good_url AND $list_type == "whitelist" )
					{
						return false;
					}
				}
			}
		}
		
		return true;
	}
	
	/**
	 * Replace bad words
	 *
	 * @param	string	Raw text
	 * @return	string	Converted text
	 */
	public function parseBadWords( $text='' )
	{
		/* Empty text or bypass? */
		if ( $text == '' || self::$Perms['memberData']['g_bypass_badwords'] )
		{
			return $text;
		}
	
		$badwords  = $this->cache->getCache('badwords');
		$temp_text = $text;
		$urls      = array();
	
		/* Got any naughty words? */
		if ( ! is_array( $badwords ) OR ! count( $badwords ) )
		{
			return $text;
		}
	
		/* strip out URLs so replacements aren't made */
		preg_match_all( '#((http|https|news|ftp)://(?:[^<>\)\[\"\s]+|[a-zA-Z0-9/\._\-!&\#;,%\+\?:=]+))#is', $text, $matches );
	
		foreach( $matches[0] as $m )
		{
			$c = count( $urls );
			$urls[ $c ] = $m;
				
			$text = str_replace( $m, '<!--url{' . $c . '}-->', $text );
		}

		//-----------------------------------------
		// Convert back entities
		//-----------------------------------------
			
		for( $i = 65; $i <= 90; $i++ )
		{
			$text = str_replace( "&#" . $i . ";", chr($i), $text );
		}
	
		for( $i = 97; $i <= 122; $i++ )
		{
			$text = str_replace( "&#" . $i . ";", chr($i), $text );
		}
	
		//-----------------------------------------
		// Go all loopy
		//-----------------------------------------
	
		foreach( $badwords as $r )
		{
			if ( $this->parseType != 'topics' )
			{
				$r['swop'] = strip_tags( $r['swop'] );
			}
	
			$replace	= $r['swop'] ? $r['swop'] : '######';
				
			if ( $r['m_exact'] )
			{
				$r['type']	= preg_quote( $r['type'], "/" );
	
				/* Link */
// 				if ( IPS_DOC_CHAR_SET == 'UTF-8' && IPSText::isUTF8( $text ) )
// 				{
// 					$text = preg_replace( '/(^|\p{L}|\s)' . $r['type'] . '(\p{L}|!|\?|\.|,|$)/i', "\\1{$replace}\\2", $text );
// 				}
// 				else
// 				{
					$text = preg_replace( '/(^|\b|\s)' . $r['type'] . '(\b|!|\?|\.|,|$)/i', "\\1{$replace}\\2", $text );
//				}
			}
			else
			{
				//----------------------------
				// 'ass' in 'class' kills css
				//----------------------------
	
				if( $r['type'] == 'ass' )
				{
					$text		= preg_replace( "/(?<!cl)" . $r['type'] . "/i", $replace, $text );
				}
				else
				{
					$text		= str_ireplace( $r['type'], $replace, $text );
				}
			}
		}
	
		/* replace urls */
		if ( count( $urls ) )
		{
			preg_match_all( '#\<\!--url\{(\d+?)\}--\>#is', $text, $matches );
				
			for ( $i = 0; $i < count($matches[0]); $i++ )
			{
				if ( isset( $matches[1][$i] ) )
				{
					$text = str_replace( $matches[0][$i], $urls[ $matches[1][$i] ], $text );
				}
			}
		}
	
		return $text ? $text : $temp_text;
	}
	
	/**
	 * Parse emoticons in text
	 *
	 * @param string $txt        	
	 * @return string $txt
	 */
	public function parseEmoticons( $txt )
	{
		/* Sort them in length order first */
		$this->_sortSmilies();
		
		$_codeBlocks = array();
		$_c 		 = 0;
		
		/* Now parse them! */
		if ( self::$Perms['parseEmoticons'] && ! $this->parse_html )
		{
			/* Make CODE tags safe... */
			while( preg_match( '/(<pre(.+?(?=<\/pre>))<\/pre>)/s', $txt, $matches ) )
			{
				$find    = $matches[0];
				$replace = '<!--C|' . $_c . '|-->';
				
				$_codeBlocks[ $_c ] = $find;
				
				$txt = str_replace( $find, $replace, $txt );
				
				$_c++;
			}
		
			$codes_seen = array();
			
			if ( count( $this->_sortedSmilies ) > 0 )
			{
				foreach( $this->_sortedSmilies as $row )
				{
					if ( is_array( $this->registry->output->skin ) and $this->registry->output->skin['set_emo_dir'] and $row['emo_set'] != $this->registry->output->skin['set_emo_dir'] )
					{
						continue;
					}
					
					$code = $row['typed'];
					
					if ( in_array( $code, $codes_seen ) )
					{
						continue;
					}
					
					$codes_seen[] = $code;
					
					// -----------------------------------------
					// Now, check for the html safe versions
					// -----------------------------------------
					
					$_emoCode = str_replace( '<', '&lt;', str_replace( '>', '&gt;', $code ) );
					$_emoImage = $row['image'];
					$emoPosition = 0;
					
					// -----------------------------------------
					// These are chars that can't surround the emo
					// -----------------------------------------
					
					$invalidWrappers = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz'\"/";
					
					// -----------------------------------------
					// Have any more chars to look at?
					// -----------------------------------------
					
					while ( ( $position = stripos( $txt, $_emoCode, $emoPosition ) ) !== false )
					{
						$lastOpenTagPosition = strrpos( substr( $txt, 0, $position ), '[' );
						$lastCloseTagPosition = strrpos( substr( $txt, 0, $position ), ']' );
						
						// -----------------------------------------
						// Are we at the start of the string, or
						// is the preceeding char not an invalid wrapper?
						// -----------------------------------------
						
						if ( ( $position === 0 or stripos( $invalidWrappers, substr( $txt, $position - 1, 1 ) ) === false )
	
						//-----------------------------------------
						// Are we inside a [tag]
						//-----------------------------------------
		
						AND ( $lastOpenTagPosition === FALSE or ( $lastCloseTagPosition !== FALSE and $lastCloseTagPosition > $lastOpenTagPosition ) )
		
						//-----------------------------------------
						// Are we at the end of the string or is the
						// next char not an invalid wrapper?
						//-----------------------------------------
		
						AND ( strlen( $txt ) == ( $position + strlen( $_emoCode ) ) or stripos( $invalidWrappers, substr( $txt, ( $position + strlen( $_emoCode ) ), 1 ) ) === false ) )
						{
							// -----------------------------------------
							// Replace the emoticon and increment position
							// counter
							// -----------------------------------------
							
							$replace = $this->_retrieveSmiley( $_emoCode, $_emoImage );
							$txt = substr_replace( $txt, $replace, $position, strlen( $_emoCode ) );
							
							$position += strlen( $replace );
						}
						
						$emoPosition = $position + 1;
						
						if ( $emoPosition > strlen( $txt ) )
						{
							break;
						}
					}
				}
			}
			
			/* Put alt tags in */
			if ( is_array( $this->emoticon_alts ) && count( $this->emoticon_alts ) )
			{
				foreach( $this->emoticon_alts as $r )
				{
					$txt = str_replace( $r[0], $r[1], $txt );
				}
			}
			
			/* Convert code tags back... */
			while( preg_match( '/<!--C\|(\d+?)\|-->/', $txt, $matches ) )
			{
				$find    = $matches[0];
				$replace = $_codeBlocks[ $matches[1] ];
								
				$txt = str_replace( $find, $replace, $txt );
			}
		}
	
		return $txt;
	}
	
	/**
	 * Remove quotes
	 * @param string $txt
	 */
	public function stripQuotes( $txt )
	{
		if ( stristr( $txt, '[quote' ) )
		{
			$txt = $this->stripBbcode( 'quote', $txt );
		}
		
		if ( stristr( $txt, '<blockquote' ) )
		{
			/* PRE: Fetch paired opening and closing tags */
			$data = $this->getTagPositions( $txt, 'blockquote', array( '<' , '>' ) );
			
			if ( is_array( $data['openWithTag'] ) )
			{
				foreach( $data['openWithTag'] as $id => $val )
				{
					$o = $data['openWithTag'][ $id ];
					$c = $data['closeWithTag'][ $id ] - $o;
						
					$slice = substr( $txt, $o, $c );
						
					/* Need to bump up lengths of opening and closing */
					$_origLength = strlen( $slice );
						
					/* Remove */
					$slice = '';
			
					$_newLength  = strlen( $slice );
						
					$txt = substr_replace( $txt, $slice, $o, $c );
						
					/* Bump! */
					if ( $_newLength != $_origLength )
					{
						foreach( $data['openWithTag'] as $_id => $_val )
						{
							$_o = $data['openWithTag'][ $_id ];
								
							if ( $_o > $o )
							{
								$data['openWithTag'][ $_id ]  += ( $_newLength - $_origLength );
								$data['closeWithTag'][ $_id ] += ( $_newLength - $_origLength );
							}
						}
					}
				}
			}
		}
		
		return $txt;
	}
	
	/**
	 * Removes bbcode tag + contents within the tag
	 *
	 * @access public
	 * @param
	 *        	string		Tag to strip
	 * @param
	 *        	string		Raw text
	 * @return string text
	 */
	public function stripBbcode( $tag, $txt )
	{
		// -----------------------------------------
		// Protect against endless loops
		// -----------------------------------------
		static $iteration = array();
		
		if ( isset( $iteration[$tag] ) and $iteration[$tag] > $this->settings['max_bbcodes_per_post'] )
		{
			return $txt;
		}
		
		$iteration[$tag] = isset( $iteration[$tag] ) ? $iteration[$tag] ++ : 1;
		
		// Got Quotes (tm)? or any tag really
		if ( stripos( $txt, '[' . $tag ) !== false )
		{
			// -----------------------------------------
			// First grab start and end positions
			// -----------------------------------------
			
			$start_position = stripos( $txt, '[' . $tag );
			$end_position = stripos( $txt, '[/' . $tag . ']', $start_position );
			
			// -----------------------------------------
			// If no end position or start position,
			// we have a mismatched bbcode...return
			// -----------------------------------------
			
			if ( $start_position === false or $end_position === false )
			{
				return $txt;
			}
			
			// -----------------------------------------
			// Then extract the content inside the bbcode
			// -----------------------------------------
			
			$inner_content = substr( $txt, stripos( $txt, ']', $start_position ) + 1, $end_position - ( stripos( $txt, ']', $start_position ) + 1 ) );
			
			// -----------------------------------------
			// Is this bbcode nested in the inner content
			// -----------------------------------------
			
			$extra_closers = substr_count( $inner_content, '[' . $tag );
			
			// -----------------------------------------
			// If so we need to move to the last ending tag
			// -----------------------------------------
			
			if ( $extra_closers > 0 )
			{
				for( $done = 0 ; $done < $extra_closers ; $done ++ )
				{
					$end_position = stripos( $txt, '[/' . $tag . ']', $end_position + 1 );
				}
			}
			
			// -----------------------------------------
			// Get rid of the bbcode opening + content + closing
			// -----------------------------------------
			
			$txt = substr_replace( $txt, '', $start_position, $end_position - $start_position + strlen( '[/' . $tag . ']' ) );
			
			// -----------------------------------------
			// And parse recursively
			// -----------------------------------------
			
			return $this->stripBbcode( $tag, trim( $txt ) );
		}
		else
		{
			return $txt;
		}
	}
	
	/**
	 * Remove ALL tags
	 *
	 * @access public
	 * @param
	 *        	string		Raw text
	 * @param
	 *        	boolean		Whether or not to run through pre-edit-parse first
	 * @return string text
	 */
	public function stripAllTags( $txt )
	{
		$txt = $this->stripBbcode( 'quote', $txt );
		
		foreach( $this->cache->getCache( 'bbcode' ) as $bbcode )
		{
			$txt = preg_replace( "#\[{$bbcode['bbcode_tag']}\](.+?)\[/{$bbcode['bbcode_tag']}\]#is", "\\1 ", $txt );
			$txt = preg_replace( "#\[{$bbcode['bbcode_tag']}=([^\]]+?)\](.+?)\[/{$bbcode['bbcode_tag']}\]#is", "\\2 ", $txt );
			$txt = str_ireplace( "[{$bbcode['bbcode_tag']}]", '', $txt );
			$txt = str_ireplace( "[/{$bbcode['bbcode_tag']}]", '', $txt );
			
			// -----------------------------------------
			// Strip single bbcodes properly
			// -----------------------------------------
			
			if ( $bbcode['bbcode_single_tag'] )
			{
				$regex = $bbcode['bbcode_single_tag'];
				
				// -----------------------------------------
				// If this has option, adjust regex
				// -----------------------------------------
				
				if ( $bbcode['bbcode_useoption'] )
				{
					$regex .= '=([^\]]+?)';
				}
				
				$txt = preg_replace( "#\[{$regex}\]#is", " ", $txt );
			}
		}
		
		// $txt = preg_replace( "#\[(.+?)\]#is", " ", $txt );
		$txt = preg_replace( '#\[([^\]]+?)=([^\]]+?)\]#is', " ", $txt );
		$txt = preg_replace( '#\[/([^\]]+?)\]#is', " ", $txt );
		$txt = preg_replace( '#\[attachment=(.+?)\]#is', " ", $txt );
		$txt = str_replace( '[*]', '', $txt );
		
		return $txt;
	}
	
	/**
	 * Remove raw smilies
	 *
	 * @access public
	 * @param
	 *        	string		Raw text
	 * @return string with smiley codes removed
	 */
	public function stripEmoticons( $txt )
	{
		$codes_seen = array();
		
		if ( count( $this->cache->getCache( 'emoticons' ) ) > 0 )
		{
			foreach( $this->cache->getCache( 'emoticons' ) as $row )
			{
				if ( is_array( $this->registry->output->skin ) and $this->registry->output->skin['set_emo_dir'] and $row['emo_set'] != $this->registry->output->skin['set_emo_dir'] )
				{
					continue;
				}
				
				$code = $row['typed'];
				
				if ( in_array( $code, $codes_seen ) )
				{
					continue;
				}
				
				$codes_seen[] = $code;
				
				// -----------------------------------------
				// Now, check for the html safe versions
				// -----------------------------------------
				
				$_emoCode = str_replace( '<', '&lt;', str_replace( '>', '&gt;', $code ) );
				$_emoImage = $row['image'];
				$emoPosition = 0;
				$invalidWrappers = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz";
				
				while ( ( $position = strpos( $txt, $_emoCode, $emoPosition ) ) !== false )
				{
					if ( strpos( $invalidWrappers, substr( $txt, $position - 1, 1 ) ) === false and strpos( $invalidWrappers, substr( $txt, ( $position + strlen( $_emoCode ) ), 1 ) ) === false )
					{
						$txt = substr_replace( $txt, '', $position, strlen( $_emoCode ) );
						
						$position += strlen( $_emoCode );
					}
					
					$emoPosition = $position + 1;
					
					if ( $emoPosition > strlen( $txt ) )
					{
						break;
					}
				}
			}
		}
		
		return $txt;
	}
	
	/**
	 * Strip shared media
	 *
	 * @param 	string			Raw posted text
	 * @return	string			Raw text with no shared media
	 */
	public function stripSharedMedia( $txt )
	{
		$txt	= preg_replace( '#\[sharedmedia=([^\]]+?)\]#is', " ", $txt );
	
		return $txt;
	}
	
	/**
	 * Strip images
	 * 
	 * @param	string
	 * @return	string
	 */
	public function stripImages( $txt )
	{
		$txt = preg_replace( '#<img([^>]+?)>#i', '', $txt );
		
		return $txt;
	}
	
	/**
	 * Convert IMG codes into text smilies
	 * 
	 * @param text $txt
	 * @return text $txt
	 */
	public function emoticonImgtoCode( $txt )
	{
		if ( count( $this->cache->getCache( 'emoticons' ) ) > 0 )
		{
			$emoDir = IPSText::getEmoticonDirectory();

			$txt    = str_replace( '<#EMO_DIR#>', $this->registry->output->skin['set_emo_dir'], $txt );
			
			foreach( $this->cache->getCache( 'emoticons' ) as $row )
			{
				if ( $row['emo_set'] != $emoDir )
				{
					continue;
				}
				
				/* BBCode */
				$txt = preg_replace( '#(\s)?\[img\]' . preg_quote( $this->settings['public_cdn_url'] . 'style_emoticons/' . $this->registry->output->skin['set_emo_dir'] . '/' . $row['image'], '#' ) . '\[/img\]#', ' ' . $row['typed'], $txt );
				
				/* HTML */
				$txt = preg_replace( '#(\s)?<img([^>]+?)src=(?:[\'"])' . preg_quote( $this->settings['public_cdn_url'] . 'style_emoticons/' . $this->registry->output->skin['set_emo_dir'] . '/' . $row['image'], '#' ) . '(?:[\'"])(?:[^>]+?)?>#', ' ' . $row['typed'], $txt );
			}
		}
		
		return $txt;
	}
	
	/**
	 * Returns an array of tags this user is not allowed
	 * to use.
	 * @return array
	 */
	public function getDisabledTags()
	{
		$disabled    = array();
		$bbcodeCache = $this->cache->getCache('bbcode');
		
		foreach( $bbcodeCache as $bbcode )
		{
			/* Allowed this BBCode? */
			if ( $bbcode['bbcode_sections'] != 'all' || $bbcode['bbcode_groups'] != 'all' )
			{			
				$sections	= explode( ',', $bbcode['bbcode_sections'] );
				$groups     = array_diff( explode( ',', $bbcode['bbcode_groups'] ), array( '' ) );
				$mygroups   = array( self::$Perms['memberData']['member_group_id'] );
				$pass       = false;
				
				if ( self::$Perms['memberData']['mgroup_others'] )
				{
					$mygroups = array_diff( array_merge( $mygroups, explode( ',', IPSText::cleanPermString( self::$Perms['memberData']['mgroup_others'] ) ) ), array( '' ) );
				}
				
				/* Perms */
				if ( $bbcode['bbcode_groups'] != 'all' )
				{
					foreach( $groups as $g_id )
					{
						if ( in_array( $g_id, $mygroups ) )
						{
							$pass = true;
						}
					}
				}
				else
				{
					$pass = true;
				}
				
				/* Sections */
				if ( self::$Perms['parseArea'] != 'global' )
				{
					foreach( $sections as $section )
					{
						if ( $section == self::$Perms['parseArea'] )
						{
							$pass = true;						
						}
					}
				}
				else
				{
					$pass = true;
				}
				
				if ( $pass !== true )
				{
					$disabled[] = $bbcode['bbcode_tag'];
				}
			}
		}
		
		return $disabled;
	}
	
	/**
	 * Return paired opening and closing positions.
	 * @param string $txt
	 * @param string $tag
	 * @return array
	 */
	public function getTagPositions( $txt, $tag, $brackets=array('[',']') )
	{
		$close_tag = $brackets[0] . '/' . $tag . $brackets[1];
		$open_tag  = $brackets[0] . $tag;
		$map      = array();
		$iteration = 0;
	
		/* Pick through bit of code */
		while( ( $curPos = stripos( $txt, $open_tag, $curPos ) ) !== false )
		{
			if ( $iteration > 1000 )
			{
				break;
			}
			
			$map['openWithTag'][ $iteration ] = $curPos;
			$map['open'][ $iteration ]        = $curPos + strlen( $open_tag );
				
			$new_pos = strpos( $txt, $brackets[0], $curPos ) ? strpos( $txt, $brackets[0], $curPos ) : $curPos + 1;
				
			/* Got an option, grab that */
			$_option = substr( $txt, $curPos + strlen($open_tag), (strpos( $txt, $brackets[1], $curPos ) - ($curPos + strlen($open_tag) )) );
			
			$map['open'][ $iteration ] += intval( strlen( $_option ) ) + 1;
			
			/* Got a closing tag? */
			$closingTagPos = stripos( $txt, $close_tag, $new_pos );
				
			if ( $closingTagPos !== false )
			{
				$map['close'][ $iteration ]        = $closingTagPos;
				$map['closeWithTag'][ $iteration ] = $closingTagPos + strlen( $close_tag );
				
				$_content  = substr( $txt, ($curPos + strlen( $open_tag )  + strlen($_option) + 1), (stripos( $txt, $close_tag, $curPos ) - ($curPos + strlen($open_tag) + strlen($_option) + 1)) );
				$_tagToEnd = substr( $txt, ($curPos + strlen( $open_tag )  + strlen($_option) + 1), strlen( $txt ));
	
				/* Did we have an opening tag in that mess? */
				if ( $_content && stristr( $_content, $open_tag ) )
				{
					$count = substr_count( strtolower( $_content ), strtolower( $open_tag) );
	
					/* Found N opening tags in portion of text */
					if ( $count > 0 )
					{
						/* So now find Nth closing tag */
						$_nPos = $closingTagPos + strlen( $close_tag );
	
						while( $count > 0 )
						{
							$_closePos = stripos( $txt, $close_tag, $_nPos );
								
							if ( $_closePos !== false )
							{
								$map['close'][ $iteration ]        = $_closePos;
								$map['closeWithTag'][ $iteration ] = $_closePos + strlen( $close_tag );
								
								$_nPos = $_closePos + strlen( $close_tag );
	
								if ( $_nPos >= strlen( $txt ) )
								{
									$count == 0;
								}
							}
								
							$count--;
						}
					}
				}
			}
				
			$iteration++;
				
			$curPos = $closingTagPos ? $closingTagPos : $curPos + 1;
	
			if ( $curPos > strlen($txt) )
			{
				$curPos	= 0;
				break;
			}
		}
	
		return $map;
	}
	
	/**
	 * Build a quote tag
	 * @param string $content
	 * @param string $author
	 * @param string $date
	 * @param int $pid
	 */
	public function buildQuoteTag( $content, $author='', $date='', $collapsed=0, $pid=0 )
	{
		$opts = array();
		
		if ( $author )
		{
			$ops[] = 'data-author="' . $author . '"';
		}
		
		if ( $pid )
		{
			$ops[] = 'data-cid="' . $pid . '"';
		}
		
		if ( $date )
		{
			if ( strlen( $date ) == 10 && intval( $date ) == $date )
			{
				$ops[] = 'data-time="' . $date . '"';
			}
			else
			{
				$ops[] = 'data-date="' . $date . '"';
			}
		}
		
		if ( $collapsed )
		{
			$ops[] = 'data-collapsed="' . $collapsed . '"';
		}
		
		/* Parse out attachments and make into links */
		preg_match_all( '#\[attachment=(.+?):(.+?)\]#', $content, $_matches );
	
		if( is_array( $_matches[1] ) && count( $_matches[1] ) )
		{
			foreach( $_matches[1] as $idx => $attach_id )
			{
				$content = str_replace( "[attachment={$attach_id}:{$_matches[2][$idx]}]", $this->registry->getClass('output')->getReplacement('post_attach_link') . " <a href='{$this->settings['board_url']}/index.php?app=core&amp;module=attach&amp;section=attach&amp;attach_rel_module=post&amp;attach_id={$attach_id}' target='_blank'>{$_matches[2][$idx]}</a>", $content );
			}
		}
		
		/* Convert if we need to */
		if ( $this->isBBCode( $content ) )
		{
			$content = $this->BBCodeToHtml( $content );
		}
		
		return "<p></p><blockquote class='ipsBlockquote'" . implode( ' ', $ops ) . '><p>' . $this->_stripParagraphWrap( $content ) . '</p></blockquote><p></p>';
	}
	
	/**
	 * Parses the bbcode to be shown in the polls.
	 * Parses img and url, if enabled
	 *
	 * @param 	string			Raw input text to parse
	 * @return	string			Parsed text ready to be displayed
	 */
	public function parsePollTags( $text )
	{
		if ( stristr( $text, '[img' ) || stristr( $text, '[url' ) )
		{
			require_once( IPS_ROOT_PATH . 'sources/classes/text/parser/bbcode.php' );
			$bbcode = new class_text_parser_bbcode();
			
			$text = $this->display( $bbcode->_parseBBCode( $text, 'display', array( 'img', 'url' ) ) );
		}
		
		return $text;
	}
	
	/**
	 * Expand the acronyms for SEO
	 * @param string $txt
	 */
	protected function _seoAcronymExpansion( $txt )
	{
		if ( $txt == '' )
		{
			return $txt;
		}
		
		$temp_text = $txt;
		$urls      = array();
		
		$txt       = str_replace( '<#EMO_DIR#>', '-#-#-#EMO_DIR#-#-#-', $txt );
		
		/* Grab images */
		preg_match_all( '#<img([^>]+?)>#i', $txt, $matches );
		
		foreach( $matches[0] as $m )
		{
			$c = count( $urls );
			$urls[ $c ] = $m;
		
			$txt = str_replace( $m, '<!--url{' . $c . '}-->', $txt );
		}
		
		/* Grab <a> */
		preg_match_all( '#<a([^>]+?)>#i', $txt, $matches );
		
		foreach( $matches[0] as $m )
		{
			$c = count( $urls );
			$urls[ $c ] = $m;
		
			$txt = str_replace( $m, '<!--url{' . $c . '}-->', $txt );
		}
		
		/* Grab non linked URLs */
		preg_match_all( '#((http|https|news|ftp)://(?:[^<>\)\[\"\s]+|[a-zA-Z0-9/\._\-!&\#;,%\+\?:=]+))#is', $txt, $matches );
		
		foreach( $matches[0] as $m )
		{
			$c = count( $urls );
			$urls[ $c ] = $m;
		
			$txt = str_replace( $m, '<!--url{' . $c . '}-->', $txt );
		}
		
		//-----------------------------------------
		// Convert back entities
		//-----------------------------------------
		
		for( $i = 65; $i <= 90; $i++ )
		{
			$txt = str_replace( "&#" . $i . ";", chr($i), $txt );
		}
		
		for( $i = 97; $i <= 122; $i++ )
		{
			$txt = str_replace( "&#" . $i . ";", chr($i), $txt );
		}		
		
		//-----------------------------------------
		// Go all loopy
		//-----------------------------------------
		
		$acronyms = $this->cache->getCache('ipseo_acronyms');
		
		if ( is_array($acronyms) && count($acronyms) )
		{
			foreach( $acronyms as $r )
			{
				$this->_currentAcronym = $r;
				
				$wordModifier	= ( IPS_DOC_CHAR_SET == 'UTF-8' && IPSText::isUTF8( $txt ) ) ? '[^\p{L}]|\b' : '\b';
				$caseModifier	= empty($r['a_casesensitive']) ? 'i' : '';
				$r['a_short']	= preg_quote( $r['a_short'], "/" );

				$txt			= preg_replace_callback( '/(^|' . $wordModifier . '|\s)(' . $r['a_short'] . ')(' . $wordModifier . '|\s|!|\?|\.|,|$)/' . $caseModifier, array( $this, '_replaceAcronym' ), $txt );
			}
		}
		
		/* replace urls */
		if ( count( $urls ) )
		{
			preg_match_all( '#\<\!--url\{(\d+?)\}--\>#is', $txt, $matches );
		
			for ( $i = 0; $i < count($matches[0]); $i++ )
			{
				if ( isset( $matches[1][$i] ) )
				{
					$txt = str_replace( $matches[0][$i], $urls[ $matches[1][$i] ], $txt );
				}
			}
		}
		
		$txt = str_replace( '-#-#-#EMO_DIR#-#-#-', '<#EMO_DIR#>', $txt );
	
		return $txt ? $txt : $temp_text;
	}
	
	/**
	 * Callback function to replace a found acronym
	 *
	 * @param	array		$matches		Array of matches
	 * @return	@e string	Replaced text
	 */
	private function _replaceAcronym( $matches=array() )
	{
		$replace = $this->_currentAcronym['a_semantic'] ? "<acronym title='{$this->_currentAcronym['a_long']}' class='bbc ipSeoAcronym'>{$matches[2]}</acronym>" : $this->_currentAcronym['a_long'];
	
		return $matches[1] . $replace . $matches[3];
	}
	
	/**
	 * Strip paragraph wrap tags
	 * @param string $txt
	 * @return string
	 */
	protected function _stripParagraphWrap( $txt )
	{
		$txt = trim( $txt );

		/* Clean up */
		$txt = preg_replace( '#^(<br([^>]+?)?>){1,}#i', '', $txt );
		
		$txt = $this->_stripEmptyLeadingAndTrailingParagraphTags( $txt );
	
		if ( substr( $txt, 0, 3 ) == '<p>' && substr( $txt, -4 ) == '</p>' )
		{
			return substr( $txt, 3, -4 );
		}
		
		return $txt;
	}
	
	/**
	 * Strips off blank or empty P tags
	 * @param string $txt
	 * @return string
	 */
	protected function _stripEmptyLeadingAndTrailingParagraphTags( $txt )
	{
		/* Strip leading Ps */
		while( preg_match( '#^<p([^>]+?)?' . '>((&nbsp;|\s)+?)?</p>#i', $txt, $match ) )
		{
			$txt = trim( preg_replace( '#^<p([^>]+?)?' . '>((&nbsp;|\s)+?)?</p>#i', '', $txt ) );
		}
		
		/* Strip trailing Ps */
		while( preg_match( '#<p([^>]+?)?' . '>((&nbsp;|\s)+?)?</p>$#i', $txt, $match ) )
		{
			$txt = trim( preg_replace( '#<p([^>]+?)?' . '>((&nbsp;|\s)+?)?</p>$#i', '', $txt ) );
		}
		
		/* Strip trailing <br /> */
		while( preg_match( '#<br([^>]+?)?' . '/>((&nbsp;|\s)+?)?$#i', $txt, $match ) )
		{
			$txt = trim( preg_replace( '#<br([^>]+?)?' . '/>((&nbsp;|\s)+?)?$#i', '', $txt ) );
		}
		
		return $txt;
	}
	
	/**
	 * Check and make safe embedded codes
	 * @param array $matches
	 */
	protected function _preserveCodeBoxes( $txt )
	{
		$map = array();

		/* CODE: Fetch paired opening and closing tags */
		$data = $this->getTagPositions( $txt, 'code', array( '[' , ']' ) );
	
		if ( is_array( $data['open'] ) )
		{
			foreach( $data['open'] as $id => $val )
			{
				$o = $data['open'][ $id ];
				$c = $data['close'][ $id ] - $o;
	
				$slice = substr( $txt, $o, $c );
	
				/* Need to bump up lengths of opening and closing */
				$_origLength = strlen( $slice );
	
				/* Extra conversion for BBCODE>HTML mode */
				$slice = str_replace( "[", "&#91;", $slice );
				$slice = preg_replace( '#(https|http|ftp)://#' , '\1&#58;//', $slice );
				#$slice = str_replace( "]", "&#93;", $slice );
				$slice = str_replace( "\n", "<!-preserve.newline-->", $slice );
	
				$_newLength  = strlen( $slice );
	
				$txt = substr_replace( $txt, $slice, $o, $c );
	
				/* Bump! */
				if ( $_newLength != $_origLength )
				{
					foreach( $data['open'] as $_id => $_val )
					{
						$_o = $data['open'][ $_id ];
							
						if ( $_o > $o )
						{
							$data['open'][ $_id ]  += ( $_newLength - $_origLength );
							$data['close'][ $_id ] += ( $_newLength - $_origLength );
						}
					}
				}
			}
		}

		/* PRE: Fetch paired opening and closing tags */
		$data = $this->getTagPositions( $txt, 'pre', array( '<' , '>' ) );
	
		if ( is_array( $data['open'] ) )
		{
			foreach( $data['open'] as $id => $val )
			{
	
				$o = $data['open'][ $id ] ;
				$c = $data['close'][ $id ] - $o;
					
				$slice = substr( $txt, $o, $c );
					
				/* Need to bump up lengths of opening and closing */
				$_origLength = strlen( $slice );
					
				/* Extra conversion for BBCODE>HTML mode */
				$slice = str_replace( "[", "&#91;", $slice );
				$slice = str_replace( "]", "&#93;", $slice );
				$slice = preg_replace( '#(https|http|ftp)://#' , '\1&#58;//', $slice );
				$slice = str_replace( "\n", "<!-preserve.newline-->", $slice );
				
				$_newLength  = strlen( $slice );
					
				$txt = substr_replace( $txt, $slice, $o, $c );
					
				/* Bump! */
				if ( $_newLength != $_origLength )
				{
					foreach( $data['open'] as $_id => $_val )
					{
						$_o = $data['open'][ $_id ] + strlen( '<pre' );
							
						if ( $_o > $o )
						{
							$data['open'][ $_id ]  += ( $_newLength - $_origLength );
							$data['close'][ $_id ] += ( $_newLength - $_origLength );
						}
					}
				}
			}
		}
		
		return $txt;
	}
	
	/**
	 * Sort the smilies nicely in order of length.
	 */
	protected function _sortSmilies()
	{
		$emoticons = array();
	
		if ( ! count( $this->_sortedSmilies ) )
		{
			/* Sort them! */
			$this->_sortedSmilies = $this->cache->getCache('emoticons');
				
			usort( $this->_sortedSmilies, array( $this, '_thisUsort' ) );
		}
	}
	
	/**
	 * Custom sort operation
	 *
	 * @param	string		A
	 * @param	string		B
	 * @return	integer
	 */
	protected static function _thisUsort($a, $b)
	{
		if ( strlen($a['typed']) == strlen($b['typed']) )
		{
			return 0;
		}
	
		return ( strlen($a['typed']) > strlen($b['typed']) ) ? -1 : 1;
	}
	
	/**
	 * Add to errors
	 * @param string $error
	 */
	protected function _addParsingError( $error )
	{
		if ( $error && is_string( $error ) )
		{
			$this->_errors[] = $error;
		
			/* Legacy @todo remove in 4 */
			$this->error = $error;
		}
	}
	
	/**
	 * Retrieve the proper emoticon image code
	 *
	 * @access	protected
	 * @param	string		Emoticon code we are replacing (i.e. :D)
	 * @param	string		Emoticon image to display (i.e. 'biggrin.png')
	 * @return	string		Converted text
	 */
	protected function _retrieveSmiley( $_emoCode, $_emoImage )
	{
		//-----------------------------------------
		// INIT
		//-----------------------------------------
	
		if ( ! $_emoCode or ! $_emoImage )
		{
			return '';
		}
	
		$this->emoticon_count++;
	
		$this->emoticon_alts[] = array( "#EMO_ALT_{$this->emoticon_count}#", $_emoCode );
	
		//-----------------------------------------
		// Return
		//-----------------------------------------
	
		return "<img src='" . $this->settings['emoticons_url'] . "/{$_emoImage}' class='bbc_emoticon' alt='#EMO_ALT_{$this->emoticon_count}#' />";
	}
	
}
