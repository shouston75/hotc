<?php

/**
 * (e32) ibEconomy
 * Shop Item: Send Custom Message
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
		return $this->lang->words['secret_message'];
	}
	
	/**
	 * Send the "Stock" Description
	 */
	public function description()
	{
		return $this->lang->words['secret_message'];
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
		$itemSettings = array( 0  => array( 'form_type' => 'formTextarea',
										    'field' 	=> 'si_extra_settings_1',
										    'words' 	=> $this->lang->words['message'],
										    'desc' 		=> $this->lang->words['message_desc']
										 ),	
							   1  => array( 'form_type' => 'formInput',
										    'field' 	=> 'si_extra_settings_2',
										    'words' 	=> $this->lang->words['message_title'],
										    'desc' 		=> ''
										 ),
							   2  => array( 'form_type' => 'formMultiDropdown',
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
		}	
		
		return $itemHtml;
	}
	
	/**
	 * Use Item
	 */
	public function useItem($theItem,$myPortItem)
	{
		#init
		$returnMe 	= '';
		$message  	= trim($theItem['si_extra_settings_1']);
		$title  	= trim($theItem['si_extra_settings_2']);
		$memName  	= $this->request['mem_name'];
		
		#no message or title?
		if ( !$message || !$title )
		{
			$returnMe['error'] = $this->lang->words['no_message_provided'];
		}

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
		}
		else		
		{
			$daMember['member_id'] = $this->memberData['member_id'];
		}
		
		#member trying to use item for which s/he cannot use because their account is the PM sender?
		if ( !$returnMe['error'] && !$theItem['si_other_users'] && $this->settings['eco_shopitems_pm_sender'] == $daMember['member_id'] )
		{
			$returnMe['error'] = $this->lang->words['cannot_use_item_because_yr_pm_sender'];
		}
		
		#no errors, use it!
		if ( !$returnMe['error'] )
		{
			#use it
			$this->doUseItem($message, $title, $daMember['member_id']);
			
			#finish up
			$this->registry->ecoclass->finishUpItemUse($theItem,$myPortItem);
			
			#add to redirect text
			$returnMe['redirect_text'] = $this->lang->words['custom_message_has_been_sent'];
		}
		
		return $returnMe;
	}

	/**
	 * Use Item EXECUTION
	 */
	public function doUseItem($message, $title, $mid)
	{	
		#send PM
		$sender =  ( $mid != $this->memberData['member_id'] ) ? $this->memberData['member_id'] : $this->settings['eco_shopitems_pm_sender'];

		$this->registry->ecoclass->sendPM($mid, '', 0, '', 'generic', $message, $title, $sender );
	}	
}