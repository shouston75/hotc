<?php

/**
 * (e32) ibEconomy
 * Public Module: ibEconomy (THE Module!)
 * Version 1.6.0
 */

if ( ! defined( 'IN_IPB' ) )
{
	print "<h1>Incorrect access</h1>You cannot access this file directly. If you have recently upgraded, make sure you upgraded all the relevant files.";
	exit();
}

class public_ibEconomy_ibEconomy_ibEconomy extends ipsCommand
{
	#init global
	private $output			= "";
	private $page_title		= "";
	private $showPage		= "";	

	/**
	 * Class entry point
	 */
	final public function doExecute( ipsRegistry $registry )
	{
		#license check
		//$this->registry->ecoclass->licenseCheck();
		
		#load Language
		$this->registry->class_localization->loadLanguageFile( array( 'public_ibEconomy' ) );
		
		#right to left lang?
		$this->lang->words['_isRtl'] = $this->registry->ecoclass->isRtl();
		
		#we have a pfields_content row for this member viewing currently?
		if ( $this->memberData['member_id'] && !isset($this->memberData['eco_points']) )
		{
			#no? then lets resync everyone
			$this->registry->mysql_ibEconomy->resync_pfields_content();	
		}
		
		#setup js url... (bug in IPB that this isn't done automatically for 3rd party apps???)
		// changed for 1.0
		//$this->settings['js_app_url'] = $this->settings['board_url']."/". CP_DIRECTORY . "/applications_addon/other/ibEconomy/js/";
		$this->settings['js_app_url'] = $this->settings['public_dir'] . "js/3rd_party/ibEconomy/";
		
		#make sure cache is loaded... not much would work without it
		$this->registry->ecoclass->ecoCacheLoader( 'all' );			

		#permission and enabled?
		$this->lang->words['ibeco_is_off'] = str_replace( "<%ibeco_name%>"  , $this->settings['eco_general_name'], $this->lang->words['ibeco_is_off'] );
		$this->registry->ecoclass->canAccess('general', false);		
		
		#init page title
		$this->page_title = $this->settings['board_name'] . " " . $this->settings['eco_general_name'];
		
		#init navigation 
		$this->registry->output->addNavigation( $this->settings['eco_general_name'], 'app=ibEconomy' );		
	
		#announcement
		$announcement = ( $this->settings['eco_general_ance'] && $this->settings['eco_general_ance_on'] ) ? IPSText::getTextClass('bbcode')->preDisplayParse( IPSText::getTextClass('bbcode')->preDbParse( $this->settings['eco_general_ance'] ) ) : '';
		
		#fill my button, baby
		$my_points = ( $this->memberData[ $this->settings['eco_general_pts_field'] ] ) ? $this->registry->getClass('class_localization')->formatNumber( $this->memberData[ $this->settings['eco_general_pts_field'] ], $this->registry->ecoclass->decimal ) : 0;
		$this->lang->words['my_points'] = str_replace( "<%POINT_NAME%>"  , $this->settings['eco_general_currency'], $this->lang->words['my_points'] );
		
		#leftside buttons
		$leftButtons = $this->registry->class_global->getLeftButtons();
		
		#do stats box near footer
		$stats = $this->registry->class_global->doStats();

		#are we in a plugin? (new to 1.6), if so, we can skip the big tab switcharoo
		if ($this->registry->ecoclass->active['inPlugin'])
		{
			$plugin = $this->registry->ecoclass->grabPluginClass($this->registry->ecoclass->active['area']);
			
			#grab html page
			$this->showPage = $plugin->showPage();
			
			#navigation
			$this->registry->output->addNavigation( $plugin->navigationText() , $plugin->navigationLink() );
			
			#page title
			$this->page_title .= ' ' .$plugin->pageTitle();
		}
		else
		{
			#What tab are we on?
			switch( $this->registry->ecoclass->active['tab'] )
			{		
				case 'cash':

					#navigation
					$this->registry->output->addNavigation( $this->lang->words['quick_cash'], 'app=ibEconomy&amp;tab=cash' );
			
					#is this quickcash page currently enabled?
					#need to add an "s" if we're in lottery stuff, everything else can remain the request[area]
					$onOffPermChecker = ( $this->request['area'] == 'single' && $this->request['type'] == 'lottery' || $this->request['area'] == 'lottery' ) ? 'lotterys' : $this->request['area']; 
					$this->registry->ecoclass->canAccess($onOffPermChecker, false);			

					#which area within the me tab are we on?
					switch( $this->registry->ecoclass->active['area'] )
					{
						case 'single':
							if ( $this->request['type'] == 'lottery' )
							{
								$this->quickCash('singleLottery');
							}
						break;
						
						case 'quickCash':
						default:
							$this->quickCash($this->request['area']);
						break;
					}
					
				break;
				
				case 'shop':
					
					#permission and enabled?
					$this->registry->ecoclass->canAccess('shop', false);	
					
					#navigation
					$this->registry->output->addNavigation( $this->lang->words['shop'], 'app=ibEconomy&amp;tab=shop' );				
					
					#which area within the me tab are we on?
					switch( $this->registry->ecoclass->active['area'] )
					{

						case 'items':							
							$this->showShopItems();
						break;
						case 'single':
							$this->shopItem();
						break;
						case 'all':
						default:
							$this->showShopCats();
						break;
					}				
				break;
				
				case 'me':

					#navigation
					$this->registry->output->addNavigation( $this->lang->words['my_portfolio'], 'app=ibEconomy&amp;tab=me' );				
					
					#which area within the me tab are we on?
					switch( $this->registry->ecoclass->active['area'] )
					{
						case 'using_shopitem':							
							$this->useShopItem();
						break;					
						case 'my_shopitems':							
							$this->myInvestments('shopitem');
						break;				
						case 'my_banks':
							$this->myInvestments('bank');
						break;
						case 'my_stocks':							
							$this->myInvestments('stock');
						break;
						case 'my_ccs':							
							$this->myInvestments('cc');
						break;
						case 'my_lts':
							$this->myInvestments('lt');
						break;
						case 'my_loans':
							$this->myInvestments('loan');
						break;					
						case 'my_welfare':
							$this->myWelfare();
						break;				
						case 'single':
							$this->mySingleItem();
						break;
						case 'my_overview':
						default:
							$this->myOverview();
						break;
					}				
				break;
				
				case 'buy':

					#navigation
					$this->registry->output->addNavigation( $this->lang->words['shopping_cart'], 'app=ibEconomy&amp;tab=buy&amp;area=cart' );
			
					#which area within the me tab are we on?
					switch( $this->registry->ecoclass->active['area'] )
					{	
						case 'checkout':							
							$this->showCart('checkout');
						break;
						case 'cart':
						default:
							$this->showCart();
						break;
					}
				break;

				case 'invest':

					#navigation
					$this->registry->output->addNavigation( $this->lang->words['invest_tab'], 'app=ibEconomy&amp;tab=invest' );
					
					#which area within the investments tab are we on?
					switch( $this->registry->ecoclass->active['area'] )
					{
						case 'single':
							$this->singleItem();
						break;
						case 'stocks':
						case 'ccs':
						case 'lts':
						case 'banks':	
						default:					
							$this->showItems();
						break;
					}
				break;

				default:
				case 'global':

					#navigation
					$this->registry->output->addNavigation( $this->lang->words['global_eco'], 'app=ibEconomy&amp;tab=global' );				
				
					#which area within the global/default tab are we on?
					switch( $this->registry->ecoclass->active['area'] )
					{	
						case 'frontpage':
							$this->frontpage();
						break;
						case 'overview':
							$this->overview();
						break;
						case 'rankings':
							$this->ranks();
						break;
						case 'find_member':
							$this->findMember();
						break;
						case 'portfolio':
							$this->portfolio();
						break;						
						case 'transactions':
							$this->transactions();
						break;
						case 'settings':
							$this->settings();
						break;						
						case 'member':
							$this->member();
						break;				

						default:
						
							switch( $this->request['do'] )
							{
								case 'welfare_payback':
									$this->registry->class_cash->welfarePayment();
								break;							
								case 'welfare_signup':
									$this->registry->class_cash->welfareApply();
								break;							
								case 'request_loan':
									$this->registry->class_cash->requestLoan();
								break;						
								case 'do_find_member':
									$this->registry->class_global->doFindMember();
								break;	
								case 'donate':
									$this->registry->class_global->donate();
								break;	
								case 'edit_member_pts':
									$this->registry->class_global->editMemberPoints();
								break;
								case 'portfolio_update':
									$this->registry->class_me->updatePortfolio();
								break;				
								case 'add_to_cart':
									$this->registry->class_cart->addToCart();
								break;
								case 'update_cart':
									$this->registry->class_cart->updateCart();
								break;					
								case 'toggle':
									$this->registry->ecoclass->_toggleSidePanel();
								break;
								case 'purchase':
									$this->showPage = $this->registry->class_cart->cart('purchase');
								break;
								case 'submit_lotto_picks':
									$this->showPage = $this->registry->class_cash->submitLottoPicks();
								break;							
								default:
									( $this->settings['eco_general_welcome_on'] ) ? $this->frontpage() : $this->overview();								
								break;
							}
						break;
					}
					
				break;
			}
		}
		
		#Setup the blocks to fill the side-panel if it's enabled
		$blocks 		= ( $this->settings['eco_display_sidebar_on'] ) ? $this->registry->class_global->blocks() : '';
		
		#throw it all to the wrapper
		$this->output .= $this->registry->output->getTemplate('ibEconomy')->wrapper($this->registry->ecoclass->active,$this->showPage);
	
		#create tabs and area links and such
		$tabs = $this->registry->class_global->buildTabHTML();
		
		#create area siderow links for the current active tab
		$areas = $this->registry->class_global->buildAreaHTML();
		
		#Switch the HTML tags
		$this->output = str_replace( "<!--JS+CSS-->"  , 	$this->registry->output->getTemplate('ibEconomy')->init_js_css(), 									$this->output );
		$this->output = str_replace( "<!--ANNOUNCE-->"  , 	$this->registry->output->getTemplate('ibEconomy')->announce($announcement), 						$this->output );
		$this->output = str_replace( "<!--BUTTONS-->"  , 	$this->registry->output->getTemplate('ibEconomy')->button_row($my_points,$leftButtons), 			$this->output );
		$this->output = str_replace( "<!--SIDE_BAR-->" ,	$this->registry->output->getTemplate('ibEconomy')->blocks($blocks), 								$this->output );	
		$this->output = str_replace( "<!--TABS-->"  , 		$this->registry->output->getTemplate('ibEconomy')->tabs($this->registry->ecoclass->active, $tabs), 	$this->output );
		$this->output = str_replace( "<!--AREAS-->"  , 		$this->registry->output->getTemplate('ibEconomy')->area_side_bars($areas), 							$this->output );
		$this->output = str_replace( "<!--STATS-->"  , 		$this->registry->output->getTemplate('ibEconomy')->stats($stats['active'], $stats['totals']), 		$this->output );
		if (true)
		{
			$this->output = str_replace( "<!--FOOTER-->"  , 	$this->registry->output->getTemplate('ibEconomy')->footer(), 									$this->output );	
		}
		else
		{
			$this->output = str_replace( "<!--FOOTER-->"  , 	"", 																							$this->output );
		}

		#Finalize page title
		if( $this->page_title == "" )
		{
			$this->page_title = $this->settings['board_name'] . " " . $this->lang->words['ibEconomy_title'];
		}
		$this->registry->output->setTitle( $this->page_title );

		#Got Navigation
		if( ! is_array( $this->registry->output->_navigation ) )
		{
			$this->registry->output->addNavigation( $this->lang->words['ibEconomy_title'], 'app=ibEconomy' );
		}

		#Output (finally), you deserve it!
		$this->registry->output->addContent( $this->output );
		$this->registry->output->sendOutput();
	}
	
