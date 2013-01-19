<?php

/*
+--------------------------------------------------------------------------
|   Invision Power Board v<{%dyn.down.var.human.version%}>
|   ========================================
|   by Matthew Mecham
|   (c) 2001 - 2009 Invision Power Services
|   http://www.invisionpower.com
|   ========================================
|   Web: http://www.invisionboard.com
|   Time: <{%dyn.down.var.time%}>
|   Release: <{%dyn.down.var.md5%}>
|   Email: matt@invisionpower.com
|   Licence Info: http://www.invisionboard.com/?license
+---------------------------------------------------------------------------
|
|   > SSI script
|   > Script written by Matt Mecham
|   > Date started: 29th April 2002
|   > UPDATED for 2.0: 1st July 2004
|   > UPDATED for 2.1: 13th Sept 2005
|   > UPDATED for 3.0: 26th Feb 2009
|
+--------------------------------------------------------------------------

+--------------------------------------------------------------------------
|   USAGE:
+--------------------------------------------------------------------------

Simply call this script via PHP includes, or SSI .shtml tags to generate content
on the fly, streamed into your own webpage.

+--------------------------------------------------------------------------
|   To show the last 10 topics and posts in the news forums...
+--------------------------------------------------------------------------

include("http://domain.com/forums/ssi.php?a=news&show=10");

You can adjust the "show" attribute to display a different amount of topics.

+--------------------------------------------------------------------------
|   To show the board statistics
+--------------------------------------------------------------------------

include("http://domain.com/forums/ssi.php?a=stats");

+--------------------------------------------------------------------------
|   To show the active users stats (x Members, X Guests, etc)
+--------------------------------------------------------------------------

include("http://domain.com/forums/ssi.php?a=active");

+--------------------------------------------------------------------------
|   RSS / XML Syndication..
+--------------------------------------------------------------------------

RSS: http://domain.com/forums/ssi.php?a=out&f=1,2,3,4,5&show=10&type=rss
XML: http://domain.com/forums/ssi.php?a=out&f=1,2,3,4,5&show=10&type=xml

Will show last 10 topics in reverse chronological last post date order from
all the forums in the comma separated list
   
*/


/**
* Main executable wrapper.
*
* Set-up and load module to run
*
* @package	InvisionPowerBoard
* @author   Matt Mecham
* @version	3.0
*/

define( 'IPB_THIS_SCRIPT', 'public' );

require_once( './initdata.php' );

require_once( IPS_ROOT_PATH . 'sources/base/ipsRegistry.php' );
require_once( IPS_ROOT_PATH . 'sources/base/ipsController.php' );

/**
* Path to SSI templates directory
*
*/
define( 'SSI_TEMPLATES_DIR', DOC_IPS_ROOT_PATH."ssi_templates" );
/**
* Maximum number of topics to show
*
*/
define( 'SSI_MAX_SHOW'     , 100 );

/**
* Allow SSI export. Enter "0" to turn off.
*
*/
define( 'SSI_ALLOW_SYND'   , 1 );

/* Go... */
$reg = ipsRegistry::instance();
$reg->init();

$ssi = new ssi( $reg );

class ssi
{
	function __construct( ipsRegistry $registry )
	{
		$this->registry   =  $registry;
		$this->DB         =  $this->registry->DB();
		$this->settings   =& $this->registry->fetchSettings();
		$this->request    =& $this->registry->fetchRequest();
		$this->cache      =  $this->registry->cache();
		$this->caches     =& $this->registry->cache()->fetchCaches();
		$this->member     =  $this->registry->member();
		$this->memberData =& $this->registry->member()->fetchMemberData();
		
		/* Load forums class */
		require_once( IPSLib::getAppDir( 'forums' ) . "/sources/classes/forums/class_forums.php" );
		$this->registry->setClass( 'class_forums', new class_forums( $registry ) );
		$this->registry->class_forums->forumsInit();

		switch ($this->request['a'])
		{
			case 'news':
				$this->_doNews();
				break;
				
			case 'active':
				$this->_doActive();
				break;
				
			case 'stats':
				$this->_doStats();
				break;
				
			case 'out':
				if ( SSI_ALLOW_SYND == 1 )
				{
					$this->_doSyndication();
				}
				else
				{
					exit();
				}
				break;
				
			default:
				echo("An error occurred whilst processing this directive");
				exit();
				break;
		}
	}

