<?php

/**
 * (e32) ibEconomy
 * Admin Module: Investing
 * @ ACP
 * + Banks
 * + Stocks
 * + Credit-Cards
 * + Long-Term Investments
 */

if ( ! defined( 'IN_ACP' ) )
{
	print "<h1>Incorrect access</h1>You cannot access this file directly. If you have recently upgraded, make sure you upgraded 'admin.php'.";
	exit();
}

class admin_ibEconomy_investing_investing extends ipsCommand
{
	public $html;
	public $registry;
	
	public $form_code;
	public $form_code_js;
	
	public $permissions;
	
	/**
	 * Main execution method
	 */	
	public function doExecute( ipsRegistry $registry )
	{
		#load templates
		$this->html         	= $this->registry->output->loadTemplate( 'cp_skin_ibEconomy');
		
		#load langs
		ipsRegistry::getClass('class_localization')->loadLanguageFile( array( 'admin_ibEconomy' ) );
		ipsRegistry::getClass('class_localization')->loadLanguageFile( array( 'public_ibEconomy' ) );
		ipsRegistry::getClass('class_localization')->loadLanguageFile( array( 'admin_tools' ), 'core' );
		ipsRegistry::getClass('class_localization')->loadLanguageFile( array( 'admin_forums' ), 'forums' );
		
		#do form codes
		$this->form_code 		= $this->html->form_code    = 'module=investing&amp;section=investing';
		$this->form_code_js 	= $this->html->form_code_js = 'module=investing&section=investing';	
		
		#saved message
		if( $this->request['saved'] == 1 )
		{
			$this->registry->output->global_message = $this->lang->words['s_updated'];
		}
		
		#switcharoo
		switch( $this->request['do'] )
		{
			//******Reorder******//	
			case 'reorder':
				$this->registry->getClass('class_permissions')->checkPermissionAutoMsg( 'ibEconomy_reorder_items' );
				$this->_reorder();
			break;
			
			//******Delete******//	
			case 'delete':
				$this->registry->getClass('class_permissions')->checkPermissionAutoMsg( 'ibEconomy_delete_investment_items' );
				$this->delete();
			break;
		
			//******Settings******//		
			case 'banking_settings':
				$this->registry->getClass('class_permissions')->checkPermissionAutoMsg( 'ibEconomy_investment_settings' );			
				$this->registry->class_ibEco_CP->doSettings( $this->request['do'], $this->form_code );
			break;

			//******Long-Terms******//
			case 'long_term':
				$this->registry->getClass('class_permissions')->checkPermissionAutoMsg( 'ibEconomy_lt_edit' );			
				$this->ltForm();
			break;
			case 'do_long_term':
				$this->registry->getClass('class_permissions')->checkPermissionAutoMsg( 'ibEconomy_lt_edit' );
				$this->doLongterm();
			break;
			case 'long_terms':
				$this->registry->getClass('class_permissions')->checkPermissionAutoMsg( 'ibEconomy_lt_view' );
				$this->listLong_terms();
			break;
			
			//******Stocks******//
			case 'stock':
				$this->registry->getClass('class_permissions')->checkPermissionAutoMsg( 'ibEconomy_stock_edit' );
				$this->stockForm();
			break;
			case 'do_stock':
				$this->registry->getClass('class_permissions')->checkPermissionAutoMsg( 'ibEconomy_stock_edit' );
				$this->doStock();
			break;
			case 'stocks':
				$this->registry->getClass('class_permissions')->checkPermissionAutoMsg( 'ibEconomy_stock_view' );
				$this->listStocks();
			break;
				
			//******Credit Cards******//
			case 'cc':
				$this->registry->getClass('class_permissions')->checkPermissionAutoMsg( 'ibEconomy_cc_edit' );
				$this->ccForm();
			break;
			case 'do_cc':
				$this->registry->getClass('class_permissions')->checkPermissionAutoMsg( 'ibEconomy_cc_edit' );
				$this->doCC();
			break;
			case 'credit_cards':
				$this->registry->getClass('class_permissions')->checkPermissionAutoMsg( 'ibEconomy_cc_view' );
				$this->listCredit_cards();
			break;
			
			//******Banks******//
			case 'bank':
				$this->registry->getClass('class_permissions')->checkPermissionAutoMsg( 'ibEconomy_bank_edit' );
				$this->bankForm();
			break;
			case 'do_bank':
				$this->registry->getClass('class_permissions')->checkPermissionAutoMsg( 'ibEconomy_bank_edit' );
				$this->doBank();
			break;	
			case 'institutions':
			default:
				$this->registry->getClass('class_permissions')->checkPermissionAutoMsg( 'ibEconomy_bank_view' );
				$this->listBanks();
			break;
		}
		
		#footer
		$this->registry->output->html .= $this->html->footer();		
		
		#output
		$this->registry->output->html_main .= $this->registry->output->global_template->global_frame_wrapper();
		$this->registry->output->sendOutput();
	}

