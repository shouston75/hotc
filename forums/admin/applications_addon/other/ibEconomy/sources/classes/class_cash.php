<?php

/**
 * (e32) ibEconomy
 * Cash Class
 * @ Quick Cash Tab
 */

if ( ! defined( 'IN_IPB' ) )
{
	print "<h1>Incorrect access</h1>You cannot access this file directly. If you have recently upgraded, make sure you upgraded all the relevant files.";
	exit();
}

class class_cash
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
	* Submit lottery picks
	*/
	public function submitLottoPicks()
	{
		$lottoPicks = array();
		
		#grab lottery
		$lotto = $this->grabALotto();
		
		if (!$lotto['l_id'])
		{
			$error = str_replace( "<%TYPE%>", $this->lang->words['lottery'], $this->lang->words['none_found_show'] );
			$this->showPage = $this->registry->output->getTemplate('ibEconomy')->error($error);

			return $this->showPage;
		}
		
		#permission and enabled?
		$this->registry->ecoclass->canAccess('lotterys', false);
				
		#format
		$lotto = $this->registry->ecoclass->formatRow($lotto, 'lottery', false);
		
		#user purchasing some tix?
		if ($lotto['your_num_tix'] > 0)
		{
			for ($tix = 1; $tix <= $lotto['your_num_tix']; $tix++)
			{
				$pickedNumbers = array();
				
				#want to input your own picks instead of random?
				if (!$this->request['use_random_nums'])
				{
					for ($balls = 1; $balls <= $lotto['l_num_balls']; $balls++)
					{
						#missed a spot?
						if (intval($this->request[$tix."_".$balls]) == 0 || intval($this->request[$tix."_".$balls]) > $lotto['l_top_num'])
						{
							$this->showPage = $this->registry->output->getTemplate('ibEconomy')->error($this->lang->words['no_lotto_num_picked']);
							return $this->showPage;					
						}
						
						#tried to pick the same number twice on once ticket?
						if (is_array($pickedNumbers) && count($pickedNumbers) && in_array($this->request[$tix."_".$balls], $pickedNumbers))
						{
							$this->showPage = $this->registry->output->getTemplate('ibEconomy')->error($this->lang->words['you_cant_pick_the_same_number_twice_on_a_single_ticket']);
							return $this->showPage;					
						}
						
						$pickedNumbers[] = $this->request[$tix."_".$balls];
						$lottoPicks[ $tix ][ $balls ] = $this->request[$tix."_".$balls];
					}
				}
				#you are so lazy!
				else
				{
					$lottoPicks[ $tix ] = $this->registry->ecoclass->generateRandomBallNumbers($lotto);
				}
			}
			
			//$this->registry->ecoclass->showVars($lottoPicks);
			
			$this->registry->mysql_ibEconomy->insertLottoTicketPicks($lottoPicks, $lotto);
		}
		else
		{
			$this->showPage = $this->registry->output->getTemplate('ibEconomy')->error($this->lang->words['you_dont_have_any_tickets_to_use']);
			return $this->showPage;		
		}
		
		#update cache
		$this->registry->ecoclass->acm('portfolios');
		$this->registry->ecoclass->acm('live_lotto');
		
		#redirect
		$redirect_message = str_replace( "<%AMOUNT%>", intval($lotto['your_num_tix']), $this->lang->words['lottery_tickets_picked'] );		
		$this->registry->output->redirectScreen( $redirect_message, $this->settings['base_url'] . "app=ibEconomy&amp;tab=cash&amp;area=single&amp;type=lottery&amp;id=".$lotto['l_id'] );			
	}
	
	/**
	 * Grab a single lottery
	 */
	public function grabALotto($l_id=0)	
	{
		#grab items from cache
		if ( $l_id == 0 && $this->caches['ibEco_live_lotto']['l_id'] > 0 || $this->caches['ibEco_live_lotto']['l_id'] == $l_id )
		{ 		
			$lotto = $this->caches['ibEco_live_lotto'];
		}
		else
		{
			$lotto = $this->registry->mysql_ibEconomy->queryLottos('single', $l_id);
		}

		return $lotto;
	}
	
	/**
	 * Show 1 Lottery
	 */
	public function singleLottery()
	{		
		#init var
		$lottoID 	= $this->request['id'];
		$myLottoTickets = array();
		$lottoBalls	= array();
		
		#grab lottery
		$lotto = $this->grabALotto($lottoID);

		if (!$lotto['l_id'])
		{
			$error = str_replace( "<%TYPE%>", $this->lang->words['lottery'], $this->lang->words['none_found_show'] );
			$this->showPage = $this->registry->output->getTemplate('ibEconomy')->error($error);

			return $this->showPage;
		}
		
		#permission and enabled?
		$this->registry->ecoclass->canAccess('lotterys', false);
				
		#format
		$lotto = $this->registry->ecoclass->formatRow($lotto, 'lottery', true);

		$itemPage 	= $this->registry->output->getTemplate('ibEconomy2')->item_row($lotto, True);
		$morePage   = $this->registry->ecoclass->grabMoreInfoHtmlForItem($lotto['l_id'], 'lottery');
		
		#throw rows to wrapper
		$this->showPage	 = $this->registry->output->getTemplate('ibEconomy2')->items_wrapper($itemPage, $this->lang->words['lottery']);
		$this->showPage	.= $morePage;
		
		return $this->showPage;
	}
	
	/**
	 * Show Lottery List
	 */
	public function lottery()
	{
		$total_possible = 0;
		$rowArr = array();

		#count lotteroes
		$total_possible = $this->registry->mysql_ibEconomy->countLottos();
				
		if ($total_possible > 0)
		{
			#grab lotteries
			$this->registry->mysql_ibEconomy->queryLottos('all', $ids=null, intval($this->request['st'] ));
			
			#get any to display?
			if ( $this->DB->getTotalRows() )
			{
				while ( $row = $this->DB->fetch() )
				{
					$rowArr[] = $row;
				}
				
				foreach($rowArr as $index => $row)
				{
					$rowArr[ $index ] = $this->registry->ecoclass->formatRow($row, 'lottery', false);
				}
			}
		}
		else
		{
			$error = str_replace( "<%TYPE%>", $this->lang->words['lotteries'], $this->lang->words['none_to_show'] );
			$this->showPage = $this->registry->output->getTemplate('ibEconomy')->error($error);

			return $this->showPage;
		}
		
		foreach($rowArr as $row)
		{
			$lotto_rows .= $this->registry->output->getTemplate('ibEconomy2')->item_row($row);	
		}
		
		#page links
		$pages		= $this->registry->output->generatePagination( array(
					'totalItems'        => $total_possible,
					'itemsPerPage'  	=> intval($this->settings['eco_general_pp']),
					'currentStartValue'	=> intval($this->request['st']),
					'baseUrl'           => "app=ibEconomy&amp;tab=cash&amp;area=lottery",
		)      );	
		
		// #throw rows to bank wrapper
		$lotto_list 	= $this->registry->output->getTemplate('ibEconomy2')->items_wrapper($lotto_rows, $this->lang->words['lotterys'], $pages);
		$this->showPage = $lotto_list;

		return 	$this->showPage;
	}
	
	/**
	 * Show Loan Page
	 */
	public function loans()
	{
		#init var
		$bank_rows 			= "";
		$permitted_banks 	= array();

		if ( is_array($this->caches['ibEco_banks']) )
		{
			#portfolio cache enabled?
			if ( !$this->settings['eco_general_cache_portfolio'] )
			{
				$myLoans = $this->registry->mysql_ibEconomy->grabMemBanksAndCCs( $this->memberData['member_id'], true );
			}
			
			foreach ( $this->caches['ibEco_banks'] AS $row )
			{	
				$preExistingLoan = ( !$this->settings['eco_general_cache_portfolio'] ) ? $myLoans[ $row['b_id'] ] : $this->caches['ibEco_portfolios'][ $this->memberData['member_id'] ]['loans_'][ $row['b_id'] ];
				
				if ( $row['b_loans_on'] && ! $preExistingLoan )
				{
					if ( !$row['b_use_perms'] || $this->registry->permissions->check( 'loans', $row ) )
					{
						$permitted_banks[] = $row['b_id'];
						$total_possible++;
					}
					else if ( !$error )
					{
						$error = str_replace( "<%TYPE%>", $this->lang->words['loans'], $this->lang->words['no_perm_to_loan'] );
					}
				}
			}
		}
			
		if ( !$total_possible )
		{
			$error = ( $error ) ? $error : str_replace( "<%TYPE%>", $this->lang->words['loans_opps'], $this->lang->words['none_to_show'] );
			$this->showPage = $this->registry->output->getTemplate('ibEconomy')->error($error);

			return $this->showPage;
		}

		#fix for hikari
		$rowArr = array();
		
		#grab all the banks, and count bank account holders and sum bank funds
		$this->registry->mysql_ibEconomy->queryBanks( 'all', $permitted_banks, $type='', intval($this->request['st']) );
		
		if ( $this->DB->getTotalRows() )
		{
			while ( $row = $this->DB->fetch() )
			{
				if ( $row['b_loans_on'] )
				{
					$row['b_loans_count']  			= $row['loaners'];
					$row['outstanding_loan_amt']	= $row['outstanding_loan_amt'];
					
					$row = $this->registry->ecoclass->formatRow($row, 'loan', false);
					
					$rowArr[] = $row;
				}
			}
		}		

		foreach($rowArr as $row)
		{
			$bank_rows .= $this->registry->output->getTemplate('ibEconomy2')->item_row($row);	
		}
		
		#page links
		$pages		= $this->registry->output->generatePagination( array(
					'totalItems'        => $total_possible,
					'itemsPerPage'  	=> intval($this->settings['eco_general_pp']),
					'currentStartValue'	=> intval($this->request['st']),
					'baseUrl'           => "app=ibEconomy&amp;tab=cash&amp;area=loans",
		)      );	
		
		#throw rows to bank wrapper
		$bank_list 		= $this->registry->output->getTemplate('ibEconomy2')->items_wrapper($bank_rows, $this->lang->words['loans'], $pages);
		$this->showPage = $bank_list;
		
		return $this->showPage;
	}

	/**
	 * Show Welfare Page
	 */
	public function welfare()
	{	
		if ( $this->memberData['eco_on_welfare'] )
		{
			$text = sprintf( $this->lang->words['youre_on_welfare_exp'], $this->settings['eco_general_currency'], $this->settings['eco_welfare_cycle'] );
			$this->showPage = $this->registry->output->getTemplate('ibEconomy')->error( " <img title = '{$this->lang->words['youre_on_welfare']}' src = '{$this->settings['img_url']}/accept.png' /> {$text}" );
			
			return $this->showPage;
		}
		
		$text = sprintf( $this->lang->words['youre_not_on_welfare_exp'], $this->settings['eco_general_currency'], $this->settings['eco_welfare_cycle'] );
		
		$this->showPage = $this->registry->output->getTemplate('ibEconomy2')->welfareApply($text);
				
		return $this->showPage;
	}

	/**
	 * Payback some of the welfare
	 */
	public function welfarePayment()
	{	
		#no welfare to pay...
		if ( ! $this->memberData['eco_welfare'] )
		{
			$this->showPage = $this->registry->output->getTemplate('ibEconomy')->error( $this->lang->words['no_welfare_to_pay_off'] );
			
			return $this->showPage;
		}
		
		#format payment amount
		$amount = $this->registry->ecoclass->makeNumeric($this->request['amount'], true);
		
		#no payment?
		if ( ! $amount )
		{
			$this->registry->output->showError( $this->lang->words['payment_amount_zero'] );
		}
		
		#payment over welfare received?
		if ( $amount > $this->memberData['eco_welfare'] )
		{
			$this->registry->output->showError( $this->lang->words['payment_over_welfare_received'] );
		}

		#paying more than you have in you have?
		if ( $this->memberData[ $this->settings['eco_general_pts_field'] ] < $amount )
		{
			$error = str_replace( "<%MY_POINTS%>", $this->registry->getClass('class_localization')->formatNumber( $this->memberData[ $this->settings['eco_general_pts_field'] ], $this->registry->ecoclass->decimal ), $this->lang->words['payment_too_high'] );
			$error = str_replace( "<%POINTS_NAME%>", $this->settings['eco_general_currency'], $error );
			
			$this->registry->output->showError( $error );						
		}
		
		#lower my welfare total!
		$newTotal = $this->memberData['eco_welfare'] - $amount;
		IPSMember::save( $this->memberData['member_id'], array( 'pfields_content' => array( 'eco_welfare' => $newTotal ) ) );
		
		#opening wallet..
		$this->registry->mysql_ibEconomy->updateMemberPts($this->memberData['member_id'], $amount, '-', true);
					
		#redirect
		$redirect_message = str_replace( "<%POINTS_NAME%>", $this->settings['eco_general_currency'], $this->lang->words['welfare_payment_made'] );
		$redirect_message = str_replace( "<%AMOUNT%>", $amount, $redirect_message );		
		$this->registry->output->redirectScreen( $redirect_message, $this->settings['base_url'] . "app=ibEconomy&amp;tab=me&amp;area=my_welfare" );		
	}	
	
	
	/**
	 * Apply for Welfare
	 */
	public function welfareApply()
	{
		#already on welfare?
		if ( $this->memberData['eco_on_welfare'] )
		{
			$text = sprintf( $this->lang->words['youre_on_welfare_exp'], $this->settings['eco_general_currency'], $this->settings['eco_welfare_cycle'] );
			$error = " <img title = '{$this->lang->words['youre_on_welfare']}' src = '{$this->settings['img_url']}/accept.png' /> {$text}";
			
			$this->registry->output->showError( $error );
		}

		#permission and enabled?
		$this->registry->ecoclass->canAccess('welfare', false);

		#signature not right?
		if ( $this->request['signature'] != $this->memberData['members_display_name'] )
		{
			$this->registry->output->showError( $this->lang->words['signature_no_match'] );
		}
		
		$error = $this->checkWelfareReqz();

		if ( $error )
		{
			$this->registry->output->showError( $error );
		}
		
		#sign em up!
		IPSMember::save( $this->memberData['member_id'], array( 'pfields_content' => array( 'eco_on_welfare' => 1 ) ) );
		
		#redirect
		$this->registry->output->redirectScreen( $this->lang->words['youre_signed_up_welfare'], $this->settings['base_url'] . "app=ibEconomy&amp;tab=me" );		
	}
	
	/**
	 * Apply for Welfare
	 */
	public function checkWelfareReqz()
	{
		#init
		$error 		= FALSE;
		$daysAsMem 	= (time() - $this->memberData['joined'])/86400;		
		
		#too many on hand pts to qualify?
		if ( $this->settings['eco_welfare_max_pts'] && $this->memberData[ $this->settings['eco_general_pts_field'] ] > $this->settings['eco_welfare_max_pts'] )
		{
			$error = sprintf( $this->lang->words['you_dont_qualify_for_welfare'], $this->lang->words['on_hand'].' '.$this->settings['eco_general_currency'], $this->registry->getClass('class_localization')->formatNumber( $this->settings['eco_welfare_max_pts'], $this->registry->ecoclass->decimal ).' '.$this->settings['eco_general_currency'] );
		}
		
		#too much worth to qualify?
		else if ( $this->settings['eco_welfare_max_worth'] && $this->memberData['eco_worth'] > $this->settings['eco_welfare_max_worth'] )
		{
			$error = sprintf( $this->lang->words['you_dont_qualify_for_welfare'], $this->lang->words['total_worth'], $this->registry->getClass('class_localization')->formatNumber( $this->settings['eco_welfare_max_worth'], $this->registry->ecoclass->decimal ).' '.$this->settings['eco_general_currency'] );
		}
		
		#too much previous welfare to qualify?
		else if ( !$error && $this->settings['eco_welfare_max_wf'] && $this->memberData['eco_welfare'] > $this->settings['eco_welfare_max_wf'] )
		{
			$error = sprintf( $this->lang->words['you_dont_qualify_for_welfare'], $this->lang->words['previous_welfare_amt'], $this->registry->getClass('class_localization')->formatNumber( $this->settings['eco_welfare_max_wf'], $this->registry->ecoclass->decimal ).' '.$this->settings['eco_general_currency'] );			
		}

		#not enough posts to qualify?
		else if ( $this->memberData['posts'] < $this->settings['eco_welfare_min_posts'] )
		{
			$error = sprintf( $this->lang->words['you_dont_qualify_for_welfare_min'], $this->lang->words['post_count'], $this->registry->getClass('class_localization')->formatNumber( $this->settings['eco_welfare_min_posts'] ).' '.$this->lang->words['posts'] );
		}

		#not enough posts per day to qualify?
		else if ( $this->memberData['posts']/$daysAsMem < $this->settings['eco_welfare_min_ppd'] )
		{
			$error = sprintf( $this->lang->words['you_dont_qualify_for_welfare_min'], $this->lang->words['posts_per_day_average'], $this->registry->getClass('class_localization')->formatNumber( $this->settings['eco_welfare_min_ppd'] ).' '.$this->lang->words['posts'] );
		}

		#not been a member long enough?
		else if ( $daysAsMem < $this->settings['eco_welfare_min_join'] )
		{
			$error = sprintf( $this->lang->words['you_dont_qualify_for_welfare_min'], $this->lang->words['time_as_mem'], $this->registry->getClass('class_localization')->formatNumber( $this->settings['eco_welfare_min_join'] ).' '.$this->lang->words['days'] );
		}

		return $error;
	}
		
}