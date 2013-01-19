<?php

/**
 * (e32) ibEconomy
 * Main Class
 * @ EVERYWHERE
 */

if ( ! defined( 'IN_IPB' ) )
{
	print "<h1>Incorrect access</h1>You cannot access this file directly. If you have recently upgraded, make sure you upgraded all the relevant files.";
	exit();
}

class class_ibEconomy
{
	protected 	$registry;
	protected 	$DB;
	protected 	$settings;
	protected 	$request;
	protected 	$lang;
	protected 	$member;
	protected 	$cache;
	protected 	$caches;
	
	public 		$active;
	public 		$tabs;
	public 		$plugins;
	public 		$pluginNames;
	public		$pluginNamesWithBlocks;
	public 		$decimal;
	public 		$roundDirection;
	public		$defaultAreas;
	public		$tabNames;
	public 		$cartTypes;
	public		$urlAfterArea;
	public 		$DEBUG_ON;
	
	/**
	 * Class entry point
	 */
	public function __construct( ipsRegistry $registry )
	{
		$this->DEBUG_ON = FALSE;
		
		if ($this->DEBUG_ON)
			echo("__construct");
        $this->registry     	=  ipsRegistry::instance();
        $this->DB           	=  $this->registry->DB();
        $this->settings     	=& $this->registry->fetchSettings();
        $this->request      	=& $this->registry->fetchRequest();
        $this->lang         	=  $this->registry->getClass('class_localization');
        $this->member       	=  $this->registry->member();
        $this->memberData  		=& $this->registry->member()->fetchMemberData();
        $this->cache        	=  $this->registry->cache();
        $this->caches       	=& $this->registry->cache()->fetchCaches();

		#load lang
		ipsRegistry::getClass('class_localization')->loadLanguageFile( array( 'public_ibEconomy' ), 'ibEconomy' );
		ipsRegistry::getClass('class_localization')->loadLanguageFile( array( 'admin_ibEconomy' ), 'ibEconomy' );
		
		$this->decimal			= ($this->settings['eco_general_use_decimal']) ? 2 : 0;	
		$this->roundDirection 	= ($this->settings['eco_general_use_decimal']) ? "" : $this->settings['eco_general_round_direction'];
		
		$this->tabNames = array('me', 'global', 'invest', 'shop', 'cash', 'buy');

		if ($this->request['app'] == 'ibEconomy')
		{
			#fix page__st__# issue.
			$urlArray = explode("&", $_SERVER['REQUEST_URI']);
			$lastUrlString = $urlArray[count($urlArray)-1];

			if (strpos($lastUrlString, "__st__"))
			{
				#first fix the requested data
				$lastUrlArray = explode("/", $lastUrlString);
				$lastUrlArray = explode("=", $lastUrlArray[0]);
				$this->request[$lastUrlArray[0]] = $lastUrlArray[1];
				
				#now set the request 'st' to match the page reqested
				$lastUrlArray 	= explode("__st__", $lastUrlString);
				$this->request['st']	= intval($lastUrlArray[1]);
			}
		}
		
		#got cart items?
		require_once( IPSLib::getAppDir( 'ibEconomy' ) . "/sources/cart_types/cart_item.php" );
		$this->fillCartTypesData();
			
		if ($this->request['app'] == 'ibEconomy' || IN_ACP)
		{		
			#any plugins installed?
			$this->fillPluginData();
		}
		
		#if we're in the public side of the application (i.e. not called from ACP or a hook), then create HTML related stuff
		if ($this->request['app'] == 'ibEconomy' && !IN_ACP)
		{
			$this->fillDefaultAreas();
			$this->fillTabData();
			$this->fillActiveData();
		}

		#need award cache? (added in 1.4)
		if( !$this->caches['awards_cat_cache'] )
		{
			$this->caches['awards_cat_cache'] = $this->cache->getCache('awards_cat_cache');
		}
	}
	
	/**
	 * Builds and returns the "More Info" area used when the expander button is clicked under an item
	 */	
	public function grabMoreInfoHtmlForItem($item_id, $item_type, $bank_type=False)
	{
		#loan hotfix
		$item_type		= ( $bank_type != 'loan' ) ? $item_type : $bank_type;
		
		#grab this cart types class object
		$cartItemType = $this->registry->ecoclass->grabCartTypeClass($item_type);
		
		$theItem	= $cartItemType->grabItemByID($item_id);
	
		if ($error = $cartItemType->gotItemCheck($item_id, $theItem))
		{
			return $error;//bug fix as of version 2.0.8 thx to rakuzas
		}
		
		$cartItemType->canAccess(false);
		
		$theItem			= $cartItemType->format($theItem, $bank_type);
		$cartTypeTemplate 	= $cartItemType->htmlTemplate();
		
		return $this->registry->output->getTemplate('ibEconomy2')->$cartTypeTemplate( $theItem );	
	}	

	/**
	* Create and return a new cart_type class object
	*/
	public function grabCartTypeClass($cartTypeKey, $item=false)
	{
		if ($DEBUG_ON)
			echo("grabCartTypeClass");
		require_once( IPSLib::getAppDir( 'ibEconomy' ) . "/sources/cart_types/".$cartTypeKey.".php" );

		$cartTypeClassName 	= "ibEconomy_cart_type_".$cartTypeKey;
		$cartType 			= new $cartTypeClassName($this->registry, $item);
		
		return $cartType;
	}
	
	/**
	* Fill the cartTypes array of the "installed" types of items that can be purchased via the shopping cart
	*/	
	public function fillCartTypesData()
	{
		$this->cartTypes 	= array();
		$cartTypeDir	 	= IPSLib::getAppDir( 'ibEconomy' ) . "/sources/cart_types";
		
		if (is_dir($cartTypeDir))
		{
			$handle = opendir($cartTypeDir);
		
			while ( false !== ($cartTypeFile = readdir($handle)) )
			{
				if ($DEBUG_ON)
					echo("fillCartTypesData::while");
				if ($cartTypeFile != "." && $cartTypeFile != "..")
				{
					$cartTypeKey 	= preg_replace("[\.php]", "", $cartTypeFile);
					$cartType 		= $this->grabCartTypeClass($cartTypeKey);
							
					if ($this->settings[ $cartType->onOffSetting() ] && $cartType->on())
					{
						#what else needs to get stored in the cartType array?
						$cartTypeArray = array('key'				=> $cartTypeKey,
											 'name'				=> $cartType->name(),
											 'enabled'			=> $cartType->on(),
											 'savedInPortfolio'	=> $cartType->savedInPortfolio(),
											 'on_off_setting'	=> $cartType->onOffSetting(),
											 'perm_group_field'	=> $cartType->permissionGroupField(),
											);
						
						#build array of "installed" cartTypes
						$this->cartTypes[$cartTypeKey] = $cartTypeArray;
					}
				}
			}
		}

		// $this->showVars($this->cartTypes);
		// exit;		
	}
	
	/**
	* Fill the default areas for each tab
	*/	
	public function fillDefaultAreas()
	{
		foreach ($this->tabNames AS $tabName)
		{
			switch($tabName)
			{
				case 'me':
					$this->defaultAreas[ $tabName ] = 'my_overview';
				break;
				case 'global':
					$this->defaultAreas[ $tabName ] = ( $this->settings['eco_general_welcome_on'] ) ? 'frontpage' : 'overview';
				break;
				case 'invest':
					$this->defaultAreas[ $tabName ] = $this->settings['eco_banking_fp'];
				break;
				case 'shop':
					$this->defaultAreas[ $tabName ] = 'categories';
				break;
				case 'cash':
					$this->defaultAreas[ $tabName ] = 'welfare';
				break;
				case 'buy':
					$this->defaultAreas[ $tabName ] = 'cart';
				break;				
			}
		}
	}
	
	/**
	* Create and return a new html output class for a plugin
	*/
	public function grabPluginSkinCpClass($pluginKey)
	{
		$pluginCpSkinName = "cp_skin_ibEconomy_plugin_".$pluginKey.".php";
		require_once( IPSLib::getAppDir( 'ibEconomy' ) . "/sources/plugins/".$pluginKey."/skin_cp/".$pluginCpSkinName );

		$pluginCpSkinClassName 	=  preg_replace("[\.php]", "", $pluginCpSkinName);;
		$pluginCpSkin 			= new $pluginCpSkinClassName($this->registry);
		
		return $pluginCpSkin;
	}
	
	/**
	* Create and return a new plugin class object
	*/
	public function grabPluginClass($pluginKey)
	{
		require_once( IPSLib::getAppDir( 'ibEconomy' ) . "/sources/plugins/".$pluginKey."/".$pluginKey.".php" );

		$pluginClassName 	= "ibEconomy_plugin_".$pluginKey;
		$plugin 			= new $pluginClassName($this->registry);
		
		return $plugin;
	}
	
