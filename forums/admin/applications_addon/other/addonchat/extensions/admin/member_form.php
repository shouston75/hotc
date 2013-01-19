<?php

if(!defined('IN_IPB')) {
	echo "<h1>Uh oh!</h1>This file may not be accessed directly. If you have recently upgraded, make sure you upgraded all the relevant files.";
	die;
}

class admin_member_form__addonchat implements admin_member_form
{
	public $tab_name = "";

	public function getSidebarLinks( $member=array() )
	{
		return array();
	}

	public function getDisplayContent( $member=array(), $tabsUsed = 2 )
	{
		$html = ipsRegistry::getClass('output')->loadTemplate( 'cp_skin_addonchat', 'addonchat' );
		ipsRegistry::getClass('class_localization')->loadLanguageFile( array( 'admin_addonchat' ), 'addonchat' );

		$member = IPSMember::load( $member['member_id'], 'extendedProfile' );

		return array( 
         'tabs' => $html->acp_member_form_tabs( $member, ($tabsUsed+1)),
         'content' => $html->acp_member_form_main( $member, ($tabsUsed+1)));
	}

	public function getForSave()
	{
		$return = array( 'core' => array(), 'extendedProfile' => array() );
		$return['core']['addonchat_banned']			= intval(ipsRegistry::$request['addonchat_banned']);
		return $return;
	}
}