<?php
/**
 * <pre>
 * Invision Power Services
 * IP.Board v3.1.3
 * User control panel forms
 * Last Updated: $Date: 2010-10-21 07:08:38 -0400 (Thu, 21 Oct 2010) $
 * </pre>
 *
 * @author 		$Author: ips_terabyte $
 * @copyright	(c) 2001 - 2009 Invision Power Services, Inc.
 * @license		http://www.invisionpower.com/community/board/license.html
 * @package		IP.Board
 * @subpackage	Members
 * @link		http://www.invisionpower.com
 * @since		1st march 2002
 * @version		$Revision: 7007 $
 */

if ( ! defined( 'IN_IPB' ) )
{
	print "<h1>Incorrect access</h1>You cannot access this file directly. If you have recently upgraded, make sure you upgraded all the relevant files.";
	exit();
}

class usercpForms_members extends public_core_usercp_manualResolver implements interface_usercp
{
	/**
	 * Tab name
	 * This can be left blank and the application title will
	 * be used
	 *
	 * @var		string
	 */
	public $tab_name = "Profile";
	
	/**
	 * Default area code
	 *
	 * @var		string
	 */
	public $defaultAreaCode = 'profileinfo';
	
	/**
	 * OK Message
	 * This is an optional message to return back to the framework
	 * to replace the standard 'Settings saved' message
	 *
	 * @var		string
	 */
	public $ok_message = '';
	
	/**
	 * Hide 'save' button and form elements
	 * Useful if you have custom output that doesn't
	 * require it
	 *
	 * @var		bool
	 */
	public $hide_form_and_save_button = false;
	
	/**
	 * If you wish to allow uploads, set a value for this
	 *
	 * @var		integer
	 */
	public $uploadFormMax = 0;	
	
	/**
	 * Initiate this module
	 *
	 * @return	void
	 */
	public function init()
	{
		$this->tab_name	= ipsRegistry::getClass('class_localization')->words['tab__members'];
	}
	
	/**
	 * Return links for this tab
	 * You may return an empty array or FALSE to not have
	 * any links show in the tab.
	 *
	 * The links must have 'area=xxxxx'. The rest of the URL
	 * is added automatically.
	 * 'area' can only be a-z A-Z 0-9 - _
	 *
	 * @author	Matt Mecham
	 * @return	array 		Links
	 */
	public function getLinks()
	{
		$array = array();

		$array[] = array( 'url'    => 'area=profileinfo',
						  'title'  => ipsRegistry::instance()->getClass('class_localization')->words['change_settings'],
						  'active' => $this->request['tab'] == 'members' && $this->request['area'] == 'profileinfo' ? 1 : 0,
						  'area'   => 'profileinfo'
						   );
		
		$array[] = array( 'url'    => 'area=aboutme',
						  'title'  => ipsRegistry::instance()->getClass('class_localization')->words['change_aboutme'],
						  'active' => $this->request['tab'] == 'members' && $this->request['area'] == 'aboutme' ? 1 : 0,
						  'area'   => 'aboutme'
						 );
		
		$sig_restrictions	= explode( ':', $this->memberData['g_signature_limits'] );
		
		if ( ! $sig_restrictions[0] OR ( $sig_restrictions[0] AND $this->memberData['g_sig_unit'] ) )
		{
			$array[] = array( 'url'    => 'area=signature',
							  'title'  => ipsRegistry::instance()->getClass('class_localization')->words['m_sig_info'],
							  'active' => $this->request['tab'] == 'members' && $this->request['area'] == 'signature' ? 1 : 0,
							  'area'   => 'signature'
							  );
		}
		
		if ( $this->memberData['g_photo_max_vars'] != "" AND $this->memberData['g_photo_max_vars'] != "::" )
		{
			$_bits	= explode( ":", $this->memberData['g_photo_max_vars'] );
			
			if( $_bits[0] )
			{
				$array[] = array( 'url'    => 'area=photo',
								  'title'  => ipsRegistry::instance()->getClass('class_localization')->words['m_change_photo'],
								  'active' => $this->request['tab'] == 'members' && $this->request['area'] == 'photo' ? 1 : 0,
								  'area'   => 'photo'
								 );
			}
		}
		
		if( $this->settings['avatars_on'] )
		{
			$array[] = array( 'url'    => 'area=avatar',
							  'title'  => ipsRegistry::instance()->getClass('class_localization')->words['m_avatar_info'],
							  'active' => $this->request['tab'] == 'members' && $this->request['area'] == 'avatar' ? 1 : 0,
							  'area'   => 'avatar'
						 );
		}
	
		$array[] = array( 'url'    => 'area=ignoredusers',
						  'title'  => ipsRegistry::instance()->getClass('class_localization')->words['m_ignore_users'],
						  'active' => $this->request['tab'] == 'members' && $this->request['area'] == 'ignoredusers' ? 1 : 0,
						  'area'   => 'ignoredusers'
						 );
						
		if ( IPSLib::fbc_enabled() === TRUE )
		{
			$array[] = array( 'url'    => 'area=facebook',
							  'title'  => ipsRegistry::instance()->getClass('class_localization')->words['m_facebook'],
							  'active' => $this->request['tab'] == 'members' && $this->request['area'] == 'facebook' ? 1 : 0,
							  'area'   => 'facebook'
							 );
		}
		
		if ( IPSLib::twitter_enabled() === TRUE )
		{
			$array[] = array( 'url'    => 'area=twitter',
							  'title'  => ipsRegistry::instance()->getClass('class_localization')->words['m_twitter'],
							  'active' => $this->request['tab'] == 'members' && $this->request['area'] == 'twitter' ? 1 : 0,
							  'area'   => 'twitter'
							 );
		}
		
		if ( $this->memberData['gbw_allow_customization'] AND ! $this->memberData['bw_disable_customization'] )
		{
			$array[] = array( 'url'    => 'area=customize',
							  'title'  => ipsRegistry::instance()->getClass('class_localization')->words['m_customize'],
							  'active' => $this->request['tab'] == 'members' && $this->request['area'] == 'customize' ? 1 : 0,
							  'area'   => 'customize'
							 );
		}
		
		if ( $this->memberData['g_is_supmod'] == 1 )
		{
			$array[] = array( 'url'    => 'area=mod_ipaddress',
							  'title'  => ipsRegistry::instance()->getClass('class_localization')->words['menu_ipsearch'],
							  'active' => $this->request['tab'] == 'members' && $this->request['area'] == 'mod_ipaddress' ? 1 : 0,
							  'area'   => 'mod_ipaddress'
							);
							
			$array[] = array( 'url'   => 'area=mod_member',
							  'title' => ipsRegistry::instance()->getClass('class_localization')->words['menu_memsearch'],
							  'active' => $this->request['tab'] == 'members' && $this->request['area'] == 'mod_member' ? 1 : 0,
							  'area'   => 'mod_member'
						 	);
		}
		
		return $array;
	}

	/**
	 * Run custom event
	 *
	 * If you pass a 'do' in the URL / post form that is not either:
	 * save / save_form or show / show_form then this function is loaded
	 * instead. You can return a HTML chunk to be used in the UserCP (the
	 * tabs and footer are auto loaded) or redirect to a link.
	 *
	 * If you are returning HTML, you can use $this->hide_form_and_save_button = 1;
	 * to remove the form and save button that is automatically placed there.
	 *
	 * @author	Matt Mecham
	 * @param	string		Current area
	 * @return	mixed		html or void
	 */
	public function runCustomEvent( $currentArea )
	{
		//-----------------------------------------
		// INIT
		//-----------------------------------------
		
		$html = '';

		//-----------------------------------------
		// What to do?
		//-----------------------------------------
		
		switch( $currentArea )
		{
			case 'removephoto':
				return $this->customEvent_removePhoto();
			break;
			case 'removeavatar':
				return $this->customEvent_removeAvatar();
			break;
			case 'removeIgnoredUser':
				return $this->customEvent_removeIgnoredUser();
			break;
			case 'toggleIgnoredUser':
				return $this->customEvent_toggleIgnoredUser();
			break;
			case 'mod_ipaddress':
			case 'modIpaddress':
				$html = $this->customEvent_modIPAddresses();
			break;
			case 'mod_member':
				$html = $this->customEvent_modFindUser();
			break;
			case 'facebookSync':
				$html = $this->customEvent_facebookSync();
			break;
			case 'facebookRemove':
				$html = $this->customEvent_facebookRemove();
			break;
			case 'twitterRemove':
				$html = $this->customEvent_twitterRemove();
			break;
			case 'facebookLink':
				$html = $this->customEvent_facebookLink();
			break;
		}
		
		//-----------------------------------------
		// Turn off save button
		//-----------------------------------------
		
		$this->hide_form_and_save_button = 1;
		
		//-----------------------------------------
		// Return
		//-----------------------------------------
		
		return $html;
	}
	
	/**
	 * Custom Event: Remove Twitter link
	 *
	 * @return	void  
	 */
	public function customEvent_twitterRemove()
	{
		//-----------------------------------------
		// Check secure hash...
		//-----------------------------------------
		
		if ( $this->request['secure_key'] != $this->member->form_hash )
		{
			$this->registry->output->showError( 'authorization_error', 100, true, null, 403 );
		}
		
		//-----------------------------------------
		// Okay... 
		//-----------------------------------------
		
		if ( $this->memberData['twitter_id'] )
		{
			/* Remove the link */
			IPSMember::save( $this->memberData['member_id'], array( 'core' => array( 'twitter_id' => 0, 'twitter_token' => '', 'twitter_secret' => '' ) ) );
		}
		
		/* Log the user out */
		$this->registry->getClass('output')->silentRedirect( $this->settings['base_url']."app=core&module=global&section=login&do=logout&k=" . $this->member->form_hash );
	}
	
	/**
	 * Custom Event: Create facebook link
	 *
	 * @return	void  
	 */
	public function customEvent_facebookLink()
	{
		//-----------------------------------------
		// Check secure hash...
		//-----------------------------------------
		
		if ( $this->request['secure_key'] != $this->member->form_hash )
		{
			$this->registry->output->showError( 'authorization_error', 100, true, null, 403 );
		}
		
		/* Load application */
		require_once( IPS_ROOT_PATH . 'sources/classes/facebook/connect.php' );
		$facebook = new facebook_connect( $this->registry );
		
		try
		{
			$facebook->linkMember( $this->memberData['member_id'] );
		}
		catch( Exception $error )
		{
			$msg = $error->getMessage();
		
			switch( $msg )
			{
				default:
				case 'NO_FACEBOOK_USER_LOGGED_IN':
				case 'ALREADY_LINKED':
					$this->registry->getClass('output')->showError( 'fbc_authorization_screwup', 1005.99, null, null, 403 );
				break;
			}
		}
		
		//-----------------------------------------
		// Return
		//-----------------------------------------

		$this->registry->getClass('output')->silentRedirect( $this->settings['base_url']."app=core&amp;module=usercp&amp;tab=members&amp;area=facebook&amp;do=show" );
	}
	
