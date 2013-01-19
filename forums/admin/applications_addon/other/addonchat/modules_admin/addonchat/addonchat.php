<?php

if (! defined ( 'IN_ACP' )) {
	echo "<h1>Uh oh!</h1>This file may not be accessed directly. If you have recently upgraded, make sure you that you have uploaded all relevant files.";
	die ();
}

class admin_addonchat_addonchat_addonchat extends ipsCommand {
	
	public function doExecute(ipsRegistry $registry) {
		
		switch ($this->request ['do']) {
			case 'addonchatsave' :
				$this->_addonchatSave ();
				break;
			default :
			case 'configure' :
				$this->_addonchatConfigure ();
				break;
		}
		
		$this->registry->getClass ( 'output' )->html_main .= $this->registry->getClass ( 'output' )->global_template->global_frame_wrapper ();
		$this->registry->getClass ( 'output' )->sendOutput ();
	}
	
	private function _addonchatConfigure() {
		require_once (IPSLib::getAppDir ( 'core' ) . '/modules_admin/settings/settings.php');
		$settings = new admin_core_settings_settings();
		$settings->makeRegistryShortcuts ( $this->registry );
		$settings->html = $this->registry->output->loadTemplate ( 'cp_skin_settings', 'core' );
		ipsRegistry::getClass ( 'class_localization' )->loadLanguageFile ( array ('admin_tools' ), 'core' );		
		$this->request ['conf_title_keyword'] = 'addonchat';
		$settings->return_after_save = $this->settings ['base_url'] . $this->form_code . '&do=configure';
		$settings->_viewSettings ();
	}
	private function _addonchatSave() {
		require_once (IPSLib::getAppDir ( 'core' ) . '/modules_admin/settings/settings.php');
		$settings = new admin_core_settings_settings();
		$settings->makeRegistryShortcuts ( $this->registry );
        $settings->html = $this->registry->output->loadTemplate( 'cp_skin_settings', 'core' );
        ipsRegistry::getClass('class_localization')->loadLanguageFile( array( 'admin_tools' ), 'core' );    
        $settings->settingsRebuildCache();
        $this->_addonchatConfigure();
   }

}