	/**
	 * Show quick cash pages 
	 */
	public function quickCash($page)
	{
		$this->showPage = $this->registry->class_cash->$page();

		#lotto fix for 1.5
		$pageTitle = ( $this->request['type'] == 'lottery' ) ? 'lottery' : $this->request['area'];
		$urlFinish = ( $this->request['type'] == 'lottery' ) ? '&amp;type=lottery&amp;id='.$this->request['id'] : '';
		
		#navigation
		if ( $this->request['type'] == 'lottery' )
		{
			$this->registry->output->addNavigation( $this->lang->words[ $pageTitle.'s' ], 'app=ibEconomy&amp;tab=cash&amp;area=lottery' );
		}
		$this->registry->output->addNavigation( $this->lang->words[ $pageTitle ], 'app=ibEconomy&amp;tab=cash&amp;area='.$this->request['area'].$urlFinish );
		
		#page title
		$this->page_title .= ' ' .$this->lang->words[ $pageTitle ];
	}
	
	/**
	 * Show shop categories
	 */
	public function showShopCats()
	{
		#grab cats page
		$this->showPage = $this->registry->class_shop->showCats();
		
		#navigation
		$this->registry->output->addNavigation( $this->lang->words['all_categories'], 'app=ibEconomy&amp;tab=shop&amp;area=categories' );
		
		#page title
		$this->page_title .= ' ' .$this->lang->words['all_categories'];
	}
	
