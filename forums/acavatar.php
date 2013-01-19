<?php
	
	define('IPB_THIS_SCRIPT', 'public');
	if ( file_exists( './initdata.php' ) )
		require_once( './initdata.php' );
	else
		require_once( '../initdata.php' );
	define('IPS_ENFORCE_ACCESS', TRUE);
	require_once( IPS_ROOT_PATH . 'sources/base/ipsRegistry.php' );
	$ipbRegistry = ipsRegistry::instance();
	$ipbRegistry->init();

	$username  = IPSText::parseCleanValue(urldecode(trim($_GET['name'])));
	
	/* Find User */
	ipsRegistry::DB()->build( array(	'select' 	=> 'm.*',
										'from'		=> array( 'members' => 'm' ),
										'where'		=> "m.members_l_display_name='".ipsRegistry::DB()->addSlashes(strtolower($username))."'",
										'limit'		=> array( 0, 1 ),
										'add_join'	=> array( array( 'select' => 'g.g_access_cp',
																	 'from'   => array( 'groups' => 'g' ),
																	 'where'  => 'g.g_id=m.member_group_id' ) )
								)		);
	ipsRegistry::DB()->execute();
	
	/* Member does not exist */
	if ( (!$member = ipsRegistry::DB()->fetch()) || (!$member['member_id']) )
	{
		header("Location: " . ipsRegistry::$settings['board_url'] . "/public/style_images/master/profile/default_thumb.png");
	    exit;
	}	

	$template = null;
	$member = IPSMember::load($member['member_id'], 'core,extendedProfile');
		
	$target = null;
	
	if(ipsRegistry::$settings['addonchat_ipbavatar']==1) {					
		if(stripos($member['avatar_location'], 'http')===0) {
			$target = $member['avatar_location'];		
		}				
	}
	
	if(ipsRegistry::$settings['addonchat_ipbphoto']==1) {
		if( ($member['pp_thumb_photo'] != null) && ($member['pp_thumb_photo']!='') ) {
			$target = ipsRegistry::$settings['board_url'] . "/uploads/" . $member['pp_thumb_photo'];	
		}	
	}	
	
	if($target != null) {
		// Redirect...		
		header("Location: $target");
		exit(0);
	}
			
	// Feature disabled, return default
	header("Location: " . ipsRegistry::$settings['board_url'] . "/public/style_images/master/profile/default_thumb.png");
	exit(0);