<?php

/**
 * (e32) ibEconomy
 * Task: Long-Terms
 * + Adjust Long-Term Invested Values
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
		
		#need lt cache?
		if( !$this->caches['ibEco_lts'] )
		{
			$this->caches['ibEco_lts'] = $this->cache->getCache('ibEco_lts');
		}			
		
		#portfolio cache enabled?
		if ( ! $this->settings['eco_general_cache_portfolio'] )
		{
			$ltPorts = $this->registry->mysql_ibEconomy->grabPortfolioItemsByType( 'lt', 0, false, true );
		}
		else
		{
			#need port cache?
			if( !$this->caches['ibEco_portfolios'] )
			{
				$this->caches['ibEco_portfolios'] = $this->cache->getCache('ibEco_portfolios');
			}
		
			#init cached lt portfolio assets
			$ltPorts = $this->caches['ibEco_portfolios']['lts'];
		}

		#init
		$needsAdjustments 		= array();
		$needsAdjustmentsPeeps 	= array();
		$multipliers 			= array();
		
		#init cached lt portfolio assets
		$longTerms 				= $this->caches['ibEco_lts'];
		
		#shall we get to tasking?
		if ( $this->settings['eco_lts_on'] && is_array( $ltPorts ) && count( $ltPorts ) )
		{
			foreach ( $ltPorts AS $portLT )
			{	
				#skip if 0 balance
				if ( $portLT['p_amount'] == 0 )
				{
					continue;
				}
				
				#skip if item off/deleted and admin wants us to skip those
				if ( !$this->settings['eco_interest_on4off'] && !$portLT['lt_on'] )
				{
					continue;
				}
				
				#skip if done more recently than admin wants em to be run
				$lastChange = ( $portLT['p_last_hit'] ) ? $portLT['p_last_hit'] : $portLT['p_purch_date'];
				
				if ( $this->settings['eco_lts_cycle'] * 86400 > time() - $lastChange )
				{
					continue;
				}				
				
				#anything left needs interst!
				$needsAdjustments[] 		= $portLT;
				$needsAdjustmentsPeeps[] 	= $portLT['p_member_id'];
			}		
		}

		#need to run member checks?
		if ( is_array( $needsAdjustmentsPeeps ) and count( $needsAdjustmentsPeeps ) and in_array('lt', explode(',', $this->settings['eco_interest_restrict_on']) ) )
		{
			#query for savings account holders to check against interest restrictions
			$memStats = $this->registry->mysql_ibEconomy->getPpdAndActivity($needsAdjustmentsPeeps);		
		}	
		
		#member pass above tests?
		if ( is_array( $memStats ) and count( $memStats ) )
		{		
			#loop through accounts
			foreach ( $needsAdjustments AS $key => $acct )
			{
				#not over ppd min?
				if ( $this->settings['eco_interest_restrict_ppd'] && $memStats['ppdstats'][ $acct['p_member_id'] ] < $this->settings['eco_interest_restrict_ppd'] )
				{
					unset($needsAdjustments[ $key ]);
					continue;
				}
				
				#not over last activity min?
				if ( $this->settings['eco_interest_restrict_act'] && $memStats['actStats'][ $acct['p_member_id'] ] < time() - $this->settings['eco_interest_restrict_act'] * 86400 )
				{
					unset($needsAdjustments[ $key ]);
					continue;
				}
			}
		}

		#finally pay up!
		if ( is_array( $needsAdjustments ) and count ( $needsAdjustments )  and is_array( $longTerms  ) and count ( $longTerms  ) )
		{
			#create value shifts
			foreach ( $longTerms AS $LT )
			{
				#generate random integer between admin choosen and 10
				$randomNum = rand($this->settings['eco_lts_curve'], 10);				
				
				#getting paid?
				$multipliers[ $LT['lt_id'] ] = ($randomNum * $LT['lt_risk_level'])/1000;
			}

			#which pids are we updating?
			foreach ( $needsAdjustments AS $lt_idAcct )
			{
				#whats the damage?
				$multipliers[ $lt_idAcct['p_type_id'] ] = ( $multipliers[ $lt_idAcct['p_type_id'] ] ) ? $multipliers[ $lt_idAcct['p_type_id'] ] : 0.1;
				$newBalance = $lt_idAcct['p_amount'] + ($lt_idAcct['p_amount'] * $multipliers[ $lt_idAcct['p_type_id'] ]);
				$newBalance = ( $newBalance > 0 ) ? $newBalance : 1;
				
				#update investment balance accordingly
				$this->registry->mysql_ibEconomy->adjustPortAmt4SpecNum($lt_idAcct['p_id'], $newBalance, time());
			}

			#update portfolio
			$this->registry->ecoclass->acm('portfolios');
		}

		//-----------------------------------------
		// Log to log table - modify but dont delete
		//-----------------------------------------
		
		$this->class->appendTaskLog( $this->task, $this->lang->words['lt_value_added'] );
		
		//-----------------------------------------
		// Unlock Task: DO NOT MODIFY!
		//-----------------------------------------
		
		$this->class->unlockTask( $this->task );
	}
}