	/**
	 * Show shop items
	 */
	public function showShopItems()
	{
		#grab items page
		$this->showPage = $this->registry->class_shop->showItems();
		
		#init navi stuff
		$navText 	= ( $this->registry->ecoclass->active['cat_name'] ) ? $this->registry->ecoclass->active['cat_name'].' '.$this->lang->words['items']  : $this->lang->words['all_shop_items'];
		$cat		= ( $this->registry->ecoclass->active['cat_name'] ) ? '&amp;cat='.$this->request['cat']  : '';
		
		#navigation
		$this->registry->output->addNavigation( $navText , 'app=ibEconomy&amp;tab=shop&amp;area=items'.$cat );
		
		#page title
		$this->page_title .= ' ' .$navText;
	}	
	
	/**
	 * Show 1 shop item
	 */
	public function shopItem()
	{
		#grab members page
		$this->showPage = $this->registry->class_shop->item();
		
		#navigation
		$this->registry->output->addNavigation( $this->registry->ecoclass->active['item_name'] , 'app=ibEconomy&amp;tab=shop&amp;area=single&amp;type=shopitem&amp;id='.$this->request['id'] );
		
		#page title
		$this->page_title .= ' ' .$this->registry->ecoclass->active['item_name'];
	}

	/**
	 *  Using shop item...
	 */
	public function useShopItem()
	{
		#grab members page
		$this->showPage = $this->registry->class_me->usingShopItem($this->request['when']);
	
		#navigation
		$this->registry->output->addNavigation( $this->lang->words['using_item'] , 'app=ibEconomy&amp;tab=me&amp;area=using_shopitem&amp;id='.$this->request['id'] );
		
		#page title
		$this->page_title .= ' ' .$this->lang->words['using_item'];
	}	
	
