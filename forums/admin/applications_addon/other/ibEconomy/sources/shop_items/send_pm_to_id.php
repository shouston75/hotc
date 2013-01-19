<?php

/**
 * (e32) ibEconomy
 * Shop Item: Send A PM to a specified ID
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
		return $this->lang->words['send_pm_to_id'];
	}
	
	/**
	 * Send the "Stock" Description
	 */
	public function description()
	{
		return $this->lang->words['send_pm_to_id'];
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
		$itemSettings = array( 0  => array( 'form_type' => 'formTextarea',
										    'field' 	=> 'si_extra_settings_1',
										    'words' 	=> $this->lang->words['instructions_to_show'],
										    'desc' 		=> $this->lang->words['instructions_to_show_desc']
										 ),
							   1  => array( 'form_type' => 'formInput',
										    'field' 	=> 'si_extra_settings_2',
										    'words' 	=> $this->lang->words['message_title'],
										    'desc' 		=> ''
										 ),	
							   2  => array( 'form_type' => 'formInput',
										    'field' 	=> 'si_extra_settings_3',
										    'words' 	=> $this->lang->words['id_of_pm_recip'],
										    'desc' 		=> ''
										 ),	
							   3  => array( 'form_type' => 'formInput',
										    'field' 	=> 'si_extra_settings_4',
										    'words' 	=> $this->lang->words['redirect_message_to_show'],
										    'desc' 		=> ''
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
		
		#need to know what to send in the PM, right?
		$itemHtml[] = array('text' => trim($theItem['si_extra_settings_1']), 'inputs' => "<textarea rows='5' cols='50' wrap='soft' id='pm_contents' class='multitext' name='pm_contents' id='pm_contents'></textarea>");	
		
		return $itemHtml;
	}
	
	/**
	 * Use Item
	 */
	public function useItem($theItem,$myPortItem)
	{
		#init
		$returnMe 	= '';
		$message  	= trim($this->request['pm_contents']);
		$title  	= trim($theItem['si_extra_settings_2']);
		$recipient	= intval($theItem['si_extra_settings_3']) ? intval($theItem['si_extra_settings_3']) : 1;
		$redMsg		= trim($_POST['si_extra_settings_4']);
		
		#no message or title?
		if ( !$message || !$title )
		{
			$returnMe['error'] = $this->lang->words['no_message_provided'];
		}

		#format message
 		IPSText::getTextClass('bbcode')->parse_nl2br   	 = 1;
 		IPSText::getTextClass('bbcode')->parse_html    	 = 0;
 		IPSText::getTextClass('bbcode')->parse_bbcode    = 1;
 		IPSText::getTextClass('bbcode')->parsing_section = 'pms';		


		#format message and title		
		$message 	= IPSText::getTextClass('bbcode')->preDisplayParse( $message );
		

		#user sending PM
		$daMember['member_id'] = $this->memberData['member_id'];
		
		#member trying to use item for which s/he cannot use because their account is the PM sender?
		if ( !$returnMe['error'] && $recipient == $daMember['member_id'] )
		{
			$returnMe['error'] = $this->lang->words['cannot_use_item_because_yr_pm_sender'];
		}
		
		#no errors, use it!
		if ( !$returnMe['error'] )
		{
			#use it
			$this->doUseItem($message, $title, $recipient);
			
			#finish up
			$this->registry->ecoclass->finishUpItemUse($theItem,$myPortItem);
			
			#add to redirect text
			$returnMe['redirect_text'] = $redMsg;
		}
		
		return $returnMe;
	}

	/**
	 * Use Item EXECUTION
	 */
	public function doUseItem($message, $title, $mid)
	{	
		#send PM
		$sender = $this->memberData['member_id'];

		$this->registry->ecoclass->sendPM($mid, '', 0, '', 'generic', $message, $title, $sender, false, false);
	}	
}