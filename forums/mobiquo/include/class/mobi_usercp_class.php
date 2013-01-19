<?php
defined('IN_MOBIQUO') or exit;
require_once (IPS_ROOT_PATH . 'applications/core/modules_public/usercp/manualResolver.php');

class mobi_usercp extends public_core_usercp_manualResolver
{
	/**
	 * Main class entry point
	 *
	 * @param	object		ipsRegistry reference
	 * @return	@e void		[Outputs to screen]
	 */
	public function doExecute( ipsRegistry $registry )
	{
		$this->registry   =  $registry;
        $this->DB         =  $this->registry->DB();
        $this->settings   =& $this->registry->fetchSettings();
        $this->request    =& $this->registry->fetchRequest();
        $this->lang       =  $this->registry->getClass('class_localization');
        $this->member     =  $this->registry->member();
        $this->memberData =& $this->registry->member()->fetchMemberData();
        $this->cache      =  $this->registry->cache();
        $this->caches     =& $this->registry->cache()->fetchCaches();
		//-----------------------------------------
		// INIT
		//-----------------------------------------

		$_thisNav = array();

		//-----------------------------------------
		// Load language
		//-----------------------------------------

		$this->registry->getClass('class_localization')->loadLanguageFile( array( 'public_usercp' ) );

		//-----------------------------------------
		// Logged in?
		//-----------------------------------------

		if ( (! $this->memberData['member_id']) && (!isset($_POST['tt_token']))  )
		{
			get_error('No permission to change password/email');
			exit();
		}
		else if (isset($_POST['tt_token']))
		{
			//@todo
			$user_email = tt_register_verify($_POST['tt_token'], $_POST['tt_code']);
			if(empty($user_email))
			{
				get_error('User verify fail');
			}
			$username = mysql_escape_string($user_email);
			$member = IPSMember::load($username, 'all', 'email');
			$this->memberData = $member;
			$this->memberData['bw_local_password_set'] = false;
			$this->memberData['members_created_remote'] = true;
			if ( (! $this->memberData['member_id']))
			{
				get_error('username is not exist');
			}
		}

		//-----------------------------------------
		// Make sure they're clean
		//-----------------------------------------

		$this->request['tab'] = IPSText::alphanumericalClean( $this->request['tab'] );
		$this->request['area'] = IPSText::alphanumericalClean( $this->request['area'] );

		//-----------------------------------------
		// Set up some basics...
		//-----------------------------------------

		$_TAB  = ( $this->request['tab'] )  ? $this->request['tab']  : 'core';
		$_AREA = ( $this->request['area'] ) ? $this->request['area'] : 'settings';
		$_DO   = ( $this->request['do'] )   ? $this->request['do']   : 'show';
		$_FUNC = ( $_DO == 'show' ) ? 'showForm' : ( $_DO == 'save' ? 'saveForm' : $_DO );
		$tabs  = array();
		$errors = array();

		//-----------------------------------------
		// Got a plug in?
		//-----------------------------------------
		
		IPSLib::loadInterface( 'interface_usercp.php' );
		
		$EXT_DIR  = IPSLib::getAppDir( $_TAB ) . '/extensions';
		if ( ! is_file($EXT_DIR . '/usercpForms.php') )
		{
			get_error("usercpForms.php is not exist");
			exit();
		}

		//-----------------------------------------
		// Cycle through applications and load
		// usercpForm extensions
		//-----------------------------------------
		foreach( IPSLib::getEnabledApplications() as $app_dir => $app_data )
		{
			$ext_dir  = IPSLib::getAppDir( $app_dir ) . '/extensions';

			// Make sure the extension exists
			if ( !is_file( $ext_dir . '/usercpForms.php' ) )
			{
				continue;
			}
			
			$__class        = IPSLib::loadLibrary( $ext_dir . '/usercpForms.php', 'usercpForms_' . $app_dir, $app_dir );
			$_usercp_module = new $__class();
			
			/* Block based on version to prevent old files showing up/causing an error */
			if( !$_usercp_module->version OR $_usercp_module->version < 32 )
			{
				continue;
			}

			$_usercp_module->makeRegistryShortcuts( $this->registry );

			if ( is_callable( array( $_usercp_module, 'init' ) ) )
			{
				$_usercp_module->init();

				/* Set default area? */
				if (  ( $_TAB == $app_dir ) AND ! isset( $_REQUEST['area'] ) )
				{
					if ( isset( $_usercp_module->defaultAreaCode ) )
					{
						$this->request['area'] = $_AREA = $_usercp_module->defaultAreaCode;
					}
				}
			}
	
			
		}

		
		//-----------------------------------------
		// Begin initilization routine for extension
		//-----------------------------------------
		$classToLoad   = IPSLib::loadLibrary( $EXT_DIR . '/usercpForms.php', 'usercpForms_' . $_TAB, $_TAB );
		$usercp_module = new $classToLoad();
		$usercp_module->makeRegistryShortcuts( $this->registry );
		$usercp_module->init();

		if ( ( $_DO == 'saveForm' || $_DO == 'showForm' ) AND ! is_callable( array( $usercp_module, $_FUNC ) ) )
		{
			get_error("Call saveForm function error");
			exit();
		}

		//-----------------------------------------
		// Run it...
		//-----------------------------------------
		if ( $_FUNC == 'saveForm' )
		{
			global $request_name;
			if($request_name == 'update_email')
			{
				$usercp_module->settings['reg_auth_type'] = false;
			}
			$errors = $usercp_module->saveForm( $_AREA );

			if ( is_array( $errors ) AND count( $errors ) )
			{
				foreach ($errors as $key=> $values)
				{
					get_error($values);
				}
			}
			else if ( $usercp_module->ok_message )
			{
				return true;
			}
			else
			{
				get_error("Update password/email faile , please try again ");
			}
		}


	}
}