	/**
	 * Show member points heirarchy
	 */
	public function ranks()
	{
		#grab members page
		$this->showPage = $this->registry->class_global->memberStandings();
		
		#navigation
		$this->registry->output->addNavigation( $this->lang->words['rankings'], 'app=ibEconomy&amp;tab=global&amp;area=rankings' );
		
		#page title
		$this->page_title .= ' ' .$this->lang->words['ranks'];
	}
	
	/**
	 * Show member points heirarchy
	 */
	public function overview()
	{
		#grab members page
		$this->showPage = $this->registry->class_global->overview();
		
		#navigation
		$this->registry->output->addNavigation( $this->lang->words['overview'], 'app=ibEconomy&amp;tab=global&amp;area=overview' );
		
		#page title
		$this->page_title .= ' ' .$this->lang->words['overview'];
	}
	
	/**
	 * Find a Member, don't worry, nothing bad will happen to you, we just need to talk
	 */
	public function findMember()
	{
		#grab members page
		$this->showPage = $this->registry->class_global->findMember();
		
		#navigation
		$this->registry->output->addNavigation( $this->lang->words['find_member'], 'app=ibEconomy&amp;tab=global&amp;area=find_member' );
		
		#page title
		$this->page_title .= ' ' .$this->lang->words['find_member'];
	}

