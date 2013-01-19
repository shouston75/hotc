<?php

/**
 * (e32) ibEconomy
 * Cart Type : Stock
 */

class ibEconomy_cart_type_stock extends ibEconomy_cart_type_cart_item implements ibEconomy_cart_type
{
	protected 	$registry;
	protected 	$DB;
	protected 	$settings;
	protected 	$request;
	protected 	$lang;
	protected 	$member;
	protected 	$cache;
	protected 	$caches;
	protected	$cartItem;
	
	/**
	 * Class entry point
	 */
	public function __construct( ipsRegistry $registry, $cartItemIn )
	{
        $this->registry     	=  ipsRegistry::instance();
        $this->DB           	=  $this->registry->DB();
        $this->settings     	=& $this->registry->fetchSettings();
        $this->request      	=& $this->registry->fetchRequest();
        $this->lang         	=  $this->registry->getClass('class_localization');
        $this->member       	=  $this->registry->member();
        $this->memberData  		=& $this->registry->member()->fetchMemberData();
        $this->cache        	=  $this->registry->cache();
        $this->caches       	=& $this->registry->cache()->fetchCaches();
		
		$this->cartItem			= $cartItemIn;
	}
	
	/*
	* Return the value of this cart type's current on/off configuration
	* This method is required!  You better return true/false!
	*/
	public function on()
	{
		return $this->settings[ $this->onOffSetting() ];
	}
	
	/*
	* Return the value of a specific item's current on/off configuration
	*/
	public function itemOn($item)
	{
		return $item['s_on'];
	}	
	
	/*
	* Return the name that items of this type are called
	*/
	public function name()
	{
		return $this->lang->words['stock'];
	}

	/*
	* Return the group field required to purchase this cart type
	*/
	public function permissionGroupField()
	{
		return "g_eco_stock";
	}
	
	/*
	* Default icon?
	*/
	public function icon()
	{
		return 'chart_curve.png';
	}
	
	/*
	* Number of decimal places to use for sums of these item types?
	*/
	public function decimalPlacesForSums()
	{
		return 0;
	}

	/*
	* Extra info needed for displaying purchased portfolio items
	* Not static!
	*/
	public function extraPortfolioItemInfo()
	{
		$extraInfo = array();
		
		$extraInfo['total_text'] 	= $this->lang->words['global_shares_owned'];
		$extraInfo['cost'] 		 	= $this->caches['ibEco_stocks'][ $this->cartItem['p_type_id'] ]['s_value'];
		$extraInfo['total']	 	 	= $this->caches['ibEco_stocks'][ $this->cartItem['p_type_id'] ]['total_share_value'];
		$extraInfo['total_bought']	= $this->currencySymbolForSums().$this->registry->getClass('class_localization')->formatNumber( $extraInfo['total'], $this->decimalPlacesForSums());
		
		return $extraInfo;
	}

	/*
	* What do we call this item anyway?
	*/
	public function title($item)
	{
		return $item[ $this->abbreviation().'_title' ].' '.$item[ $this->abbreviation().'_title_long' ];
	}	
	
	/*
	* Return the text displayed after the amount is purchased, stocks is shares, everything else is points...
	*/
	public function numberLang()
	{
		return $this->lang->words['shares'];
	}

	/*
	* Return true if amounts of this type can be purchased in fractions/decimals
	*/
	public function buyDecimalAmount()
	{
		return false;
	}	
	
	/*
	* Return the group field required to access this plugin (false if none required)
	*/
	public function onOffSetting()
	{
		return "eco_stocks_on";
	}
	
	/*
	* Return the abbreviation used for this type, for example, the setting names use them
	*/
	public function abbreviation()
	{
		return "s";
	}

	/*
	* Return an item of this type from our cache
	*/
	public function grabItemByID($itemID)
	{
		return $this->caches['ibEco_stocks'][ $itemID ];
	}
	
	/*
	* Runs checks to make sure this item is allowed to be accessed currently
	*/
	public function canAccess($returnNeeded=false)
	{
		return $this->registry->ecoclass->canAccess('stocks', $returnNeeded);
	}

	/*
	* Make sure we have an ID and/or an actual item, if not, return error message 
	*/
	public function gotItemCheck($id, $item)
	{
		$error = "";
		
		if ( !$id )
		{
			$error = str_replace( "<%TYPE%>", $this->lang->words['stock'], $this->lang->words['no_id'] );
		}	

		#no item found by that ID? error!
		if ( !$error && !$item )
		{
			$error = str_replace( "<%TYPE%>", $this->lang->words['stock'], $this->lang->words['none_found_show'] );		
		}
		
		#do I have permission to purchase this item? if not, error!
		if ( !$error && $item[ $this->abbreviation().'_use_perms'] && !$this->registry->permissions->check( 'open', $item ) )
		{
			$error = str_replace( "<%TYPE%>", $this->lang->words['stock'], $this->lang->words['no_perm_to_buy_it'] );		
		}

		#item isn't enabled currently? error!
		if ( !$error && !$item[ $this->abbreviation().'_on'] )
		{
			$error = str_replace( "<%ITEM%>", $this->lang->words['stock'], $this->lang->words['item_disabled'] );
		}		
		
		return $error;
	}

