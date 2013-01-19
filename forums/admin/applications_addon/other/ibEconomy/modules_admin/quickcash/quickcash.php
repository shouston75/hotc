<?php

/**
 * (e32) ibEconomy
 * Admin Module: Quick Cash
 * @ ACP
 * + Loan Settings
 * + Welfare Settings
 */

if ( ! defined( 'IN_ACP' ) )
{
	print "<h1>Incorrect access</h1>You cannot access this file directly. If you have recently upgraded, make sure you upgraded 'admin.php'.";
	exit();
}

class admin_ibEconomy_quickcash_quickcash extends ipsCommand
{
	public $html;
	public $registry;
	
	public $form_code;
	public $form_code_js;
	
	/**
	 * Main execution method
	 */	
	public function doExecute( ipsRegistry $registry )
	{
		#load templates
		$this->html         	= $this->registry->output->loadTemplate( 'cp_skin_ibEconomy');

		#load langs
		ipsRegistry::getClass('class_localization')->loadLanguageFile( array( 'admin_ibEconomy' ) );
		ipsRegistry::getClass('class_localization')->loadLanguageFile( array( 'admin_tools' ), 'core' );
		ipsRegistry::getClass('class_localization')->loadLanguageFile( array( 'admin_main' ) );
		
		#do form codes
		$this->form_code 		= $this->html->form_code    = 'module=quickcash&amp;section=quickcash';
		$this->form_code_js 	= $this->html->form_code_js = 'module=quickcash&section=quickcash';
		
		#saved message
		if( $this->request['saved'] == 1 )
		{
			$this->registry->output->global_message = $this->lang->words['s_updated'];
		}
		
		#switcharoo
		switch( $this->request['do'] )
		{		
			//******Welfare Settings******//	
			case 'welfare_settings':
				$this->registry->getClass('class_permissions')->checkPermissionAutoMsg( 'ibEconomy_welfare_settings_edit' );
			break;
			
			//******Lottery Settings******//				
			case 'lottery_settings':
				$this->registry->getClass('class_permissions')->checkPermissionAutoMsg( 'ibEconomy_lottery_settings_edit' );
			break;	
			
			//******Loan Settings******//				
			case 'loan_settings':
			default:
				$this->registry->getClass('class_permissions')->checkPermissionAutoMsg( 'ibEconomy_loan_settings_edit' );
			break;
		}
		
		#grab settings stuff
		$this->registry->class_ibEco_CP->doSettings( $this->request['do'], $this->form_code );

		#footer
		$this->registry->output->html .= $this->html->footer();			
		
		#output
		$this->registry->output->html_main .= $this->registry->output->global_template->global_frame_wrapper();
		$this->registry->output->sendOutput();
	}
}