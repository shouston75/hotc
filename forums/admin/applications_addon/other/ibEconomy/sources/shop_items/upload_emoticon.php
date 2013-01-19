<?php

/**
 * (e32) ibEconomy
 * Shop Item: Upload Emoticon
 */

if ( ! defined( 'IN_IPB' ) )
{
	print "<h1>Incorrect access</h1>You cannot access this file directly. If you have recently upgraded, make sure you upgraded all the relevant files.";
	exit();
}

class class_shop_item implements ibEconomy_shop_item
{
	protected $registry;
	protected $DB;
	protected $settings;
	protected $request;
	protected $lang;
	protected $member;
	protected $cache;	

	/**
	 * Class entry point
	 */
	public function __construct( ipsRegistry $registry )
	{
        $this->registry     =  ipsRegistry::instance();
        $this->DB           =  $this->registry->DB();
        $this->settings     =& $this->registry->fetchSettings();
        $this->request      =& $this->registry->fetchRequest();
        $this->lang         =  $this->registry->getClass('class_localization');
        $this->member       =  $this->registry->member();
        $this->memberData  	=& $this->registry->member()->fetchMemberData();
        $this->cache        =  $this->registry->cache();
        $this->caches       =& $this->registry->cache()->fetchCaches();	
	}
	
	//*************************//
	//($%^   ADMIN STUFF   ^%$)//
	//*************************//	

	/**
	 * Send the "Stock" Title
	 */
	public function title()
	{
		return $this->lang->words['upload_emoticon'];
	}
	
	/**
	 * Send the "Stock" Description
	 */
	public function description()
	{
		return $this->lang->words['upload_emoticon'];
	}
	
	/**
	 * Need to pick self or others applicable?
	 */
	public function otherOrSelf()
	{
		return FALSE;
	}	

	/**
	 * Send the Extra Settings
	 */
	public function extra_settings()
	{
		$itemSettings = array( 0 => array( 'form_type' 	=> 'formsimpleinput',
										   'field' 		=> 'si_max_num',
										   'words' 		=> $this->lang->words['max_emoticon_size'],
										   'desc' 		=> ''
										 ),
							   1 => array( 'form_type' 	=> 'formsimpleinput',
										   'field' 		=> 'si_extra_settings_1',
										   'words' 		=> $this->lang->words['max_emoticon_height'],
										   'desc' 		=> ''
										 ),
							   2 => array( 'form_type' 	=> 'formsimpleinput',
										   'field' 		=> 'si_extra_settings_2',
										   'words' 		=> $this->lang->words['max_emoticon_width'],
										   'desc' 		=> ''
										 ),										 
							   3  => array( 'form_type' => 'formMultiDropdown',
										    'field' 	=> 'si_extra_settings_3',
										    'words' 	=> $this->lang->words['emoticon_image_formats'],
										    'desc' 		=> '',
										    'type'      => 'image_types'
										 )										 
							 );
		
		return $itemSettings;
	}
	
	//*************************//
	//($%^   PUBLIC STUFF   ^%$)//
	//*************************//	

	/**
	 * Using Item HTML
	 */
	public function usingItem($theItem)
	{
		$itemHtml = array();
		
		$itemHtml[] = array('text' => $this->lang->words['choose_emoticon_file'], 'inputs' => "<input type='file' class='realbutton' name='uploaded_emo' size='30' />");
		$itemHtml[] = array('text' => $this->lang->words['choose_emoticon_file'], 'inputs' => "<input type='text' name='emo_text' size='15' maxlength='20' />");
		
		return $itemHtml;
	}
	
	/**
	 * Use Item
	 */
	public function useItem($theItem,$myPortItem)
	{
		#init
		$returnMe 			= '';
		$emo_text  			= trim( IPSText::getTextClass('bbcode')->stripBadWords( $this->request['emo_text'] ) );

		#no text input?
		if ( !$emo_text )
		{
			$returnMe['error'] = $this->lang->words['no_emo_text'];
		}
		
		$emo_text  			= ':'.$emo_text.':';
		
		if ( !$returnMe['error'] )
		{		
			$returnMe['error'] = $this->emoticonsUpload($theItem, $emo_text);
		}

		#no errors, use it!
		if ( ! $returnMe['error'] )
		{
			#use it
			$this->doUseItem($numDrawn,$daMember);
			
			#finish up
			$this->registry->ecoclass->finishUpItemUse($theItem,$myPortItem,$daMember['members_display_name']);
			
			#add to redirect text
			$returnMe['redirect_text'] = $this->lang->words['emoticon_added'].$this->lang->words['!'];
		}
		
		return $returnMe;
	}	

	/**
	 * Use Item EXECUTION
	 */
	public function doUseItem($numDrawn,$daMember)
	{
		//already done earlier...
	}
	
