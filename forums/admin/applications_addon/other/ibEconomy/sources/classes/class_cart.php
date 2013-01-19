<?php

/**
 * (e32) ibEconomy
 * Shopping Cart Class
 * @ Buy Tab (cart + checkout)
 */

if ( ! defined( 'IN_IPB' ) )
{
	print "<h1>Incorrect access</h1>You cannot access this file directly. If you have recently upgraded, make sure you upgraded all the relevant files.";
	exit();
}

class class_cart
{

	private $showPage	= "";
	
	protected $registry;
	protected $DB;
	protected $settings;
	protected $request;
	protected $lang;
	protected $member;
	protected $cache;
	protected $caches;

	/**
	 * Class entry point
	 */
	public function __construct( ipsRegistry $registry )
	{
        $this->registry     =  ipsRegistry::instance();
        $this->DB           =  $this->registry->DB();
        $this->settings     =& $this->registry->fetchSettings();
        $this->request      =& $this->registry->fetchRequest();
        $this->lang         =  $this->registry->getClass('class_localization');
        $this->member       =  $this->registry->member();
        $this->memberData  	=& $this->registry->member()->fetchMemberData();
        $this->cache        =  $this->registry->cache();
        $this->caches       =& $this->registry->cache()->fetchCaches();			
	}