	/**
	 * Custom Event: Remove facebook link
	 *
	 * @return	void  
	 */
	public function customEvent_facebookRemove()
	{
		//-----------------------------------------
		// Check secure hash...
		//-----------------------------------------
		
		if ( $this->request['secure_key'] != $this->member->form_hash )
		{
			$this->registry->output->showError( 'authorization_error', 100, true, null, 403 );
		}
		
		require_once( IPS_ROOT_PATH . 'sources/classes/facebook/connect.php' );
		$facebook = new facebook_connect( $this->registry );
			
		//-----------------------------------------
		// Okay...
		//-----------------------------------------
		
		if ( $this->memberData['fb_uid'] )
		{
			/* Unauthorize application */
			$facebook->revokeAuthorization();
						
			/* Remove the link */
			IPSMember::save( $this->memberData['member_id'], array( 'core' => array( 'fb_uid' => 0, 'fb_emailhash' => '', 'fb_token' => '', 'fb_lastsync' => 0 ) ) );
		}
		
		/* Log the user out */
		$this->registry->getClass('output')->silentRedirect( $this->settings['base_url']."app=core&module=global&section=login&do=logout&k=" . $this->member->form_hash );
	}
	
	/**
	 * Custom Event: Sync up facebook
	 * NO LONGER USED. LEFT FOR FIX CONFIRMATION
	 *
	 * @return	void  
	 */
	public function customEvent_facebookSync()
	{
		if ( IPSLib::fbc_enabled() === TRUE )
		{
			require_once( IPS_ROOT_PATH . 'sources/classes/facebook/connect.php' );
			$facebook = new facebook_connect( $this->registry );
			
			try
			{
				$facebook->syncMember( $this->memberData );
			}
			catch( Exception $error )
			{
				$msg = $error->getMessage();
				
				switch( $msg )
				{
					case 'NOT_LINKED':
					case 'NO_MEMBER':
					default:
						$this->registry->getClass('output')->showError( 'fbc_authorization_screwup', 1005, null, null, 403 );
					break;
				}
			}
			
			//-----------------------------------------
			// Return
			//-----------------------------------------

			$this->registry->getClass('output')->silentRedirect( $this->settings['base_url']."app=core&amp;module=usercp&amp;tab=members&amp;area=facebook&amp;do=show" );
		}
	}
	
	/**
	 * Custom Event: Run the find user tool
	 *
	 * @return	void  
	 */
	public function customEvent_toggleIgnoredUser()
	{
		//-----------------------------------------
		// INIT
		//-----------------------------------------
		
		$member_id = intval( $this->request['id'] );
		$field	   = $this->request['field'];
		$update    = array();
		
		//-----------------------------------------
		// Grab user
		//-----------------------------------------
		
		$ignoredUser = $this->DB->buildAndFetch( array( 'select' => '*',
															   'from'   => 'ignored_users',
															   'where'  => 'ignore_ignore_id=' . $member_id . ' AND ignore_owner_id=' . $this->memberData['member_id'] ) );
															
		if ( $ignoredUser['ignore_id'] )
		{
			switch( $field )
			{
				default:
				case 'topics':
					$update = array( 'ignore_topics' => ( $ignoredUser['ignore_topics'] == 1 ) ? 0 : 1 );
				break;
				case 'messages':
					$update = array( 'ignore_messages' => ( $ignoredUser['ignore_messages'] == 1 ) ? 0 : 1 );
				break;
			}
			
			//-----------------------------------------
			// Update
			//-----------------------------------------

			$this->DB->update( 'ignored_users', $update, 'ignore_id=' . $ignoredUser['ignore_id'] );
			
			/* Rebuild cache */
			IPSMember::rebuildIgnoredUsersCache( $this->memberData );
		}
	
		//-----------------------------------------
		// Return
		//-----------------------------------------
		
		$this->registry->getClass('output')->silentRedirect( $this->settings['base_url']."app=core&amp;module=usercp&amp;tab=members&amp;area=ignoredusers&amp;do=show" );
	}
	
	/**
	 * Custom Event: Run the find user tool
	 *
	 * @return	string	HTML
	 */
	public function customEvent_modFindUser()
	{
		//-----------------------------------------
		// INIT
		//-----------------------------------------
		
		$name     = $this->request['name'];
		$startVal = intval( $this->request['st'] );
		$sql      = "m.members_l_username LIKE '" . $name . "%' OR m.members_l_display_name LIKE '%" . $name . "%'";
		
		if ( $name == "" )
		{
			return $this->formModMember($this->lang->words['cp_no_matches']);
		}
		
		//-----------------------------------------
		// Query the DB for possible matches
		//-----------------------------------------
		
		$this->DB->build( array( 'select' => 'COUNT(member_id) as max', 'from' => 'members m', 'where' => $sql ) );
		$this->DB->execute();
		
		$total_possible = $this->DB->fetch();
		
		if ( $total_possible['max'] < 1 )
		{
			return $this->formModMember( $this->lang->words['cp_no_matches'] );
		}
		
		$pages = $this->registry->getClass('output')->generatePagination( array( 'totalItems'         => $total_possible['max'],
														  						 'itemsPerPage'       => 20,
																				 'currentStartValue'  => $startVal,
																				 'baseUrl'            => "app=core&amp;module=usercp&amp;tab=members&amp;area==mod_member&amp;do=custom&amp;name=" . $name . "",
																				)  );
									  
		$this->DB->build( array( 'select'	=> 'm.name, m.members_display_name, m.members_seo_name, m.member_id, m.ip_address, m.posts, m.joined, m.member_group_id',
								   'from'	=> array( 'members' => 'm' ),
								   'where'	=> $sql,
								   'order'	=> "m.joined DESC",
								   'limit'	=> array( $startVal,20 ),
								   'add_join'	=> array(
								   						array( 'select'	=> 'g.g_access_cp',
								   								'from'	=> array( 'groups' => 'g' ),
								 								'where' => 'm.member_group_id=g.g_id',
								   								'type'	=> 'left'
								   							)
								   						)
						)		);
		$this->DB->execute();
		
		while( $row = $this->DB->fetch() )
		{
			$row['joined']    = $this->registry->getClass( 'class_localization')->getDate( $row['joined'], 'JOINED' );
			$row['groupname'] = IPSLib::makeNameFormatted( $this->caches['group_cache'][ $row['member_group_id'] ]['g_title'], $row['member_group_id'] );
							  
			if ( !$this->memberData['g_access_cp'] and $row['g_access_cp'] )
			{
				$row['ip_address'] = '--';
			}
			
			$members[ $row['member_id'] ] = $row;
		}

		return $this->formModMember( $this->registry->getClass('output')->getTemplate('ucp')->membersModIPFormMembers($pages, $members) );
	}
	