	/**
	 * Fills our $plugins global var with the tab names and such
	 */	
 	public function fillPluginData()
 	{
		$this->plugins 	= array();
		$pluginDir	 	= IPSLib::getAppDir( 'ibEconomy' ) . "/sources/plugins";
		
		if (is_dir($pluginDir))
		{
			$handle = opendir($pluginDir);
		
			while ( false !== ($pluginFdrName = readdir($handle)) )
			{
				if (is_dir($pluginDir."/".$pluginFdrName) && ($pluginFdrName != "." && $pluginFdrName != ".."))
				{
					$pluginFldrhandle = opendir($pluginDir."/".$pluginFdrName);
					
					while ( false !== ($pluginFileName = readdir($pluginFldrhandle)) )
					{
						if ($pluginFileName != "." && $pluginFileName != ".." && $pluginFileName == $pluginFdrName.".php")
						{
							$plugin 	= $this->grabPluginClass($pluginFdrName);
							$pluginName = preg_replace("[\.php]", "", $pluginFileName);
							
							#what else needs to get stored in the plugin array?
							$pluginArray = array('isPlugin'			=> true,
												 'key'				=> $pluginName,
												 'name'				=> $plugin->name(),
												 'enabled'			=> $plugin->on(),
												 'on_off_setting'	=> $plugin->onOffSetting(),
												 'title'			=> $plugin->areaTitle(),
												 'group_settings' 	=> $plugin->grabGroupSettings(),
												 'public_settings'	=> $plugin->grabSettingsForDisplay(),
												 'perm_group_field'	=> $plugin->permissionGroupField(),
												 'has_block'		=> $plugin->hasBlock()
												);
												
							#build big array ordered by tab and such for html
							#use "no_tab" if the plugin does not list a tab (and therefore won't be listed as public page)
							$tab = ($plugin->tab()) ? $plugin->tab() : 'no_tab';
							$this->plugins[ $tab ][ $pluginName ] = $pluginArray;
							
							#build small array of just the names of installed/enabled plugins
							$this->pluginNames[] = $pluginName;
							
							#build small array of names of plugins with a sidebar block
							if ($plugin->hasBlock())
							{
								$this->pluginNamesWithBlocks[ $pluginName ] = $plugin->name();
							}
							
							// $this->showVars($plugin);
							// exit;
						}
					}
				}
			}
		}
		
		//$this->showVars($this->plugins);
	}
	
	/**
	 * Fills our $tabs global var with the tab names and such
	 */	
 	public function fillTabData()
 	{
		$this->tabs = array();
		
		foreach ($this->tabNames AS $tabName)
		{
			$thisTabsViewableAreas = $this->fillTabWithAreas($tabName);
			
			#if there are viewable areas, then add that tab to $tabs..
			if ( is_array($thisTabsViewableAreas) && count($thisTabsViewableAreas) )
			{
				$this->tabs[ $tabName ] = $thisTabsViewableAreas;
			}
		}

		//$this->showVars($this->tabs);
	}
	
	/**
	 * Fills each $tab with it's necessary area sidebars
	 * Checking if the current viewer has access first
	 */	
 	public function fillTabWithAreas($tabName)
 	{
		$areas = array();
		
		#add default areas
		switch ($tabName)
		{
			case 'me':
				$areas['my_overview'] 		= array('name' => $this->lang->words['glance'], 			'title' => $this->lang->words['view_glance']);
				if ($this->canAccess('shop', true))
				{
					$areas['my_shopitems'] 	= array('name' => $this->lang->words['my_shopitems'], 		'title' => $this->lang->words['view_my_shopitems']);
				}
				if ($this->canAccess('banks', true))
				{
					$areas['my_banks'] 		= array('name' => $this->lang->words['my_banks'], 			'title' => $this->lang->words['view_my_banks']);
				}
				if ($this->canAccess('stocks', true))
				{
					$areas['my_stocks'] 	= array('name' => $this->lang->words['my_stocks'], 			'title' => $this->lang->words['view_my_stocks']);
				}
				if ($this->canAccess('ccs', true))
				{
					$areas['my_ccs'] 		= array('name' => $this->lang->words['my_credit'], 			'title' => $this->lang->words['view_my_credit']);
				}
				if ($this->canAccess('lts', true))
				{
					$areas['my_lts'] 		= array('name' => $this->lang->words['my_long_term'], 		'title' => $this->lang->words['view_my_lt']);
				}
				if ($this->canAccess('loans', true))
				{
					$areas['my_loans'] 		= array('name' => $this->lang->words['my_loans'], 			'title' => $this->lang->words['view_my_loans']);
				}
				if ($this->canAccess('welfare', true))
				{
					$areas['my_welfare'] 	= array('name' => $this->lang->words['my_welfare'], 		'title' => $this->lang->words['view_my_welfare']);
				}
			break;
			
			case 'global':
				if ($this->settings['eco_general_welcome_on'] )
				{
					$areas['frontpage'] 	= array('name' => $this->lang->words['welcome'], 			'title' => $this->lang->words['view_welcome']);
				}			
				$areas['overview'] 			= array('name' => $this->lang->words['overview'], 			'title' => $this->lang->words['view_overview']);
				if ($this->canAccess('rankings', true))
				{
					$areas['rankings'] 			= array('name' => $this->lang->words['rankings'], 		'title' => $this->lang->words['view_richest']);	
				}					
				$areas['find_member'] 		= array('name' => $this->lang->words['find_member'], 		'title' => $this->lang->words['view_find_member']);
				if ($this->canAccess('transactions', true))
				{
					$areas['transactions'] 	= array('name' => $this->lang->words['transactions'], 		'title' => $this->lang->words['view_transactions']);
				}
				if ($this->canAccess('assets', true))
				{
					$areas['portfolio'] 	= array('name' => $this->lang->words['global_assets'], 		'title' => $this->lang->words['view_global_assets']);
				}
				if ($this->canAccess('settings', true))
				{
					$areas['settings'] 		= array('name' => $this->lang->words['settings'], 			'title' => $this->lang->words['view_settings']);
				}
			break;
			
			case 'invest':
				if ($this->canAccess('banks', true))
				{
					$areas['banks'] 		= array('name' => $this->lang->words['banks'], 				'title' => $this->lang->words['view_bank_menu']);
				}
				if ($this->canAccess('stocks', true))
				{
					$areas['stocks'] 		= array('name' => $this->lang->words['stocks'], 			'title' => $this->lang->words['view_stock_menu']);
				}
				if ($this->canAccess('ccs', true))
				{
					$areas['ccs'] 			= array('name' => $this->lang->words['credit_cards'], 		'title' => $this->lang->words['view_cc_menu']);
				}
				if ($this->canAccess('lts', true))
				{
					$areas['lts'] 			= array('name' => $this->lang->words['long_term'], 			'title' => $this->lang->words['view_long_term_menu']);
				}
			break;
			
			case 'shop':
				if ($this->canAccess('shop', true))
				{
					$areas['categories'] 	= array('name' => $this->lang->words['all_categories'], 	'title' => $this->lang->words['view_all_cats']);array();
					$areas['items'] 		= array('name' => $this->lang->words['all_items'], 			'title' => $this->lang->words['view_items']);
				}			
			break;
			case 'cash':
				if ($this->canAccess('welfare', true))
				{
					$areas['welfare'] 		= array('name' => $this->lang->words['welfare'], 	'title' => $this->lang->words['view_welfare_info']);
				}
				if ($this->canAccess('loans', true))
				{
					$areas['loans'] 		= array('name' => $this->lang->words['loans'], 		'title' => $this->lang->words['view_loan_info']);
				}
				if ($this->canAccess('lotterys', true))
				{
					$areas['lottery'] 		= array('name' => $this->lang->words['lotterys'], 	'title' => $this->lang->words['view_lottery_info']);
				}	
			break;	
			case 'buy':
				if ($this->canAccess('cart', true))
				{
					$areas['cart'] 			= array('name' => $this->lang->words['shopping_cart'], 	'title' => $this->lang->words['view_shopping_cart']);
					$areas['checkout'] 		= array('name' => $this->lang->words['checkout'], 		'title' => $this->lang->words['view_checkout']);
				}				
			break;
		}
		
		#add single area
		if ($this->request['area'] == 'single')
		{
			$areas['single']				= array('name' => $this->lang->words[ $this->request['type'] ], 	'title' => $this->lang->words[ $this->request['type'] ]);
		}
		
		#add plugin areas
		if (is_array($this->plugins[ $tabName ]) && count($this->plugins[ $tabName ]))
		{
			foreach ($this->plugins[ $tabName ] AS $pluginKey => $pluginData)
			{
				if ($this->canAccess('plugin', true, $pluginData))
				{
					$areas[$pluginKey]		= $pluginData;
				}
			}
		}
		
		return $areas;
	}	
	
