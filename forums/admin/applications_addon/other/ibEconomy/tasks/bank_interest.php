<?php

/**
 * (e32) ibEconomy
 * Task: Bank Interest
 * + Adjust bank balances for interest
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
			$bankPorts = $this->registry->mysql_ibEconomy->grabPortfolioItemsByType( 'bank', 0, false, true );
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
		$needsInterestAccts = array();
		$needsInterestPeeps = array();
		$needsInterestP_ids = array();
		
		#shall we get to tasking?
		if ( $this->settings['eco_banks_on'] && is_array( $bankPorts ) && count( $bankPorts ) )
		{
			foreach ( $bankPorts AS $portBank )
			{	
				#skip if not savings acct
				if ( $portBank['p_type_class'] != 'savings' )
				{
					continue;
				}
				
				#skip if 0 balance
				if ( $portBank['p_amount'] == 0 )
				{
					continue;
				}				
				
				#skip if item off/deleted and admin wants us to skip those
				if ( !$this->settings['eco_interest_on4off'] && !$portBank['b_on'] )
				{
					continue;
				}
				
				#skip if done more recently than admin wants em to be run
				$lastChange = ( $portBank['p_last_hit'] ) ? $portBank['p_last_hit'] : $portBank['p_purch_date'];
				
				if ( $this->settings['eco_banks_cycle'] * 86400 > time() - $lastChange )
				{
					continue;
				}				
				
				#anything left needs interst!
				$needsInterestAccts[] 	= $portBank;
				$needsInterestPeeps[] 	= $portBank['p_member_id'];
			}		
		}

		#need to run member checks?
		if ( is_array( $needsInterestPeeps ) and count( $needsInterestPeeps ) and in_array('bank', explode(',', $this->settings['eco_interest_restrict_on']) ) )
		{
			#query for savings account holders to check against interest restrictions
			$memStats = $this->registry->mysql_ibEconomy->getPpdAndActivity($needsInterestPeeps);		
		}
		
		#member pass above tests?
		if ( is_array( $memStats ) and count( $memStats ) )
		{		
			#loop through accounts
			foreach ( $needsInterestAccts AS $key => $acct )
			{
				#not over ppd min?
				if ( $this->settings['eco_interest_restrict_ppd'] && $memStats['ppdstats'][ $acct['p_member_id'] ] < $this->settings['eco_interest_restrict_ppd'] )
				{
					unset($needsInterestAccts[ $key ]);
					continue;
				}
				
				#not over last activity min?
				if ( $this->settings['eco_interest_restrict_act'] && $memStats['actStats'][ $acct['p_member_id'] ] < time() - $this->settings['eco_interest_restrict_act'] * 86400 )
				{
					unset($needsInterestAccts[ $key ]);
					continue;
				}
			}
		}

		#finally pay up!
		if ( is_array( $needsInterestAccts ) and count ( $needsInterestAccts ) )
		{
			#which pids are we updating?
			foreach ( $needsInterestAccts AS $pidAcct )
			{
				$needsInterestP_ids[] = $pidAcct['p_id'];
			}
			
			#add interest to balances
			$this->registry->mysql_ibEconomy->adjustPortAmt4Interest($needsInterestP_ids, '+', time(), 0);

			#update portfolio
			$this->registry->ecoclass->acm('portfolios');
		}
		
		//-----------------------------------------
		// Log to log table - modify but dont delete
		//-----------------------------------------
		
		$this->class->appendTaskLog( $this->task, $this->lang->words['bank_interest_added'] );
		
		//-----------------------------------------
		// Unlock Task: DO NOT MODIFY!
		//-----------------------------------------
		
		$this->class->unlockTask( $this->task );
	}
}