	/**
	 * Display Member
	 */
	public function member()
	{
		#grab members page details
		$memberDeetz = $this->registry->class_global->member();
		
		#show page
		$this->showPage = $memberDeetz['showPage'];
		
		#side area title
		$this->registry->ecoclass->active['member_prof'] = $memberDeetz['memName'];
		
		#navigation
		$this->registry->output->addNavigation( $this->lang->words['viewing'].' '.$memberDeetz['memName'].$this->lang->words['s_profile'], 'app=ibEconomy&amp;tab=global&amp;area=member&amp;id='.$memberDeetz['memID'] );
		
		#page title
		$this->page_title .= ' ' .$this->lang->words['viewing'].' '.$memberDeetz['memName'].$this->lang->words['s_profile'];
	}
	
	/**
	 * All Forum IBECO Assets
	 */
	public function portfolio()
	{
		#permission and enabled?
		$this->registry->ecoclass->canAccess('assets', false);	

		#grab portfolio page
		$this->showPage = $this->registry->class_global->portfolio();
		
		#navigation
		$this->registry->output->addNavigation( $this->lang->words['global_assets'], 'app=ibEconomy&amp;tab=global&amp;area=portfolio' );
		
		#page title
		$this->page_title .= ' ' .$this->lang->words['global_assets'];
	}	

	/**
	 * Transactions
	 */
	public function transactions()
	{
		#permission and enabled?
		$this->registry->ecoclass->canAccess('transactions', false);				

		#grab transactions page
		$this->showPage = $this->registry->class_global->transactions();
		
		#navigation
		$this->registry->output->addNavigation( $this->lang->words['transactions'], 'app=ibEconomy&amp;tab=global&amp;area=transactions' );
		
		#page title
		$this->page_title .= ' ' .$this->lang->words['transactions'];
	}

	/**
	 * Settings
	 */
	public function settings()
	{
		#permission and enabled?
		$this->registry->ecoclass->canAccess('settings', false);

		#grab settings page
		$this->showPage = $this->registry->class_global->settings();
		
		#navigation
		$this->registry->output->addNavigation( $this->lang->words['settings'], 'app=ibEconomy&amp;tab=global&amp;area=settings' );
		
		#page title
		$this->page_title .= ' ' .$this->lang->words['settings'];
	}	

	/**
	 * Show my items, by type
	 */
	public function myInvestments($type='all')
	{
		if ( !in_array( $type, array('bank','stock','lt','cc','shopitem','loan') ) )
		{
			$this->registry->output->showError( $this->lang->words['no_type'] );
		}

		#permission and enabled?
		$this->registry->ecoclass->canAccess($type.'s', false);				
		
		#nav/title text
		$text = $this->lang->words[ 'my_'.$type.'s' ];
		
		#grab it via our me class
		$this->showPage = $this->registry->class_me->myItems($type.'s');
		
		#navigation
		$this->registry->output->addNavigation( $text, 'app=ibEconomy&amp;tab=me&amp;area=my_'.$type.'s' );
		
		#page title
		$this->page_title .= ' ' .$text;
	}
	
	/**
	 * Show my items, by type
	 */
	public function myWelfare()
	{
		#permission and enabled?
		$this->registry->ecoclass->canAccess('welfare', false);				

		#grab it via our me class
		$this->showPage = $this->registry->class_me->myWelfare();
		
		#navigation
		$this->registry->output->addNavigation( $this->lang->words['my_welfare'], 'app=ibEconomy&amp;tab=me&amp;area=my_welfare' );
		
		#page title
		$this->page_title .= ' ' .$this->lang->words['my_welfare'];
	}
	
	/**
	 * Show my items, 1 by 1
	 */
	public function mySingleItem()
	{
		$type = $this->request['type'];
		
		if ( !in_array( $type, array('bank','stock','lt','cc','shopitem','loan') ) )
		{
			$this->registry->output->showError( $this->lang->words['no_type'] );
		}
		
		if ( ! intval($this->request['id']) )
		{
			$error = str_replace( "<%TYPE%>" , $this->lang->words[ $type ], $this->lang->words['no_id'] );
			$this->registry->output->showError( $error );
		}		
		
		#permission and enabled?
		$this->registry->ecoclass->canAccess($type.'s', false);					
		
		#grab it via our me class
		$this->showPage = $this->registry->class_me->myItem($type, $this->request['id'], strtolower($this->request['type_type']));
		
		#navigation
		$this->registry->output->addNavigation( $this->lang->words[ $type ], 'app=ibEconomy&amp;tab=me&amp;area=single&amp;type='.$type.'&amp;id='.$this->request['id'].'&amp;type_type='.$this->request['type_type'] );
		
		#page title
		$this->page_title .= ' ' .$this->lang->words[ $type ];
	}
	