	/**
	 * Reorder items
	 */
	private function _reorder()
	{
		#init
		$classToLoad = IPSLib::loadLibrary( IPS_KERNEL_PATH . 'classAjax.php', 'classAjax' );
		$ajax = new $classToLoad();
		
		#which item	
		$itemTypes		= array('banks','stocks','lts','ccs');
		
		foreach ( $itemTypes as $type )
		{
			if ( is_array($this->request[ $type ]) AND count($this->request[ $type ]) )
			{
				$thisType = $type;
				break;
			}
		}
		
		#which DB and field
		if ( !in_array($thisType, array('banks','stocks')) )
		{
			$thisTypeDB 	= ( $thisType == 'lts' ) ? 'eco_long_terms': 'eco_credit_cards';
			$thisTypeField 	= ( $thisType == 'lts' ) ? 'lt': 'cc';
		}
		else
		{
			$thisTypeDB 	= 'eco_'.$thisType;
			$thisTypeField	= $thisType[0];
		}
		
		#checks
		if( $this->registry->adminFunctions->checkSecurityKey( $this->request['md5check'], true ) === false )
		{
			$ajax->returnString( $this->lang->words['postform_badmd5'] );
			exit();
		}
		
 		#Save new position
 		$position	= 1;
 		
 		if( is_array($this->request[ $thisType ]) AND count($this->request[ $thisType ]) )
 		{
 			foreach( $this->request[ $thisType ] as $this_id )
 			{
 				$this->DB->update( $thisTypeDB, array( $thisTypeField.'_position' => $position ), $thisTypeField.'_id=' . $this_id );
 				
 				$position++;
 			}
 		}
 		
		#rebuild da cache
		$this->registry->ecoclass->acm(array($thisType,'portfolios'));
		
		#a ok
 		$ajax->returnString( 'OK' );
 		exit();
	}
	
	/**
	 * Add/Edit Long-Term Form
	 */	
	private function ltForm()
	{
		#init
		$content = array();
		
		if ( $this->request['type'] == 'edit' )
		{
			if ( ! $this->request['lt_id'] )
			{
				$this->registry->output->showError( str_replace('<%TYPE%>', $this->lang->words['lt'], $this->lang->words['error_no_id']) );		
			}
			
			#get stock
			$this->DB->build( array( 'select'	=> 'lt.*',
									 'from'		=> array ('eco_long_terms' => 'lt' ),
									 'where'	=> 'lt.lt_id='.$this->request['lt_id'],
													'add_join' => array(
																		array(
																				'select' => 'p.*',
																				'from'   => array( 'permission_index' => 'p' ),
																				'where'  => "p.app = 'ibEconomy' AND p.perm_type='long_term' AND perm_type_id=lt.lt_id",
																				'type'   => 'left',
																			 ) 
														               )
							)		);
			$this->DB->execute();
			
			if ( !$this->DB->getTotalRows() )
			{
				$this->registry->output->showError( str_replace('<%TYPE%>', $this->lang->words['lt'], $this->lang->words['error_no_id']) );	
			}	
			else
			{
				$content = $this->DB->fetch();
				$content['lt_min'] = $this->registry->ecoclass->makeNumeric($content['lt_min'], false);
			}
		}
		
		#buttons
		$buttonRows = $this->registry->class_ibEco_CP->makeButtonRow( array('tools', 'add_bank', 'add_stock', 'add_cc') );
		
		#get permission matrix
		$matrix_html    = $this->registry->getClass('class_perms')->adminPermMatrix( 'long_term', $content );
		
		#output
		$this->registry->output->html .= $this->html->ltForm( $content, $matrix_html, $buttonRows );
	}
	
