<?php

/**
 * (e32) ibEconomy
 * Admin Module: Shop
 * @ ACP
 * + Shop Items
 * + Shop Categories
 */

if ( ! defined( 'IN_ACP' ) )
{
	print "<h1>Incorrect access</h1>You cannot access this file directly. If you have recently upgraded, make sure you upgraded 'admin.php'.";
	exit();
}

class admin_ibEconomy_shop_shop extends ipsCommand
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
		ipsRegistry::getClass('class_localization')->loadLanguageFile( array( 'admin_tools' ), 'core' );
		ipsRegistry::getClass('class_localization')->loadLanguageFile( array( 'admin_forums' ), 'forums' );		
		
		#format langs
		$this->lang->words['default_msg_desc'] = sprintf( $this->lang->words['default_msg_desc'], $this->lang->words['variable'] );
		
		#do form codes
		$this->form_code 		= $this->html->form_code    = 'module=shop&amp;section=shop';
		$this->form_code_js 	= $this->html->form_code_js = 'module=shop&section=shop';
		
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
				$this->registry->getClass('class_permissions')->checkPermissionAutoMsg( 'ibEconomy_delete_shop_items' );
				$this->delete();
			break;
		
			//******Settings******//		
			case 'shop_settings':
				$this->registry->getClass('class_permissions')->checkPermissionAutoMsg( 'ibEconomy_shop_settings' );			
				$this->registry->class_ibEco_CP->doSettings( $this->request['do'], $this->form_code );
			break;
				
			//******Shop Items******//
			case 'item':
				$this->registry->getClass('class_permissions')->checkPermissionAutoMsg( 'ibEconomy_shop_items_edit' );
				$this->itemForm();
			break;
			case 'do_item':
				$this->registry->getClass('class_permissions')->checkPermissionAutoMsg( 'ibEconomy_shop_items_edit' );
				$this->doItem();
			break;
			case 'items':
			default:			
				$this->registry->getClass('class_permissions')->checkPermissionAutoMsg( 'ibEconomy_shop_items_view' );
				$this->listItems();
			break;
			
			//******Shop Categories******//
			case 'cat':
				$this->registry->getClass('class_permissions')->checkPermissionAutoMsg( 'ibEconomy_shop_cats_edit' );
				$this->catForm();
			break;
			case 'do_cat':
				$this->registry->getClass('class_permissions')->checkPermissionAutoMsg( 'ibEconomy_shop_cats_edit' );
				$this->doCat();
			break;
			case 'cats':
			default:			
				$this->registry->getClass('class_permissions')->checkPermissionAutoMsg( 'ibEconomy_shop_cats_view' );
				$this->listCats();
			break;
			
			//******newImage******//
			case 'newImage':
				$this->registry->getClass('class_permissions')->checkPermissionAutoMsg( 'ibEconomy_shop_items_edit' );
				$this->newImage();
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
		require_once( IPS_KERNEL_PATH . 'classAjax.php' );
		$ajax			= new classAjax();
		
		#which item	
		$itemTypes		= array('cats','items');
		
		foreach ( $itemTypes as $type )
		{
			if ( is_array($this->request[ $type ]) AND count($this->request[ $type ]) )
			{
				$thisType = $type;
				break;
			}
		}
		
		#which DB and field
		$thisTypeDB 	= ( $thisType == 'cats' ) ? 'eco_shop_cats': 'eco_shop_items';
		$thisTypeField 	= ( $thisType == 'cats' ) ? 'sc': 'si';
		
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
		$this->registry->ecoclass->acm(array('shopcats','shopitems'));
		
		#a ok
 		$ajax->returnString( 'OK' );
 		exit();
	}
	
	/**
	 * Add/Edit Shop Item
	 */	
	private function itemForm()
	{
		#init
		$content = array();		
		
		if ( $this->request['type'] == 'edit' )
		{
			if ( ! $this->request['si_id'] )
			{
				$this->registry->output->showError( str_replace('<%TYPE%>', $this->lang->words['shop_item'], $this->lang->words['error_no_id']) );			
			}
			
			#get bank
			$this->DB->build( array( 'select'	=> 'esi.*',
									 'from'		=> array('eco_shop_items' => 'esi'),
									 'where'	=> 'esi.si_id='.$this->request['si_id'],
												   'add_join' => array(
																		array(
																				'select' => 'p.*',
																				'from'   => array( 'permission_index' => 'p' ),
																				'where'  => "p.app = 'ibEconomy' AND p.perm_type='shopitem' AND perm_type_id=esi.si_id",
																				'type'   => 'left',
																			 ) 
														               )
							)		);
			$this->DB->execute();
			
			if ( !$this->DB->getTotalRows() )
			{
				$this->registry->output->showError( str_replace('<%TYPE%>', $this->lang->words['shop_item'], $this->lang->words['error_no_id']) );	
			}	
			else
			{				
				$content 					= $this->DB->fetch();
				$content['si_cost'] 		= $this->registry->ecoclass->makeNumeric($content['si_cost'], false);
				$content['si_url_image']	= $this->registry->ecoclass->awardImageURL($content);

				#get file paired with this item
				require_once( IPSLib::getAppDir( 'ibEconomy' ).'/sources/shop_items/'.$content['si_file'] );
				$shopItem =  new class_shop_item( $this->registry );				
			}
		}
		else
		{
			#connect with original item
			require_once( IPSLib::getAppDir( 'ibEconomy' ).'/sources/shop_items/'.$this->request['shop_item_file'] );
			$shopItem =  new class_shop_item( $this->registry );	
			
			#format some defaults
			$content['si_title'] 	= $shopItem->title();
			$content['si_desc'] 	= $shopItem->description();
			$content['si_file'] 	= $this->request['shop_item_file'];			
		}
		
		#do extra settings and file name
		$extraSettings	= $shopItem->extra_settings();
		
		if ( is_array( $extraSettings ) )
		{
			#loop through settings to create rows
			foreach ( $extraSettings AS $setting )
			{
				if ( $setting['type'] == 'groups' )
				{
					$setting['dd'] = $this->registry->ecoclass->getGroups('shop_item');
				}
				else if ( $setting['type'] == 'groups2Choose' )
				{
					$setting['dd'] = $this->registry->ecoclass->getGroups('');
				}				
				else if ( $setting['type'] == 'forums' )
				{
					$setting['dd'] = $this->registry->class_ibEco_CP->getForums();
				}
				else if ( $setting['type'] == 'forumsNoText' )
				{
					$setting['dd'] = $this->registry->class_ibEco_CP->getForums(FALSE, FALSE);
				}				
				else if ( $setting['type'] == 'shopitems' )
				{
					$setting['dd'] = $this->registry->class_ibEco_CP->getAllItems(array('shopitem'), TRUE);
				}
				else if ( $setting['type'] == 'forums_password' )
				{
					$setting['dd'] = $this->registry->class_ibEco_CP->getForums(FALSE, TRUE);
				}
				else if ( $setting['type'] == 'image_types' )
				{
					$setting['dd'] = $this->registry->class_ibEco_CP->getImageTypes();
				}				
				else if ( $setting['type'] == 'awards' )
				{
					#(PIN) Award Management System installed and enabled?
					$app_cache 	= $this->cache->getCache('app_cache');
					$awards  	= $app_cache[ 'awards' ];

					#SOS VIP Members not installed?
					if( ! $awards['app_enabled'] )
					{
						$this->registry->output->global_message = $this->lang->words['award_management_system__not_installed'];
						return;
					}
		
					$setting['dd'] = $this->registry->class_ibEco_CP->getAwards();
				}
				else if ( $setting['type'] == 'skins' )
				{
					$setting['dd'] = $this->registry->class_ibEco_CP->html_fetchSetsDropDown();
				}
				
				#insert current values
				$setting['value'] = $content[ $setting['field'] ];
				
				$content['extra_settings'] .= $this->html->itemExtraSettingRow( $setting );
			}
		}
		else
		{
			#no extra settings
			$no['words'] = $this->lang->words['no_extra_settings'];
			$content['extra_settings'] = $this->html->itemExtraSettingRow( $no );
		}		
		
		#own or self needed?
		$content['own_or_other'] = ( $shopItem->otherOrSelf() ) ? TRUE : FALSE;
		
		#buttons
		$buttonRows = $this->registry->class_ibEco_CP->makeButtonRow( array('tools', 'add_sc') );
		
		#get permission matrix
		$matrix_html    = $this->registry->getClass('class_perms')->adminPermMatrix( 'shopitem', $content );	
		
		#output
		$this->registry->output->html .= $this->html->itemForm( $content, $matrix_html, $buttonRows );
	}
	
	/**
	 * Add Edit Shop Item
	 */
	private function doItem()
	{		
		#INIT
		$si_protected 		 = $_POST['si_protected'];
		$si_protected_g	 	 = $_POST['si_protected_g'];
		$si_extra_settings_1 = $_POST['si_extra_settings_1'];
		$si_extra_settings_2 = $_POST['si_extra_settings_2'];
		$si_extra_settings_3 = $_POST['si_extra_settings_3'];
		$si_extra_settings_4 = $_POST['si_extra_settings_4'];
		$si_extra_settings_5 = $_POST['si_extra_settings_5'];
		$si_extra_settings_6 = $_POST['si_extra_settings_6'];
		$si_default_pm 		 = trim($_POST['si_default_pm']);
		
		#create data array
		$item_datas = array('si_title'				=> $this->request['si_title'],
							'si_desc'				=> $this->request['si_desc'],
							'si_cat'				=> $this->request['si_cat'],
							'si_cost'				=> $this->registry->ecoclass->makeNumeric($this->request['si_cost'], true),
							'si_inventory'			=> $this->request['si_inventory'],
							'si_restock'			=> $this->request['si_restock'],
							'si_restock_amt'		=> $this->request['si_restock_amt'],
							'si_restock_time'		=> $this->request['si_restock_time'],
							'si_limit'				=> $this->request['si_limit'],
							'si_other_users'		=> $this->request['si_other_users'],
							'si_allow_user_pm'		=> $this->request['si_allow_user_pm'],
							'si_default_pm'			=> $si_default_pm,
							'si_min_num'			=> $this->request['si_min_num'],
							'si_max_num'			=> $this->request['si_max_num'],
							'si_protected'			=> ( $si_protected && is_array($si_protected) && count($si_protected) ) ? ',' . implode( ",", $si_protected ) . ',' : $si_protected,
							'si_protected_g'		=> ( $si_protected_g && is_array($si_protected_g) && count($si_protected_g) ) ? ',' . implode( ",", $si_protected_g ) . ',' : $si_protected_g,
							'si_on'					=> $this->request['si_on'],
							'si_use_perms'			=> $this->request['si_use_perms'],
							'si_file'				=> $this->request['si_file'],
							'si_max_daily_buys'		=> $this->request['si_max_daily_buys'],
							'si_extra_settings_1'	=> ( $si_extra_settings_1 && is_array($si_extra_settings_1) && count($si_extra_settings_1) ) ? ',' . implode( ",", $si_extra_settings_1 ) . ',' : $si_extra_settings_1,
							'si_extra_settings_2'	=> ( $si_extra_settings_2 && is_array($si_extra_settings_2) && count($si_extra_settings_2) ) ? ',' . implode( ",", $si_extra_settings_2 ) . ',' : $si_extra_settings_2,
							'si_extra_settings_3'	=> ( $si_extra_settings_3 && is_array($si_extra_settings_3) && count($si_extra_settings_3) ) ? ',' . implode( ",", $si_extra_settings_3 ) . ',' : $si_extra_settings_3,
							'si_extra_settings_4'	=> ( $si_extra_settings_4 && is_array($si_extra_settings_4) && count($si_extra_settings_4) ) ? ',' . implode( ",", $si_extra_settings_4 ) . ',' : $si_extra_settings_4,
							'si_extra_settings_5'	=> ( $si_extra_settings_5 && is_array($si_extra_settings_5) && count($si_extra_settings_5) ) ? ',' . implode( ",", $si_extra_settings_5 ) . ',' : $si_extra_settings_5,
							'si_extra_settings_6'	=> ( $si_extra_settings_6 && is_array($si_extra_settings_6) && count($si_extra_settings_6) ) ? ',' . implode( ",", $si_extra_settings_6 ) . ',' : $si_extra_settings_6
						   );
		
		#if this is an inv award, do the award's image
		if ( $this->request['si_file'] == 'award_item.php' && ($this->settings['awds_system_status'] === '0' || $this->settings['awds_system_status'] === '1'))
		{
			$awardCatAndAward = explode('_', $si_extra_settings_1);

			$award = $this->DB->buildAndFetch( array( 	'select' => '*',
														'from'   => 'inv_awards',
														'where'  => 'id = ' . $awardCatAndAward[1],
												)		);
													
			$item_datas['si_image'] = $award['icon'];
		}
		
		#insert or update...		
		if ( ! $this->request['si_id'] )
		{			
			$item_datas['si_added_on'] = time();
			$this->DB->insert( 'eco_shop_items', $item_datas );
			$new_id = $this->DB->getInsertId();
		
			$this->registry->output->global_message = $this->lang->words['shop_item_added'];	
		}
		else
		{						  
			$this->DB->update( 'eco_shop_items', $item_datas, 'si_id='.$this->request['si_id'] );
			
			$this->registry->output->global_message = str_replace('<%ITEM_NAME%>', $this->request['si_name'], $this->lang->words['item_edited']);			
		}
				
		$si_id = ( $this->request['si_id'] ) ? $this->request['si_id'] : $new_id;
		
		#do image if this is the initial creation
		if ( !$this->request['si_id'] )
		{
			$return = $this->registry->class_ibEco_CP->imageForInitialItem('shop_item', $si_id);
		}
		
		#do perms
		$this->registry->getClass('class_perms')->savePermMatrix( $this->request['perms'], $si_id, 'shopitem' );
		
		#rebuild cache
		$this->registry->ecoclass->acm(array('shopcats','shopitems','portfolios','stats'));		

		#write log
		$logType = ( ! $this->request['si_id'] ) ? $this->lang->words['added'] : $this->lang->words['edited'];
		$this->registry->adminFunctions->saveAdminLog( sprintf( $this->lang->words['item_done'], $logType, $this->request['si_name'] ) );				
		
		#get out o hear
		$this->registry->output->silentRedirectWithMessage( $this->settings['base_url'].$this->html->form_code.'&amp;do=items' );	

	}
	
	/**
	 * List Shop Items
	 */	
	private function listItems()
	{
		#init
		$content = "";
		$where	 = intval($this->request['cat_id']) ? 'esc.sc_id = '.intval($this->request['cat_id']) : 'esi.si_id > 0';
		
		#get cats
		$this->DB->build( array( 	'select'	=> 'esi.*',
									'from'		=> array( 'eco_shop_items' => 'esi' ),
									'group'		=> 'esi.si_id',
									'order'		=> 'esi.si_position',
									'where'  	=> $where,
									'add_join'	=> array(
														0 => array( 'select' 	=> 'SUM(ep.p_amount) as total_items',
																		'from'	=> array( 'eco_portfolio' => 'ep' ),
																		'where'	=> "esi.si_id = ep.p_type_id and p_type = 'shopitem'",
																		'type'	=> 'left',
																  ),
														1 => array( 'select' 	=> 'esc.sc_id',
																		'from'	=> array( 'eco_shop_cats' => 'esc' ),
																		'where'	=> "esi.si_cat = esc.sc_id",
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
				$row['si_cost'] 		= $this->registry->ecoclass->makeNumeric($row['si_cost'], false);
				$row['si_url_image']	= $this->registry->ecoclass->awardImageURL($row);

				$content .= $this->html->itemRow( $row );
			}
		}

                #grab default shop items from folder
                $defaultItems = $this->registry->class_ibEco_CP->getShopItemFiles();
		
		#buttons
		$buttonRows = $this->registry->class_ibEco_CP->makeButtonRow( array('tools', 'cache', 'add_sc') );		
		
		#output
		$this->registry->output->html .= $this->html->itemsOverviewWrapper( $content, $defaultItems, $buttonRows );
	}
	
	/**
	 * Add/Edit Cat Form
	 */	
	private function catForm()
	{
		#init
		$content = array();
		
		if ( $this->request['type'] == 'edit' )
		{
			if ( ! $this->request['sc_id'] )
			{
				$this->registry->output->showError( str_replace('<%TYPE%>', $this->lang->words['cat'], $this->lang->words['error_no_id']) );			
			}
			
			#get bank
			$this->DB->build( array( 'select'	=> 'esc.*',
									 'from'		=> array('eco_shop_cats' => 'esc'),
									 'where'	=> 'esc.sc_id='.$this->request['sc_id'],
												   'add_join' => array(
																		array(
																				'select' => 'p.*',
																				'from'   => array( 'permission_index' => 'p' ),
																				'where'  => "p.app = 'ibEconomy' AND p.perm_type='shop_cat' AND perm_type_id=esc.sc_id",
																				'type'   => 'left',
																			 ) 
														               )
							)		);
			$this->DB->execute();
			
			if ( !$this->DB->getTotalRows() )
			{
				$this->registry->output->showError( str_replace('<%TYPE%>', $this->lang->words['cat'], $this->lang->words['error_no_id']) );	
			}	
			else
			{
				$content = $this->DB->fetch();
			}
		}
		
		#buttons
		$buttonRows = $this->registry->class_ibEco_CP->makeButtonRow( array('tools') );
		
		#get permission matrix
		$matrix_html    = $this->registry->getClass('class_perms')->adminPermMatrix( 'shop_cat', $content );	
		
		#output
		$this->registry->output->html .= $this->html->catForm( $content, $matrix_html, $buttonRows );
	}
	
	/**
	 * Add Edit Category
	 */
	private function doCat()
	{		
		#init
		$sc_desc 		 = trim($_POST['sc_desc']);
		
		#create data array
		$cat_datas  = array('sc_title'			=> $this->request['sc_title'],
							'sc_desc'			=> $sc_desc,
							'sc_on'				=> $this->request['sc_on'],
							'sc_use_perms'		=> $this->request['sc_use_perms']
						   );
		
		#insert or update...		
		if ( ! $this->request['sc_id'] )
		{					  
			$this->DB->insert( 'eco_shop_cats', $cat_datas );
			$new_id = $this->DB->getInsertId();
		
			$this->registry->output->global_message = $this->lang->words['shop_cat_added'];	
		}
		else
		{						  
			$this->DB->update( 'eco_shop_cats', $cat_datas, 'sc_id='.$this->request['sc_id'] );
			
			$this->registry->output->global_message = str_replace('<%CAT_NAME%>', $this->request['sc_name'], $this->lang->words['cat_edited']);			
		}
				
		$sc_id = ( $this->request['sc_id'] ) ? $this->request['sc_id'] : $new_id;
		
		#do image if this is the initial creation
		if ( !$this->request['sc_id'] )
		{
			$return = $this->registry->class_ibEco_CP->imageForInitialItem('shop_cat', $sc_id);
		}
		
		#do perms
		$this->registry->getClass('class_perms')->savePermMatrix( $this->request['perms'], $sc_id, 'shop_cat' );
		
		#rebuild cache
		$this->cache->rebuildCache('ibEco_shopcats','ibEconomy');

		#write log
		$logType = ( ! $this->request['sc_id'] ) ? $this->lang->words['added'] : $this->lang->words['edited'];
		$this->registry->adminFunctions->saveAdminLog( sprintf( $this->lang->words['cat_done'], $logType, $this->request['sc_name'] ) );				
		
		#get out o hear
		$this->registry->output->silentRedirectWithMessage( $this->settings['base_url'].$this->html->form_code.'&amp;do=cats' );	
	}
	
	/**
	 * List Shop Categories
	 */	
	private function listCats()
	{
		#init
		$content = "";
		
		#get cats
		$this->DB->build( array( 	'select'	=> 'esc.*',
									'from'		=> array( 'eco_shop_cats' => 'esc' ),
									'group'		=> 'esc.sc_id',
									'order'		=> 'esc.sc_position',
									'add_join'	=> array(
														0 => array( 'select' 	=> 'COUNT(esi.si_id) as total_items',
																		'from'	=> array( 'eco_shop_items' => 'esi' ),
																		'where'	=> "esi.si_cat = esc.sc_id",
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
				$content .= $this->html->catRow( $row );
			}
		}
		
		#buttons
		$buttonRows = $this->registry->class_ibEco_CP->makeButtonRow( array('tools', 'cache') );		
		
		#output
		$this->registry->output->html .= $this->html->catsOverviewWrapper( $content, $buttonRows );
	}
	
	/**
	 * Delete!
	 */		
	private function delete()
	{
		#no eco items passed?
		if( ! in_array( $this->request['type'], array( 'cat', 'item' ) ) )
		{
			$this->registry->output->showError( $this->lang->words['error_no_type_to_delete'] );
		}
		#no id?
		if( ! $this->request['id'] )
		{
			$this->registry->output->showError( str_replace('<%TYPE%>', $this->request['type'], $this->lang->words['error_no_id']) );
		}
		
		#you had to do all items in this one function?
		$prefix 	= ( $this->request['type'] == 'cat' ) ? 'sc' : 'si';
		$type 		= ucfirst($this->request['type']);
		$itemName 	= $this->caches['ibEco_shop'.$type.'s'][ $this->request['id'] ][ $prefix.'_title' ];
		
		#delete it
		$this->DB->delete( 'eco_shop_'.$this->request['type'].'s', $prefix.'_id = ' . $this->request['id'] );
		
		#delete corresponding assets
		if ($this->request['type'] == 'item')
		{
			$this->DB->delete( 'eco_portfolio', "p_type_id = " . $this->request['id']." AND p_type = 'shopitem'" );

			#delete corresponding cart items
			$this->DB->delete( 'eco_cart', "c_type_id = " . $this->request['id']." AND c_type = 'shopitem'" );		
		}

		#rebuild da cache
		$this->registry->ecoclass->acm(array('shopcats','shopitems','portfolios','stats'));
				
		#save log
		$this->registry->adminFunctions->saveAdminLog( sprintf( $this->lang->words['item_deleted'], $this->lang->words[ $this->request['type'] ], $itemName ) );		
		
		#message
		$this->registry->output->global_message = str_replace('<%TYPE%>', $this->lang->words[ $this->request['type'] ], $this->lang->words['type_deleted']);
		
		#get out o hear
		$this->registry->output->silentRedirectWithMessage( $this->settings['base_url'].$this->html->form_code.'&amp;do='.$this->request['type'].'s' );	
	}
	
	/**
	 * Upload a Custom image item for a shop item/cat or investment
	 * Or delete it
	 */		
	public function newImage()
	{	
		#init
		$itemType = trim($this->request['item_type']);
		$itemID	  = intval($this->request['item_id']);
		
		#upload the image
		$uploadedImage = $this->registry->class_ibEco_CP->uploadImage( $itemID, $itemType );
		
		#message		
		if( $uploadedImage['status'] == 'no_image' )
		{
			$this->registry->output->showError( $this->lang->words['no_image_selected'] );
		}
		else if ($uploadedImage['status'] == 'deleted')
		{
			$this->registry->output->global_message = $this->lang->words['image_deleted'];
		}
		else if ($uploadedImage['status'] == 'ok')
		{
			$this->registry->output->global_message = $this->lang->words['image_added'];
		}
		
		else if( $uploadedImage['status'] != 'ok' )
		{
			$this->registry->output->showError( $this->lang->words['image_failed_because'] . " " .$uploadedImage['error'] );
		}		

		#create url map
		$itemMap = $this->registry->class_ibEco_CP->itemMap( $itemType );
		
		#create data array
		$imageName = ( $uploadedImage['t_final_image_name'] ) ? $uploadedImage['t_final_image_name'] : $uploadedImage['final_image_name'];
		$datas  = array($this->registry->ecoclass->getTypeAbr($itemType).'_image' => $imageName);
		
		#insert or update...								  
		$this->DB->update( $itemMap[$itemType]['db'], $datas, $itemMap[$itemType]['db_fld'].'='.$itemID );
		
		#recache necessary items
		$this->registry->ecoclass->acm($itemMap[$itemType]['cache_name']);
		 
		#get out o hear
		$this->registry->output->silentRedirectWithMessage( $this->settings['base_url'].'&app=ibEconomy&amp;module='.$itemMap[$itemType]['module'].'&amp;section='.$itemMap[$itemType]['section'].'&amp;do='.$itemMap[$itemType]['single'].'&amp;type=edit&amp;'.$itemMap[$itemType]['type_id'].'='.$itemID );		
	}
}