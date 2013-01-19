<?php

/**
 * (e32) ibEconomy
 * Cart Type : Cart Item 
 * Parent of all other cart_types
 */

class ibEconomy_cart_type_cart_item implements ibEconomy_cart_type
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
		return false;
	}
	
	/*
	* Return the value of a specific item's current on/off configuration
	*/
	public function itemOn($item)
	{
		return false;
	}	
	
	/*
	* Return the name that items of this type are called
	*/
	public function name()
	{
		return "Cart Item";
	}
	
	/*
	* Should this item go to the portfolio?
	*/
	public function savedInPortfolio()
	{
		return true;
	}

	/*
	* Should this item go to the portfolio?
	*/
	public function icon()
	{
		return 'tag_blue.png';
	}

	/*
	* Should this item go to the portfolio?
	*/
	public function decimalPlacesForSums()
	{
		return $this->registry->ecoclass->decimal;
	}

	/*
	* Return the currency symbol to display for sums of these items
	*/
	public function currencySymbolForSums()
	{
		return '';
	}

	/*
	* For portfolio tab, what keyword is used to describe the purchase
	*/
	public function purchasedKeyword()
	{
		return $this->lang->words['purchased'];
	}
	
	/*
	* What tab are these on?
	*/
	public function tab()
	{
		return 'invest';
	}
	
	/*
	* Cost of the cartItem object in this class
	* Not static!
	*/
	public function cost()
	{
		return 0;
	}	

	/*
	* Return the group field required to purchase this cart type
	*/
	public function permissionGroupField()
	{
		return "g_eco";
	}
	
	/*
	* Return the text displayed after the amount is purchased, stocks is shares, everything else is points...
	*/
	public function numberLang()
	{
		return $this->settings['eco_general_currency'];
	}

	/*
	* Return true if amounts of this type can be purchased in fractions/decimals
	*/
	public function buyDecimalAmount()
	{
		return true;
	}

	/*
	* Return true if you can buy more than one of this item
	*/
	public function countNumForTotalPurchased()
	{
		return false;
	}	
	
	/*
	* Return the group field required to access this plugin (false if none required)
	*/
	public function onOffSetting()
	{
		return "eco_general_on";
	}

	/*
	* Return the abbreviation used for this type, for, for example, the setting names use them
	*/
	public function abbreviation()
	{
		return "ci";
	}
	
	/*
	* Return an item of this type from our cache
	*/
	public function grabItemByID($itemID)
	{
		return null;
	}

	/*
	* Runs checks to make sure this item is allowed to be accessed currently
	*/
	public function canAccess($returnNeeded=false)
	{
		return false;
	}
	
	/*
	* Make sure we have an ID and/or an actual item, if not, return error message 
	*/
	public function gotItemCheck($id, $item)
	{
		$error = "";
		
		if ( !$id )
		{
			$error = str_replace( "<%TYPE%>", $this->lang->words['cart_item'], $this->lang->words['no_id'] );
		}	

		#no item found by that ID? error!
		if ( !$error && !$item )
		{
			$error = str_replace( "<%TYPE%>", $this->lang->words['cart_item'], $this->lang->words['none_found_show'] );		
		}
		
		#do I have permission to purchase this item? if not, error!
		if ( !$error && $item[ $this->abbreviation().'_use_perms'] && !$this->registry->permissions->check( 'open', $item ) )
		{
			$error = str_replace( "<%TYPE%>", $this->lang->words['cart_item'], $this->lang->words['no_perm_to_buy_it'] );		
		}

		#item isn't enabled currently? error!
		if ( !$error && !$item[ $this->abbreviation().'_on'] )
		{
			$error = str_replace( "<%ITEM%>", $this->lang->words['cart_item'], $this->lang->words['item_disabled'] );
		}		
		
		return $error;
	}
	
	/*
	* Final checks before adding item to cart (like checking quantity, type, etc)
	*/
	public function finalAdd2CartChecks($item, $number, $folioItem, $cartItem, $currentCartQuant, $typeType=false)
	{
		$error = "";				
		
		#no positive deposit amount? error!
		if ( !$error && !$number > 0 )
		{
			$error = $this->lang->words['number_needs_to_be_positive'];		
		}
		
		#already in my portfolio?
		if ( !$error && $folioItem && !$this->countNumForTotalPurchased() )
		{
			$error = str_replace( "<%TYPE%>", $this->lang->words['cart_item'], $this->lang->words['item_present_in_folio'] );		
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
		$redirectMessage = str_replace( "<%TYPE_NAME%>", $this->lang->words['cart_item'].' <i>'.$item[ $this->abbreviation().'_title'].'</i>', $this->lang->words['invest_added_to_cart'] );
		$redirectMessage = str_replace( "<%NUMBER_TEXT%>", '<i>'.$messageText.'<i> '.$numberName, $redirectMessage );

		return $redirectMessage;
	}

	/*
	* Returns the cost of a particular quantity of purchase
	*/
	public function tallyCost($row)
	{
		return $row['c_quantity'] + $row['cost'];
	}
	
	/*
	* Returns the fees of a particular quantity of purchase
	*/
	public function tallyFee($row)
	{
		return $row['c_quantity'] * $row['account_fee']/100;
	}
	
	/*
	* Do any extra type-specific processing that needs to occur upon successful purchase
	*/
	public function postPurchaseProcessing($cartItem)
	{
		return false;
	}	
	
	/*
	* After sucessful purchase of this item, need to go some place special?  (first one that return something not false will win)
	*/
	public function redirectToPage($cartItem)
	{
		return false;
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
		$itemArrayFields['p_rate_ends']	 	= 0;
		$itemArrayFields['p_last_hit']		= 0;
		$itemArrayFields['cash_advance']	= 0;
		
		return $itemArrayFields;
	}	
	
	/*
	* Build and return the html that will be displayed as the title of the cart item in the cart's rows
	*/
	public function rowHtmlTitle($row)
	{
		return "{$row['name']} <a href='{$this->settings['base_url']}app=ibEconomy&amp;tab=invest&amp;area=single&amp;type={$row['c_type']}&amp;id={$row['c_type_id']}' class='__item __id{$row['c_type_id']}__type_{$row['l_type']}_{$row['type']}' title='{$this->lang->words['view_further_details']}'><img src='{$this->settings['img_url']}/user_popup.png'></a>";
	}
	
	/*
	* Build and return the html that will be displayed as the description of the cart item in the cart's rows
	*/
	public function rowHtmlDescription($row)
	{
		return "{$row['c_type']} - {$row['type']}";
	}
	
	/*
	* Return the name of the html template which this cart type uses to show more info when shopping/investing/etc
	*/
	public function htmlTemplate()
	{	
		return 'shop_items_row';
	}	
	
	/*
	* Format the row to make things pretty for the templates (prettier than this function for sure)
	*/
	public function format($item, $type)
	{	
		return $item;
	}
}

?>