	/**
	 * Custom Event: Run the IP tool
	 *
	 * @return	string	HTML  
	 */
	public function customEvent_modIPAddresses()
	{
		//-----------------------------------------
		// INIT
		//-----------------------------------------
		
		$exactMatch     = 1;
		$finalIPString  = trim( $this->request['ip'] );
		$startVal       = intval ($this->request['st'] );
		$ipTool			= $this->request['iptool'];
		$content        = "";
		
		//-----------------------------------------
		// Have permission to match?
		//-----------------------------------------
		
		if ( ! $this->memberData['g_is_supmod'] )
 		{
 			$this->registry->getClass('output')->showError( 'members_tool_supmod', 2020, null, null, 403 );
 		}
 		
 		//-----------------------------------------
		// Remove trailing periods
		//-----------------------------------------
		
		if ( strstr( $finalIPString, '*' ) )
		{
			$exactMatch    = 0;
			$finalIPString = preg_replace( "/^(.+?)\*(.+?)?$/", "\\1", $finalIPString ).'%';
		}
		
		//-----------------------------------------
		// H'okay, what have we been asked to do?
		// (that's a metaphorical "we" in a rhetorical question)
		//-----------------------------------------
		
		if ( $ipTool == 'resolve' )
		{
			$resolved = @gethostbyaddr( $finalIPString );
			
			if ( $resolved == "" )
			{
				return $this->formIPAddresses( $this->registry->output->getTemplate('ucp')->inlineModIPMessage( $this->lang->words['cp_no_matches'] ) );
			}
			else
			{
				return $this->formIPAddresses( $this->registry->output->getTemplate('ucp')->inlineModIPMessage( sprintf($this->lang->words['ip_resolve_result'], $finalIPString, $resolved) ) );
			}
		}
		else if ( $ipTool == 'members' )
		{
			if ( $exactMatch == 0 )
			{
				$sql = "ip_address LIKE '$finalIPString'";
			}
			else
			{
				$sql = "ip_address='$finalIPString'";
			}
			
			$this->DB->build( array( 'select' => 'count(member_id) as max', 'from' => 'members', 'where' => $sql ) );
			$this->DB->execute();
			
			$total_possible = $this->DB->fetch();
			
			if ($total_possible['max'] < 1)
			{
				return $this->formIPAddresses( $this->registry->output->getTemplate('ucp')->inlineModIPMessage( $this->lang->words['cp_no_matches'] ) );
			}
			
			$pages = $this->registry->getClass('output')->generatePagination( array( 'totalItems'         => $total_possible['max'],
															  						 'itemsPerPage'       => 20,
																					 'currentStartValue'  => $startVal,
																					 'baseUrl'            => "app=core&amp;module=usercp&amp;tab=members&amp;area=mod_ipaddress&amp;do=custom&amp;iptool=members&amp;ip=" . $this->request['ip'] . "",
																					) );
										  
			
			
			if ( !$this->memberData['g_access_cp'] )
			{
				$sql .= "AND g.g_access_cp != 1";
			}
			
			$this->DB->build( array( 'select'	=> 'm.name, m.members_display_name, m.members_seo_name, m.member_id, m.ip_address, m.posts, m.joined, m.member_group_id',
								   'from'	=> array( 'members' => 'm' ),
								   'where'	=> 'm.' . $sql,
								   'order'	=> "m.joined DESC",
								   'limit'	=> array( $startVal,20 ),
								   'add_join'	=> array(
								   						array( 'select'	=> 'g.g_access_cp',
								   								'from'	=> array( 'groups' => 'g' ),
								   								'type'	=> 'left',
								   								'where'	=> 'g.g_id=m.member_group_id',
								   							)
								   						)
						)		);
			$this->DB->execute();
		
			while( $row = $this->DB->fetch() )
			{
				$row['joined']    = $this->registry->getClass( 'class_localization')->getDate( $row['joined'], 'JOINED' );
				$row['groupname'] = IPSLib::makeNameFormatted( $this->caches['group_cache'][ $row['member_group_id'] ]['g_title'], $row['member_group_id'] );

				$members[ $row['member_id'] ] = $row;
			}
			
			return $this->formIPAddresses( $this->registry->getClass('output')->getTemplate('ucp')->membersModIPFormMembers($pages, $members) );
		}
		else
		{
			// Find posts then!
			if ($exactMatch == 0)
			{
				$sql = "p.ip_address LIKE '$finalIPString'";
			}
			else
			{
				$sql = "p.ip_address='$finalIPString'";
			}
			
			// Get forums we're allowed to view
			$aforum = array();
			
			foreach( $this->registry->getClass('class_forums')->forum_by_id as $data )
			{
				if ( IPSMember::checkPermissions('read', $data['id'] ) == TRUE )
				{
					$aforum[] = $data['id'];
				}
			}
			
			if ( count($aforum) < 1)
			{
				$this->formIPAddresses( $this->registry->output->getTemplate('ucp')->inlineModIPMessage( $this->lang->words['cp_no_matches'] ) );
				return;
			}
			
			$the_forums	= implode( ",", $aforum);
			$st			= intval($this->request['st']);
			
			$count = $this->DB->buildAndFetch( array(	'select'	=> 'COUNT(*) as total',
														'from'		=> array( 'posts' => 'p' ),
														'where'		=> "t.forum_id IN({$the_forums}) AND {$sql}",
														'add_join'	=> array(
																			array(
																					'from'		=> array( 'topics' => 't' ),
																					'where'		=> 't.tid=p.topic_id',
																					'type'		=> 'left'
																				),
																			)
											)		);
											
			//-----------------------------------------
			// Do we have any results?
			//-----------------------------------------
			
			if ( !$count['total'] )
			{
				return $this->formIPAddresses( $this->registry->output->getTemplate('ucp')->inlineModIPMessage( $this->lang->words['cp_no_matches'] ) );
			}
			
			//-----------------------------------------
			// Get forum class as we'll need it
			//-----------------------------------------
			
			$classToLoad    = IPSLib::loadLibrary( IPSLib::getAppDir('forums') . '/app_class_forums.php', 'app_class_forums', 'forums' );
			$appclassforums = new $classToLoad( $this->registry );
						
	 		//-----------------------------------------
	 		// Pages
	 		//-----------------------------------------
	 		
	 		$pageLinks = $this->registry->getClass('output')->generatePagination( array( 'totalItems'        => $count['total'],
															   							 'itemsPerPage'      => 10,
																						 'currentStartValue' => $st,
																						 'baseUrl'           => "app=core&amp;module=usercp&amp;tab=members&amp;area=mod_ipaddress&amp;do=custom&amp;ip=" . $this->request['ip'] . "&amp;iptool=posts",
																				 )		);
			
			$this->DB->build( array(	'select'	=> 'p.*',
										'from'		=> array( 'posts' => 'p' ),
										'where'		=> "t.forum_id IN({$the_forums}) AND {$sql}",
										'limit'		=> array( $st,10 ),
										'order'		=> 'pid DESC',
										'add_join'	=> array(
															array(
																	'select'	=> 't.forum_id',
																	'from'		=> array( 'topics' => 't' ),
																	'where'		=> 't.tid=p.topic_id',
																	'type'		=> 'left'
																),
															array(
																	'select'	=> 'm.*',
																	'from'		=> array( 'members' => 'm' ),
																	'where'		=> 'm.member_id=p.author_id',
																	'type'		=> 'left'
																),
															array(
																	'select'	=> 'pp.*',
																	'from'		=> array( 'profile_portal' => 'pp' ),
																	'where'		=> 'pp.pp_member_id=m.member_id',
																	'type'		=> 'left'
																),
															array(
																	'select'	=> 'pf.*',
																	'from'		=> array( 'pfields_content' => 'pf' ),
																	'where'		=> 'pf.member_id=m.member_id',
																	'type'		=> 'left'
																),
															)
											)		);
			$outer	= $this->DB->execute();

			$results  = array();
			
			while ($row = $this->DB->fetch($outer) )
			{
				//-----------------------------------------
				// Parse the member
				//-----------------------------------------
				
				$row	= IPSMember::buildDisplayData( $row, array( 'customFields', 'signature', 'avatar', 'warn' ) );
				
				//-----------------------------------------
				// Parse the post
				//-----------------------------------------
		
				IPSText::getTextClass( 'bbcode' )->parse_smilies			= $row['use_emo'];
				IPSText::getTextClass( 'bbcode' )->parse_html				= ( $this->registry->class_forums->allForums[ $row['forum_id'] ]['use_html'] and $this->caches['group_cache'][ $row['member_group_id'] ]['g_dohtml'] and $row['post_htmlstate'] ) ? 1 : 0;
				IPSText::getTextClass( 'bbcode' )->parse_nl2br				= $row['post_htmlstate'] == 2 ? 1 : 0;
				IPSText::getTextClass( 'bbcode' )->parse_bbcode				= $this->registry->class_forums->allForums[ $row['forum_id'] ]['use_ibc'];
				IPSText::getTextClass( 'bbcode' )->parsing_section			= 'topics';
				IPSText::getTextClass( 'bbcode' )->parsing_mgroup			= $row['member_group_id'];
				IPSText::getTextClass( 'bbcode' )->parsing_mgroup_others	= $row['mgroup_others'];	
			
				$row['post']	= IPSText::getTextClass( 'bbcode' )->preDisplayParse( $row['post'] );
				
				$results[] = $row;
			}

			return $this->formIPAddresses( $this->registry->getClass('output')->getTemplate('ucp')->membersModIPFormPosts( $count['total'], $pageLinks, $results ) );
				
			return TRUE;
		}
	}
	
	/**
	 * Custom event: Remove ignored user
	 *
	 * @author	Matt Mecham
	 * @return	string		Processed HTML
	 */
	public function customEvent_removeIgnoredUser()
	{
		//-----------------------------------------
		// INIT
		//-----------------------------------------
		
		$removeID = intval( $this->request['id'] );
		
		$this->DB->delete( 'ignored_users', 'ignore_owner_id=' . $this->memberData['member_id'] . ' AND ignore_ignore_id=' . $removeID );
 		
		/* Rebuild cache */
		IPSMember::rebuildIgnoredUsersCache( $this->memberData );
		
		$this->registry->getClass('output')->silentRedirect( $this->settings['base_url']."app=core&amp;module=usercp&amp;tab=members&amp;area=ignoredusers&amp;do=show" );
	}
	
	/**
	 * Custom event: Remove avatar
	 *
	 * @author	Matt Mecham
	 * @return	string		Processed HTML
	 */
	public function customEvent_removeAvatar()
	{
		//-----------------------------------------
		// Check secure hash...
		//-----------------------------------------
		
		if ( $this->request['secure_key'] != $this->member->form_hash )
		{
			$this->registry->output->showError( 'authorization_error', 100, true, null, 403 );
		}
		
		try
		{
			IPSMember::getFunction()->removeAvatar( $this->memberData['member_id'] );
		
			$this->registry->getClass('output')->silentRedirect( $this->settings['base_url']."app=core&amp;module=usercp&amp;tab=members&amp;area=avatar&amp;do=show" );
		}
		catch( Exception $error )
		{
			switch ( $error->getMessage() )
			{
				case 'NO_MEMBER_ID':
					return array( 0 => $this->lang->words['removeav_notexist'] );
				break;
				case 'NO_PERMISSION':
					return array( 0 => $this->lang->words['removeav_noperm'] );
				break;
			}
		}
	}
	
	/**
	 * Custom event: Remove photo
	 *
	 * @author	Matt Mecham
	 * @return	string		Processed HTML
	 */
	function customEvent_removePhoto()
	{
		//-----------------------------------------
		// Check secure hash...
		//-----------------------------------------
		
		if ( $this->request['secure_key'] != $this->member->form_hash )
		{
			$this->registry->output->showError( 'authorization_error', 100, true, null, 403 );
		}
		
		IPSMember::getFunction()->removeUploadedPhotos( $this->memberData['member_id'] );
		
		$bwOptions = IPSBWOptions::thaw( $this->memberData['fb_bwoptions'], 'facebook' );
		$bwOptions['fbc_s_pic']	= 0;
		
		IPSMember::save( $this->memberData['member_id'], array( 'extendedProfile' => array( 'pp_main_photo'		=> '',
											  				   	 							'pp_main_width'		=> 0,
																							'pp_main_height'	=> 0,
																							'pp_thumb_photo'	=> '',
																							'pp_thumb_width'	=> 0,
																							'pp_thumb_height'	=> 0,
																							'fb_photo'			=> '',
																							'fb_photo_thumb'	=> '',
																							'fb_bwoptions'		=> IPSBWOptions::freeze( $bwOptions, 'facebook' )
																						) ) );
		
		$this->registry->getClass('output')->silentRedirect( $this->settings['base_url']."app=core&amp;module=usercp&amp;tab=members&amp;area=photo&amp;do=show" );
	}

	/**
	 * UserCP Form Show
	 *
	 * @author	Matt Mecham
	 * @param	string		Current area as defined by 'get_links'
	 * @param	array		Any errors
	 * @return	string		Processed HTML
	 */
	public function showForm( $current_area, $errors=array() )
	{
		//-----------------------------------------
		// Where to go, what to see?
		//-----------------------------------------

		switch( $current_area )
		{
			default:
			case 'profileinfo':
				return $this->formProfileInfo();
			break;
			case 'aboutme':
				return $this->formAboutMe();
			break;
			case 'signature':
				return $this->formSignature();
			break;
			case 'photo':
				return $this->formPhoto();
			break;
			case 'avatar':
				return $this->formAvatar();
			break;
			case 'ignoredusers':
				return $this->formIgnoredUsers();
			break;
			case 'mod_ipaddress':
				return $this->formIPAddresses();
			break;
			case 'mod_member':
				return $this->formModMember();
			break;
			case 'facebook':
				return $this->formFacebook();
			break;
			case 'twitter':
				return $this->formTwitter();
			break;
			case 'customize':
				return $this->formCustomize();
			break;
		}
	}
	
