<?php

/**
 * (e32) ibEconomy
 * Task: Loan Late-fees
 * + Remove Charge loan penalties
 */

if ( ! defined( 'IN_IPB' ) )
{
	print "<h1>Incorrect access</h1>You cannot access this file directly. If you have recently upgraded, make sure you upgraded all the relevant files.";
	exit();
}

class task_item
{
	/**
	 * Parent task manager class
	 */
	protected $class;

	/**
	 * This task data
	 */
	protected $task			= array();

	/**
	 * Prevent logging
	 */
	protected $restrict_log	= false;
	
	/**#@+
	 * Registry Object Shortcuts
	 */
	protected $registry;
	protected $DB;
	protected $settings;
	protected $request;
	protected $lang;
	protected $member;
	protected $cache;
	/**#@-*/
	
	/**
	 * Constructor
	 */
	public function __construct( ipsRegistry $registry, $class, $task )	
	{
		/* Make registry objects */
		$this->registry	= $registry;
		$this->DB		= $this->registry->DB();
		$this->settings =& $this->registry->fetchSettings();
		$this->request  =& $this->registry->fetchRequest();
		$this->lang		= $this->registry->getClass('class_localization');
		$this->member	= $this->registry->member();
		$this->memberData =& $this->registry->member()->fetchMemberData();
		$this->cache	= $this->registry->cache();
		$this->caches   =& $this->registry->cache()->fetchCaches();

		$this->class	= $class;
		$this->task		= $task;
	}
	
	/**
	 * Run this task
	 *
	 * @access	public
	 * @return	void
	 */
	public function runTask()
	{
		#ibEconomy not on?
		if ( ! $this->settings['eco_general_on'] )
		{
			$this->class->unlockTask( $this->task );
			return;
		}
		
		#load lang
		ipsRegistry::getClass('class_localization')->loadLanguageFile( array( 'admin_ibEconomy' ), 'ibEconomy' );

		#master ibEconomy SQL Queries
		if ( ! $this->registry->isClassLoaded( 'mysql_ibEconomy' ) )
		{
			require_once( IPSLib::getAppDir( 'ibEconomy' ) . "/sql/mysql_ibEconomy.php" );
			$this->registry->setClass( 'mysql_ibEconomy', new ibEconomyMySQL( $this->registry ) );
		}

		#master ibEconomy Class
		if ( ! $this->registry->isClassLoaded( 'ecoclass' ) )
		{
			require_once( IPSLib::getAppDir( 'ibEconomy' ) . "/sources/ecoclass.php" );
			$this->registry->setClass( 'ecoclass', new class_ibEconomy( $this->registry ) );
		}	
		
		#portfolio cache enabled?
		if ( ! $this->settings['eco_general_cache_portfolio'] )
		{
			$bankPorts = $this->registry->mysql_ibEconomy->grabPortfolioItemsByType( 'bank', 0, false, true, 'loan' );
		}
		else
		{
			#need port cache?
			if( !$this->caches['ibEco_portfolios'] )
			{
				$this->caches['ibEco_portfolios'] = $this->cache->getCache('ibEco_portfolios');
			}
		
			#init cached bank portfolio assets
			$bankPorts = $this->caches['ibEco_portfolios']['banks'];
		}

		#init
		$needsPenaltyAccts = array();
		$needsPenaltyPeeps = array();

		#shall we get to tasking?
		if ( $this->settings['eco_loans_on'] && is_array( $bankPorts ) && count( $bankPorts ) )
		{
			foreach ( $bankPorts AS $portBank )
			{	
				#skip if not savings acct
				if ( $portBank['p_type_class'] != 'loan' )
				{
					continue;
				}
				
				#skip if 0 balance
				if ( $portBank['p_amount'] == 0 )
				{
					continue;
				}				
				
				#skip if item off/deleted and admin wants us to skip those
				if ( !$this->settings['eco_interest_on4off'] && !$portBank['b_loans_on'] )
				{
					continue;
				}
				
				#skip if done more recently than admin wants em to be run
				$lastChange = ( $portBank['p_last_hit'] ) ? $portBank['p_last_hit'] : $portBank['p_purch_date'];
				
				if ( $this->settings['eco_loans_pen_rate'] * 86400 > time() - $lastChange )
				{
					continue;
				}				
				
				#anything left needs interst!
				$needsPenaltyAccts[] 	= $portBank;
				$needsPenaltyPeeps[] 	= $portBank['p_member_id'];
			}		
		}

		#need to run member checks?
		if ( is_array( $needsPenaltyPeeps ) and count( $needsPenaltyPeeps ) and in_array('loan', explode(',', $this->settings['eco_interest_restrict_on']) ) )
		{
			#query for loan holders to check against interest restrictions
			$memStats = $this->registry->mysql_ibEconomy->getPpdAndActivity($needsPenaltyPeeps);		
		}
		
		#member pass above tests?
		if ( is_array( $memStats ) and count( $memStats ) )
		{		
			#loop through accounts
			foreach ( $needsPenaltyAccts AS $key => $acct )
			{
				#not over last activity min?
				if ( $this->settings['eco_interest_restrict_act'] && $memStats['actStats'][ $acct['p_member_id'] ] < time() - $this->settings['eco_interest_restrict_act'] * 86400 )
				{
					unset($needsPenaltyAccts[ $key ]);
					continue;
				}
			}
		}

		#finally pay up!
		if ( is_array( $needsPenaltyAccts ) and count ( $needsPenaltyAccts ) )
		{
			#which pids are we updating?
			foreach ( $needsPenaltyAccts AS $pidAcct )
			{
				#not the shoes
				$newBalance = $pidAcct['p_amount'] + ($pidAcct['p_amount'] * ($pidAcct['b_loans_pen']/100));
				
				#update investment balance accordingly
				$this->registry->mysql_ibEconomy->adjustPortAmt4SpecNum($pidAcct['p_id'], $newBalance, time());			
			}

			#update portfolio
			$this->registry->ecoclass->acm('portfolios');
		}

		//-----------------------------------------
		// Log to log table - modify but dont delete
		//-----------------------------------------
		
		$this->class->appendTaskLog( $this->task, $this->lang->words['loan_penalty_added'] );
		
		//-----------------------------------------
		// Unlock Task: DO NOT MODIFY!
		//-----------------------------------------
		
		$this->class->unlockTask( $this->task );
	}
}