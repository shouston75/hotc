<?php

/**
 * (e32) ibEconomy
 * Admin Module: Members
 * @ ACP
 * + Member Edit
 * + Member Item Send
 * + Edit Global Portfolio
 */

if ( ! defined( 'IN_ACP' ) )
{
	print "<h1>Incorrect access</h1>You cannot access this file directly. If you have recently upgraded, make sure you upgraded 'admin.php'.";
	exit();
}

class admin_ibEconomy_members_members extends ipsCommand
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
		#load template
		$this->html         	= $this->registry->output->loadTemplate( 'cp_skin_ibEconomy');

		#load langs
		ipsRegistry::getClass('class_localization')->loadLanguageFile( array( 'admin_ibEconomy' ) );
		ipsRegistry::getClass('class_localization')->loadLanguageFile( array( 'public_ibEconomy' ) );		
		
		#do form codes
		$this->form_code 		= $this->html->form_code    = 'module=members&amp;section=members';
		$this->form_code_js 	= $this->html->form_code_js = 'module=members&section=members';		
		
		#switcharoo
		switch( $this->request['do'] )
		{
			//******Delete******//
			case 'delete':
				$this->registry->getClass('class_permissions')->checkPermissionAutoMsg( 'ibEconomy_members_edit' );
				$this->delete();
			break;
			//******Edit Portfolio Item******//
			case 'portfolio_item_form':	
				$this->registry->getClass('class_permissions')->checkPermissionAutoMsg( 'ibEconomy_members_edit' );
				$this->portItemForm();
			break;
			case 'edit_port_item':	
				$this->registry->getClass('class_permissions')->checkPermissionAutoMsg( 'ibEconomy_members_edit' );
				$this->editPortItem();
			break;			
			//******Find Member******//
			case 'find_member':
			default:
				$this->registry->getClass('class_permissions')->checkPermissionAutoMsg( 'ibEconomy_members_edit' );
				$this->findMember('edit');
			break;
			case 'find_em':
				$this->registry->getClass('class_permissions')->checkPermissionAutoMsg( 'ibEconomy_members_edit' );
				$this->findEm('edit');
			break;
			//******Edit Member******//
			case 'edit_member':
				$this->registry->getClass('class_permissions')->checkPermissionAutoMsg( 'ibEconomy_members_edit' );
				$this->editMember();
			break;
			//******View Portfolio******//
			case 'portfolio':
				$this->registry->getClass('class_permissions')->checkPermissionAutoMsg( 'ibEconomy_portfolio_view' );
				$this->portfolio();
			break;
			//******Send Items******//
			case 'send_items':
				$this->registry->getClass('class_permissions')->checkPermissionAutoMsg( 'ibEconomy_send_items' );
				$this->findMember('send_item');
			break;
			case 'send_item':
				$this->registry->getClass('class_permissions')->checkPermissionAutoMsg( 'ibEconomy_send_items' );
				$this->findEm('send_item');
			break;	
			case 'sendEm':
				$this->registry->getClass('class_permissions')->checkPermissionAutoMsg( 'ibEconomy_send_items' );
				$this->sendEm();
			break;			
			//******Edit Portfolio******//
			case 'edit_portfolio':
				$this->registry->getClass('class_permissions')->checkPermissionAutoMsg( 'ibEconomy_portfolio_edit' );
				$this->editPortfolio();
			break;
			//******Delete Portfolio Items******//
			case 'delete_port_items':
				$this->registry->getClass('class_permissions')->checkPermissionAutoMsg( 'ibEconomy_portfolio_delete' );
				$this->deletePortItems();
			break;
			//******MASS Tools******//
			case 'mass_donate':
				$this->registry->getClass('class_permissions')->checkPermissionAutoMsg( 'ibEconomy_mass_donate' );
				$this->massDonate();
			break;
			case 'convert_points':
				$this->registry->getClass('class_permissions')->checkPermissionAutoMsg( 'ibEconomy_mass_convert' );
				$this->convertPts();
			break;	
			case 'mass_delete':
				$this->registry->getClass('class_permissions')->checkPermissionAutoMsg( 'ibEconomy_mass_delete' );
				$this->massDelete();
			break;
		}
		
		#footer
		$this->registry->output->html .= $this->html->footer();		
		
		#output
		$this->registry->output->html_main .= $this->registry->output->global_template->global_frame_wrapper();
		$this->registry->output->sendOutput();
	}
	
	/**
	 * Mass Donate!
	 */	
	private function massDonate()
	{	
		#init
		$to			= $this->request['id'];
		$amount		= $this->registry->ecoclass->makeNumeric($this->request['amount'], true);	
		
		#no group id or all?
		if ( !$to )
		{
			$this->registry->output->showError( $this->lang->words['no_one_to_donate_to'] );
		}
		
		#no item/item id?
		if ( !$amount )
		{
			$this->registry->output->showError( $this->lang->words['no_amount_entered'] );
		}
		
		#grab specified mems
		$memberIDs = $this->registry->mysql_ibEconomy->grabMembers4Donation( $to );

		#send points and worth
		if ( is_array( $memberIDs ) and count( $memberIDs ) )
		{
			$this->registry->mysql_ibEconomy->massUpdateMemberPts( $memberIDs, $amount, '+', TRUE );
		}
		
		#redirect message
		$groupTitle = ( intval($to) > 0 ) ? $this->caches['group_cache'][$to]['g_title'] : $this->lang->words['all_groups'];
		$this->registry->output->global_message = str_replace('<%PTS_NAME%>', $this->settings['eco_general_currency'], $this->lang->words['mass_donation_complete']);
		$this->registry->output->global_message = str_replace('<%AMOUNT%>', $this->registry->getClass('class_localization')->formatNumber( $amount ), $this->registry->output->global_message );
		$this->registry->output->global_message = str_replace('<%GROUP_NAME%>', $groupTitle, $this->registry->output->global_message );

		#rebuild cache
		$this->cache->rebuildCache('ibEco_stats','ibEconomy');
		
		#write log
		$this->registry->adminFunctions->saveAdminLog( $this->registry->output->global_message );				
		
		#get out o here
		$this->registry->output->silentRedirectWithMessage( $this->settings['base_url'].'&amp;app=ibEconomy' );
	}

	/**
	 * Mass Delete!
	 */	
	private function massDelete()
	{
		#init
		$toDelete 		= explode('_', $this->request['delete_item']);
		$toDeleteType 	= $toDelete[0];
		$toDeleteID 	= $toDelete[1];
		
		#no item/item id?
		if ( !$toDeleteType or $toDeleteType == 'none' )
		{
			$this->registry->output->showError( $this->lang->words['no_type_entered_2_delete'] );
		}

		#delete em
		$asset = $this->registry->mysql_ibEconomy->massDelete( $toDeleteType, $toDeleteID );
		
		#redirect/log
		$this->registry->output->global_message = str_replace('<%ASSET_NAME%>', $asset, $this->lang->words['mass_asset_deletion_successful']);
		
		#rebuild cache
		$this->registry->ecoclass->acm(array('stats','portfolios'));
		
		#write log
		$this->registry->adminFunctions->saveAdminLog( $this->registry->output->global_message );				
		
		#get out o here
		$this->registry->output->silentRedirectWithMessage( $this->settings['base_url'].'&amp;app=ibEconomy' );		
	}
	
	/**
	 * Convert from other point systems
	 */	
	private function convertPts()
	{
		#run conversion
		$this->registry->mysql_ibEconomy->convertPts2EcoPts( $this->request['pts_field'] );
		
		#rebuild cache
		$this->cache->rebuildCache('ibEco_stats','ibEconomy');
		
		#redirect
		$this->registry->output->global_message = str_replace('<%ECO_PTS_NAME%>', $this->settings['eco_general_currency'], $this->lang->words['pts_conversion_done']);
		$this->registry->output->global_message = str_replace('<%PTS_NAME%>', $this->request['pts_field'], $this->registry->output->global_message );

		#write log
		$this->registry->adminFunctions->saveAdminLog( $this->registry->output->global_message );				
		
		#get out o here
		$this->registry->output->silentRedirectWithMessage( $this->settings['base_url'].'&amp;app=ibEconomy' );		
	}
	
	/**
	 * Send those items you giving bastard!
	 */	
	private function sendEm()
	{	
		#init
		$itemTypeID			= explode('_', $this->request['itemtype_id']);
		$bankType			= $this->request['bank_type'];
		$amount				= $this->registry->ecoclass->makeNumeric($this->request['amount'], true);	
		$type				= $itemTypeID[0];
		$id					= $itemTypeID[1];
		$typeAbr 			= $this->registry->ecoclass->getTypeAbr($type);
		$daItem				= $this->caches['ibEco_'.$type.'s'][ $id ];
		
		#no member id?
		if ( !$this->request['member_id'] )
		{
			$this->registry->output->showError( $this->lang->words['no_mem_found'] );
		}
		#no item/item id?
		if ( !$itemTypeID || !$type || !$id || !$daItem )
		{
			$this->registry->output->showError( str_replace('<%TYPE%>', $this->lang->words['basic_item'], $this->lang->words['error_no_id']) );
		}
		
		#make cart item
		$cartItem = array('c_type_class' 	=> $bankType,
						  'c_quantity'		=> $amount,
						  'c_type_id'		=> $id
					     );	

		#make cart item
		$member	  = array('member_id' 				=> $this->request['member_id'],
						  'members_display_name'	=> $this->request['member_name']
					     );							 

		#good thing we already wrote this for the cart...
		$this->registry->ecoclass->addItem2Portfolio( $cartItem, $type, $daItem, 0, $member );
		
		#redirect message
		$this->registry->output->global_message = str_replace('<%MEM_NAME%>', $this->request['member_name'], $this->lang->words['member_item_sent']);
		$this->registry->output->global_message = str_replace('<%ITEM_NAME%>', $daItem[ $typeAbr.'_title'], $this->registry->output->global_message);

		#rebuild cache
		$this->registry->ecoclass->acm(array('stats','portfolios'));
		
		#write log
		$this->registry->adminFunctions->saveAdminLog( sprintf( $this->lang->words['member_item_sent_done'], $daItem[ $typeAbr.'_title'], $this->request['member_name'] ) );				
		
		#get out o here
		$this->registry->output->silentRedirectWithMessage( $this->settings['base_url'].$this->html->form_code.'&amp;do=find_em&amp;mem_id='.$this->request['member_id'] );
	}	
	
	/**
	 * Find Member to Edit,yo
	 */	
	private function findMember($type)
	{	
		#buttons
		$buttonRows = $this->registry->class_ibEco_CP->makeButtonRow( array('tools') );	
		
		#output
		$this->registry->output->html .= $this->html->findMember( $buttonRows, $type );	
	}
	
	/**
	 * Really, find em!
	 */	
	private function findEm($type)
	{
		#init
		$member_name 	= $this->request['mem_name'];
		$member_id 		= $this->request['mem_id'];
		$portItemRows	= "";
		$memsStuff		= array();
		
		#get Id if we need it
		if ( !$member_id )
		{
			$mem 		= IPSMember::load( $member_name, 'pfields_content', 'displayname' );
			$member_id 	= $mem['member_id'];
			
			if ( !$member_id )
			{
				$mem 		= IPSMember::load( $member_name, 'pfields_content' );
				$member_id 	= $mem['member_id'];
			}
		}

		#load with ID
		$member = IPSMember::load( $member_id, 'all' );

		#format some mem stuff
		$member['total_points'] 	= $this->registry->ecoclass->makeNumeric($member[ $this->settings['eco_general_pts_field'] ], false);
		$member['eco_worth'] 		= $this->registry->ecoclass->makeNumeric($member['eco_worth'], false);
		
		if ($this->settings['eco_plugin_ppns_on'] && ($member['ibEco_plugin_ppns_prefix'] || $member['ibEco_plugin_ppns_suffix'] || $this->settings['eco_plugin_ppns_use_gf']))
		{
			$member['formatted_name'] 	= IPSMember::makeNameFormatted( $member['members_display_name'], $member['member_group_id'], $member['ibEco_plugin_ppns_prefix'], $member['ibEco_plugin_ppns_suffix'] ); 
		}
		else
		{
			$member['formatted_name'] 	= $member['members_display_name']; 
		}
		
		#no member id passed?
		if( !$member['member_id'] )
		{
			$this->registry->output->showError( $this->lang->words['no_mem_found'] );
		}

		if ( $type == 'edit' ) 
		{
			if ( $this->settings['eco_general_cache_portfolio'] )
			{
				$memsStuff = $this->caches['ibEco_portfolios'][ $member['member_id'] ];
			}
			else
			{
				$this->registry->mysql_ibEconomy->grabPortfolioItems( $member['member_id'], 'all', 'cache' );
				
				if ( $this->DB->getTotalRows() )
				{
					while ( $row = $this->DB->fetch() )
					{
						$memsStuff[] = $row;
					}
				}
			}
			if ( is_array ( $memsStuff ) )
			{
				foreach ( $memsStuff AS $itemNum => $memThing )
				{
					#we only one portfolios indexed by number...
					if ( !intval($itemNum) && $this->settings['eco_general_cache_portfolio'] )
					{
						continue;
					}
					#format for rows
					$memThing = $this->registry->ecoclass->formatPortRow( $memThing );
					
					$portItemRows	   .= $this->html->memPortfolioListRow($memThing);
				}
			}
			
			#build port tab
			$portTab = $this->html->memPortfolioListWrapper( $portItemRows );
			
			#buttons
			$buttonRows = $this->registry->class_ibEco_CP->makeButtonRow( array('send_item','tools') );		
			
			#output
			$this->registry->output->html .= $this->html->memberForm( $member, $portTab, $buttonRows );
		}
		else
		{
			#buttons
			$buttonRows = $this->registry->class_ibEco_CP->makeButtonRow( array('send_item','tools') );		
			
			#output
			$this->registry->output->html .= $this->html->sendItemForm( $member, $buttonRows );		
		}
		
	}	
	
	/**
	 * Port Item Inventory Form
	 */
	private function portItemForm()
	{	
		#no member ID?		
		if ( !$this->request['member_id'] )
		{				
			$this->registry->output->showError( $this->lang->words['no_mem_found']);
		}
		
		#no portfolio item ID?		
		if ( !$this->request['id'] )
		{				
			$this->registry->output->showError( str_replace('<%TYPE%>', $this->lang->words['port_item'], $this->lang->words['error_no_id']) );
		}
		
		#grab portfolio item
		if ( $this->settings['eco_general_cache_portfolio'] )
		{
			$portItem = $this->caches['ibEco_portfolios'][ $this->request['member_id'] ][ $this->request['id'] ];
		}
		else
		{
			$portItem = $this->registry->mysql_ibEconomy->grabSinglePortItem( $this->request['member_id'], $this->request['id'] );
		}

		#no port item on file, sir
		if ( !$portItem['p_id'] )
		{				
			$this->registry->output->showError( $this->lang->words['no_port_item_found']);
		}
		
		#load member with ID
		$member = IPSMember::load( $portItem['p_member_id'], 'all' );
		
		#grab this cart types class object
		$portItemType = $this->registry->ecoclass->grabCartTypeClass($portItem['p_type']);
		
		#format a bit
		if ($this->settings['eco_plugin_ppns_on'] && ($member['ibEco_plugin_ppns_prefix'] || $member['ibEco_plugin_ppns_suffix'] || $this->settings['eco_plugin_ppns_use_gf']))
		{
			$member['formatted_name'] 	= IPSMember::makeNameFormatted( $member['members_display_name'], $member['member_group_id'], $member['ibEco_plugin_ppns_prefix'], $member['ibEco_plugin_ppns_suffix'] ); 
		}
		else
		{
			$member['formatted_name'] 	= $member['members_display_name']; 
		}
		
		$portItem['title']			= $portItemType->name();
		$portItem['type']			= ( $portItem['p_type_class'] ) ? ' - '.$this->lang->words[ $portItem['p_type_class'] ] : '';
		
		#buttons
		$buttonRows = $this->registry->class_ibEco_CP->makeButtonRow( array('send_item','tools') );			
		
		#output
		$this->registry->output->html .= $this->html->portfolioItemForm( $member, $portItem, $buttonRows );	
	}
	
	/**
	 * Edit that mem's eco stuff yo!
	 */
	private function editMember()
	{
		#no ID? error!						  
		if ( ! $this->request['member_id'] )
		{		
			$this->registry->output->showError( str_replace('<%TYPE%>', $this->lang->words['member'], $this->lang->words['error_no_id']) );
		}
		
		#do points
		$this->registry->mysql_ibEconomy->updateMemberPts2SpecNum( $this->request['member_id'], $this->request['total_points'], 0 );

		#do rest
		$member_data  = array('eco_worth'		=> $this->request['eco_worth'],
							 'eco_welfare' 		=> $this->request['eco_welfare'],
							 'eco_on_welfare' 	=> $this->request['eco_on_welfare']
						    );
							
		#do update				  
		$this->DB->update( 'pfields_content', $member_data, 'member_id='.$this->request['member_id'] );
		
		#any items need deleting?
		if ( $this->request['port_items'] )
		{
			foreach ( $this->request['port_items'] AS $pItem )
			{
				if ( $this->request['delete_item_'.$pItem ] )
				{
					#delete it
					$this->DB->delete( 'eco_portfolio', 'p_id = ' . $pItem  );					
				}
			}
		}
		
		#redirect message
		$this->registry->output->global_message = str_replace('<%MEM_NAME%>', $this->request['member_name'], $this->lang->words['member_edited']);
		
		#rebuild cache
		$this->registry->ecoclass->acm(array('stats','portfolios'));
		
		#write log
		$this->registry->adminFunctions->saveAdminLog( sprintf( $this->lang->words['member_edit_done'], $this->request['member_name'] ) );				
		
		#get out o here
		$this->registry->output->silentRedirectWithMessage( $this->settings['base_url'].$this->html->form_code.'&amp;do=find_em&amp;mem_id='.$this->request['member_id'] );	
	}	
	
	/**
	 * Edit that port item's specs, yo
	 */
	private function editPortItem()
	{
		#no ID? error!						  
		if ( ! $this->request['p_id'] )
		{		
			$this->registry->output->showError( str_replace('<%TYPE%>', $this->lang->words['port_item'], $this->lang->words['error_no_id']) );
		}
		
		#create data array
		$pItem_data  = array('p_amount' 		=> $this->request['p_amount'],
							 'p_max'			=> $this->request['p_max'],
							 'p_rate' 			=> $this->request['p_rate']
						    );
							
		#do update				  
		$this->DB->update( 'eco_portfolio', $pItem_data, 'p_id='.$this->request['p_id'] );
						
		$this->registry->output->global_message = str_replace('<%PI_NAME%>', $this->request['port_name'], $this->lang->words['port_item_edited']);
		$this->registry->output->global_message = str_replace('<%MEM_NAME%>', $this->request['member_name'], $this->registry->output->global_message );
		
		#rebuild cache
		$this->registry->ecoclass->acm('portfolios');

		#write log
		$this->registry->adminFunctions->saveAdminLog( sprintf( $this->lang->words['port_item_edit_done'], $this->request['member_name'], $this->request['port_name'] ) );				
		
		#get out o here
		$this->registry->output->silentRedirectWithMessage( $this->settings['base_url'].$this->html->form_code.'&amp;do=find_em&amp;mem_id='.$this->request['member_id'] );	
	}
	
	/**
	 * Really, find em!
	 */	
	private function portfolio()
	{
		#count em
		$numPortItemTotal = $this->registry->mysql_ibEconomy->countPortItems( 'all', $ids='', $this->request['p_type'], $mem='' );

		#query em
		$this->registry->mysql_ibEconomy->grabPortfolioItems( $mem='', 'all', $type='acp', $ids='', $this->request['p_type'], $this->request['st'], $this->request['sort'], $this->request['sw'] );

		#no portfolio items in the db
		if ( ! $this->DB->getTotalRows() )
		{
			$this->registry->output->showError( $this->lang->words['no_port_items_to_show'] );		
		}
		
		#make loop through items
		while ( $row = $this->DB->fetch() )
		{
			#format for rows
			$row = $this->registry->ecoclass->formatPortRow( $row );
			if ($this->settings['eco_plugin_ppns_on'] && ($row['ibEco_plugin_ppns_prefix'] || $row['ibEco_plugin_ppns_suffix'] || $this->settings['eco_plugin_ppns_use_gf']))
			{
				$row['formatted_name'] 	= IPSMember::makeNameFormatted( $row['members_display_name'], $row['member_group_id'], $row['ibEco_plugin_ppns_prefix'], $row['ibEco_plugin_ppns_suffix'] ); 
			}
			else
			{
				$row['formatted_name'] 	= $row['members_display_name']; 
			}			
			
			$portItemRows	   .= $this->html->portfolioListRow($row);
		}
		
		#title
		$showType = $this->lang->words[ $this->request['p_type'] ];
		
		#page links
		$pages		= $this->registry->output->generatePagination( array(
					'totalItems'        => $numPortItemTotal,
					'itemsPerPage'  	=> $this->settings['eco_acp_pp'],
					'currentStartValue'	=> intval($this->request['st']),
					'baseUrl'           => $this->settings['base_url'].$this->form_code."&amp;do=portfolio&amp;p_type={$this->request['p_type']}&amp;sort={$this->request['sort']}&amp;sw={$this->request['sw']}",
		)      );		
		
		#buttons
		$buttonRows = $this->registry->class_ibEco_CP->makeButtonRow( array('send_item','tools') );		
		
		#output
		$this->registry->output->html .= $this->html->portfolioListWrapper( $portItemRows, $showType, $buttonRows, $pages );	
	}	
	
	/**
	 * Delete!
	 */		
	private function deletePortItems()
	{
		$deleted = 0;
		
		#any items need deleting?
		if (is_array($this->request['port_items']) && count($this->request['port_items']))
		{
			foreach ( $this->request['port_items'] AS $pItem )
			{
				if ( $this->request['delete_item_'.$pItem ] )
				{
					$this->DB->delete( 'eco_portfolio', 'p_id = ' . $pItem  );
						
					$deleted++;
				}
			}	
		}
		else if ( intval($this->request['id_to_delete']) )
		{
			$this->DB->delete( 'eco_portfolio', 'p_id = ' . intval($this->request['id_to_delete']) );
						
			$deleted++;		
		}
		
		#no items in your cart, son!
		if ( ! $deleted )
		{
			$this->registry->output->showError( $this->lang->words['no_port_items_selected'] );		
		}		

		#rebuild da cache
		$this->registry->ecoclass->acm('portfolios');
				
		#save log
		$itemsDeletedText = str_replace( '<%NUMBER%>', $deleted, $this->lang->words['port_items_deleted'] );
		$this->registry->adminFunctions->saveAdminLog( $itemsDeletedText );		
		
		#message
		$this->registry->output->global_message = $itemsDeletedText;
		
		#get out o here
		$this->registry->output->silentRedirectWithMessage( $this->settings['base_url'].$this->html->form_code.'&amp;do=portfolio' );	
	}
	
}