	/**
	 * Handles the upload emoticon form
	 */
	public function emoticonsUpload($item, $emo_text)
	{
		#init
		$overwrite		= 0;
		$uploaded		= 0;
		$this->request['dir_default'] = 1;
		
		#setup checks
		$item['si_max_num'] 	= ( $item['si_max_num'] ) 			? $item['si_max_num'] 			: 5000;
		$item['max_width'] 		= ( $item['si_extra_settings_1'] ) 	? $item['si_extra_settings_1'] 	: 50;
		$item['max_height'] 	= ( $item['si_extra_settings_2'] ) 	? $item['si_extra_settings_2'] 	: 50;
		
		#Check the request for uploads
		$directories = array('default');
		$first_dir   = '';
		
		foreach( $this->request as $key => $value )
		{
			if( preg_match( "/^dir_(.*)$/", $key, $match ) )
			{
				if( $this->request[ $match[0] ] == 1 )
				{
					$directories[] = $match[1];
				}
			}
		}
		
		#Can't upload to default
		if ( ! count( $directories ) )
		{
			return $this->lang->words['no_emo_directory'];
		}

		#Get the first directory
		$first_dir = array_shift( $directories );
		
		#Loop through the dirs
		$emodirs = array( 0 => '' );
		
		try
		{
			foreach( new DirectoryIterator( DOC_IPS_ROOT_PATH . 'public/style_emoticons' ) as $file )
 			{
 				if( ! $file->isDot() && $file->isDir() )
 				{
 					#Add to emoticon list
					if( $file->getFilename() == 'default' )
					{
						$emodirs[0] = $file->getFilename();
					}
					else
					{
						$emodirs[] = $file->getFilename();
					}
 				}
 			}
		} catch ( Exception $e ) {}
		
		#Upload Data
		$field     		= 'uploaded_emo';
		$FILE_NAME 		= $_FILES[$field]['name'];
		$FILE_SIZE 		= $_FILES[$field]['size'];
		$FILE_TYPE 		= $_FILES[$field]['type'];
		$FILE_INFO 		= ($FILE_NAME) ? getimagesize($_FILES[$field]['tmp_name']) :'';
		$FILE_HEIGHT 	= $FILE_INFO[1]; 
		$FILE_WIDTH 	= $FILE_INFO[0];

		if ( $FILE_SIZE > $item['si_max_num'] )
		{
			return $this->lang->words['image_too_big']. ' ' .$item['si_max_num'];
		}		
		if ( $FILE_HEIGHT > $item['max_height'] )
		{
			return $this->lang->words['image_too_tall']. ' ' .$item['max_height'];
		}
		if ( $FILE_WIDTH > $item['max_width'] )
		{
			return $this->lang->words['image_too_wide']. ' ' .$item['max_width'];
		}
		
		#Naughty Opera adds the filename on the end of the
		#mime type - we don't want this.$FILE_TYPE = preg_replace( "/^(.+?);.*$/", "\\1", $FILE_TYPE );
		$FILE_TYPE = preg_replace( "/^(.+?);.*$/", "\\1", $FILE_TYPE );
				
		#Naughty Mozilla likes to use "none" to indicate an empty upload field.
		#I love universal languages that aren't universal.
		if ( $_FILES[$field]['name'] == "" or ! $_FILES[$field]['name'] or ($_FILES[$field]['name'] == "none") )
		{
			return $this->lang->words['no_file_selected'];
		}
		
		#Make sure it's not a NAUGHTY file
		$file_extension = preg_replace( "#^.*\.(.+?)$#si", "\\1", strtolower( $_FILES[ $field ]['name'] ) );
	
		if ( ! in_array( $file_extension, explode(',', $item['si_extra_settings_3'] ) ) )
		{
			return $this->lang->words['image_type_not_allowed'];
		} 
		
		#Copy the upload to the uploads directory
		if ( file_exists( DOC_IPS_ROOT_PATH . 'public/style_emoticons/' . $first_dir . "/" . $FILE_NAME ) )
		{
			return $this->lang->words['emo_already_exists'];
		}	
		
		$alreadyThere = $this->registry->mysql_ibEconomy->checkForEmoticons($emo_text);	

		if ( $alreadyThere )
		{
			return $this->lang->words['emo_text_already_exists'];
		}
					
		if ( ! @move_uploaded_file( $_FILES[ $field ]['tmp_name'], DOC_IPS_ROOT_PATH . 'public/style_emoticons/' . $first_dir . "/" . $FILE_NAME) )
		{
			return $this->lang->words['image_upload_failed'];
		}
		else
		{

			@chmod( DOC_IPS_ROOT_PATH . 'public/style_emoticons/' . $first_dir . "/" . $FILE_NAME, 0777 );
			
			#Copy to other folders
			// if ( is_array( $directories ) and count( $directories ) )
			// {
				// foreach ( $directories as $newdir )
				// {
					// if ( file_exists( DOC_IPS_ROOT_PATH . 'public/style_emoticons/' . $newdir . "/" . $FILE_NAME ) )
					// {
						// if ( $overwrite != 1 OR $newdir == 'default' )
						// {
							// return $this->lang->words['emo_already_exists'];
						// }
					// }
					
					// if ( @copy( DOC_IPS_ROOT_PATH . 'public/style_emoticons/' . $first_dir . "/" . $FILE_NAME, DOC_IPS_ROOT_PATH . 'public/style_emoticons/' . $newdir . "/" . $FILE_NAME ) )
					// {
						// @chmod( DOC_IPS_ROOT_PATH . 'public/style_emoticons/' . $newdir . "/" . $FILE_NAME, 0777 );
					// }
				// }
			// }
			
			#Let's make sure this 'image' is available in all directories too
			if ( is_array( $emodirs ) and count( $emodirs ) )
			{
				foreach ( $emodirs as $newdir )
				{
					if ( file_exists( DOC_IPS_ROOT_PATH . 'public/style_emoticons/' . $newdir . "/" . $FILE_NAME ) )
					{
						continue;
					}
					
					if( @copy( DOC_IPS_ROOT_PATH . 'public/style_emoticons/' . $first_dir . "/" .$FILE_NAME, DOC_IPS_ROOT_PATH . 'public/style_emoticons/' . $newdir . "/" . $FILE_NAME ) )
					{
						@chmod( DOC_IPS_ROOT_PATH . 'public/style_emoticons/' . $newdir . "/" . $FILE_NAME, 0777 );
					}
				}
			}
			
			$uploaded++;
		}
		
		if( !$uploaded )
		{
			return $this->lang->words['image_upload_failed'];
		}
		else
		{
			$this->emoticonsAdd($emo_text, $FILE_NAME);
		}
	}
	
