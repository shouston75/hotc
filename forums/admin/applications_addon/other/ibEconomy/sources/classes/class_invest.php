<?php

/**
 * (e32) ibEconomy
 * Invest Class
 * @ Investments Tab
 * For Banks, credit-cards
 * stocks, long-terms, etc
 */

if ( ! defined( 'IN_IPB' ) )
{
	print "<h1>Incorrect access</h1>You cannot access this file directly. If you have recently upgraded, make sure you upgraded all the relevant files.";
	exit();
}

class class_invest
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
	 * Show those banks
	 */
	public function showBanks()
	{	
		#init var
		$bank_rows 			= "";
		$permitted_banks 	= array();

		if ( is_array($this->caches['ibEco_banks']) )
		{
			foreach ( $this->caches['ibEco_banks'] AS $row )
			{			
				if ( !$row['b_use_perms'] || $this->registry->permissions->check( 'view', $row ) )
				{
					$permitted_banks[] = $row['b_id'];
					$total_possible++;
				}
				else if ( !$error )
				{
					$error = str_replace( "<%TYPE%>", $this->lang->words['banks'], $this->lang->words['no_perm_to_show'] );
				}
			}
		}
			
		if ( !$total_possible )
		{
			$error = ( $error ) ? $error : str_replace( "<%TYPE%>", $this->lang->words['banks'], $this->lang->words['none_to_show'] );
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
				#damn 2 left joins...
				// $row['c_total']  = $row['c_total']/2;
				// $row['s_total']  = $row['s_total']/2;
				// $row['c_funds']  = $row['c_funds']/2;
				// $row['s_funds']  = $row['s_funds']/2;
				
				#savings
				if ( $row['b_savings_on'] )
				{
					$row = $this->registry->ecoclass->formatRow($row, 'bank', 'savings');
					$rowArr[] = $row;
				}
				#checking
				if ( $row['b_checking_on'] )
				{
					$row = $this->registry->ecoclass->formatRow($row, 'bank', 'checking');
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
					'baseUrl'           => "app=ibEconomy&amp;tab=invest&amp;area=banks",
		)      );	
		
		// #throw rows to bank wrapper
		$bank_list 		= $this->registry->output->getTemplate('ibEconomy2')->items_wrapper($bank_rows, $this->lang->words['banks'], $pages);
		$this->showPage = $bank_list;
		
		return $this->showPage;
	}
	
	/**
	 * Show those stocks
	 */
	public function showStocks()
	{	
		#init var
		$stock_rows 		= "";
		$permitted_stocks	 = array();
		
		if ( is_array($this->caches['ibEco_stocks']) )
		{
			foreach ( $this->caches['ibEco_stocks'] AS $row )
			{
				if ( !$row['s_use_perms'] || $this->registry->permissions->check( 'view', $row ) )
				{
					$permitted_stocks[] = $row['s_id'];
					$total_possible++;
				}
				else if ( !$error )
				{
					$error = str_replace( "<%TYPE%>", $this->lang->words['stocks'], $this->lang->words['no_perm_to_show'] );
				}
			}
		}

		if ( !$total_possible )
		{
			$error = ( $error ) ? $error : str_replace( "<%TYPE%>", $this->lang->words['stocks'], $this->lang->words['none_to_show'] );
			$this->showPage = $this->registry->output->getTemplate('ibEconomy')->error($error);

			return $this->showPage;
		}

		#grab all the stocks, and count share holders and sum share values
		$stocksQuery = $this->registry->mysql_ibEconomy->queryStocks( 'all', $permitted_stocks, $type='', intval($this->request['st']) );
		
		#fix for hikari
		$rowArr = array();
		
		if ( $this->DB->getTotalRows($stocksQuery) )
		{
			while ( $row = $this->DB->fetch($stocksQuery) )
			{
				$row = $this->registry->ecoclass->formatRow($row, 'stock', false);
				$rowArr[] = $row;
			}
		}
		
		foreach($rowArr as $row)
		{
			$stock_rows .= $this->registry->output->getTemplate('ibEconomy2')->item_row($row);	
		}
		
		#page links
		$pages		= $this->registry->output->generatePagination( array(
					'totalItems'        => $total_possible,
					'itemsPerPage'  	=> intval($this->settings['eco_general_pp']),
					'currentStartValue'	=> intval($this->request['st']),
					'baseUrl'           => "app=ibEconomy&amp;tab=invest&amp;area=stocks",
		)      );	
		
		// #throw rows to bank wrapper
		$stock_list 	= $this->registry->output->getTemplate('ibEconomy2')->items_wrapper($stock_rows, $this->lang->words['stocks'], $pages);
		$this->showPage = $stock_list;

		return 	$this->showPage;
	}
	
	/**
	 * Show those credit-cards
	 */
	public function showCCs()
	{	
		#init var
		$cc_rows 			= "";
		$permitted_ccs	 	= array();

		if ( is_array($this->caches['ibEco_ccs']) )
		{
			foreach ( $this->caches['ibEco_ccs'] AS $row )
			{			
				if ( !$row['cc_use_perms'] || $this->registry->permissions->check( 'view', $row ) )
				{
					$permitted_ccs[] = $row['cc_id'];
					$total_possible++;
				}
				else if ( !$error )
				{
					$error = str_replace( "<%TYPE%>", $this->lang->words['credit_cards'], $this->lang->words['no_perm_to_show'] );
				}
			}
		}

		if ( !$total_possible )
		{
			$error = ( $error ) ? $error : str_replace( "<%TYPE%>", $this->lang->words['credit_cards'], $this->lang->words['none_to_show'] );
			$this->showPage = $this->registry->output->getTemplate('ibEconomy')->error($error);

			return $this->showPage;
		}

		#fix for hikari
		$rowArr = array();
				
		#grab all the ccs, and count account holders and sum cc balances
		$this->registry->mysql_ibEconomy->queryCCs( 'all', $permitted_ccs, $type='', intval($this->request['st']) );

		if ( $this->DB->getTotalRows() )
		{
			while ( $row = $this->DB->fetch() )
			{
				$row = $this->registry->ecoclass->formatRow($row, 'cc', false);
				$rowArr[] = $row;
			}
		}

		foreach($rowArr as $row)
		{
			$cc_rows .= $this->registry->output->getTemplate('ibEconomy2')->item_row($row);	
		}				
		
		#page links
		$pages		= $this->registry->output->generatePagination( array(
					'totalItems'        => $total_possible,
					'itemsPerPage'  	=> intval($this->settings['eco_general_pp']),
					'currentStartValue'	=> intval($this->request['st']),
					'baseUrl'           => "app=ibEconomy&amp;tab=invest&amp;area=ccs",
		)      );	
		
		// #throw rows to bank wrapper
		$cc_list 		= $this->registry->output->getTemplate('ibEconomy2')->items_wrapper($cc_rows, $this->lang->words['ccs'], $pages);
		$this->showPage = $cc_list;
		
		return $this->showPage;
	}	

	/**
	 * Show those long-terms
	 */
	public function showLTs()
	{	
		#init var
		$lt_rows 		= "";
		$permitted_lts	= array();

		if ( is_array($this->caches['ibEco_lts']) )
		{
			foreach ( $this->caches['ibEco_lts'] AS $row )
			{			
				if ( !$row['lt_use_perms'] || $this->registry->permissions->check( 'view', $row ) )
				{
					$permitted_lts[] = $row['lt_id'];
					$total_possible++;
				}
				else if ( !$error )
				{
					$error = str_replace( "<%TYPE%>", $this->lang->words['long_term_inv'], $this->lang->words['no_perm_to_show'] );
				}
			}
		}

		if ( !$total_possible )
		{
			$error = ( $error ) ? $error : str_replace( "<%TYPE%>", $this->lang->words['long_term_inv'], $this->lang->words['none_to_show'] );
			$this->showPage = $this->registry->output->getTemplate('ibEconomy')->error($error);

			return $this->showPage;
		}
		
		#fix for hikari
		$rowArr = array();
				
		#grab all the long-terms, and count investors and sum investment value
		$this->registry->mysql_ibEconomy->queryLTs( 'all', $permitted_lts, $type='', intval($this->request['st']) );

		if ( $this->DB->getTotalRows() )
		{
			while ( $row = $this->DB->fetch() )
			{
				$row = $this->registry->ecoclass->formatRow($row, 'lt', false);
				$rowArr[] = $row;
			}
		}	

		foreach($rowArr as $row)
		{
			$lt_rows .= $this->registry->output->getTemplate('ibEconomy2')->item_row($row);	
		}			
		
		#page links
		$pages		= $this->registry->output->generatePagination( array(
					'totalItems'        => $total_possible,
					'itemsPerPage'  	=> intval($this->settings['eco_general_pp']),
					'currentStartValue'	=> intval($this->request['st']),
					'baseUrl'           => "app=ibEconomy&amp;tab=invest&amp;area=lts",
		)      );	
		
		// #throw rows to bank wrapper
		$stock_list 	= $this->registry->output->getTemplate('ibEconomy2')->items_wrapper($lt_rows, $this->lang->words['lts'], $pages);
		$this->showPage = $stock_list;

		return 	$this->showPage;
	}

	/**
	 * Show single stock
	 */
	public function stock($stock_id)
	{	
		$stock = $this->grabAStock($stock_id);
		
		if ( !$stock['s_id'] )
		{
			$error 		= str_replace( "<%TYPE%>", $this->lang->words['stock'], $this->lang->words['none_found_show'] );
			$this->showPage = $this->registry->output->getTemplate('ibEconomy')->error($error);
			
			return $this->showPage;
		}
		
		if ( $stock['s_use_perms'] && !$this->registry->permissions->check( 'view', $stock ) )
		{
			$error = str_replace( "<%TYPE%>", $this->lang->words['stock'], $this->lang->words['no_perm_to_show_it'] );
			$this->showPage = $this->registry->output->getTemplate('ibEconomy')->error($error);

			return $this->showPage;			
		}		
		
		$stock_row = $this->registry->output->getTemplate('ibEconomy2')->item_row($stock);
		
		#throw stock to the wrapper
		$stock_list 	= $this->registry->output->getTemplate('ibEconomy2')->items_wrapper($stock_row, $stock['s_title']);
		$this->showPage = $stock_list;

		return 	$this->showPage;
	}

	/**
	 * Show single bank
	 */
	public function bank($bank_id,$type)
	{	
		#init
		$type = ( $type ) ? $type : 'both';
		$bank = $this->grabABank($bank_id,$type);
		$permCheckType = ( $type != 'Loan' ) ? 'view' : 'loans';
							
		if ( !$bank['b_id'] )
		{
			$error 		= str_replace( "<%TYPE%>", $this->lang->words['bank'], $this->lang->words['none_found_show'] );
			$this->showPage = $this->registry->output->getTemplate('ibEconomy')->error($error);
			
			return $this->showPage;
		}
		
		if ( $bank['b_use_perms'] && !$this->registry->permissions->check( $permCheckType, $bank ) )
		{
			$error = str_replace( "<%TYPE%>", $this->lang->words['bank'], $this->lang->words['no_perm_to_show_it'] );
			$this->showPage = $this->registry->output->getTemplate('ibEconomy')->error($error);

			return $this->showPage;			
		}
		
		$bank['b_loans_count']  		= $bank['loaners'];
		$bank['outstanding_loan_amt']  	= $bank['outstanding_loan_amt'];		
		
		$bank_row = ( $type != 'Loan' ) ? $this->registry->output->getTemplate('ibEconomy2')->item_row($bank) : $this->registry->output->getTemplate('ibEconomy2')->item_row($bank);
					
		#throw to the wrapper
		$bank_list 	= $this->registry->output->getTemplate('ibEconomy2')->items_wrapper($bank_row, $bank['b_title']);
		$this->showPage = $bank_list;

		return 	$this->showPage;
	}

	/**
	 * Show single credit-card
	 */
	public function cc($cc_id)
	{	
		$cc = $this->grabACC($cc_id);
		
		if ( !$cc['cc_id'] )
		{
			$error 		= str_replace( "<%TYPE%>", $this->lang->words['cc'], $this->lang->words['none_found_show'] );
			$this->showPage = $this->registry->output->getTemplate('ibEconomy')->error($error);
			
			return $this->showPage;
		}
		
		if ( $cc['cc_use_perms'] && !$this->registry->permissions->check( 'view', $cc ) )
		{
			$error = str_replace( "<%TYPE%>", $this->lang->words['cc'], $this->lang->words['no_perm_to_show_it'] );
			$this->showPage = $this->registry->output->getTemplate('ibEconomy')->error($error);

			return $this->showPage;			
		}
		
		$cc_row = $this->registry->output->getTemplate('ibEconomy2')->item_row($cc);
		
		#throw to the wrapper
		$cc_list 	= $this->registry->output->getTemplate('ibEconomy2')->items_wrapper($cc_row, $cc['cc_title']);
		$this->showPage = $cc_list;

		return 	$this->showPage;
	}

	/**
	 * Show single long-term investment
	 */
	public function lt($lt_id)
	{	
		$lt = $this->grabAnLT($lt_id);
		
		if ( !$lt['lt_id'] )
		{
			$error 		= str_replace( "<%TYPE%>", $this->lang->words['lt'], $this->lang->words['none_found_show'] );
			$this->showPage = $this->registry->output->getTemplate('ibEconomy')->error($error);
			
			return $this->showPage;
		}
		
		if ( $lt['lt_use_perms'] && !$this->registry->permissions->check( 'view', $lt ) )
		{
			$error = str_replace( "<%TYPE%>", $this->lang->words['lt'], $this->lang->words['no_perm_to_show_it'] );
			$this->showPage = $this->registry->output->getTemplate('ibEconomy')->error($error);

			return $this->showPage;
		}		
		
		$lt_row = $this->registry->output->getTemplate('ibEconomy2')->item_row($lt);
		
		#throw to the wrapper
		$lt_list 	= $this->registry->output->getTemplate('ibEconomy2')->items_wrapper($lt_row, $lt['lt_title']);
		$this->showPage = $lt_list;

		return 	$this->showPage;
	}
	
	/**
	 * Grab a Bank
	 */
	public function grabABank($bank_id,$type)	
	{
		#grab the bank, and count share holders and sum share values
		$bank = $this->caches['ibEco_banks'][$bank_id];
		
		#format the array vals
		if ( $bank['b_savings_on'] && $type == 'Savings' )
		{
			$bank = $this->registry->ecoclass->formatRow($bank, 'bank', 'savings');
		}
		else if ( $bank['b_checking_on'] && $type == 'Checking' )
		{
			$bank = $this->registry->ecoclass->formatRow($bank, 'bank', 'checking');
		}
		else if ( $bank['b_loans_on'] && $type == 'Loan' )
		{
			$bank = $this->registry->ecoclass->formatRow($bank, 'loan', false);
		}
		
		return $bank;		
	}	
	
	/**
	 * Grab a Stock
	 */
	public function grabAStock($stock_id)	
	{
		#grab the stock and count share holders and sum share values
		$stock = $this->caches['ibEco_stocks'][$stock_id];
		
		#make values pretty
		$stock = $this->registry->ecoclass->formatRow($stock, 'stock', false);
		return $stock;
	}	
	
	/**
	 * Grab a Credit-Card
	 */
	public function grabACC($cc_id)	
	{
		#grabthe cc and count share holders and sum share values
		$cc = $this->caches['ibEco_ccs'][$cc_id];		
		
		#format the array vals
		$cc = $this->registry->ecoclass->formatRow($cc, 'cc', false);
		return $cc;
	}
	
	/**
	 * Grab a Long-Term Investment
	 */
	public function grabAnLT($lt_id)	
	{
		#grab the lt, and count investors  and sum investment value
		$lt = $this->caches['ibEco_lts'][$lt_id];			
		
		#format the array vals
		$lt = $this->registry->ecoclass->formatRow($lt, 'lt', false);
		return $lt;
	}
		
}