	/**
	 * Master Can Access function to test any and all pages for on/off and group perms
	 */	
 	public function canAccess($stuff, $returnNeeded, $pluginData=false)
 	{
		$on			= false;
		$allowed 	= false;

		#fix some input
		if ($stuff == 'shopitem' || $stuff == 'shopitems')
		{
			$stuff = 'shop';
		}
		
		#dealing with a plugin?
		if (is_array($pluginData) && count($pluginData))
		{
			if ($pluginData['on_off_setting'])
			{
				$on = $this->checkOnOffPosition( $pluginData['on_off_setting'], 'page_is_off', $pluginData['name'], $returnNeeded);			
			}
			else
			{
				$on = true;
			}
			if ($pluginData['perm_group_field'])
			{
				$allowed = $this->checkGroupPerms( $pluginData['perm_group_field'], 'no_perm_to_play', $pluginData['name'], $returnNeeded );
			}
			else
			{
				$allowed = false;
			}
		}
		else
		{
			switch($stuff)
			{
				case 'banks':
				case 'stocks':
				case 'lts':
				case 'ccs':
				case 'transactions':
				case 'assets':
				case 'loans':
				case 'lotterys':
				case 'welfare':
				case 'rankings':
					$on 			= $this->checkOnOffPosition( 'eco_'.$stuff.'_on', 'page_is_off', $stuff, $returnNeeded);
					$allowed 		= 1;
				break;

				case 'shop':
					$on 			= $this->checkOnOffPosition( 'eco_shopitems_on', 'page_is_off', 'the_shops', $returnNeeded);
					$allowed 		= $this->checkGroupPerms( 'g_eco_shopitem', 'no_perm_to_play', $this->lang->words[ 'the_shops' ], $returnNeeded );
				break;	

				break;
				
				case 'settings':
					$on 		= $this->checkOnOffPosition( 'eco_settings_page_on', 'page_is_off', 'settings', $returnNeeded);
					$allowed 	= 1;
				break;
				
				case 'cart':
					$on 		= $this->checkOnOffPosition( 'eco_cashier_on', 'page_is_off', 'purchases', $returnNeeded);
					$allowed 	= 1;
				break;
				
				case 'donations':
					$on 		= $this->checkOnOffPosition( 'eco_dons_on', 'page_is_off', 'donations' );
					$allowed 	= 1;
				break;
				
				case 'general':
					$on 		= $this->checkOnOffPosition( 'eco_general_on', 'ibeco_is_off', '' );
					$allowed 	= $this->checkGroupPerms( 'g_eco', 'no_eco_permission', 0 );
				break;
				
				case 'edit_points':
					$on 		= 1;
					$allowed 	= $this->checkGroupPerms( 'g_eco_edit_pts', 'no_perm_to_edit_pts', $this->settings['eco_general_pts_field'] );
				break;
				
			}		
		}

		return $on && $allowed;
	}
	
	/**
	 * A helper function for our main Active Data Populator
	 */	
 	public function fillActiveDataHelper()
 	{
		#set up area properly
		if ( is_array($this->tabs[ $this->active['tab'] ]) && count($this->tabs[ $this->active['tab'] ]) )
		{
			$curAreaInAllowedAreas = in_array($this->active['area'], array_keys($this->tabs[ $this->active['tab'] ]));
		}

		if ( $this->active['area'] == "" || !$curAreaInAllowedAreas )
		{
			if ( $this->request['area'] != "")
			{
				$this->active['area'] = $this->request['area'];
			}
			else if ( is_array($this->tabs[ $this->active['tab'] ]) && count($this->tabs[ $this->active['tab'] ]) )
			{
				$this->active['area'] = key($this->tabs[ $this->active['tab'] ]);
			}
		}
	}
	
	/**
	 * Fills our $Active global var with current tab, area, switch, sort, etc
	 */	
 	public function fillActiveData()
 	{
		#init
		$this->active 		= array();
		
		#switch direction
		$this->active['sw'] 	= ( $this->request['sw'] != 'ASC' ) ? 'ASC' : 'DESC';
		$this->request['sw'] 	= ( $this->request['sw'] ) ? $this->request['sw'] : 'DESC';

		#tab
		if ( $this->request['tab'] == "" && $this->request['do'] == "" )
		{
			#custom landing page (new to 1.3)
			$landingPage = array();
			$landingPage = $this->findLandingPage();		

			$this->active['tab'] 	= $landingPage['tab'];
			$this->active['area'] 	= ($this->request['area'] != "") ? $this->request['area'] : $landingPage['area'];
		}
		else if ( $this->request['tab'] != "" )
		{
			$this->active['tab'] 	= $this->request['tab'];
		}
		else
		{
			$this->active['tab'] = 'global';
		}
				
		#area plus other random active dealies
		switch( $this->active['tab'] )
		{		
			case 'me':
			
				#set up area properly
				$this->fillActiveDataHelper();

				#which area within the me tab are we on?
				switch( $this->active['area'] )
				{	
					case 'single':
						$this->active['item_name'] = $this->lang->words[ $this->request['type'] ];
					break;
				}			
			break;
			
			case 'shop':

				#set up area properly
				$this->fillActiveDataHelper();	

				#do active category tab
				$this->active['cat_name'] = ( $this->request['cat'] ) ? $this->caches['ibEco_shopcats'][ $this->request['cat' ] ]['sc_title']  : '';

				#which area within the me tab are we on?
				switch( $this->active['area'] )
				{
					case 'single':
						$this->active['item_name'] = ( $this->caches['ibEco_shopitems'][ $this->request['id'] ]['si_title'] ) ? $this->caches['ibEco_shopitems'][ $this->request['id'] ]['si_title'] : $this->lang->words[ $this->request['type'] ];
					break;
					case 'all':
						$this->active['area'] = 'categories';
					break;
				}
			break;

			case 'invest':
			
				#extra needed for investments tab...
				if ( $this->request['area'] == "")
				{
					$this->active['area'] = $this->defaultAreas[ $this->active['tab'] ]; 
				}
				
				#set up area properly
				$this->fillActiveDataHelper();

				#which area within the investments tab are we on?
				switch( $this->active['area'] )
				{
					case 'single':
						$this->active['item_name'] = $this->lang->words[ $this->request['type'] ];
					break;
				}
			break;

			default:
			case 'global':
			case 'cash':
			case 'buy':
				
				#set up area properly
				$this->fillActiveDataHelper();

			break;
		}

		#kinda a special situation... when there is a do request we are in "global mode"
		#so we don't want to go to an area, but rather "do" some action
		
		#to make it trickier, when there is a "when" request, that DOES need an area
		#because it is used on the ME tab, which needs the tab and area to do...
		if ( $this->request['do'] && !$this->request['when'] )
		{
			$this->active['area'] 	= '';
		}
		#just give em the default area for the tab since we still don't have a place to go
		else if ( $this->active['area'] == "")
		{
			$this->active['area'] = $this->defaultAreas[ $this->active['tab'] ];
		}
		
		#are we in a plugin area?
		$this->active['inPlugin'] = (is_array($this->pluginNames) && count($this->pluginNames) && in_array($this->active['area'], $this->pluginNames)) ? true : false;
		
		#sync up request data too
		$this->request['tab'] 	= $this->active['tab'];
		$this->request['area'] 	= $this->active['area'];
	}
	
	/**
	 * Check group permission to be at a specific spot in this app
	 */	
 	public function checkGroupPerms( $g_field, $error, $replace, $returnNeeded='', $mem='' )
 	{
		#init check
		$check = 0;
		
		#me or task?
		$mem = ( $mem ) ? $mem : $this->memberData;
		
		#create group array for me
		$this_member_mgroups[] = $mem['member_group_id'];

		if( $mem['mgroup_others'] )
		{
			$this_member_mgroups = array_merge( $this_member_mgroups, explode( ",", IPSText::cleanPermString( $mem['mgroup_others'] ) ) );
		}
		
		#check off if group has perm
		foreach( $this_member_mgroups as $this_member_mgroup )
		{
			if ( $this->caches['group_cache'][$this_member_mgroup][ $g_field ] == 1 )
			{
				$check = 1;
				break;
			}
		}
		
		#error if not allowed to be here
		if ( ! $check  && ! $returnNeeded )
		{
			if ( $replace )
			{
				$error = str_replace( "<%TYPE%>"  , $replace, $this->lang->words[ $error ] );
			}
			
			$this->registry->output->showError( $error );
		}

		#let em know the ruling
		if ( $returnNeeded )
		{		
			return $check;
		}		
	}
	
	/**
	 * Check to make sure page has been turned on and therefore we allowed to c
	 */	
 	public function checkOnOffPosition( $area, $error, $replace_me, $returnNeeded='' )
 	{
		#init
		$returnVal = 1;

		#lotto fix (Added in ibEco 1.5)
		$replace_me = ( $replace_me == 'lottery' ) ? 'lotterys' : $replace_me;
		
		#page enabled?  if not, NO SOUP FOR YOU!
		if ( ! $this->settings[ $area ] || $this->settings[ $area ] == '0' )
		{
			if ( ! $returnNeeded  )
			{
				if ( $replace_me )
				{
					$error = str_replace( "<%PAGE%>"  , $this->lang->words[ $replace_me ], $this->lang->words[ $error ] );
				}
				
				$this->registry->output->showError( $error );
			}
			else
			{
				$returnVal = 0;
			}
		}
		
		#let em know the ruling
		if ( $returnNeeded )
		{		
			return $returnVal;
		}
	}
	
	/**
	 * Finish up a shop item use, i.e. add log and delete and update portfolios
	 */	
 	public function finishUpItemUse($theItem,$myPortItem,$subject='')
 	{	
		#log it
		$this->addLog( 'use_shopitem', 1, $theItem[ 'si_id'], $subject, $theItem[ 'si_title'] );

		#delete it!
		if ( $myPortItem['p_amount'] < 2 )
		{
			$this->DB->delete( 'eco_portfolio', 'p_id = '.$myPortItem['p_id'] );
		}
		else
		{
			$newTotal = $myPortItem['p_amount'] - 1;
			$this->DB->update( 'eco_portfolio', array('p_amount' => $newTotal), 'p_id='.$myPortItem['p_id'] );
		}
		
		#update cache
		$this->acm('portfolios');
	}
	