	/**
	 * Show my overview
	 */
	public function myOverview()
	{
		#grab it via our me class
		$this->showPage = $this->registry->class_me->myOverview($noid);
		
		#navigation
		$this->registry->output->addNavigation( $this->lang->words['glance'], 'app=ibEconomy&amp;tab=me' );
		
		#page title
		$this->page_title .= ' ' .$this->lang->words['glance'];
	}	
	
	/**
	 * Show a single item
	 */
	public function singleItem()
	{
		$type		= $this->request['type']; 
		$bank_type	= $this->request['bank_type'];
		
		if ( !in_array( $type, array('bank','stock','lt','cc') ) )
		{
			$this->registry->output->showError( $this->lang->words['no_type'] );
		}
		
		if ( ! intval($this->request['id']) )
		{
			$error = str_replace( "<%TYPE%>" , $this->lang->words[ $type ], $this->lang->words['no_id'] );
			$this->registry->output->showError( $error );
		}
		
		#permission and enabled?
		$this->registry->ecoclass->canAccess($type.'s', false);		
		
		#grab it via our investing class
		$this->showPage = $this->registry->class_invest->$type($this->request['id'], $bank_type);
		
		#navigation
		$this->registry->output->addNavigation( $this->lang->words[ $type ], 'app=ibEconomy&amp;tab=invest&amp;area=single&amp;type='.$type.'&amp;id='.$this->request['id'] );
		
		#page title
		$this->page_title .= ' ' .$this->lang->words[ $type ];
	}	
	
	/**
	 * Shopping Cart!
	 */
	public function showCart($show='cart')
	{
		#permission and enabled?
		$this->registry->ecoclass->canAccess('cart', false);
		
		#checkout page
		if ( $show == 'checkout' )
		{
			#Show checkout page!
			$this->showPage = $this->registry->class_cart->cart('checkOut');
			
			#navigation
			$this->registry->output->addNavigation( $this->lang->words['checkout'], 'app=ibEconomy&amp;tab=buy&amp;area=checkout' );

			#page title
			$this->page_title .= ' ' .$this->lang->words['checkout'];				
		}
		#shopping cart page
		else
		{
			#show shopping cart!
			$this->showPage = $this->registry->class_cart->cart('showCart');
			
			#page title
			$this->page_title .= ' ' .$this->lang->words['shopping_cart'];
		}
	}

	/**
	 * ibEco Frontpage!
	 */
	public function frontpage()
	{
		#show page
		$this->showPage = $this->registry->output->getTemplate('ibEconomy')->welcome(IPSText::getTextClass('bbcode')->preDisplayParse( IPSText::getTextClass('bbcode')->preDbParse( $this->settings['eco_general_welcome'] ) ) );

		#navigation
		$this->registry->output->addNavigation( $this->lang->words['welcome'], 'app=ibEconomy&amp;tab=global' );
		
		#page title
		$this->page_title .= ' ' .$this->lang->words['welcome'];
	}

	/**
	 * Show those items
	 */
	public function showItems()
	{		
		#permission and enabled?
		$this->registry->ecoclass->canAccess($this->request['area'], false);

		#grab it via our investing class
		$investFunction = 'show'.$this->request['area'];
		$this->showPage = $this->registry->class_invest->$investFunction();
		
		#navigation
		$this->registry->output->addNavigation( $this->lang->words['browse'].' '.$this->lang->words[ $this->request['area'] ], 'app=ibEconomy&amp;tab=invest&amp;area='.$this->request['area'] );
		
		#page title
		$this->page_title .= ' ' .$this->lang->words['browse'].' '.$this->lang->words[ $this->request['area'] ];
	}
		
}