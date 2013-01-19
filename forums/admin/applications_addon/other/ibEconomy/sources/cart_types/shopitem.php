<?php

/**
 * (e32) ibEconomy
 * Cart Type : Shop Item
 */

class ibEconomy_cart_type_shopitem extends ibEconomy_cart_type_cart_item implements ibEconomy_cart_type
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
		return $item['si_on'];
	}	
	
	/*
	* Return the name that items of this type are called
	*/
	public function name()
	{
		return $this->lang->words['shop_item'];
	}
	
	/*
	* Default icon?
	*/
	public function icon()
	{
		return 'tag_blue.png';
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
		
		$extraInfo['total_text'] 	= $this->lang->words['total_sold'];
		$extraInfo['cost'] 		 	= $this->caches['ibEco_shopitems'][ $this->cartItem['p_type_id'] ]['si_cost'];
		$extraInfo['total'] 		= $this->caches['ibEco_shopitems'][ $this->cartItem['p_type_id'] ]['si_sold'];		
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
	* What tab are these on?
	*/
	public function tab()
	{
		return 'shop';
	}	

	/*
	* Return the group field required to purchase this cart type
	*/
	public function permissionGroupField()
	{
		return "g_eco_shopitem";
	}

	/*
	* Return true if amounts of this type can be purchased in fractions/decimals
	*/
	public function buyDecimalAmount()
	{
		return false;
	}

	/*
	* Return true if you can buy more than one of this item
	*/
	public function countNumForTotalPurchased()
	{
		return true;
	}	
	
	/*
	* Return the group field required to access this plugin (false if none required)
	*/
	public function onOffSetting()
	{
		return "eco_shopitems_on";
	}
	
	/*
	* Return the abbreviation used for this type, for example, the setting names use them
	*/
	public function abbreviation()
	{
		return "si";
	}

	/*
	* Return an item of this type from our cache
	*/
	public function grabItemByID($itemID)
	{
		return $this->caches['ibEco_shopitems'][ $itemID ];
	}
	
	/*
	* Runs checks to make sure this item is allowed to be accessed currently
	*/
	public function canAccess($returnNeeded=false)
	{
		return $this->registry->ecoclass->canAccess('shopitems', $returnNeeded);
	}
	
	/*
	* Make sure we have an ID and/or an actual item, if not, return error message 
	*/
	public function gotItemCheck($id, $item)
	{
		$error = "";
		
		if ( !$id )
		{
			$error = str_replace( "<%TYPE%>", $this->lang->words['shopitem'], $this->lang->words['no_id'] );
		}	

		#no item found by that ID? error!
		if ( !$error && !$item )
		{
			$error = str_replace( "<%TYPE%>", $this->lang->words['shopitem'], $this->lang->words['none_found_show'] );		
		}
		
		#do I have permission to purchase this item? if not, error!
		if ( !$error && $item[ $this->abbreviation().'_use_perms'] && !$this->registry->permissions->check( 'open', $item ) )
		{
			$error = str_replace( "<%TYPE%>", $this->lang->words['shopitem'], $this->lang->words['no_perm_to_buy_it'] );		
		}

		#item isn't enabled currently? error!
		if ( !$error && !$item[ $this->abbreviation().'_on'] )
		{
			$error = str_replace( "<%ITEM%>", $this->lang->words['shopitem'], $this->lang->words['item_disabled'] );
		}		
		
		return $error;
	}

	/*
	* Final checks before adding item to cart (like checking quantity, type, etc)
	*/
	public function finalAdd2CartChecks($item, $number, $folioItem, $cartItem, $currentCartQuant, $typeType=false)
	{
		$error = "";
		
		#can't purchase a fraction of an item
		$number = intval($number);				
		
		#need at least 1!  error!
		if ( $number < 1 )
		{
			$error = $this->lang->words['need_at_least_one_item'];
		}
		
		#not enough inventory?  error!
		if ( !$error && $number + $currentCartQuant > $item['si_inventory'] )
		{
			$error = $this->lang->words['cart_item_quantity_over_item_inventory'];						
		}					

		#trying to own over item max?  error!
		if ( !$error && $item['si_limit'] && $number + $folioItem['p_amount'] + $currentCartQuant > $item['si_limit'] )
		{
			$error = $this->lang->words['cart_item_quantity_over_item_max'];						
		}
		
		#trying to buy too many today?  error!
		if ( !$error && $item['si_max_daily_buys'] && $number + $this->registry->mysql_ibEconomy->countNumItemPurchasesToday($this->memberData['member_id'], $item['si_id']) + $currentCartQuant > $item['si_max_daily_buys'] )
		{
			$error = $this->lang->words['cart_item_quantity_over_item_max_daily'];						
		}

		return $error;
	}
	
	/*
	* Return the message to be displayed upon successfully adding an item to cart
	*/
	public function add2CartRedirectMessage($checks, $item)
	{
		$redirectMessage = "";	
		
		$s_have 		 = ( $checks['number'] > 1 ) ? $this->lang->words['s_have'] : $this->lang->words['_has'];
		$redirectMessage = str_replace( "<%TYPE_NAME%>", ' <i>'.$item[ $this->abbreviation().'_title'].'</i>', $this->lang->words['shopitem_added_to_cart'] );
		$redirectMessage = str_replace( "<%NUMBER_TEXT%>", '<i>'.$this->registry->getClass('class_localization')->formatNumber( $checks['number'] ), $redirectMessage );			
		$redirectMessage = str_replace( "<%S_HAVE%>", $s_have, $redirectMessage );

		return $redirectMessage;
	}	
	
	/*
	* Returns the cost of a particular quantity of purchase
	*/
	public function tallyCost($row)
	{
		return $row['c_quantity'] * $row['og_cost'];
	}
	
	/*
	* Returns the fees of a particular quantity of purchase
	*/
	public function tallyFee($row)
	{
		return ($row['c_quantity'] * $row['og_cost']) * ($this->settings['eco_shop_tax']/100);
	}

	/*
	* Do any extra type-specific processing that needs to occur upon successful purchase
	*/
	public function postPurchaseProcessing($cartItem)
	{
		$dbUpdates = 'si_sold = si_sold + '.intval($cartItem['c_quantity']).', si_inventory = si_inventory - '.intval($cartItem['c_quantity']);

		$this->DB->buildAndFetch( array( 'update' => 'eco_shop_items', 'set' => $dbUpdates, 'where' => "si_id = ".$cartItem['c_type_id'] ) );
	}	
	
	/*
	* Build and return the html that will be displayed as the description of the cart item in the cart's rows
	*/
	public function rowHtmlDescription($row)
	{
		return "{$row['type']}";
	}	
	
	/*
	* Format the row to make things pretty for the templates (prettier than this function for sure)
	*/
	public function format($item, $extra)
	{	
		#init
		$item['og_cost']			= $item['si_cost'];
		
		#image
		$item['si_url_image']		= $this->registry->ecoclass->awardImageURL($item);
		$item['image_thumb_link']	= $this->registry->ecoclass->customItemImageHTML($item['si_image'], 'tag_blue.png', true, $item['si_url_image']);
		$item['image_link']			= $this->registry->ecoclass->customItemImageHTML($item['si_image'], 'tag_blue.png', false, $item['si_url_image']);	
		$item['image_link_popup']	= $this->registry->ecoclass->customItemImageHTML($item['si_image'], 'tag_blue.png', false, $item['si_url_image'], true);
		
		#perms
		$item['can_sell']			= ( !$item['si_use_perms'] || $this->registry->permissions->check( 'sell', $item ) ) ? TRUE : FALSE;
		$item['can_trade']			= ( !$item['si_use_perms'] || $this->registry->permissions->check( 'trade', $item ) ) ? TRUE : FALSE;
		
		#basic format 
		$item['image'] 				= 'tag_blue.png';
		$item['si_last_restock']	= ( $item['si_last_restock'] ) ? $item['si_last_restock'] : time();
		$item['si_restock_date'] 	= ( $item['si_restock_time'] && $item['si_restock'] ) ? $this->registry->getClass('class_localization')->getDate( $item['si_last_restock'] + $item['si_restock_time'] * 86400, 'JOINED' ) : $this->lang->words['n_a'];
		$item['si_inventory']		= $this->registry->getClass('class_localization')->formatNumber( $item['si_inventory'] );
		$item['si_cost']			= $this->registry->getClass('class_localization')->formatNumber( $this->registry->ecoclass->makeNumeric($item['si_cost'], false), $this->registry->ecoclass->decimal );
		$item['si_limit']			= ( $item['si_limit'] ) ? $this->registry->getClass('class_localization')->formatNumber( $item['si_limit'] ) : $this->lang->words['n_a'];
		$item['si_sold']			= $this->registry->getClass('class_localization')->formatNumber( $item['si_sold'] );

		#cart format
		$item['name'] 				= $item['si_title'];
		$item['type'] 				= $this->lang->words['item'];
		$item['l_type']				= $item['c_type'];
		$item['c_type']				= $this->lang->words['shop'];		
		$item['pre_amount_text']	= $this->lang->words['Quantity'];
		$item['amount'] 			= $this->registry->getClass('class_localization')->formatNumber( $item['c_quantity'] );
		$item['total'] 				= $this->registry->getClass('class_localization')->formatNumber( $this->registry->ecoclass->makeNumeric($item['c_quantity'] * $item['og_cost'], false), $this->registry->ecoclass->decimal );

		#even further (portfolio item)
		$item['type_type']			= $item['si_desc'];
		$item['value']				= ( $item['can_sell'] ) ? "<img title = '{$this->lang->words['you_can_sell']}' src = '{$this->settings['img_url']}/accept.png' />" : "<img title = '{$this->lang->words['you_can_not_sell']}' src = '{$this->settings['img_url']}/cross.png' />";						
		$item['value2']				= ( $item['can_trade'] ) ? "<img title = '{$this->lang->words['you_can_trade']}' src = '{$this->settings['img_url']}/accept.png' />" : "<img title = '{$this->lang->words['you_can_not_trade']}' src = '{$this->settings['img_url']}/cross.png' />";
		$item['total_value']		= $this->registry->getClass('class_localization')->formatNumber( $item['p_amount'] );
		$item['link_type_type']		= $this->lang->words['item'];
		
		#new stuff (1.5.2+)
		$item['cart_item_title']		= $this->registry->ecoclass->truncate($item['si_title'], 32);		
		$item['cart_item_more_info']	= $this->registry->ecoclass->truncate($item['si_desc']);
		$item['num_purchased']			= $item['si_sold'];
		$item['num_purchased_text']		= $this->lang->words['total_sold'];
		$item['cost'] 					= $item['si_cost'];
		$item['cost_text']				= $this->lang->words['price'];
		$item['right_fields_1']			= $item['si_inventory'];
		$item['right_fields_1_text']	= $this->lang->words['current_inventory'];
		$item['right_fields_1_image']	= 'folder_magnify.png';
		
		$item['cart_item_type']			= 'shopitem';
		$item['cart_item_id']			= $item['si_id'];
		$item['cart_item_bank_type']	= 'x';
		$item['cart_item_bank_Type']	= 'X';		
		$item['cart_item_tab']			= 'shop';
		
		#return it
		return $item;
	}
}

?>