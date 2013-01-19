<?php

/**
 * (e32) ibEconomy
 * Member Form
 * @ ACP Edit Member
 * + Edit Per-Member Stats
 * + Edit Per-Member Portfolio
 */

if ( ! defined( 'IN_IPB' ) )
{
	print "<h1>Incorrect access</h1>You cannot access this file directly. If you have recently upgraded, make sure you upgraded all the relevant files.";
	exit();
}

class admin_member_form__ibEconomy implements admin_member_form
{
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
	
	/**
	* Tab name
	*/
	public $tab_name = "";
	
	/**
	* Returns sidebar links for this tab
	*/
	public function getSidebarLinks( $member=array() )
	{
		#init
		$array = array();
		
		#load lang
		ipsRegistry::getClass('class_localization')->loadLanguageFile( array( 'admin_ibEconomy' ), 'ibEconomy' );
			
		#build link
		$array[] = array( 'img'   => $this->settings['board_url'].'/admin/skin_cp/images/applications/ibEconomy.png', 
						  'url'   => 'section=members&amp;module=members&amp;do=find_em&amp;mem_id='.$member['member_id'],
						  'title' => sprintf(ipsRegistry::getClass('class_localization')->words['edit_eco_port_items'], $this->settings['eco_general_name'] ) );
	
		#return it
		return $array;
	}
	
	/**
	* Returns content for the page
	*/
	public function getDisplayContent( $member=array() )
	{
		#Load skin
		$this->html = ipsRegistry::getClass('output')->loadTemplate('cp_skin_ibEconomy_member_form', 'ibEconomy');

		#Load lang		
		ipsRegistry::getClass('class_localization')->loadLanguageFile( array( 'admin_ibEconomy' ), 'ibEconomy' );
		ipsRegistry::getClass('class_localization')->loadLanguageFile( array( 'public_ibEconomy' ), 'ibEconomy' );
		
		#Get member data
		$member 				= IPSMember::load( $member['member_id'], 'all' );
		$member['total_points']	= $member[ $this->settings['eco_general_pts_field'] ];

		#display it
		return array( 'tabs' => $this->html->acp_member_form_tabs( $member ), 'content' => $this->html->acp_member_form_main( $member ) );
	}
	
	/**
	* Process the entries for saving and return
	*/
	public function getForSave()
	{
		#init
		$return = array( 'core' => array(), 'extendedProfile' => array() );

		#master ibEconomy SQL Queries
		require_once( IPSLib::getAppDir( 'ibEconomy' ) . "/sql/mysql_ibEconomy.php" );
		ipsRegistry::setClass( 'mysql_ibEconomy', new ibEconomyMySQL($this->registry) );	

		#do points
		$this->registry->mysql_ibEconomy->updateMemberPts2SpecNum( ipsRegistry::$request['member_id'], ipsRegistry::$request['total_points'], 0 );

		#do rest
		$member_data  = array('eco_worth'		=> ipsRegistry::$request['eco_worth'],
							  'eco_welfare' 	=> ipsRegistry::$request['eco_welfare'],
							  'eco_on_welfare' 	=> ipsRegistry::$request['eco_on_welfare']
							 );

		#do update				  
		$this->DB->update( 'pfields_content', $member_data, 'member_id='.ipsRegistry::$request['member_id'] );

		return $return;
	}
	
}