	/**
	 * Add Edit Long-term
	 */
	private function doLongterm()
	{
		#create data array
		$lterms_data = array('lt_title' 		=> $this->request['lt_title'],
							 'lt_type'			=> $this->request['lt_type'],
							 'lt_min' 			=> $this->registry->ecoclass->makeNumeric($this->request['lt_min'], true),
							 'lt_min_days' 		=> $this->request['lt_min_days'],
							 'lt_risk_level' 	=> $this->request['lt_risk_level'],
							 'lt_early_cash' 	=> $this->request['lt_early_cash'],
							 'lt_early_cash_fee'=> $this->request['lt_early_cash_fee'],
							 'lt_use_perms'		=> $this->request['lt_use_perms'],
							 'lt_on' 			=> $this->request['lt_on']
						   );
		#insert or update...						  
		if ( ! $this->request['lt_id'] )
		{		
			$lterms_data['lt_added_on'] = time();
			$this->DB->insert( 'eco_long_terms', $lterms_data );
			$new_id = $this->DB->getInsertId();
					
			$this->registry->output->global_message = $this->lang->words['long_term_added'];	
		}
		else
		{						  
			$this->DB->update( 'eco_long_terms', $lterms_data, 'lt_id='.$this->request['lt_id'] );
						
			$this->registry->output->global_message = str_replace('<%LT_NAME%>', $this->request['lt_name'], $this->lang->words['long_term_edited']);
		}
		
		$lt_id = ( $this->request['lt_id'] ) ? $this->request['lt_id'] : $new_id;
		
		#do image if this is the initial creation
		if ( !$this->request['lt_id'] )
		{
			$return = $this->registry->class_ibEco_CP->imageForInitialItem('lt', $lt_id);
		}

		#do perms
		$this->registry->getClass('class_perms')->savePermMatrix( $this->request['perms'], $lt_id, 'long_term' );
		
		#rebuild cache
		$this->registry->ecoclass->acm(array('lts','portfolios'));

		#write log
		$logType = ( ! $this->request['lt_id'] ) ? $this->lang->words['added'] : $this->lang->words['edited'];
		$this->registry->adminFunctions->saveAdminLog( sprintf( $this->lang->words['lt_done'], $logType, $this->request['lt_title'] ) );				
		
		#get out o hear
		$this->registry->output->silentRedirectWithMessage( $this->settings['base_url'].$this->html->form_code.'&amp;do=long_terms' );	
	}
	
	/**
	 * List Long-terms
	 */	
	private function listLong_terms()
	{
		#init
		$content = "";
		
		#get long term investments
		$this->DB->build( array( 	'select'	=> 'elt.*',
									'from'		=> array( 'eco_long_terms' => 'elt' ),
									'group'		=> 'elt.lt_id',
									'order'		=> 'elt.lt_position',
									'add_join'	=> array(
														array( 'select' => 'COUNT(eps.p_member_id) as total_investors, SUM(eps.p_amount) as total_invested',
																'from'	=> array( 'eco_portfolio' => 'eps' ),
																'where'	=> "eps.p_type_id = elt.lt_id and eps.p_type='long_term'",
																'type'	=> 'left',
															 )															
														)
							)		);
		$this->DB->execute();
		
		if ( $this->DB->getTotalRows() )
		{
			while ( $row = $this->DB->fetch() )
			{
				#add row
				$content .= $this->html->ltRow( $row );
			}
		}
		
		#buttons
		$buttonRows = $this->registry->class_ibEco_CP->makeButtonRow( array('tools', 'cache', 'add_bank', 'add_stock', 'add_cc') );	
		
		#output
		$this->registry->output->html .= $this->html->ltsOverviewWrapper( $content, $buttonRows );
	}
	
	/**
	 * Add/Edit Stock Form
	 */	
	private function stockForm()
	{
		#init
		$content = array();
		
		if ( $this->request['type'] == 'edit' )
		{
			if ( ! $this->request['s_id'] )
			{
				$this->registry->output->showError( str_replace('<%TYPE%>', $this->lang->words['stock'], $this->lang->words['error_no_id']) );		
			}
			
			#get stock
			$content = $this->DB->buildAndFetch( array( 
													'select'   => 's.*', 
													'from'     => array( 'eco_stocks' => 's' ), 
													'where'    => 's.s_id=' . $this->request['s_id'],
													'add_join' => array(
																		array(
																				'select' => 'p.*',
																				'from'   => array( 'permission_index' => 'p' ),
																				'where'  => "p.app = 'ibEconomy' AND p.perm_type='stock' AND perm_type_id=s.s_id",
																				'type'   => 'left',
																			 ) 
														               )
											)	 );		

			if ( !content)
			{
				$this->registry->output->showError( str_replace('<%TYPE%>', $this->lang->words['stock'], $this->lang->words['error_no_id']) );
			}
		}
		
		$content['s_value'] = $this->registry->ecoclass->makeNumeric($content['s_value'], false);
		
		if ( $content['s_type'] == 'member' and intval($content['s_type_var_value']) )
		{
			$member	 = $this->DB->buildAndFetch( array( 
											'select'   => 'members_display_name', 
											'from'     => 'members',
											'where'    => 'member_id='.$content['s_type_var_value']
									)	 );
									
			$content['s_type_var_value'] = $member['members_display_name'];
		}
		
		#buttons
		$buttonRows = $this->registry->class_ibEco_CP->makeButtonRow( array('tools', 'add_bank', 'add_cc', 'add_lt') );
		
		#get permission matrix
		$matrix_html    = $this->registry->getClass('class_perms')->adminPermMatrix( 'stock', $content );		
		
		#output
		$this->registry->output->html .= $this->html->stockForm( $content, $matrix_html, $buttonRows );
	}
	
