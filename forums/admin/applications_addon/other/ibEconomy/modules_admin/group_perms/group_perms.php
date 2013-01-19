<?php

/**
 * (e32) ibEconomy
 * Admin Module: Group Permissions
 * @ ACP
 * + Set Group Permissions in ibEconomy
 */

if ( ! defined( 'IN_ACP' ) )
{
	print "<h1>Incorrect access</h1>You cannot access this file directly. If you have recently upgraded, make sure you upgraded 'admin.php'.";
	exit();
}

class admin_ibEconomy_group_perms_group_perms extends ipsCommand 
{
	public $html;
	public $registry;
	
	private $form_code;
	private $form_code_js;
	
	/**
	 * Main execution method
	 */
	public function doExecute( ipsRegistry $registry )
	{
		#load templates
		$this->html               = $this->registry->output->loadTemplate( 'cp_skin_ibEconomy' );
		
		#load lang
		ipsRegistry::getClass('class_localization')->loadLanguageFile( array( 'admin_ibEconomy' ) );	

		#do form codes
		$this->html->form_code    = 'module=group_perms&amp;section=group_perms';
		$this->html->form_code_js = '&module=group_perms&section=group_perms';		
		
		#switcharoo
		switch( $this->request['do'] )
		{
			//******Edit Group Do******//
			case 'edit_group':
				$this->registry->getClass('class_permissions')->checkPermissionAutoMsg( 'ibEconomy_group_edit' );
				$this->editGroup();
			break;
			
			//******Enable All 4 Grp******//
			case 'group_enable_all':
				$this->registry->getClass('class_permissions')->checkPermissionAutoMsg( 'ibEconomy_group_edit' );
				$this->editGroup(TRUE);
			break;	
			
			//******Disable All 4 Grp******//
			case 'group_disable_all':
				$this->registry->getClass('class_permissions')->checkPermissionAutoMsg( 'ibEconomy_group_edit' );
				$this->editGroup(FALSE, TRUE);
			break;			
			//******Group Settings******//			
			case 'show_group':
				$this->registry->getClass('class_permissions')->checkPermissionAutoMsg( 'ibEconomy_group_view' );
				$this->showGroup();
			break;
			
			//******Show Group List******//
			case 'groups':				
			default:
				$this->registry->getClass('class_permissions')->checkPermissionAutoMsg( 'ibEconomy_group_view' );
				$this->groupList();
			break;
		}
		
		#footer
		$this->registry->output->html .= $this->html->footer();			
		
		#output
		$this->registry->output->html_main .= $this->registry->output->global_template->global_frame_wrapper();
		$this->registry->output->sendOutput();			
	}
	
	/**
	 * List the groups
	 */
	private function groupList()
	{
		#init
		$content = "";
		
		#get groups
		$this->DB->build( array( 'select'			=> 'g.*',
										'from'		=> array( 'groups' => 'g' ),
										'group'		=> 'g.g_id',
										'order'		=> 'g.g_title',
										'add_join'	=> array(
															array( 'select'	=> 'COUNT(m.member_id) as count',
																	'from'	=> array( 'members' => 'm' ),
																	'where'	=> "m.member_group_id = g.g_id OR m.mgroup_others LIKE " . $this->DB->buildConcat( array( array( '%,', 'string' ), array( 'g.g_id' ), array( ',%', 'string' ) ) ),
																	'type'	=> 'left',
																)
															)
							)		);
		$this->DB->execute();
		
		while ( $row = $this->DB->fetch() )
		{
			#format some stuff
			$row['_can_delete']		= ( $row['g_id'] > 4 ) ? 1 : 0;
			$row['_can_acp']		= ( $row['g_access_cp'] == 1 ) ? 1 : 0;
			$row['_can_supmod']		= ( $row['g_is_supmod'] == 1 ) ? 1 : 0;

			#add row
			$content .= $this->html->groupsOverviewRow( $row );
		}
		
		#buttons
		$buttonRows = $this->registry->class_ibEco_CP->makeButtonRow( array('tools') );
		
		#output
		$this->registry->output->html .= $this->html->groupsOverviewWrapper( $content, $buttonRows );
	}

	/**
	 * List the groups
	 */
	private function showGroup()
	{
		#nav
		$this->registry->output->extra_nav[] = array( $this->form_code, $this->lang->words['edit_group'] );
		
		#group data
		if ($this->request['id'] == "")
		{
			$this->registry->output->showError( $this->lang->words['error_no_group_id'] );
		}
		
		$group = $this->DB->buildAndFetch( array( 'select' => '*', 'from' => 'groups', 'where' => "g_id=" . intval($this->request['id']) ) );
		$group = IPSMember::unpackGroup( $group );
		
		#output
		$this->registry->output->html .= $this->html->groupsForm( $group );
	}
	
