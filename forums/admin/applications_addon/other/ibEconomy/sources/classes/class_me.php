<?php

/**
 * (e32) ibEconomy
 * Me Class
 * @ My Portfolio Tab
 * Everything for the Id
 */

if ( ! defined( 'IN_IPB' ) )
{
	print "<h1>Incorrect access</h1>You cannot access this file directly. If you have recently upgraded, make sure you upgraded all the relevant files.";
	exit();
}

class class_me
{
	private $showPage	= "";
	
	protected $registry;
	protected $DB;
	protected $settings;
	protected $request;
	protected $lang;
	protected $member;
	protected $cache;	

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
	 * Perform one of a handful of Update My Portfolio functions
	 */
	public function updatePortfolio()
	{
		#init
		$action 	= $this->request['type'];
		$idType 	= explode('_', $this->request['item_id']);
		$typeID 	= $idType[0];
		$typeType	= strtolower($idType[1]);

		$actionMap  = array('bank_deposit' 		=> 'bank',
							'bank_withdrawal'	=> 'bank',
							'sell_stock'		=> 'stock',
							'cc_payment'		=> 'cc',
							'balance_transfer'	=> 'cc',
							'cash_advance'		=> 'cc',
							'sell_lt'			=> 'lt',
							'sell_shopitem'		=> 'shopitem',
							'trade_shopitem'	=> 'shopitem',
							'use_shopitem'		=> 'shopitem',
							'loan_payment'		=> 'bank',
						  );
		$type		= $actionMap[ $action ];
		$amount 	= ( in_array($type, array('stock','shopitem')) ) ? intval($this->request['amount']) : $this->registry->ecoclass->makeNumeric($this->request['amount'], true);
		$theItem	= $this->caches['ibEco_'.$type.'s'][ $typeID ];
		$realType	= ( $typeType != 'loan' ) ? $type : $typeType;
		$typeAbr 	= $this->registry->ecoclass->getTypeAbr($type);
				  
		#we got something to do?
		if ( !$action )
		{
			$this->registry->output->showError( $this->lang->words['no_action'] );
		}
		
		#do we have an item ID?
		if ( !$typeID )
		{
			$this->registry->output->showError( str_replace( "<%TYPE%>", $this->lang->words[ $type ], $this->lang->words['no_id'] ) );
		}

		#if bank, do we have a bank type?
		if ( $type == 'bank' && !$typeType )
		{
			$this->registry->output->showError( $this->lang->words['no_type'] );
		}
		
		#was an amount entered if needed?
		if ( !$amount > 0 && $action != 'use_shopitem' )
		{
			$this->registry->output->showError( $this->lang->words['no_amount'] );
		}
		
		#no item found by that ID? error!
		if ( !$theItem )
		{
			$this->registry->output->showError( str_replace( "<%TYPE%>", $this->lang->words[ $type ], $this->lang->words['none_found_show'] ) );
		}
		
		#make sure we have this item already
		$folioItem	= $this->registry->mysql_ibEconomy->checkFolio($type, $typeID, $typeType);		

		#no item found by that ID? error!
		if ( !$folioItem )
		{
			$this->registry->output->showError( str_replace( "<%TYPE%>", $this->lang->words[ $type ], $this->lang->words['none_found_in_port'] ) );	
		}
		
		#permission and enabled?
		$this->registry->ecoclass->canAccess($realType.'s', false);				  
		
		#switcharoo for the various portfolio actions
		switch ($action)
		{
			case 'bank_deposit':

				#deposit over group max?
				if ( $this->memberData['g_eco_bank_max'] && $amount + $folioItem['p_amount'] > $this->memberData['g_eco_bank_max'] )
				{
					$error = str_replace( "<%YOUR_MAX%>", $this->registry->getClass('class_localization')->formatNumber( $this->memberData['g_eco_bank_max'] ) .' '. $this->settings['eco_general_currency'], $this->lang->words['port_item_quantity_over_group_max'] );		
					$error = str_replace( "<%TYPE%>", $this->lang->words[ $type ], $error );
					$error = str_replace( "<%NUMBER_WORD%>", $this->lang->words['amount'], $error );

					$this->registry->output->showError( $error );						
				}
				
				#deposit over my max?
				if ( $this->memberData[ $this->settings['eco_general_pts_field'] ] < $amount )
				{
					$error = str_replace( "<%MY_POINTS%>", $this->registry->getClass('class_localization')->formatNumber( $this->memberData[ $this->settings['eco_general_pts_field'] ] ), $this->lang->words['deposit_too_high'] );
					$error = str_replace( "<%POINTS_NAME%>", $this->settings['eco_general_currency'], $error );
					
					$this->registry->output->showError( $error );						
				}
				
				#and you owe..
				$fee = ( $typeType == 'checking' ) ? $theItem['b_c_dep_fee'] : $theItem['b_s_dep_fee'];
				$pointsSpent = $this->registry->ecoclass->makeNumeric($amount + $amount * $fee/100, false);
				
				#do deposit
				$newAmount = $this->registry->ecoclass->makeNumeric($folioItem['p_amount'] + $amount, false);
				$this->DB->update( 'eco_portfolio', array('p_amount' => $newAmount), 'p_id = ' . $folioItem['p_id'] );
				
				#redirect message is..
				$redirectMessage = str_replace( "<%AMOUNT%>", $this->registry->getClass('class_localization')->formatNumber( $amount, $this->registry->ecoclass->decimal ), $this->lang->words['deposit_redirect'] );				
				$redirectMessage = str_replace( "<%POINTS_NAME%>", $this->settings['eco_general_currency'], $redirectMessage );
				
			break;
			
			case 'bank_withdrawal':
			
				#withdrawal more than in bank?
				if ( $amount > $folioItem['p_amount'] )
				{
					$this->registry->output->showError( $this->lang->words['withdrawal_too_much'] );
				}
				
				#here is your cash voucher, please show at the door
				$fee = ( $typeType == 'checking' ) ? $theItem['b_c_wthd_fee'] : $theItem['b_s_wthd_fee'];
				$pointsGot = $this->registry->ecoclass->makeNumeric($amount - $amount * $fee/100, false);				
				
				#do withdrawal
				$newAmount = $this->registry->ecoclass->makeNumeric($folioItem['p_amount'] - $amount, false);
				$this->DB->update( 'eco_portfolio', array('p_amount' => $newAmount), 'p_id = ' . $folioItem['p_id'] );
				
				#redirect message is..
				$redirectMessage = str_replace( "<%AMOUNT%>", $this->registry->getClass('class_localization')->formatNumber( $amount, $this->registry->ecoclass->decimal ), $this->lang->words['withdrawal_redirect'] );				
				$redirectMessage = str_replace( "<%POINTS_NAME%>", $this->settings['eco_general_currency'], $redirectMessage );
			
			break;

			case 'sell_stock':
			
				#not allowed to sell stocks? (added in 1.4.2)
				if ( $theItem['s_use_perms'] && !$this->registry->permissions->check( 'close', $theItem ) )
				{
					$this->registry->output->showError( $this->lang->words['not_allowed_to_sell_this_stock'] );
				}
				
				#selling more shares than you have?
				if ( $amount > $folioItem['p_amount'] )
				{
					$this->registry->output->showError( $this->lang->words['sell_more_shares_than_have'] );						
				}
				
				#here is your cash voucher, please show at the door
				$sellValue = $theItem['s_value'] * $amount;
				$pointsGot = $this->registry->ecoclass->makeNumeric($sellValue - $sellValue * $this->settings['eco_stocks_sell_fee']/100, false);				
				
				#do withdrawal
				$newAmount = $folioItem['p_amount'] - $amount;
				if ( $newAmount == 0 )
				{
					$portItemDeleted = 1;
					$this->DB->delete( 'eco_portfolio', 'p_id = ' . $folioItem['p_id'] );
				}
				else
				{
					$this->DB->update( 'eco_portfolio', array('p_amount' => $newAmount), 'p_id = ' . $folioItem['p_id'] );
				}
				
				#redirect message is..
				$redirectMessage = str_replace( "<%AMOUNT%>", $this->registry->getClass('class_localization')->formatNumber( $amount, $this->registry->ecoclass->decimal ), $this->lang->words['stocksell_redirect']);				
				
			break;

			case 'cc_payment':

				#paying under minimum due?
				if ( $amount < $folioItem['p_amount'] * $this->settings['eco_ccs_min_pay']/100 )
				{
					$this->registry->output->showError( $this->lang->words['underpaid_due_amt'] );						
				}
				
				#paying more than your current balance?
				if ( $amount > $folioItem['p_amount'] )
				{
					$this->registry->output->showError( $this->lang->words['overpaid_due_amt'] );						
				}
				
				#paying more than you have in you have?
				if ( $this->memberData[ $this->settings['eco_general_pts_field'] ] < $amount )
				{
					$error = str_replace( "<%MY_POINTS%>", $this->registry->getClass('class_localization')->formatNumber( $this->memberData[ $this->settings['eco_general_pts_field'] ], $this->registry->ecoclass->decimal ), $this->lang->words['payment_too_high'] );
					$error = str_replace( "<%POINTS_NAME%>", $this->settings['eco_general_currency'], $error );
					
					$this->registry->output->showError( $error );						
				}				

				#and you owe..
				$pointsSpent = $amount;
				
				#do deposit
				$newAmount = $this->registry->ecoclass->makeNumeric($folioItem['p_amount'] - $amount, false);
				$this->DB->update( 'eco_portfolio', array('p_amount' => $newAmount, 'p_update_date' => time() ), 'p_id = ' . $folioItem['p_id'] );
				
				#redirect message is..
				$redirectMessage = str_replace( "<%AMOUNT%>", $this->registry->getClass('class_localization')->formatNumber( $amount, $this->registry->ecoclass->decimal ), $this->lang->words['cc_payment_redirect'] );				
				$redirectMessage = str_replace( "<%POINTS_NAME%>", $this->settings['eco_general_currency'], $redirectMessage );
				
			break;

			case 'balance_transfer':
			
				#did a balance transfer already that has yet to expire?
				if ( $folioItem['p_rate_ends'] > time() )
				{
					$this->registry->output->showError( str_replace( "<%TIME%>", $this->registry->getClass('class_localization')->getDate( $folioItem['p_rate_ends'], 'JOINED' ), $this->lang->words['existing_bal_transfer_already'] ) );
				}
				
				#trying to go over your credit card's credit line? :nonono:
				if ( $amount > $folioItem['p_max'] - $folioItem['p_amount'] )
				{
					$this->registry->output->showError( str_replace( "<%TYPE%>", $this->lang->words[ $type ], $this->lang->words['balance_transfer_too_high'] ) );
				}

				#portfolio cache enabled?
				if ( ! $this->settings['eco_general_cache_portfolio'] )
				{
					$cc = $this->registry->mysql_ibEconomy->grabSinglePortItemExtended( $this->memberData['member_id'], $this->request['cc_id'], 'credit_card' );
				}
				else
				{
					#if I have a checking/savings account by that ID in my cache, lets see how many and grab the data
					$cc = $this->caches['ibEco_portfolios'][ $this->memberData['member_id'] ]['ccs_'][ $this->request['cc_id'] ];
				}
				
				#did a balance transfer already that has yet to expire?
				if ( $amount > $cc['p_amount'] )
				{
					$this->registry->output->showError( str_replace( "<%TYPE%>", $this->lang->words[ $type ], $this->lang->words['balance_transfer_over_balance'] ) );
				}				
				
				#you don't own this credit card, frauder!
				if ( !$cc )
				{
					$this->registry->output->showError( str_replace( "<%TYPE%>", $this->lang->words[ $type ], $this->lang->words['none_found_in_port'] ) );
				}

				#balance transfer over group max?
				if ( $this->memberData['g_eco_bal_trnsfr_max'] && $amount > $this->memberData['g_eco_bal_trnsfr_max'] )
				{
					$error = str_replace( "<%YOUR_MAX%>", $this->registry->getClass('class_localization')->formatNumber( $this->memberData['g_eco_bal_trnsfr_max'] ), $this->lang->words['bal_transfer_over_group_max'] );		
					$error = str_replace( "<%TYPE%>", $this->lang->words[ $type ], $error );
					$error = str_replace( "<%POINTS_NAME%>", $this->settings['eco_general_currency'], $error );

					$this->registry->output->showError( $error );						
				}				
				
				#step 1 pay amount to other card (the transfer, duh)
				$newAmount = $this->registry->ecoclass->makeNumeric($cc['p_amount'] - $amount, false);
				$this->DB->update( 'eco_portfolio', array('p_amount' => $newAmount), 'p_id = ' . $cc['p_id'] );
				
				#step 2 add amount to balance of this card
				$fee = $theItem['cc_bal_trnsfr_fee']/100;
				#changed to + below for version 1.6
				$newAmount = $folioItem['p_amount'] + $amount + $fee * $amount;
				$this->DB->update( 'eco_portfolio', array('p_amount' => $newAmount), 'p_id = ' . $folioItem['p_id'] );
				
				#redirect message is..
				$redirectMessage = str_replace( "<%AMOUNT%>", $this->registry->getClass('class_localization')->formatNumber( $amount, $this->registry->ecoclass->decimal ), $this->lang->words['balance_transfer_redirect']);				
				$redirectMessage = str_replace( "<%POINTS_NAME%>", $this->settings['eco_general_currency'], $redirectMessage );
				
			break;

			case 'cash_advance':
			
				#balance transfer would go over credit line?  error!
				if ( !$theItem['cc_csh_adv'] )
				{
					$this->registry->output->showError( $this->lang->words['cash_advance_off'] );								
				}			
			
				#cash advance over group max?  error!
				if ( $this->memberData['g_eco_cash_adv_max'] && $amount + $folioItem['p_rate_next'] > $this->memberData['g_eco_cash_adv_max'] )
				{
					$error = str_replace( "<%YOUR_MAX%>", $this->registry->getClass('class_localization')->formatNumber( $this->memberData['g_eco_cash_adv_max'] ) .' '. $this->settings['eco_general_currency'], $this->lang->words['port_item_quantity_over_group_max'] );		
					$error = str_replace( "<%TYPE%>", $this->lang->words[ $type ], $error );
					$error = str_replace( "<%NUMBER_WORD%>", $this->lang->words['cash_advance'], $error );
					
					$this->registry->output->showError( $error );
				}
			
				#balance transfer would go over credit line?  error!
				if ( $amount + $folioItem['p_amount'] > $folioItem['p_max'] )
				{
					$this->registry->output->showError( str_replace( "<%CREDIT_LINE%>", $this->registry->getClass('class_localization')->formatNumber( $folioItem['p_max'] ), $this->lang->words['port_cash_advance_over_card_max'] ) );								
				}
				
				#here is your cash voucher, please show at the door
				$fee = $theItem['cc_csh_adv_fee']/100;
				$pointsGot = $this->registry->ecoclass->makeNumeric($amount - $fee * $amount, false);
				
				#my new balance is...
				$newAmount = $this->registry->ecoclass->makeNumeric($folioItem['p_amount'] + $amount, false);
				$this->DB->update( 'eco_portfolio', array('p_amount' => $newAmount), 'p_id = ' . $folioItem['p_id'] );
				
				#redirect message is..
				$redirectMessage = str_replace( "<%AMOUNT%>", $this->registry->getClass('class_localization')->formatNumber( $amount, $this->registry->ecoclass->decimal ), $this->lang->words['cash_advance_redirect']);				
				$redirectMessage = str_replace( "<%POINTS_NAME%>", $this->settings['eco_general_currency'], $redirectMessage );
				
			break;	

			case 'sell_lt':
			
				#selling prematurely and early cashout is disabled for this investment?
				if ( !$theItem['lt_early_cash'] && $folioItem['p_rate_ends'] > time() )
				{
					$this->registry->output->showError( str_replace( "<%WHEN_CAN_SELL%>", $this->registry->getClass('class_localization')->getDate( $folioItem['p_rate_ends'], 'JOINED' ), $this->lang->words['cant_sell_lt_yet'] ) );						
				}			
			
				#selling more of the investment than your current investment balance?
				if ( $amount > $folioItem['p_amount'] )
				{
					$this->registry->output->showError( $this->lang->words['oversold_lt'] );						
				}
				
				#here is your cash voucher, please show at the door
				$fee = ( $folioItem['p_rate_ends'] > time() ) ? $theItem['lt_early_cash_fee']/100 : 0;
				$pointsGot = $this->registry->ecoclass->makeNumeric($amount - $fee * $amount, false);
				
				#do lt cashout
				$newAmount = $this->registry->ecoclass->makeNumeric($folioItem['p_amount'] - $amount, false);
				
				#sold all your lt value?  or just adjust it?
				if ( $newAmount == 0 )
				{
					$portItemDeleted = 1;
					$this->DB->delete( 'eco_portfolio', 'p_id = ' . $folioItem['p_id'] );
				}
				else
				{
					$this->DB->update( 'eco_portfolio', array('p_amount' => $newAmount), 'p_id = ' . $folioItem['p_id'] );
				}
				
				#redirect message is..
				$redirectMessage = str_replace( "<%AMOUNT%>", $this->registry->getClass('class_localization')->formatNumber( $amount, $this->registry->ecoclass->decimal ), $this->lang->words['ltsell_redirect']);				
				$redirectMessage = str_replace( "<%POINTS_NAME%>", $this->settings['eco_general_currency'], $redirectMessage );
				
			break;
			
			case 'sell_shopitem':
			
				#not allowed to sell this item?
				if ( $theItem['si_use_perms'] && !$this->registry->permissions->check( 'sell', $theItem ) )
				{
					$this->registry->output->showError( $this->lang->words['no_perm_to_sell'] );						
				}			
			
				#selling more than you have?
				if ( $amount > $folioItem['p_amount'] )
				{
					$this->registry->output->showError( $this->lang->words['selling_more_than_have'] );						
				}
				
				#here is your cash voucher, please show at the door
				$fee 		= $theItem['si_cost'] * $amount * $this->settings['eco_shop_sell_fee']/100;
				$pointsGot 	= $this->registry->ecoclass->makeNumeric($theItem['si_cost'] * $amount - $fee, false);
				
				#do shopitem sell
				$newAmount = $folioItem['p_amount'] - $amount;
				
				if ( $newAmount == 0 )
				{
					$portItemDeleted = 1;
					$this->DB->delete( 'eco_portfolio', 'p_id = ' . $folioItem['p_id'] );
				}
				else
				{
					$this->DB->update( 'eco_portfolio', array('p_amount' => $newAmount), 'p_id = ' . $folioItem['p_id'] );
				}
				
				#redirect message is..
				$redirectMessage = str_replace( "<%AMOUNT%>", $this->registry->getClass('class_localization')->formatNumber( $amount ), $this->lang->words['shopitem_sell_redirect']);				
				$redirectMessage = str_replace( "<%POINTS_NAME%>", $this->settings['eco_general_currency'], $redirectMessage );
				$redirectMessage = str_replace( "<%POINTS%>", $this->registry->getClass('class_localization')->formatNumber( $pointsGot, $this->registry->ecoclass->decimal ), $redirectMessage );
				
			break;

			case 'trade_shopitem':
			
				#not allowed to trade this item?
				if ( $theItem['si_use_perms'] && !$this->registry->permissions->check( 'trade', $theItem ) )
				{
					$this->registry->output->showError( $this->lang->words['no_perm_to_trade'] );						
				}			
			
				#trading more than you have?
				if ( $amount > $folioItem['p_amount'] )
				{
					$this->registry->output->showError( $this->lang->words['trading_more_than_have'] );						
				}
				
				#load item recipient
				$recipient = IPSMember::load( $this->request['mem_name'], 'all', 'displayname' );
				
				#no one found?
				if ( !$recipient['member_id'] )
				{	
					$this->registry->output->showError( $this->lang->words['no_member_found_by_id'] );			
				}
				
				#trading with yourself?
				if ( $recipient['member_id'] == $this->memberData['member_id'] )
				{	
					$this->registry->output->showError( $this->lang->words['no_trade_with_yourself'] );			
				}

				#can recipient even own this item?
				$recipPerms	= ( $recipient['org_perm_id'] ) ? $recipient['org_perm_id'] : $recipient['g_perm_id'];
				$recipArray = explode( ",", $recipPerms );
				
				if ( $theItem['si_use_perms'] && !$this->registry->permissions->check( 'open', $theItem, $recipArray ) )
				{
					$this->registry->output->showError( str_replace( "<%MEM_NAME%>", $recipient['members_display_name'], $this->lang->words['no_perm_to_receive'] ) );						
				}

				#send item(s) on over...
				#portfolio cache enabled?
				if ( ! $this->settings['eco_general_cache_portfolio'] )
				{
					$recipientItem = $this->registry->mysql_ibEconomy->grabSinglePortItemExtended( $recipient['member_id'], $typeID, 'shop_item' );
				}
				else
				{
					#if I have this item already in my portfolio
					$recipientItem = $this->caches['ibEco_portfolios'][ $recipient['member_id'] ]['shopitems_'][ $typeID ];
				}
				
				if ( $recipientItem['p_id'] )
				{
					$newAmount = $recipientItem['p_amount'] + $amount;
					$this->DB->update( 'eco_portfolio', array('p_amount' => $newAmount), 'p_id = ' . $recipientItem['p_id'] );				
				}
				else
				{
					#add to portfolio of receiver, from sratch
					$newPortItem = array( 'p_member_id'	=> $recipient['member_id'],
										'p_member_name'	=> $recipient['members_display_name'],
										'p_type'		=> $type,
										'p_type_id'		=> $typeID,
										'p_type_class'	=> '',
										'p_amount'		=> $amount,
										'p_max'			=> 0,
										'p_rate'		=> 0,
										'p_last_hit'	=> 0,
										'p_rate_ends'	=> 0,
										'p_rate_next'	=> 0,
										'p_purch_date'	=> time()
										);
										
					$this->DB->insert( 'eco_portfolio', $newPortItem );				
				}
				
				#here is your cash voucher, please show at the door
				$pointsSpent = $this->registry->ecoclass->makeNumeric($amount * $theItem['si_cost'] * $this->settings['eco_shop_trade_tax']/100, false);
				
				#do shopitem trade
				$newAmount = $folioItem['p_amount'] - $amount;
				
				if ( $newAmount == 0 )
				{
					$portItemDeleted = 1;
					$this->DB->delete( 'eco_portfolio', 'p_id = ' . $folioItem['p_id'] );
				}
				else
				{
					$this->DB->update( 'eco_portfolio', array('p_amount' => $newAmount), 'p_id = ' . $folioItem['p_id'] );
				}
				
				#extra log data
				$logBit = '_'.$recipient['members_display_name'];

				#send PM to item recipient
				$this->registry->ecoclass->sendPM( $recipient['member_id'], $recipient['members_display_name'], $amount, $theItem['si_title'], 'trade' );				
				
				#redirect message is..
				$redirectMessage = str_replace( "<%AMOUNT%>", $this->registry->getClass('class_localization')->formatNumber( $amount ), $this->lang->words['shopitem_trade_redirect']);				
				$redirectMessage = str_replace( "<%ITEM_NAME%>", $theItem['si_title'], $redirectMessage );
				$redirectMessage = str_replace( "<%MEM_NAME%>", $recipient['members_display_name'], $redirectMessage );
				
			break;
			
			case 'loan_payment':

				#payment over loan amount due?
				if ( $amount > $folioItem['p_amount'] )
				{
					$error = str_replace( "<%OWED%>", $this->registry->getClass('class_localization')->formatNumber( $folioItem['p_amount'], $this->registry->ecoclass->decimal ) .' '. $this->settings['eco_general_currency'], $this->lang->words['loan_payment_over_amt'] );

					$this->registry->output->showError( $error );						
				}
				
				#paying more than you have in you have?
				if ( $this->memberData[ $this->settings['eco_general_pts_field'] ] < $amount )
				{
					$error = str_replace( "<%MY_POINTS%>", $this->registry->getClass('class_localization')->formatNumber( $this->memberData[ $this->settings['eco_general_pts_field'] ], $this->registry->ecoclass->decimal ), $this->lang->words['payment_too_high'] );
					$error = str_replace( "<%POINTS_NAME%>", $this->settings['eco_general_currency'], $error );
					
					$this->registry->output->showError( $error );						
				}				
				
				#and you owe..
				$pointsSpent = $amount;
				
				#do deposit
				$newAmount = $this->registry->ecoclass->makeNumeric($folioItem['p_amount'] - $amount, false);
				
				if ( $newAmount == 0 )
				{
					$this->DB->delete( 'eco_portfolio', 'p_id = ' . $folioItem['p_id'] );
					$portItemDeleted = 1;
				}
				else
				{
					$this->DB->update( 'eco_portfolio', array('p_amount' => $newAmount), 'p_id = ' . $folioItem['p_id'] );
				}
				
				#redirect message is..
				$redirectMessage = str_replace( "<%AMOUNT%>", $this->registry->getClass('class_localization')->formatNumber( $amount, $this->registry->ecoclass->decimal ), $this->lang->words['loan_payment_redirect'] );				
				$redirectMessage = str_replace( "<%POINTS_NAME%>", $this->settings['eco_general_currency'], $redirectMessage );
				
			break;			

			case 'use_shopitem':

				$this->registry->output->silentRedirect( $this->settings['base_url'].'app=ibEconomy&amp;tab=me&amp;area=using_shopitem&amp;id='.$theItem['si_id'] );
				
			break;			

			default:
			
				$error = $this->lang->words['no_type'];
				
			break;
		}

		#spent some cash?  better collect
		if ( $pointsSpent )
		{
			$this->registry->mysql_ibEconomy->updateMemberPts($this->memberData['member_id'], $pointsSpent, '-', true);	
		}
		
		#picked up some cash?  better fork it over
		if ( $pointsGot )
		{
			$this->registry->mysql_ibEconomy->updateMemberPts($this->memberData['member_id'], $pointsGot, '+', true);
		}

		#add log
		$logBit = ( $logBit ) ? $logBit : $realType;
		$this->registry->ecoclass->addLog( $action, $amount, $typeID, $logBit, $theItem[ $typeAbr.'_title'] );

		#update cache
		$this->registry->ecoclass->acm('portfolios');
		
		#redirect em
		$url = ( $portItemDeleted ) ? "app=ibEconomy&amp;tab=me" : "app=ibEconomy&amp;tab=me&amp;area=single&amp;type=".$realType."&amp;id=".$typeID."&amp;type_type=".$typeType;
		$this->registry->output->redirectScreen( $redirectMessage, $this->settings['base_url'] . $url );
	}
	