	/**
	 * Add Edit Stock
	 */
	private function doStock()
	{
		#init crazy stock variable madness
		if ( ! $this->request['s_type'] )
		{
			$this->registry->output->showError( str_replace('<%TYPE%>', $this->lang->words['stock'].' '.$this->lang->words['type'], $this->lang->words['error_no_id']) );	
		}	
		if ( $this->request['s_type'] != 'basic' )
		{
			if ( $this->request['s_type'] == 'group' )
			{
				$this->request['s_type_var'] = $this->request['s_grpgrp_var'];
			}
			else if ( $this->request['s_type'] == 'member' )
			{
				$this->request['s_type_var'] = $this->request['s_memgrp_var'];
			}
			else
			{
				$this->request['s_type_var'] = $this->request['s_forum_var'];
			}

			$this->request['s_type_var_value'] = ( $this->request['s_type'] == 'group' ) ? $this->request['s_grp_var_value'] : $this->request['s_mem_var_value'];
			$this->request['s_type_var_value'] = ( $this->request['s_type'] == 'forum' ) ? '' : $this->request['s_type_var_value'];
		
			if ( $this->request['s_type'] == 'member' )
			{
				$content = $this->DB->buildAndFetch( array( 
														'select'   => 'member_id', 
														'from'     => 'members',
														'where'    => "members_l_display_name='".$this->request['s_type_var_value']."'"
												)	 );	
		
				if ( !$content['member_id'] )
				{
					$this->registry->output->showError( str_replace('<%TYPE%>', $this->lang->words['stock'].' '.$this->lang->words['type'], $this->lang->words['error_no_id']) );	
				}
				
				$this->request['s_type_var_value'] = $content['member_id'];
			}
		}
		
		#create data array
		$stock_data = array('s_title' 			=> $this->request['s_title'],
							's_title_long'		=> $this->request['s_title_long'],
							's_type'			=> $this->request['s_type'],
							's_type_var'		=> $this->request['s_type_var'],
							's_type_var_value'	=> $this->request['s_type_var_value'],
							's_risk_level' 		=> $this->request['s_risk_level'],
							's_value' 			=> $this->registry->ecoclass->makeNumeric($this->request['s_value'], true),
							's_limit' 			=> $this->request['s_limit'],
							's_can_trade'		=> $this->request['s_can_trade'],
							's_use_perms'		=> $this->request['s_use_perms'],
							's_on' 				=> $this->request['s_on']
						   );
		
		#insert or update...		
		if ( ! $this->request['s_id'] )
		{			
			$stock_data['s_added_on'] = time();
			$this->DB->insert( 'eco_stocks', $stock_data );
			$new_id = $this->DB->getInsertId();
		
			$this->registry->output->global_message = $this->lang->words['stock_added'];	
		}
		else
		{						  
			$this->DB->update( 'eco_stocks', $stock_data, 's_id='.$this->request['s_id'] );
			
			$this->registry->output->global_message = str_replace('<%STOCK_NAME%>', $this->request['s_name'], $this->lang->words['stock_edited']);			
		}
		
		$stock_id = ( $this->request['s_id'] ) ? $this->request['s_id'] : $new_id;
		
		#do image if this is the initial creation
		if ( !$this->request['s_id'] )
		{
			$return = $this->registry->class_ibEco_CP->imageForInitialItem('stock', $stock_id);
		}
		
		#do perms
		$this->registry->getClass('class_perms')->savePermMatrix( $this->request['perms'], $stock_id, 'stock' );
		
		#rebuild cache
		$this->registry->ecoclass->acm(array('stocks','portfolios'));	

		#write log
		$logType = ( ! $this->request['s_id'] ) ? $this->lang->words['added'] : $this->lang->words['edited'];
		$this->registry->adminFunctions->saveAdminLog( sprintf( $this->lang->words['stock_done'], $logType, $this->request['s_title'] ) );				
		
		#get out o hear
		$this->registry->output->silentRedirectWithMessage( $this->settings['base_url'].$this->html->form_code.'&amp;do=stocks' );	
	}
	