	/*
	* Final checks before adding item to cart (like checking quantity, type, etc)
	*/
	public function finalAdd2CartChecks($item, $number, $folioItem, $cartItem, $currentCartQuant, $typeType=false)
	{
		$error = "";
		
		#can't purchase a fraction of a share
		$number = intval($number);
		
		#no number of shares? error!
		if ( $number < 1 )
		{
			$error = $this->lang->words['no_shares_to_add'];
		}
		
		#number of shares over group max?  error!
		if ( !$error && $this->memberData['g_eco_stock_max'] && $number + $folioItem['p_amount'] + $currentCartQuant > $this->memberData['g_eco_stock_max'] )
		{
			$error = str_replace( "<%YOUR_MAX%>", $this->registry->getClass('class_localization')->formatNumber( $this->memberData['g_eco_stock_max'] ) .' '. $this->numberLang(), $this->lang->words['cart_item_quantity_over_group_max'] );		
			$error = str_replace( "<%TYPE%>", $this->lang->words['stock'], $error );
			$error = str_replace( "<%NUMBER_WORD%>", $this->lang->words['quantity'], $error );						
		}
		
		#number of shares over stock max?  error!
		if ( !$error && $item['s_limit'] && $number + $folioItem['p_amount'] + $currentCartQuant > $item['s_limit'] )
		{
			$error = str_replace( "<%MAX%>", $this->registry->getClass('class_localization')->formatNumber( $item['s_limit'] ) .' '. $this->numberLang(), $this->lang->words['cart_item_quantity_over_item_max_with_vars'] );		
			$error = str_replace( "<%TYPE%>", $this->lang->words['stock'], $error );
			$error = str_replace( "<%NUMBER_WORD%>", $this->lang->words['quantity'], $error );						
		}		

		return $error;
	}	
	
	/*
	* Return the message to be displayed upon successfully adding an item to cart
	*/
	public function add2CartRedirectMessage($checks, $item)
	{
		$redirectMessage = "";	
		
		$messageText 	 = ( $checks['number'] > 0 ) ?  $this->registry->getClass('class_localization')->formatNumber( $checks['number'], 0 ) : 0;
		$redirectMessage = str_replace( "<%TYPE_NAME%>", $this->lang->words['stock'].' <i>'.$item[ $this->abbreviation().'_title'].'</i>', $this->lang->words['stock_added_to_cart'] );
		$redirectMessage = str_replace( "<%NUMBER_TEXT%>", '<i>'.$messageText.'<i> '.$numberName, $redirectMessage );

		return $redirectMessage;
	}
	
	/*
	* Returns the cost of a particular quantity of purchase
	*/
	public function tallyCost($row)
	{
		return $row['c_quantity'] * $row['og_s_value'];
	}
	
	/*
	* Returns the fees of a particular quantity of purchase
	*/
	public function tallyFee($row)
	{
		return ($row['c_quantity'] * $row['og_s_value']) * ($this->settings['eco_stocks_buy_fee']/100);
	}
	
	/*
	* Returns an array of fields required for when a user purchases an item and it goes to their portfolio
	*/
	public function fieldsForAddingToPortfolio($cartItem, $item)
	{
		$itemArrayFields = array();
		
		$itemArrayFields['p_type_class'] 	= $cartItem['c_type_class'];
		$itemArrayFields['p_amount'] 		= $cartItem['c_quantity'];
		$itemArrayFields['p_max']	 		= $item['s_limit'];
		$itemArrayFields['p_rate']	 		= 0;
		$itemArrayFields['p_rate_ends']	 	= 0;	
		$itemArrayFields['p_last_hit']		= 0;	
		$itemArrayFields['cash_advance']	= 0;
		
		return $itemArrayFields;
	}
	
	/*
	* Return the name of the html template which this cart type uses to show more info when shopping/investing/etc
	*/
	public function htmlTemplate()
	{	
		return 'stock_row';
	}	
	