	/**
	 * Show the customization form
	 *
	 * @author	Matt Mecham
	 * @param	string		Any inline message to show
	 * @return	string		Processed HTML
	 */
	public function formCustomize( $inlineMsg='' )
	{
		/* Allow uploads */
		$this->uploadFormMax = 10000 * 1024;
		
		if ( ! $this->memberData['gbw_allow_customization'] OR $this->memberData['bw_disable_customization'] )
		{		
			$this->registry->getClass('output')->showError( 'no_permission', 1005.5 );
		}
		
		/* Grab current options */
		$options = unserialize( $this->memberData['pp_customization'] );
		$options = is_array( $options ) ? $options : array();
		
		/* Build input */
		foreach( $options as $k => $v )
		{
			$input[ $k ] = ( $this->request[ $k ] ) ? $this->request[ $k ] : $v;
		}
		
		/* Figure out preview URL */
		if ( $options['type'] == 'url' AND $options['bg_url'] )
		{
			$input['_preview'] = $options['bg_url'];
		}
		else if ( $options['type'] == 'upload' AND $options['bg_url'] )
		{
			$input['_preview'] = $this->settings['upload_url'] . '/' . $options['bg_url'];
			$input['bg_url']   = '';
		}
		
		/* Show form */
		return $this->registry->getClass('output')->getTemplate('ucp')->membersProfileCustomize( $options, $input, $inlineMsg );
	}
	
	/**
	 * Show the twitter form
	 *
	 * @author	Matt Mecham
	 * @param	string		Any inline message to show
	 * @return	string		Processed HTML
	 */
	public function formTwitter( $inlineMsg='' )
	{
		//-----------------------------------------
		// INIT
		//-----------------------------------------
		
		if( !IPSLib::twitter_enabled() )
		{		
			$this->registry->getClass('output')->showError( 'twitter_disabled', 1005.1 );
		}
		
		//-----------------------------------------
		// Twitter user logged in?
		//-----------------------------------------
		
		require_once( IPS_ROOT_PATH . 'sources/classes/twitter/connect.php' );
		$twitter = new twitter_connect( $this->registry, $this->memberData['twitter_token'], $this->memberData['twitter_secret'] );
		
		//-----------------------------------------
		// Thaw bitfield options
		//-----------------------------------------
		
		$bwOptions = IPSBWOptions::thaw( $this->memberData['tc_bwoptions'], 'twitter' );
		
		//-----------------------------------------
		// Merge..
		//-----------------------------------------
		
		if ( is_array( $bwOptions ) )
		{
			foreach( $bwOptions as $k => $v )
			{
				$this->memberData[ $k ] = $v;
			}
		}
		
		if( ! $twitter->isConnected() )
		{
			$this->hide_form_and_save_button = 1;
		}
		
		$userData = $twitter->fetchUserData();
		
		if ( isset( $userData['status']['text'] ) )
		{	
			if ( IPS_DOC_CHAR_SET != 'UTF-8' )
			{
				$userData['status']['text'] = IPSText::utf8ToEntities( $userData['status']['text'] );
			}
		}
		
		return $this->registry->getClass('output')->getTemplate('ucp')->membersTwitterConnect( $twitter->isConnected(), $userData );
	}
	
	


	/**
	 * Show the member form
	 *
	 * @author	Matt Mecham
	 * @param	string		Any inline message to show
	 * @return	string		Processed HTML
	 */
	public function formFacebook( $inlineMsg='' )
	{
		//-----------------------------------------
		// INIT
		//-----------------------------------------
		
		if( !IPSLib::fbc_enabled() )
		{		
			$this->registry->getClass('output')->showError( 'fbc_disabled', 1005.2 );
		}
		
		//-----------------------------------------
		// FB user logged in?
		//-----------------------------------------
		
		require_once( IPS_ROOT_PATH . 'sources/classes/facebook/connect.php' );
		$facebook = new facebook_connect( $this->registry );
		
		/* Now get the linked user */
		$linkedMemberData = IPSMember::load( intval($this->memberData['fb_uid']), 'all', 'fb_uid' );
		
		$userData = $facebook->fetchUserData();
						
		/* Email */
		$perms['email']          = $facebook->fetchHasAppPermission( 'email' );
		
		/* Publish Stream */
		$perms['publish_stream'] = $facebook->fetchHasAppPermission( 'publish_stream' );
		
		/* Read stream */
		$perms['read_stream']    = $facebook->fetchHasAppPermission( 'read_stream' );
		
		/* Offline access */
		$perms['offline_access'] = $facebook->fetchHasAppPermission( 'offline_access' );
		
		
		//-----------------------------------------
		// Thaw bitfield options
		//-----------------------------------------
		
		$bwOptions = IPSBWOptions::thaw( $this->memberData['fb_bwoptions'], 'facebook' );
		
		//-----------------------------------------
		// Merge..
		//-----------------------------------------
		
		if ( is_array( $bwOptions ) )
		{
			foreach( $bwOptions as $k => $v )
			{
				$this->memberData[ $k ] = $v;
			}
		}
		
		//-----------------------------------------
		// Able to update status?
		//-----------------------------------------

		$classToLoad = IPSLib::loadLibrary( IPS_ROOT_PATH . 'sources/classes/member/status.php', 'memberStatus' );
		$this->registry->setClass( 'memberStatus', new $classToLoad( $this->registry ) );
		
		$this->registry->memberStatus->setAuthor( $this->memberData );
		$this->memberData['can_updated_status'] = $this->registry->memberStatus->canCreate();
		
		if( ! is_array( $userData ) )
		{
			$this->hide_form_and_save_button = 1;
		}
		
		$_updates = $facebook->fetchUserTimeline( $userData['id'], 0, true );
					
		/* Got any? */
		if ( count( $_updates ) )
		{
			$update = array_shift( $_updates );
			
			if ( count( $update ) AND is_array( $update ) )
			{
				$userData['status'] = $update;
			}
		}
		
		if ( is_array( $userData ) AND $userData['status']['message'] AND IPS_DOC_CHAR_SET != 'UTF-8' )
		{
			$userData['status']['message'] = IPSText::utf8ToEntities( $userData['status']['message'] );
		}
		
		return $this->registry->getClass('output')->getTemplate('ucp')->membersFacebookConnect( intval($this->memberData['fb_uid']), $userData, $linkedMemberData, $perms );
	}
	
	/**
	 * Show the member form
	 *
	 * @author	Matt Mecham
	 * @param	string		Any inline message to show
	 * @return	string		Processed HTML
	 */
	public function formModMember( $inlineMsg='' )
	{
		//-----------------------------------------
		// Can we see this?
		//-----------------------------------------
		
		if ( ! $this->memberData['g_is_supmod'] )
		{
			$this->registry->getClass('output')->showError( 'members_tool_supmod', 2021, null, null, 403 );
		}
		
		//-----------------------------------------
		// Remove standard form stuff
		//-----------------------------------------
		
		$this->hide_form_and_save_button = 1;
			
		return $this->registry->getClass('output')->getTemplate('ucp')->membersModFindUser( $inlineMsg );
	}
	
	/**
	 * Show the IP Address form
	 *
	 * @author	Matt Mecham
	 * @param	string		Any inline message to show
	 * @return	string		Processed HTML
	 */
	public function formIPAddresses( $inlineMsg='' )
	{
		//-----------------------------------------
		// Can we see this?
		//-----------------------------------------
		
		if ( ! $this->memberData['g_is_supmod'] )
 		{
 			$this->registry->getClass('output')->showError( 'members_tool_supmod', 2022, null, null, 403 );
 		}

		//-----------------------------------------
		// Remove standard form stuff
		//-----------------------------------------
		
		$this->hide_form_and_save_button = 1;
 		
 		return $this->registry->getClass('output')->getTemplate('ucp')->membersModIPForm( $this->request['ip'], $inlineMsg );
	}
	
	/**
	 * Show the ignored users
	 *
	 * @author	Matt Mecham
	 * @return	string		Processed HTML
	 */
	public function formIgnoredUsers()
	{
		//-----------------------------------------
		// INIT
		//-----------------------------------------
		
		$final_users  = array();
 		$temp_users   = array();
 		$uid          = intval( $this->request['uid'] );
		$ignoredUsers = array();
		
 		//-----------------------------------------
 		// Do we have incoming?
 		//-----------------------------------------
 		
 		if ( $uid )
 		{
 			$newmem = IPSMember::load( $uid );

 			$this->request[ 'newbox_1'] =  $newmem['members_display_name'] ;
 		}
 		
 		//-----------------------------------------
 		// Get ignored users
 		//-----------------------------------------
 		
		$this->DB->build( array( 'select' => '*', 'from' => 'ignored_users', 'where' => 'ignore_owner_id=' . $this->memberData['member_id'] ) );
 		$this->DB->execute();

		while( $row = $this->DB->fetch() )
		{
			$ignoredUsers[ $row['ignore_ignore_id'] ] = $row;
		}
 		
 		//-----------------------------------------
 		// Get members and check to see if they've
 		// since been moved into a group that cannot
 		// be ignored
 		//-----------------------------------------
 		
 		foreach( $ignoredUsers as $_id => $data )
 		{
 			if ( intval($_id) )
 			{
 				$temp_users[] = $_id;
 			}
 		}
 		
 		if ( count($temp_users) )
 		{
 			$members = IPSMember::load( $temp_users, 'all' );
		
 			foreach( $members as $m )
 			{
 				$m['g_title'] = IPSLib::makeNameFormatted( $this->caches['group_cache'][ $m['member_group_id'] ]['g_title'], $m['member_group_id'] );
 				
 				$final_users[ $m['member_id'] ] = IPSMember::buildDisplayData( $m );
				$final_users[ $m['member_id'] ]['ignoreData'] = $ignoredUsers[ $m['member_id'] ];
 			}
 		}

 		$this->request['newbox_1'] = $this->request['newbox_1'] ? $this->request['newbox_1'] : '';
 		
 		return $this->registry->getClass('output')->getTemplate('ucp')->membersIgnoredUsersForm( $final_users );
	}