	/**
	 * List Stocks
	 */	
	private function listStocks()
	{
		#init
		$content = "";
		
		#get credit cards
		$this->DB->build( array( 	'select'	=> 'es.*',
									'from'		=> array( 'eco_stocks' => 'es' ),
									'group'		=> 'es.s_id',
									'order'		=> 'es.s_position',
									'add_join'	=> array(
														array( 'select' => 'COUNT(eps.p_member_id) as total_shareholders, SUM(eps.p_amount) as total_invested, SUM(eps.p_type_class) as total_stock_shares',
																'from'	=> array( 'eco_portfolio' => 'eps' ),
																'where'	=> "eps.p_type_id = es.s_id and eps.p_type='stock'",
																'type'	=> 'left',
															 )															
														)
							)		);
		$this->DB->execute();
		
		if ( $this->DB->getTotalRows() )
		{
			while ( $row = $this->DB->fetch() )
			{
				#add row
				$row['s_value'] = $this->registry->ecoclass->makeNumeric($row['s_value'], false);
				$content .= $this->html->stockRow( $row );
			}
		}
		
		#buttons
		$buttonRows = $this->registry->class_ibEco_CP->makeButtonRow( array('tools', 'cache', 'add_bank', 'add_cc', 'add_lt') );
		
		#output
		$this->registry->output->html .= $this->html->stocksOverviewWrapper( $content, $buttonRows );
	}

	/**
	 * Add/Edit CC Form
	 */	
	private function ccForm()
	{
		#init
		$content = array();
		
		if ( $this->request['type'] == 'edit' )
		{
			if ( ! $this->request['cc_id'] )
			{
				$this->registry->output->showError( str_replace('<%TYPE%>', $this->lang->words['cc'], $this->lang->words['error_no_id']) );			
			}
			
			#get bank
			$this->DB->build( array( 'select'	=> 'ecc.*',
									 'from'		=> array('eco_credit_cards' => 'ecc'),
									 'where'	=> 'ecc.cc_id='.$this->request['cc_id'],
													'add_join' => array(
																		array(
																				'select' => 'p.*',
																				'from'   => array( 'permission_index' => 'p' ),
																				'where'  => "p.app = 'ibEconomy' AND p.perm_type='cc' AND perm_type_id=ecc.cc_id",
																				'type'   => 'left',
																			 ) 
														               )
							)		);
			$this->DB->execute();
			
			if ( !$this->DB->getTotalRows() )
			{
				$this->registry->output->showError( str_replace('<%TYPE%>', $this->lang->words['cc'], $this->lang->words['error_no_id']) );	
			}	
			else
			{
				$content = $this->DB->fetch();
				$content['cc_cost'] = $this->registry->ecoclass->makeNumeric($content['cc_cost'], false);
				$content['cc_no_pay_chrg'] = $this->registry->ecoclass->makeNumeric($content['cc_no_pay_chrg'], false);	
			}
		}
		
		#buttons
		$buttonRows = $this->registry->class_ibEco_CP->makeButtonRow( array('tools', 'add_bank', 'add_stock', 'add_lt') );
		
		#get permission matrix
		$matrix_html    = $this->registry->getClass('class_perms')->adminPermMatrix( 'cc', $content );	
		
		#output
		$this->registry->output->html .= $this->html->ccForm( $content, $matrix_html, $buttonRows );
	}
	
