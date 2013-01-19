<?php

/*-----------------------------------------*\
|   (FSY23) Universal Mod Installer v2.6.4  |
+-------------------------------------------+
|   Admin Component File                    |
+-------------------------------------------+
|   (c) 2008 Michael McCune                 |
|   Email: michael.mccune@gmail.com         |
|   http://www.invisionmodding.com/         |
\*-----------------------------------------*/

define( 'IPS_API_PATH', ROOT_PATH.'sources/api/' );

if ( !defined( 'IN_ACP' ) )
{
	print "<h1>Incorrect access</h1>You cannot access this file directly. If you have recently upgraded, make sure you upgraded 'admin.php'.";
	exit();
}

class ad_umi
{
	var $ipsclass;
	
	var $xml_array = array();
	var $tasks     = array();
	var $steps     = array();
	
	var $umi_ver   = "2.6.4";
	
	/*-------------------------------------------------------------------------*/
	// Our 'auto_run' function
	/*-------------------------------------------------------------------------*/
	
	function auto_run()
	{
		//-----------------------------------------
		// Load some more caches
		//-----------------------------------------
		
		$this->ipsclass->init_load_cache( array( 'components', 'languages' ) );
		
		//-----------------------------------------
		// Init the nav
		//-----------------------------------------
		
		$this->ipsclass->admin->nav[] = array( $this->ipsclass->form_code, '(FSY23) Universal Mod Installer' );
		
		//-----------------------------------------
		// Load the XML class
		//-----------------------------------------
		
		require_once( KERNEL_PATH."class_xml.php" );
		$this->ipsclass->xml  = new class_xml();
		
		//-----------------------------------------
		// Init the settings
		//-----------------------------------------
		
		$this->settings_init();
		
		//-----------------------------------------
		// Init ACP Help
		//-----------------------------------------
		
		$this->acp_help_init();
		
		//-----------------------------------------
		// Get our installed mods
		//-----------------------------------------
		
		$this->installed_mods_init();
		
		//-----------------------------------------
		// Init the mod
		//-----------------------------------------
		
		$this->mod_init();
		
		//-----------------------------------------
		// What are we doing?
		//-----------------------------------------
		
		switch ( $this->ipsclass->input['code'] )
		{
			case 'settings':
				$this->view_settings();
				break;
			case 'view':
				$this->view_mods();
				break;
			case 'install':
				$this->pre_install();
				break;
			case 'work':
				$this->runme();
				break;
			default:
				$this->view_mods();
				break;
		}
	}
	
	/*-------------------------------------------------------------------------*/
	// Settings
	/*-------------------------------------------------------------------------*/
	
	function view_settings()
	{
		//-----------------------------------------
		// Basic admin page stuff
		//-----------------------------------------
		
		$this->ipsclass->admin->page_title  = "(FSY23) Universal Mod Installer: Manage Settings";
		$this->ipsclass->admin->page_detail = "You may edit the configuration below to suit your needs";
		$this->ipsclass->admin->nav[]       = array( '', 'Manage Settings' );
		
		//-----------------------------------------
		// Load the settings lib
		//-----------------------------------------
		
		$settings = $this->ipsclass->load_class( ROOT_PATH.'sources/action_admin/settings.php', 'ad_settings' );
		$settings->get_by_key        = 'umi';
		$settings->return_after_save = 'section=components&amp;act=umi&amp;code=settings';
		
		//-----------------------------------------
		// Show the settings
		//-----------------------------------------
		
		$settings->setting_view();
	}
	
	/*-------------------------------------------------------------------------*/
	// Make sure the Settings are set up
	/*-------------------------------------------------------------------------*/
	
	function settings_init()
	{
		//-----------------------------------------
		// Load the settings lib
		//-----------------------------------------
		
		$settings = $this->ipsclass->load_class( ROOT_PATH.'sources/action_admin/settings.php', 'ad_settings' );
		
		//-----------------------------------------
		// Have we created this setting group yet?
		//-----------------------------------------
		
		$group = $this->ipsclass->DB->build_and_exec_query( array( 'select' => 'conf_title_id', 'from' => 'conf_settings_titles', 'where' => "conf_title_keyword='umi'" ) );
		
		if ( !$group['conf_title_id'] )
		{
			$this->ipsclass->DB->do_insert( 'conf_settings_titles', array( 'conf_title_title'   => 'Universal Mod Installer',
																		   'conf_title_desc'    => 'Install and manage all compatible mods',
																		   'conf_title_count'   => 0,
																		   'conf_title_noshow'  => 1,
																		   'conf_title_keyword' => 'umi',
										  )								 );
			
			$group['conf_title_id'] = $this->ipsclass->DB->get_insert_id();
		}
		
		//-----------------------------------------
		// Define our settings
		//-----------------------------------------
		
		$umi_settings = array( 'umi_mods_perpage' => array( 'conf_title'       => 'Number of mods to display per page',
															'conf_description' => 'How many mods do you want to display per page on the mods list?',
															'conf_type'        => 'input',
															'conf_default'     => 10,
															'conf_extra'       => '',
															'conf_evalphp'     => '',
															'conf_position'    => 1,
															'conf_start_group' => 'Universal Mod Installer settings',
															'conf_end_group'   => 0,
															'conf_help_key'    => '',
														  ),
							   'umi_do_callbacks' => array( 'conf_title'       => 'Use the \'callback\' functions?',
															'conf_description' => 'These functions can help ensure your mods are up to date, but not all hosts allow them to work.',
															'conf_type'        => 'yes_no',
															'conf_default'     => 1,
															'conf_extra'       => '',
															'conf_evalphp'     => '',
															'conf_position'    => 2,
															'conf_start_group' => '',
															'conf_end_group'   => 0,
															'conf_help_key'    => '',
														  ),
							   'umi_skin_recache' => array( 'conf_title'       => 'Recache skins automatically?',
															'conf_description' => 'If any templates are added, should the skin be automatically recached?  Turn this off if installing a mod exhausts your memory at this point.',
															'conf_type'        => 'yes_no',
															'conf_default'     => 1,
															'conf_extra'       => '',
															'conf_evalphp'     => '',
															'conf_position'    => 3,
															'conf_start_group' => '',
															'conf_end_group'   => 1,
															'conf_help_key'    => '',
														  ),
							 );
		
		//-----------------------------------------
		// Ensure the settings have been created
		//-----------------------------------------
		
		$cnt = 0;
		
		foreach ( $umi_settings as $key => $value )
		{
			if ( !isset( $this->ipsclass->vars[ $key ] ) )
			{
				$cnt++;
				
				$insert = array( 'conf_title'       => $value['conf_title'],
								 'conf_description' => $value['conf_description'],
								 'conf_group'       => $group['conf_title_id'],
								 'conf_type'        => $value['conf_type'],
								 'conf_key'         => $key,
								 'conf_default'     => $value['conf_default'],
								 'conf_extra'       => $value['conf_extra'],
								 'conf_evalphp'     => $value['conf_evalphp'],
								 'conf_protected'   => 1,
								 'conf_position'    => $value['conf_position'],
								 'conf_start_group' => $value['conf_start_group'],
								 'conf_end_group'   => $value['conf_end_group'],
								 'conf_help_key'    => $value['conf_help_key'],
								 'conf_add_cache'   => 1,
							   );
				
				if ( !$this->ipsclass->DB->field_exists( 'conf_help_key', 'conf_settings' ) )
				{
					unset( $insert['conf_help_key'] );
				}
				
				$this->ipsclass->DB->do_insert( 'conf_settings', $insert );
			}
		}
		
		//-----------------------------------------
		// Recache, if needed
		//-----------------------------------------
		
		if ( $cnt )
		{
			$settings->setting_rebuildcache();
		}
		
		return TRUE;
	}
	
	/*-------------------------------------------------------------------------*/
	// ACP Help (New in 2.3)
	/*-------------------------------------------------------------------------*/
	
	function acp_help_init()
	{
		//-----------------------------------------
		// Are we on 2.3?
		//-----------------------------------------
		
		if ( isset( $this->ipsclass->vars['acp_tutorial_mode'] ) )
		{
			//-----------------------------------------
			// Define our help sections
			//-----------------------------------------
			
			$umi_acp_help = array( 'components_umi_'		=> array( 'is_setting'     => 0,
																	  'help_title'     => '(FSY23) Universal Mod Installer: Manage Mod Installations',
																	  'help_body'      => 'From this page, you can install, upgrade, and uninstall all compatible mods.  Simply select the appropriate option from the box to the right of the mod in the table below.',
																	  'help_mouseover' => '',
																	),
								   'components_umi_view'    => array( 'is_setting'     => 0,
																	  'help_title'     => '(FSY23) Universal Mod Installer: Manage Mod Installations',
																	  'help_body'      => 'From this page, you can install, upgrade, and uninstall all compatible mods.  Simply select the appropriate option from the box to the right of the mod in the table below.',
																	  'help_mouseover' => '',
																	),
								   'components_umi_install' => array( 'is_setting'     => 0,
																	  'help_title'     => '(FSY23) Universal Mod Installer: XML Analysis',
																	  'help_body'      => 'The mod\'s XML file has been analyzed and the proper steps have been determined.',
																	  'help_mouseover' => '',
																	),
								   'settinggroup_umi'       => array( 'is_setting'     => 0,
																	  'help_title'     => '(FSY23) Universal Mod Installer: Manage Settings',
																	  'help_body'      => 'Use the settings below to customize how the Universal Mod Installer behaves. If you experience any problems with the pages timing out when using the Universal Mod Installer, set the "Use the \'callback\' functions?" value to <b>No</b>.',
																	  'help_mouseover' => '',
																	),
								 );
			
			//-----------------------------------------
			// Ensure the help keys have been created
			//-----------------------------------------
			
			$this->ipsclass->DB->build_query( array( 'select' => 'page_key',
													 'from'   => 'acp_help',
													 'where'  => "page_key IN ('".implode( "','", array_keys( $umi_acp_help ) )."')",
											)	   );
			$this->ipsclass->DB->exec_query();
			
			if ( $this->ipsclass->DB->get_num_rows() )
			{
				while ( $row = $this->ipsclass->DB->fetch_row() )
				{
					$helps[] = $row['page_key'];
				}
			}
			
			foreach ( $umi_acp_help as $key => $value )
			{
				if ( is_array( $helps ) && count( $helps ) )
				{
					if ( !in_array( $key, $helps ) )
					{
						$insert = array( 'is_setting'     => $value['is_setting'],
										 'page_key'       => $key,
										 'help_title'     => $value['help_title'],
										 'help_body'      => $value['help_body'],
										 'help_mouseover' => $value['help_mouseover'],
										);
						
						$this->ipsclass->DB->do_insert( 'acp_help', $insert );
					}
				}
			}
		}
		
		return TRUE;
	}
	