	/**
	* Do Syndication
	*
	* Export topics / titles from selected forums
	*
	* @access	private
	*/
	private function _doSyndication()
	{
		//----------------------------------------
		// Sort out the forum ids
		//----------------------------------------
		
		$tmp_forums = array();
		$forums     = array();
		
		if ( $this->request['f'] )
		{
			$tmp_forums = explode( ",", $this->request['f'] );
		}
		else
		{
			fatal_error("Fatal error: no forum id specified");
		}
		
		//----------------------------------------
		// Intval the IDs
		//----------------------------------------
		
		foreach ($tmp_forums as $f )
		{
			$f = intval($f);
			
			if ( $f )
			{
				$forums[] = $f;
			}
		}
		
		//----------------------------------------
		// Check...
		//----------------------------------------
		
		if ( count($forums) < 1 )
		{
			fatal_error("Fatal error: no forum id specified");
		}
		
		$sql_fields = implode( ",", $forums );
		
		//----------------------------------------
		// Number of topics to return?
		//----------------------------------------
		
		$perpage = intval($this->request['show']) ? intval($this->request['show']) : 10;
		
		$perpage = ( $perpage > SSI_MAX_SHOW ) ? SSI_MAX_SHOW : $perpage;
		
		//----------------------------------------
		// Load the template...
		//----------------------------------------
		
		if ( $this->request['type'] == 'xml' )
		{
			$template = $this->_loadTemplate("syndicate_xml.html");
		}
		else
		{
			$template = $this->_loadTemplate("syndicate_rss.html");
		}
		
		//----------------------------------------
		// parse..
		//----------------------------------------
		
		$to_echo = "";
		$top     = "";
		$row     = "";
		$bottom  = "";
		
		preg_match( "#\[TOP\](.+?)\[/TOP\]#is", $template, $match );
		
		$top    = trim($match[1]);
		
		preg_match( "#\[ROW\](.+?)\[/ROW\]#is", $template, $match );
		
		$row    = trim($match[1]);
		
		preg_match( "#\[BOTTOM\](.+?)\[/BOTTOM\]#is", $template, $match );
		
		$bottom = trim($match[1]);
		
		//----------------------------------------
		// Header parse...
		//----------------------------------------
		
		@header("Content-Type: text/xml;charset={$this->settings['gb_char_set']}");
		@header('Expires: ' . gmdate('D, d M Y H:i:s') . ' GMT');
		@header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		@header('Pragma: public');
		
		$to_echo .= $this->_parseTemplate( $top, array ( 'board_url'  => $this->settings['base_url'] ,
														 'board_name' => $this->settings['board_name'] ) );
		
		//----------------------------------------
		// Fix up
		//----------------------------------------
		
		$group = $this->caches['group_cache'][ $this->settings['guest_group'] ];
			
		//----------------------------------------
		// Get the topics, member info and other stuff
		//----------------------------------------
		
		$this->DB->build( array( 'select' => '*',
													  'from'   => 'topics',
													  'where'  => "forum_id IN ($sql_fields) AND approved=1",
													  'order'  => 'last_post DESC',
													  'limit'  => array( 0, $perpage ) ) );
		
		$this->DB->execute();
				   
		if ( ! $this->DB->getTotalRows() )
		{
			fatal_error("Could not get the information from the database");
		}
	
		while ( $i = $this->DB->fetch() )
		{
			$forum = $this->registry->class_forums->forum_by_id[ $i['forum_id'] ];
			
			if ( IPSMember::checkPermissions( 'read_perms', $i['forum_id'] ) != TRUE )
			{
				continue;
			}
			
			if ( $forum['password'] != "" )
			{
				continue;
			}
			
			$to_echo .= $this->_parseTemplate( $row, array ( 'topic_title'    => str_replace( '&#', '&amp;#', $i['title'] ),
															 'topic_id'       => $i['tid'],
															 'topic_link'     => $this->settings['base_url']."showtopic=".$i['tid'],
															 'forum_title'    => htmlspecialchars($forum['name']),
															 'forum_id'       => $i['forum_id'],
															 'last_poster_id' => $i['last_poster_id'],
															 'last_post_name' => $i['last_poster_name'],
															 'last_post_time' => $this->registry->getClass('class_localization')->getDate( $i['last_post'] , 'LONG', 1 ),
															 'timestamp'      => $i['last_post'],
															 'starter_id'     => $i['starter_id'],
															 'starter_name'   => $i['starter_name'],
															 'board_url'      => $this->settings['board_url']          ,
															 'board_name'     => $this->settings['board_name'],
															 'rfc_date'       => date( 'r', $i['last_post'] ) )             ) . "\r\n";
		}
		
		//----------------------------------------
		// Print bottom...
		//----------------------------------------
		
		echo $to_echo."\r\n".$bottom;
		
		exit();
	}
	
	
	/**
	* Do Stats
	*
	* Show totals
	*/
	function _doStats()
	{
		//----------------------------------------
		// Load the template...
		//----------------------------------------
		
		$template = $this->_loadTemplate("stats.html");
		
		//----------------------------------------
		// INIT
		//----------------------------------------
		
		$to_echo = "";
		$time    = time() - 900;
		
		$stats       = $this->caches['stats'];
		$total_posts = $stats['total_replies'] + $stats['total_topics'];
		$to_echo  = $this->_parseTemplate( $template, array(  'total_posts'  => $total_posts,
															  'topics'       => $stats['total_topics'],
															  'replies'      => $stats['total_replies'],
															  'members'      => $stats['mem_count'] ) );
		echo $to_echo;
		
		exit();
	}
	