	/**
	 * Toggle side-panel for non-JS h8rs?
	 */	
 	public function _toggleSidePanel()
 	{
		#currently their cookie says... lets reverse it
 		$current	= IPSCookie::get('hide_eco_sidebar');
 		$new		= $current ? 0 : 1;
 		
		#set the updated cookie! not a schickerdoodle but still not bad
 		IPSCookie::set( 'hide_eco_sidebar', $new );
 		
		#bonk em back to where they were 
 		$this->registry->getClass('output')->silentRedirect( $this->settings['base_url'].'app=ibEconomy&amp;tab='.$this->request['url'].'&amp;area='.$this->request['area'].'&amp;type='.$this->request['type'].'&amp;id='.$this->request['id']);
 	}
	
	/**
	 * Format a few things related to ibEco players
	 */	
 	public function formatEcoMember( $member )
 	{
		#add those damn commas
		$member['eco_points'] 	= $this->registry->getClass('class_localization')->formatNumber( $member['eco_points'], $this->decimal );
		$member['eco_worth'] 	= $this->registry->getClass('class_localization')->formatNumber( $member['eco_worth'], $this->decimal ); 

		#send em back
		return $member;
	}
	
	/**
	 * simple eh?
	 */	
 	public function makeItemList()
 	{
		return array( 'bank', 'stock', 'cc', 'lt', 'shopitem', 'loan' );
	}

	/**
	 * Create overview of items/worth array
	 */	
 	public function makeOverviewMap()
 	{
		#array
		$words 		= array( 'points' 		=> array( 'image' => 'money.png', 		 'title'  => $this->settings['eco_general_currency'], 'words1' => $this->lang->words['total'].' '.$this->settings['eco_general_currency'], 		
									'words2' => ($this->settings['eco_worth_on']) ? $this->lang->words['total_worth'] : $this->lang->words['rank'] ), 		
							 'banks_' 		=> array( 'image' => 'building_key.png', 'title'  => $this->lang->words['banks'], 			  'words1' => $this->lang->words['total_savings'],  'words2' => $this->lang->words['total_checking'] ),
							 'stocks_' 		=> array( 'image' => 'chart_curve.png',  'title'  => $this->lang->words['stocks'], 			  'words1' => $this->lang->words['total_shares'],   'words2' => $this->lang->words['total_share_value'] ),
							 'ccs_' 		=> array( 'image' => 'creditcards.png',  'title'  => $this->lang->words['ccs'], 			  'words1' => $this->lang->words['total_debt'],     'words2' => $this->lang->words['total_credit_line'] ),
							 'lts_' 		=> array( 'image' => 'bar_graph.png',    'title'  => $this->lang->words['lts'],				  'words1' => $this->lang->words['num_investments'],'words2' => $this->lang->words['total_inv_worth'] ),
							 'shopitems_' 	=> array( 'image' => 'tag_blue.png',     'title'  => $this->lang->words['shop_items'],		  'words1' => $this->lang->words['num_shop_items'], 'words2' => $this->lang->words['total_shopitem_worth'] ),
							 'loans_' 		=> array( 'image' => 'money_add.png',    'title'  => $this->lang->words['loans'],		      'words1' => $this->lang->words['num_loans'],      'words2' => $this->lang->words['total_loan_debt'] ),
						  );
						  
		#sendit  back
		return $words;
	}

	/**
	 *  get that item type abbreviation
	 */	
	public function getTypeAbr($type)
	{
		if ( $type == 'lottery' || $type == 'lotterys' )
		{
			$typeAbr = 'l';
		}	
		else if ( $type == 'shopitem' || $type == 'shop_item' )
		{
			$typeAbr = 'si';
		}
		else if ( $type == 'shopcat' || $type == 'shop_cat' )
		{
			$typeAbr = 'sc';
		}
		else if ( in_array( $type, array('loan','loans') ) )
		{
			$typeAbr = 'b';
		}
		else if (in_array( $type, array('bank','stock') ))
		{
			$typeAbr = $type[0];
		}
		else if ( $type == 'credit_card' || $type == 'long_term' )
		{
			$typeAbr = ( $type == 'credit_card' ) ? 'cc' : 'lt';
		}
		else
		{
			if ($type != "")
			{
				$cartItemType = $this->registry->ecoclass->grabCartTypeClass($type);
				$typeAbr = $cartItemType->abbreviation();
			}
			else
			{
				$typeAbr = $type;		
			}
		}
		
		return $typeAbr;
	}
	
	/**
	 *  get that item type abbreviation
	 */	
	public function getDBName($type)
	{
		if ( in_array($type, array('shopitem', 'shop_item', 'si', 'shopitems', 'shop_items')) )
		{
			$dbName = 'eco_shop_items';
		}
		else if ( in_array($type, array('shopcat', 'shop_cat', 'sc', 'shopcats', 'shop_cats')) )
		{
			$dbName = 'eco_shop_cats';
		}
		else if ( in_array($type, array('bank', 'banks', 'b')) )
		{
			$dbName = 'eco_banks';
		}
		else if ( in_array($type, array('creditcard', 'credit_card', 'cc', 'ccs', 'creditcards', 'credit_cards')) )
		{
			$dbName = 'eco_credit_cards';
		}
		else if ( in_array($type, array('stock', 's', 'stocks')) )
		{
			$dbName = 'eco_stocks';
		}
		else if ( in_array($type, array('longterm', 'long_term', 'lt', 'lts', 'longterms', 'long_terms')) )
		{
			$dbName = 'eco_long_terms';
		}
		else if ( in_array($type, array('lottery', 'lotto')) )
		{
			$dbName = 'eco_lotteries';
		}		
		else if ( in_array($type, array('loan', 'loans')) )
		{
			$dbName = 'eco_banks';
		}
		else
		{
			if ($type != "")
			{
				$cartItemType = $this->registry->ecoclass->grabCartTypeClass($type);
				$dbName = $cartItemType->dbName();
			}
		}
		
		return $dbName;
	}

		/**
	 *  get that item type abbreviation
	 */	
	public function getPortTypeName($type)
	{
		if ( in_array($type, array('shopitem', 'shop_item', 'si', 'shopitems', 'shop_items')) )
		{
			$portTypeName = 'shopitem';
		}
		else if ( in_array($type, array('bank', 'banks', 'b')) )
		{
			$portTypeName = 'bank';
		}
		else if ( in_array($type, array('creditcard', 'credit_card', 'sc', 'scs', 'creditcards', 'credit_cards')) )
		{
			$portTypeName = 'cc';
		}
		else if ( in_array($type, array('stock', 's', 'stocks')) )
		{
			$portTypeName = 'stock';
		}
		else if ( in_array($type, array('longterm', 'long_term', 'lt', 'lts', 'longterms', 'long_terms')) )
		{
			$portTypeName = 'lt';
		}
		else if ( in_array($type, array('lottery', 'lotto')) )
		{
			$portTypeName = 'lottery';
		}
		else
		{
			$portTypeName = $type;
		}		

		return $portTypeName;
	}

	/**
	 * Out half-assed function to make numbers proper
	 */	 
	public function makeNumeric($number, $mayNeedAdjustments)
	{
		#removed as of 2.0.8 thanks to Johnymnemo
		#$number = str_replace(',', '', $number);
		
		$number = ($this->settings['eco_general_use_decimal']) ? floatval($number) : intval($number);

		#number parsing bug fix for Demeter
		$number = str_replace(',', '.', $number);
		
		if ( !(strpos($number, "'") === false && strpos($number, "\"") === false) )
		{
			$number = 0;
		}

		if ($mayNeedAdjustments)
		{
			$number = preg_replace('/[^0-9.]+/', '', $number);
			
			if (($pos = strpos($number, '.')) !== false) 
			{
				$number = substr($number, 0, $pos+1).str_replace('.', '', substr($number, $pos+1));
			}
		}
		
		$number = ($this->settings['eco_general_use_decimal']) ? $number : $this->roundIt($number);

		return $number;
	}
	
	/**
	 * Round the number up, down, or to closest whole number
	 */	 
	public function roundIt($number)
	{
		if ($this->roundDirection == "FLOOR")
		{
			$number = floor($number);
		}
		else if ($this->roundDirection == "CEILING")
		{
			$number = ceil($number);
		}
		else if ($this->roundDirection == "ROUND")
		{
			$number = round($number);
		}
		
		return $number;
	}	

