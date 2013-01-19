<?php

/**
 * (e32) ibEconomy
 * Shop Item: Password to Forum
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
		return $this->lang->words['password_to_forum'];
	}
	
	/**
	 * Send the "Stock" Description
	 */
	public function description()
	{
		return $this->lang->words['password_to_forum'];
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
		$itemSettings = array( 0  => array( 'form_type' => 'formDropdown',
										    'field' 	=> 'si_extra_settings_1',
										    'words' 	=> $this->lang->words['which_forum'],
										    'desc' 		=> $this->lang->words['which_forum_desc'],
										    'type'      => 'forums_password'
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
				
		return $itemHtml;
	}
	
	/**
	 * Use Item
	 */
	public function useItem($theItem,$myPortItem)
	{
		#init
		$returnMe 	= '';
		$forum_id  	= intval($theItem['si_extra_settings_1']);
		
		#no forum ID?
		if ( !$forum_id )
		{
			$returnMe['error'] = $this->lang->words['no_forum_id_provided'];
		}
		
		#Forum set up
		require_once( IPSLib::getAppDir( 'forums' ) . "/sources/classes/forums/class_forums.php" );
		$this->registry->setClass( 'class_forums', new class_forums( $this->registry ) );
		
		$this->registry->getClass('class_forums')->strip_invisible = 1;
		$this->registry->getClass('class_forums')->forumsInit();
		
		#load forum
		$forum = ipsRegistry::getClass('class_forums')->forum_by_id[ $forum_id ];

		#make sure we have a good forum
		if ( !$forum['id'] )
		{
			$returnMe['error'] = $this->lang->words['no_forum_found'];
		}
		
		#no password needed for that forum?
		if ( !$returnMe['error'] && !$forum['password'] )
		{
			$returnMe['error'] = $this->lang->words['no_password_for_forum'];
		}

		#member trying to use item for which s/he cannot use because their account is the PM sender?
		if ( !$returnMe['error'] && $this->settings['eco_shopitems_pm_sender'] == $this->memberData['member_id'] )
		{
			$returnMe['error'] = $this->lang->words['cannot_use_item_because_yr_pm_sender'];
		}
		
		#no errors, use it!
		if ( !$returnMe['error'] )
		{
			#use it
			$this->doUseItem($forum['password']);
			
			#finish up
			$this->registry->ecoclass->finishUpItemUse($theItem,$myPortItem,'#'.$forum_id);
			
			#add to redirect text
			$returnMe['redirect_text'] = $this->lang->words['password_has_been_pmed'];
		}
		
		return $returnMe;
	}

	/**
	 * Use Item EXECUTION
	 */
	public function doUseItem($password)
	{	
		#send PM
		$this->registry->ecoclass->sendPM($this->memberData['member_id'], '', 0, '', 'generic', $password, $this->lang->words['your_forum_password'] );
	}	
}