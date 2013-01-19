<?php

/**
 * (e32) ibEconomy
 * Shop Item: Change Member Skin
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
		return $this->lang->words['change_mem_skin'];
	}
	
	/**
	 * Send the "Stock" Description
	 */
	public function description()
	{
		return $this->lang->words['change_mem_skin'];
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
										 ),
							   1  => array( 'form_type' => 'formMultiDropdown',
										    'field' 	=> 'si_protected',
										    'words' 	=> $this->lang->words['select_skins_available'],
										    'desc' 		=> '',
										    'type'      => 'skins'
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
		
		#make skins dropdown
		$allowedSkins = $this->getSkins($theItem['si_protected']);
		
		$allowedSkinsDD  = "<select id='sid' name='sid' class='input_text'>";
		$allowedSkinsDD .= "<optgroup label='{$this->lang->words['select_skin']}'>";
		
		foreach ($allowedSkins as $skin )
		{
			$allowedSkinsDD .= "<option value ='{$skin[0]}'>{$skin[1]}</option>";
		}
		
		$allowedSkinsDD .= "</select>";
		
		$itemHtml[] = array('text' => $this->lang->words['move_to_witch_skin'], 'inputs' => $allowedSkinsDD);		
		
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
		$newSkinID  = $this->request['sid'];
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
		
		#no errors, use it!
		if ( ! $returnMe['error'] )
		{
			#use it
			$this->doUseItem($daMember['member_id'],$newSkinID);
						
			#send PM
			if ( $msg2send != '' )
			{			
				$title = sprintf( $this->lang->words['shop_item_used_on_you'], $theItem['si_title'] );
				$newSkinName = $this->caches['skinsets'][ $newSkinID ]['set_name'];
				$this->registry->ecoclass->sendPM($daMember['member_id'] , '', 0, '', 'generic', $msg2send, $title, $sender, $newSkinName );			
			}
			
			#finish up
			$this->registry->ecoclass->finishUpItemUse($theItem,$myPortItem,$daMember['members_display_name'] != $this->memberData['members_display_name'] ? $daMember['members_display_name'] : '');
			
			#add to redirect text
			$returnMe['redirect_text'] = $this->lang->words['skin_has_been_updated'];
		}
		
		return $returnMe;
	}

	/**
	* Generate available skins dropdown
	*/
	public function getSkins($theItemsAllowedSkins)
	{
		$cache = ipsRegistry::cache()->getCache('skinsets');
		$allowedSkins = explode(",", $theItemsAllowedSkins);
		
		foreach( $cache as $id => $data )
		{
			if (in_array($id, $allowedSkins))
			{
				$skins[] =  array( $data['set_id'], $data['set_name'] );
			}
		}	
		
		#return
		return $skins;				
	}
	/**
	 * Use Item EXECUTION
	 */
	public function doUseItem($mid, $newSkinID)
	{	
		IPSMember::save( $mid, array( 'members' => array( 'skin' => $newSkinID ) ) );
	}	
		
}