	/**
	 * Use Shop Item Page
	 */
	public function usingShopItem( $do )
	{
		#init
		$itemID 	= $this->request['id'];
		$theItem	= $this->caches['ibEco_shopitems'][ $itemID ];
		$this->lang->words['optional_message'] = sprintf( $this->lang->words['optional_message'], $this->lang->words['variable'] );
		
		#do we have an item ID?
		if ( !$itemID )
		{
			$this->registry->output->showError( str_replace( "<%TYPE%>", $this->lang->words['shopitem'], $this->lang->words['no_id'] ) );
		}

		#no item found by that ID? error!
		if ( !$theItem )
		{
			$this->registry->output->showError( str_replace( "<%TYPE%>", $this->lang->words['shopitem'], $this->lang->words['none_found_show'] ) );
		}
		
		#make sure we have this item already
		$folioItem	= $this->registry->mysql_ibEconomy->checkFolio('shopitem', $itemID, $typeType='');		

		#no item found by that ID? error!
		if ( !$folioItem )
		{
			$this->registry->output->showError( str_replace( "<%TYPE%>", $this->lang->words['shopitem'], $this->lang->words['none_found_in_port'] ) );	
		}
		
		#permission and enabled?
		$this->registry->ecoclass->canAccess('shop', false);				  
	
		#get file paired with this item
		require_once( IPSLib::getAppDir( 'ibEconomy' ).'/sources/shop_items/'.$theItem['si_file'] );
		$itemFile =  new class_shop_item( $this->registry );
		
		switch ( $do )
		{
			case 'now':
			
			#try to use item
			$gotten = $itemFile->useItem($theItem,$folioItem); 
			
			if ( $gotten['error'] )
			{
				$this->showPage = $this->registry->output->getTemplate('ibEconomy')->error($gotten['error']);

				return $this->showPage;				
			}
			
			#no errors, redirect outta here!
			$this->registry->output->redirectScreen( $this->lang->words['shopitem_used'].$gotten['redirect_text'], $this->settings['base_url'] .'app=ibEconomy&amp;tab=me' );
			
			break;
			
			default:
				
				#send to using item page
				$theItem['html'] = $itemFile->usingItem($theItem);	

				$this->showPage = $this->registry->output->getTemplate('ibEconomy2')->usingItem($theItem);
				
				return $this->showPage;
			
			break;			
		}
	}	
	