	/**
	 * Show the avatar page
	 *
	 * @author	Matt Mecham
	 * @return	string		Processed HTML
	 */
	public function formAvatar()
	{
		//-----------------------------------------
		// INIT
		//-----------------------------------------
		
		$member = IPSMember::load( $this->memberData['member_id'], 'extendedProfile,customFields,groups' );
		
		//-----------------------------------------
		// Check to make sure that we can edit profiles..
		//-----------------------------------------
		
		if ( ! $member['g_edit_profile'] )
		{
			$this->registry->getClass('output')->showError( 'members_profile_disabled', 1021, null, null, 403 );
		}
		
		if( ! $this->settings['avatars_on'] )
		{
			$this->registry->getClass('output')->showError( 'members_profile_disabled', 1030, null, null, 403 );
		}
			 	
		//-----------------------------------------
 		// Organise the dimensions
 		//-----------------------------------------
 		
 		if ( strpos( $this->memberData['avatar_size'], "x" ) )
 		{
 			list( $this->settings['currentWidth'], $this->settings['currentHeight'] ) = explode( "x", strtolower($member['avatar_size'] ) );
		}
		
 		list( $this->settings['maxWidth'], $this->settings['maxHeight'] ) = explode( "x", strtolower($this->settings['avatar_dims'] ) );
 		list( $w, $h ) = explode ( "x", strtolower($this->settings['avatar_def']) );
 	
 		//-----------------------------------------
 		// Get the avatar gallery
 		//-----------------------------------------
 		
		$av_categories  = array_merge( array( 0 => array( 0, '&lt; ' . $this->lang->words['av_root'] . ' &gt;' ) ), IPSMember::getFunction()->getHostedAvatarCategories() );
 	
 		//-----------------------------------------
 		// Get the avatar gallery selected
 		//-----------------------------------------
 		
 		$url_avatar = "http://";
 		 		
 		$avatar_type = "na";
 		
 		if ( ($member['avatar_location'] != "") and ($member['avatar_location'] != "noavatar") )
 		{
 			if ( ! $member['avatar_type'] )
 			{
				if ( preg_match( "/^upload:/", $member['avatar'] ) )
				{
					$avatar_type = "upload";
				}
				else if ( ! preg_match( "/^http/i", $member['avatar'] ) )
				{
					$avatar_type = "local";
				}
				else
				{
					$url_avatar  = $member['avatar'];
					$avatar_type = "url";
				}
			}
			else
			{
				switch ( $member['avatar_type'] )
				{
					case 'upload':
						$avatar_type = 'upload';
					break;
					case 'url':
						$avatar_type = 'url';
						$url_avatar  = $member['avatar_location'];
					break;
					case 'gravatar':
						$avatar_type = 'gravatar';
					break;
					
					default:
						$avatar_type = 'local';
					break;
				}
			}
 		}
 		
 		//-----------------------------------------
 		// Rest of the form..
 		//-----------------------------------------
 		
 		if ( $member['g_avatar_upload'] == 1 )
 		{
 			$this->uploadFormMax = 9000000;
		}
		
		//-----------------------------------------
		// Force a form action?
		//-----------------------------------------
		
		$is_reset = 0;
		
		if ( $this->settings['upload_domain'] )
		{
			$is_reset = 1;
			$original = $this->settings['base_url'];
			
			if( $this->member->session_type == 'cookie' )
			{
				$this->settings['base_url'] = $this->settings['upload_domain'] . '/index.' . $this->settings['php_ext'].'?';
			}
			else
			{
				$this->settings['base_url'] = $this->settings['upload_domain'] . '/index.' . $this->settings['php_ext'].'?s='.$this->member->session_id .'&amp;';
			}
		}
		
		//-----------------------------------------
 		// If yes, show little thingy at top
 		//-----------------------------------------

 		$this->lang->words['av_allowed_files'] = sprintf($this->lang->words['av_allowed_files'], implode (' .', explode( "|", $this->settings['avatar_ext'] ) ) );

 		$return = $this->registry->getClass('output')->getTemplate('ucp')->memberAvatarForm( array( 'member'				=> $member,
																									'avatar_categories'		=> $av_categories,
																									'current_url_avatar'	=> $url_avatar,
																									'current_avatar_image'	=> IPSMember::buildAvatar( $member, 1, 1 ),
																									'current_avatar_type'	=> $this->lang->words['av_t_'.$avatar_type],
																									'current_avatar_dims'	=> $avatar_type != 'gravatar' ? $member['avatar_size'] == "x" ? "" : $member['avatar_size'] : ''
																							)		);
		
		//-----------------------------------------
		// Reset forced form action?
		//-----------------------------------------
		
		if ( $is_reset )
		{
			$this->settings['base_url'] = $original;
		}
		
		return $return;
	}
	
	/**
	 * Show the photo page
	 *
	 * @author	Matt Mecham
	 * @return	string		Processed HTML
	 */
	public function formPhoto()
	{
		//-----------------------------------------
		// INIT
		//-----------------------------------------
		
		list($p_max, $p_width, $p_height) = explode( ":", $this->memberData['g_photo_max_vars'] );
		$p_w       = "";
		$p_h       = "";
		$cur_photo = "";
		$rand      = urlencode( microtime() );
		
		//-----------------------------------------
		// Load all data
		//-----------------------------------------
		
		$member = IPSMember::buildDisplayData( IPSMember::load( $this->memberData['member_id'], 'all' ) );
		
		//-----------------------------------------
		// Check to make sure that we can edit profiles..
		//-----------------------------------------
		
		if ( ! $this->memberData['g_edit_profile'] )
		{
			$this->registry->getClass('output')->showError( 'members_profile_disabled', 1022, null, null, 403 );
		}
		
		//-----------------------------------------
		// Not allowed a photo
		//-----------------------------------------
		
 		if ( $this->memberData['g_photo_max_vars'] == "" or $this->memberData['g_photo_max_vars'] == "::" OR !$p_max )
 		{
 			$this->registry->getClass('output')->showError( 'members_profile_disabled', 1023, null, null, 403 );
 		}
 		
 		//-----------------------------------------
 		// SET DIMENSIONS
 		//-----------------------------------------
 		
 		$this->lang->words['pph_max']  = sprintf( $this->lang->words['pph_max'], $p_max );
 		$this->lang->words['pph_max'] .= sprintf( $this->lang->words['pph_max2'], $p_width, $p_height );
 		
 		$show_size = "(".$member['pp_main_width'] .' x ' . $member['pp_main_height'].")";
 		
 		//-----------------------------------------
 		// TYPE?
 		//-----------------------------------------
 		
 		if ( $member['pp_main_photo'] )
 		{
 			$cur_photo = "<img src='".$member['pp_main_photo'].'?__rand='. $rand . "' width='". $member['pp_main_width'] ."' height='". $member['pp_main_height'] ."' alt='" . $this->lang->words['pph_title'] . "' />";
 		}
 		
		//-----------------------------------------
		// Force a form action?
		//-----------------------------------------
		
		if ( $this->settings['upload_domain'] )
		{
			if( $member->session_type == 'cookie' )
			{
				$this->settings[ 'base_url'] =  $this->settings['upload_domain'] . '/index.' . $this->settings['php_ext'].'?' ;
			}
			else
			{
				$this->settings[ 'base_url'] =  $this->settings['upload_domain'] . '/index.' . $this->settings['php_ext'].'?s='.$this->member->session_id .'&amp;' ;
			}
		}
		 		
 		//-----------------------------------------
 		// SHOW THE FORM
 		//-----------------------------------------
 		
		$this->uploadFormMax = $p_max*1024;
		
 		return $this->registry->getClass('output')->getTemplate('ucp')->membersPhotoForm( $cur_photo, $show_size );
	}
	
	/**
	 * Show the signature page
	 *
	 * @author	Matt Mecham
	 * @return	string		Processed HTML
	 */
	public function formSignature()
	{
		//-----------------------------------------
		// Check to make sure that we can edit profiles..
		//-----------------------------------------
		
		$sig_restrictions = explode( ':', $this->memberData['g_signature_limits'] );
		
		if ( ! $this->memberData['g_edit_profile'] OR ( $sig_restrictions[0] AND ! $this->memberData['g_sig_unit'] ) )
		{
			$this->registry->getClass('output')->showError( 'members_profile_disabled', 1024, null, null, 403 );
		}
		
		/* Signature Limits */
	 	if ( $sig_restrictions[0] AND $this->memberData['g_sig_unit'] )
		{
			if ( $this->memberData['gbw_sig_unit_type'] )
			{
				/* days */
				if ( $this->memberData['joined'] > ( time() - ( 86400 * $this->memberData['g_sig_unit'] ) ) )
				{
					$this->hide_form_and_save_button = 1;
					$form['_noPerm'] = sprintf( $this->lang->words['sig_group_restrict_date'], $this->lang->getDate( $this->memberData['joined'] + ( 86400 * $this->memberData['g_sig_unit'] ), 'long' ) );
				}
			}
			else
			{
				/* Posts */
				if ( $this->memberData['posts'] < $this->memberData['g_sig_unit'] )
				{
					$this->hide_form_and_save_button = 1;
					$form['_noPerm'] = sprintf( $this->lang->words['sig_group_restrict_posts'], $this->memberData['g_sig_unit'] - $this->memberData['posts'] );
				}
			}
			
			if( $form['_noPerm'] )
			{
				return $this->registry->getClass('output')->getTemplate('ucp')->membersSignatureFormError( $form );
			}
		}
	
 		//-----------------------------------------
 		// Set max length
 		//-----------------------------------------
 		
 		$this->lang->words['the_max_length'] = $this->settings['max_sig_length'] ? $this->settings['max_sig_length'] : 0;
		
 		$current_sig	= '';
 		$t_sig			= '';
 		
		//-----------------------------------------
		// Unconvert for editing
		//-----------------------------------------

		if( $this->memberData['signature'] )
		{
			if ( IPSText::getTextClass( 'editor' )->method == 'rte' )
			{
				$t_sig = IPSText::getTextClass( 'bbcode' )->convertForRTE( $this->memberData['signature'] );
			}
			else
			{
				IPSText::getTextClass( 'bbcode' )->parse_smilies	= 0;
				IPSText::getTextClass( 'bbcode' )->parse_html		= $this->settings['sig_allow_html'];
				IPSText::getTextClass( 'bbcode' )->parse_bbcode		= $this->settings['sig_allow_ibc'];
				IPSText::getTextClass( 'bbcode' )->parsing_section	= 'signatures';
				
				$t_sig = IPSText::getTextClass( 'bbcode' )->preEditParse( $this->memberData['signature'] );
			}
		}
		
		$this->lang->words['override']    = 1;

		//-----------------------------------------
		// Show
		//-----------------------------------------
		
		if( $this->memberData['signature'] )
		{
			IPSText::getTextClass('bbcode')->parse_bbcode				= $this->settings[ 'sig_allow_ibc' ];
			IPSText::getTextClass('bbcode')->parse_smilies				= 0;
			IPSText::getTextClass('bbcode')->parse_html					= $this->settings[ 'sig_allow_html' ];
			IPSText::getTextClass('bbcode')->parse_nl2br				= 1;
			IPSText::getTextClass('bbcode')->parsing_section			= 'signatures';
			IPSText::getTextClass( 'bbcode' )->parsing_mgroup			= $this->memberData['member_group_id'];
			IPSText::getTextClass( 'bbcode' )->parsing_mgroup_others	= $this->memberData['mgroup_others'];
			
			$current_sig	= IPSText::getTextClass('bbcode')->preDisplayParse( $this->memberData['signature'] );
		}
		
		IPSText::getTextClass( 'editor' )->remove_emoticons = 1;

		return $this->registry->getClass('output')->getTemplate('ucp')->membersSignatureForm( $current_sig, IPSText::getTextClass( 'editor' )->showEditor( $t_sig, 'Post' ), $sig_restrictions );
	}
	