	/**
	 * Add Edit CC
	 */
	private function doCC()
	{		
		#create data array
		$card_datas = array('cc_title'			=> $this->request['cc_title'],
							'cc_apr' 			=> $this->request['cc_apr'],
							'cc_cost' 			=> $this->registry->ecoclass->makeNumeric($this->request['cc_cost'], true),
							'cc_max' 			=> $this->request['cc_max'],
							'cc_csh_adv' 		=> $this->request['cc_csh_adv'],
							'cc_csh_adv_fee'	=> $this->request['cc_csh_adv_fee'],
							'cc_bal_trnsfr' 	=> $this->request['cc_bal_trnsfr'],
							'cc_bal_trnsfr_apr' => $this->request['cc_bal_trnsfr_apr'],
							'cc_bal_trnsfr_end' => $this->request['cc_bal_trnsfr_end'],
							'cc_bal_trnsfr_fee' => $this->request['cc_bal_trnsfr_fee'],
							'cc_apr_max' 		=> $this->request['cc_apr_max'],
							'cc_apr_min' 		=> $this->request['cc_apr_min'],
							'cc_allow_od' 		=> $this->request['cc_allow_od'],
							'cc_max_od' 		=> $this->request['cc_max_od'],
							'cc_no_pay_chrg'	=> $this->registry->ecoclass->makeNumeric($this->request['cc_no_pay_chrg'], true),
							'cc_od_pnlty' 		=> $this->request['cc_od_pnlty'],
							'cc_use_perms'		=> $this->request['cc_use_perms'],
							'cc_on' 			=> $this->request['cc_on']
						   );
		
		#insert or update...		
		if ( ! $this->request['cc_id'] )
		{				
			$card_datas['cc_added_on'] = time();
			$this->DB->insert( 'eco_credit_cards', $card_datas );
			$new_id = $this->DB->getInsertId();
		
			$this->registry->output->global_message = $this->lang->words['credit_card_added'];	
		}
		else
		{						  
			$this->DB->update( 'eco_credit_cards', $card_datas, 'cc_id='.$this->request['cc_id'] );
			
			$this->registry->output->global_message = str_replace('<%CC_NAME%>', $this->request['cc_name'], $this->lang->words['credit_card_edited']);			
		}
				
		$cc_id = ( $this->request['cc_id'] ) ? $this->request['cc_id'] : $new_id;
		
		#do image if this is the initial creation
		if ( !$this->request['cc_id'] )
		{
			$return = $this->registry->class_ibEco_CP->imageForInitialItem('cc', $cc_id);
		}
		
		#do perms
		$this->registry->getClass('class_perms')->savePermMatrix( $this->request['perms'], $cc_id, 'cc' );
		
		#rebuild cache
		$this->registry->ecoclass->acm(array('ccs','portfolios'));

		#write log
		$logType = ( ! $this->request['cc_id'] ) ? $this->lang->words['added'] : $this->lang->words['edited'];
		$this->registry->adminFunctions->saveAdminLog( sprintf( $this->lang->words['cc_done'], $logType, $this->request['cc_title'] ) );				
		
		#get out o hear
		$this->registry->output->silentRedirectWithMessage( $this->settings['base_url'].$this->html->form_code.'&amp;do=credit_cards' );	
	}
	
	/**
	 * List Credit Cards
	 */	
	private function listCredit_cards()
	{
		#init
		$content = "";
		
		#get credit cards
		$this->DB->build( array( 	'select'	=> 'ecc.*',
									'from'		=> array( 'eco_credit_cards' => 'ecc' ),
									'group'		=> 'ecc.cc_id',
									'order'		=> 'ecc.cc_position',
									'add_join'	=> array(
														0 => array( 'select' 	=> 'COUNT(eps.p_member_id) as total_standard_cards, SUM(eps.p_amount) as total_standard_debt, AVG(eps.p_rate) as avg_standard_apr',
																		'from'	=> array( 'eco_portfolio' => 'eps' ),
																		'where'	=> "eps.p_type_id = ecc.cc_id and eps.p_type_class='standard' and eps.p_type='credit_card'",
																		'type'	=> 'left',
															),
														1 => array( 'select' 	=> 'COUNT(ept.p_member_id) as total_transfer_cards, SUM(ept.p_amount) as total_transfer_debt, AVG(ept.p_rate) as avg_transfer_apr',
																		'from'	=> array( 'eco_portfolio' => 'ept' ),
																		'where'	=> "ept.p_type_id = ecc.cc_id and ept.p_type_class='transfer' and ept.p_type='credit_card'",
																		'type'	=> 'left',
															)															
														)
							)		);
		$this->DB->execute();
		
		if ( $this->DB->getTotalRows() )
		{
			while ( $row = $this->DB->fetch() )
			{
				#add row
				$content .= $this->html->ccRow( $row );
			}
		}
		
		#buttons
		$buttonRows = $this->registry->class_ibEco_CP->makeButtonRow( array('tools', 'cache', 'add_bank', 'add_stock', 'add_lt') );	
		
		#output
		$this->registry->output->html .= $this->html->ccsOverviewWrapper( $content, $buttonRows );
	}
	