	/**
	 * Send off a mailer in the form of a Private Message
	 */		
	public function sendPM($toID, $toName, $amount, $subject, $pmType, $body='', $title='', $fromID=0, $variable='', $formatIt=true)
	{
		#setup messenger lib
		$classToLoad = IPSLib::loadLibrary( IPSLib::getAppDir( 'members' ) . '/sources/classes/messaging/messengerFunctions.php', 'messengerFunctions', 'members' );
		$messengerFunctions = new $classToLoad( $this->registry );

		switch ( $pmType )
		{
			case 'donation':
					
				#sent from
				$fromID = $this->memberData['member_id'];
				
				#replace message variables
				$Finds 	= array( "<#TO_NAME#>", "<#MESSAGE#>", "<#DONATION_AMOUNT#>", "<#FROM_NAME#>" );
				$Rplcs 	= array( $toName, $subject, $amount, $this->memberData['members_display_name'] );

				#assign body/title type
				$title 	= $this->settings['eco_general_pm_title'];
				$body 	= $this->settings['eco_general_pm_body'];
			
			break;
			
			case 'trade':
					
				#sent from
				$fromID = $this->memberData['member_id'];
				
				#replace message variables
				$Finds 	= array( "<#RECIPIENT#>", "<#ITEM#>", "<#QUANTITY#>", "<#SENDER#>" );
				$Rplcs 	= array( $toName, $subject, $amount, $this->memberData['members_display_name'] );

				#assign body/title type
				$title 	= $this->settings['eco_shop_trading_pm_title'];
				$body 	= $this->settings['eco_shop_trading_pm_body'];
			
			break;
			
			case 'lottery_winner':
					
				#sent from
				//$fromID = $fromID;
				
				#replace message variables
				$Finds 	= array( "<#NAME#>", "<#AMOUNT#>" );
				$Rplcs 	= array( $toName, $amount );

				#assign body/title type
				$title 	= $this->settings['eco_lotto_pm_title'];
				$body 	= $this->settings['eco_lotto_pm_body'];
			
			break;			
			
			case 'generic':

				#assign body/title type
				$title 	= $title;
				$body 	= $body;
				
				#replace message variables
				$Finds 	= array( "{".$this->lang->words['variable']."}", "{".strtolower($this->lang->words['variable'])."}" );
				$Rplcs 	= array( $variable, $variable );				
			
			break;			

			default:
			
			$this->registry->output->showError( $this->lang->words['no_type'] );
		}

		if ($formatIt)
		{
			#setup parser
			IPSText::getTextClass('bbcode')->parse_smilies	 = 0;
			IPSText::getTextClass('bbcode')->parse_nl2br   	 = 0;
			IPSText::getTextClass('bbcode')->parse_html    	 = 1;
			IPSText::getTextClass('bbcode')->parse_bbcode    = 0;
			IPSText::getTextClass('bbcode')->parsing_section = 'pms';		


			#format message and title		
			$title 	= IPSText::getTextClass('bbcode')->preDisplayParse( IPSText::getTextClass('bbcode')->preDbParse( $title ) );
			$body 	= IPSText::getTextClass('bbcode')->preDisplayParse( IPSText::getTextClass('bbcode')->preDbParse( $body ) );		
		}

		#replace macros
		$title 	= str_replace( $Finds, $Rplcs, $title );
		$body 	= str_replace( $Finds, $Rplcs, $body );		

		#no specific PM sender so assign default shop item PM sender
		$fromID = (intval($fromID) > 0) ? $fromID : $this->settings['eco_shopitems_pm_sender'];

		#finally... send a message
		$body = IPSText::getTextClass( 'editor' )->method == 'rte' ? nl2br($body) : $body;
		
		try
		{
 			$messengerFunctions->sendNewPersonalTopic( $toID, $fromID, $inviteUsers=False, $title, $body );
 		}
		catch( Exception $error )
		{
			$msg = $error->getMessage();

			if ( strstr( $msg, 'BBCODE_' ) )
			{
				$msg = str_replace( 'BBCODE_', '', $msg );
				
				$this->registry->output->showError( $msg );
			}
			else if ( isset($this->lang->words[ 'err_' . $msg ]) )
			{
				$_msgString = $this->lang->words[ 'err_' . $msg ];
				$_msgString = str_replace( '#NAMES#'   , implode( ",", $this->messengerFunctions->exceptionData ), $_msgString );
				$_msgString = str_replace( '#LIMIT#'   , $this->memberData['g_max_mass_pm'], $_msgString );
				$_msgString = str_replace( '#TONAME#'  , $toName    , $_msgString );
				$_msgString = str_replace( '#FROMNAME#', $this->memberData['members_display_name'], $_msgString );
				$_msgString = str_replace( '#DATE#'    , $this->messengerFunctions->exceptionData[0], $_msgString );
			}
			else
			{
				$_msgString = $this->lang->words['err_UNKNOWN'] . ' ' . $msg;
			}

			$this->registry->output->showError($_msgString);
		}
	}
	
	/**
	 * Add a log
	 */		
	public function addLog( $action, $amount, $subjectID, $subjectName, $note )
	{
        #setup parser
		IPSText::getTextClass('bbcode')->parse_smilies	 = $this->settings['eco_general_dons_emo'];
 		IPSText::getTextClass('bbcode')->parse_nl2br   	 = 0;
 		IPSText::getTextClass('bbcode')->parse_html    	 = 0;
 		IPSText::getTextClass('bbcode')->parse_bbcode    = $this->settings['eco_general_dons_bbc'];
 		IPSText::getTextClass('bbcode')->parsing_section = 'global';
		
		$note = IPSText::getTextClass('bbcode')->preDbParse( $note );
		
		#special case for lottery
		$subjectName = ( $subjectName == 'lottery' ) ? 'lottery_ticket' : $subjectName;
		
		$log = array(
					'l_member_id' 	=> $this->memberData['member_id'],
					'l_action' 		=> $action,
					'l_amount' 		=> $amount,
					'l_subject_name'=> $subjectName,
					'l_subject_id' 	=> $subjectID,
					'l_log' 		=> $note,
					'l_date' 		=> time(),
					'l_ip_address' 	=> $this->member->ip_address
					);
					
		$this->DB->insert( 'eco_logs', $log );
	}
	

	/**
	 * Do the actual adding of the item to a the portfolio
	 */	
	public function addItem2Portfolio( $cartItem, $type, $daItem, $cashAdvance, $member )
	{	
		#init
		$itemArrayFields = array();
		
		#grab this cart types class object
		$officialType	= $type;
		$type			= ( $cartItem['c_type_class'] != 'loan' ) ? $type : $cartItem['c_type_class'];
		$cartItemType 	= $this->registry->ecoclass->grabCartTypeClass($type);
					
		if ( ! $this->settings['eco_general_cache_portfolio'] )
		{
			$portItem = $this->registry->mysql_ibEconomy->grabSinglePortItemExtended( $member['member_id'], $cartItem['c_type_id'], $type );
		}
		else
		{
			$portItem = $this->caches['ibEco_portfolios'][ $member['member_id'] ][ $type.'s_'.$cartItem['c_type_class'] ][ $cartItem['c_type_id'] ];
		}

		$itemArrayFields = $cartItemType->fieldsForAddingToPortfolio($cartItem, $daItem);
			
		$cashAdvance = $cashAdvance + $itemArrayFields['cash_advance'];
		
		#update pre-existing portfolio item
		if ( $cartItem['c_type_class'] != 'loan' && $portItem['p_id'] )
		{
			$newAmount = $cartItem['c_quantity'] + $portItem['p_amount'];
			$this->DB->update( 'eco_portfolio', array('p_amount' => $newAmount), 'p_id = ' .$portItem['p_id'] );						
		}
		else
		{
			#purchase those items (add to portfolio)
			$newPortItem = array( 'p_member_id'	=> $member['member_id'],
								'p_member_name'	=> $member['members_display_name'],
								'p_type'		=> $officialType,
								'p_type_id'		=> $cartItem['c_type_id'],
								'p_type_class'	=> $itemArrayFields['p_type_class'],
								'p_amount'		=> $itemArrayFields['p_amount'],
								'p_max'			=> $itemArrayFields['p_max'],
								'p_rate'		=> $itemArrayFields['p_rate'],
								'p_last_hit'	=> $itemArrayFields['p_last_hit'],
								'p_rate_ends'	=> $itemArrayFields['p_rate_ends'],
								'p_rate_next'	=> 0,
								'p_purch_date'	=> time()
								);
								
			$this->DB->insert( 'eco_portfolio', $newPortItem );
		}
					
		#return cash advance amt
		return $cashAdvance;
	}
	
	/**
	 * Format Shop Category
	 */
	public function formatShopCatRow( $cat )
	{	
		#image
		$cat['image_thumb_link']	= $this->customItemImageHTML($cat['sc_image'], 'application_home.png', true); 
		$cat['image_link']			= $this->customItemImageHTML($cat['sc_image'], 'application_home.png', false);		
		
		#return it
		return $cat;
	}
	
