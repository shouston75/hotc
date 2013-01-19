<?php

/**
 * (e32) ibEconomy
 * Cart Type : Lottery Ticket
 */

class ibEconomy_cart_type_lottery extends ibEconomy_cart_type_cart_item implements ibEconomy_cart_type
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
		return $this->on();
	}	
	
	/*
	* Return the name that items of this type are called
	*/
	public function name()
	{
		return $this->lang->words['lottery_ticket'];
	}
	
	/*
	* Default icon?
	*/
	public function icon()
	{
		return 'lotto.png';
	}
	
	/*
	* Ticket cost
	*/
	public function cost($lotto=false)
	{
		if ($lotto['l_tix_price'])
		{
			return $lotto['l_tix_price'];
		}
		else if ($this->cartItem['l_id'])
		{
			return $this->cartItem['l_tix_price'];
		}
		else if ($this->caches['ibEco_live_lotto']['l_id'])
		{
			return $this->caches['ibEco_live_lotto']['l_tix_price'];
		}
		else
		{
			return $this->settings['eco_lotto_ticket_price'];
		}
	}
	
	/*
	* Extra info needed for displaying purchased portfolio items
	* Not static!
	*/
	public function extraPortfolioItemInfo()
	{
		$extraInfo = array();

		$extraInfo['total_text'] 	= $this->lang->words['draw_date'];
		$extraInfo['cost'] 		 	= $this->cost();
		$extraInfo['total'] 	 	= $this->registry->getClass('class_localization')->getDate( $this->cartItem['p_rate_ends'], 'SHORT' );
		$extraInfo['total_bought']	= $extraInfo['total'];
		
		return $extraInfo;
	}
	
	/*
	* What do we call this item anyway?
	*/
	public function title($item)
	{
		return $this->lang->words['lottery_ticket'];
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
		return "g_eco_lottery";
	}
	
	/*
	* Number of decimal places to use for sums of these item types?
	*/
	public function decimalPlacesForSums()
	{
		return 0;
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
		return "eco_lotterys_on";
	}
	
	/*
	* Return the abbreviation used for this type, for example, the setting names use them
	*/
	public function abbreviation()
	{
		return "ltix";
	}

	/*
	* Return an item of this type from our cache
	*/
	public function grabItemByID($itemID)
	{
		return (intval($itemID) > 0) ? $this->registry->mysql_ibEconomy->queryLottos('single', $itemID) : $this->caches['ibEco_live_lotto'];
	}
	
	/*
	* Runs checks to make sure this item is allowed to be accessed currently
	*/
	public function canAccess($returnNeeded=false)
	{
		return $this->registry->ecoclass->canAccess('lotterys', $returnNeeded);
	}

	/*
	* Make sure we have an ID and/or an actual item, if not, return error message 
	*/
	public function gotItemCheck($id, $item)
	{
		$error = "";
		
		if ( !$id )
		{
			$error = str_replace( "<%TYPE%>", $this->lang->words['lottery'], $this->lang->words['no_id'] );
		}	

		#no item found by that ID? error!
		if ( !$error && !$item )
		{
			$error = str_replace( "<%TYPE%>", $this->lang->words['lottery'], $this->lang->words['none_found_show'] );		
		}
		
		#do I have permission to purchase this item? if not, error!
		if ( !$error && !$this->memberData['g_eco_lottery']  )
		{
			$error = str_replace( "<%TYPE%>", $this->lang->words['lottery'], $this->lang->words['no_perm_to_buy_it'] );		
		}

		#item isn't enabled currently? error!
		if ( !$error && !$this->settings['eco_lotterys_on'])
		{
			$error = str_replace( "<%ITEM%>", $this->lang->words['lottery'], $this->lang->words['item_disabled'] );
		}		
		
		return $error;
	}

	/*
	* Final checks before adding item to cart (like checking quantity, type, etc)
	*/
	public function finalAdd2CartChecks($item, $number, $folioItem, $cartItem, $currentCartQuant, $typeType=false)
	{
		$error = "";
		
		#tried to buy a negative number of tix?
		if ( !$error && !$number > 0 )
		{
			$error = $this->lang->words['positive_lotto_tix_only'];		
		}
		
		#ticket purchasing has closed for this lotto?
		if ( !$error && $item['l_draw_date'] < time() )
		{
			$error = $this->lang->words['lotto_ticket_sales_closed'];		
		}
		
		#already made a pick or 2?  need to count those too!
		$alreadyPickedTix = count($this->registry->mysql_ibEconomy->grabUsersPickedLottoTix($this->memberData['member_id'], $cartItem['c_type_id']));
		
		#tried to buy more tickets than group is allowed to?
		if ( !$error && $this->memberData['g_eco_lottery_tix'] && $number + $currentCartQuant + $folioItem['p_amount'] + $alreadyPickedTix > $this->memberData['g_eco_lottery_tix'] )
		{
			$error = str_replace( "<%MAX%>", $this->registry->getClass('class_localization')->formatNumber( $this->memberData['g_eco_lottery_tix'] ), $this->lang->words['over_max_lotto_tix'] );						
		}
		
		#tried to buy more tickets than default allowment?
		else if ( !$error && $this->memberData['g_eco_lottery_tix'] == 0 && $number + $currentCartQuant + $folioItem['p_amount'] + $alreadyPickedTix  > $this->settings['eco_lotto_def_max_tix'] )
		{
			$error = str_replace( "<%MAX%>", $this->registry->getClass('class_localization')->formatNumber( $this->settings['eco_lotto_def_max_tix'] ), $this->lang->words['over_max_lotto_tix'] );
		}

		return $error;
	}
	
	/*
	* Return the message to be displayed upon successfully adding an item to cart
	*/
	public function add2CartRedirectMessage($checks, $item)
	{
		$redirectMessage = "";
		
		$messageText 	  =  $this->registry->getClass('class_localization')->formatNumber( $checks['number'] );
		$redirectMessage = str_replace( "<%NUMBER%>", $messageText, $this->lang->words['lotto_added_to_cart'] );
		
		return $redirectMessage;
	}	
	
	/*
	* Returns the cost of a particular quantity of purchase
	*/
	public function tallyCost($row)
	{
		return $row['c_quantity'] * $this->cost();
	}
	
	/*
	* Returns the fees of a particular quantity of purchase
	*/
	public function tallyFee($row)
	{
		return 0;
	}	
	
	/*
	* After sucessful purchase of this item, need to go some place special?  (first one that return something not false will win)
	*/
	public function redirectToPage($cartItem)
	{
		return "app=ibEconomy&amp;tab=cash&amp;area=single&amp;type=lottery&amp;id=".$cartItem['c_type_id'];
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
		$itemArrayFields['p_rate_ends']	 	= $item['l_draw_date'];
		$itemArrayFields['p_last_hit']		= 0;
		$itemArrayFields['cash_advance']	= 0;
		
		return $itemArrayFields;
	}
	
	/*
	* Build and return the html that will be displayed as the description of the cart item in the cart's rows
	*/
	public function rowHtmlDescription($row)
	{
		return "{$row['type']}";
	}
	
	/*
	* Return the name of the html template which this cart type uses to show more info when shopping/investing/etc
	*/
	public function htmlTemplate()
	{	
		return 'lottery_row';
	}	
	
	/*
	* Format the row to make things pretty for the templates (prettier than this function for sure)
	*/
	public function format($lotto, $single)
	{	
		#from cart?
		$lotto['l_id'] = ($lotto['l_id'] > 0) ? $lotto['l_id'] : $lotto['c_type_id'];
		
		#image
		$lotto['image_thumb_link'] 	= $this->registry->ecoclass->customItemImageHTML($lotto['image'], 'lotto.png', true);
		$lotto['image_link_popup']	= $this->registry->ecoclass->customItemImageHTML($lotto['image'], 'lotto.png', true, '', true);
		
		#name
		$lotto['name'] 	= $this->lang->words['lottery'];
		
		#how many has everyone bought?
		$lotto['l_tix_purchased'] 	= $this->registry->getClass('class_localization')->formatNumber( $lotto['l_tix_purchased'] );
	
		#ticket cost
		$lotto['l_tix_price'] 		= $this->cost($lotto);

		#various stuff (cart for example)
		$lotto['c_type']			= '';//$this->registry->getClass('class_localization')->formatNumber( $lotto['c_quantity'] )'#'.$lotto['c_type_id'];
		$lotto['type']				= $this->lang->words['ticket'];//$lotto['c_type_id'];
		$lotto['pre_amount_text']	= $this->lang->words['tickets'];
		$lotto['amount']			= $this->registry->getClass('class_localization')->formatNumber( $lotto['c_quantity'] );;
		$lotto['total'] 			= $this->registry->getClass('class_localization')->formatNumber( $this->registry->ecoclass->makeNumeric($lotto['c_quantity'] * $lotto['l_tix_price'], false), $this->registry->ecoclass->decimal );
		$lotto['l_type']			= 'lottery';
		
		#can we still buy tickets?
		$lotto['tix_still_4_sale']	= ( $lotto['l_draw_date'] > time() + 60 ) ? TRUE : FALSE;
		
		#viewer's number of tickets
		#in portfolio
		if ($this->memberData['member_id'] > 0)
		{
			if ( ! $this->settings['eco_general_cache_portfolio'] )
			{
				$myPortLottoRow 					= $this->registry->mysql_ibEconomy->grabPortfolioItemsByType( 'lottery', $this->memberData['member_id'], false, '', $lotto['l_id'] );
				$myPortLottoRow 					= $myPortLottoRow[0];
			}
			else
			{
				$myPortLottoRow 					= $this->caches['ibEco_portfolios'][ $this->memberData['member_id'] ]['lotterys_'][ $lotto['l_id'] ];
			}
			
			#users tickets which s/he has already picked numbers for (only for current lottery or if on a single lottery page
			if ($single || $lotto['l_id'] == $this->caches['ibEco_live_lotto']['l_id'] || $lotto['l_draw_date'] > time() + 60 )
			{
				$lotto['myPickedTickets'] 				= $this->registry->mysql_ibEconomy->grabUsersPickedLottoTix($this->memberData['member_id'], $lotto['l_id']);
				$lotto['your_num_picked'] 				= count($lotto['myPickedTickets']);
				$lotto['your_num_tix'] 					= $myPortLottoRow['p_amount'];	
				$lotto['your_num_total_tix'] 			= $myPortLottoRow['p_amount'] + $lotto['your_num_picked'];		
				$lotto['your_num_total_tix_formatted'] 	= $this->registry->getClass('class_localization')->formatNumber($lotto['your_num_total_tix']);					
			}
			else
			{
				$lotto['your_num_total_tix'] 			= $this->lang->words['old'];
			}			
		}
		else
		{
			$lotto['your_num_tix'] 					= 0;	
			$lotto['your_num_picked'] 				= 0;		
			$lotto['your_num_total_tix'] 			= 0;		
			$lotto['your_num_total_tix_formatted'] 	= 0;			
		}

		#dates and descriptions
		$lotto['unparsed_draw_date']			= $lotto['l_draw_date'];
		$lotto['l_draw_date'] 					= $this->registry->getClass('class_localization')->getDate( $lotto['l_draw_date'], 'SHORT' );
		$lotto['l_draw_date_no_time'] 			= $this->registry->getClass('class_localization')->getDate( $lotto['unparsed_draw_date'], 'JOINED' );
		$lotto['l_start_date'] 					= $this->registry->getClass('class_localization')->getDate( $lotto['l_start_date'], 'SHORT' );
		
		if ($lotto['tix_still_4_sale'])
		{
			$this->lang->words['lotto_description'] = str_replace("<%DRAW_DATE%>", $lotto['l_draw_date'], $this->lang->words['lotto_description']);
			$this->lang->words['lotto_pick_remind'] = str_replace("<%DRAW_DATE%>", $lotto['l_draw_date'], $this->lang->words['lotto_pick_remind']);
		}
		else
		{
			$this->lang->words['lotto_description'] = $this->lang->words['lotto_tickets_closed'];
			$this->lang->words['lotto_pick_remind'] = str_replace("<%DRAW_DATE%>", $lotto['l_draw_date'], $this->lang->words['lotto_tickets_closed_on']);		
		}
		
		#winner?
		if ( $lotto['l_winner_id'] > 0 )
		{
			$winner = IPSMember::load( $lotto['l_winner_id'], 'extendedProfile' );
			$lotto['winnerMemData'] = $winner;
			$lotto['winnings'] 		= $this->registry->getClass('class_localization')->formatNumber($lotto['l_final_pot_size'], $this->registry->ecoclass->decimal );
			
			if ($this->settings['eco_plugin_ppns_on'] && ($winner['ibEco_plugin_ppns_prefix'] || $winner['ibEco_plugin_ppns_suffix'] || $this->settings['eco_plugin_ppns_use_gf']))
			{
				$winner['formatted_name'] 	= IPSMember::makeNameFormatted( $winner['members_display_name'], $winner['member_group_id'], $winner['ibEco_plugin_ppns_prefix'], $winner['ibEco_plugin_ppns_suffix'] ); 
			}
			else
			{
				$winner['formatted_name'] 	= $winner['members_display_name']; 
			}
			
			$this->lang->words['view_eco_profile'] = str_replace( "<%IBECO_NAME%>"  , $this->settings['eco_general_name'], $this->lang->words['view_eco_profile']);
			$lotto['winner_link'] = "<a style='font-weight:bolder' href='{$this->settings['base_url']}app=ibEconomy&tab=global&area=member&id={$winner['member_id']}' title='{$this->lang->words['view_eco_profile']}'>{$winner['formatted_name']}</a>";	
		}
		else if ( $lotto['l_winners'] != "" )
		{
			if ($single)
			{
				$winners = explode(",", $lotto['l_winners']);
				$numWinners = count($winners);
				
				$lotto['winner_link'] = "<span title='";
						
				$winnersIncluded = 0;
				foreach($winners AS $winner)
				{
					$thisWinner = IPSMember::load( $winner, 'core' );
					$lotto['winner_link'] .= $thisWinner['members_display_name'];
					$winnersIncluded++;
					
					if ($winnersIncluded != $numWinners)
					{
						$lotto['winner_link'] .= ", ";
					}
				}
						
				$lotto['winner_link'] .= "'>{$this->lang->words['multiple_winners']}</span>";			
			}
			else
			{
				$lotto['winner_link'] = $this->lang->words['multiple_winners'];
			}
		}
		else if ( $lotto['l_winner_id'] == -1 )
		{
			$lotto['winner_link'] = $this->lang->words['none'];
		}		
		else
		{
			$lotto['winner_link'] = $this->lang->words['n_a'];
		}
		
		#create data for viewer's tickets (if they own any)
		if ($lotto['your_num_total_tix'] > 0)
		{
			#column headers
			for ($balls = 1; $balls <= $lotto['l_num_balls']; $balls++)
			{
				$lottoBalls[ $balls ]['number'] = $balls;
				$lottoBalls[ $balls ]['header'] = str_replace("<%NUM%>", $balls, $this->lang->words['lotto_ball_num']);
			}
									
			//$this->registry->ecoclass->showVars($lotto['your_num_picked']);
			for ($tix = 1; $tix <= $lotto['your_num_total_tix']; $tix++)
			{
				$myLottoTickets[ $tix ]['ticketNum'] = str_replace("<%NUM%>", $tix, $this->lang->words['lotto_ticket_num']);
				$myLottoTickets[ $tix ]['rowStyle']  = ($tix % 2 == 0) ? 'altrow' : '';
				
				$alreadyPicked = ($tix <= $lotto['your_num_picked']) ? true : false;
				
				for ($balls = 1; $balls <= $lotto['l_num_balls']; $balls++)
				{
					$myLottoTickets[ $tix ]['ballDD_'.$balls] = $this->createBallNumberDropdown($lotto, $lotto['myPickedTickets'][$tix], $tix-$lotto['your_num_picked'], $balls, $alreadyPicked);
				}
			}
			
			$lotto['lottoBalls'] 		= $lottoBalls;
			$lotto['myLottoTickets'] 	= $myLottoTickets;
		}		
		
		#winning numbers?
		$lotto['winning_numbers'] 		= ( $lotto['l_winning_nums'] != "" ) ? $lotto['l_winning_nums'] : $this->lang->words['n_a'];
		
		#new stuff (1.5.2+)
		$lotto['cart_item_title']		= $this->registry->ecoclass->truncate($lotto['l_draw_date'], 32);
		$lotto['cart_item_more_info']	= $this->lang->words['pot_size'].": ".$this->settings['eco_general_cursymb'].$this->registry->getClass('class_localization')->formatNumber($lotto['l_final_pot_size'], $this->registry->ecoclass->decimal )."<br />".$this->lang->words['your_tickets'].": ".$lotto['your_num_total_tix'];		
		$lotto['cost'] 					= $this->registry->getClass('class_localization')->formatNumber( $lotto['l_tix_price'], $this->registry->ecoclass->decimal );
		$lotto['cost_text']				= $this->lang->words['ticket_cost'];
		$lotto['num_purchased']			= $lotto['l_tix_purchased'];
		$lotto['num_purchased_text']	= $this->lang->words['total_sold'];
		$lotto['right_fields_1']		= $lotto['winner_link'];
		$lotto['right_fields_1_text']	= $this->lang->words['winner'];
		$lotto['right_fields_1_image']	= 'ruby_key.png';
		$lotto['l_final_pot_size']		= $this->registry->getClass('class_localization')->formatNumber( $lotto['l_final_pot_size'], $this->registry->ecoclass->decimal );
		$lotto['cart_item_type']		= 'lottery';
		$lotto['cart_item_id']			= $lotto['l_id'];
		$lotto['cart_item_bank_type']	= 'x';
		$lotto['cart_item_bank_Type']	= 'X';		
		$lotto['cart_item_tab']			= 'cash';
				

		return $lotto;
	}
	
	/**
	 * Create a dropdown to select your number pick for a single lotto ball
	 */
	public function createBallNumberDropdown($lotto, $myTicket, $ticketNumber, $ballNumber, $alreadyPicked)
	{
		$ballDropdown = "";

		if ($alreadyPicked)
		{
			$numbers = explode(",", $myTicket['ltix_numbers']);
			$ballDropdown =  $numbers[ $ballNumber-1 ];
		}
		else if ($lotto['unparsed_draw_date'] > time() + 60)
		{	
			#init
			$selectName 		= $ticketNumber.' '.$ballNumber;
			
			$ballDropdown = "<select name='{$selectName}' class='input_select'>
				<optgroup label='{$this->lang->words['your_pick']}...'>";
			
			for ($i = 1; $i <= $lotto['l_top_num']; $i++)
			{
				if ($i == 1)
				{
					$ballDropdown .= "<option value='' selected='SELECTED'> </option>";
				}
				
				$ballDropdown .= "<option value='{$i}' {$selected}>{$i}</option>";
			}

			$ballDropdown .= "</select>";
		}
		else
		{
			$ballDropdown = $this->lang->words['will_be_refunded'];
		}
		
		return $ballDropdown;
	}	
}

?>