	/**
	 * Show the about me page
	 *
	 * @author	Matt Mecham
	 * @return	string		Processed HTML
	 */
	public function formAboutMe()
	{
		//-----------------------------------------
		// Check to make sure that we can edit profiles..
		//-----------------------------------------
		
		if ( ! $this->memberData['g_edit_profile'] )
		{
			$this->registry->getClass('output')->showError( 'members_profile_disabled', 1025, null, null, 403 );
		}
			 	
	 	$aboutme = $this->DB->buildAndFetch( array( 'select' => 'pp_about_me', 'from' => 'profile_portal', 'where' => 'pp_member_id=' . $this->memberData['member_id'] ) );
	 	
		//-----------------------------------------
		// Unconvert for editing
		//-----------------------------------------
		
		$am_text	='';
		
		if( $aboutme['pp_about_me'] )
		{
			if ( IPSText::getTextClass( 'editor' )->method == 'rte' )
			{
				$am_text = IPSText::getTextClass( 'bbcode' )->convertForRTE( $aboutme['pp_about_me'] );
			}
			else
			{
				IPSText::getTextClass( 'bbcode' )->parse_html        = intval($this->settings['aboutme_html']);
				IPSText::getTextClass( 'bbcode' )->parse_nl2br       = 1;
				IPSText::getTextClass( 'bbcode' )->parse_smilies     = $this->settings['aboutme_emoticons'];
				IPSText::getTextClass( 'bbcode' )->parse_bbcode      = $this->settings['aboutme_bbcode'];
				IPSText::getTextClass( 'bbcode' )->parsing_section   = 'aboutme';
				
				$am_text = IPSText::getTextClass( 'bbcode' )->preEditParse( $aboutme['pp_about_me'] );
			}
		}
		
		//-----------------------------------------
 		// Format for preview
 		//-----------------------------------------
 		
 		if( $aboutme['pp_about_me'] )
 		{
			IPSText::getTextClass( 'bbcode' )->parse_html				= intval($this->settings['aboutme_html']);
			IPSText::getTextClass( 'bbcode' )->parse_nl2br				= 1;
			IPSText::getTextClass( 'bbcode' )->parse_smilies			= $this->settings['aboutme_emoticons'];
			IPSText::getTextClass( 'bbcode' )->parse_bbcode				= $this->settings['aboutme_bbcode'];
			IPSText::getTextClass( 'bbcode' )->parsing_section	 		= 'aboutme';
			IPSText::getTextClass( 'bbcode' )->parsing_mgroup			= $this->memberData['member_group_id'];
			IPSText::getTextClass( 'bbcode' )->parsing_mgroup_others	= $this->memberData['mgroup_others'];
			
			$aboutme['pp_about_me'] = IPSText::getTextClass( 'bbcode' )->preDisplayParse( $aboutme['pp_about_me'] );
		}

		//-----------------------------------------
		// Show
		//-----------------------------------------
		
		return $this->registry->getClass('output')->getTemplate('ucp')->membersAboutMeForm( $aboutme['pp_about_me'], IPSText::getTextClass( 'editor' )->showEditor( $am_text, 'Post' ) );
	}
	
	/**
	 * Show the profile information
	 *
	 * @author	Matt Mecham
	 * @return	string		Processed HTML
	 */
	public function formProfileInfo()
	{
		/* Load Lang File */
		$this->registry->class_localization->loadLanguageFile( array( 'public_profile' ), 'members' );
		
		/* INIT */
	 	$required_output = "";
		$optional_output = "";
		
		/* Permission Check */
		if( ! $this->memberData['g_edit_profile'] )
		{
			$this->registry->getClass('output')->showError( 'members_profile_disabled', 1026, null, null, 403 );
		}
		
		/* Format the birthday drop boxes.. */
		$date = getdate();
		
		$day  = array();
		$mon  = array();
		$year = array();
		
		/* Build the day options */
		$day[] = array( '0', '--' );
		for ( $i = 1 ; $i < 32 ; $i++ )
		{
			$day[] = array( $i, $i );			
		}
		
		/* Build the month options */
		$mon[] = array( '0', '--' );
		for( $i = 1 ; $i < 13 ; $i++ )
		{
			$mon[] = array( $i, $this->lang->words['M_' . $i ] );
		}
		
		/* Build the years options */
		$i = $date['year'] - 1;
		$j = $date['year'] - 100;
		
		$year[] = array( '0', '--' );
		for( $i ; $j < $i ; $i-- )
		{
			$year[] = array( $i, $i );
		}
	
		/* Custom Fields */
		$classToLoad = IPSLib::loadLibrary( IPS_ROOT_PATH . 'sources/classes/customfields/profileFields.php', 'customProfileFields' );
		$fields      = new $classToLoad();
		
		$fields->member_data = $this->member->fetchMemberData();
		$fields->initData( 'edit' );
		$fields->parseToEdit();
		
		if ( count( $fields->out_fields ) )
		{
			foreach( $fields->out_fields as $id => $data )
	    	{
	    		if ( $fields->cache_data[ $id ]['pf_not_null'] == 1 )
				{
					$ftype = 'required_output';
				}
				else
				{
					$ftype = 'optional_output';
				}

				${$ftype} .= $this->registry->getClass('output')->getTemplate('ucp')->field_entry( $fields->field_names[ $id ], $fields->field_desc[ $id ], $data, $id );
	    	}
		}

		/* Build and return the form */
		$template = $this->registry->getClass('output')->getTemplate('ucp')->membersProfileForm( $required_output, $optional_output, $day, $mon, $year );

		return $template;
	}
	
	/**
	 * UserCP Form Check
	 *
	 * @author	Matt Mecham
	 * @param	string		Current area as defined by 'get_links'
	 * @return	string		Processed HTML
	 */
	public function saveForm( $current_area )
	{
		//-----------------------------------------
		// Where to go, what to see?
		//-----------------------------------------

		switch( $current_area )
		{
			default:
			case 'profileinfo':
				return $this->saveProfileInfo();
			break;
			case 'aboutme':
				return $this->saveAboutMe();
			break;
			case 'signature':
				return $this->saveSignature();
			break;
			case 'photo':
				return $this->savePhoto();
			break;
			case 'avatar':
				return $this->saveAvatar();
			break;
			case 'ignoredusers':
				return $this->saveIgnoredUsers();
			break;
			case 'facebook':
				return $this->saveFacebook();
			break;
			case 'twitter':
				return $this->saveTwitter();
			break;
			case 'customize':
				return $this->saveCustomize();
			break;
		}
	}
	
	/**
	 * UserCP Save Form: Customize
	 *
	 * @return	array	Errors
	 */
	public function saveCustomize()
	{
		/* Init */
		$errors   = array();
		$custom   = array();
		$bg_nix   = trim( $this->request['bg_nix'] );
		$kill_img = trim( $this->request['kill_img'] );
		$bg_url   = trim( $this->request['bg_url'] );
		$bg_tile  = intval( $this->request['bg_tile'] );
		$bg_color = trim( str_replace( '#', '', $this->request['bg_color'] ) );
		
		/* reset custom */
		$custom   = unserialize( $this->memberData['pp_customization'] );
		
		/* Bug #21578 */
		if( ! $bg_color && $custom['bg_color'] )
		{
			$bg_color = $custom['bg_color'];
		}
		
		/* Delete all? */
		if ( $bg_nix )
		{
			/* reset array */
			$custom = array( 'bg_url' => '', 'type' => '', 'bg_color' => '', 'bg_tile' => '' );
			
			/* remove bg images */
			IPSMember::getFunction()->removeUploadedBackgroundImages( $this->memberData['member_id'] );
		}
		/* Delete imgs only */
		else if ( $kill_img )
		{
			/* reset array */
			$custom = array( 'bg_url' => '', 'type' => ( $bg_color ) ? 'color' : '' );
			
			/* remove bg images */
			IPSMember::getFunction()->removeUploadedBackgroundImages( $this->memberData['member_id'] );
		}
		else
		{
			if ( $bg_url AND $this->memberData['gbw_allow_url_bgimage'] )
			{
				/* Check */
				if ( ! stristr( $bg_url, 'http://' ) OR preg_match( '#\(\*#', $bg_url ) )
				{
					return array( 0 => $this->lang->words['pp_bgimg_url_bad'] );
				}
				
				$image_extension = strtolower( pathinfo( $bg_url, PATHINFO_EXTENSION ) );
				
				if( ! in_array( $image_extension, array( 'png', 'jpg', 'gif', 'jpeg'  ) ) )
				{
					return array( 0 => $this->lang->words['pp_bgimg_ext_bad'] );
				}
				
				$custom['bg_url'] = $bg_url;
				$custom['type']   = 'url';
			}
			else if ( $this->memberData['gbw_allow_upload_bgimage'] )
			{
				/* Load more lang strings */
				$this->registry->class_localization->loadLanguageFile( array( 'public_profile' ), 'members' );
		
				/* Upload img */
				$img = IPSMember::getFunction()->uploadBackgroundImage();
		
				if ( $img['status'] == 'fail' )
				{
					return array( 0 => $this->lang->words[ 'pp_' . $img['error'] ] );
				}
				else if ( $img['final_location'] )
				{
					$custom['bg_url'] = $img['final_location'];
					$custom['type']   = 'upload';
				}
			}
		}
		
		/* BG color */
		$custom['bg_color'] = $bg_nix ? '' : IPSText::alphanumericalClean( $bg_color );
		
		/* Tile */
		$custom['bg_tile']  = $bg_nix ? '' : $bg_tile;
		
		/* Save it */
		if ( ! $this->memberData['bw_disable_customization'] AND $this->memberData['gbw_allow_customization'] )
		{
			IPSMember::save( $this->memberData['member_id'], array( 'extendedProfile' => array( 'pp_customization' => serialize( $custom ) ) ) );
		}
		
		return TRUE;
	}
	