	/*-------------------------------------------------------------------------*/
	// Build arrays of what mods we have installed
	/*-------------------------------------------------------------------------*/
	
	function installed_mods_init()
	{
		//-----------------------------------------
		// Ensure we have the necessary database table
		//-----------------------------------------
		
		if ( !in_array( SQL_PREFIX.'installed_mods', $this->ipsclass->DB->get_table_names() ) )
		{
			$this->ipsclass->DB->query( $this->fix_create_table( "CREATE TABLE ".SQL_PREFIX."installed_mods
				(m_id int(11) NOT NULL auto_increment,
				 m_name varchar(255) NOT NULL default '',
				 m_version varchar(8) NOT NULL default '',
				 m_author varchar(255) NOT NULL default '',
				 m_website varchar(255) NOT NULL default 'http://',
				 m_started tinyint(1) NOT NULL default '0',
				 m_finished tinyint(1) NOT NULL default '0',
				 PRIMARY KEY (m_id))
				TYPE=MyISAM" ) );
		}
		
		if ( !$this->ipsclass->DB->field_exists( 'm_website', 'installed_mods' ) )
		{
			$this->ipsclass->DB->sql_add_field( 'installed_mods', 'm_website', "varchar(255) NOT NULL", "'http://' AFTER `m_author`" );
		}
	}
	
	/*-------------------------------------------------------------------------*/
	// Initialize a selected mod
	/*-------------------------------------------------------------------------*/
	
	function mod_init()
	{
		//-----------------------------------------
		// If we have a mod, load it up
		//-----------------------------------------
		
		if ( $this->ipsclass->input['mod'] )
		{
			$alert = array();
			
			$this->load_xml( $this->ipsclass->input['mod'] );
			
			//-----------------------------------------
			// Different task sections
			//-----------------------------------------
			
			$task_sections = array( 'sections'   => 'section',
									'settings'   => 'setting',
									'components' => 'component',
									'languages'  => 'language',
									'templates'  => 'template',
									'tasks'      => 'task',
									'helps'      => 'help',
									'acp_helps'  => 'acp_help',
									'tables'     => 'table',
									'alters'     => 'alter',
									'inserts'    => 'insert',
									'updates'    => 'update',
									'customs'    => 'custom',
								  );
			
			//-----------------------------------------
			// See how many of each task we're doing
			//-----------------------------------------
			
			foreach ( $task_sections as $k => $v )
			{
				if ( isset( $this->xml_array[ $k."_group" ][ $v ] ) )
				{
					if ( !is_array( $this->xml_array[ $k."_group" ][ $v ][0] ) )
					{
						$tmp = $this->xml_array[ $k."_group" ][ $v ];
						unset( $this->xml_array[ $k."_group" ][ $v ] );
						$this->xml_array[ $k."_group" ][ $v ][0] = $tmp;
					}
				}
				
				$this->tasks[ $k ] = count( $this->xml_array[ $k."_group" ][ $v ] );
			}
			
			//-----------------------------------------
			// Custom scripts
			//-----------------------------------------
			
			if ( $this->tasks['customs'] > 0 )
			{
				if ( !file_exists( ROOT_PATH.'mod_install/'.$this->xml_array['customs_group']['custom'][0]['script_name']['VALUE'].'.php' ) )
				{
					$alert[] = "WARNING: The required custom installation script, {$this->xml_array['customs_group']['custom'][0]['script_name']['VALUE']}.php, is not present in your forum's mod_install directory.  Please ensure that this file has been uploaded and re-try the installation.";
				}
				else
				{
					$tmp = array();
					$tmp['customs_init'] = $this->xml_array['customs_group']['custom'][0]['has_init']['VALUE'];
					$this->tasks = array_merge( $tmp, $this->tasks );
				}
			}
			
			//-----------------------------------------
			// ACP Help in 2.2.x
			//-----------------------------------------
			
			if ( $this->tasks['acp_helps'] > 0 && !isset( $this->ipsclass->vars['acp_tutorial_mode'] ) )
			{
				unset( $this->tasks['acp_helps'] );
			}
			
			//-----------------------------------------
			// Create our steps array
			//-----------------------------------------
			
			foreach ( $this->tasks as $k => $v )
			{
				if ( $v )
				{
					$this->steps[] = $k;
				}
			}
			
			//-----------------------------------------
			// Which steps require a recache?
			//-----------------------------------------
			
			$cachesections = array( 'sections', 'settings', 'components', 'alters', 'inserts', 'updates' );
			
			foreach ( $cachesections as $k )
			{
				if ( in_array( $k, $this->steps ) )
				{
					$this->steps[] = 'recache';
					break;
				}
			}
			
			if ( !in_array( 'recache', $this->steps ) && ( in_array( 'customs', $this->steps ) ) )
			{
				if ( $this->xml_array['customs_group']['custom'][0]['recache']['VALUE'] == 1 )
				{
					$this->steps[] = 'recache';
				}
			}
			
			//-----------------------------------------
			// Do we need to rebuild skin caches?
			//-----------------------------------------
			
			if ( $this->ipsclass->vars['umi_skin_recache'] && ( in_array( 'templates', $this->steps ) || ( in_array( 'customs', $this->steps ) && $this->xml_array['customs_group']['custom'][0]['templatesrecache']['VALUE'] == 1 ) ) )
			{
				$this->steps[] = 'templatesrecache';
			}
			
			//-----------------------------------------
			// Ensure our skin files are writeable
			//-----------------------------------------
			
			if ( in_array( 'templates', $this->steps ) )
			{
				$skin_files = array();
					
				foreach ( $this->xml_array['templates_group']['template'] as $k => $v )
				{
					if ( !in_array( $v['group_name']['VALUE'], $skin_files ) )
					{
						$skin_files[] = $v['group_name']['VALUE'];
					}
				}
				
				foreach ( $this->ipsclass->cache['skin_id_cache'] as $k => $v )
				{
					if ( $v['set_skin_set_id'] == 1 )
					{
						continue;
					}
					
					foreach ( $skin_files as $kk => $vv )
					{
						if ( file_exists( CACHE_PATH.'cache/skin_cache/cacheid_'.$v['set_skin_set_id'].'/'.$vv.'.php' ) && !is_writeable( CACHE_PATH.'cache/skin_cache/cacheid_'.$v['set_skin_set_id'].'/'.$vv.'.php' ) )
						{
							if ( !@chmod( CACHE_PATH.'cache/skin_cache/cacheid_'.$v['set_skin_set_id'].'/'.$vv.'.php', 0777 ) )
							{
								$alert[] = "WARNING: cache/skin_cache/cacheid_{$v['set_skin_set_id']}/{$vv}.php is not writeable<br />Please check the CHMOD value on this file before continuing, it should be set to 777. Failure to do so could mean that the skin changes for this mod may not be done properly.";
							}
						}
					}
				}
			}
			
			//-----------------------------------------
			// Ensure our language path is writeable
			//-----------------------------------------
			
			if ( in_array( 'languages', $this->steps ) )
			{
				$lang_files = array();
				
				foreach ( $this->xml_array['languages_group']['language'] as $k => $v )
				{
					if ( !in_array( $v['file']['VALUE'], $lang_files ) )
					{
						$lang_files[] = $v['file']['VALUE'];
					}
				}
				
				foreach ( $this->ipsclass->cache['languages'] as $k => $v )
				{
					foreach ( $lang_files as $kk => $vv )
					{
						if ( file_exists( CACHE_PATH.'cache/lang_cache/'.$v['ldir'].'/'.$vv.'.php' ) && !is_writeable( CACHE_PATH.'cache/lang_cache/'.$v['ldir'].'/'.$vv.'.php' ) )
						{
							if ( !@chmod( CACHE_PATH.'cache/lang_cache/'.$v['ldir'].'/'.$vv.'.php', 0777 ) )
							{
								$alert[] = "WARNING: cache/lang_cache/{$v['ldir']}/{$vv}.php is not writeable<br />Please check the CHMOD value on this file before continuing, it should be set to 777. Failure to do so could mean that the language changes for this mod may not be done properly.";
							}
						}
					}
				}
			}
			
			//-----------------------------------------
			// The finishing step
			//-----------------------------------------
			
			$this->steps[] = 'finish';
			
			//-----------------------------------------
			// Did we have any alerts?
			//-----------------------------------------
			
			if ( count( $alert ) )
			{
				$this->ipsclass->admin->error( implode( "<br /><br />", $alert ) );
			}
		}
	}
	
	/*-------------------------------------------------------------------------*/
	// Load an XML
	/*-------------------------------------------------------------------------*/
	
	function load_xml( $mod="" )
	{
		if ( $mod )
		{
			$this->ipsclass->xml->xml_parse_document( file_get_contents( ROOT_PATH."mod_install/".$mod.".xml" ) );
			
			if ( !is_array( $this->ipsclass->xml->xml_array['mod_data'] ) )
			{
				$this->ipsclass->admin->error( "There was an error reading the XML file '$mod'.xml in your mod_install directory.  Please correct the problem before continuing." );
			}
			
			$this->xml_array = $this->ipsclass->xml->xml_array['mod_data'];
		}
	}
	
	/*-------------------------------------------------------------------------*/
	// Fix the CREATE TABLE statements to be driver-specific
	/*-------------------------------------------------------------------------*/
	
	function fix_create_table( $query )
	{
		//-----------------------------------------
		// Are we using something other than MySQL?
		//-----------------------------------------
		
		if ( $this->ipsclass->vars['sql_driver'] == 'mssql' )
		{
			// Replace auto_increment with identity
			$query = str_ireplace( 'auto_increment', 'identity', $query );
			
			// Replace the data types
			$query = preg_replace( "/(TINYINT|SMALLINT|MEDIUMINT|INT|BIGINT)\(\d+\)/is", "\\1", $query );
			
			// Remove the MySQL table types
			$query = str_ireplace( 'TYPE=MyISAM', '', $query );
			$query = str_ireplace( 'TYPE=INNODB', '', $query );
			$query = str_ireplace( 'TYPE=HEAP'  , '', $query );
			
			// Remove any dangling whitespace
			$query = trim( $query );
		}
		else if ( $this->ipsclass->vars['sql_driver'] == 'oracle' )
		{
			$this->ipsclass->admin->error( "At this time, Oracle is unsupported as a database driver for use with the Universal Mod Installer, we are working to add this functionality, but need some Oracle users to assist with this.  Visit <a href='http://www.invisionmodding.com/' target='_blank'>my site</a> if you are interested in assisting with this." );
		}
		
		//-----------------------------------------
		// Return the fixed query
		//-----------------------------------------
		
		return $query;
	}
	
	/*-------------------------------------------------------------------------*/
	// Main page
	/*-------------------------------------------------------------------------*/
	
	function view_mods()
	{
		//-----------------------------------------
		// Page Info
		//-----------------------------------------
		
		$this->ipsclass->admin->page_title  = "(FSY23) Universal Mod Installer: Manage Mod Installations";
		$this->ipsclass->admin->page_detail = "Install, upgrade, and uninstall all compatible mods";
		$this->ipsclass->admin->nav[]       = array( '', 'Manage Mod Installations' );
		
		//-----------------------------------------
		// Instantiate some arrays
		//-----------------------------------------
		
		$installable_mods = array();
		$installed_names  = array();
		$installed_info   = array();
		
		//-----------------------------------------
		// Page jump stuff
		//-----------------------------------------
		
		if ( $this->ipsclass->input['st'] > 0 )
		{
			$first = intval( $this->ipsclass->input['st'] );
		}
		
		$this->ipsclass->vars['umi_mods_perpage'] = ( $this->ipsclass->vars['umi_mods_perpage'] ) ? $this->ipsclass->vars['umi_mods_perpage'] : 10;
		
		//-----------------------------------------
		// Does PHP have CURL available?
		//-----------------------------------------
		
		$can_curl = function_exists( 'curl_init' );
		
		//-----------------------------------------
		// Make sure we have a mod_install directory
		//-----------------------------------------
		
		if ( !file_exists( ROOT_PATH."mod_install/" ) )
		{
			if ( !@mkdir( ROOT_PATH."mod_install/", 0755 ) )
			{
				$this->ipsclass->admin->error( "You have no ./mod_install directory.  Please create this directory using your FTP program, and ensure the CHMOD is set to 755." );
			}
		}
		
		//-----------------------------------------
		// Look for any installer files
		//-----------------------------------------
		
		$path  = ROOT_PATH."mod_install/";
		$files = array();
		
		if ( $handle = opendir( $path ) )
		{
			while ( false !== ( $file = readdir( $handle ) ) )
			{
				$ext = preg_replace( "/^.*\.(\S+)$/", "\\1", $file );
				
				if ( $ext == "xml" )
				{
					$files[] = $file;
				}
			}
			
			closedir( $handle );
		}
		
		//-----------------------------------------
		// Do we have any files to look at?
		//-----------------------------------------
		
		if ( count( $files ) )
		{
			foreach ( $files as $k => $v )
			{
				$this->ipsclass->xml->xml_parse_document( file_get_contents( ROOT_PATH."mod_install/".$v ) );
				
				if ( !is_array( $this->ipsclass->xml->xml_array['mod_data'] ) )
				{
					$this->ipsclass->admin->error( "There was an error reading the XML file '$v' in your mod_install directory. Please correct the problem before continuing." );
				}
				
				$themod = array();
				$temp   = $this->ipsclass->xml->xml_array['mod_data']['mod_info'];
				
				$themod['title']        = $temp['title']['VALUE'];
				$themod['version']      = $temp['version']['VALUE'];
				$themod['author']       = $temp['author']['VALUE'];
				$themod['website']      = $temp['website']['VALUE'];
				$themod['file']         = str_replace( ".xml", "", $v );
				$themod['ipbver']       = $temp['ipbver']['VALUE'];
				$themod['version_file'] = $temp['version_file']['VALUE'];
				
				$key = strtolower( $temp['title']['VALUE'] );
				$key = str_replace( " ", "_", $key );
				
				if ( $themod['title'] != "" && in_array( $themod['ipbver'], array( '2.2', '2.3' ) ) )
				{
					$installable_mods[str_replace( '.', '', $themod['ipbver'])][ $key ] = $themod;
				}
			}
			
			foreach ( $installable_mods as $ver => $mods )
			{
				ksort( $installable_mods[ $ver ] );
			}
			
			$new_mods  = $installable_mods;
			unset( $installable_mods );
			$installable_mods = array();
			
			foreach ( $new_mods as $version => $itsmods )
			{
				$new_index = 0;
				
				foreach ( $itsmods as $key => $mod )
				{
					$installable_mods[ $version ][ $new_index++ ] = $mod;
				}
			}
		}
		
		//-----------------------------------------
		// Let's find what the latest mods are
		//-----------------------------------------
		
		$latest_mods   = array();
		$latest_umi    = 0;
		$link          = base64_decode( "aHR0cDovL3d3dy5pbnZpc2lvbm1vZGRpbmcuY29tL3VtaS9tb2RzLnhtbA==" );
		$mod_files     = array();
		$file_contents = "";
		
		if ( $this->ipsclass->vars['umi_do_callbacks'] )
		{
			if ( $can_curl )
			{
				$ch = curl_init();
				
				curl_setopt( $ch, CURLOPT_URL, $link );
				curl_setopt( $ch, CURLOPT_HEADER, 0 );
				curl_setopt( $ch, CURLOPT_TIMEOUT, 3 );
				
				ob_start();
				
				curl_exec( $ch );
				curl_close( $ch );
				$file_contents = ob_get_contents();
				
				ob_end_clean();
			}
			
			if ( !$file_contents )
			{
				$handle = @fopen( $link, "r" );
					
				if ( $handle !== false )
				{
					@fclose( $handle );
					$file_contents = @file_get_contents( $link );
				}
			}
		}
		
		if ( $file_contents )
		{
			$this->ipsclass->xml->xml_parse_document( $file_contents );
			
			if ( is_array( $this->ipsclass->xml->xml_array['mods']['ipb22']['mod'] ) )
			{
				foreach ( $this->ipsclass->xml->xml_array['mods']['ipb22']['mod'] as $k => $v )
				{
					$latest_mods[22][ $v['file']['VALUE'] ] = $v['version']['VALUE'];
				}
			}
			
			if ( is_array( $this->ipsclass->xml->xml_array['mods']['ipb23']['mod'] ) )
			{
				foreach ( $this->ipsclass->xml->xml_array['mods']['ipb23']['mod'] as $k => $v )
				{
					$latest_mods[23][ $v['file']['VALUE'] ] = $v['version']['VALUE'];
				}
			}
			
			$latest_umi = $this->ipsclass->xml->xml_array['mods']['umi']['VALUE'];
		}
		
		//-----------------------------------------
		// Build the mods table
		//-----------------------------------------
		
		$this->ipsclass->adskin->td_header[] = array( "Mod Title"   , "40%" );
		$this->ipsclass->adskin->td_header[] = array( "Version"     , "10%" );
		$this->ipsclass->adskin->td_header[] = array( "Author"      , "15%" );
		$this->ipsclass->adskin->td_header[] = array( "Installed?"  , "10%" );
		
		if ( $this->ipsclass->vars['umi_do_callbacks'] )
		{
			$this->ipsclass->adskin->td_header[] = array( "Up To Date?" , "15%" );
		}
		
		$this->ipsclass->adskin->td_header[] = array( "Options"     , "10%" );
		
		$this->ipsclass->html .= $this->ipsclass->adskin->start_table( "Available Modifications" );
		
		//-----------------------------------------
		// What mods are we displaying?
		//-----------------------------------------
		
		$to_display = array();
		$i          = 0;
		
		foreach ( $installable_mods as $a => $b )
		{
			foreach ( $b as $c => $d )
			{
				$to_display[ $i++ ] = $d;
			}
		}
		
		//-----------------------------------------
		// Get the latest info for these mods
		//-----------------------------------------
		
		if ( count( $to_display ) )
		{
			foreach ( $to_display as $idx => $mod )
			{
				if ( $idx < $first )
				{
					continue;
				}
				
				if ( $idx >= $first + $this->ipsclass->vars['umi_mods_perpage'] )
				{
					break;
				}
				
				if ( $mod['version_file'] && !in_array( $mod['version_file'], $mod_files ) )
				{
					$mod_files[] = $mod['version_file'];
				}
			}
		}
		
		if ( $this->ipsclass->vars['umi_do_callbacks'] && count( $mod_files ) )
		{
			foreach ( $mod_files as $mod_file )
			{
				if ( $can_curl )
				{
					$ch = curl_init();
					
					curl_setopt( $ch, CURLOPT_URL, $mod_file );
					curl_setopt( $ch, CURLOPT_HEADER, 0 );
					curl_setopt( $ch, CURLOPT_TIMEOUT, 3 );
					
					ob_start();
					
					curl_exec( $ch );
					curl_close( $ch );
					$version_contents = ob_get_contents();
					
					ob_end_clean();
				}
				
				if ( !$version_contents )
				{
					$handle = @fopen( $mod_file, "r" );
					
					if ( $handle !== false )
					{
						@fclose( $handle );
						$version_contents = @file_get_contents( $mod_file );
					}
				}
				
				if ( $version_contents )
				{
					$this->ipsclass->xml->xml_parse_document( $version_contents );
					
					if ( !is_array( $this->ipsclass->xml->xml_array['mods']['ipb22']['mod'][0] ) )
					{
						$tmp = $this->ipsclass->xml->xml_array['mods']['ipb22']['mod'];
						unset( $this->ipsclass->xml->xml_array['mods']['ipb22']['mod'] );
						$this->ipsclass->xml->xml_array['mods']['ipb22']['mod'][0] = $tmp;
					}
					
					if ( !is_array( $this->ipsclass->xml->xml_array['mods']['ipb23']['mod'][0] ) )
					{
						$tmp = $this->ipsclass->xml->xml_array['mods']['ipb23']['mod'];
						unset( $this->ipsclass->xml->xml_array['mods']['ipb23']['mod'] );
						$this->ipsclass->xml->xml_array['mods']['ipb23']['mod'][0] = $tmp;
					}
					
					foreach ( $this->ipsclass->xml->xml_array['mods']['ipb22']['mod'] as $kk => $vv )
					{
						if ( !in_array( $vv['file']['VALUE'], array_keys( $latest_mods ) ) )
						{
							$latest_mods[22][ $vv['file']['VALUE'] ] = $vv['version']['VALUE'];
						}
					}
					
					foreach ( $this->ipsclass->xml->xml_array['mods']['ipb23']['mod'] as $kk => $vv )
					{
						if ( !in_array( $vv['file']['VALUE'], array_keys( $latest_mods ) ) )
						{
							$latest_mods[23][ $vv['file']['VALUE'] ] = $vv['version']['VALUE'];
						}
					}
				}
			}
		}
		
		//-----------------------------------------
		// What mods do we have installed?
		//-----------------------------------------
		
		$this->ipsclass->DB->build_query( array( 'select' => '*',
												 'from'   => 'installed_mods',
												 'order'  => 'm_name',
										)	   );
		$this->ipsclass->DB->exec_query();
		
		if ( $this->ipsclass->DB->get_num_rows() )
		{
			while ( $r = $this->ipsclass->DB->fetch_row() )
			{
				$installed_names[] = $r['m_name'];
				$installed_info[ str_replace( " ", "_", $r['m_name'] ) ] = $r;
			}
		}
		
		//-----------------------------------------
		// OK, let's show some mods
		//-----------------------------------------
		
		foreach ( $to_display as $k => $v )
		{
			//-----------------------------------------
			// Page jump stuff
			//-----------------------------------------
			
			if ( $k < $first )
			{
				continue;
			}
			
			if ( $k >= $first + $this->ipsclass->vars['umi_mods_perpage'] )
			{
				break;
			}
			
			//-----------------------------------------
			// Possible row entries
			//-----------------------------------------
			
			$title     = "<strong>{$v['title']}</strong>";
			$version   = "<div align='center'>v{$v['version']}</div>";
			$author    = "<div align='center'><a href='{$v['website']}' target='_blank'>{$v['author']}</a></div>";
			$outdated  = "<div align='center'><img src='{$this->ipsclass->skin_acp_url}/images/memsearch_delete.gif' border='0' title='Out of Date, you have v<!--VERSION--> installed' /></div>";
			$installed = "<div align='center'><img src='{$this->ipsclass->skin_acp_url}/images/aff_tick.png' border='0' title='Installed' /></div>";
			$problem   = "<div align='center'><img src='{$this->ipsclass->skin_acp_url}/images/acp_trashcan.gif' border='0' title='There was a problem completing a previous installation, it is recommended that you uninstall this mod' /></div>";
			$not_ins   = "<div align='center'><img src='{$this->ipsclass->skin_acp_url}/images/aff_cross.png' border='0' title='Not Installed' /></div>";
			$upd_avail = "<div align='center'><img src='{$this->ipsclass->skin_acp_url}/images/aff_cross.png' border='0' title='An update to this mod is available!' /></div>";
			$uptodate  = "<div align='center'><img src='{$this->ipsclass->skin_acp_url}/images/aff_tick.png' border='0' title='You have the latest version of this mod installed!' /></div>";
			$unknown   = "<div align='center'><img src='{$this->ipsclass->skin_acp_url}/images/memsearch_delete.gif' border='0' title='Unknown' /></div>";
			$options   = "<div align='center'><img id='menu{$v['file']}' src='{$this->ipsclass->skin_acp_url}/images/filebrowser_action.gif' border='0' title='Options' class='ipd' /></div>";
			
			//-----------------------------------------
			// Possible install options
			//-----------------------------------------
			
			$opts   = "";
			$opts_1 = "img_add + \" <a href='{$this->ipsclass->base_url}&amp;{$this->ipsclass->form_code}&amp;code=install&amp;mod={$v['file']}&amp;st={$this->ipsclass->input['st']}'>Upgrade...</a>\",
img_delete + \" <a href='{$this->ipsclass->base_url}&amp;{$this->ipsclass->form_code}&amp;code=install&amp;mod={$v['file']}&amp;un=1&amp;st={$this->ipsclass->input['st']}'>Uninstall...</a>\"";
			$opts_2 = "img_add + \" <a href='{$this->ipsclass->base_url}&amp;{$this->ipsclass->form_code}&amp;code=install&amp;mod={$v['file']}&amp;st={$this->ipsclass->input['st']}'>Reinstall...</a>\",
img_delete + \" <a href='{$this->ipsclass->base_url}&amp;{$this->ipsclass->form_code}&amp;code=install&amp;mod={$v['file']}&amp;un=1&amp;st={$this->ipsclass->input['st']}'>Uninstall...</a>\"";
			$opts_3 = "img_delete + \" <a href='{$this->ipsclass->base_url}&amp;{$this->ipsclass->form_code}&amp;code=install&amp;mod={$v['file']}&amp;un=1&amp;st={$this->ipsclass->input['st']}'>Uninstall...</a>\"";
			$opts_4 = "img_add + \" <a href='{$this->ipsclass->base_url}&amp;{$this->ipsclass->form_code}&amp;code=install&amp;mod={$v['file']}&amp;st={$this->ipsclass->input['st']}'>Install...</a>\"";
			
			//-----------------------------------------
			// Our different possibilities
			//-----------------------------------------
			
			if ( in_array( $v['title'], $installed_names ) )
			{
				$mod_info = $installed_info[ str_replace( " ", "_", $v['title'] ) ];
				
				if ( $mod_info['m_started'] == 1 )
				{
					if ( $mod_info['m_finished'] == 1 )
					{
						if ( $mod_info['m_version'] < $v['version'] )
						{
							$opts = $opts_1;
							$outdated = str_replace( "<!--VERSION-->", $mod_info['m_version'], $outdated );
							$data = ( $this->ipsclass->vars['umi_do_callbacks'] ) ? array( $title, $version, $author, $outdated, $upd_avail, $options ): array( $title, $version, $author, $outdated, $options );
						}
						else
						{
							$ipbver = str_replace( '.', '', $v['ipbver'] );
							if ( isset( $latest_mods[ $ipbver ][ $v['file'] ] ) )
							{
								if ( $latest_mods[ $ipbver ][ $v['file'] ] > $v['version'] )
								{
									$uptodate = $upd_avail;
								}
							}
							else
							{
								$uptodate = $unknown;
							}
							
							$opts = $opts_2;
							$data = ( $this->ipsclass->vars['umi_do_callbacks'] ) ? array( $title, $version, $author, $installed, $uptodate, $options ) : array( $title, $version, $author, $installed, $options );
						}
					}
					else
					{
						$opts = $opts_3;
						$data = ( $this->ipsclass->vars['umi_do_callbacks'] ) ? array( $title, $version, $author, $problem, $unknown, $options ) : array( $title, $version, $author, $problem, $options );
					}
				}
				else
				{
					if ( $mod_info['m_finished'] == 1 )
					{
						$opts = $opts_3;
						$data = ( $this->ipsclass->vars['umi_do_callbacks'] ) ? array( $title, $version, $author, $problem, $unknown, $options ) : array( $title, $version, $author, $problem, $options );
					}
					else
					{
						$opts = $opts_4;
						$data = ( $this->ipsclass->vars['umi_do_callbacks'] ) ? array( $title, $version, $author, $not_ins, $not_ins, $options ) : array( $title, $version, $author, $not_ins, $options );
					}
				}
			}
			else
			{
				$opts = $opts_4;
				$data = ( $this->ipsclass->vars['umi_do_callbacks'] ) ? array( $title, $version, $author, $not_ins, $not_ins, $options ) : array( $title, $version, $author, $not_ins, $options );
			}
			
			$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( $data );
			
			//-----------------------------------------
			// Add the file options
			//-----------------------------------------
			
			$this->ipsclass->html .="<script type=\"text/javascript\">
  menu_build_menu(
  \"menu{$v['file']}\",
  new Array( ".$opts."
		    ) );
 </script>
";
		}
		
		//-----------------------------------------
		// Page Jump
		//-----------------------------------------
		
		$pages = $this->ipsclass->adskin->build_pagelinks( array( 'TOTAL_POSS' => count( $to_display ),
																  'PER_PAGE'   => $this->ipsclass->vars['umi_mods_perpage'],
																  'CUR_ST_VAL' => $first,
																  'L_SINGLE'   => "",
																  'L_MULTI'    => "Pages: ",
																  'BASE_URL'   => $this->ipsclass->base_url."&amp;".$this->ipsclass->form_code."&amp;code=view",
														 )      );
		
		$this->ipsclass->html .= $this->ipsclass->adskin->add_td_basic( $pages, 'right', 'tablerow2' );
		
		//-----------------------------------------
		// Finish the table
		//-----------------------------------------
		
		$this->ipsclass->html .= $this->ipsclass->adskin->end_table();
		
		//-----------------------------------------
		// Make sure UMI is up to date
		//-----------------------------------------
		
		if ( $this->ipsclass->vars['umi_do_callbacks'] )
		{
			$this->ipsclass->html .= $this->ipsclass->adskin->start_table( "Update Checker" );
				
			if ( $latest_umi > 0 )
			{
				$latest_text = ( $latest_umi != $this->umi_ver ) ? "<span style='font-weight: bold; color: red;'>OUT OF DATE</span>" : "<span style='font-weight: bold; color: green;'>UP TO DATE</span>";
			}
			else
			{
				$latest_text = "<span style='font-weight: bold; color: red;'>UNKNOWN</span>";
			}
			
			$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( "<div align='center'>Your copy of (FSY23) Universal Mod Installer is $latest_text!</div>" ) );
			
			$this->ipsclass->html .= $this->ipsclass->adskin->end_table();
		}
		
		$this->ipsclass->html .= $this->ipsclass->adskin->add_standalone_row( "(FSY23) Universal Mod Installer v{$this->umi_ver}, by <a href='http://www.invisionmodding.com/' target='_blank'>Invision Modding</a>, &copy; ".date("Y"), 'center', 'tablefooter' );
		
		//-----------------------------------------
		// All done, output it!
		//-----------------------------------------
		
		$this->ipsclass->admin->output();
	}
	
	/*-------------------------------------------------------------------------*/
	// Right before doing the work
	/*-------------------------------------------------------------------------*/
	
	function pre_install()
	{
		//-----------------------------------------
		// Installing, or uninstalling?
		//-----------------------------------------
		
		$type = ( $this->ipsclass->input['un'] == 1 ) ? 'uninstallation' : 'installation';
		$text = ( $this->ipsclass->input['un'] == 1 ) ? 'Uninstalling'   : 'Installing';
		
		//-----------------------------------------
		// Page Info
		//-----------------------------------------
		
		$this->ipsclass->admin->page_title  = "(FSY23) Universal Mod Installer: XML Analysis";
		$this->ipsclass->admin->page_detail = "The mod's XML file has been analyzed and the proper {$type} steps have been determined.";
		$this->ipsclass->admin->nav[]       = array( $this->ipsclass->form_code.'&code=view', 'Manage Mod Installations' );
		$this->ipsclass->admin->nav[]       = array( '', $text." ".$this->xml_array['mod_info']['title']['VALUE'] );
		
		//-----------------------------------------
		// Show the output
		//-----------------------------------------
		
		$this->ipsclass->html .= $this->ipsclass->adskin->start_table( $this->xml_array['mod_info']['title']['VALUE'] );
		
		$this->ipsclass->html .= $this->ipsclass->adskin->add_td_basic( "<span style='font-size: 12px;'>Click the button below to proceed with the mod $type.<br /><br /><input type='button' class='realbutton' value='Proceed...' onclick='locationjump(\"&amp;{$this->ipsclass->form_code}&amp;code=work&amp;mod={$this->ipsclass->input['mod']}&amp;un={$this->ipsclass->input['un']}&amp;step=0&amp;st={$this->ipsclass->input['st']}\")' /></span>", "center" );
		
		$this->ipsclass->html .= $this->ipsclass->adskin->end_table();
		
		$this->ipsclass->admin->output();
	}
	
	/*-------------------------------------------------------------------------*/
	// Calls the install functions
	/*-------------------------------------------------------------------------*/
	
	function runme()
	{
		if ( $this->ipsclass->input['un'] != 1 )
		{
			$mod_info = $this->ipsclass->DB->build_and_exec_query( array( 'select' => '*',
																		  'from'   => 'installed_mods',
																		  'where'  => "m_name='".$this->xml_array['mod_info']['title']['VALUE']."'",
																 )		);
			
			if ( !$mod_info )
			{
				$mod              = array();
				$mod['m_name']    = $this->xml_array['mod_info']['title']['VALUE'];
				$mod['m_version'] = $this->xml_array['mod_info']['version']['VALUE'];
				$mod['m_author']  = $this->xml_array['mod_info']['author']['VALUE'];
				$mod['m_started'] = 1;
				
				$this->ipsclass->DB->force_data_type['m_version'] = 'string';
				
				$this->ipsclass->DB->do_insert( 'installed_mods', $mod );
			}
			else
			{
				if ( $mod_info['m_started'] != 1 )
				{
					$this->ipsclass->DB->do_update( 'installed_mods', array( 'm_started' => 1 ), "m_name='".$this->xml_array['mod_info']['title']['VALUE']."'" );
				}
			}
		}
		
		$step = ( $this->ipsclass->input['step'] ) ? $this->ipsclass->input['step'] : 0;
		$task = $this->steps[ $step ];
		$this->$task();
	}
	
	/*-------------------------------------------------------------------------*/
	// customs_init
	/*-------------------------------------------------------------------------*/
	
	function customs_init()
	{
		$this->xml_array['customs_group']['custom'][0]['script_name']['VALUE'] = str_replace( '.php', '', $this->xml_array['customs_group']['custom'][0]['script_name']['VALUE'] );
		$custom = $this->ipsclass->load_class( ROOT_PATH.'mod_install/'.$this->xml_array['customs_group']['custom'][0]['script_name']['VALUE'].'.php', $this->xml_array['customs_group']['custom'][0]['script_name']['VALUE'] );
		$custom->xml_array =& $this->xml_array;
		
		if ( $this->ipsclass->input['un'] )
		{
			$custom->init_uninstall();
		}
		else
		{
			$custom->init_install();
		}
	}
	
	/*-------------------------------------------------------------------------*/
	// Setting sections
	/*-------------------------------------------------------------------------*/
	
	function sections()
	{
		$this->ipsclass->input['step']++;
		$uninstall = ( $this->ipsclass->input['un'] == 1 ) ? "&amp;un=1" : "";
		
		$object      = ( $this->tasks['sections'] == 1 ) ? 'Settings Group' : 'Settings Groups';
		$operation   = ( $this->ipsclass->input['un'] )  ? 'removed'        : 'created';
		$sectionkeys = array();
		$group       = "";
		$groups      = array();
		
		foreach ( $this->xml_array['sections_group']['section'] as $k => $v )
		{
			$sectionkeys[] = "'{$v['conf_title_keyword']['VALUE']}'";
		}
		
		$this->ipsclass->DB->do_delete( 'conf_settings_titles', "conf_title_keyword IN (".implode( ",", $sectionkeys ).")" );
		
		if ( !$this->ipsclass->input['un'] )
		{
			foreach ( $this->xml_array['sections_group']['section'] as $k => $v )
			{
				$groups[] = $this->_add_section( $v );
			}
			
			$group = "&amp;group=".implode( "|", $groups );
		}
		
		$this->ipsclass->admin->redirect( "{$this->ipsclass->form_code}&amp;code=work&amp;mod={$this->ipsclass->input['mod']}&amp;step={$this->ipsclass->input['step']}{$uninstall}{$group}&amp;st={$this->ipsclass->input['st']}", "{$this->xml_array['mod_info']['title']['VALUE']}<br />{$this->tasks['sections']} {$object} {$operation}...." );
	}
	
	/*-------------------------------------------------------------------------*/
	// Settings
	/*-------------------------------------------------------------------------*/
	
	function settings()
	{
		$this->ipsclass->input['step']++;
		$uninstall = ( $this->ipsclass->input['un'] == 1 ) ? "&amp;un=1" : "";
		
		$object       = ( $this->tasks['settings'] == 1 ) ? 'Setting' : 'Settings';
		$operation    = ( $this->ipsclass->input['un'] )  ? 'removed' : 'created';
		$settingkeys  = array();
		$old_settings = array();
		$had_old_sets = 0;
		$group        = 0;
		$groups       = array();
		$to_recount   = array();
		$group_map    = array();
		$more_groups  = array();
		
		foreach ( $this->xml_array['settings_group']['setting'] as $k => $v )
		{
			$settingkeys[] = "'{$v['conf_key']['VALUE']}'";
		}
		
		$this->ipsclass->DB->build_query( array( 'select' => '*',
												 'from'   => 'conf_settings',
												 'where'  => "conf_key IN (".implode( ",", $settingkeys ).")",
										)	   );
		$this->ipsclass->DB->exec_query();
		
		if ( $this->ipsclass->DB->get_num_rows() )
		{
			$had_old_sets = 1;
			
			while ( $r = $this->ipsclass->DB->fetch_row() )
			{
				$old_settings[ $r['conf_key'] ] = ( $r['conf_value'] != '' && $r['conf_value'] != $r['conf_default'] ) ? $r['conf_value'] : '';
			}
		}
		
		$this->ipsclass->DB->do_delete( 'conf_settings', "conf_key IN (".implode( ",", $settingkeys ).")" );
		
		if ( $this->ipsclass->input['group'] )
		{
			$groups = explode( "|", $this->ipsclass->input['group'] );
		}
		
		foreach ( $groups as $k => $v )
		{
			if ( $v == 0 )
			{
				unset( $groups[ $k ] );
			}
			else
			{
				$to_recount[] = $v;
			}
		}
		
		if ( !$this->ipsclass->input['un'] )
		{
			foreach ( $this->xml_array['settings_group']['setting'] as $k => $v )
			{
				if ( $this->ipsclass->input['group'] )
				{
					$v['conf_group']['VALUE'] = ( count( $groups ) == 1 ) ? $groups[0] : $groups[ $v['conf_group']['VALUE'] ];
				}
				else
				{
					if ( !in_array( $v['conf_group']['VALUE'], array_keys( $group_map ) ) )
					{
						$query = $this->ipsclass->DB->build_and_exec_query( array( 'select' => 'conf_group',
																				   'from'   => 'conf_settings',
																				   'where'  => "conf_key='{$v['conf_group']['VALUE']}'",
																		  )		 );
						$v['conf_group']['VALUE'] = $query['conf_group'];
						
						if ( !in_array( $query['conf_group'], $to_recount ) )
						{
							$to_recount[] = $query['conf_group'];
						}
						
						$group_map[ $v['conf_group']['VALUE'] ] = $query['conf_group'];
					}
					else
					{
						$v['conf_group']['VALUE'] = $group_map[ $v['conf_group']['VALUE'] ];
					}
				}
								
				$this->_add_setting( $v );
			}
			
			if ( $had_old_sets == 1 && is_array( $old_settings ) )
			{
				foreach ( $old_settings as $k => $v )
				{
					if ( $v != '' )
					{
						$this->ipsclass->DB->do_update( 'conf_settings', array( 'conf_value' => $v ), "conf_key='{$k}'" );
					}
				}
			}
		}
		else
		{
			foreach ( $this->xml_array['settings_group']['setting'] as $k => $v )
			{
				if ( !is_numeric( $v['conf_group']['VALUE'] ) )
				{
					if ( !in_array( $v['conf_group']['VALUE'], $more_groups ) )
					{
						$more_groups[] = $v['conf_group']['VALUE'];
					}
				}
			}
			
			$this->ipsclass->DB->build_query( array( 'select' => 'conf_group',
													 'from'   => 'conf_settings',
													 'where'  => "conf_key IN ('".implode( "','", $more_groups )."')",
											)	   );
			$this->ipsclass->DB->exec_query();
			
			if ( $this->ipsclass->DB->get_num_rows() )
			{
				while ( $row = $this->ipsclass->DB->fetch_row() )
				{
					if ( $row['conf_group'] && !in_array( $row['conf_group'], $to_recount ) )
					{
						$to_recount[] = $row['conf_group'];
					}
				}
			}
		}
		
		if ( count( $to_recount ) )
		{
			foreach ( $to_recount as $k => $v )
			{
				$this->_recount_section( $v );
			}
		}
		
		$this->ipsclass->admin->redirect( "{$this->ipsclass->form_code}&amp;code=work&amp;mod={$this->ipsclass->input['mod']}&amp;step={$this->ipsclass->input['step']}{$uninstall}&amp;st={$this->ipsclass->input['st']}", "{$this->xml_array['mod_info']['title']['VALUE']}<br />{$this->tasks['settings']} {$object} {$operation}...." );
	}
	
	/*-------------------------------------------------------------------------*/
	// Components
	/*-------------------------------------------------------------------------*/
	
	function components()
	{
		$this->ipsclass->input['step']++;
		$uninstall = ( $this->ipsclass->input['un'] == 1 ) ? "&amp;un=1" : "";
		
		$object        = ( $this->tasks['components'] == 1 ) ? 'Component' : 'Components';
		$operation     = ( $this->ipsclass->input['un'] )    ? 'removed'   : 'created';
		
		$api = $this->ipsclass->load_class( IPS_API_PATH.'/api_components.php', 'api_components' );
		$api->path_to_ipb =  ROOT_PATH;
		$api->api_init();
		
		foreach ( $this->xml_array['components_group']['component'] as $k => $v )
		{
			$api->acp_component_remove( $v['com_section']['VALUE'] );
			
			if ( !$this->ipsclass->input['un'] )
			{
				$api->acp_component_insert( $v['com_section']['VALUE'], array( 'com_title'       => $v['com_title']['VALUE'],
																			   'com_author'      => $v['com_author']['VALUE'],
																			   'com_version'     => $v['com_version']['VALUE'],
																			   'com_url'         => $v['com_url']['VALUE'],
																			   'com_menu_data'   => $v['com_menu_data']['VALUE'],
																			   'com_enabled'     => $v['com_enabled']['VALUE'],
																			   'com_safemode'    => $v['com_safemode']['VALUE'],
																			   'com_section_key' => $v['com_section']['VALUE'],
																			   'com_description' => $v['com_description']['VALUE'],
																			   'com_url_uri'     => $v['com_url_uri']['VALUE'],
																			   'com_url_title'   => $v['com_url_title']['VALUE'],
										  )									 );
			}
		}
		
		$this->ipsclass->admin->redirect( "{$this->ipsclass->form_code}&amp;code=work&amp;mod={$this->ipsclass->input['mod']}&amp;step={$this->ipsclass->input['step']}{$uninstall}&amp;st={$this->ipsclass->input['st']}", "{$this->xml_array['mod_info']['title']['VALUE']}<br />{$this->tasks['components']} {$object} {$operation}...." );
	}
	
	/*-------------------------------------------------------------------------*/
	// Language strings
	/*-------------------------------------------------------------------------*/
	
	function languages()
	{
		$this->ipsclass->input['step']++;
		$uninstall = ( $this->ipsclass->input['un'] == 1 ) ? "&amp;un=1" : "";
		
		$object        = ( $this->tasks['languages'] == 1 ) ? 'Language String' : 'Language Strings';
		$operation     = ( $this->ipsclass->input['un'] )   ? 'removed'         : 'created';
		
		$api = $this->ipsclass->load_class( IPS_API_PATH.'/api_language.php', 'api_language' );
		$api->path_to_ipb =  ROOT_PATH;
		$api->api_init();
		
		foreach ( $this->xml_array['languages_group']['language'] as $k => $v )
		{
			if ( !$this->ipsclass->input['un'] )
			{
				$api->lang_add_strings( array( $v['key']['VALUE'] => $v['text']['VALUE'] ), $v['file']['VALUE'] );
			}
			else
			{
				foreach ( $this->ipsclass->cache['languages'] as $kk => $vv )
				{
					$lang = array();
					require( CACHE_PATH.'cache/lang_cache/'.$vv['ldir'].'/'.$v['file']['VALUE'].'.php' );
					
					unset( $lang[ $v['key']['VALUE'] ] );
					
					$start = "<?php\n\n".'$lang = array('."\n";
					
					foreach ( $lang as $kkk => $vvv )
					{
						$vvv    = preg_replace( "/\n{1,}$/", "", $vvv );
						$vvv 	= stripslashes( $vvv );
						$vvv	= preg_replace( '/"/', '\\"', $vvv );
						$start .= "\n'".$kkk."'  => \"".$vvv."\",";
					}
					
					$start .= "\n\n);\n\n?".">";
					
					if ( $fh = @fopen( CACHE_PATH.'cache/lang_cache/'.$vv['ldir'].'/'.$v['file']['VALUE'].'.php', 'w' ) )
					{
						@fwrite( $fh, $start );
						@fclose( $fh );
					}
					
					unset( $lang, $start, $fh );
				}
			}
		}
		
		$this->ipsclass->admin->redirect( "{$this->ipsclass->form_code}&amp;code=work&amp;mod={$this->ipsclass->input['mod']}&amp;step={$this->ipsclass->input['step']}{$uninstall}&amp;st={$this->ipsclass->input['st']}", "{$this->xml_array['mod_info']['title']['VALUE']}<br />{$this->tasks['languages']} {$object} {$operation}...." );
	}
	
	/*-------------------------------------------------------------------------*/
	// Skin templates
	/*-------------------------------------------------------------------------*/
	
	function templates()
	{
		$this->ipsclass->input['step']++;
		$uninstall = ( $this->ipsclass->input['un'] == 1 ) ? "&amp;un=1" : "";
		
		$object       = ( $this->tasks['templates'] == 1 ) ? 'Skin Template' : 'Skin Templates';
		$operation    = ( $this->ipsclass->input['un'] )   ? 'removed'       : 'created';
		
		foreach ( $this->xml_array['templates_group']['template'] as $k => $v )
		{
			$this->ipsclass->DB->do_delete( 'skin_templates', "set_id=1 AND group_name='".$v['group_name']['VALUE']."' AND func_name='".$v['func_name']['VALUE']."'" );
			
			if ( !$this->ipsclass->input['un'] )
			{
				$this->_add_template( $v );
			}
		}
		
		$this->ipsclass->admin->redirect( "{$this->ipsclass->form_code}&amp;code=work&amp;mod={$this->ipsclass->input['mod']}&amp;step={$this->ipsclass->input['step']}{$uninstall}&amp;st={$this->ipsclass->input['st']}", "{$this->xml_array['mod_info']['title']['VALUE']}<br />{$this->tasks['templates']} {$object} {$operation}...." );
	}
	
	/*-------------------------------------------------------------------------*/
	// Task Manager tasks
	/*-------------------------------------------------------------------------*/
	
	function tasks()
	{
		$this->ipsclass->input['step']++;
		$uninstall = ( $this->ipsclass->input['un'] == 1 ) ? "&amp;un=1" : "";
		
		$object    = ( $this->tasks['tasks'] == 1 )   ? 'Task'    : 'Tasks';
		$operation = ( $this->ipsclass->input['un'] ) ? 'removed' : 'created';
		$taskkeys  = array();
		
		foreach ( $this->xml_array['tasks_group']['task'] as $k => $v )
		{
			$taskkeys[] = "'{$v['task_key']['VALUE']}'";
		}
		
		$this->ipsclass->DB->do_delete( 'task_manager', "task_key IN (".implode( ",", $taskkeys ).")" );
		
		if ( !$this->ipsclass->input['un'] )
		{
			foreach ( $this->xml_array['tasks_group']['task'] as $k => $v )
			{
				$this->_add_task( $v );
			}
		}
		
		$this->ipsclass->admin->redirect( "{$this->ipsclass->form_code}&amp;code=work&amp;mod={$this->ipsclass->input['mod']}&amp;step={$this->ipsclass->input['step']}{$uninstall}&amp;st={$this->ipsclass->input['st']}", "{$this->xml_array['mod_info']['title']['VALUE']}<br />{$this->tasks['tasks']} {$object} {$operation}...." );
	}
	
	/*-------------------------------------------------------------------------*/
	// Help Files
	/*-------------------------------------------------------------------------*/
	
	function helps()
	{
		$this->ipsclass->input['step']++;
		$uninstall = ( $this->ipsclass->input['un'] == 1 ) ? "&amp;un=1" : "";
		
		$object    = ( $this->tasks['helps'] == 1 )   ? 'Help File' : 'Help Files';
		$operation = ( $this->ipsclass->input['un'] ) ? 'removed'   : 'created';
		$helpkeys  = array();
		
		foreach ( $this->xml_array['helps_group']['help'] as $k => $v )
		{
			$helpkeys[] = "'{$v['title']['VALUE']}'";
		}
		
		$this->ipsclass->DB->do_delete( 'faq', "title IN (".implode( ",", $helpkeys ).")" );
		
		if ( !$this->ipsclass->input['un'] )
		{
			foreach ( $this->xml_array['helps_group']['help'] as $k => $v )
			{
				$this->_add_help( $v );
			}
		}
		
		$this->ipsclass->admin->redirect( "{$this->ipsclass->form_code}&amp;code=work&amp;mod={$this->ipsclass->input['mod']}&amp;step={$this->ipsclass->input['step']}{$uninstall}{$group}&amp;st={$this->ipsclass->input['st']}", "{$this->xml_array['mod_info']['title']['VALUE']}<br />{$this->tasks['helps']} {$object} {$operation}...." );
	}
	
	/*-------------------------------------------------------------------------*/
	// ACP Help Entries
	/*-------------------------------------------------------------------------*/
	
	function acp_helps()
	{
		$this->ipsclass->input['step']++;
		$uninstall = ( $this->ipsclass->input['un'] == 1 ) ? "&amp;un=1" : "";
		
		$object    = ( $this->tasks['acp_helps'] == 1 ) ? 'ACP Help Entry' : 'ACP Help Entries';
		$operation = ( $this->ipsclass->input['un'] )   ? 'removed'        : 'created';
		$helpkeys  = array();
		
		foreach ( $this->xml_array['acp_helps_group']['acp_help'] as $k => $v )
		{
			$helpkeys[] = "'{$v['page_key']['VALUE']}'";
		}
		
		$this->ipsclass->DB->do_delete( 'acp_help', "page_key IN (".implode( ",", $helpkeys ).")" );
		
		if ( !$this->ipsclass->input['un'] )
		{
			foreach ( $this->xml_array['acp_helps_group']['acp_help'] as $k => $v )
			{
				$this->_add_acp_help( $v );
			}
		}
		
		$this->ipsclass->admin->redirect( "{$this->ipsclass->form_code}&amp;code=work&amp;mod={$this->ipsclass->input['mod']}&amp;step={$this->ipsclass->input['step']}{$uninstall}{$group}&amp;st={$this->ipsclass->input['st']}", "{$this->xml_array['mod_info']['title']['VALUE']}<br />{$this->tasks['acp_helps']} {$object} {$operation}...." );
	}
	
	/*-------------------------------------------------------------------------*/
	// Database tables
	/*-------------------------------------------------------------------------*/
	
	function tables()
	{
		$this->ipsclass->input['step']++;
		$uninstall = ( $this->ipsclass->input['un'] == 1 ) ? "&amp;un=1" : "";
		
		$object     = ( $this->tasks['tables'] == 1 )  ? 'Database Table' : 'Database Tables';
		$operation  = ( $this->ipsclass->input['un'] ) ? 'dropped'        : 'created';
		$old_data   = array();
		$new_fields = array();
		
		foreach ( $this->xml_array['tables_group']['table'] as $k => $v )
		{
			$this->ipsclass->DB->sql_drop_table( $v['name']['VALUE']."_bak" );
			
			if ( !$this->ipsclass->input['un'] && in_array( SQL_PREFIX.$v['name']['VALUE'], $this->ipsclass->DB->get_table_names() ) )
			{
				$this->ipsclass->DB->query( "RENAME TABLE ".SQL_PREFIX.$v['name']['VALUE']." TO ".SQL_PREFIX.$v['name']['VALUE']."_bak;" );
				$this->ipsclass->DB->cached_tables[] = SQL_PREFIX.$v['name']['VALUE']."_bak";
			}
			
			$this->ipsclass->DB->sql_drop_table( $v['name']['VALUE'] );
			
			if ( !$this->ipsclass->input['un'] )
			{
				$this->ipsclass->DB->query( $this->fix_create_table( "CREATE TABLE IF NOT EXISTS ".SQL_PREFIX.$v['name']['VALUE']." (".$v['data']['VALUE'].") TYPE=".$v['type']['VALUE'] ) );
				
				if ( in_array( SQL_PREFIX.$v['name']['VALUE']."_bak", $this->ipsclass->DB->get_table_names() ) )
				{
					$this->ipsclass->DB->build_query( array( 'select' => '*',
															 'from'   => $v['name']['VALUE']."_bak",
													)	   );
					$this->ipsclass->DB->exec_query();
					
					if ( $this->ipsclass->DB->get_num_rows() )
					{
						while ( $r = $this->ipsclass->DB->fetch_row() )
						{
							$old_data[ $v['name']['VALUE'] ][] = $r;
						}
						
						$this->ipsclass->DB->query( "SHOW COLUMNS FROM ".SQL_PREFIX.$v['name']['VALUE'].";" );
						
						if ( $this->ipsclass->DB->get_num_rows() )
						{
							while ( $row = $this->ipsclass->DB->fetch_row() )
							{
								$new_fields[ $v['name']['VALUE'] ][] = $row['Field'];
							}
						}
						
						foreach ( $old_data[ $v['name']['VALUE'] ] as $kk => $vv )
						{
							$insert = array();
							
							foreach ( $vv as $field => $value)
							{
								if ( in_array( $field, $new_fields[ $v['name']['VALUE'] ] ) )
								{
									$insert[ $field ] = $this->ipsclass->txt_safeslashes( $value );
								}
							}
							
							if ( count( $insert ) )
							{
								$this->ipsclass->DB->do_insert( $v['name']['VALUE'], $insert );
							}
						}
					}
				}
				
				$this->ipsclass->DB->sql_drop_table( $v['name']['VALUE']."_bak" );
			}
		}
		
		$this->ipsclass->admin->redirect( "{$this->ipsclass->form_code}&amp;code=work&amp;mod={$this->ipsclass->input['mod']}&amp;step={$this->ipsclass->input['step']}{$uninstall}&amp;st={$this->ipsclass->input['st']}", "{$this->xml_array['mod_info']['title']['VALUE']}<br />{$this->tasks['tables']} {$object} {$operation}...." );
	}
	
	/*-------------------------------------------------------------------------*/
	// Database alters
	/*-------------------------------------------------------------------------*/
	
	function alters()
	{
		$this->ipsclass->input['step']++;
		$uninstall = ( $this->ipsclass->input['un'] == 1 ) ? "&amp;un=1" : "";
		
		$object       = ( $this->tasks['alters'] == 1 )  ? 'Database Field' : 'Database Fields';
		$operation    = ( $this->ipsclass->input['un'] ) ? 'dropped'        : 'added';
		
		foreach ( $this->xml_array['alters_group']['alter'] as $k => $v )
		{
			if ( !$this->ipsclass->input['un'] && $v['alter_type']['VALUE'] == 'add' )
			{
				if ( !$this->ipsclass->DB->field_exists( "{$v['field_name']['VALUE']}", "{$v['table']['VALUE']}" ) )
				{
					$this->ipsclass->DB->sql_add_field( "{$v['table']['VALUE']}", "{$v['field_name']['VALUE']}", "{$v['field_type']['VALUE']}", "{$v['field_default']['VALUE']}" );
				}
			}
			else
			{
				if ( $this->ipsclass->DB->field_exists( "{$v['field_name']['VALUE']}", "{$v['table']['VALUE']}" ) )
				{
					$this->ipsclass->DB->sql_drop_field( "{$v['table']['VALUE']}", "{$v['field_name']['VALUE']}" );
				}
			}
		}
		
		$this->ipsclass->admin->redirect( "{$this->ipsclass->form_code}&amp;code=work&amp;mod={$this->ipsclass->input['mod']}&amp;step={$this->ipsclass->input['step']}{$uninstall}&amp;st={$this->ipsclass->input['st']}", "{$this->xml_array['mod_info']['title']['VALUE']}<br />{$this->tasks['alters']} {$object} {$operation}...." );
	}
	
	/*-------------------------------------------------------------------------*/
	// Database inserts
	/*-------------------------------------------------------------------------*/
	
	function inserts()
	{
		$this->ipsclass->input['step']++;
		$uninstall = ( $this->ipsclass->input['un'] == 1 ) ? "&amp;un=1" : "";
		
		$object       = ( $this->tasks['inserts'] == 1 ) ? 'Database Row' : 'Database Rows';
		$operation    = ( $this->ipsclass->input['un'] ) ? 'removed'      : 'inserted';
		
		foreach ( $this->xml_array['inserts_group']['insert'] as $k => $v )
		{
			if ( !$this->ipsclass->input['un'] )
			{
				unset( $f );
				
				foreach ( $v['fields'] as $kk => $vv )
				{
					if ( strtolower( $kk ) == 'value' )
					{
						continue;
					}
					
					$f[ $kk ] = $vv['VALUE'];
				}
				
				$this->ipsclass->DB->build_and_exec_query( array( 'select' => '*',
																  'from'   => $v['table']['VALUE'],
																  'where'  => $v['delete_key']['VALUE'],
														 )		);
				
				if ( !$this->ipsclass->DB->get_num_rows() )
				{
					$this->ipsclass->DB->do_insert( $v['table']['VALUE'], $f );
				}
			}
			else
			{
				if ( in_array( SQL_PREFIX.$v['table']['VALUE'], $this->ipsclass->DB->get_table_names() ) )
				{
					$this->ipsclass->DB->do_delete( $v['table']['VALUE'], $v['delete_key']['VALUE'] );
				}
			}
		}
		
		$this->ipsclass->admin->redirect( "{$this->ipsclass->form_code}&amp;code=work&amp;mod={$this->ipsclass->input['mod']}&amp;step={$this->ipsclass->input['step']}{$uninstall}&amp;st={$this->ipsclass->input['st']}", "{$this->xml_array['mod_info']['title']['VALUE']}<br />{$this->tasks['inserts']} {$object} {$operation}...." );
	}
	
	/*-------------------------------------------------------------------------*/
	// Database updates
	/*-------------------------------------------------------------------------*/
	
	function updates()
	{
		$this->ipsclass->input['step']++;
		$uninstall = ( $this->ipsclass->input['un'] == 1 ) ? "&amp;un=1" : "";
		
		$object       = ( $this->tasks['updates'] == 1 ) ? 'Database Update' : 'Database Updates';
		$operation    = ( $this->ipsclass->input['un'] ) ? 'reverted'        : 'executed';
		
		foreach ( $this->xml_array['updates_group']['update'] as $k => $v )
		{
			if ( !$this->ipsclass->input['un'] )
			{
				$this->ipsclass->DB->do_update( "{$v['table']['VALUE']}", array( "{$v['key']['VALUE']}" => "{$v['new_value']['VALUE']}" ), "{$v['where']['VALUE']}" );
			}
			else
			{
				if ( $v['old_value'] )
				{
					$this->ipsclass->DB->do_update( "{$v['table']['VALUE']}", array( "{$v['key']['VALUE']}" => "{$v['old_value']['VALUE']}" ), "{$v['where']['VALUE']}" );
				}
			}
		}
		
		$this->ipsclass->admin->redirect( "{$this->ipsclass->form_code}&amp;code=work&amp;mod={$this->ipsclass->input['mod']}&amp;step={$this->ipsclass->input['step']}{$uninstall}&amp;st={$this->ipsclass->input['st']}", "{$this->xml_array['mod_info']['title']['VALUE']}<br />{$this->tasks['updates']} {$object} {$operation}...." );
	}
	
	/*-------------------------------------------------------------------------*/
	// Custom scripts
	/*-------------------------------------------------------------------------*/
	
	function customs()
	{
		$custom = $this->ipsclass->load_class( ROOT_PATH.'mod_install/'.$this->xml_array['customs_group']['custom'][0]['script_name']['VALUE'].'.php', $this->xml_array['customs_group']['custom'][0]['script_name']['VALUE'] );
		$custom->xml_array =& $this->xml_array;
		
		if ( $this->ipsclass->input['un'] )
		{
			$custom->uninstall();
		}
		else
		{
			$custom->install();
		}
	}
	
	/*-------------------------------------------------------------------------*/
	// Recache our caches
	/*-------------------------------------------------------------------------*/
	
	function recache()
	{
		$this->ipsclass->input['step']++;
		$uninstall = ( $this->ipsclass->input['un'] == 1 ) ? "&amp;un=1" : "";
		
		//-----------------------------------------
		// Components
		//-----------------------------------------
		
		$components = $this->ipsclass->load_class( ROOT_PATH.'sources/action_admin/components.php', 'ad_components' );
		$components->components_rebuildcache();
		
		//-----------------------------------------
		// Forum Cache
		//-----------------------------------------
		
		$this->ipsclass->update_forum_cache();
		
		//-----------------------------------------
		// Group Cache
		//-----------------------------------------
		
		$this->ipsclass->cache['group_cache'] = array();
		
		$this->ipsclass->DB->build_query( array( 'select' => '*',
												 'from'   => 'groups'
										)      );
		$this->ipsclass->DB->exec_query();
		
		while ( $i = $this->ipsclass->DB->fetch_row() )
		{
			$this->ipsclass->cache['group_cache'][ $i['g_id'] ] = $i;
		}
		
		$this->ipsclass->update_cache( array( 'name' => 'group_cache', 'array' => 1, 'deletefirst' => 1 ) );
		
		//-----------------------------------------
		// Settings
		//-----------------------------------------
		
		$settings = $this->ipsclass->load_class( ROOT_PATH.'sources/action_admin/settings.php', 'ad_settings' );
		$settings->setting_rebuildcache();
		
		$this->ipsclass->admin->redirect( "{$this->ipsclass->form_code}&amp;code=work&amp;mod={$this->ipsclass->input['mod']}&amp;step={$this->ipsclass->input['step']}{$uninstall}&amp;st={$this->ipsclass->input['st']}", "{$this->xml_array['mod_info']['title']['VALUE']}<br />IPB caches updated...." );
	}
	
	/*-------------------------------------------------------------------------*/
	// Rebuild skin caches
	/*-------------------------------------------------------------------------*/
	
	function templatesrecache()
	{
		$uninstall = ( $this->ipsclass->input['un'] == 1 ) ? "&amp;un=1" : "";
		
		$justdone = intval( $this->ipsclass->input['justdone'] );
		$justdone = $justdone ? $justdone : 1;
		
		$s = $this->ipsclass->DB->build_and_exec_query( array( 'select' => '*',
															   'from'   => 'skin_sets',
															   'where'  => 'set_skin_set_id > '.$justdone,
															   'order'  => 'set_skin_set_id',
															   'limit'  => array( 0, 1 ),
													  )		 );
		
		if ( $s['set_skin_set_id'] )
		{
			$this->ipsclass->cache_func->_rebuild_all_caches( array( $s['set_skin_set_id'] ) );
			
			$this->ipsclass->admin->redirect( "{$this->ipsclass->form_code}&amp;code=work&amp;mod={$this->ipsclass->input['mod']}&amp;step={$this->ipsclass->input['step']}&amp;justdone={$s['set_skin_set_id']}{$uninstall}&amp;st={$this->ipsclass->input['st']}", "{$this->xml_array['mod_info']['title']['VALUE']}<br />Rebuilt the '{$s['set_name']}' skin cache..." );
		}
		else
		{
			$this->ipsclass->input['step']++;
						
			$this->ipsclass->admin->redirect( "{$this->ipsclass->form_code}&amp;code=work&amp;mod={$this->ipsclass->input['mod']}&amp;step={$this->ipsclass->input['step']}{$uninstall}&amp;st={$this->ipsclass->input['st']}", "{$this->xml_array['mod_info']['title']['VALUE']}<br />No more skins to rebuild..." );
		}
	}
	
	/*-------------------------------------------------------------------------*/
	// Finish it up!
	/*-------------------------------------------------------------------------*/
	
	function finish()
	{
		$type = ( $this->ipsclass->input['un'] == 1 ) ? 'uninstallation' : 'installation';
		
		if ( $type == 'installation' )
		{
			$this->ipsclass->DB->do_update( 'installed_mods', array( 'm_version'  => $this->xml_array['mod_info']['version']['VALUE'],
																	 'm_author'   => $this->xml_array['mod_info']['author']['VALUE'],
																	 'm_website'  => $this->xml_array['mod_info']['website']['VALUE'],
																	 'm_finished' => 1,
																   ), "m_name='".$this->xml_array['mod_info']['title']['VALUE']."'" );
		}
		else
		{
			$this->ipsclass->DB->do_delete( 'installed_mods', "m_name='".$this->xml_array['mod_info']['title']['VALUE']."'" );
			
			$this->ipsclass->DB->sql_optimize_table( 'installed_mods' );
		}
		
		$this->ipsclass->main_msg = 'Modification '.$type.' complete...';
		
		$this->view_mods();
	}
	
	/*-------------------------------------------------------------------------*/
	// Add a section
	/*-------------------------------------------------------------------------*/
	
	function _add_section( $data=array() )
	{
		$section = array();
		
		$section['conf_title_title']   = $data['conf_title_title']['VALUE'];
		$section['conf_title_desc']    = $data['conf_title_desc']['VALUE'];
		$section['conf_title_count']   = 0;
		$section['conf_title_noshow']  = $data['conf_title_noshow']['VALUE'];
		$section['conf_title_keyword'] = $data['conf_title_keyword']['VALUE'];
		$section['conf_title_module']  = $data['conf_title_module']['VALUE'];
		
		$this->ipsclass->DB->do_insert( 'conf_settings_titles', $section );
		
		return $this->ipsclass->DB->get_insert_id();
	}
	
	/*-------------------------------------------------------------------------*/
	// Add a setting
	/*-------------------------------------------------------------------------*/
	
	function _add_setting( $data=array() )
	{
		$setting = array();
		
		$setting['conf_title']       = $data['conf_title']['VALUE'];
		$setting['conf_description'] = $data['conf_description']['VALUE'];
		$setting['conf_group']       = $data['conf_group']['VALUE'];
		$setting['conf_type']        = $data['conf_type']['VALUE'];
		$setting['conf_key']         = $data['conf_key']['VALUE'];
		$setting['conf_default']     = $data['conf_default']['VALUE'];
		$setting['conf_extra']       = $data['conf_extra']['VALUE'];
		$setting['conf_evalphp']     = $data['conf_evalphp']['VALUE'];
		$setting['conf_protected']   = 1;
		$setting['conf_position']    = $data['conf_position']['VALUE'];
		$setting['conf_start_group'] = $data['conf_start_group']['VALUE'];
		$setting['conf_end_group']   = isset( $data['conf_end_group']['VALUE'] ) ? $data['conf_end_group']['VALUE'] : 0;
		$setting['conf_help_key']    = $data['conf_help_key']['VALUE'];
		$setting['conf_add_cache']   = 1;
		
		if ( !$this->ipsclass->DB->field_exists( 'conf_help_key', 'conf_settings' ) )
		{
			unset( $setting['conf_help_key'] );
		}
		
		$this->ipsclass->DB->do_insert( 'conf_settings', $setting );
	}
	
	/*-------------------------------------------------------------------------*/
	// Recount a section
	/*-------------------------------------------------------------------------*/
	
	function _recount_section( $group )
	{
		$conf = $this->ipsclass->DB->build_and_exec_query( array( 'select' => 'COUNT(*) AS count',
																  'from'   => 'conf_settings',
																  'where'  => 'conf_group='.$group,
														 )		);
		
		$this->ipsclass->DB->do_update( 'conf_settings_titles', array( 'conf_title_count' => intval( $conf['count'] ) ), 'conf_title_id='.$group );
	}
	
	/*-------------------------------------------------------------------------*/
	// Add a skin template
	/*-------------------------------------------------------------------------*/
	
	function _add_template( $data=array() )
	{
		$template = array();
		
		$template['set_id']                = 1;
		$template['group_name']            = $data['group_name']['VALUE'];
		$template['section_content']       = $data['section_content']['VALUE'];
		$template['func_name']             = $data['func_name']['VALUE'];
		$template['func_data']             = $data['func_data']['VALUE'];
		$template['updated']               = time();
		$template['group_names_secondary'] = $data['group_names_secondary']['VALUE'];
		
		if ( !$this->ipsclass->DB->field_exists( 'group_names_secondary', 'skin_templates' ) )
		{
			unset( $template['group_names_secondary'] );
		}
		
		$this->ipsclass->DB->do_insert( 'skin_templates', $template );
	}
	
	/*-------------------------------------------------------------------------*/
	// Add a task
	/*-------------------------------------------------------------------------*/
	
	function _add_task( $data=array() )
	{
		$taskfunc = $this->ipsclass->load_class( ROOT_PATH.'sources/lib/func_taskmanager.php', 'func_taskmanager' );
		
		$task = array();
		
		$task['task_title']       = $data['task_title']['VALUE'];
		$task['task_file']        = $data['task_file']['VALUE'];
		$task['task_week_day']    = $data['task_week_day']['VALUE'];
		$task['task_month_day']   = $data['task_month_day']['VALUE'];
		$task['task_hour']        = $data['task_hour']['VALUE'];
		$task['task_minute']      = $data['task_minute']['VALUE'];
		$task['task_cronkey']     = md5( microtime() );
		$task['task_log']         = $data['task_log']['VALUE'];
		$task['task_description'] = $data['task_description']['VALUE'];
		$task['task_enabled']     = $data['task_enabled']['VALUE'];
		$task['task_key']         = $data['task_key']['VALUE'];
		$task['task_safemode']    = $data['task_safemode']['VALUE'];
		$task['task_next_run']    = $taskfunc->generate_next_run( $task );
		
		$this->ipsclass->DB->do_insert( 'task_manager', $task );
		
		$taskfunc->save_next_run_stamp();
	}
	
	/*-------------------------------------------------------------------------*/
	// Add a help file
	/*-------------------------------------------------------------------------*/
	
	function _add_help( $data=array() )
	{
		$help = array();
		
		$help['title']       = $data['title']['VALUE'];
		$help['text']        = $data['text']['VALUE'];
		$help['description'] = $data['description']['VALUE'];
		$help['position']    = $data['position']['VALUE'];
		
		$this->ipsclass->DB->do_insert( 'faq', $help );
	}
	
	/*-------------------------------------------------------------------------*/
	// Add an ACP help entry
	/*-------------------------------------------------------------------------*/
	
	function _add_acp_help( $data = array() )
	{
		$acp_help = array();
		
		$acp_help['is_setting']     = $data['is_setting']['VALUE'];
		$acp_help['page_key']       = $data['page_key']['VALUE'];
		$acp_help['help_title']     = $data['help_title']['VALUE'];
		$acp_help['help_body']      = $data['help_body']['VALUE'];
		$acp_help['help_mouseover'] = $data['help_mouseover']['VALUE'];
					
		$this->ipsclass->DB->do_insert( 'acp_help', $acp_help );
	}
}

?>