	/**
	 * Out MASTER cart function
	 */
	public function cart($page='')
	{
		#permission and enabled?
		$this->registry->ecoclass->canAccess('cart', false);
		
		#init vars
		$item_rows 			= "";
		$paymentOpts 		= "";
		
		$totalCost			= 0;
		$totalFees			= 0;
		$arrayIndex 		= -1;
		
		$payment_options 	= array();
		$totals 			= array();
		$shoppingCartItems 	= array();
		$paymentTypes	 	= array();	
		$paymentRow		 	= array();
		$purchasedThese		= array();	
		$pType				= array();
		$myCCsAndBanks		= array();
		
		#query for all shopping cart items we need to show
		$this->registry->mysql_ibEconomy->grabShoppingCart();
		
		#no items in your cart, son!
		if ( ! $this->DB->getTotalRows() )
		{
			$error = str_replace( "<%TYPE%>", $this->lang->words['items'], $this->lang->words['none_to_show'] );
			$this->showPage = $this->registry->output->getTemplate('ibEconomy')->error($error);

			return $this->showPage;		
		}
		
		#make item vars easy on the eyes and fill the rows
		while ( $row = $this->DB->fetch() )
		{
			#loan hotfix
			$type		= ( $row['c_type_class'] != 'loan' ) ? $row['c_type'] : $row['c_type_class'];
			
			#grab this cart types class object
			$cartItemType = $this->registry->ecoclass->grabCartTypeClass($type);
			
			#last minute checks
			if ( !$cartItemType->itemOn($row))
			{
				continue;
			}
			
			#descriptives				
			$row = $cartItemType->format($row, $row['c_type_class']);
			
			#title and description (new to 1.6)
			$row['title_line'] = $cartItemType->rowHtmlTitle($row);
			$row['desc_line']  = $cartItemType->rowHtmlDescription($row);
			
			#totals
			$totalCost	+= $cartItemType->tallyCost($row);
			$totalFees	+= $cartItemType->tallyFee($row);
			
			#compile master cart item array for later
			$shoppingCartItems[]		= $row;
			
			#tally up the html rows
			$item_rows .= $this->registry->output->getTemplate('ibEconomy')->cart_row($row);
		}
		
		#calculate total cost and fees
		$totalCost	= $this->registry->ecoclass->makeNumeric($totalCost, false);
		$totalFees	= $this->registry->ecoclass->makeNumeric($totalFees, false);
		$grand_total = $totalCost + $totalFees;
		
		#format totals
		$totals['sub']		= $this->registry->getClass('class_localization')->formatNumber( $totalCost, $this->registry->ecoclass->decimal );		
		$totals['fees']		= $this->registry->getClass('class_localization')->formatNumber( $totalFees, $this->registry->ecoclass->decimal );	
		$totals['grand']	= $this->registry->getClass('class_localization')->formatNumber( $grand_total, $this->registry->ecoclass->decimal );
		
		#portfolio cache enabled?
		if (!$this->settings['eco_general_cache_portfolio'] )
		{
			$myCCsAndBanks = $this->registry->mysql_ibEconomy->grabMemBanksAndCCs( $this->memberData['member_id'] );
		}
		
		switch ( $page )
		{
			case 'checkOut':

				#grab payment  options, start with points
				if ( $this->memberData[ $this->settings['eco_general_pts_field'] ] > 0 )
				{
					$pType['id']		= 'points';		
					$pType['name'] 		= $this->settings['eco_general_currency'].' ('. $this->settings['eco_general_cursymb'].$this->registry->getClass('class_localization')->formatNumber( $this->memberData[ $this->settings['eco_general_pts_field'] ] ) .')';

					$payment_options[] 	= array('id' => $pType['id'], 'name' => $pType['name']);
				}
				
				#cont grab payment opts, now my portfolio  CCs and Banks
				#portfolio cache enabled?
				if ( $this->caches['ibEco_portfolios'] && $this->settings['eco_general_cache_portfolio'] )
				{
					#checking accounts
					if ( is_array( $this->caches['ibEco_portfolios'][ $this->memberData['member_id'] ]['banks_checking'] ) )
					{
						foreach ( $this->caches['ibEco_portfolios'][ $this->memberData['member_id'] ]['banks_checking'] AS $paymentType )
						{
							$pType['id'] 	= $paymentType['p_id'];
							$pType['title'] = $this->caches['ibEco_banks'][ $paymentType['p_type_id'] ]['b_title'];
							$pType['name'] 	= $paymentType['b_title'].' - '.$this->lang->words['checking'].' ('.$this->settings['eco_general_cursymb'].$this->registry->getClass('class_localization')->formatNumber( $paymentType['p_amount'], $this->registry->ecoclass->decimal ).')';
						
							$payment_options[] 		= $pType;
						}
					}
					
					#credit-cards
					if ( is_array( $this->caches['ibEco_portfolios'][ $this->memberData['member_id'] ]['ccs_'] ) )
					{					
						foreach ( $this->caches['ibEco_portfolios'][ $this->memberData['member_id'] ]['ccs_'] AS $paymentType )
						{
							if ( $paymentType['p_amount'] >= $paymentType['p_max'] )
							{
								continue;
							}	
							
							$pType['id'] 	= $paymentType['p_id'];
							$pType['title'] = $this->caches['ibEco_ccs'][ $paymentType['p_type_id'] ]['cc_title'];
							$pType['name'] 	= $paymentType['cc_title'].' ('.$this->settings['eco_general_cursymb'].$this->registry->getClass('class_localization')->formatNumber( $paymentType['p_max'] - $paymentType['p_amount'], $this->registry->ecoclass->decimal ).')';
						
							$payment_options[] 		= $pType;
						}
					}
				}
				else
				{					
					//$this->registry->mysql_ibEconomy->grabPortfolioItems( $this->memberData['member_id'], 'all', 'cache' );
					foreach ( $myCCsAndBanks AS $row )
					{
						if ( $row['p_type'] == 'bank' && $row['p_type_class'] == 'checking' )
						{
							$pType['id'] 	= $row['p_id'];
							$pType['title'] = $this->caches['ibEco_banks'][ $row['p_type_id'] ]['b_title'];
							$pType['name'] 	= $pType['title'].' - '.$this->lang->words['checking'].' ('.$this->settings['eco_general_cursymb'].$this->registry->getClass('class_localization')->formatNumber( $row['p_amount'], $this->registry->ecoclass->decimal ).')';
						
							$payment_options[] 		= $pType;
						}
						else if ( $row['p_type'] == 'cc' && $row['p_amount'] < $row['p_max'] )
						{
							$pType['id'] 	= $row['p_id'];
							$pType['title'] = $this->caches['ibEco_ccs'][ $row['p_type_id'] ]['cc_title'];
							$pType['name'] 	= $pType['title'].' ('.$this->settings['eco_general_cursymb'].$this->registry->getClass('class_localization')->formatNumber( $row['p_max'] - $row['p_amount'], $this->registry->ecoclass->decimal ).')';
						
							$payment_options[] 		= $pType;
						}
					}
				}
				
				#no points, ccs, or banks?
				if ( !count($payment_options) )
				{
					$this->registry->output->showError( $this->lang->words['no_pts_to_buy'] );	
				}
				
				#make payment dropdown
				$payment_drop .= "<select id='payment_#{qid}_#{cid}' name='choice_#{cid}' class='input_text' value='#{choice}'>";
				$payment_drop .= "<optgroup label='{$this->lang->words['select_payment_type']}...'>";
				
				foreach ($payment_options as $paytype )
				{
					$payment_drop .= "<option value ='{$paytype['id']}'>{$paytype['name']}</option>";
				}
				
				$payment_drop .= "</select>";
				
				#payment options dropdown
				$paymentOpts .= $this->registry->output->getTemplate('ibEconomy')->checkOut_row($payment_drop);
				
				#throw payment options to checkout wrapper
				$shopping_cart 	= $this->registry->output->getTemplate('ibEconomy')->checkOut_wrapper($paymentOpts, $totals);
				$this->showPage = $shopping_cart;				
				
			break;
			
			case 'purchase':
				
				#Quick Buy!
				#new to 1.0.3+
				if ( $this->request['one_click_buy'] )
				{
					$paymentRow['id'] 	= 'points';
					$paymentRow['amt']	= $grand_total;
					
					$paymentTypes[] 	= $paymentRow;
				}
				
				#no payment types entered...
				else if ( !$this->request['payment_ids'] && !$this->request['choice_#{cid}'] )
				{
					$this->registry->output->showError( $this->lang->words['no_payment_method'] );
				}			
				
				#no js, just paid with 1 method and 1 amount
				else if ( !$this->request['payment_ids'] )
				{
					$paymentRow['id'] 	= $this->request['choice_#{cid}'];
					$paymentRow['amt']	= $this->request['payment_amt'];
					
					$paymentTypes[] 	= $paymentRow;				
				}
				
				#perpare multuple javascripted payments
				else
				{
					foreach ( $this->request['payment_ids'] AS $id )
					{
						$paymentRow['id'] 	= $this->request['choice_'.$id];
						$paymentRow['amt'] 	= $this->request['amount_'.$id];

						$paymentTypes[] 	= $paymentRow;
					}					
				}

				#weed out empty payment rows and make sure total amt inputted is spot on
				foreach ( $paymentTypes AS $pType )
				{	
					#what num within loop?
					$arrayIndex++;

					#portfolio cache enabled?
					if ( $this->settings['eco_general_cache_portfolio'] )
					{
						$portfolioRowUsed = $this->caches['ibEco_portfolios'][ $this->memberData['member_id'] ][ $pType['id'] ];
					}
					else if ( $pType['id'] != 'points' )
					{
						$portfolioRowUsed = $myCCsAndBanks[ $pType['id'] ];
					}

					$payment_method		= ( $portfolioRowUsed['p_type'] ) ? $portfolioRowUsed['p_type'] : 'points';
					
					$payment_amount		= $this->registry->ecoclass->makeNumeric( $pType['amt'], true );

					#skip it if no amount was entered
					if ( !$payment_amount > 0 )
					{
						unset($paymentTypes[ $arrayIndex ]);
						continue;
					}
					
					#skip it if payment type isn't proper
					if ( !in_array( $payment_method, array('points', 'bank', 'cc' ) ) )
					{
						unset($paymentTypes[ $arrayIndex ]);
						continue;
					}

					#error if tried to spend more points than that had on hand
					if ( $payment_method == 'points' )
					{
						$pointsSpent = $pointsSpent + $payment_amount;
						
						if ( $pointsSpent > $this->memberData[ $this->settings['eco_general_pts_field'] ] )
						{
							$this->registry->output->showError( str_replace ( "<%POINTS_NAME%>", $this->settings['eco_general_currency'], $this->lang->words['not_enough_points'] ) );
						}
					}
					
					#error if tried to use savings account
					if ( $portfolioRowUsed['p_type_class'] == 'savings' )
					{
						$this->registry->output->showError( $this->lang->words['cant_buy_with_savings'] );
					}
					
					#error if tried to input more than in the checking account
					if ( $portfolioRowUsed['p_type'] == 'bank' && $portfolioRowUsed['p_amount'] < $payment_amount )
					{
						$this->registry->output->showError( str_replace ( "<%BANK_NAME%>", $this->caches['ibEco_banks'][ $portfolioRowUsed['p_type_id'] ]['b_title'], $this->lang->words['not_enough_in_bank'] ) );	
					}
					
					#error if tried to overdraft and cc doesn't allow overdrafts or amt was over od limit
					if ( $portfolioRowUsed['p_type'] == 'cc' )
					{
						$odAllowed = ( $this->caches['ibEco_ccs'][ $portfolioRowUsed['p_type_id'] ]['cc_allow_od'] ) ? $this->caches['ibEco_ccs'][ $portfolioRowUsed['p_type_id'] ]['cc_max_od'] : '';
						if ( $portfolioRowUsed['p_amount'] + $payment_amount > $portfolioRowUsed['p_max'] + $odAllowed  )
						{
							$this->registry->output->showError( str_replace ( "<%CC_NAME%>", $this->caches['ibEco_ccs'][ $portfolioRowUsed['p_type_id'] ]['cc_title'], $this->lang->words['not_enough_cc_bal'] ) );
							$this->registry->output->showError( $this->lang->words['not_enough_cc_bal'] );	
						}
					}
					
					#cashier'in is a tuf job
					$paymentTotal		= $paymentTotal + $payment_amount;
				}
				
				#paid too much
				if ( $paymentTotal > $grand_total + 0.01 )
				{
					$redirect_message = str_replace( "<%GRAND_TOTAL%>", $this->registry->getClass('class_localization')->formatNumber( $grand_total, $this->registry->ecoclass->decimal ), $this->lang->words['spent_too_much'] );
					$redirect_message = str_replace( "<%PAY_AMOUNT%>", $this->registry->getClass('class_localization')->formatNumber( $paymentTotal, $this->registry->ecoclass->decimal ), $redirect_message );
					
					$this->registry->output->showError( $redirect_message );	
				}
				
				#paid too little
				if ( $paymentTotal + 0.01 < $grand_total )
				{
					$redirect_message = str_replace( "<%GRAND_TOTAL%>", $this->registry->getClass('class_localization')->formatNumber( $grand_total, $this->registry->ecoclass->decimal ), $this->lang->words['spent_too_little'] );
					$redirect_message = str_replace( "<%PAY_AMOUNT%>", $this->registry->getClass('class_localization')->formatNumber( $paymentTotal, $this->registry->ecoclass->decimal ), $redirect_message );
					
					$this->registry->output->showError( $redirect_message );	
				}
				
				#made it through the checks, make sure we recache later cause we're buying something!
				{
					$purchasedThese[] = 'portfolios';
				}
				
				#process each payment source 1 by 1 (charge em!)
				foreach ( $paymentTypes AS $pType )
				{
					#portfolio cache enabled?
					if ( $this->settings['eco_general_cache_portfolio'] )
					{
						$portfolioRowUsed 	= $this->caches['ibEco_portfolios'][ $this->memberData['member_id'] ][ $pType['id'] ];
					}
					else if ( $pType['id'] != 'points' )
					{
						$portfolioRowUsed = $myCCsAndBanks[ $pType['id'] ];
					}

					$payment_method		= ( $portfolioRowUsed['p_type'] ) ? $portfolioRowUsed['p_type'] : 'points';
					
					$payment_amount		= $this->registry->ecoclass->makeNumeric($pType['amt'], true);
				
					#paying the old fashioned route, eh old chap?, skip it and remove all points after loop
					if ( $payment_method == 'points' )
					{
						continue;
					}
					else
					{
						if ( $payment_method == 'bank' )
						{	
							#portfolio cache enabled?
							if ( $this->settings['eco_general_cache_portfolio'] )
							{
								$adjustedFunds = $this->registry->ecoclass->makeNumeric($this->caches['ibEco_portfolios'][ $this->memberData['member_id'] ][ $pType['id'] ]['p_amount'] - $payment_amount, false);
							}
							else if ( $pType['id'] != 'points' )
							{
								$thisBank = $myCCsAndBanks[ $pType['id'] ];
								$adjustedFunds = $this->registry->ecoclass->makeNumeric($thisBank['p_amount'] - $payment_amount, false);
							}
						}
						else if ( $payment_method == 'cc' )
						{
							if ( $portfolioRowUsed['p_amount'] + $payment_amount > $portfolioRowUsed['p_max'] )
							{							
								$grand_total = $grand_total + $this->caches['ibEco_ccs'][ $portfolioRowUsed['p_type_id'] ]['cc_od_pnlty'];
							}
							
							#portfolio cache enabled?
							if ( $this->settings['eco_general_cache_portfolio'] )
							{
								$adjustedFunds = $this->registry->ecoclass->makeNumeric($this->caches['ibEco_portfolios'][ $this->memberData['member_id'] ][ $pType['id'] ]['p_amount'] + $payment_amount, false);
							}
							else if ( $pType['id'] != 'points' )
							{
								$thisCC = $myCCsAndBanks[ $pType['id'] ];
								$adjustedFunds = $this->registry->ecoclass->makeNumeric($thisCC['p_amount'] + $payment_amount, false);
							}
						}

						#Adjust portfolio bank  accordingly
						$this->DB->update( 'eco_portfolio', array( 'p_amount' => $adjustedFunds ), 'p_id = ' . $pType['id'] );
					}					
				}
				
				$lottoTixFor	= 0;
				$redirectToPage = "";
				$cashAdvance	= 0;
				
				#add items to bag
				foreach ( $shoppingCartItems AS $cartItem )
				{
					#init
					$type			= $cartItem['l_type'];
					$type			= ( $banktype != 'loan' ) ? $type : $banktype;
					
					#grab this cart types class object
					$cartItemType 	= $this->registry->ecoclass->grabCartTypeClass($type);
					$daItem			= $cartItemType->grabItemByID($cartItem['c_type_id']);
					
					$actualType		= ( $cartItem['c_type_class'] != 'loan' ) ? $type : 'loan';
					$lottoTixFor 	= ($type == 'lottery') ? $cartItem['c_type_id'] : $lottoTixFor;
					
					#gonna need to recache later?
					if ( !in_array( $type, $purchasedThese ) )
					{
						$purchasedThese[] = $type.'s';
					}
					
					#add the items to a member's portfolio stash
					$cashAdvance = $this->registry->ecoclass->addItem2Portfolio( $cartItem, $type, $daItem, $cashAdvance, $this->memberData );

					#add log
					$this->registry->ecoclass->addLog( 'purchase', $cartItem['c_quantity'], $cartItem['c_type_id'], $actualType, $daItem[ $cartItemType->abbreviation().'_title'] );
					
					#finish up any special processing the specific types need
					$cartItemType->postPurchaseProcessing($cartItem);
					
					#need to set a special page to redirect to after purchase?
					if (!$redirectToPage && $specialRedirect = $cartItemType->redirectToPage($cartItem))
					{
						$redirectToPage = $specialRedirect;
					}
					
					#number purchased incrementation
					$itemsPurchased += ($cartItemType->countNumForTotalPurchased()) ? $cartItem['c_quantity'] : 1;
				}
				
				#good a place as any to hand out any cash-advance requested with new credit-card(s) and charge points for purchase
				if ( $cashAdvance or $pointsSpent )
				{
					$bothPtTotals = $this->registry->ecoclass->makeNumeric($cashAdvance - $pointsSpent, false);
				
					$this->registry->mysql_ibEconomy->updateMemberPts($this->memberData['member_id'], $bothPtTotals, '+', true);
				}				
				
				#update necessary caches
				$this->registry->ecoclass->acm( $purchasedThese );

				#remove all my cart items
				$this->DB->delete( 'eco_cart', 'c_member_id = ' . $this->memberData['member_id'] );

				#redirect on success!
				$s = ( $itemsPurchased > 1 ) ? 's' : '';
				$redirect_message = str_replace( "<%GRAND_TOTAL%>", $this->registry->getClass('class_localization')->formatNumber( $grand_total, $this->registry->ecoclass->decimal ), $this->lang->words['purchase_successful'] );
				$redirect_message = str_replace( "<%S%>", $s, $redirect_message );
				$redirect_message = str_replace( "<%NUM_ITEMS%>", $itemsPurchased, $redirect_message );
				$redirect_message = str_replace( "<%POINTS_NAME%>", $this->settings['eco_general_currency'], $redirect_message );
				$redirect_message = str_replace( "<%TYPE%>", $this->lang->words[ $payment_method ], $redirect_message );
				
				#special redirect (added for 1.6)
				if ($redirectToPage)
				{
					$redirectToPage = (strpos($redirectToPage, "http") === 0) ? $redirectToPage : $this->settings['base_url'] . $redirectToPage;
				}
				else
				{
					$redirectToPage = $this->settings['base_url'] . "app=ibEconomy&amp;tab=me";
				}

				$this->registry->output->redirectScreen( $redirect_message, $redirectToPage );			

			break;			
			
			default:
			
				#throw item rows to cart  wrapper
				$shopping_cart 	= $this->registry->output->getTemplate('ibEconomy')->cart_wrapper($item_rows, $totals);
				$this->showPage = $shopping_cart;			

			break;
		}
		
		return $this->showPage;
	}
	
