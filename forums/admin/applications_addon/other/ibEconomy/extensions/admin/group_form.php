<?php

/**
 * (e32) ibEconomy
 * Group Form
 * @ ACP Edit Group
 * + Edit Group ibEco Perms
 */

class admin_group_form__ibEconomy implements admin_group_form
{
	/**
	* Tab name
	*/
	public $tab_name = "";
	
	/**
	* Returns content for the page
	*/
	public function getDisplayContent( $group=array(), $tabsUsed = 4 )
	{
		#Load html template
		$this->html = ipsRegistry::getClass('output')->loadTemplate('cp_skin_ibEconomy_group_form', 'ibEconomy' );
		
		#load lang		
		ipsRegistry::getClass('class_localization')->loadLanguageFile( array( 'admin_ibEconomy' ), 'ibEconomy' );
		
		#return display stuff
		return array( 'tabs' => $this->html->acp_group_form_tabs( $group, ( $tabsUsed + 1 ) ), 'content' => $this->html->acp_group_form_main( $group, ( $tabsUsed + 1 ) ), 'tabsUsed' => 1 );
	}

	/**
	* Process the entries for saving and return
	*/
	public function getForSave()
	{
		$return = array( 
				'g_eco'					=> ipsRegistry::$request['g_eco'],
				'g_eco_bank' 			=> ipsRegistry::$request['g_eco_bank'],
				'g_eco_welfare'	 		=> ipsRegistry::$request['g_eco_welfare'],
				'g_eco_loan' 			=> ipsRegistry::$request['g_eco_loan'],
				'g_eco_stock' 			=> ipsRegistry::$request['g_eco_stock'],
				'g_eco_cc' 				=> ipsRegistry::$request['g_eco_cc'],
				'g_eco_lt' 				=> ipsRegistry::$request['g_eco_lt'],
				'g_eco_frm_ptsx' 		=> ipsRegistry::$request['g_eco_frm_ptsx'],
				'g_eco_max_pts' 		=> ipsRegistry::$request['g_eco_max_pts'],
				'g_eco_max_cc_debt'		=> ipsRegistry::$request['g_eco_max_cc_debt'],
				'g_eco_max_loan_debt'	=> ipsRegistry::$request['g_eco_max_loan_debt'],
				'g_eco_bank_max' 		=> ipsRegistry::$request['g_eco_bank_max'],
				'g_eco_stock_max' 		=> ipsRegistry::$request['g_eco_stock_max'],
				'g_eco_lt_max' 			=> ipsRegistry::$request['g_eco_lt_max'],
				'g_eco_cash_adv_max'	=> ipsRegistry::$request['g_eco_cash_adv_max'],
				'g_eco_bal_trnsfr_max'	=> ipsRegistry::$request['g_eco_bal_trnsfr_max'],
				'g_eco_welfare_max' 	=> ipsRegistry::$request['g_eco_welfare_max'],
				'g_eco_transaction' 	=> ipsRegistry::$request['g_eco_transaction'],
				'g_eco_asset' 			=> ipsRegistry::$request['g_eco_asset'],
				'g_eco_shopitem' 		=> ipsRegistry::$request['g_eco_shopitem'],
				'g_eco_edit_pts' 		=> ipsRegistry::$request['g_eco_edit_pts'],
				'g_eco_lottery' 		=> ipsRegistry::$request['g_eco_lottery'],
				'g_eco_lottery_tix' 	=> ipsRegistry::$request['g_eco_lottery_tix'],
				'g_eco_lottery_odds' 	=> ipsRegistry::$request['g_eco_lottery_odds']
		);
		
	
		$return = array_merge($return, $this->getPluginGroupFieldForSaving());

		return $return;
	}
	
	public function getPluginGroupFieldForSaving()
	{
		#master ibEconomy Class
		if ( ! ipsRegistry::isClassLoaded( 'ecoclass' ) )
		{
			require_once( IPSLib::getAppDir( 'ibEconomy' ) . "/sources/ecoclass.php" );
			$ecoclass = new class_ibEconomy( ipsRegistry::instance() );
		}
		
		#ACP ibEconomy Class
		if ( ! ipsRegistry::isClassLoaded( 'class_ibEco_CP' ) )
		{
			require_once( IPSLib::getAppDir( 'ibEconomy' ) . "/sources/classes/class_ibEco_CP.php" );
			$ecoAcpClass = new class_ibEco_CP( ipsRegistry::instance() );
		}
		
		$moreGroupSettings = $ecoAcpClass->buildPluginGroupSettingsSaver($ecoclass->plugins ? $ecoclass->plugins : true);

		return $moreGroupSettings;	
	}
}