<?php

class admin_iArcade_settings_settings extends ipsCommand
{
	public $html;
	public $registry;
	
	private $form_code;
	private $form_code_js;
	
	public function doExecute( ipsRegistry $registry )
	{
		$this->html         = $this->registry->output->loadTemplate( 'cp_skin_tools', 'core' );	
		
		$this->form_code 	= $this->html->form_code    = 'module=settings&amp;section=settings';
		$this->form_code_js = $this->html->form_code_js = 'module=settings&section=settings';
		
		ipsRegistry::getClass('class_localization')->loadLanguageFile( array( 'admin_tools' ), 'core' );
		ipsRegistry::getClass('class_localization')->loadLanguageFile( array( 'admin_main' ) );
		
		if( $this->request['saved'] == 1 )
		{
			$this->registry->output->global_message = $this->lang->words['s_updated'];
		}
		

		if( $this->request['guess'] == 1 )
		{

$udir = $this->settings['upload_dir'];
$path = str_replace("uploads", "", $udir);
$pathr = "{$path}admin/applications_addon/other/iArcade/games/all";

$uurl = $this->settings['upload_url'];
$url = str_replace("uploads", "", $uurl);
$urlr = "{$url}admin/applications_addon/other/iArcade/games/all";



			$this->registry->output->global_message = "<b>Based on other configuration of your board, it appears that the correct WEB PATH should be: <br> $urlr <br><br> Based on other configuration of your board, it appears that the correct UPLOAD PATH should be: <br> $pathr <br><br></b>Please note, these may not be correct and if you wish to use a different folder structure to score games, you should adjust them both accordingly. <br>Please note that the tars directory, as well as all the subdirectories of the games directory need to have proper CHMOD permission (usually 777)<br> If you are having trouble configuring this, please post in the iArcade Support Community.";
		}




		if( $this->request['permcheck'] == 1 )
		{

			$this->registry->output->global_message = " It appears you do not have sufficent read/write permission in your games directory to continue to import this game. Please check for 777 permission before you continue. ";
		}


		$this->showSettings( $this->request['do'] );

			
	$this->registry->output->html_main .=  "If you are having issues configuring iArcade paths, please <a href='index.php?adsess={$_GET[adsess]}&app=iArcade&module=settings&section=settings&guess=1'/>Click Here</a> to open help.<br>";



		$this->registry->output->html_main .= $this->registry->output->global_template->global_frame_wrapper();
		$this->registry->output->sendOutput();
	}
	

	
	private function showSettings( $module )
	{
		require_once( IPSLib::getAppDir( 'core' ).'/modules_admin/tools/settings.php' );
		$settings =  new admin_core_tools_settings();
		$settings->makeRegistryShortcuts( $this->registry );
				
		$settings->html			= $this->html;
		$settings->form_code	= $settings->html->form_code    = $this->form_code;
		$settings->form_code_js	= $settings->html->form_code_js = $this->form_code_js;

		$this->request['conf_title_keyword'] = 'iArcade';
		$settings->return_after_save     = $this->settings['base_url'] . $this->form_code . '&saved=1';
		$settings->_viewSettings();
	}




}