	/**
	 * Update our cart
	 */	
	public function updateCart()
	{	
		#permission and enabled?
		$this->registry->ecoclass->canAccess('cart', false);
		
		if ( !$this->request['update_me'] )
		{
			$this->registry->output->showError( $this->lang->words['none_to_update'] );
		}
		
		#init error, loop will rid the error if needed
		$error = $this->lang->words['no_new_quantities'];
		
		foreach ( $this->request['update_me'] AS $cartItemID )
		{		
			if ( $this->request['method'] == 'delete' )
			{	
				#remove error as we are actually doing something
				$error = '';
				
				#tally total deleted
				$numDel++;
				
				#just... get rid of it, I never want to see it AGAIN!
				$this->DB->delete( 'eco_cart', 'c_id=' . $cartItemID );
			}
			else
			{
				#process form inputs
				$type 		= $this->request['item_type_'.$cartItemID ];
				$oldNum 	= $this->request['old_amount_'.$cartItemID ];
				$newNum 	= $this->request['new_amount_'.$cartItemID ];
				$banktype 	= $this->request['bank_type_'.$cartItemID ];			
				
				#derive item ID
				$itemID		= $this->request['item_id_'.$cartItemID ];

				#loan hotfix
				$type		= ( $banktype != 'loan' ) ? $type : $banktype;
				
				#grab this cart types class object
				$cartItemType = $this->registry->ecoclass->grabCartTypeClass($type);
				
				$theItem	= $cartItemType->grabItemByID($itemID);
		
				#current amount same as "update" amount, skip it!  Else, check perms to update it, then do so if all checks out
				if ( $oldNum == $newNum )
				{
					continue;
				}
				else
				{
					#remove error as we are actually doing something
					$error = '';			
					
					#init
					$checks = array();
					$number	= $newNum;
					
					#format number a bit..	
					$number = $this->registry->ecoclass->makeNumeric($number, true);
					$number = ($cartItemType->buyDecimalAmount()) ? $number : intval($number);				
					
					#run all those damn checks to see if this update is legal
					$checks = $this->checkCartAdditions( $itemID, $type, $banktype, $number, $theItem, 'update', $cartItemType );

					if ( $checks['error'] )
					{
						$this->registry->output->showError( $checks['error'] );
					}
					
					if ( !$checks['cartItem'] )
					{
						$this->registry->output->showError( 'item_not_in_cart' );
					}					

					$add2Item = array('c_quantity' => $number );
									
					$this->DB->update( 'eco_cart', $add2Item, 'c_member_id = ' .$this->memberData['member_id'].' AND c_id = '.$checks['cartItem']['c_id'] );
				}
			}
		}
		
		#if we have an error
		if ( $error )
		{
			$this->registry->output->showError( $error );
		}
		
		#redirect message
		if ( $numDel )
		{
			$s = ( $numDel > 1 ) ? 's' : '';
			$redirect_message = str_replace( "<%NUMBER%>", $this->registry->getClass('class_localization')->formatNumber( $numDel ), $this->lang->words['cart_items_removed'] );
			$redirect_message = str_replace( "<%S%>", $s, $redirect_message );
		}
		else
		{
			$redirect_message = $this->lang->words['cart_items_updated'];
		}
		
		$this->registry->output->redirectScreen( $redirect_message, $this->settings['base_url'] . "app=ibEconomy&amp;tab=buy&amp;area=cart" );			
	}
	