	/**
	 * Format those portfolio item rows!
	 */		
	public function formatPortRow( $item )
	{
		#init
		$item['type']			= ( $item['p_type_class'] == 'loan' ) ? 'loan' : $item['p_type'];
		$cartItemType 			= $this->registry->ecoclass->grabCartTypeClass($item['type'], $item);
		
		#get cost and total sold/invested for profiles
		$extras					= $cartItemType->extraPortfolioItemInfo();
		$item['cost']			= $this->registry->getClass('class_localization')->formatNumber( $extras['cost'], $this->decimal);
		$item['total_bought']	= $extras['total_bought'];
		$item['total_text']		= $extras['total_text'];
		$item['purchased_text']	= $cartItemType->purchasedKeyword();
		
		#format for balloon popup icon..
		$item['tab']			= $cartItemType->tab();
		$item['p_type_class']	= $item['p_type_class'] ? ucfirst($item['p_type_class']) : 'x';
		$item['type_type']		= in_array( $item['p_type_class'], array('savings','checking') ) ? ' ('.$this->lang->words[ $item['p_type_class'] ].')' : '';
		$item['type']			= $this->lang->words[ $item['type'] ].$item['type_type'];
		
		#format display data
		$item['title']			= $cartItemType->title($item);
		$item['quantity']		= $this->registry->getClass('class_localization')->formatNumber($item['p_amount'], $cartItemType->decimalPlacesForSums());
		$item['quantity']		= $cartItemType->currencySymbolForSums().$item['quantity'];
		$item['rate']			= $this->registry->getClass('class_localization')->formatNumber( $item['p_rate'], 2 );
		$item['purchase_date'] 	= $this->registry->getClass( 'class_localization')->getDate( $item['p_purch_date'], 'JOINED' );
	
		#image
		$item['image']				= $cartItemType->icon();
		$item['custom_image']		= $item[$cartItemType->abbreviation().'_image'];
		
		$item['si_url_image']		= $this->registry->ecoclass->awardImageURL($item);
		$item['url_image']			= $item[$cartItemType->abbreviation().'_url_image'];
		$item['image_thumb_link']	= $this->customItemImageHTML($item['custom_image'], $item['image'], true, $item['url_image']); 
		$item['image_link']			= $this->customItemImageHTML($item['custom_image'], $item['image'], false, $item['url_image']);	
		$item['image_link_prf_tab']	= $this->customItemImageHTML($item['custom_image'], $item['image'], false, $item['url_image'], false, true);//sdfsdf
		
		return $item;
	}
	
	/**
	 * Format those block item rows!
	 */		
	public function formatBlockItemRow( $item, $block )
	{		
		#make stuff pretty
		$item['typeAbr']	= $this->registry->ecoclass->getTypeAbr( $block['s_type'] );		
		if ( !in_array( $block['s_type'], array('loan','shopitem') ) )
		{
			$item['tab']	= 'invest';
		}
		else
		{
			$item['tab']	= ( $block['s_type'] == 'loan' ) ? 'cash' : 'shop' ;
		}
		
		$item['type']		= $block['s_type'];
		$item['s_type']		= ( $block['type_type'] ) ? ucfirst($block['type_type']) : 'x';
		$item['id']			= $item[ $item['typeAbr'].'_id' ];
		$item['type_type']	= ( $block['type_type'] ) ? $block['type_type'] : 'x';
		$item['title']		= $item[ $item['typeAbr'].'_title' ];
		$item['start_date'] = $this->registry->getClass( 'class_localization')->getDate( $item[ $item['typeAbr'].'_added_on'], 'JOINED' ); 
	
		return $item;
	}
	
	/**
	 * A master formatter function for use with new Cart Type classes
	 * Creates the new cart type object and calls it's format on the row passed in
	 * $extra is a placeholder in case you need another argument, like "single" for lottery
	 */	
	public function formatRow($row, $type, $extra=FALSE)
	{
		#grab this cart types class object
		$cartItemType 	= $this->grabCartTypeClass($type);
		
		return $cartItemType->format($row, $extra);
	}

	/**
	 * Creates balance transfer dropdown (list other credit-cards with balance)
	 */	
	public function balTransferCards($ccId)
	{	
		#init
		$weHaveDrop = 0;
		
		#portfolio cache enabled?
		if ( ! $this->settings['eco_general_cache_portfolio'] )
		{
			$myCCs = $this->registry->mysql_ibEconomy->grabPortfolioItemsByType( 'cc', $this->memberData['member_id'], false );
		}
		else
		{
			$myCCs = $this->caches['ibEco_portfolios'][ $this->memberData['member_id'] ]['ccs_'];
		}

		if ( is_array ( $myCCs ) )
		{
			#init dropdown
			$cc_drop .= "<select id='cc_id' name='cc_id' class='input_text'>";
			$cc_drop .= "<optgroup label='{$this->lang->words['select_cc']}...'>";
	
			foreach ( $myCCs AS $cc )
			{
				if ( $cc['cc_id'] != $ccId && $cc['p_amount'] > 0 )
				{
					$ccid = ( $cc['cc_id'] ) ? $cc['cc_id'] : $cc['p_type_id'];
					
					$weHaveDrop = 1;
					$title		= $this->caches['ibEco_ccs'][ $ccid ]['cc_title'];
					$balance	= $this->registry->getClass('class_localization')->formatNumber( $cc['p_amount'], $this->decimal );
					$cc_drop   .= "<option value ='{$ccid}'>{$title} ( {$this->settings['eco_general_cursymb']}{$balance} )</option>";
				}
			}
			$cc_drop .= "</select>";	
		}
		
		#return what we made if we made anything
		if ( $weHaveDrop )
		{
			return $cc_drop;
		}
	}
	
	/**
	 * Sort and return an array
	 */	
	public function sortByKeyX($array, $key, $sort)
	{
		#init
		$sorted  = array();
		$counter = 0;
			
		#loop through to create array indexed by our $key
		foreach ($array AS $dataBit)
		{
			$counter++;
			$sorted[ $dataBit[ $key ] + .0000000.$counter ] = $dataBit;
		}

		#sort it!
		if ( $sort == 'ASC' )
		{
			ksort($sorted);			
		}
		else
		{
			krsort($sorted);
		}

		#return it!
		return $sorted;	
	}
	
	/**
	 * Randomize and return an array
	 */
	public function shizuffle($array) 
	{
		#init
		$shuffled = array();
		$keys = array_keys($array);

		#shuffle keys
		shuffle($keys);

		#loopy
		foreach($keys as $key) 
		{
			$shuffled[$key] = $array[$key];
			unset($array[$key]);
		}

		#return it!
		return $shuffled;
	}
	
	/**
	 * Map our Block Type Keys
	 */
	public function blockTypeKeyMap() 
	{	
		#init
		$ptsField 	= $this->settings['eco_general_pts_field'];
		$ptsName	= $this->settings['eco_general_currency'];
		
		#sort key map
		$keyMap = array('bank_savings' 	=> array('newest' => 'b_added_on',  'popular'	=> 's_total', 		'points' => 'b_s_acnt_cost', 'worth' => 's_funds' 			),
						'bank_checking' => array('newest' => 'b_added_on',  'popular'	=> 'c_total', 		'points' => 'b_c_acnt_cost', 'worth' => 'c_funds' 			),
						'stock' 		=> array('newest' => 's_added_on',  'popular' 	=> 'share_holders', 'points' => 's_value', 		 'worth' => 'total_share_value' ),
						'cc' 			=> array('newest' => 'cc_added_on', 'popular'	=> 'card_holders', 	'points' => 'cc_cost', 		 'worth' => 'funds' 			),
						'lt' 			=> array('newest' => 'lt_added_on', 'popular'	=> 'investors', 	'points' => 'lt_min', 		 'worth' => 'total_invested' 	),
						'loan' 			=> array('newest' => 'b_added_on',  'popular' 	=> 'loaners', 		'points' => 'b_loans_app_fee','worth' => 'outstanding_loan_amt' ),
						'shopitem' 		=> array('newest' => 'si_added_on', 'popular'	=> 'total_items', 	'points' => 'si_cost', 		 'worth' => 'si_sold' 			),
						'member' 		=> array('newest' => '',			'popular' 	=> '', 				'points' => $ptsField, 		 'worth' => 'eco_worth', 'welfare' => 'eco_welfare' ),
				  );
		#send it back		  
		return $keyMap;
	}
	
	/**
	 * Map our Block Type Text to Display
	 */
	public function blockTypeTextMap() 
	{	
		#init
		$ptsField 	= $this->settings['eco_general_pts_field'];
		$ptsName	= $this->settings['eco_general_currency'];
		
		#type text map
		$txtMap = array('bank_savings' 	=> array('newest' => 'new_acct_cost',  	'popular'	=> 'account_holders', 	'points' => 'new_acct_cost', 	'worth' => 'total_balance' 			),
						'bank_checking' => array('newest' => 'new_acct_cost',  	'popular'	=> 'account_holders', 	'points' => 'new_acct_cost', 	'worth' => 'total_balance' 			),
						'stock' 		=> array('newest' => 'share_value',  	'popular' 	=> 'share_holders',   	'points' => 'share_value', 		'worth' => 'total_share_value' ),
						'cc' 			=> array('newest' => 'card_cost', 		'popular'	=> 'card_holders', 		'points' => 'card_cost', 		'worth' => 'total_debt_s' 			),
						'lt' 			=> array('newest' => 'min_investment', 	'popular'	=> 'investors', 		'points' => 'min_investment', 	'worth' => 'total_invested' 	),
						'loan' 			=> array('newest' => 'loan_app_fee',  	'popular' 	=> 'total_loaners', 	'points' => 'loan_app_fee', 	'worth' => 'total_debt_s' ),
						'shopitem' 		=> array('newest' => 'price', 			'popular'	=> 'total_sold', 		'points' => 'price', 			'worth' => 'total_value' 			),
						'member' 		=> array('newest' => '',				'popular' 	=> '', 					'points' => $ptsName, 		 	'worth' => 'short_worth', 'welfare' => 'welfare' ),
				  );
		#send it back		  
		return $txtMap;
	}	
			