	/*
	* Format the row to make things pretty for the templates (prettier than this function for sure)
	*/
	public function format($stock, $extra)
	{	
		#can sell back this stock?(added in 1.4.2)
		$stock['can_sell'] = TRUE;
		if ( $stock['s_use_perms'] && !$this->registry->permissions->check( 'close', $stock ) )
		{
			$stock['can_sell'] = FALSE;
		}
		
		#image
		$stock['image_thumb_link']	= $this->registry->ecoclass->customItemImageHTML($stock['s_image'], 'chart_curve.png', true); 
		$stock['image_link']		= $this->registry->ecoclass->customItemImageHTML($stock['s_image'], 'chart_curve.png', false);		
		$stock['image_link_popup']	= $this->registry->ecoclass->customItemImageHTML($stock['s_image'], 'chart_curve.png', false, '', true);
		
		#init description....
		$stock['desc']				= $this->lang->words[ $stock['s_type'].'_stock_exp'];	
		
		#main details...
		$stock['og_s_value']		= $stock['s_value'];
		$stock['s_value']			= $this->registry->getClass('class_localization')->formatNumber( $stock['s_value'], $this->registry->ecoclass->decimal );
		$stock['s_type']			= ucfirst($stock['s_type']);

		if ( $stock['s_type'] != 'Basic' )
		{
			$stock['s_type_var']	= ( $stock['s_type_var'] == 'points' ) ? $this->settings['eco_general_currency'] : $this->lang->words[ ucfirst($stock['s_type_var']) ];
		

			if ( $stock['s_type'] != 'Forum' )
			{
				if ( $stock['s_type'] == 'Member' )
				{
					if ($this->settings['eco_plugin_ppns_on'])
					{
						$member = IPSMember::load( $stock['s_type_var_value'], 'profile_portal' ); 
					}
					else
					{
						$member = $stock; 
					}

					$stock['s_type_var_value']	= $this->registry->getClass('output')->getTemplate('global')->userHoverCard( $member );
				}
				else
				{
					$stock['s_type_var_value']	= $this->caches['group_cache'][intval($stock['s_type_var_value'])]['g_title'];				
				}
			}																
		}

		$stock['s_type_var']		= ( $stock['s_type_var'] ) ? $stock['s_type_var'] : $this->lang->words['n_a'];
		$stock['s_type_var_value']	= ( $stock['s_type_var_value'] ) ? $stock['s_type_var_value'] : $this->lang->words['n_a'];

		#even further (portfolio item)
		$stock['image'] 			= 'chart_curve.png';
		$stock['name'] 				= $stock['s_title'];
		$stock['type'] 				= ucfirst($stock['p_type']);
		$stock['link_type_type']	= $stock['p_type'];	
		$stock['type_type']			= $stock['s_type'];
		$stock['num_shares']		= $this->registry->getClass('class_localization')->formatNumber( $stock['p_amount'] );
		$stock['value']				= ( $stock['s_type_var'] ) ? $stock['s_type_var'] : $this->lang->words['n_a'];						
		$stock['value2']			= $this->settings['eco_general_cursymb'].$stock['s_value'];
		$stock['total_value']		= $this->registry->getClass('class_localization')->formatNumber( $stock['p_amount'] * $stock['og_s_value'], $this->registry->ecoclass->decimal );
		$stock['open_date']			= $this->registry->getClass( 'class_localization')->getDate( $stock['p_purch_date'], 'JOINED' );		
		
		#and yet more! (cart)
		$stock['type'] 				= ( $stock['s_type_var'] == $this->lang->words['n_a'] ) ? $this->lang->words['basic'] : $stock['s_type_var'];	
		$stock['pre_amount_text']	= $this->lang->words['shares'];
		$stock['amount'] 			= $this->registry->getClass('class_localization')->formatNumber( $stock['c_quantity'] );
		$stock['total'] 			= $this->registry->getClass('class_localization')->formatNumber( $stock['c_quantity'] * $stock['og_s_value'], $this->registry->ecoclass->decimal );
		$stock['l_type']			= $stock['c_type'];
		$stock['c_type']			= ucfirst($stock['c_type']);	
		
		#finish description
		$stock['desc'] 				= str_replace( "<%MEMBER_NAME%>", $stock['s_type_var_value'], $stock['desc'] );
		$stock['desc'] 				= str_replace( "<%VAR_NAME%>", $stock['s_type_var'], $stock['desc'] );
		$stock['desc'] 				= str_replace( "<%GROUP_NAME%>", $stock['s_type_var_value'], $stock['desc'] );	

		#totals...
		$stock['holders']			= $this->registry->getClass('class_localization')->formatNumber( $stock['share_holders'] );
		$stock['funds']				= $this->registry->getClass('class_localization')->formatNumber( $stock['total_share_value'] );
		
		#new stuff (1.5.2+)
		$stock['cart_item_title']		= $this->registry->ecoclass->truncate($stock['s_title']." ".$stock['s_title_long'], 32);		
		$stock['cart_item_more_info']	= $this->lang->words['type'].": ".$stock['s_type']." &middot; ".$this->lang->words['variable'].": ".$stock['s_type_var']."<br />".$this->lang->words['subject'].": ".$stock['s_type_var_value'];
		$stock['num_purchased']			= $stock['holders'];
		$stock['num_purchased_text']	= $this->lang->words['share_holders'];
		$stock['cost'] 					= $this->settings['eco_general_cursymb'].$this->registry->getClass('class_localization')->formatNumber($stock['og_s_value'], $this->registry->ecoclass->decimal );
		$stock['cost_text']				= $this->lang->words['current_value'];
		$stock['right_fields_1']		= $stock['funds'];
		$stock['right_fields_1_text']	= $this->lang->words['total_shares_owned'];
		$stock['right_fields_1_image']	= 'bar_graph.png';
		
		$stock['cart_item_type']		= 'stock';
		$stock['cart_item_id']			= $stock['s_id'];
		$stock['cart_item_bank_type']	= 'x';
		$stock['cart_item_bank_Type']	= 'X';		
		$stock['cart_item_tab']			= 'invest';

		return $stock;
	}
}

?>