	/**
	 * Show overview of my stuff
	 */
	public function myOverview( $memberIN )
	{
		#init
		$items 		= "";
		$item  		= array();
		$portfolios = array();
		$itemTypes 	= $this->registry->ecoclass->makeItemList();
		$words 		= $this->registry->ecoclass->makeOverviewMap();
		
		#who we doing?
		$member = ( $memberIN ) ? $memberIN : $this->memberData;

		#do points
		$item['value1'] = $this->settings['eco_general_cursymb'] . $this->registry->getClass('class_localization')->formatNumber( $member[ $this->settings['eco_general_pts_field'] ], $this->registry->ecoclass->decimal );
		if ($this->settings['eco_worth_on'])
		{
			$item['value2'] = $this->settings['eco_general_cursymb'] . $this->registry->getClass('class_localization')->formatNumber( $member['eco_worth'], $this->registry->ecoclass->decimal );
		}
		else
		{
			#awfully big query just to grab 1 number (rank)
			$item['value2'] = $this->registry->mysql_ibEconomy->rankMembers( 'points', $member['member_id'] );
			$item['value2'] = $this->registry->getClass('class_localization')->formatNumber( $item['value2']);
		}
		
		$items .= $this->registry->output->getTemplate('ibEconomy')->overviewRow($item, $words['points']);
			
		#portfolio cache enabled?
		if ( ! $this->settings['eco_general_cache_portfolio'] )
		{
			$portfolios = $this->registry->mysql_ibEconomy->grabPortfolioItemsByType('', $member['member_id'], true );
		}

		foreach ( $itemTypes AS $iType )
		{
			#init
			$item['value1'] = 0;
			$item['value2'] = 0;
			
			#permission and enabled?
			$continue = $this->registry->ecoclass->canAccess($iType.'s', true);

			if ( !$continue )
			{
				continue;
			}
			
			#portfolio cache enabled?
			if ( $this->settings['eco_general_cache_portfolio'] )
			{
				$portfolios[ $iType ] = $this->caches['ibEco_portfolios'][ $member['member_id'] ][ $iType.'s_'];
			}
			
			if ( $iType == 'bank' )
			{
				#portfolio cache enabled?
				if ( $this->settings['eco_general_cache_portfolio'] )
				{
					$portfolios['banks_savings']  = $this->caches['ibEco_portfolios'][ $member['member_id'] ]['banks_savings'];
					$portfolios['banks_checking'] = $this->caches['ibEco_portfolios'][ $member['member_id'] ]['banks_checking'];
				}	
				
				#tally up banks
				if ( ( is_array($portfolios['banks_savings']) && count($portfolios['banks_savings']) ) || ( is_array($portfolios['banks_checking']) && count($portfolios['banks_checking']) ) )
				{ 
					#format savings vals
					if ( is_array($portfolios['banks_savings']) && count($portfolios['banks_savings']) )
					{ 		
						foreach ( $portfolios['banks_savings'] AS $row )
						{	
							$item['value1'] = $item['value1'] + $row['p_amount'];
						}
					}
					
					#format checking vals
					if ( is_array($portfolios['banks_checking']) && count($portfolios['banks_checking']) )
					{ 			
						foreach ( $portfolios['banks_checking'] AS $row )
						{	
							$item['value2'] = $item['value2'] + $row['p_amount'];
						}
					}
					
					#show row if we bank
					if ( $item['value1'] or $item['value2'] )
					{
						$item['value1'] = $this->settings['eco_general_cursymb'] . $this->registry->getClass('class_localization')->formatNumber( $item['value1'], $this->registry->ecoclass->decimal );
						$item['value2'] = $this->settings['eco_general_cursymb'] . $this->registry->getClass('class_localization')->formatNumber( $item['value2'], $this->registry->ecoclass->decimal );

						$items .= $this->registry->output->getTemplate('ibEconomy')->overviewRow($item, $words['banks_']);
					}
				}
			}
			else
			{					
				#everything but those tricky banks
				if ( is_array($portfolios[ $iType ] ) )
				{ 					
					foreach ( $portfolios[ $iType ] AS $row )
					{
						if ( $iType == 'stock' )
						{
							$item['value1']	= $item['value1'] + $row['p_amount'];
							$item['value2'] = $item['value2'] + $row['p_amount'] * $this->caches['ibEco_'.$iType.'s' ][ $row['p_type_id'] ]['s_value'];
						}
						else if ( $iType == 'cc' )
						{
							$item['value1']	= $item['value1'] + $row['p_amount'];
							$item['value2'] = $item['value2'] + $row['p_max'];
						}
						else if ( $iType == 'lt' )
						{
							$item['value1']++;
							$item['value2'] = $item['value2'] + $row['p_amount'];
						}
						else if ( $iType == 'shopitem' )
						{
							$item['value1'] = $item['value1'] + $row['p_amount'];
							$item['value2'] = $item['value2'] + $row['p_amount'] * $this->caches['ibEco_'.$iType.'s' ][ $row['p_type_id'] ]['si_cost'];
						}
						else if ( $iType == 'loan' )
						{
							$item['value1']++;
							$item['value2'] = $item['value2'] + $row['p_amount'];
						}						
					}

					#last bit of formatting
					if ( $iType == 'cc' )
					{
						$item['value1'] = $this->settings['eco_general_cursymb'] . $this->registry->getClass('class_localization')->formatNumber( $item['value1'], $this->registry->ecoclass->decimal );
					}
					else
					{
						$item['value1'] = $this->registry->getClass('class_localization')->formatNumber( $item['value1'] );
					}					
						
					$item['value2'] = $this->settings['eco_general_cursymb'] . $this->registry->getClass('class_localization')->formatNumber( $item['value2'], $this->registry->ecoclass->decimal );
					
					#do html row
					$items .= $this->registry->output->getTemplate('ibEconomy')->overviewRow($item, $words[ $iType.'s_']);					
				}
			}
		}
		
		#throw rows to wrapper
		$this->showPage = $this->registry->output->getTemplate('ibEconomy')->overviewWrapper($items);
		
		return $this->showPage;
	}
	