	/**
	 * Add/Edit Bank Form
	 */	
	private function bankForm()
	{
		#init
		$content = array();
		
		if ( $this->request['type'] == 'edit' )
		{
			if ( ! $this->request['bank_id'] )
			{
				$this->registry->output->showError( str_replace('<%TYPE%>', $this->lang->words['bank'], $this->lang->words['error_no_id']) );		
			}
			
			#get bank
			$this->DB->build( array( 'select'	=> 'eb.*',
									 'from'		=> array('eco_banks' => 'eb'),
									 'where'	=> 'eb.b_id='.$this->request['bank_id'],
													'add_join' => array(
																		array(
																				'select' => 'p.*',
																				'from'   => array( 'permission_index' => 'p' ),
																				'where'  => "p.app = 'ibEconomy' AND p.perm_type='bank' AND perm_type_id=eb.b_id",
																				'type'   => 'left',
																			 ) 
														               )
							)		);
			$this->DB->execute();
			
			if ( !$this->DB->getTotalRows() )
			{
				$this->registry->output->showError( str_replace('<%TYPE%>', $this->lang->words['bank'], $this->lang->words['error_no_id']) );	
			}	
			else
			{
				$content = $this->DB->fetch();
				$content['b_c_acnt_cost'] = $this->registry->getClass('class_localization')->formatNumber( $this->registry->ecoclass->makeNumeric($content['b_c_acnt_cost'], false) );
				$content['b_s_acnt_cost'] = $this->registry->getClass('class_localization')->formatNumber( $this->registry->ecoclass->makeNumeric($content['b_s_acnt_cost'], false) );				
			}
		}
		
		#get permission matrix
		$matrix_html    = $this->registry->getClass('class_perms')->adminPermMatrix( 'bank', $content );
		
		#buttons
		$buttonRows = $this->registry->class_ibEco_CP->makeButtonRow( array('tools', 'add_stock', 'add_cc', 'add_lt') );
		
		#output
		$this->registry->output->html .= $this->html->bankForm( $content, $matrix_html, $buttonRows );
	}
	
	/**
	 * Add/Edit Bank
	 */
	private function doBank()
	{		
		#create data array
		$bank_datas = array('b_title'			=> $this->request['b_title'],
							'b_savings_on' 		=> $this->request['b_savings_on'],
							'b_checking_on'	 	=> $this->request['b_checking_on'],
							'b_c_acnt_cost' 	=> $this->registry->ecoclass->makeNumeric($this->request['b_c_acnt_cost'], true),
							'b_s_acnt_cost' 	=> $this->registry->ecoclass->makeNumeric($this->request['b_s_acnt_cost'], true),
							'b_c_dep_fee' 		=> $this->registry->ecoclass->makeNumeric($this->request['b_c_dep_fee'], true),
							'b_s_dep_fee' 		=> $this->registry->ecoclass->makeNumeric($this->request['b_s_dep_fee'], true),
							'b_c_wthd_fee' 		=> $this->registry->ecoclass->makeNumeric($this->request['b_c_wthd_fee'], true),
							'b_s_wthd_fee' 		=> $this->registry->ecoclass->makeNumeric($this->request['b_s_wthd_fee'], true),
							'b_sav_interest' 	=> $this->request['b_sav_interest'],
							'b_loans_on' 		=> $this->request['b_loans_on'],
							'b_loans_max' 		=> $this->registry->ecoclass->makeNumeric($this->request['b_loans_max'], true),
							'b_loans_fee' 		=> $this->registry->ecoclass->makeNumeric($this->request['b_loans_fee'], true),
							'b_loans_app_fee' 	=> $this->registry->ecoclass->makeNumeric($this->request['b_loans_app_fee'], true),
							'b_loans_days'		=> $this->request['b_loans_days'],
							'b_loans_pen' 		=> $this->request['b_loans_pen'],							
							'b_use_perms'		=> $this->request['b_use_perms'],
							'b_on' 				=> $this->request['b_on']								
						   );
		
		#insert or update...
		if ( ! $this->request['bank_id'] )
		{	
			$bank_datas['b_added_on'] = time();		
			$this->DB->insert( 'eco_banks', $bank_datas );
			$new_id = $this->DB->getInsertId();
		
			$this->registry->output->global_message = $this->lang->words['bank_added'];	
		}
		else
		{						  
			$this->DB->update( 'eco_banks', $bank_datas, 'b_id='.$this->request['bank_id'] );
			
			$this->registry->output->global_message = str_replace('<%BANK_NAME%>', $this->request['bank_name'], $this->lang->words['bank_edited']);			
		}
		
		$bank_id = ( $this->request['bank_id'] ) ? $this->request['bank_id'] : $new_id;
		
		#do image if this is the initial creation
		if ( !$this->request['bank_id'] )
		{
			$return = $this->registry->class_ibEco_CP->imageForInitialItem('bank', $bank_id);
		}
		
		#do perms
		$this->registry->getClass('class_perms')->savePermMatrix( $this->request['perms'], $bank_id, 'bank' );
		
		#rebuild cache
		$this->registry->ecoclass->acm(array('banks','portfolios'));	
		
		#write log
		$logType = ( ! $this->request['bank_id'] ) ? $this->lang->words['added'] : $this->lang->words['edited'];
		$this->registry->adminFunctions->saveAdminLog( sprintf( $this->lang->words['bank_done'], $logType, $this->request['b_title'] ) );				
		
		#get out o hear
		$this->registry->output->silentRedirectWithMessage( $this->settings['base_url'].$this->html->form_code.'&amp;do=banks' );
	}
	
