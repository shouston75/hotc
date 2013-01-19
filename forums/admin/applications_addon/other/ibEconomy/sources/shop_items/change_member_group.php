<?php

/**
 * (e32) ibEconomy
 * Shop Item: Change Member Group
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
		return $this->lang->words['change_member_group'];
	}
	
	/**
	 * Send the "Stock" Description
	 */
	public function description()
	{
		return $this->lang->words['change_member_group'];
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
		$itemSettings = array( 0 => array( 'form_type' 	=> 'formYesNo',
										   'field' 		=> 'si_min_num',
										   'words' 		=> $this->lang->words['primary_or_secondary_group'],
										   'desc' 		=> $this->lang->words['primary_or_secondary_group_exp']
										 ),
							   1 => array( 'form_type' 	=> 'formMultiDropdown',
										   'field' 		=> 'si_protected',
										   'words' 		=> $this->lang->words['which_groups_to_include'],
										   'desc' 		=> $this->lang->words['selected_groups_will_be_available_to_choose_from'],
										   'type'      => 'groups2Choose'
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
			
			if ( $theItem['si_allow_user_pm'] )
			{
				$itemHtml[] = array('text' => $this->lang->words['input_message']."<br /><span class='desc'>{$this->lang->words['optional_message']}</span>", 'inputs' => "<textarea size='50' cols='40' rows='5' wrap='soft' name='message' id='message' class='multitext'></textarea>");
			}				
		}
		
		#make group dropdown
		$allowedGroups = $this->registry->ecoclass->getGroups('',$theItem['si_protected'] );
		
		$allowedGrpsDD  = "<select id='gid' name='gid' class='input_text'>";
		$allowedGrpsDD .= "<optgroup label='{$this->lang->words['select_group']}...'>";
		
		foreach ($allowedGroups as $grp )
		{
			$allowedGrpsDD .= "<option value ='{$grp[0]}'>{$grp[1]}</option>";
		}
		
		$allowedGrpsDD .= "</select>";
		
		$itemHtml[] = array('text' => $this->lang->words['move_to_which_group'], 'inputs' => $allowedGrpsDD);

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
		$newGroup  	= $this->request['gid'];
		$usrMessage	= trim(IPSText::getTextClass( 'bbcode' )->stripBadWords( $this->request['message'] ));
		$msg2send	= '';		
				
		#input?
		if ( !$newGroup )
		{
			$returnMe['error'] = $this->lang->words['no_group_selected'];
		}
		
		#group not in allowed groups
		if ( ! in_array( $newGroup, explode(',', $theItem['si_protected']) ) )
		{
			$returnMe['error'] = $this->lang->words['group_not_allowed'];
		}
		
		#no member?
		if ( $theItem['si_other_users'] )
		{
			#input?
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

		#member already in that primary group?
		if ( !$returnMe['error'] && $daMember['member_group_id'] == $newGroup )
		{
			$returnMe['error'] = $this->lang->words['member_already_in_that_primary_group'];
		}

		#member already in that secondary group?
		if ( !$returnMe['error'] && in_array( $newGroup, explode(',', $daMember['mgroup_others']) ) )
		{
			$returnMe['error'] = $this->lang->words['member_already_in_that_secondary_group'];
		}

		#no errors, use it!
		if ( ! $returnMe['error'] )
		{
			#use it
			$this->doUseItem($daMember, $newGroup, $theItem);
				
			#send PM
			if ( $msg2send != '' )
			{			
				$title = sprintf( $this->lang->words['shop_item_used_on_you'], $theItem['si_title'] );
				
				$this->registry->ecoclass->sendPM($daMember['member_id'] , '', 0, '', 'generic', $msg2send, $title, $sender, $this->caches['group_cache'][$newGroup]['g_title'] );			
			}
					
			#finish up
			$this->registry->ecoclass->finishUpItemUse($theItem,$myPortItem,$daMember['members_display_name'] != $this->memberData['members_display_name'] ? $daMember['members_display_name'].' - '.$this->caches['group_cache'][$newGroup]['g_title'] : $this->caches['group_cache'][$newGroup]['g_title']);
			
			#add to redirect text
			$returnMe['redirect_text'] = $this->lang->words['user_group_has_been_adjusted'];
		}
		
		return $returnMe;
	}

	/**
	 * Use Item EXECUTION
	 */
	public function doUseItem($member, $newGroup, $theItem)
	{	
		#do primary group...
		if ( !$theItem['si_min_num'] )
		{
			IPSMember::save( $member['member_id'], array( 'members' => array( 'member_group_id' => $newGroup ) ) );
		}
		
		#do secondary group
		else
		{
			$secondaryGroups = explode(",", $member['mgroup_others']);

			if ( !in_array( $newGroup, $secondaryGroups ) )
			{
				$secondaryGroups[] = $newGroup;
				
				$newSecondaryGrps = implode(",", $secondaryGroups).",";
				$newSecondaryGrps = strpos($newSecondaryGrps, ",") === 0 ? $newSecondaryGrps : ",".$newSecondaryGrps;
				
				IPSMember::save( $member['member_id'], array( 'members' => array( 'mgroup_others' => $newSecondaryGrps ) ) );
			}	
		}
	}
}