	/**
	* Do News
	*
	* Show news text
	*/
	function _doNews()
	{
		//----------------------------------------
		// Check
		//----------------------------------------
		
		if ( (! $this->settings['news_forum_id']) or ($this->settings['news_forum_id'] == "" ) )
		{
			fatal_error("No news forum assigned");
		}
		
		//----------------------------------------
		// INIT
		//----------------------------------------
		
		$perpage = intval($this->request['show']) > 0 ? intval($this->request['show']) : 10;
		$perpage = ( $perpage > SSI_MAX_SHOW ) ? SSI_MAX_SHOW : $perpage;
		$to_echo = "";
		
		//-----------------------------------------
		// Load the template...
		//-----------------------------------------
		
		$template = $this->_loadTemplate("news.html");
			
		//-----------------------------------------
		// Get the topics, member info and other stuff
		//-----------------------------------------
		
		$this->DB->build( array( 'select'   => 't.*, t.posts as comments',
								 'from'     => array( 'topics' => 't' ),
								 'where'    => "t.forum_id={$this->settings['news_forum_id']} AND t.approved=1",
								 'order'    => 't.tid DESC',
								 'limit'    => array( 0, $perpage ),
								 'add_join' => array( 0 => array( 'select' => 'm.members_display_name as member_name, m.member_group_id, m.member_id,m.title as member_title',
																  'from'   => array( 'members' => 'm' ),
																  'where'  => "m.member_id=t.starter_id",
																  'type'   => 'left' ),
													  1 => array( 'select' => 'f.*',
													  			  'from'   => array( 'forums' => 'f' ),
													              'where'  => 'f.id=t.forum_id',
													              'type'   => 'left' ),
													  2 => array( 'select' => 'p.*',
																  'from'   => array( 'posts' => 'p' ),
																  'where'  => "t.topic_firstpost=p.pid",
																  'type'   => 'left' )  ) )      );
		
		$o = $this->DB->execute();
		
		while ( $row = $this->DB->fetch( $o ) )
		{
			IPSText::getTextClass( 'bbcode' )->parse_smilies			= $row['use_emo'];
			IPSText::getTextClass( 'bbcode' )->parse_html				= ( $row['use_html'] and $this->caches['group_cache'][ $row['member_group_id'] ]['g_dohtml'] and $row['post_htmlstate'] ) ? 1 : 0;
			IPSText::getTextClass( 'bbcode' )->parse_nl2br				= $row['post_htmlstate'] == 2 ? 1 : 0;
			IPSText::getTextClass( 'bbcode' )->parse_bbcode				= $row['use_ibc'];
			IPSText::getTextClass( 'bbcode' )->parsing_section			= 'topics';
			IPSText::getTextClass( 'bbcode' )->parsing_mgroup			= $row['member_group_id'];
			IPSText::getTextClass( 'bbcode' )->parsing_mgroup_others	= $row['mgroup_others'];

			$row['post']	= IPSText::getTextClass( 'bbcode' )->preDisplayParse( $row['post'] );
			
			$row['member_name']  = $row['member_name'] ? $row['member_name'] : $row['author_name'];
			
			$to_echo .= $this->_parseTemplate( $template, array ( 'profile_link'   => $this->settings['base_url']."showuser=".intval($row['member_id']),
																  'member_name'    => $row['member_name'],
																  'post_date'      => $this->registry->getClass('class_localization')->getDate( $row['post_date'], 'LONG', 1 ),
																  'topic_title'    => $row['title'],
																  'post'           => $row['post'],
																  'comments'       => $row['comments'],
																  'view_all_link'  => $this->settings['base_url']."showtopic={$row['tid']}" ) );
		}
		
		echo $to_echo;
		
		exit();
	}
	