	/**
	 * Map our Block Type Sorters
	 */
	public function blockTypeSortMap() 
	{			
		#sorting direction map
		$sortDir = array( 'random' 		=> array('sort' => '', 		'key' => 'points'	),
						'newest' 		=> array('sort' => 'DESC',  'key' => 'newest'   ),
						'popular' 		=> array('sort' => 'DESC',  'key' => 'popular'  ),
						'points_desc' 	=> array('sort' => 'DESC',  'key' => 'points'   ),
						'points_asc' 	=> array('sort' => 'ASC', 	'key' => 'points'  	),
						'worth_desc' 	=> array('sort' => 'DESC',  'key' => 'worth'	),
						'worth_asc' 	=> array('sort' => 'ASC', 	'key' => 'worth'   	),
						'welfare_desc' 	=> array('sort' => 'DESC',	'key' => 'welfare' 	),
						'welfare_asc' 	=> array('sort' => 'ASC', 	'key' => 'welfare' 	),
					  );
		#send it back		  
		return $sortDir;
	}
	
	/**
	 * Our Master Cache Loader
	 */
	public function ecoCacheLoader( $cacheTypes ) 
	{
		# recaching all?
		if ( $cacheTypes == 'all' )
		{
			$cacheTypes = array('banks','stocks','ccs','lts','shopitems','shopcats','portfolios','stats');
		}
		
		#got cache?
		if ( is_array( $cacheTypes ) && count( $cacheTypes ) )
		{
			#loop through what we need to recache
			foreach ( $cacheTypes AS $type )
			{
				if( !$this->caches['ibEco_'.$type ] )
				{
					$this->caches['ibEco_'.$type ] = $this->cache->getCache('ibEco_'.$type );
				}
			}
		}
		else if ( $cacheTypes )
		{
			if( !$this->caches['ibEco_'.$cacheTypes ] )
			{		
				$this->caches['ibEco_'.$cacheTypes ] = $this->cache->getCache('ibEco_'.$cacheTypes );
			}
		}
	}	
	
	/**
	 * Our Master Automated Cache Machine
	 */
	public function acm( $cacheTypes ) 
	{
		# recaching all?
		if ( $cacheTypes == 'all' )
		{
			$cacheTypes = array('banks','stocks','ccs','lts','shopitems','shopcats','portfolios','stats','blocks','live_lotto');
		}
		
		#got cache?
		if ( is_array( $cacheTypes ) && count( $cacheTypes ) )
		{
			#loop through what we need to recache
			foreach ( $cacheTypes AS $type )
			{
				if ( $this->settings['eco_general_cache_portfolio'] || $type != 'portfolios' )
				{
					$this->cache->rebuildCache('ibEco_'.$type,'ibEconomy');
				}
			}
		}
		else if ( $cacheTypes )
		{
			$this->cache->rebuildCache('ibEco_'.$cacheTypes,'ibEconomy');
		}
		
		#good place to sync pfields_content I suppose...
		$this->registry->mysql_ibEconomy->resync_pfields_content();		
	}
	
	/**
	 * Get Groups Array
	 */	
	public function getGroups( $page, $selectFew='' )
	{	
		#init
		$groups 		= array();
		$selectFewExp	= explode(',',$selectFew);
		
		if ( $page == 'shop_item' )
		{
			$groups['none'] = array('none', $this->lang->words['no_protect'] );
		}
		else if ( $page == 'mass_donate' )
		{
			$groups['all']  = array('all', $this->lang->words['all'] );
		}
		
		#grab groups and arrayitize em
		$this->DB->build( array( 'select' => '*', 'from' => 'groups' ) );
		$this->DB->execute();

		while( $row = $this->DB->fetch() )
		{
			if ( !$selectFew || in_array( $row['g_id'], $selectFewExp ) )
			{
				if ( $row['g_access_cp'] )
				{
					$row['g_title'] .= "( STAFF )";
				}
				
				$groups[] = array( $row['g_id'], $row['g_title'] );
			}
		}
		
		#return
		return $groups;
	}

	/**
	 * Determine if user is using an RTL lang
	 */	
	public function isRtl()
	{	
		#init
		$isRtl = FALSE;

		foreach ( $this->cache->getCache('lang_data') as $data )
		{
			if ( intval($this->member->language_id) == intval($data['lang_id']) )
			{
				if ( $data['lang_isrtl'] )
				{
					$isRtl = TRUE;
				}
			}
		}
		
		#return
		return $isRtl;
	}

	/**
	 * Build custom image html (for all items)
	 */	
	public function customItemImageHTML($dbImageName, $altImage, $thumb, $url='', $popUpCard=false, $profileTab=false, $srcOnly=false)
	{	
		#init
		$thumbStyle = ($this->settings['eco_display_lrg_thmb']) ? "style='width:78px;height:78px;'" : "style='width:50px;height:50px;'";	
		$style 		= ( $thumb || !strpos($dbImageName, '-thumb' )) ? $thumbStyle : "style='width:129px;height:129px'";		
		$imageName 	= ( $thumb && !$thumbStyle) ? $dbImageName : str_replace('-thumb', '', $dbImageName);		
		$iconStyle  = ($this->settings['eco_display_lrg_thmb'])  ? "style='padding:31px'" : "style='padding:17px'";
		
		#doing an award or another image with a direct image url?
		if ($url != '')
		{
			$image_src 	= $url;
		}
		else
		{
			#no url provided, lets create one
			$image_src 	= ( $dbImageName ) ? "{$this->settings['upload_url']}/ibEconomy_images/{$imageName}" : "{$this->settings['img_url']}/eco_images/{$altImage}";
		}
		
		if (!$popUpCard && !$profileTab && !$srcOnly)
		{
			$imageHTML	= ( $dbImageName != '' || $url != '' ) ? "<img src='{$image_src}' class='photo' {$style} />" : "<img src='{$image_src}' class='photo' {$iconStyle} />"; 		
		}
		else if ($profileTab)
		{
			$imageHTML	= ( $dbImageName != '' || $url != '' ) ? 
				"<img src='{$image_src}' class='ipsUserPhoto ipsUserPhoto_medium'>"
					:"<img src='{$image_src}' class='ipsUserPhoto ipsUserPhoto_medium' style='height:16px;width:16px; padding:18px;'>";
		}
		else if ($srcOnly)
		{
			$imageHTML	= $image_src;
		}		
		else
		{
			$imageHTML	= ( $dbImageName != '' || $url != '' ) ? "<img src='{$image_src}'  class='ipsUserPhoto ipsUserPhoto_large' />" :
				"<img src='{$image_src}' class='ipsUserPhoto ipsUserPhoto_large' style='height:16px;width:16px; padding:38px;'/>";
		}
		
		#return
		return $imageHTML;
	}

	/**
	 * Figure out what tab and page to start on
	 */	
	public function findLandingPage()
	{
		$landingPageDetails = array();
		$tempArray			= array();
		
		$tempArray = explode('.', $this->settings['eco_display_landing_page']);
		
		$landingPageDetails['tab'] 	= $tempArray[0];
		$landingPageDetails['area'] = $tempArray[1];
		
		return $landingPageDetails;
	}

	/**
	 * Create and return map of actions and their appropriate icons
	 */	
	public function grabActionMap()
	{
		$actionMap  = array('bank_deposit' 		=> 'money_add.png',
							'bank_withdrawal'	=> 'money_delete.png',
							'sell_stock'		=> 'cancel.png',
							'cc_payment'		=> 'money_add.png',
							'balance_transfer'	=> 'bullet_go.png',
							'cash_advance'		=> 'money_add.png',
							'sell_lt'			=> 'cancel.png',
							'sell_shopitem'		=> 'tag_blue_delete.png',
							'trade_shopitem'	=> 'tag_blue_add.png',
							'use_shopitem'		=> 'tag_blue_edit.png',
							'loan_payment'		=> 'money_add.png',
							'donation'			=> 'group_go.png',
							'purchase'			=> 'cart_put.png',
							'edit'				=> 'wrench_orange.png',							
						  );	
		
		return $actionMap;
	}
	