	/**
	 * List Banks
	 */	
	private function listBanks()
	{
		#init
		$content = "";
		
		#get banks
		$this->DB->build( array( 	'select'	=> 'eb.*',
									'from'		=> array( 'eco_banks' => 'eb' ),
									'group'		=> 'eb.b_id',
									'order'		=> 'eb.b_position',
									'add_join'	=> array(
														0 => array( 'select' 	=> 'COUNT(epc.p_member_id) as c_total, SUM(epc.p_amount) as c_funds',
																		'from'	=> array( 'eco_portfolio' => 'epc' ),
																		'where'	=> "epc.p_type_id = eb.b_id and epc.p_type_class='checking' and epc.p_type='bank'",
																		'type'	=> 'left',
															),
														1 => array( 'select' 	=> 'COUNT(eps.p_member_id) as s_total, SUM(eps.p_amount) as s_funds',
																		'from'	=> array( 'eco_portfolio' => 'eps' ),
																		'where'	=> "eps.p_type_id = eb.b_id and eps.p_type_class='savings' and eps.p_type='bank'",
																		'type'	=> 'left',
															)															
														)
							)		);
		$this->DB->execute();
		
		if ( $this->DB->getTotalRows() )
		{
			while ( $row = $this->DB->fetch() )
			{
				#format totals
				$row['b_c_acnt_cost'] = $this->registry->getClass('class_localization')->formatNumber( $this->registry->ecoclass->makeNumeric($row['b_c_acnt_cost'], false) );
				$row['b_s_acnt_cost'] = $this->registry->getClass('class_localization')->formatNumber( $this->registry->ecoclass->makeNumeric($row['b_s_acnt_cost'], false) );
				$row['total'] = $this->registry->getClass('class_localization')->formatNumber( $this->registry->ecoclass->makeNumeric(($row['s_total'] + $row['c_total']), false) );
				$row['funds'] = $this->registry->getClass('class_localization')->formatNumber( $this->registry->ecoclass->makeNumeric(($row['s_funds'] + $row['c_funds']), false) );				
				
				#add row
				$content .= $this->html->bankRow( $row );
			}
		}
		
		#buttons
		$buttonRows = $this->registry->class_ibEco_CP->makeButtonRow( array('tools', 'cache', 'add_stock', 'add_cc', 'add_lt') );
		
		#output
		$this->registry->output->html .= $this->html->banksOverviewWrapper( $content, $buttonRows );
	}
	
	/**
	 * Delete!
	 */		
	private function delete()
	{
		#no eco items passed?
		if( ! in_array( $this->request['type'], array( 'bank', 'stock', 'credit_card', 'long_term' ) ) )
		{
			$this->registry->output->showError( $this->lang->words['error_no_type_to_delete'] );
		}
		#no id?
		if( ! $this->request['id'] )
		{
			$this->registry->output->showError( str_replace('<%TYPE%>', $this->request['type'], $this->lang->words['error_no_id']) );
		}
		
		#you had to do all items in this one function?
		$prefix 	= ( $this->request['type']{0} == 'c' ) ? $this->request['type']{0}.$this->request['type']{0} : $this->request['type']{0};
		$prefix 	= ( $this->request['type']{0} == 'l' ) ? $this->request['type']{0}.'t' : $prefix;
		$abr    	= ( in_array ($prefix, array('lt','cc') ) ) ? $prefix : $this->request['type'];
		$itemName 	= $this->caches['ibEco_'.$abr.'s'][ $this->request['id'] ][ $prefix.'_title' ];
		$type 		= ucfirst($this->request['type']);	
		
		#delete it
		$this->DB->delete( 'eco_'.$this->request['type'].'s', $prefix.'_id = ' . $this->request['id'] );
		
		#delete corresponding assets
		$this->DB->delete( 'eco_portfolio', "p_type_id = " . $this->request['id']." AND p_type = '$abr'" );

		#delete corresponding cart items
		$this->DB->delete( 'eco_cart', "c_type_id = " . $this->request['id']." AND c_type = '$abr'" );
		
		#rebuild da cache
		$this->registry->ecoclass->acm(array($abr.'s','portfolios'));		
		
		#save log
		$this->registry->adminFunctions->saveAdminLog( sprintf( $this->lang->words['item_deleted'], $this->lang->words[ $abr ], $itemName ) );		
		
		#lowercase
		$type = strtolower($type);
		
		#message to display on next page
		$this->registry->output->global_message = str_replace('<%TYPE%>', $this->lang->words[ $type ], $this->lang->words['type_deleted']);
		
		#get out o hear
		$this->registry->output->silentRedirectWithMessage( $this->settings['base_url'].$this->html->form_code.'&amp;do='.$type.'s' );
	}
	
}