	/**
	 * UserCP Save Form: Twitter
	 *
	 * @return	array	Errors
	 */
	public function saveTwitter()
	{
		if( !IPSLib::twitter_enabled() )
		{		
			$this->registry->getClass('output')->showError( 'twitter_disabled', 1005.2 );
		}
		
		//-----------------------------------------
		// Data
		//-----------------------------------------
		
		$toSave = IPSBWOptions::thaw( $this->memberData['tc_bwoptions'], 'twitter' );
		
		//-----------------------------------------
		// Loop and save... simple
		//-----------------------------------------
		
		foreach( array( 'tc_s_pic', 'tc_s_avatar', 'tc_s_status', 'tc_s_aboutme', 'tc_s_bgimg', 'tc_si_status' ) as $field )
		{
			$toSave[ $field ] = intval( $this->request[ $field ] );
		}
		
		IPSMember::save( $this->memberData['member_id'], array( 'extendedProfile' => array( 'tc_bwoptions' => IPSBWOptions::freeze( $toSave, 'twitter' ) ) ) );
		
		//-----------------------------------------
		// Now sync
		//-----------------------------------------
		
		require_once( IPS_ROOT_PATH . 'sources/classes/twitter/connect.php' );
		$twitter = new twitter_connect( $this->registry, $this->memberData['twitter_token'], $this->memberData['twitter_secret'] );
		
		try
		{
			$twitter->syncMember( $this->memberData );
		}
		catch( Exception $error )
		{
			$msg = $error->getMessage();
			
			switch( $msg )
			{
				case 'NOT_LINKED':
				case 'NO_MEMBER':
				break;
			}
		}
		
		return TRUE;
	}
	
	/**
	 * UserCP Save Form: Facebook
	 *
	 * @return	array	Errors
	 */
	public function saveFacebook()
	{
		if( !IPSLib::fbc_enabled() )
		{		
			$this->registry->getClass('output')->showError( 'fbc_disabled', 1005 );
		}
		
		//-----------------------------------------
		// Data
		//-----------------------------------------
		
		$toSave = IPSBWOptions::thaw( $this->memberData['members_bitoptions'], 'members' );
		
		//-----------------------------------------
		// Loop and save... simple
		//-----------------------------------------
		
		foreach( array( 'fbc_s_pic', 'fbc_s_avatar', 'fbc_s_status', 'fbc_s_aboutme', 'fbc_si_status' ) as $field )
		{
			$toSave[ $field ] = intval( $this->request[ $field ] );
		}
		
		IPSMember::save( $this->memberData['member_id'], array( 'extendedProfile' => array( 'fb_bwoptions' => IPSBWOptions::freeze( $toSave, 'facebook' ) ) ) );
		
		//-----------------------------------------
		// Now sync
		//-----------------------------------------
		
		require_once( IPS_ROOT_PATH . 'sources/classes/facebook/connect.php' );
		$facebook = new facebook_connect( $this->registry );
		
		try
		{
			$facebook->syncMember( $this->memberData );
		}
		catch( Exception $error )
		{
			$msg = $error->getMessage();
			
			switch( $msg )
			{
				case 'NOT_LINKED':
				case 'NO_MEMBER':
				break;
			}
		}
		
		return TRUE;
	}
	
	/**
	 * UserCP Save Form: Ignore Users
	 *
	 * @return	array	Errors
	 */
	public function saveIgnoredUsers()
	{
		//-----------------------------------------
		// INIT
		//-----------------------------------------
		
		$newName = $this->request['newbox_1'];
		
		if ( ! trim( $newName ) )
		{
			return array( 0 => $this->lang->words['ignoreuser_nomem'] );
		}
		
		//-----------------------------------------
		// Load
		//-----------------------------------------
		
		$member = IPSMember::load( $newName, 'core', 'displayname' );
		
		if ( ! $member['member_id'] )
		{
			return array( 0 => $this->lang->words['ignoreuser_nomem'] );
		}
		
		if ( $member['member_id'] == $this->memberData['member_id'] )
		{
			return array( 0 => $this->lang->words['ignoreuser_cannot'] );
		}
		
		//-----------------------------------------
		// Already ignoring?
		//-----------------------------------------
		
		$ignoreMe = $this->DB->buildAndFetch( array( 
													'select' => '*',
													'from'   => 'ignored_users',
													'where'  => 'ignore_owner_id=' . $this->memberData['member_id'] . ' AND ignore_ignore_id=' . $member['member_id'] 
											)	 );
		
		if ( $ignoreMe['ignore_id'] )
		{
			return array( 0 => $this->lang->words['ignoreuser_already'] );
		}
		
		//-----------------------------------------
		// Can we ignore them?
		//-----------------------------------------
		
		if ( $member['_canBeIgnored'] !== TRUE )
		{
			return array( 0 => $this->lang->words['ignoreuser_cannot'] );
	 	}

		//-----------------------------------------
		// Add it
		//-----------------------------------------

		$this->DB->insert( 'ignored_users', array( 
													'ignore_owner_id'  => $this->memberData['member_id'],
													'ignore_ignore_id' => $member['member_id'],
													'ignore_messages'  => isset( $this->request['ignore_messages'] ) && $this->request['ignore_messages'] ? 1 : 0,
													'ignore_topics'    => isset( $this->request['ignore_topics'] ) && $this->request['ignore_topics'] ? 1 : 0,
												) 
						);
						
		/* Rebuild cache */
		IPSMember::rebuildIgnoredUsersCache( $this->memberData );
		
		return TRUE;
	}
	
	
	/**
	 * UserCP Save Form: Avatar
	 *
	 * @return	array	Errors
	 */
	public function saveAvatar()
	{
		if( ! $this->settings['avatars_on'] )
		{
			$this->registry->getClass('output')->showError( 'members_profile_disabled', 1031, null, null, 403 );
		}
		
		try
		{
			IPSMember::getFunction()->saveNewAvatar( $this->memberData['member_id'] );
		}
		catch( Exception $error )
		{
			$msg = $error->getMessage();
			
			switch( $msg )
			{
				case 'NO_MEMBER_ID':
					return array( 0 => $this->lang->words['saveavatar_nomem'] );
				break;
				case 'NO_PERMISSION':
					return array( 0 => $this->lang->words['saveavatar_noperm'] );
				break;
				case 'UPLOAD_NO_IMAGE':
					return array( 0 => $this->lang->words['saveavatar_nofile'] );
				break;
				case 'UPLOAD_INVALID_FILE_EXT':
					return array( 0 => $this->lang->words['saveavatar_noimg'] );
				break;
				case 'UPLOAD_TOO_LARGE':
					return array( 0 => $this->lang->words['saveavatar_toobig'] );
				break;
				case 'UPLOAD_CANT_BE_MOVED':
					return array( 0 => $this->lang->words['saveavatar_chmod'] );
				break;
				case 'UPLOAD_NOT_IMAGE':
					return array( 0 => $this->lang->words['saveavatar_badimg'] );
				break;
				case 'NO_AVATAR_TO_SAVE':
					return array( 0 => $this->lang->words['saveavatar_noimg2'] );
				break;
				case 'INVALID_FILE_EXT':
					return array( 0 => $this->lang->words['saveavatar_badimgext'] );
				break;
			}
		}
	}
	
	/**
	 * UserCP Save Form: Photo
	 *
	 * @return	array	Errors
	 */
	public function savePhoto()
	{
		//-----------------------------------------
		// Check to make sure that we can edit profiles..
		//-----------------------------------------
	
		if ( ! $this->memberData['g_edit_profile'] )
		{
			$this->registry->getClass('output')->showError( 'members_profile_disabled', 1027, null, null, 403 );
		}
		
		//-----------------------------------------
		// Load lang file
		//-----------------------------------------
		
		$this->registry->class_localization->loadLanguageFile( array( 'public_profile' ), 'members' );
		
		//-----------------------------------------
		// Do upload...
		//-----------------------------------------
	
		$photo = IPSMember::getFunction()->uploadPhoto();

		if ( $photo['status'] == 'fail' )
		{
			return array( 0 => $this->lang->words[ 'pp_' . $photo['error'] ] );
		}
		else
		{
			IPSMember::save( $this->memberData['member_id'], array( 'extendedProfile' => array( 'pp_main_photo'   => $photo['final_location'],
												  				   	 						'pp_main_width'   => intval($photo['final_width']),
																						   	'pp_main_height'  => intval($photo['final_height']),
																							'pp_thumb_photo'  => $photo['t_final_location'],
																							'pp_thumb_width'  => intval($photo['t_final_width']),
																							'pp_thumb_height' => intval($photo['t_final_height']) ) ) );
		}
		
		return TRUE;
	}
	
