<?php

/**
 * (e32) ibEconomy
 * Shop Item: Award Item
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
		return $this->lang->words['award_item'];
	}
	
	/**
	 * Send the "Stock" Description
	 */
	public function description()
	{
		return $this->lang->words['award_description'];
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
		$itemSettings = array( 0 => array( 'form_type' 	=> 'formDropdown',
										   'field' 		=> 'si_extra_settings_1',
										   'words' 		=> $this->lang->words['which_award'],
										   'type'      	=> 'awards'
										 ),
							   1  => array( 'form_type' => 'formMultiDropdown',
										    'field' 	=> 'si_protected_g',
										    'words' 	=> $this->lang->words['protected_groups'],
										    'desc' 		=> $this->lang->words['cannot_be_done_to_groups'],
										    'type'      => 'groups'
										 ),
							   2  => array( 'form_type' => 'forminput',
										    'field' 	=> 'si_extra_settings_2',
										    'words' 	=> $this->lang->words['reason_for_award'],
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
		$memName  	= $this->request['mem_name'];
		$usrMessage	= trim(IPSText::getTextClass( 'bbcode' )->stripBadWords( $this->request['message'] ));
		$msg2send	= '';		

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
		
		#reason for "award" and which award?
		$reason 		= $theItem['si_extra_settings_2'];
		$catAndAwardID	= explode('_', $theItem['si_extra_settings_1']);
		
		if ($this->settings['awds_system_status'] === '0' || $this->settings['awds_system_status'] === '1')
		{
			$award 		= $this->DB->buildAndFetch( array( 	'select'	=> '*',
															'from'   => 'inv_awards',
															'where'  => 'id = ' . $catAndAwardID[1],
											)		);
											
			$awardName		= $award['name'];
		}
		else
		{
			$awardName		= $this->caches['awards_cat_cache'][ $catAndAwardID[0] ]['awards'][ $catAndAwardID[1] ]['awards_name'];
		}
		
		#no errors, use it!
		if ( ! $returnMe['error'] )
		{
			#use it
			$this->doUseItem($daMember['member_id'], $reason, $theItem['si_extra_settings_1']);
			
			#send PM
			if ( $msg2send != '' )
			{			
				$title = sprintf( $this->lang->words['shop_item_used_on_you'], $theItem['si_title'] );
				
				$this->registry->ecoclass->sendPM($daMember['member_id'] , '', 0, '', 'generic', $msg2send, $title, $sender, $awardName );			
			}
			
			#finish up
			$this->registry->ecoclass->finishUpItemUse($theItem,$myPortItem,$daMember['members_display_name'] != $this->memberData['members_display_name'] ? $daMember['members_display_name'] : '');
			
			#add to redirect text
			$returnMe['redirect_text'] = $this->lang->words['item_has_been_added'] ;
		}
		
		return $returnMe;
	}

	/**
	 * Use Item EXECUTION
	 */
	public function doUseItem($member_id, $awards_reason, $awardIdAndCat)
	{	
		#add "award" to mid's profile.  
		$done_by 		= ($member_id != $this->memberData['member_id']) ? $this->memberData['member_id'] : $this->settings['eco_shopitems_pm_sender'];

		#Get the reason ready for parsing
		IPSText::getTextClass('bbcode')->parse_html		= 0;
		IPSText::getTextClass('bbcode')->parse_nl2br	= 1;
		IPSText::getTextClass('bbcode')->parse_smilies	= 1;
		IPSText::getTextClass('bbcode')->parse_bbcode	= 1;
		IPSText::getTextClass('bbcode')->parsing_section= 'awards_reason';
		$awards_reason = IPSText::getTextClass('bbcode')->preDbParse( $awards_reason );
		$lang = $this->lang->words['awards_system_give'];
		$awarded_date = date('Y-m-d H:i:s');
		
		#Get the award id of the award being given
		#explode $awards_action around '_'
		$action_array 	= explode('_',$awardIdAndCat);
		$cat_id 		= $action_array[0];
		$awards_id 		= $action_array[1];
		
		#Check that the award exists
		if ($this->settings['awds_system_status'] === '0' || $this->settings['awds_system_status'] === '1')
		{
			$this->DB->build( array( 
									'select'	=> 't.*',
									'from'		=> array( 'inv_awards' => 't' ),
									'where'		=> 't.id = '.$awards_id.' AND t.parent = '.$cat_id,
								) 	);
		}
		else
		{
			$this->DB->build( array( 
									'select'	=> 't.*',
									'from'		=> array( 'awards' => 't' ),
									'where'		=> 't.awards_id = '.$awards_id.' AND t.awards_cat_id = '.$cat_id,
								) 	);		
		}

		$o = $this->DB->execute();
		
		#Assign the rows to the necessary variables.
		while( $row = $this->DB->fetch( $o ) )
		{
			$exist = 1;
		}
		#if they are allowed to save we do all of our updating in here
		if($exist == 1) 
		{
			if ($this->settings['awds_system_status'] === '0' || $this->settings['awds_system_status'] === '1')
			{
				$this->DB->insert('inv_awards_awarded', array('award_id' => $awards_id, 'user_id' => $member_id, 'awarded_by' => $done_by, 'notes' => $awards_reason, 'date' => time()));		
			}
			else
			{
				$this->DB->insert( 'awarded', array( 'awards_id' => $awards_id, 'awards_user_id' => $member_id, 'awarded_who_gave' => $done_by, 'awarded_reason' => $awards_reason, 'awarded_date' => $awarded_date ));			
			}
		} 
		#if they aren't then send them away
		else 
		{
			$this->awardsRedirect($this->lang->words['item_does_not_exist']);
		}		
	}
}