	/**
	 * Add item(s) to cart
	 */	
	public function addToCart()
	{
		$id 	= $this->request['item_id'];
		$number = intval($this->request['quantity']);
		
		#permission and enabled?
		$this->registry->ecoclass->canAccess('cart', false);
		
		#init
		$checks 		= array();

		#break up the item classification that we're adding to our cart			 
		$item_input_name = explode('_' , $id);

		$type 			= $item_input_name[0];
		$id 			= $item_input_name[1];
		$banktype 		= strtolower($item_input_name[2]);

		#loan hotfix
		$type			= ( $banktype != 'loan' ) ? $type : $banktype;
		
		#grab this cart types class object
		$cartItemType = $this->registry->ecoclass->grabCartTypeClass($type);

		#format number a bit..	
		$number = $this->registry->ecoclass->makeNumeric($number, true);

		#grab item from cache
		$theItem	= $cartItemType->grabItemByID($id);
				
		#check for any illegal activity :shifty:
		$checks = $this->checkCartAdditions( $id, $type, $banktype, $number, $theItem, false, $cartItemType );
		
		#if after all that we have an error...
		if ( $checks['error'] )
		{
			$this->registry->output->showError( $checks['error'] );
		}

		#no error?  Lets throw the item and number in our cart!
		if ( $checks['cartItem'] )
		{
			$add2Item = array('c_quantity' => $checks['cartItem']['c_quantity'] + $checks['number'] );
							
			$this->DB->update( 'eco_cart', $add2Item, 'c_member_id = ' .$this->memberData['member_id'].' AND c_id = '.$checks['cartItem'] ['c_id'] );
		}
		else
		{
			$newItem = array( 'c_member_id' 	=> $this->memberData['member_id'],
							  'c_member_name'	=> $this->memberData['members_display_name'],
							  'c_type' 			=> ( $type == 'loan' ) ? 'bank' : $type,
							  'c_type_id' 		=> $id,
							  'c_type_class' 	=> ( $type == 'bank' || $type == 'loan' ) ? $banktype : '',
							  'c_quantity'		=> $checks['number'],
							);
			$this->DB->insert( 'eco_cart', $newItem );
		}
		
		#redirect message and show what we added
		$redirect_message = $cartItemType->add2CartRedirectMessage($checks, $theItem);
		
		$this->registry->output->redirectScreen( $redirect_message, $this->settings['base_url'] . "app=ibEconomy&amp;tab=buy&amp;area=cart" );
	}
	