	/**
	 * Add emoticons to the default directory
	 */
	public function emoticonsAdd($emo_text, $emo_name)
	{
		#Can only upload to the default directory
		$this->request['id'] == 'default';

		#Get the emoticon info
		$typed = str_replace( '&quot;', "", $emo_text );
		$click = 0;
		$add   = 1;
		$image = $emo_name;
		$set   = 'default';					
		$typed = str_replace( '&#092;', "", $typed );

		#Add this emoticon if we have have the required info */
		if( $add and $typed and $image )
		{
			#Insert the emo record
			$this->DB->insert( 'emoticons', array( 'clickable' => intval( $click ), 'typed' => $typed, 'image' => $image, 'emo_set' => $set ) );
			
			// #Emoticon list
			// $emodirs = array( 0 => '');
			
			// #Loop through all the emoticons
			// try
			// {
				// foreach( new DirectoryIterator( DOC_IPS_ROOT_PATH . 'public/style_emoticons' ) as $file )				 		
				// {
					// if( ! $file->isDot() && $file->isDir() )
					// {
						// if( $file->getFilename() == 'default' )
						// {
							// $emodirs[0] = $file->getFilename();
						// }
						// else
						// {
							// $emodirs[] = $file->getFilename();
						// }
					// }
				// }
			// } catch ( Exception $e ) {}
			
			// #Add this emoticon to the other sets
			// foreach( $emodirs as $directory )
			// {
				// if( $directory == $set )
				// {
					// continue;
				// }
				
				// $this->DB->insert( 'emoticons', array( 'clickable' => intval( $click ), 'typed' => $typed, 'image' => $image, 'emo_set' => $directory ) );
			// }
		}
		
		#Rebuild the cache
		$this->emoticonsRebuildCache();
	}	
	
	/**
	 * Rebuilds the emoticon cache
	 */
	public function emoticonsRebuildCache()
	{
		$cache = array();
			
		$this->DB->build( array( 'select' => 'typed,image,clickable,emo_set', 'from' => 'emoticons' ) );
		$c = $this->DB->execute();
	
		while ( $r = $this->DB->fetch( $c ) )
		{
			$cache[] = $r;
		}
		
		usort( $cache, array( $this, '_thisUsort' ) );

		ipsRegistry::cache()->setCache( 'emoticons', $cache, array( 'array' => 1 ) );
	}

	/**
	 * Custom sort operation
	 */
	private static function _thisUsort($a, $b)
	{
		if ( strlen($a['typed']) == strlen($b['typed']) )
		{
			return 0;
		}

		return ( strlen($a['typed']) > strlen($b['typed']) ) ? -1 : 1;
	}
}