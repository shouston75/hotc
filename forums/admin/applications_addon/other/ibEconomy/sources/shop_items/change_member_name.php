<?php

/**
 * (e32) ibEconomy
 * Shop Item: Change Member Title
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
		
		$this->registry->class_localization->loadLanguageFile( array( 'public_usercp' ), 'core' );
	}
	
	//*************************//
	//($%^   ADMIN STUFF   ^%$)//
	//*************************//	

	/**
	 * Send the "Stock" Title
	 */
	public function title()
	{
		return $this->lang->words['change_mem_name'];
	}
	
	/**
	 * Send the "Stock" Description
	 */
	public function description()
	{
		return $this->lang->words['change_mem_name'];
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
				$itemHtml[] = array('text' => $this->lang->words['input_message']."<br /><span class='desc'>{$this->lang->words['optional_message']}</span>", 'inputs' => "<textarea size='50' cols='40' rows='5' wrap='soft' name='message' id='message' class='multitext'></textarea>");
			}				
		}
		
		$itemHtml[] = array('text' => $this->lang->words['enter_new_name'], 'inputs' => "<input type='text' size='30' name='new_name' id='new_name' />");
		
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
			$daMember['member_id'] = $this->memberData['member_id'];
		}
		
		#use it (or try to)
		if ( !$returnMe['error'] )
		{		
			$returnMe['error'] = $this->doUseItem($daMember['member_id']);
		}
		
		#no errors, finish it!
		if ( ! $returnMe['error'] )
		{			
			#send PM
			if ( $msg2send != '' )
			{			
				$title = sprintf( $this->lang->words['shop_item_used_on_you'], $theItem['si_title'] );
				
				$this->registry->ecoclass->sendPM($daMember['member_id'] , '', 0, '', 'generic', $msg2send, $title, $sender, trim($this->request['new_name']) );			
			}
			
			#finish up
			$this->registry->ecoclass->finishUpItemUse($theItem,$myPortItem,$daMember['members_display_name'] != $this->memberData['members_display_name'] ? $daMember['members_display_name'] : $this->memberData['members_display_name']);
			
			#add to redirect text
			$returnMe['redirect_text'] = $this->lang->words['display_name_has_been_edited'];
		}
		
		return $returnMe;
	}

	/**
	 * Use Item EXECUTION
	 */
	public function doUseItem($mid)
	{	
		#INIT
		$members_display_name  = trim($this->request['new_name']);
		
		#Check for blanks...
		if ( ! $members_display_name )
		{
			return $this->lang->words['complete_entire_form'];
		}
		
		try
		{
			if ( $this->changeName( $mid, $members_display_name ) === TRUE )
			{
				$this->cache->rebuildCache( 'stats', 'global' );
				
				//return $this->showFormDisplayname( '', $this->lang->words['dname_change_ok'] );
			}
			else
			{
				# We should absolutely never get here. So this is a fail-safe, really to
				# prevent a "false" positive outcome for the end-user
				return $this->lang->words['name_taken_change'];
			}
		}
		catch( Exception $error )
		{
			switch( $error->getMessage() )
			{
				case 'NO_MORE_CHANGES':
					return $this->lang->words['name_change_no_more'];
				break;
				case 'NO_USER':
					return $this->lang->words['name_change_noload'];
				break;
				case 'NO_PERMISSION':
					return $this->lang->words['name_change_noperm'];
				case 'NO_NAME':
					return sprintf( $this->lang->words['name_change_tooshort'], $this->settings['max_user_name_length'] );
				break;
				case 'TOO_LONG':
					return sprintf( $this->lang->words['name_change_tooshort'], $this->settings['max_user_name_length'] );
				break;
				case 'ILLEGAL_CHARS':
					return $this->lang->words['name_change_illegal'];
				break;
				case 'USER_NAME_EXISTS':
					return $this->lang->words['name_change_taken'];
				break;
				default:
					return $error->getMessage();
				break;
			}
		}
	}
	
	/**
	 * Actually change the name...
	 */
	public function changeName($member_id,$name)
	{	
		//-----------------------------------------
		// Load the member
		//-----------------------------------------
		
		$member   = IPSMember::load( $member_id );
		$_seoName = IPSText::makeSeoTitle( $name );
		
		if ( ! $member['member_id'] )
		{
			throw new Exception( "NO_USER" );
		}
		
		//-----------------------------------------
		// Make sure name does not exist
		//-----------------------------------------
		
		try
		{
			if ( IPSMember::getFunction()->checkNameExists( $name, $member, 'members_display_name', true ) === TRUE )
			{
				throw new Exception( "USER_NAME_EXISTS" );
			}
			else
			{
				$this->DB->force_data_type = array( 'dname_previous'	=> 'string',
													'dname_current'		=> 'string' );

				$this->DB->insert( 'dnames_change', array( 'dname_member_id'		=> $member_id,
															  'dname_date'			=> time(),
															  'dname_ip_address'	=> $member['ip_address'],
															  'dname_previous'		=> $member['members_display_name'],
															  'dname_current'		=> $name ) );

				//-----------------------------------------
				// Still here? Change it then
				//-----------------------------------------

				IPSMember::save( $member['member_id'], array( 'core' => array( 'members_display_name' => $name, 'members_l_display_name' => strtolower( $name ), 'members_seo_name' => $_seoName ) ) );

				$this->DB->force_data_type = array( 'last_poster_name' => 'string', 'seo_last_name' => 'string' );
				$this->DB->update( 'forums', array( 'last_poster_name' => $name, 'seo_last_name' => $_seoName ), "last_poster_id=" . $member['member_id'] );

				$this->DB->force_data_type = array( 'member_name' => 'string', 'seo_name' => 'string' );
				$this->DB->update( 'sessions', array( 'member_name' => $name, 'seo_name' => $_seoName ), "member_id=" . $member['member_id'] );

				$this->DB->force_data_type = array( 'starter_name' => 'string', 'seo_first_name' => 'string' );
				$this->DB->update( 'topics', array( 'starter_name' => $name, 'seo_first_name' => $_seoName ), "starter_id=" . $member['member_id'] );

				$this->DB->force_data_type = array( 'last_poster_name' => 'string', 'seo_last_name' => 'string' );
				$this->DB->update( 'topics', array( 'last_poster_name' => $name, 'seo_last_name' => $_seoName ), "last_poster_id=" . $member['member_id'] );

				//-----------------------------------------
				// Recache moderators
				//-----------------------------------------

				$this->registry->cache()->rebuildCache( 'moderators', 'forums' );

				//-----------------------------------------
				// Recache announcements
				//-----------------------------------------

				$this->registry->cache()->rebuildCache( 'announcements', 'forums' );

				//-----------------------------------------
				// Stats to Update?
				//-----------------------------------------

				$this->registry->cache()->rebuildCache( 'stats', 'core' );
				
				IPSLib::runMemberSync( 'onNameChange', $member['member_id'], $name );

				return TRUE;
			}
		}
		catch( Exception $error )
		{
			throw new Exception( $error->getMessage() );
		}
	}
		
}