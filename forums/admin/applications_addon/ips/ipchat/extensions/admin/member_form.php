<?php

/**
 * Invision Power Services
 * IP.Board v3.0.4
 * Member property updater
 * Last Updated: $Date: 2010-12-17 07:53:02 -0500 (Fri, 17 Dec 2010) $
 *
 * @author 		$Author: ips_terabyte $
 * @copyright	(c) 2001 - 2009 Invision Power Services, Inc.
 * @license		http://www.invisionpower.com/community/board/license.html
 * @package		IP.Chat
 * @link		http://www.invisionpower.com
 * @version		$Revision: 7443 $
 *
 */

if ( ! defined( 'IN_IPB' ) )
{
	print "<h1>Incorrect access</h1>You cannot access this file directly. If you have recently upgraded, make sure you upgraded all the relevant files.";
	exit();
}

class admin_member_form__ipchat implements admin_member_form
{	
	/**
	 * Tab name
	 *
	 * @var		string		Tab name
	 */
	public $tab_name = "";

	
	/**
	 * Returns sidebar links for this tab
	 *
	 * @author	Brandon Farber
	 * @param	array 			Member data
	 * @return	array 			Array of links
	 */
	public function getSidebarLinks( $member=array() )
	{
		return array();
	}
	
	/**
	 * Returns content for the page.
	 *
	 * @param	array 				Member data
	 * @return	array 				Array of tabs, content
	 */
	public function getDisplayContent( $member=array() )
	{
		//-----------------------------------------
		// Load skin
		//-----------------------------------------
		
		$html = ipsRegistry::getClass('output')->loadTemplate( 'cp_skin_chat', 'ipchat' );

		//-----------------------------------------
		// Load lang
		//-----------------------------------------
				
		ipsRegistry::getClass('class_localization')->loadLanguageFile( array( 'admin_chat' ), 'ipchat' );
		
		//-----------------------------------------
		// Get member data
		//-----------------------------------------
		
		$member 			= IPSMember::load( $member['member_id'], 'extendedProfile' );

		//-----------------------------------------
		// Show...
		//-----------------------------------------
		
		return array( 'tabs' => $html->acp_member_form_tabs( $member ), 'content' => $html->acp_member_form_main( $member ) );
	}
	
	/**
	 * Process the entries for saving and return
	 *
	 * @author	Brandon Farber
	 * @return	array 				Multi-dimensional array (core, extendedProfile) for saving
	 */
	public function getForSave()
	{
		$return = array( 'core' => array(), 'extendedProfile' => array() );
		
		$return['core']['chat_banned']			= intval(ipsRegistry::$request['chat_banned']);

		return $return;
	}
}