	/**
	 * UserCP Save Form: Signature
	 *
	 * @return	array	Errors
	 */
	public function saveSignature()
	{
		//-----------------------------------------
		// Check to make sure that we can edit profiles..
		//-----------------------------------------

		$sig_restrictions	= explode( ':', $this->memberData['g_signature_limits'] );
		
		if ( ! $this->memberData['g_edit_profile'] OR ( $sig_restrictions[0] AND ! $this->memberData['g_sig_unit'] ) )
		{
			$this->registry->getClass('output')->showError( 'members_profile_disabled', 1028, null, null, 403 );
		}
		
		//-----------------------------------------
		// Check length
		//-----------------------------------------
		
		if ( (IPSText::mbstrlen($_POST['Post']) > $this->settings['max_sig_length']) and ($this->settings['max_sig_length']) )
		{
			$this->registry->getClass('output')->showError( 'members_sig_too_long', 1029, null, null, 403 );
		}

		//-----------------------------------------
		// Post process the editor
		// Now we have safe HTML and bbcode
		//-----------------------------------------
		
		$signature = IPSText::getTextClass( 'editor' )->processRawPost( 'Post' );
		
		//-----------------------------------------
		// Parse post
		//-----------------------------------------
		
		IPSText::getTextClass( 'bbcode' )->parse_smilies    = 0;
		IPSText::getTextClass( 'bbcode' )->parse_html       = intval($this->settings['sig_allow_html']);
		IPSText::getTextClass( 'bbcode' )->parse_bbcode     = intval($this->settings['sig_allow_ibc']);
		IPSText::getTextClass( 'bbcode' )->parsing_section	= 'signatures';

		$signature		= IPSText::getTextClass('bbcode')->preDbParse( $signature );
		$testSignature	= IPSText::getTextClass('bbcode')->preDisplayParse( $signature );		

		if (IPSText::getTextClass( 'bbcode' )->error != "")
		{
			$this->lang->loadLanguageFile( array( 'public_post' ), 'forums' );
			
			$this->registry->getClass('output')->showError( IPSText::getTextClass( 'bbcode' )->error, 10210 );
		}
		
		//-----------------------------------------
		// Signature restrictions...
		//-----------------------------------------
		
		$sig_errors	= array();
		
		//-----------------------------------------
		// Max number of images...
		//-----------------------------------------
		
		if( isset($sig_restrictions[1]) and $sig_restrictions[1] !== '' )
		{
			if( substr_count( strtolower($signature), "[img]" ) > $sig_restrictions[1] )
			{
				$sig_errors[] = sprintf( $this->lang->words['sig_toomanyimages'], $sig_restrictions[1] );
			}
		}
		
		//-----------------------------------------
		// Max number of urls...
		//-----------------------------------------
				
		if( isset($sig_restrictions[4]) and $sig_restrictions[4] !== '' )
		{
			if( substr_count( strtolower($signature), "[url" ) > $sig_restrictions[4] )
			{
				$sig_errors[] = sprintf( $this->lang->words['sig_toomanyurls'], $sig_restrictions[4] );
			}
			else
			{
				preg_match_all( "#(^|\s|>)((http|https|news|ftp)://\w+[^\s\[\]\<]+)#is", $signature, $matches );
				
				if( count($matches[1]) > $sig_restrictions[4] )
				{
					$sig_errors[] = sprintf( $this->lang->words['sig_toomanyurls'], $sig_restrictions[4] );
				}
			}
		}
		
		//-----------------------------------------
		// Max number of lines of text...
		//-----------------------------------------
				
		if( isset($sig_restrictions[5]) and $sig_restrictions[5] !== '' )
		{
			$testSig	= IPSText::getTextClass( 'bbcode' )->wordWrap( $signature, $this->settings['post_wordwrap'], '<br />' );

			if( substr_count( $testSig, "<br />" ) >= $sig_restrictions[5] )
			{
				$sig_errors[] = sprintf( $this->lang->words['sig_toomanylines'], $sig_restrictions[5] );
			}
		}
		
		//-----------------------------------------
		// Now the crappy part..
		//-----------------------------------------
				
		if( isset($sig_restrictions[2]) and $sig_restrictions[2] !== '' AND isset($sig_restrictions[3]) and $sig_restrictions[3] !== '' )
		{
			preg_match_all( "/\[img\](.+?)\[\/img\]/i", $signature, $allImages );

			if( count($allImages[1]) )
			{
				foreach( $allImages[1] as $foundImage )
				{
					$imageProperties = @getimagesize( $foundImage );
					
					if( is_array($imageProperties) AND count($imageProperties) )
					{
						if( $imageProperties[0] > $sig_restrictions[2] OR $imageProperties[1] > $sig_restrictions[3] )
						{
							$sig_errors[] = sprintf( $this->lang->words['sig_imagetoobig'], $foundImage, $sig_restrictions[2], $sig_restrictions[3] );
						}
					}
				}
			}
		}
		
		if( count($sig_errors) )
		{
			$this->registry->getClass('output')->showError( implode( '<br />', $sig_errors ), 10211 );
		}
		
		//-----------------------------------------
		// Write it to the DB.
		//-----------------------------------------
		
		IPSMember::save( $this->memberData['member_id'], array( 'extendedProfile' => array( 'signature' => $signature ) ) );
		
		/* Update cache */
		IPSContentCache::update( $this->memberData['member_id'], 'sig', $testSignature );
		
		return TRUE;
	}
	
	/**
	 * UserCP Save Form: About me page
	 *
	 * @return	array	Errors
	 */
	public function saveAboutMe()
	{
		//-----------------------------------------
		// Check to make sure that we can edit profiles..
		//-----------------------------------------
		
		if ( ! $this->memberData['g_edit_profile'] )
		{
			$this->registry->getClass('output')->showError( 'members_profile_disabled', 10212, null, null, 403 );
		}
			 	
	 	$aboutme = $this->DB->buildAndFetch( array( 'select' => 'pp_member_id, pp_about_me', 'from' => 'profile_portal', 'where' => 'pp_member_id=' . $this->memberData['member_id'] ) );

		//-----------------------------------------
		// Post process the editor
		// Now we have safe HTML and bbcode
		//-----------------------------------------
		
		$post = IPSText::getTextClass( 'editor' )->processRawPost( 'Post' );
		
		//-----------------------------------------
		// Parse post
		//-----------------------------------------
		
		IPSText::getTextClass( 'bbcode' )->parse_smilies			= intval($this->settings['aboutme_emoticons']);
		IPSText::getTextClass( 'bbcode' )->parse_html				= intval($this->settings['aboutme_html']);
		IPSText::getTextClass( 'bbcode' )->parse_bbcode 			= intval($this->settings['aboutme_bbcode']);
		IPSText::getTextClass( 'bbcode' )->parsing_section			= 'aboutme';

		$post	= IPSText::getTextClass( 'bbcode' )->preDbParse( $post );
		
		$text	= IPSText::getTextClass( 'bbcode' )->preDisplayParse( $post );

		if ( IPSText::getTextClass( 'bbcode' )->error != "" )
		{
			$this->lang->loadLanguageFile( array( 'public_post' ), 'forums' );
			$this->registry->getClass('output')->showError( IPSText::getTextClass( 'bbcode' )->error, 10213 );
		}
		
		//-----------------------------------------
		// Write it to the DB.
		//-----------------------------------------
		
		IPSMember::save( $this->memberData['member_id'], array( 'extendedProfile' => array( 'pp_about_me' => $post ) ) );
		
		return TRUE;
	}
	
	/**
	 * UserCP Save Form: Profile Info
	 *
	 * @return	array	Errors
	 */
	public function saveProfileInfo()
	{
		//-----------------------------------------
		// INIT
		//-----------------------------------------
		
		$pp_setting_moderate_comments = intval( $this->request['pp_setting_moderate_comments'] );
		$pp_setting_moderate_friends  = intval( $this->request['pp_setting_moderate_friends'] );
		$pp_setting_count_visitors    = intval( $this->request['pp_setting_count_visitors'] );
		$pp_setting_count_comments    = intval( $this->request['pp_setting_count_comments'] );
		$pp_setting_count_friends     = intval( $this->request['pp_setting_count_friends'] );
		$_gender                      = $this->request['gender'] == 'male' ? 'male' : ( $this->request['gender'] == 'female' ? 'female' : '' );
		
		//-----------------------------------------
		// Check to make sure that we can edit profiles..
		//-----------------------------------------
		
		if ( ! $this->memberData['g_edit_profile'] )
		{
			$this->registry->getClass('output')->showError( 'members_profile_disabled', 10214, null, null, 403 );
		}
		
		//-----------------------------------------
		// make sure that either we entered
		// all calendar fields, or we left them
		// all blank
		//-----------------------------------------
		
		$c_cnt = 0;
		
		foreach ( array('day','month','year') as $v )
		{
			if ( $this->request[ $v ] )
			{
				$c_cnt++;
			}
		}
		
		if( $c_cnt > 0 && $c_cnt < 2 )
		{
			$this->registry->getClass('output')->showError( 'member_bad_birthday', 10215 );
		}
		else if( $c_cnt > 0 )
		{
			//-----------------------------------------
			// Make sure it's a legal date
			//-----------------------------------------
			
			$_year = $this->request['year'] ? $this->request['year'] : 1999;
			
			if ( ! checkdate( $this->request['month'], $this->request['day'], $_year ) )
			{
				$this->registry->getClass('output')->showError( 'member_bad_birthday', 10216 );
			}
		}

		//-----------------------------------------
		// Start off our array
		//-----------------------------------------
		
		$core = array(  
					   'bday_day'    => $this->request['day'],
					   'bday_month'  => $this->request['month'],
					   'bday_year'   => $this->request['year'],
					);

		$extendedProfile = array( 'pp_setting_moderate_comments' => $pp_setting_moderate_comments,
								  'pp_setting_moderate_friends'  => $pp_setting_moderate_friends,
								  'pp_setting_count_visitors'    => $pp_setting_count_visitors,
								  'pp_setting_count_comments'    => $pp_setting_count_comments,
								  'pp_setting_count_friends'     => $pp_setting_count_friends );
		
		//-----------------------------------------
		// check to see if we can enter a member title
		// and if one is entered, update it.
		//-----------------------------------------
		
		if( isset( $this->request['member_title'] ) and ( $this->settings['post_titlechange'] ) and ( $this->memberData['posts'] >= $this->settings['post_titlechange']) )
		{
			$core['title'] = IPSText::getTextClass( 'bbcode' )->stripBadWords( $this->request['member_title'] );
		}
		
		//-----------------------------------------
		// Custom profile field stuff
		//-----------------------------------------
		
		$classToLoad = IPSLib::loadLibrary( IPS_ROOT_PATH . 'sources/classes/customfields/profileFields.php', 'customProfileFields' );
		$fields      = new $classToLoad();
		
		$fields->member_data = $this->member->fetchMemberData();
		$fields->initData( 'edit' );
		/* Use $_POST and not ipsRegistry::$request as the custom profile field kernel class has its own cleaning routines for saving and showing
		   which means we end up with double & -> &amp; conversion (&amp;lt;, etc) */
		$fields->parseToSave( $_POST );
		
		if( $fields->error_messages )
		{
			return $fields->error_messages;
		}
		
		/* Check the website url field */
		$website_field = $fields->getFieldIDByKey( 'website' );

		if( $website_field && $fields->out_fields[ 'field_' . $website_field ] )
		{
			if( ! stristr( $fields->out_fields[ 'field_' . $website_field ], 'http://' ) )
			{
				$fields->out_fields[ 'field_' . $website_field ] = 'http://' . $fields->out_fields[ 'field_' . $website_field ];
			}
		}

		//-----------------------------------------
		// Check...
		//-----------------------------------------
		
		if ( count( $fields->error_fields['empty'] ) )
		{
			$this->registry->getClass('output')->showError( array( 'customfields_empty', $fields->error_fields['empty'][0]['pf_title'] ), 10217 );
		}
		
		if ( count( $fields->error_fields['invalid'] ) )
		{
			$this->registry->getClass('output')->showError( array( 'customfields_invalid', $fields->error_fields['invalid'][0]['pf_title'] ), 10218 );
		}
		
		if ( count( $fields->error_fields['toobig'] ) )
		{
			$this->registry->getClass('output')->showError( array( 'customfields_toobig', $fields->error_fields['toobig'][0]['pf_title'] ), 10219 );
		}
		
		//-----------------------------------------
		// Update the DB
		//-----------------------------------------
		
		IPSMember::save( $this->memberData['member_id'], array( 'core'            => $core,
													 		'customFields'    => $fields->out_fields,
													 		'extendedProfile' => $extendedProfile ) );

		//-----------------------------------------
		// Update birthdays cache if user set to today
		// or if birthday was today but isn't now
		//-----------------------------------------
		
		if( $core['bday_month'] == date('m') AND $core['bday_day'] == date('d') )
		{
			$this->cache->rebuildCache( 'birthdays', 'calendar' );
		}
		else if( $this->memberData['bday_month'] == date('m') AND $this->memberData['bday_day'] == date('d') AND ( $core['bday_month'] != date('m') OR $core['bday_day'] != date('d') ) )
		{
			$this->cache->rebuildCache( 'birthdays', 'calendar' );
		}

		return TRUE;
	}
}