	/**
	 * Show my items
	 */
	public function myWelfare()
	{
		#already on welfare?
		if ( ! $this->memberData['eco_on_welfare'] )
		{
			$text = sprintf( $this->lang->words['youre_not_on_welfare_exp'], $this->settings['eco_general_currency'], $this->settings['eco_welfare_cycle'] );
			$this->showPage = $this->registry->output->getTemplate('ibEconomy')->error( " <img title = '{$this->lang->words['youre_not_on_welfare']}' src = '{$this->settings['img_url']}/cross.png' /> {$text}<br /><br /><a href='{$this->settings['base_url']}app=ibEconomy&amp;tab=cash&amp;area=welfare' style='font-decoration:none;font-weight:bolder;font-size:1.5em'>{$this->lang->words['sign_up']}</a>" );
			
			return $this->showPage;
		}

		$text = sprintf( $this->lang->words['Im_on_welfare_exp'], $this->settings['eco_general_currency'], $this->settings['eco_welfare_cycle'] );
		
		$this->showPage = $this->registry->output->getTemplate('ibEconomy2')->myWelfare($text);
				
		return $this->showPage;
	}	
	
	/**
	 * Show my items
	 */
	public function myItems($type)
	{	
		#init vars
		$item_rows 			= "";
		$Num				= 0;
		$itemsShown			= 0;
		$column_titles		= array();
		$portfolios			= array();
		
		#portfolio cache enabled?
		if ( ! $this->settings['eco_general_cache_portfolio'] )
		{
			$portfolios = $this->registry->mysql_ibEconomy->grabPortfolioItems( $this->memberData['member_id'], $howMany='all', 'cache', '', '', 0, '', '', $organizeItFirst=true );
		}
		else
		{
			$portfolios[ $type ] = $this->caches['ibEco_portfolios'][ $this->memberData['member_id'] ][ $type.'_'];
		}

		switch ($type)
		{
			case 'banks':
				#init bank vars
				$my_savings_accts 	= array();
				$my_checking_accts 	= array();
				
				#portfolio cache enabled?
				if ( $this->settings['eco_general_cache_portfolio'] )
				{
					$portfolios['banks_savings']  = $this->caches['ibEco_portfolios'][ $this->memberData['member_id'] ]['banks_savings'];
					$portfolios['banks_checking'] = $this->caches['ibEco_portfolios'][ $this->memberData['member_id'] ]['banks_checking'];
				}

				#if I have some checking and/or savings accounts in my cache, lets see how many and grab the data
				if ( is_array($portfolios['banks_savings']) || is_array($portfolios['banks_checking']) )
				{ 
					if ( is_array($portfolios['banks_savings'] ) )
					{ 		
						foreach ( $portfolios['banks_savings'] AS $row )
						{	
							$my_savings_accts[] = $row;
							$total_items++;
						}
					}
					if ( is_array($portfolios['banks_checking']) )
					{ 			
						foreach ( $portfolios['banks_checking'] AS $row )
						{	
							$my_checking_accts[] = $row;
							$total_items++;
						}
					}
				}
				
				#process savings accounts for display
				if ( is_array( $my_savings_accts ) )
				{
					foreach ( $my_savings_accts AS $row )
					{
						$Num++;
						
						if ( $Num <= intval($this->request['st'] ) )
						{
							continue;
						}
						
						$row = $this->registry->ecoclass->formatRow($row, 'bank', 'savings');
						$item_rows .= $this->registry->output->getTemplate('ibEconomy2')->my_items_row($row);
						
						$itemsShown++;
						
						if ( $itemsShown == $this->settings['eco_general_pp'] )
						{
							break;
						}					
					}
				}
				
				#process checking accounts for display
				if ( $itemsShown != $this->settings['eco_general_pp'] && is_array( $my_checking_accts ) )
				{
					foreach ( $my_checking_accts AS $row )
					{
						$Num++;
						
						if ( $Num <= intval($this->request['st'] ) )
						{
							continue;
						}
						
						$row = $this->registry->ecoclass->formatRow($row, 'bank', 'checking');
						$item_rows .= $this->registry->output->getTemplate('ibEconomy2')->my_items_row($row);
						
						$itemsShown++;
						
						if ( $itemsShown == $this->settings['eco_general_pp'] )
						{
							break;
						}					
					}
				}			

				#column titles
				$column_titles['two'] 	= $this->lang->words['type'];
				$column_titles['three'] = $this->lang->words['apy'];
				$column_titles['four'] 	= $this->lang->words['with_fee'];
				$column_titles['five'] 	= $this->lang->words['total_balance'];			
			
			break;
			
			case 'stocks':

				#init stock vars
				$my_stocks 	= array();
				
				#if I have some stocks in my cache, lets see how many and grab the data
				if ( is_array($portfolios[ $type ] ) )
				{ 		
					foreach ( $portfolios[ $type ] AS $row )
					{	
						$my_stocks[] = $row;
						$total_items++;
					}
				}
				
				#process our stocks for display
				if ( is_array( $my_stocks ) )
				{
					foreach ( $my_stocks AS $row )
					{
						$Num++;
						
						if ( $Num <= intval($this->request['st'] ) )
						{
							continue;
						}
						
						$row = $this->registry->ecoclass->formatRow($row, 'stock', false);
						$item_rows .= $this->registry->output->getTemplate('ibEconomy2')->my_items_row($row);
						
						$itemsShown++;
						
						if ( $itemsShown == $this->settings['eco_general_pp'] )
						{
							break;
						}					
					}
				}			

				#column titles
				$column_titles['two'] 	= $this->lang->words['type'];
				$column_titles['three'] = $this->lang->words['variable'];
				$column_titles['four'] 	= $this->lang->words['share_value'];
				$column_titles['five'] 	= $this->lang->words['total_share_value'];			
			
			break;
			
			case 'ccs':

				#init cc vars
				$my_ccs 	= array();
				
				#if I have some credit-cards in my cache, lets see how many and grab the data
				if ( is_array($portfolios[ $type ] ) )
				{ 		
					foreach ( $portfolios[ $type ] AS $row )
					{	
						$my_ccs[] = $row;
						$total_items++;
					}
				}
				
				#process our ccs for display
				if ( is_array( $my_ccs ) )
				{
					foreach ( $my_ccs AS $row )
					{
						$Num++;
						
						if ( $Num <= intval($this->request['st'] ) )
						{
							continue;
						}
						
						$row = $this->registry->ecoclass->formatRow($row, 'cc', false);
						$item_rows .= $this->registry->output->getTemplate('ibEconomy2')->my_items_row($row);
						
						$itemsShown++;
						
						if ( $itemsShown == $this->settings['eco_general_pp'] )
						{
							break;
						}					
					}
				}			

				#column titles
				$column_titles['two'] 	= $this->lang->words['my_credit_line'];
				$column_titles['three'] = $this->lang->words['apr'];
				$column_titles['four'] 	= $this->lang->words['balance_transfer'];
				$column_titles['five'] 	= $this->lang->words['cur_card_balance'];			
			
			break;

			case 'lts':
			
				#init lt vars
				$my_lts 	= array();
				
				#if I have some long-term investments in my cache, lets see how many and grab the data
				if ( is_array($portfolios[ $type ] ) )
				{ 		
					foreach ( $portfolios[ $type ] AS $row )
					{	
						$my_lts[] = $row;
						$total_items++;
					}
				}
				
				#process our long_terms for display
				if ( is_array( $my_lts ) )
				{
					foreach ( $my_lts AS $row )
					{
						$Num++;
						
						if ( $Num <= intval($this->request['st'] ) )
						{
							continue;
						}

						$row = $this->registry->ecoclass->formatRow($row, 'lt', false);					
						
						$item_rows .= $this->registry->output->getTemplate('ibEconomy2')->my_items_row($row);
						
						$itemsShown++;
						
						if ( $itemsShown == $this->settings['eco_general_pp'] )
						{
							break;
						}					
					}
				}			

				#column titles
				$column_titles['two'] 	= $this->lang->words['type'];
				$column_titles['three'] = $this->lang->words['sell_date'];
				$column_titles['four'] 	= $this->lang->words['early_cashout'];
				$column_titles['five'] 	= $this->lang->words['total_value'];				
			
			break;
			
			case 'shopitems':
			
				#init shopitem vars
				$my_shopitems 	= array();
				
				#if I have some long-term investments in my cache, lets see how many and grab the data
				if ( is_array($portfolios[ $type ] ) )
				{ 		
					foreach ( $portfolios[ $type ] AS $row )
					{							
						$my_shopitems[] = $row;
						$total_items++;
					}
				}
				
				#process our shopitems for display
				if ( is_array( $my_shopitems ) )
				{
					foreach ( $my_shopitems AS $row )
					{
						$Num++;
						
						if ( $Num <= intval($this->request['st'] ) )
						{
							continue;
						}

						$row = $this->registry->ecoclass->formatRow($row, 'shopitem', false);
						$item_rows .= $this->registry->output->getTemplate('ibEconomy2')->my_items_row($row);
						
						$itemsShown++;
						
						if ( $itemsShown == $this->settings['eco_general_pp'] )
						{
							break;
						}					
					}
				}			

				#column titles
				$column_titles['two'] 	= $this->lang->words['description'];
				$column_titles['three'] = $this->lang->words['can_sell'];
				$column_titles['four'] 	= $this->lang->words['Can_Trade'];
				$column_titles['five'] 	= $this->lang->words['Quantity'];				
			
			break;

			case 'loans':
			
				#init loan vars
				$my_loans = array();
				
				#if I have some long-term investments in my cache, lets see how many and grab the data
				if ( is_array($portfolios[ $type ] ) )
				{ 		
					foreach ( $portfolios[ $type ] AS $row )
					{							
						$my_loans[] = $row;
						$total_items++;
					}
				}
				
				#process our loan for display
				if ( is_array( $my_loans ) )
				{
					foreach ( $my_loans AS $row )
					{
						$Num++;
						
						if ( $Num <= intval($this->request['st'] ) )
						{
							continue;
						}

						$row = $this->registry->ecoclass->formatRow($row, 'loan', false);
						$item_rows .= $this->registry->output->getTemplate('ibEconomy2')->my_items_row($row);
						
						$itemsShown++;
						
						if ( $itemsShown == $this->settings['eco_general_pp'] )
						{
							break;
						}					
					}
				}			

				#column titles
				$column_titles['two'] 	= $this->lang->words['type'];
				$column_titles['three'] = $this->lang->words['apy'];
				$column_titles['four'] 	= $this->lang->words['due_date'];
				$column_titles['five'] 	= $this->lang->words['total_balance'];			
			
			break;			

			default:
				$this->registry->output->getTemplate('ibEconomy')->error( $this->lang->words['no_type'] );
			break;			
		}
		
		#I don't have any items of this type. :(
		if ( !$total_items )
		{
			$error = ( $error ) ? $error : str_replace( "<%TYPE%>", $this->lang->words[ $type ], $this->lang->words['none_in_port_to_show'] );
			$this->showPage = $this->registry->output->getTemplate('ibEconomy')->error($error);

			return $this->showPage;
		}		
		
		#page links
		$pages		= $this->registry->output->generatePagination( array(
					'totalItems'        => $total_items,
					'itemsPerPage'  	=> intval($this->settings['eco_general_pp']),
					'currentStartValue'	=> intval($this->request['st']),
					'baseUrl'           => "app=ibEconomy&amp;tab=me&amp;area=my_".$type,
		)      );	
		
		#throw rows to bank wrapper
		$item_list 		= $this->registry->output->getTemplate('ibEconomy2')->my_items_wrapper($item_rows, $column_titles, $pages);
		$this->showPage = $item_list;
		
		return $this->showPage;
	}
	
