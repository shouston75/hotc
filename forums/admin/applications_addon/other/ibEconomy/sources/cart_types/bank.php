<?php

/**
 * (e32) ibEconomy
 * Cart Type : Bank
 */

class ibEconomy_cart_type_bank extends ibEconomy_cart_type_cart_item implements ibEconomy_cart_type
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
	protected	$cartItemTypeType;
	
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
		$this->cartItemTypeType	= $cartItemIn['p_type_class'];
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
		return $item['b_on'] && $item['b_'.$item['c_type_class'].'_on'];
	}	
	
	/*
	* Return the name that items of this type are called
	*/
	public function name()
	{
		return $this->lang->words['bank'];
	}
	
	/*
	* Default icon?
	*/
	public function icon()
	{
		return 'building_key.png';
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
		
		$extraInfo['total_text'] = $this->lang->words['total_funds'];
		
		if ($cartItemTypeType == 'savings')
		{
			$extraInfo['cost']  = $this->caches['ibEco_banks'][ $cartItem['p_type_id'] ]['b_s_acnt_cost'];
			$extraInfo['total'] = $this->caches['ibEco_banks'][ $cartItem['p_type_id'] ]['s_funds'];
		}
		else if ($cartItemTypeType == 'checking')
		{
			$extraInfo['cost']  = $this->caches['ibEco_banks'][ $cartItem['p_type_id'] ]['b_c_acnt_cost'];
			$extraInfo['total'] = $this->caches['ibEco_banks'][ $cartItem['p_type_id'] ]['c_funds'];
		}
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
		return "g_eco_bank";
	}	
	
	/*
	* Return the group field required to access this plugin (false if none required)
	*/
	public function onOffSetting()
	{
		return "eco_banks_on";
	}

	/*
	* Return the abbreviation used for this type, for example, the setting names use them
	*/
	public function abbreviation()
	{
		return "b";
	}
	
	/*
	* Return an item of this type from our cache
	*/
	public function grabItemByID($itemID)
	{
		return $this->caches['ibEco_banks'][ $itemID ];
	}

	/*
	* Runs checks to make sure this item is allowed to be accessed currently
	*/
	public function canAccess($returnNeeded=false)
	{
		return $this->registry->ecoclass->canAccess('banks', $returnNeeded);
	}
	
	/*
	* Make sure we have an ID and/or an actual item, if not, return error message 
	*/
	public function gotItemCheck($id, $item)
	{
		$error = "";
		
		if ( !$id )
		{
			$error = str_replace( "<%TYPE%>", $this->lang->words['bank'], $this->lang->words['no_id'] );
		}	

		#no item found by that ID? error!
		if ( !$error && !$item )
		{
			$error = str_replace( "<%TYPE%>", $this->lang->words['bank'], $this->lang->words['none_found_show'] );		
		}
		
		#do I have permission to purchase this item? if not, error!
		if ( !$error && $item[ $this->abbreviation().'_use_perms'] && !$this->registry->permissions->check( 'open', $item ) )
		{
			$error = str_replace( "<%TYPE%>", $this->lang->words['bank'], $this->lang->words['no_perm_to_buy_it'] );		
		}

		#item isn't enabled currently? error!
		if ( !$error && !$item[ $this->abbreviation().'_on'] )
		{
			$error = str_replace( "<%ITEM%>", $this->lang->words['bank'], $this->lang->words['item_disabled'] );
		}		
		
		return $error;
	}
	
	/*
	* Final checks before adding item to cart (like checking quantity, type, etc)
	*/
	public function finalAdd2CartChecks($item, $number, $folioItem, $cartItem, $currentCartQuant, $typeType=false)
	{
		$error = "";
		
		#no banktype?  error!
		if ( $typeType != 'checking' && $typeType != 'savings' )
		{
			$error = $this->lang->words['no_bank_type'];		
		}

		#no new accounts in that type for this bank? error!
		if ( !$error && !$item['b_'.$typeType.'_on'] )
		{
			$error = str_replace( "<%BANK_TYPE%>", $this->lang->words[ $typeType ], $this->lang->words['bank_type_disabled'] );
		}					
		
		#no positive deposit amount? error!
		if ( !$error && !$number > 0 )
		{
			$error = $this->lang->words['positive_bank_dep_needed'];		
		}
		
		#already in my portfolio?
		if ( !$error && $folioItem )
		{
			$error = str_replace( "<%TYPE%>", $this->lang->words['bank'], $this->lang->words['item_present_in_folio'] );		
		}
		
		#deposit over group max?
		if ( !$error && $this->memberData['g_eco_bank_max'] && $number + $currentCartQuant > $this->memberData['g_eco_bank_max'] )
		{
			$error = str_replace( "<%YOUR_MAX%>", $this->registry->getClass('class_localization')->formatNumber( $this->memberData['g_eco_bank_max'] ) .' '. $this->numberLang(), $this->lang->words['cart_item_quantity_over_group_max'] );		
			$error = str_replace( "<%TYPE%>", $this->lang->words['bank'], $error );
			$error = str_replace( "<%NUMBER_WORD%>", strtolower($this->lang->words['amount']), $error );						
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
		return $row['c_quantity'] + $row['og_cost'];
	}
	
	/*
	* Returns the fees of a particular quantity of purchase
	*/
	public function tallyFee($row)
	{
		return $row['c_quantity'] * $row['account_fee']/100;
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
		$itemArrayFields['p_rate']	 		= ($cartItem['c_type_class'] == 'savings' ) ? $item['b_sav_interest'] : '';						
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
		return 'bank_row';
	}	
	
	/*
	* Format the row to make things pretty for the templates (prettier than this function for sure)
	*/
	public function format($bank, $type)
	{	
		#image
		$bank['image_thumb_link']	= $this->registry->ecoclass->customItemImageHTML($bank['b_image'], 'building_key.png', true); 
		$bank['image_link']			= $this->registry->ecoclass->customItemImageHTML($bank['b_image'], 'building_key.png', false);		
		$bank['image_link_popup']	= $this->registry->ecoclass->customItemImageHTML($bank['b_image'], 'building_key.png', false, '', true);
		
		#langed type
		$bank['langed_type'] = $this->lang->words[ $type ];
		
		if ( $type == 'checking' )
		{
			#format checking account details
			$bank['og_cost'] 		= $bank['b_c_acnt_cost'];
			$bank['cost'] 			= $this->registry->getClass('class_localization')->formatNumber( $bank['b_c_acnt_cost'], $this->registry->ecoclass->decimal );
			$bank['dep_fee'] 		= $bank['b_c_dep_fee'] . $this->lang->words['%'];
			$bank['wth_fee'] 		= $bank['b_c_wthd_fee'] . $this->lang->words['%'];
			$bank['account_fee']  	= $bank['b_c_dep_fee'];
			$bank['apy'] 			= $this->lang->words['n_a'];
			$bank['holders']		= $this->registry->getClass('class_localization')->formatNumber( $bank['c_total']  );
			$bank['funds']			= $this->registry->getClass('class_localization')->formatNumber( $bank['c_funds'], $this->registry->ecoclass->decimal );
			$bank['type']			= 'Checking';
			$bank['value']			= $this->lang->words['n_a'];
			$bank['value2']			= $bank['b_c_wthd_fee'] . $this->lang->words['%'];
			$bank['desc']			= $this->lang->words[ 'bank_checking_exp'];
		}
		else if ( $type == 'savings' )
		{
			#format savings account details
			$bank['og_cost'] 		= $bank['b_s_acnt_cost'];
			$bank['cost'] 			= $this->registry->getClass('class_localization')->formatNumber( $bank['b_s_acnt_cost'], $this->registry->ecoclass->decimal );
			$bank['dep_fee'] 		= $bank['b_s_dep_fee'] . $this->lang->words['%'];
			$bank['wth_fee'] 		= $bank['b_s_wthd_fee'] . $this->lang->words['%'];
			$bank['account_fee']  	= $bank['b_s_dep_fee'];
			$bank['apy'] 			= $bank['b_sav_interest'] . $this->lang->words['%'];
			$bank['holders']		= $this->registry->getClass('class_localization')->formatNumber( $bank['s_total']  );
			$bank['funds']			= $this->registry->getClass('class_localization')->formatNumber( $bank['s_funds'], $this->registry->ecoclass->decimal );
			$bank['type']			= 'Savings';
			$bank['value']			= $bank['b_sav_interest'] . $this->lang->words['%'];			
			$bank['value2']			= $bank['b_s_wthd_fee'] . $this->lang->words['%'];
			$bank['desc']			= $this->lang->words[ 'bank_savings_exp'];
		}
				
		#even further (portfolio this time)
		$bank['image'] 				= 'building_key.png';
		$bank['name'] 				= $bank['b_title'];
		$bank['type_type']			= $bank['link_type_type'] = ucfirst($bank['p_type_class']);		

		$bank['open_date']			= $this->registry->getClass('class_localization')->getDate( $bank['p_purch_date'], 'JOINED' );		
		$bank['total_value']		= $this->registry->getClass('class_localization')->formatNumber( $bank['p_amount'], $this->registry->ecoclass->decimal );
		
		#and more! (cart row)
		$bank['link_type_type']	 	= ( $bank['type_type'] ) ? $bank['type_type'] : ucfirst($bank['c_type_class']);
		$bank['pre_amount_text']	= $this->lang->words['deposit'];
		$bank['amount'] 			= $this->registry->getClass('class_localization')->formatNumber( $bank['c_quantity'], $this->registry->ecoclass->decimal );
		$bank['total'] 				= $this->registry->getClass('class_localization')->formatNumber( $bank['c_quantity'] + $bank['og_cost'], $this->registry->ecoclass->decimal );
		$bank['l_type']				= $bank['c_type'];
		$bank['c_type']				= ucfirst($bank['c_type']);	
		
		#OMG just stop! (loan row)
		$bank['b_loans_max']			= $this->registry->getClass('class_localization')->formatNumber( $bank['b_loans_max'], $this->registry->ecoclass->decimal );
		$bank['b_loans_fee'] 			= $this->registry->getClass('class_localization')->formatNumber( $bank['b_loans_fee'], 2 );
		$bank['b_loans_days'] 			= $this->registry->getClass('class_localization')->formatNumber( $bank['b_loans_days'] );
		$bank['b_loans_pen']			= $this->registry->getClass('class_localization')->formatNumber( $bank['b_loans_pen'], 2 );
		$bank['b_loans_count']			= $this->registry->getClass('class_localization')->formatNumber( $bank['loaners'] );	
		$bank['outstanding_loan_amt']	= $this->registry->getClass('class_localization')->formatNumber( $bank['outstanding_loan_amt'], $this->registry->ecoclass->decimal );			
		$bank['b_loans_app_fee']		= $this->registry->getClass('class_localization')->formatNumber( $bank['b_loans_app_fee']);
		
		#new stuff (1.5.2+)
		$bank['cart_item_title']		= $this->registry->ecoclass->truncate($bank['b_title']." ".$bank['langed_type'], 32);
		$bank['cart_item_more_info']	= $this->lang->words['dep_fee'].": ".$bank['dep_fee']."<br />".$this->lang->words['with_fee'].": ".$bank['wth_fee'];
		$bank['cost'] 					= $this->settings['eco_general_cursymb'].$bank['cost'];
		$bank['cost_text']				= $this->lang->words['cost'];		
		$bank['num_purchased']			= $bank['holders'];
		$bank['num_purchased_text']		= $this->lang->words['account_holders'];
		$bank['right_fields_1']			= $bank['funds'];
		$bank['right_fields_1_text']	= $this->lang->words['total_funds'];
		$bank['right_fields_1_image']	= 'donate.png';
		
		$bank['cart_item_type']			= 'bank';
		$bank['cart_item_id']			= $bank['b_id'];
		$bank['cart_item_bank_type']	= $type;
		$bank['cart_item_bank_Type']	= ucfirst($type);
		$bank['cart_item_tab']			= 'invest';	
		
		$bank['desc'] 					= str_replace( "<%POINTS_NAME%>", $this->settings['eco_general_currency'], $bank['desc'] );
		
		return $bank;
	}
}

?>