<?php

/**
 * (e32) ibEconomy
 * Cart Type : Long-Term
 */

class ibEconomy_cart_type_lt extends ibEconomy_cart_type_cart_item implements ibEconomy_cart_type
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
		return $item['lt_on'];
	}	
	
	/*
	* Return the name that items of this type are called
	*/
	public function name()
	{
		return $this->lang->words['long_term'];
	}
	
	/*
	* Default icon?
	*/
	public function icon()
	{
		return 'bar_graph.png';
	}

	/*
	* Return the currency symbol to display for sums of these items
	*/
	public function currencySymbolForSums()
	{
		return $this->settings['eco_general_cursymb'];
	}

	/*
	* For portfolio tab, what keyword is used to describe the purchase
	*/
	public function purchasedKeyword()
	{
		return $this->lang->words['invested'];
	}
	
	/*
	* Extra info needed for displaying purchased portfolio items
	* Not static!
	*/
	public function extraPortfolioItemInfo()
	{
		$extraInfo = array();
		
		$extraInfo['total_text'] 	= $this->lang->words['global_invested'];
		$extraInfo['cost'] 		 	= $this->caches['ibEco_lts'][ $this->cartItem['p_type_id'] ]['lt_min'];
		$extraInfo['total'] 	 	= $this->caches['ibEco_lts'][ $this->cartItem['p_type_id'] ]['total_invested'];
		$extraInfo['total_bought']	= $this->currencySymbolForSums().$this->registry->getClass('class_localization')->formatNumber( $extraInfo['total'], $this->decimalPlacesForSums());
		
		return $extraInfo;
	}

	/*
	* What do we call this item anyway?
	*/
	public function title($item)
	{
		return $item[ $this->abbreviation().'_title' ];
	}

	/*
	* Return the group field required to purchase this cart type
	*/
	public function permissionGroupField()
	{
		return "g_eco_lt";
	}
	
	/*
	* Return the group field required to access this plugin (false if none required)
	*/
	public function onOffSetting()
	{
		return "eco_lts_on";
	}
	
	/*
	* Return the abbreviation used for this type, for example, the setting names use them
	*/
	public function abbreviation()
	{
		return "lt";
	}	
	
	/*
	* Return an item of this type from our cache
	*/
	public function grabItemByID($itemID)
	{
		return $this->caches['ibEco_lts'][ $itemID ];
	}
	
	/*
	* Runs checks to make sure this item is allowed to be accessed currently
	*/
	public function canAccess($returnNeeded=false)
	{
		return $this->registry->ecoclass->canAccess('lts', $returnNeeded);
	}

	/*
	* Make sure we have an ID and/or an actual item, if not, return error message 
	*/
	public function gotItemCheck($id, $item)
	{
		$error = "";
		
		if ( !$id )
		{
			$error = str_replace( "<%TYPE%>", $this->lang->words['lt'], $this->lang->words['no_id'] );
		}	

		#no item found by that ID? error!
		if ( !$error && !$item )
		{
			$error = str_replace( "<%TYPE%>", $this->lang->words['lt'], $this->lang->words['none_found_show'] );		
		}
		
		#do I have permission to purchase this item? if not, error!
		if ( !$error && $item[ $this->abbreviation().'_use_perms'] && !$this->registry->permissions->check( 'open', $item ) )
		{
			$error = str_replace( "<%TYPE%>", $this->lang->words['lt'], $this->lang->words['no_perm_to_buy_it'] );		
		}

		#item isn't enabled currently? error!
		if ( !$error && !$item[ $this->abbreviation().'_on'] )
		{
			$error = str_replace( "<%ITEM%>", $this->lang->words['lt'], $this->lang->words['item_disabled'] );
		}		
		
		return $error;
	}
	
	/*
	* Final checks before adding item to cart (like checking quantity, type, etc)
	*/
	public function finalAdd2CartChecks($item, $number, $folioItem, $cartItem, $currentCartQuant, $typeType=false)
	{
		$error = "";
		
		#too little to invest?  error!
		if ( $number < $item['lt_min'] )
		{
			$error = $this->lang->words['not_met_let_min'];
		}

		#invested over group max?  error!
		if ( !$error && $this->memberData['g_eco_lt_max'] && $number + $folioItem['p_amount'] + $currentCartQuant > $this->memberData['g_eco_lt_max'] )
		{
			$error = str_replace( "<%YOUR_MAX%>", $this->registry->getClass('class_localization')->formatNumber( $this->memberData['g_eco_lt_max'] ) .' '. $this->numberLang(), $this->lang->words['cart_item_quantity_over_group_max'] );		
			$error = str_replace( "<%TYPE%>", $this->lang->words['lt'], $error );
			$error = str_replace( "<%NUMBER_WORD%>", $this->lang->words['amount'], $error );							
		}

		return $error;
	}	
	
	/*
	* Return the message to be displayed upon successfully adding an item to cart
	*/
	public function add2CartRedirectMessage($checks, $item)
	{
		$redirectMessage = "";	
		
		$messageText 	 = ( $checks['number'] > 0 ) ?  $this->registry->getClass('class_localization')->formatNumber( $checks['number'], $this->registry->ecoclass->decimal ) : 0;
		$redirectMessage = str_replace( "<%TYPE_NAME%>", $this->lang->words['bank'].' <i>'.$item[ $this->abbreviation().'_title'].'</i>', $this->lang->words['invest_added_to_cart'] );
		$redirectMessage = str_replace( "<%NUMBER_TEXT%>", '<i>'.$messageText.'<i> '.$numberName, $redirectMessage );

		return $redirectMessage;
	}
	
	/*
	* Returns the cost of a particular quantity of purchase
	*/
	public function tallyCost($row)
	{
		return $row['c_quantity'];
	}
	
	/*
	* Returns the fees of a particular quantity of purchase
	*/
	public function tallyFee($row)
	{
		return $row['c_quantity'] * $this->settings['eco_lts_buy_fee']/100;
	}	
	
	/*
	* Returns an array of fields required for when a user purchases an item and it goes to their portfolio
	*/
	public function fieldsForAddingToPortfolio($cartItem, $item)
	{
		$itemArrayFields = array();
		
		$itemArrayFields['p_type_class'] 	= $cartItem['c_type_class'];
		$itemArrayFields['p_amount'] 		= $cartItem['c_quantity'];
		$itemArrayFields['p_max']	 		= 0;
		$itemArrayFields['p_rate']	 		= 0;
		$itemArrayFields['p_rate_ends']	 	= time() + $item['lt_min_days'] * 86400;
		$itemArrayFields['p_last_hit']		= 0;
		$itemArrayFields['cash_advance']	= 0;
		
		return $itemArrayFields;
	}

	/*
	* Return the name of the html template which this cart type uses to show more info when shopping/investing/etc
	*/
	public function htmlTemplate()
	{	
		return 'lt_row';
	}	
	
	/*
	* Format the row to make things pretty for the templates (prettier than this function for sure)
	*/
	public function format($lt, $extra)
	{	
		#image
		$lt['image_thumb_link']		= $this->registry->ecoclass->customItemImageHTML($lt['lt_image'], 'bar_graph.png', true); 
		$lt['image_link']			= $this->registry->ecoclass->customItemImageHTML($lt['lt_image'], 'bar_graph.png', false);	
		$lt['image_link_popup']		= $this->registry->ecoclass->customItemImageHTML($lt['lt_image'], 'bar_graph.png', false, '', true);
	
		#main details...
		$lt['min_invest'] 			= $this->registry->getClass('class_localization')->formatNumber( $lt['lt_min'] );
		$lt['min_days']				= $this->registry->getClass('class_localization')->formatNumber( $lt['lt_min_days'] );
		$lt['lt_type']				= ucfirst($lt['lt_type']);
		//$lt['lt_early_cash']		= ( $lt['lt_early_cash'] ) ? $lt['lt_early_cash_fee'] . $this->lang->words['%'] : $this->lang->words['not_allowed'];
		$lt['holders']				= $this->registry->getClass('class_localization')->formatNumber( $lt['investors'] );
		$lt['funds']				= $this->registry->getClass('class_localization')->formatNumber( $lt['total_invested'], $this->registry->ecoclass->decimal );

		#even further (portfolio item)		
		$lt['image'] 				= 'bar_graph.png';
		$lt['name'] 				= $lt['lt_title'];
		$lt['type'] 				= ucfirst($lt['p_type']);						
		$lt['link_type_type']		= $lt['p_type'];	
		$lt['type_type']			= $lt['lt_type'];
		$lt['value']				= ( $lt['p_rate_ends'] ) ? $this->registry->getClass( 'class_localization')->getDate( $lt['p_rate_ends'], 'JOINED' ) : $this->lang->words['n_a'];						
		$lt['value2']				= ( $lt['lt_early_cash'] ) ? "<img title = '{$this->lang->words['early_cashout_on']}' src = '{$this->settings['img_url']}/accept.png' />" : "<img title = '{$this->lang->words['early_cashout_off']}' src = '{$this->settings['img_url']}/cross.png' />";
		$lt['total_value']			= $this->registry->getClass('class_localization')->formatNumber( $lt['p_amount'], $this->registry->ecoclass->decimal );
		$lt['open_date']			= $this->registry->getClass('class_localization')->getDate( $lt['p_purch_date'], 'JOINED' );
		
		#and yet more (cart)
		$lt['type'] 				= $lt['lt_type'];
		$lt['l_type']				= $lt['c_type'];
		$lt['c_type']				= $this->lang->words['lt'];		
		$lt['cost'] 				= $this->lang->words['n_a'];
		$lt['pre_amount_text']		= $this->lang->words['investment'];
		$lt['amount'] 				= $this->registry->getClass('class_localization')->formatNumber( $lt['c_quantity'], $this->registry->ecoclass->decimal );
		$lt['total'] 				= $this->registry->getClass('class_localization')->formatNumber( $lt['c_quantity'], $this->registry->ecoclass->decimal );
		
		#description...
		$lt['desc']					= $this->lang->words['lt_exp'];
		$lt['desc'] 				= str_replace( "<%POINTS_NAME%>", $this->settings['eco_general_currency'], $lt['desc'] );
		$lt['desc'] 				= str_replace( "<%DAYS%>", $lt['min_days'], $lt['desc'] );
		$lt['desc'] 				= str_replace( "<%FEE%>", $lt['lt_early_cash'] ? $lt['lt_early_cash_fee'].$this->lang->words['%'] : $this->lang->words['it_isnt_allowed'], $lt['desc'] );
		
		#new stuff (1.5.2+)
		$lt['cart_item_title']			= $this->registry->ecoclass->truncate($lt['lt_title']." ".$lt['lt_type'], 32);		
		$lt['cart_item_more_info']		= $this->lang->words['min_invest_time'].": ".$this->settings['eco_general_cursymb'].$lt['min_invest']."<br />".$this->lang->words['early_cash_fee'].": ".$lt['lt_early_cash'];
		$lt['num_purchased']			= $lt['holders'];
		$lt['num_purchased_text']		= $this->lang->words['total_investors'];
		$lt['cost'] 					= $this->settings['eco_general_cursymb'].$lt['min_invest'];
		$lt['cost_text']				= $this->lang->words['min_investment'];
		$lt['right_fields_1']			= $lt['funds'];
		$lt['right_fields_1_text']		= $this->lang->words['total_invested'];
		$lt['right_fields_1_image']		= 'donate.png';
		
		$lt['cart_item_type']			= 'lt';
		$lt['cart_item_id']				= $lt['lt_id'];
		$lt['cart_item_bank_type']		= 'x';
		$lt['cart_item_bank_Type']		= 'X';		
		$lt['cart_item_tab']			= 'invest';
	
		return $lt;
	}
}

?>