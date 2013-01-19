<?php

/**
 * (e32) ibEconomy
 * Shop Item: Steal Reputation
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
		return $this->lang->words['steal_reputation'];
	}
	
	/**
	 * Send the "Stock" Description
	 */
	public function description()
	{
		return $this->lang->words['steal_reputation'];
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
										   'field' 		=> 'si_min_num',
										   'words' 		=> $this->lang->words['min_num_rep_steal'],
										   'desc' 		=> $this->lang->words['min_num_rep_exp_steal']
										 ),
							   1 => array( 'form_type' 	=> 'formsimpleinput',
										   'field' 		=> 'si_max_num',
										   'words' 		=> $this->lang->words['max_num_rep_steal'],
										   'desc' 		=> $this->lang->words['max_num_exp']
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
		
		$itemHtml[] = array('text' => $this->lang->words['input_member_name'], 'inputs' => "<input type='text' size='30' name='mem_name' id='mem_name1' />");
		
		#need member name input?
		if ( $theItem['si_other_users'] )
		{
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

		$theItem['si_min_num'] = ( $theItem['si_min_num'] ) ? $theItem['si_min_num'] : 5;
		$theItem['si_max_num'] = ( $theItem['si_max_num'] ) ? $theItem['si_max_num'] : 10;
		
		#get a random num?
		$numDrawn = rand($theItem['si_min_num'] , $theItem['si_max_num']);

		#if negative, is it allowed?
		if( !$returnMe['error'] && !$this->settings['eco_shopitems_steal_neg'] && $daMember['pp_reputation_points'] - $numDrawn < 0 )
		{
			if ( !$this->settings['eco_shopitems_steal_zero'] )
			{
				$returnMe['error'] = $this->lang->words['would_make_member_negative'];
			}
			else
			{
				$numDrawn = $daMember['pp_reputation_points'];
			}
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
		
		#no errors, use it!
		if ( ! $returnMe['error'] )
		{
			#use it
			$this->doUseItem($numDrawn,$daMember);
						
			#send PM
			if ( $msg2send != '' )
			{			
				$title = sprintf( $this->lang->words['shop_item_used_on_you'], $theItem['si_title'] );
				
				$this->registry->ecoclass->sendPM($daMember['member_id'] , '', 0, '', 'generic', $msg2send, $title, $sender, $numDrawn );			
			}
			
			#finish up
			$this->registry->ecoclass->finishUpItemUse($theItem,$myPortItem,$daMember['members_display_name'].' - '.$this->registry->getClass('class_localization')->formatNumber( $numDrawn ));
			
			#add to redirect text
			$returnMe['redirect_text'] = $this->lang->words['you_have_stolen'].' '.$this->registry->getClass('class_localization')->formatNumber( $numDrawn ).' '.$this->lang->words['reputation_points'].$this->lang->words['!'];
		}
		
		return $returnMe;
	}

	/**
	 * Use Item EXECUTION
	 */
	public function doUseItem($numDrawn,$daMember)
	{
		#steal rep points from s/he
		IPSMember::save( $daMember['member_id'], array( 'extendedProfile'	=> array('pp_reputation_points'	=> $daMember['pp_reputation_points'] - $numDrawn ) ) );
		
		#give rep points 2 me
		IPSMember::save( $this->memberData['member_id'], array( 'extendedProfile'	=> array('pp_reputation_points'	=> $this->memberData['pp_reputation_points'] + $numDrawn ) ) );
	}
}