	/**
	 * Edit Group
	 */
	private function editGroup($enableALL=FALSE, $disableALL=FALSE)
	{	
		#no group ID?
		if ( ! $this->request['group_id'] )
		{
			$this->registry->output->global_message = $this->lang->words['no_group_id_sent'];
			$this->registry->output->silentRedirectWithMessage( $this->settings['base_url'].$this->html->form_code.'&amp;do=groups' );
			return;		
		}
		
		if ( !$enableALL && !$disableALL)
		{
			#create group data array
			$new_group_options = array(	'g_eco'				=> $this->request['g_eco'],
									'g_eco_bank' 			=> $this->request['g_eco_bank'],
									'g_eco_welfare'	 		=> $this->request['g_eco_welfare'],
									'g_eco_loan' 			=> $this->request['g_eco_loan'],
									'g_eco_stock' 			=> $this->request['g_eco_stock'],
									'g_eco_cc' 				=> $this->request['g_eco_cc'],
									'g_eco_lt' 				=> $this->request['g_eco_lt'],
									'g_eco_frm_ptsx' 		=> $this->request['g_eco_frm_ptsx'],
									'g_eco_max_pts' 		=> $this->request['g_eco_max_pts'],
									'g_eco_max_cc_debt'		=> $this->request['g_eco_max_cc_debt'],
									'g_eco_max_loan_debt'	=> $this->request['g_eco_max_loan_debt'],
									'g_eco_bank_max' 		=> $this->request['g_eco_bank_max'],
									'g_eco_stock_max' 		=> $this->request['g_eco_stock_max'],
									'g_eco_lt_max' 			=> $this->request['g_eco_lt_max'],
									'g_eco_cash_adv_max'	=> $this->request['g_eco_cash_adv_max'],
									'g_eco_bal_trnsfr_max'	=> $this->request['g_eco_bal_trnsfr_max'],
									'g_eco_welfare_max' 	=> $this->request['g_eco_welfare_max'],
									'g_eco_transaction' 	=> $this->request['g_eco_transaction'],
									'g_eco_asset' 			=> $this->request['g_eco_asset'],
									'g_eco_shopitem' 		=> $this->request['g_eco_shopitem'],
									'g_eco_edit_pts' 		=> $this->request['g_eco_edit_pts'],
									'g_eco_lottery' 		=> $this->request['g_eco_lottery'],
									'g_eco_lottery_tix' 	=> $this->request['g_eco_lottery_tix'],
									'g_eco_lottery_odds' 	=> $this->request['g_eco_lottery_odds']
									  );
									  
			$new_group_options = array_merge($new_group_options, $this->registry->class_ibEco_CP->buildPluginGroupSettingsSaver());
		}
		else if ( $enableALL)
		{
			#enable all of the on/offs!
			$new_group_options = array(	'g_eco'				=> 1,
									'g_eco_bank' 			=> 1,
									'g_eco_welfare'	 		=> 1,
									'g_eco_loan' 			=> 1,
									'g_eco_stock' 			=> 1,
									'g_eco_cc' 				=> 1,
									'g_eco_lt' 				=> 1,
									'g_eco_transaction' 	=> 1,
									'g_eco_asset' 			=> 1,
									'g_eco_shopitem' 		=> 1,
									'g_eco_lottery' 		=> 1
									  );
		}
		else if ( $disableALL)
		{
			#enable all of the on/offs!
			$new_group_options = array(	'g_eco'				=> 0,
									'g_eco_bank' 			=> 0,
									'g_eco_welfare'	 		=> 0,
									'g_eco_loan' 			=> 0,
									'g_eco_stock' 			=> 0,
									'g_eco_cc' 				=> 0,
									'g_eco_lt' 				=> 0,
									'g_eco_transaction' 	=> 0,
									'g_eco_asset' 			=> 0,
									'g_eco_shopitem' 		=> 0,
									'g_eco_lottery' 		=> 0,
									'g_eco_edit_pts'		=> 0
									  );
		}
		#update group
		$this->DB->update( 'groups', $new_group_options, 'g_id='.$this->request['group_id'] );

		#init cache
		$cache	= array();
		
		#group cache
		$this->DB->build( array( 'select'	=> '*',
								 'from'	    => 'groups' ) );
		$this->DB->execute();
		
		while ( $i = $this->DB->fetch() )
		{
			$cache[ $i['g_id'] ] = IPSMember::unpackGroup( $i );
		}
		
		$this->cache->setCache( 'group_cache', $cache, array( 'array' => 1, 'deletefirst' => 1 ) );
		
		#log
		$this->registry->adminFunctions->saveAdminLog( sprintf( $this->lang->words['group_perms_edited'], $this->request['group_name'] ) );		
		
		#message
		$this->registry->output->global_message = str_replace('<%GROUP_NAME%>', $this->request['group_name'], $this->lang->words['group_edited']);

		#get out o hear
		$this->registry->output->silentRedirectWithMessage( $this->settings['base_url'].$this->html->form_code.'&amp;do=groups' );	
	}
	
}