	/**
	 * Check cart additions for foul play and booboos
	 */	
	public function checkCartAdditions( $id, $type, $banktype, $number, $theItem, $actionType, $cartItemType=false )
	{
		#init
		$returnThis 	= array();
		$actualType 	= ( $banktype != 'loan' ) ? $type : $banktype;
		$officialType 	= ( $banktype != 'loan' ) ? $type : 'bank';
		
		#no type? error!
		if ( !array_key_exists ( $actualType, $this->registry->ecoclass->cartTypes ) )
		{
			$error = $this->lang->words['no_type'];
		}

		#permission and enabled?
		$this->registry->ecoclass->canAccess($actualType.'s', false);	
		
		#item and id checks and any further permissions on/off checks
		if ( !$error )
		{
			$error = $cartItemType->gotItemCheck($id, $theItem);
		}	
		
		#We've got this far, lets check my portfolio and cart, for this item
		if ( !$error )
		{
			$cartItem	= $this->registry->mysql_ibEconomy->checkCart($officialType, $id, $banktype);
			$folioItem	= $this->registry->mysql_ibEconomy->checkFolio($officialType, $id, $banktype);
		}
				
		#Need to check current cart quantity?
		$currentCartQuant = ( $actionType == 'update' ) ? 0 : $cartItem['c_quantity'];		

		#last checks, really specific to item type, good thing I have their class methods to call
		if ( !$error )
		{
			$error = $cartItemType->finalAdd2CartChecks($theItem, $number, $folioItem, $cartItem, $currentCartQuant, $banktype);
		}
		
		$returnThis['folioItem']	= $folioItem;
		$returnThis['cartItem'] 	= $cartItem;
		$returnThis['error'] 		= $error;
		$returnThis['number'] 		= $number;
		
		return $returnThis;
	}	
		
}