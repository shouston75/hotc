<?php

/**
 * (e32) ibEconomy
 * Shop Item: Close Topic
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
		return $this->lang->words['close_topic'];
	}
	
	/**
	 * Send the "Stock" Description
	 */
	public function description()
	{
		return $this->lang->words['close_topic'];
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
										    'field' 	=> 'si_protected',
										    'words' 	=> $this->lang->words['protected_forums'],
										    'desc' 		=> $this->lang->words['cannot_be_done_in_forums'],
										    'type'      => 'forums'
										 ),
							   1  => array( 'form_type' => 'formMultiDropdown',
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
		
		$itemHtml[] = array('text' => $this->lang->words['enter_topic_wish_to_close'], 'inputs' => "<input type='text' size='30' name='topic_id' id='topic_id' />");
		
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
		$tid   		= intval($this->request['topic_id']);
		$usrMessage	= trim(IPSText::getTextClass( 'bbcode' )->stripBadWords( $this->request['message'] ));
		$msg2send	= '';		
				
		#no ID?
		if ( !$tid )
		{
			$returnMe['error'] = $this->lang->words['no_topic_found'];
		}
		
		#make sure we have this item already
		$topic	= $this->registry->mysql_ibEconomy->grabTopicByID($tid);
		
		#no topic?
		if ( !$topic['tid'] )
		{
			$returnMe['error'] = $this->lang->words['no_topic_found'];
		}
		
		#topic already closed?
		if ( !$returnMe['error'] && $topic['state'] == 'closed' )
		{
			$returnMe['error'] = $this->lang->words['topic_already_closed'];
		}

		#your own topic when not allowed?
		if ( !$returnMe['error'] && $theItem['si_other_users'] && $topic['starter_id'] == $this->memberData['member_id'] )
		{
			$returnMe['error'] = $this->lang->words['your_topic_not_allowed'];
		}		
		
		#not your own topic
		if ( !$returnMe['error'] && !$theItem['si_other_users'] && $topic['starter_id'] != $this->memberData['member_id'] )
		{
			$returnMe['error'] = $this->lang->words['not_your_topic'];
		}

		#topic in protected forum?
		if ( !$returnMe['error'] && in_array( $topic['forum_id'], explode(',', $theItem['si_protected']) ) )
		{
			$returnMe['error'] = $this->lang->words['topic_in_protected_forum'];
		}

		#topic created by member in protected?
		if ( !$returnMe['error'] && $topic['starter_id'] != $this->memberData['member_id'] && $theItem['si_other_users'] && in_array( $topic['member_group_id'], explode(',', $theItem['si_protected_g']) ) )
		{
			$returnMe['error'] = $this->lang->words['topic_made_by_mem_in_protected_group'];
		}
		
		#topic is someone elses, send message?
		if ( $theItem['si_other_users'] )
		{
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
		
		#no errors, use it!
		if ( ! $returnMe['error'] )
		{
			#use it
			$this->doUseItem($tid);
						
			#send PM
			if ( $msg2send != '' )
			{			
				$title = sprintf( $this->lang->words['shop_item_used_on_you'], $theItem['si_title'] );
				
				$this->registry->ecoclass->sendPM($topic['starter_id'] , '', 0, '', 'generic', $msg2send, $title, $sender, $topic['title'] );			
			}
			
			#finish up
			$this->registry->ecoclass->finishUpItemUse($theItem,$myPortItem,'#'.$tid);
			
			#add to redirect text
			$returnMe['redirect_text'] = $this->lang->words['topic_has_been_closed'];
		}
		
		return $returnMe;
	}

	/**
	 * Use Item EXECUTION
	 */
	public function doUseItem($tid)
	{	
		#pin topic!
		$updateStuff = array('state' => 'closed');
		
		$this->registry->mysql_ibEconomy->adjustTopicViaItem($tid,$updateStuff);		
	}
}