	/**
	 * Format logs
	 */	
	public function formatLogRow($row, $numRow=0)
	{	
		#image action map
		$actionImg = $this->grabActionMap();
		
		#format stuff
		$row['l_date'] 			= $this->registry->getClass('class_localization')->getDate( $row['l_date'], 'SHORT' );
		$row['l_image']			= $actionImg[ $row['l_action'] ];
		$row['l_action'] 		= ( $row['l_subject_name'] != 'loan' ) ? $row['l_action'] : 'loan';
		$row['format_amt']  	= ( !in_array($row['l_subject_name'], array('stock','shopitem') ) ) ? $this->registry->ecoclass->decimal : 0;
		$row['currsymb']  		= ( !in_array($row['l_subject_name'], array('stock','shopitem') ) ) ? $this->settings['eco_general_cursymb'] : '';
		$row['class']			= ($numRow % 2 == 0) ? 'row1' : 'row2';
		
		#hovercard fix for 2.0.7
		$row['members_display_name'] 	= $row['donator_name'];
		$row['member_group_id'] 		= $row['donator_group'];
		$row['member_id'] 				= $row['donator_id'];
		$row['ibEco_plugin_ppns_prefix'] = $row['ibEco_plugin_ppns_prefix'];
		$row['ibEco_plugin_ppns_suffix'] = $row['ibEco_plugin_ppns_suffix'];

		#print_r($row);
		#format rest by action type
		if ( $row['l_action'] == 'donation' )
		{
			$row['l_action'] 	= sprintf( $this->lang->words['donated_to_log'], $this->registry->getClass('class_localization')->formatNumber( $row['l_amount'], $this->registry->ecoclass->decimal ), $this->settings['eco_general_currency'], $row['donatee_name'] );
		}
		else if ( $row['l_action'] == 'purchase' )
		{
			$row['l_action'] 	= sprintf( $this->lang->words['purchased_a_log'], $this->lang->words[ $row['l_subject_name'] ] );
			
			#lottery case
			if ( $row['l_subject_name'] == 'lottery_ticket' )
			{
				$ticketText 		= ( $row['l_amount'] > 1 ) ? $this->lang->words['tickets'] : $this->lang->words['ticket'];
				$row['l_log'] 		= $this->registry->getClass('class_localization')->formatNumber( $row['l_amount'] ).' '.$ticketText .' ('.$row['currsymb'].$this->registry->getClass('class_localization')->formatNumber( $row['l_amount'] * $this->settings['eco_lotto_ticket_price'], $row['format_amt'] ).')';
			}
			else
			{
				$row['l_log'] 		= $row['l_log'].' ('.$row['currsymb'].$this->registry->getClass('class_localization')->formatNumber( $row['l_amount'], $row['format_amt'] ).')';
			}
		}
		else if ( $row['l_action'] == 'edit' )
		{
			$row['l_action'] 	= sprintf( $this->lang->words['edited_members_points'], $row['l_subject_name'], $this->settings['eco_general_currency'] );
			$row['l_log'] 		= $row['l_log'].' ('.$row['currsymb'].$this->registry->getClass('class_localization')->formatNumber( $row['l_amount'], $row['format_amt'] ).')';
		}				
		else
		{
			if ( in_array($row['l_action'], array('sell_shopitem','trade_shopitem') ) )
			{
				$row['l_log'] 	= $row['l_log'].' ('.$this->registry->getClass('class_localization')->formatNumber( $row['l_amount'] ).')';
			}
			else if ( $row['l_action'] == 'use_shopitem' )
			{
				$row['l_log'] 	= ( $row['l_subject_name'] ) ? $row['l_log'].' ('.$row['l_subject_name'].')' : $row['l_log'];
			}
			else
			{
				$row['l_log'] 	= $row['l_log'].' ('.$row['currsymb'].$this->registry->getClass('class_localization')->formatNumber( $row['l_amount'], $row['format_amt'] ).')'; 
			}
			$row['l_action'] 	= $this->lang->words[ $row['l_action'] ];				
		}
					
		return $row;
	}
	
	/**
	 * Return image link for an award item
	 */	
	public function awardImageURL($item)
	{	
		$url = '';
		
		if ( !strstr($item['si_image'], "shop_item_img") && $item['si_file'] == 'award_item.php' )
		{
			$awardCatAndAward = explode('_', $item['si_extra_settings_1']);
			
			#using inv awards
			if ($this->settings['awds_system_status'] === '0' || $this->settings['awds_system_status'] === '1')
			{
				$url = $this->settings['upload_url']."/awards/".$item['si_image'];
			}				
			#using AMS still?
			else
			{
				$url = $this->caches['awards_cat_cache'][ $awardCatAndAward[0] ]['awards'][ $awardCatAndAward[1] ]['awards_img_url'];
			}
		}
		
		return $url;
	}
	
	/**
	* For testing, move along, nothing to see here
	*/
	public function showVars($stuff)
	{
		echo"<pre>";
		print_r($stuff);
		echo"</pre>";
		exit;
	}

	/**
	* Generate randomly ball numbers
	*/
	public function generateRandomBallNumbers($lotto) 
	{
		$winningBalls = array();
		
		for ($i = 1; $i <= $lotto['l_num_balls']; $i++)
		{
			$randNum			= rand(1, $lotto['l_top_num']);
			while (in_array($randNum, $winningBalls))
			{
				$randNum			= rand(1, $lotto['l_top_num']);
			}
			$winningBalls[$i] 	= $randNum;			
		}
		
		return $winningBalls;
	}

	/**
	* Truncate that string!
	*/	
	public function truncate($string, $max = 40, $replacement = '')
	{
		if (strlen($string) <= $max)
		{
			return $string;
		}
		
		$leave = $max - strlen ($replacement);
		return substr_replace($string, $replacement, $leave)."...";
	}

	/**
	* Generate a slick new box of mini shop item icons, per member
	*/	
	public function miniBoxedShopItems($mid, $maxToShow=100)
	{
		$miniShopItemBoxHTML = "";
		$shopItems = array();
		
		#portfolio cache enabled?
		if ( ! $this->settings['eco_general_cache_portfolio'] )
		{
		
			$shopItems = $this->registry->mysql_ibEconomy->grabPortfolioItemsByType('shopitem', $mid, false, true);
		}
		else
		{
			$this->ecoCacheLoader( 'portfolios' );
			$shopItems = $this->caches['ibEco_portfolios'][ $mid ][ 'shopitems_'];
		}
		
		$disallowedItems = explode(',', $this->settings['eco_disallowed_box_items']);
		
		//$this->showVars($disallowedItems );
		if (is_array($shopItems ) && count($shopItems ))
		{
			$count = 0;
			foreach ($shopItems AS $shopItem)
			{
				$info = pathinfo($shopItem['si_file']);
				$file_name =  basename($shopItem['si_file'],'.'.$info['extension']);			

				if (in_array(basename($file_name), $disallowedItems))
				{
					continue;
				}
				
				$shopItem['si_url_image']	= $this->registry->ecoclass->awardImageURL($shopItem);
				$imageSrc 					= $this->customItemImageHTML($shopItem['si_image'], 'tag_blue.png', false, $shopItem['si_url_image'], false, false, true);
				$imageStyle 				= ($shopItem['si_image']) ? "" : "style='height:16px;width:16px; padding:8px;'";
				$title						= (!$this->request['f']) ? "title='{$this->lang->words['view_further_details']}'" : "";

				$miniShopItemBoxHTML .= 
				"<a href='{$this->settings['base_url']}app=ibEconomy&amp;tab=shop&amp;area=single&amp;type=shopitem&amp;id={$shopItem['p_type_id']}&amp;bank_type={$shopItem['p_type_class']}' class='__item __id{$shopItem['p_type_id']}__type_shopitem_x' {$title}>
					<img src='{$imageSrc}' {$imageStyle} class='ipsUserPhoto ipsUserPhoto_mini' data-tooltip='{$shopItem['si_title']}' />
				</a>";
				
				$count++;
				
				if ($count == $maxToShow)
				{
					break;
				}	
			}
		}
		
		return $miniShopItemBoxHTML;
	}
}

/**
* Our master cart type interface
* All cart types need to have their own file in sources/cart_types and each
* must either implement these methods or fall back on the default methods in cart_item.php
*/
interface ibEconomy_cart_type
{
	public function on();
	public function itemOn($item);
	public function name();
	public function permissionGroupField();
	public function numberLang();
	public function buyDecimalAmount();
	public function countNumForTotalPurchased();
	public function onOffSetting();
	public function abbreviation();
	public function grabItemByID($itemID);
	public function canAccess($returnNeeded=false);
	public function gotItemCheck($id, $item);
	public function finalAdd2CartChecks($item, $number, $folioItem, $cartItem, $currentCartQuant, $typeType=false);
	public function add2CartRedirectMessage($checks, $item);
	public function tallyCost($row);
	public function tallyFee($row);
	public function postPurchaseProcessing($cartItem);
	public function redirectToPage($cartItem);
	public function fieldsForAddingToPortfolio($cartItem, $item);
	public function rowHtmlTitle($row);
	public function rowHtmlDescription($row);
	public function htmlTemplate();
	public function format($item, $type);	
}

/**
* Our master plugin interface
* All main plugin files (in their own same-named directories in sources/plugins/)
* must implement these methods
* ???????which need contents and which can just return false if needed??????
*/
interface ibEconomy_plugin
{
	public function on();
	public function tab();
	public function name();
	public function areaTitle();
	public function hasBlock();
	public function grabSettingsForDisplay();
	public function grabGroupSettings();
	public function permissionGroupField();
	public function onOffSetting();
}

/**
* Our master shop_item interface
* All shop_item files in sources/shop_items/
* must implement these methods
*/
interface ibEconomy_shop_item
{
	public function title();
	public function description();
	public function otherOrSelf();
	public function extra_settings();
	public function usingItem($theItem);
	public function useItem($theItem,$myPortItem);
}