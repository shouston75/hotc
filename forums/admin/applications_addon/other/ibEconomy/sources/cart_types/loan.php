<?php

/**
 * (e32) ibEconomy
 * Cart Type : Loan
 */

class ibEconomy_cart_type_loan extends ibEconomy_cart_type_cart_item implements ibEconomy_cart_type
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
		return $item['b_loans_on'];
	}	
	
	/*
	* Return the name that items of this type are called
	*/
	public function name()
	{
		return $this->lang->words['loan'];
	}
	
	/*
	* Default icon?
	*/
	public function icon()
	{
		return 'money_add.png';
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
		return $this->lang->words['taken'];
	}
	
	/*
	* Extra info needed for displaying purchased portfolio items
	* Not static!
	*/
	public function extraPortfolioItemInfo()
	{
		$extraInfo = array();
		
		$extraInfo['total_text'] 	= $this->lang->words['total_funds'];
		$extraInfo['cost']  		= $this->caches['ibEco_banks'][ $cartItem['p_type_id'] ]['b_loans_app_fee'];
		$extraInfo['total'] 		= $this->caches['ibEco_banks'][ $cartItem['p_type_id'] ]['outstanding_loan_amt'];
		$extraInfo['total_bought']	= $this->currencySymbolForSums().$this->registry->getClass('class_localization')->formatNumber( $extraInfo['total'], $this->decimalPlacesForSums());
		
		return $extraInfo;
	}
	
	/*
	* What do we call this item anyway?
	*/
	public function title($item)
	{
		return $item['b_title'];
	}	

	/*
	* What tab are these on?
	*/
	public function tab()
	{
		return 'cash';
	}	

	/*
	* Return the group field required to purchase this cart type
	*/
	public function permissionGroupField()
	{
		return "g_eco_loan";
	}
	
	/*
	* Return the group field required to access this plugin (false if none required)
	*/
	public function onOffSetting()
	{
		return "eco_loans_on";
	}
	
	/*
	* Return the abbreviation used for this type, for example, the setting names use them
	*/
	public function abbreviation()
	{
		return "l";
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
		return $this->registry->ecoclass->canAccess('loans', $returnNeeded);
	}
	
	/*
	* Make sure we have an ID and/or an actual item, if not, return error message 
	*/
	public function gotItemCheck($id, $item)
	{
		$error = "";
		
		if ( !$id )
		{
			$error = str_replace( "<%TYPE%>", $this->lang->words['loan'], $this->lang->words['no_id'] );
		}	

		#no item found by that ID? error!
		if ( !$error && !$item )
		{
			$error = str_replace( "<%TYPE%>", $this->lang->words['loan'], $this->lang->words['none_found_show'] );		
		}
		
		#do I have permission to purchase this item? if not, error!
		if ( !$error && $item[ $this->abbreviation().'_use_perms'] && !$this->registry->permissions->check( 'loans', $item ) )
		{
			$error = str_replace( "<%TYPE%>", $this->lang->words['loan'], $this->lang->words['no_perm_to_buy_it'] );		
		}

		#item isn't enabled currently? error!
		if ( !$error && !$item['b_loans_on'] )
		{
			$error = str_replace( "<%ITEM%>", $this->lang->words['loan'], $this->lang->words['item_disabled'] );
		}		
				
		return $error;
	}

	/*
	* Final checks before adding item to cart (like checking quantity, type, etc)
	*/
	public function finalAdd2CartChecks($item, $number, $folioItem, $cartItem, $currentCartQuant, $typeType=false)
	{
		$error = "";
		
		#already have a previous unpaid loan with this bank
		if ( !$error && $folioItem )
		{
			$error = $this->lang->words['previous_loan_unpaid'];
		}
		
		#no new accounts in that type for this bank? error!
		if ( !$error && !$item['b_loans_on'] )
		{
			$error = $this->lang->words['bank_loans_disabled'];
		}					
		
		#no positive deposit amount? error!
		if ( !$error && !$number > 0 )
		{
			$error = $this->lang->words['positive_loan_amount_needed'];		
		}
		
		#deposit over group max?
		if ( !$error && $this->memberData['g_eco_max_loan_debt'] && $number + $currentCartQuant > $this->memberData['g_eco_max_loan_debt'] )
		{
			$error = str_replace( "<%YOUR_MAX%>", $this->registry->getClass('class_localization')->formatNumber( $this->memberData['g_eco_max_loan_debt'] ) .' '. $this->numberLang(), $this->lang->words['cart_item_quantity_over_group_max'] );		
			$error = str_replace( "<%TYPE%>", $this->lang->words['loans'], $error );
			$error = str_replace( "<%NUMBER_WORD%>", $this->lang->words['loan_amount'], $error );						
		}
		
		#deposit over bank loan max?
		if ( !$error && $item['b_loans_max'] && $number + $currentCartQuant > $item['b_loans_max'] )
		{
			$error = str_replace( "<%MAX%>", $this->registry->getClass('class_localization')->formatNumber( $item['b_loans_max'] ) .' '. $this->numberLang(), $this->lang->words['over_bank_loan_max'] );
		}

		return $error;
	}
	
	/*
	* Return the message to be displayed upon successfully adding an item to cart
	*/
	public function add2CartRedirectMessage($checks, $item)
	{
		$redirectMessage = "";	
		
		$redirectMessage = str_replace( "<%TYPE_NAME%>", '<i>'.$theItem[ $typeAbr.'_title'].'</i>', $this->lang->words['loan_added_to_cart'] );
		$redirectMessage = str_replace( "<%NUMBER_TEXT%>", '<i>'.$this->registry->getClass('class_localization')->formatNumber( $checks['number'], $this->registry->ecoclass->decimal ).'<i> '.$numberName, $redirectMessage );

		return $redirectMessage;
	}	
	
	/*
	* Returns the cost of a particular quantity of purchase
	*/
	public function tallyCost($row)
	{
		return $row['b_loans_app_fee'];
	}
	
	/*
	* Returns the fees of a particular quantity of purchase
	*/
	public function tallyFee($row)
	{
		return 0;
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
		$itemArrayFields['p_rate_ends']		= time() + $item['b_loans_days'] * 86400;
		$itemArrayFields['p_rate']	 		= $item['b_loans_fee'];
		$itemArrayFields['cash_advance']	= $cartItem['c_quantity'];
		
		return $itemArrayFields;
	}

	/*
	* Return the name of the html template which this cart type uses to show more info when shopping/investing/etc
	*/
	public function htmlTemplate()
	{	
		return 'bankLoanRows';
	}
	
	/*
	* Format the row to make things pretty for the templates (prettier than this function for sure)
	*/
	public function format($bank, $extra)
	{	
		#image
		$bank['image_thumb_link']	= $this->registry->ecoclass->customItemImageHTML($bank['b_image'], 'money_add.png', true); 
		$bank['image_link']			= $this->registry->ecoclass->customItemImageHTML($bank['b_image'], 'money_add.png', false);	
		$bank['image_link_popup']	= $this->registry->ecoclass->customItemImageHTML($bank['b_image'], 'money_add.png', false, '', true);
		
		#langed type
		$bank['langed_type'] 	= $this->lang->words['loan'];

		#format savings account details
		$bank['og_cost'] 		= $bank['b_loans_app_fee'];
		$bank['cost'] 			= $this->registry->getClass('class_localization')->formatNumber( $bank['b_loans_app_fee'], $this->registry->ecoclass->decimal );
		$bank['dep_fee'] 		= $bank['b_s_dep_fee'] . $this->lang->words['%'];
		$bank['wth_fee'] 		= $bank['b_s_wthd_fee'] . $this->lang->words['%'];
		$bank['account_fee']  	= $bank['b_s_dep_fee'];
		$bank['apy'] 			= $bank['b_loans_fee'] . $this->lang->words['%'];
		$bank['holders']		= $this->registry->getClass('class_localization')->formatNumber( $bank['s_total']  );
		$bank['funds']			= $this->registry->getClass('class_localization')->formatNumber( $bank['s_funds'], $this->registry->ecoclass->decimal );
		$bank['type']			= 'Loan';
		$bank['value']			= $bank['b_loans_fee'] . $this->lang->words['%'];
		$bank['value2']			= $this->registry->getClass( 'class_localization')->getDate( $bank['p_rate_ends'], 'JOINED' );	
		
		#even further (portfolio this time)
		$bank['image'] 				= 'building_key.png';
		$bank['name'] 				= $bank['b_title'];
		$bank['type_type']			= $bank['link_type_type'] = ucfirst($bank['p_type_class']);		

		$bank['open_date']			= $this->registry->getClass('class_localization')->getDate( $bank['p_purch_date'], 'JOINED' );		
		$bank['total_value']		= $this->registry->getClass('class_localization')->formatNumber( $bank['p_amount'], $this->registry->ecoclass->decimal );
		
		#and more! (cart row)
		$bank['link_type_type']	 	= ( $bank['type_type'] ) ? $bank['type_type'] : ucfirst($bank['c_type_class']);
		$bank['pre_amount_text']	= $this->lang->words['Loan_Amount'];
		$bank['amount'] 			= $this->registry->getClass('class_localization')->formatNumber( $bank['c_quantity'], $this->registry->ecoclass->decimal );
		$bank['total'] 				= $this->registry->getClass('class_localization')->formatNumber( $bank['og_cost'], $this->registry->ecoclass->decimal );
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
		$bank['cart_item_title']		= $this->registry->ecoclass->truncate($bank['b_title'], 32);
		$bank['cart_item_more_info']	= $this->lang->words['max_loan'].": ".$this->settings['eco_general_cursymb'].$bank['b_loans_max']."<br />".$this->lang->words['loan_fee'].": ".$bank['b_loans_fee'].$this->lang->words['%'];
		$bank['cost'] 					= $this->settings['eco_general_cursymb'].$bank['b_loans_app_fee'];
		$bank['cost_text']				= $this->lang->words['application_fee'];		
		$bank['num_purchased']			= $bank['b_loans_count'];
		$bank['num_purchased_text']		= $this->lang->words['loans_approved'];
		$bank['right_fields_1_text']	= $this->lang->words['timelimit'];
		$bank['right_fields_1']			= $bank['b_loans_days']." ".$this->lang->words['days'];
		$bank['right_fields_1_image']	= 'clock_red.png';
		
		$bank['cart_item_type']			= 'bank';
		$bank['cart_item_id']			= $bank['b_id'];
		$bank['cart_item_bank_type']	= 'loan';
		$bank['cart_item_bank_Type']	= 'Loan';
		$bank['cart_item_tab']			= 'cash';	

		$bank['desc']					= $this->lang->words[ 'bank_loan_exp'];
		$bank['desc'] 					= str_replace( "<%POINTS_NAME%>", $this->settings['eco_general_currency'], $bank['desc'] );

		return $bank;
	}
}

?>