	/**
	 * Show my item (singular!)
	 */
	public function myItem($type, $id, $type_type)
	{	
		#init vars
		$item = "";
		
		switch ($type)
		{
			case 'bank':
				
				#init bank vars
				$bank 	= array();
				
				#portfolio cache enabled?
				if ( ! $this->settings['eco_general_cache_portfolio'] )
				{
					$bank = $this->registry->mysql_ibEconomy->grabSinglePortItemExtended( $this->memberData['member_id'], $id, 'bank', $type_type );
				}
				else
				{
					#if I have a checking/savings account by that ID in my cache, lets see how many and grab the data
					$bank = $this->caches['ibEco_portfolios'][ $this->memberData['member_id'] ]['banks_'.$type_type ][ $id ];
				}

				#process accounts for display
				if ( $bank )
				{
					$bankSpecs = $this->registry->ecoclass->formatRow($bank, 'bank', $type_type);
					$item = $this->registry->output->getTemplate('ibEconomy2')->myBank($bankSpecs);
				}		
			
			break;
			
			case 'stock':

				#init stock vars
				$stock 	= array();
				
				#portfolio cache enabled?
				if ( ! $this->settings['eco_general_cache_portfolio'] )
				{
					$stock = $this->registry->mysql_ibEconomy->grabSinglePortItemExtended( $this->memberData['member_id'], $id, 'stock' );
				}
				else
				{
					#if I have a stock in my cache by that Id, lets see how many and grab the data
					$stock = $this->caches['ibEco_portfolios'][ $this->memberData['member_id'] ]['stocks_'][ $id ];
				}
				
				#process stocks for display
				if ( $stock )
				{
					$stockSpecs = $this->registry->ecoclass->formatRow($stock, 'stock', false);
					$item = $this->registry->output->getTemplate('ibEconomy2')->myStock($stockSpecs);
				}			
			
			break;
			
			case 'cc':	

				#init lt vars
				$cc 	= array();

				#portfolio cache enabled?
				if ( ! $this->settings['eco_general_cache_portfolio'] )
				{
					$cc = $this->registry->mysql_ibEconomy->grabSinglePortItemExtended( $this->memberData['member_id'], $id, 'credit_card' );
				}
				else
				{
					#if I have a cc in my cache by that Id, lets see how many and grab the data
					$cc = $this->caches['ibEco_portfolios'][ $this->memberData['member_id'] ]['ccs_'][ $id ];
				}

				#process ccs for display
				if ( $cc )
				{
					$ccSpecs = $this->registry->ecoclass->formatRow($cc, 'cc', false);
					$item = $this->registry->output->getTemplate('ibEconomy2')->myCC($ccSpecs);
				}				
			
			break;

			case 'lt':	

				#init lt vars
				$lt 	= array();
				
				#portfolio cache enabled?
				if ( ! $this->settings['eco_general_cache_portfolio'] )
				{
					$lt = $this->registry->mysql_ibEconomy->grabSinglePortItemExtended( $this->memberData['member_id'], $id, 'long_term' );
				}
				else
				{
					#if I have a lt in my cache by that Id, lets see how many and grab the data
					$lt = $this->caches['ibEco_portfolios'][ $this->memberData['member_id'] ]['lts_'][ $id ];
				}

				#process lts for display
				if ( $lt )
				{
					$ltSpecs = $this->registry->ecoclass->formatRow($lt, 'lt', false);	
					$item = $this->registry->output->getTemplate('ibEconomy2')->myLT($ltSpecs);
				}
			
			break;			

			case 'shopitem':	

				#init shopitem vars
				$shopitem 	= array();
				
				#portfolio cache enabled?
				if ( ! $this->settings['eco_general_cache_portfolio'] )
				{
					$shopitem = $this->registry->mysql_ibEconomy->grabSinglePortItemExtended( $this->memberData['member_id'], $id, 'shop_item' );
				}
				else
				{
					#if I have a shopitem in my cache by that Id, lets see how many and grab the data
					$shopitem = $this->caches['ibEco_portfolios'][ $this->memberData['member_id'] ]['shopitems_'][ $id ];
				}

				#process shop items for display
				if ( $shopitem )
				{
					$shopitemSpecs = $this->registry->ecoclass->formatRow($shopitem, 'shopitem', false);
					$item = $this->registry->output->getTemplate('ibEconomy2')->myShopItem($shopitemSpecs);
				}				
			
			break;
			
			case 'loan':
				
				#init loan vars
				$loan 	= array();
				
				#portfolio cache enabled?
				if ( ! $this->settings['eco_general_cache_portfolio'] )
				{
					$loan = $this->registry->mysql_ibEconomy->grabSinglePortItemExtended( $this->memberData['member_id'], $id, 'bank', 'loan' );
				}
				else
				{
					#if I have a checking/savings account by that ID in my cache, lets see how many and grab the data
					$loan = $this->caches['ibEco_portfolios'][ $this->memberData['member_id'] ]['loans_' ][ $id ];
				}
	
				#process accounts for display
				if ( $loan )
				{
					$loanSpecs = $this->registry->ecoclass->formatRow($loan, 'loan', false);
					$item = $this->registry->output->getTemplate('ibEconomy2')->myLoan($loanSpecs);
				}		
			
			break;			

			default:
				$this->registry->output->getTemplate('ibEconomy')->error( $this->lang->words['no_type'] );
			break;			
		}
		
		#I don't have a portfolio item of this type and ID. :(
		if ( !$item )
		{
			$error = ( $error ) ? $error : str_replace( "<%TYPE%>", $this->lang->words[ $type ], $this->lang->words['none_found_show'] );
			$this->showPage = $this->registry->output->getTemplate('ibEconomy')->error($error);

			return $this->showPage;
		}
		
		#throw rows to wrapper
		$itemPage 		= $this->registry->output->getTemplate('ibEconomy2')->items_wrapper($item, $this->lang->words[ $type ]);
		$this->showPage = $itemPage;
		
		return $this->showPage;
	}
}