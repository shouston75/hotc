<?php

/**
 * (e32) ibEconomy
 * Cart Type : Credit-Card
 */

class ibEconomy_cart_type_cc extends ibEconomy_cart_type_cart_item implements ibEconomy_cart_type
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
		return $item['cc_on'];
	}	
	
	/*
	* Return the name that items of this type are called
	*/
	public function name()
	{
		return $this->lang->words['credit_card'];
	}
	
	/*
	* Default icon?
	*/
	public function icon()
	{
		return 'creditcards.png';
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
		return $this->lang->words['opened'];
	}
	
	/*
	* Extra info needed for displaying purchased portfolio items
	* Not static!
	*/
	public function extraPortfolioItemInfo()
	{
		$extraInfo = array();
		
		$extraInfo['total_text'] 	= $this->lang->words['global_debt'];
		$extraInfo['cost'] 		 	= $this->caches['ibEco_ccs'][ $this->cartItem['p_type_id'] ]['cc_cost'];
		$extraInfo['total'] 	 	= $this->caches['ibEco_ccs'][ $this->cartItem['p_type_id'] ]['funds'];
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
		return "g_eco_cc";
	}	
	
	/*
	* Return the group field required to access this plugin (false if none required)
	*/
	public function onOffSetting()
	{
		return "eco_ccs_on";
	}
	
	/*
	* Return the abbreviation used for this type, for example, the setting names use them
	*/
	public function abbreviation()
	{
		return "cc";
	}

	/*
	* Return an item of this type from our cache
	*/
	public function grabItemByID($itemID)
	{
		return $this->caches['ibEco_ccs'][ $itemID ];
	}
	
	/*
	* Runs checks to make sure this item is allowed to be accessed currently
	*/
	public function canAccess($returnNeeded=false)
	{
		return $this->registry->ecoclass->canAccess('ccs', $returnNeeded);
	}

	/*
	* Make sure we have an ID and/or an actual item, if not, return error message 
	*/
	public function gotItemCheck($id, $item)
	{
		$error = "";
		
		if ( !$id )
		{
			$error = str_replace( "<%TYPE%>", $this->lang->words['cc'], $this->lang->words['no_id'] );
		}	

		#no item found by that ID? error!
		if ( !$error && !$item )
		{
			$error = str_replace( "<%TYPE%>", $this->lang->words['cc'], $this->lang->words['none_found_show'] );		
		}
		
		#do I have permission to purchase this item? if not, error!
		if ( !$error && $item[ $this->abbreviation().'_use_perms'] && !$this->registry->permissions->check( 'open', $item ) )
		{
			$error = str_replace( "<%TYPE%>", $this->lang->words['cc'], $this->lang->words['no_perm_to_buy_it'] );		
		}

		#item isn't enabled currently? error!
		if ( !$error && !$item[ $this->abbreviation().'_on'] )
		{
			$error = str_replace( "<%ITEM%>", $this->lang->words['cc'], $this->lang->words['item_disabled'] );
		}		
		
		return $error;
	}
	
	/*
	* Final checks before adding item to cart (like checking quantity, type, etc)
	*/
	public function finalAdd2CartChecks($item, $number, $folioItem, $cartItem, $currentCartQuant, $typeType=false)
	{
		$error = "";
		
		#already in my portfolio?
		if ( $folioItem )
		{
			$error = str_replace( "<%TYPE%>", $this->lang->words['cc'], $this->lang->words['item_present_in_folio'] );		
		}
		
		#cash advance when cash advance is OFF for this cc?
		if ( !$error && $number && !$item['cc_csh_adv'] )
		{
			$error = $this->lang->words['cash_advance_off'];								
		}
		
		#cash advance over group max?  error!
		if ( !$error && $this->memberData['g_eco_cash_adv_max'] && $number + $currentCartQuant > $this->memberData['g_eco_cash_adv_max'] )
		{
			$error = str_replace( "<%YOUR_MAX%>", $this->registry->getClass('class_localization')->formatNumber( $this->memberData['g_eco_cash_adv_max'] ) .' '. $this->numberLang(), $this->lang->words['cart_item_quantity_over_group_max'] );		
			$error = str_replace( "<%TYPE%>", $this->lang->words['cc'], $error );
			$error = str_replace( "<%NUMBER_WORD%>", $this->lang->words['cash_advance'], $error );							
		}

		#cash advance over credit line?  error!
		if ( !$error && $number + $currentCartQuant > $item['cc_max'] )
		{
			$error = str_replace( "<%CREDIT_LINE%>", $this->registry->getClass('class_localization')->formatNumber( $item['cc_max'] ), $this->lang->words['cart_cash_advance_over_card_max'] );								
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
		$redirectMessage = str_replace( "<%TYPE_NAME%>", $this->lang->words['bank'].' <i>'.$item[ $this->abbreviation().'_title'].'</i>', $this->lang->words['stock_added_to_cart'] );
		$redirectMessage = str_replace( "<%NUMBER_TEXT%>", '<i>'.$messageText.'<i> '.$numberName, $redirectMessage );

		return $redirectMessage;
	}
	
	/*
	* Returns the cost of a particular quantity of purchase
	*/
	public function tallyCost($row)
	{
		return $row['cc_cost'];
	}
	
	/*
	* Returns the fees of a particular quantity of purchase
	*/
	public function tallyFee($row)
	{
		return $row['c_quantity'] * $row['cc_csh_adv_fee']/100;
	}	
	
	/*
	* Returns an array of fields required for when a user purchases an item and it goes to their portfolio
	*/
	public function fieldsForAddingToPortfolio($cartItem, $item)
	{
		$itemArrayFields = array();
		
		$itemArrayFields['p_type_class'] 	= $cartItem['c_type_class'];
		$itemArrayFields['p_amount'] 		= $cartItem['c_quantity'];
		$itemArrayFields['p_max']	 		= $item['cc_max'];
		$itemArrayFields['p_rate']	 		= $item['cc_apr'];
		$itemArrayFields['p_rate_ends']	 	= 0;
		$itemArrayFields['p_last_hit']		= time();
		$itemArrayFields['cash_advance']	= $cartItem['c_quantity'];
		
		return $itemArrayFields;
	}

	/*
	* Return the name of the html template which this cart type uses to show more info when shopping/investing/etc
	*/
	public function htmlTemplate()
	{	
		return 'cc_row';
	}
	
	/*
	* Format the row to make things pretty for the templates (prettier than this function for sure)
	*/
	public function format($cc, $extra)
	{	
		#image
		$cc['image_thumb_link']		= $this->registry->ecoclass->customItemImageHTML($cc['cc_image'], 'creditcards.png', true); 
		$cc['image_link']			= $this->registry->ecoclass->customItemImageHTML($cc['cc_image'], 'creditcards.png', false);	
		$cc['image_link_popup']		= $this->registry->ecoclass->customItemImageHTML($cc['cc_image'], 'creditcards.png', false, '', true);
		
		#main details...
		$cc['cost'] 				= $this->registry->getClass('class_localization')->formatNumber( $cc['cc_cost'], $this->registry->ecoclass->decimal );
		$cc['cc_csh_adv_fee']		= ( $cc['cc_csh_adv'] ) ? $cc['cc_csh_adv_fee'] . $this->lang->words['%'] : $this->lang->words['n_a'];
		$cc['cash_adv_max']			= $this->registry->getClass('class_localization')->formatNumber( $this->memberData['g_eco_cash_adv_max'], $this->registry->ecoclass->decimal );
		$cc['cc_bal_trnsfr_apr'] 	= ( $cc['cc_bal_trnsfr'] ) ? $cc['cc_bal_trnsfr_apr'] . $this->lang->words['%'] : $this->lang->words['n_a'];
		$cc['cc_bal_trnsfr_ends']	= $this->registry->getClass('class_localization')->getDate( $cc['cc_bal_trnsfr_end'] * 86400 + time(), 'JOINED' );
		$cc['apr'] 					= $cc['cc_apr'] . $this->lang->words['%'];
		$cc['holders']				= $this->registry->getClass('class_localization')->formatNumber( $cc['card_holders'] );
		$cc['funds']				= $this->registry->getClass('class_localization')->formatNumber( $cc['funds'] );
		$cc['credit_line']			= $this->registry->getClass('class_localization')->formatNumber( $cc['cc_max'] );

		#even further (portfolio item)
		$cc['image'] 				= 'creditcards.png';
		$cc['name'] 				= $cc['cc_title'];
		$cc['type'] 				= ucfirst($cc['p_type']);					
		$cc['link_type_type']		= $cc['p_type'];	
		$cc['type_type']			= ( $cc['cc_max'] ) ? $this->settings['eco_general_cursymb'] . $this->registry->getClass('class_localization')->formatNumber($cc['cc_max']) : $this->lang->words['n_a'];
		$cc['value']				= ( $cc['p_rate'] ) ? $cc['p_rate'] . $this->lang->words['%'] : $this->lang->words['n_a'];						
		$cc['value2']				= ( $cc['cc_bal_trnsfr'] ) ? "<img title = '{$this->lang->words['balance_transfer_on']}' src = '{$this->settings['img_url']}/accept.png' />" : "<img title = '{$this->lang->words['balance_transfer_off']}' src = '{$this->settings['img_url']}/cross.png' />";
		$cc['total_value']			= $this->registry->getClass('class_localization')->formatNumber( $cc['p_amount'], $this->registry->ecoclass->decimal );
		$cc['cc_max']				= ( $cc['cc_max'] ) ? $this->registry->getClass('class_localization')->formatNumber( $cc['cc_max'], $this->registry->ecoclass->decimal ) : $this->lang->words['n_a'];
		$cc['open_date']			= $this->registry->getClass('class_localization')->getDate( $cc['p_purch_date'], 'JOINED' );
		$cc['due_date']				= $this->registry->getClass('class_localization')->getDate( $cc['p_last_hit'] + $this->settings['eco_ccs_cycle'] * 86400, 'JOINED' );
		$cc['min_due']				= ( time() - $cc['p_update_date'] > $this->settings['eco_ccs_cycle'] * 86400 ) ? $this->registry->getClass('class_localization')->formatNumber( $cc['p_amount'] * $this->settings['eco_ccs_min_pay']/100, $this->registry->ecoclass->decimal ) : 0;
		$cc['my_credit_line']		= $this->registry->getClass('class_localization')->formatNumber( $cc['p_max'], $this->registry->ecoclass->decimal );
		$cc['other_cards_dd']		= $this->registry->ecoclass->balTransferCards( $cc['cc_id'] );
		
		#and yet more (cart)
		$cc['type'] 				= ( $cc['cc_csh_adv'] ) ? $this->lang->words['cash'] : $this->lang->words['no_cash'];
		$cc['l_type']				= $cc['c_type'];
		$cc['c_type']				= $this->lang->words['cc'];		
		$cc['pre_amount_text']		= $this->lang->words['advance'];
		$cc['amount'] 				= $this->registry->getClass('class_localization')->formatNumber( $cc['c_quantity'], $this->registry->ecoclass->decimal );
		$cc['total'] 				= $this->registry->getClass('class_localization')->formatNumber( $cc['cc_cost'], $this->registry->ecoclass->decimal );
		
		#description...
		$cc['desc']					= $this->lang->words[ 'cc_exp'];
		$cc['desc'] 				= str_replace( "<%POINTS_NAME%>", $this->settings['eco_general_currency'], $cc['desc'] );
		$cc['desc'] 				= str_replace( "<%APR%>", $cc['apr'], $cc['desc'] );		
		
		#new stuff (1.5.2+)
		$cc['cart_item_title']			= $this->registry->ecoclass->truncate($cc['cc_title']." (".$this->settings['eco_general_cursymb'].$cc['credit_line'].")", 32);		
		$cc['cart_item_more_info']		= $this->lang->words['apr'].": ".$cc['apr']."<br />".$this->lang->words['csh_adv_fee'].": ".$cc['cc_csh_adv_fee'];
		$cc['num_purchased']			= $cc['holders'];
		$cc['num_purchased_text']		= $this->lang->words['card_holders'];
		$cc['cost'] 					= $this->settings['eco_general_cursymb'].$cc['cost'];
		$cc['cost_text']				= $this->lang->words['cost'];
		$cc['right_fields_1']			= $this->settings['eco_general_cursymb'].$cc['funds'];
		$cc['right_fields_1_text']		= $this->lang->words['total_debt'];
		$cc['right_fields_1_image']		= 'money_delete.png';
		
		$cc['cart_item_type']			= 'cc';
		$cc['cart_item_id']				= $cc['cc_id'];
		$cc['cart_item_bank_type']		= 'x';
		$cc['cart_item_bank_Type']		= 'X';		
		$cc['cart_item_tab']			= 'invest';
				
		return $cc;
	}
}

?>