	/**
	* Do Active
	*
	* Show active users
	*/
	function _doActive()
	{
		//--------------------------------
		// Load the template...
		//--------------------------------
		
		$template = $this->_loadTemplate("active.html");
		
		//--------------------------------
		// INIT
		//--------------------------------
		
		$to_echo = "";
		
		//--------------------------------
		// Make sure we have a cut off...
		//--------------------------------
		
		if ($this->settings['au_cutoff'] == "")
		{
			$this->settings['au_cutoff'] = 15;
		}
		
		//-----------------------------------------
		// Get the users from the DB
		//-----------------------------------------
		
		$cut_off = $this->settings['au_cutoff'] * 60;
		$time    = time() - $cut_off;
		$rows    = array();
		$ar_time = time();
		
		if ( $this->member['member_id'] )
		{
			$rows = array( $ar_time => array( 'login_type'   => substr($this->member['login_anonymous'],0, 1),
											  'running_time' => $ar_time,
											  'member_id'    => $this->member['member_id'],
											  'member_name'  => $this->member['members_display_name'],
											  'member_group' => $this->member['member_group_id'] ) );
		}
		
		$this->DB->build( array( 'select' => 'id, member_id, member_name, login_type, running_time, member_group',
													  'from'   => 'sessions',
													  'where'  => "running_time > $time",
													  
											 )      );
		
		
		$this->DB->execute();
		
		//-----------------------------------------
		// FETCH...
		//-----------------------------------------
		
		while ( $r = $this->DB->fetch() )
		{
			$rows[ $r['running_time'].'.'.$r['id'] ] = $r;
		}
		
		krsort( $rows );
		
		//--------------------------------
		// cache all printed members so we
		// don't double print them
		//--------------------------------
					
		$cached = array();
		$active = array();
		
		foreach( $rows as $result )
		{
			//-----------------------------------------
			// Bot?
			//-----------------------------------------
			
			if ( strstr( $result['id'], '_session' ) )
			{
				//-----------------------------------------
				// Seen bot of this type yet?
				//-----------------------------------------
				
				$botname = preg_replace( '/^(.+?)=/', "\\1", $result['id'] );
				
				if ( ! $cached[ $result['member_name'] ] )
				{
					$cached[ $result['member_name'] ] = 1;
				}
				else
				{
					//-----------------------------------------
					// Yup, count others as guest
					//-----------------------------------------
					
					$active['GUESTS']++;
				}
			}
			
			//-----------------------------------------
			// Guest?
			//-----------------------------------------
			
			else if ( ! $result['member_id'] )
			{
				$active['GUESTS']++;
			}
			
			//-----------------------------------------
			// Member?
			//-----------------------------------------
			
			else
			{
				if ( empty( $cached[ $result['member_id'] ] ) )
				{
					$cached[ $result['member_id'] ] = 1;
					
					if ($result['login_type'])
					{
						$active['ANON']++;
					}
					else
					{
						$active['MEMBERS']++;
					}
				}
			}
		}
		
		$active['TOTAL'] = $active['MEMBERS'] + $active['GUESTS'] + $active['ANON'];
				   
		$to_echo  = $this->_parseTemplate( $template, array (  'total'   => $active['TOTAL']   ? $active['TOTAL']   : 0 ,
															   'members' => $active['MEMBERS'] ? $active['MEMBERS'] : 0,
															   'guests'  => $active['GUESTS']  ? $active['GUESTS']  : 0,
															   'anon'    => $active['ANON']    ? $active['ANON']    : 0 ) );
		
		
		echo $to_echo;
		
		exit();
	}
	
	
	/**
	* Parse template
	*
	* Parses the template. Duh.
	*
	* @access	private
	* @param	string		Template data
	* @param	array 		Array of data
	*/
	private function _parseTemplate( $template, $assigned=array() )
	{
		foreach( $assigned as $word => $replace)
		{
			$template = preg_replace( "/\{$word\}/i", "$replace", $template );
		}
		
		$template = str_replace( '/ssi.php', '/index.php', $template );
		
		return $this->registry->output->replaceMacros( $template );
	}
	
	/**
	* Load template
	*
	* Loads the template
	*
	* @access   private
	* @param	string	Template to load
	*/
	private function _loadTemplate($template="")
	{
		$filename = SSI_TEMPLATES_DIR."/".$template;
		
		if ( file_exists($filename) )
		{
			if ( $FH = fopen($filename, 'r') )
			{
				$template = fread( $FH, filesize($filename) );
				fclose($FH);
			}
			else
			{
				fatal_error("Couldn't open the template file");
			}
		}
		else
		{
			fatal_error("Template file does not exist");
		}
		
		return $template;
	}
}

function fatal_error( $error )
{
	print $error;
	exit();
}

?>