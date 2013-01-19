<?php

/**
 * (e32) ibEconomy
 * Shop Item: Promote to VIP Group
 * Requires SOS VIP Members 1.0 to work
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
		return $this->lang->words['promote_to_vip'];
	}
	
	/**
	 * Send the "Stock" Description
	 */
	public function description()
	{
		return $this->lang->words['promote_to_vip_desc'];
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
		$itemSettings = array( 0  => array( 'form_type' => 'formInput',
										    'field' 	=> 'si_extra_settings_1',
										    'words' 	=> $this->lang->words['number_of_days'],
										    'desc' 		=> $this->lang->words['number_of_days_desc']
										 ),
							   1  => array( 'form_type' => 'formDropdown',
										    'field' 	=> 'si_extra_settings_2',
										    'words' 	=> $this->lang->words['which_group'],
										    'desc' 		=> $this->lang->words['which_group_desc'],
										    'type'      => 'groups2Choose'
										 ),
							   2  => array( 'form_type' => 'formYesNo',
										    'field' 	=> 'si_extra_settings_3',
										    'words' 	=> $this->lang->words['make_permanent'],
										    'desc' 		=> $this->lang->words['make_permanent_desc']
										 ),
							   3  => array( 'form_type' => 'formMultiDropdown',
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
		
		return $itemHtml;
	}
	
	/**
	 * Use Item
	 */
	public function useItem($theItem,$myPortItem)
	{
		#init
		$returnMe 	= '';
		$vipGroup  	= intval($theItem['si_extra_settings_2']);
		$permanent  = intval($theItem['si_extra_settings_3']);
		$numDays  	= ($permanent) ? 0 : intval($theItem['si_extra_settings_1']);
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
			$daMember['member_id'] 			  = $this->memberData['member_id'];
			$daMember['members_display_name'] = $this->memberData['members_display_name'];
		}
		
		#SOS VIP Members installed and enabled?
		$app_cache = $this->cache->getCache('app_cache');
		$sosPromote       = $app_cache[ 'sospromote' ];

		#SOS VIP Members not installed?
		if( ! $sosPromote['app_enabled'] )
		{
 			$returnMe['error'] =  $this->lang->words['SOS_VIP_Members_not_installed'];
		}

		#no days or group?
		if ( !$returnMe['error'] && ( $vipGroup == 0 || (!$permanent && $numDays == 0) ) )
		{
			$returnMe['error'] = $this->lang->words['group_promotion_not_setup_properly'];
		}
		
		#member trying to use item for which s/he cannot use because their account is the PM sender?
		if ( !$returnMe['error'] && $this->settings['eco_shopitems_pm_sender'] == $daMember['member_id'] )
		{
			$returnMe['error'] = $this->lang->words['cannot_use_item_because_yr_pm_sender'];
		}
		
		#load rest of user (again) to see if already promoted
		$user = IPSMember::load( $daMember['member_id'], 'all' );
		
		if ( $user['sospromote_vip'] == 1 )
		{
			$returnMe['error'] = $this->lang->words['already_promoted'];
		}
		
		#no errors, use it!
		if ( !$returnMe['error'] )
		{
			#use it
			$this->doUseItem($user, $numDays, $vipGroup, $permanent);
			
			#send PM
			if ( $msg2send != '' )
			{			
				$title = sprintf( $this->lang->words['shop_item_used_on_you'], $theItem['si_title'] );

				$this->registry->ecoclass->sendPM($daMember['member_id'] , '', 0, '', 'generic', $msg2send, $title, $sender, $this->caches['group_cache'][ $vipGroup ]['g_title'] );			
			}
			
			#finish up
			$this->registry->ecoclass->finishUpItemUse($theItem,$myPortItem, ($theItem['si_other_users']) ? $user['members_display_name'] : "");
			
			#add to redirect text
			$returnMe['redirect_text'] = $this->lang->words['promotion_successful'];
		}
		
		return $returnMe;
	}

	/**
	 * Use Item EXECUTION
	 */
	public function doUseItem($user, $numDays, $vipGroup, $permanent)
	{	
		$this->_sospromoteDoAddMember($user, $numDays, $vipGroup, $permanent);
	}
	
	/**
	 * Promote the member!
	 */
	public function _sospromoteDoAddMember($user, $numDays, $vipGroup, $permanent)
	{
		$permanente = array( 'member_group_id' => $vipGroup, 'sospromote_vip' => 1, 'sospromote_vip_g_origem' => $user['member_group_id'], 'sospromote_vip_eterno' => $permanent, 'sospromote_vip_dias' => $numDays );

		$this->DB->update( 'members', $permanente, 'member_id='.$user['member_id'] );
		
		if ( $this->settings['sospromote_sendpm'] )
		{
			$MP = (!$permanent) ? $this->_sendPM( $this->settings['sospromote_mpativacao'], $user, $numDays ) : $this->_sendPM( $this->settings['sospromote_mpativacaopermanente'], $user, $numDays );
		}
	}

	public function _sendPM( $text, $user, $days )
	{
		#setup messenger lib
        require_once( IPSLib::getAppDir( 'members' ) . '/sources/classes/messaging/messengerFunctions.php' );
        $this->messenger    = new messengerFunctions( $this->registry );
		
        #setup parser
		IPSText::getTextClass('bbcode')->parse_smilies	 = 0;
 		IPSText::getTextClass('bbcode')->parse_nl2br   	 = 0;
 		IPSText::getTextClass('bbcode')->parse_html    	 = 1;
 		IPSText::getTextClass('bbcode')->parse_bbcode    = 0;
 		IPSText::getTextClass('bbcode')->parsing_section = 'pms';		

		#finalize message....		
		$message_body 	= IPSText::getTextClass('bbcode')->preDisplayParse( IPSText::getTextClass('bbcode')->preDbParse( $text ) );
        
		$find     = array( "{nome}", "{dias}", "{diasadicionados}", "{grupooriginal}" );
		$replace  = array( $user['members_display_name'], $days, $user['sospromote_vip_dias'], $this->caches['group_cache'][ $user['member_group_id'] ]['g_title'] );		
		
		$message_body 	= str_replace( $find, $replace, $message_body );				
		
		#finally... send a message		
		$this->messenger->sendNewPersonalTopic( $user['member_id'], 
												$this->settings['sospromote_autormps'], 
												array(), 
												$this->settings['sospromote_titulo'], 
												IPSText::getTextClass( 'editor' )->method == 'rte' ? nl2br($message_body) : $message_body, 
												array(  'origMsgID'     	=> 0,
														'fromMsgID'      	=> 0,
														'postKey'           => md5(microtime()),
														'trackMsg'        	=> 0,
														'addToSentFolder' 	=> 0,
														'hideCCUser'        => 0,
														'forcePm'         	=> 1,
														'isSystem'          => FALSE,
													 )
											   );
	}
}