<?php

/**
 * (e32) ibEconomy
 * Shop Item: Change Member Signature
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
		return $this->lang->words['change_mem_sig'];
	}
	
	/**
	 * Send the "Stock" Description
	 */
	public function description()
	{
		return $this->lang->words['change_mem_sig'];
	}

	/**
	 * Need to pick self or others applicable?
	 */
	public function otherOrSelf()
	{
		return TRUE;
	}	

	/**
	 * Send the Extra Settings
	 */
	public function extra_settings()
	{
		$itemSettings = array( 0  => array( 'form_type' => 'formMultiDropdown',
										    'field' 	=> 'si_protected_g',
										    'words' 	=> $this->lang->words['protected_groups'],
										    'desc' 		=> $this->lang->words['cannot_be_done_to_groups'],
										    'type'      => 'groups'
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
		
		#need member name input?
		if ( $theItem['si_other_users'] )
		{
			$itemHtml[] = array('text' => $this->lang->words['input_member_name'], 'inputs' => "<input type='text' size='30' name='mem_name' id='mem_name1' />");
			
			if ( $theItem['si_allow_user_pm'] )
			{
				$itemHtml[] = array('text' => $this->lang->words['input_message']."<br /><span class='desc'>{$this->lang->words['optional']}</span>", 'inputs' => "<textarea size='50' cols='40' rows='5' wrap='soft' name='message' id='message' class='multitext'></textarea>");
			}				
		}
		else
		{
			#unconvert for editing
			if( $this->memberData['signature'] )
			{
				if ( IPSText::getTextClass( 'editor' )->method == 'rte' )
				{
					$t_sig = IPSText::getTextClass( 'bbcode' )->convertForRTE( $this->memberData['signature'] );
				}
				else
				{
					IPSText::getTextClass( 'bbcode' )->parse_smilies	= 0;
					IPSText::getTextClass( 'bbcode' )->parse_html		= $this->settings['sig_allow_html'];
					IPSText::getTextClass( 'bbcode' )->parse_bbcode		= $this->settings['sig_allow_ibc'];
					IPSText::getTextClass( 'bbcode' )->parsing_section	= 'signatures';
					
					$t_sig = IPSText::getTextClass( 'bbcode' )->preEditParse( $this->memberData['signature'] );
				}
			}

			IPSText::getTextClass( 'editor' )->remove_emoticons = 1;
		}
		
		$itemHtml[] = array('text' => $this->lang->words['enter_new_sig_below'], 'inputs' => "");
		
		$itemHtml[] = array('text' => "", 'inputs' => IPSText::getTextClass( 'editor' )->showEditor( $t_sig, 'Post' ));

		return $itemHtml;
	}
	
	/**
	 * Use Item
	 */
	public function useItem($theItem,$myPortItem)
	{
		#init
		$returnMe 	= '';
		$memName  	= $this->request['mem_name'];
		$usrMessage	= trim(IPSText::getTextClass( 'bbcode' )->stripBadWords( $this->request['message'] ));
		$msg2send	= '';		
				
		#no member?
		if ( $theItem['si_other_users'] )
		{
			if ( !$memName )
			{
				$returnMe['error'] = $this->lang->words['no_member_entered'];
			}
			else
			{
				#load item recipient
				$daMember = IPSMember::load( $memName, 'all', 'displayname' );				
			}
		
			#no one found?
			if ( !$returnMe['error'] && !$daMember['member_id'] )
			{	
				$returnMe['error'] = $this->lang->words['no_member_found_by_id'];			
			}

			#your own self, when not allowed?
			if ( !$returnMe['error'] && $daMember['member_id'] == $this->memberData['member_id'] )
			{
				$returnMe['error'] = $this->lang->words['item_cannot_be_done_on_self'];
			}			

			#member in protected group?
			if ( !$returnMe['error'] && in_array( $daMember['member_group_id'], explode(',', $theItem['si_protected_g']) ) )
			{
				$returnMe['error'] = $this->lang->words['member_in_protected_group'];
			}	
			
			#send message about item use?
			if ( $theItem['si_allow_user_pm'] && $usrMessage != "" )
			{
				$msg2send = $usrMessage;
				$sender = $this->memberData['member_id'];
			}
			else if ( trim($theItem['si_default_pm']) != "" )
			{
				$msg2send = trim($theItem['si_default_pm']);
			}		
		}
		else		
		{
			$daMember = $this->memberData;
		}
		
		#no errors, use it!
		if ( ! $returnMe['error'] )
		{
			#use it
			$this->doUseItem($daMember);
					
			#send PM
			if ( $msg2send != '' )
			{			
				$title = sprintf( $this->lang->words['shop_item_used_on_you'], $theItem['si_title'] );
				
				$this->registry->ecoclass->sendPM($daMember['member_id'] , '', 0, '', 'generic', $msg2send, $title, $sender );			
			}
				
			#finish up
			$this->registry->ecoclass->finishUpItemUse($theItem,$myPortItem,$daMember['members_display_name'] != $this->memberData['members_display_name'] ? $daMember['members_display_name'] : '');
			
			#add to redirect text
			$returnMe['redirect_text'] = $this->lang->words['sig_has_been_edited'];
		}
		
		return $returnMe;
	}

	/**
	 * Use Item EXECUTION
	 */
	public function doUseItem($member)
	{	
		#init
		$sig_restrictions	= explode( ':', $this->memberData['g_signature_limits'] );
		
		#load usercp lang
		$this->registry->class_localization->loadLanguageFile( array( 'public_usercp' ), 'core' );
		$this->registry->class_localization->loadLanguageFile( array( 'public_error' ), 'core' );
		
		#check length
		if ( (IPSText::mbstrlen($_POST['Post']) > $this->settings['max_sig_length']) and ($this->settings['max_sig_length']) )
		{
			$this->registry->getClass('output')->showError( 'members_sig_too_long', 1029 );
		}
		
		//-----------------------------------------
		// Post process the editor
		// Now we have safe HTML and bbcode
		//-----------------------------------------
		
		$signature = IPSText::getTextClass( 'editor' )->processRawPost( 'Post' );
		
		#parse post
		IPSText::getTextClass( 'bbcode' )->parse_smilies    = 0;
		IPSText::getTextClass( 'bbcode' )->parse_html       = intval($this->settings['sig_allow_html']);
		IPSText::getTextClass( 'bbcode' )->parse_bbcode     = intval($this->settings['sig_allow_ibc']);
		IPSText::getTextClass( 'bbcode' )->parsing_section	= 'signatures';

		$signature		= IPSText::getTextClass('bbcode')->preDbParse( $signature );
		$testSignature	= IPSText::getTextClass('bbcode')->preDisplayParse( $signature );		

		if (IPSText::getTextClass( 'bbcode' )->error != "")
		{
			$this->registry->getClass('output')->showError( IPSText::getTextClass( 'bbcode' )->error, 10210 );
		}
		
		//-----------------------------------------
		// Signature restrictions...
		//-----------------------------------------
		
		$sig_errors	= array();
		
		#max number of images
		if( $sig_restrictions[1] !== '' )
		{
			if( substr_count( strtolower($signature), "[img]" ) > $sig_restrictions[1] )
			{
				$sig_errors[] = sprintf( $this->lang->words['sig_toomanyimages'], $sig_restrictions[1] );
			}
		}
		
		#max number of urls		
		if( $sig_restrictions[4] !== '' )
		{
			if( substr_count( strtolower($signature), "[url" ) > $sig_restrictions[4] )
			{
				$sig_errors[] = sprintf( $this->lang->words['sig_toomanyurls'], $sig_restrictions[4] );
			}
			else
			{
				preg_match_all( "#(^|\s|>)((http|https|news|ftp)://\w+[^\s\[\]\<]+)#is", $signature, $matches );
				
				if( count($matches[1]) > $sig_restrictions[4] )
				{
					$sig_errors[] = sprintf( $this->lang->words['sig_toomanyurls'], $sig_restrictions[4] );
				}
			}
		}
		
		#max number of lines		
		if( $sig_restrictions[5] !== '' )
		{
			$testSig	= IPSText::getTextClass( 'bbcode' )->wordWrap( $signature, $this->settings['post_wordwrap'], '<br />' );

			if( substr_count( $testSig, "<br />" ) >= $sig_restrictions[5] )
			{
				$sig_errors[] = sprintf( $this->lang->words['sig_toomanylines'], $sig_restrictions[5] );
			}
		}
		
		#crapp part		
		if( $sig_restrictions[2] !== '' AND $sig_restrictions[3] !== '' )
		{
			preg_match_all( "/\[img\](.+?)\[\/img\]/i", $signature, $allImages );

			if( count($allImages[1]) )
			{
				foreach( $allImages[1] as $foundImage )
				{
					$imageProperties = @getimagesize( $foundImage );
					
					if( is_array($imageProperties) AND count($imageProperties) )
					{
						if( $imageProperties[0] > $sig_restrictions[2] OR $imageProperties[1] > $sig_restrictions[3] )
						{
							$sig_errors[] = sprintf( $this->lang->words['sig_imagetoobig'], $foundImage, $sig_restrictions[2], $sig_restrictions[3] );
						}
					}
				}
			}
		}
		
		if( count($sig_errors) )
		{
			$this->registry->getClass('output')->showError( implode( '<br />', $sig_errors ), 10211 );
		}
		
		#do it to DB
		IPSMember::save( $member['member_id'], array( 'extendedProfile' => array( 'signature' => $signature ) ) );
		
		#update cache
		IPSContentCache::update( $member['member_id'], 'sig', $testSignature );
		
		return TRUE;
	}	
		
}