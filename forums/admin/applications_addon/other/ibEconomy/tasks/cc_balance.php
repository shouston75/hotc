<?php

/**
 * (e32) ibEconomy
 * Task: Credit-Card Balance
 * + Account for interest/late-fees for credit-cards
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
			$ccPorts = $this->registry->mysql_ibEconomy->grabPortfolioItemsByType( 'cc', 0, false, true );
		}
		else
		{
			#need port cache?
			if( !$this->caches['ibEco_portfolios'] )
			{
				$this->caches['ibEco_portfolios'] = $this->cache->getCache('ibEco_portfolios');
			}
		
			#init cached credit-cards portfolio assets
			$ccPorts = $this->caches['ibEco_portfolios']['ccs'];
		}

		#init
		$needsInterestAccts = array();
		$needsInterestPeeps = array();
		$needsInterestP_ids = array();
		$missedPaymentsCrds	= array();

		#shall we get to tasking?
		if ( $this->settings['eco_ccs_on'] && is_array( $ccPorts ) && count( $ccPorts ) )
		{
			foreach ( $ccPorts AS $portCC )
			{	
				#no balance?
				if ( $portCC['p_amount'] == 0 )
				{
					continue;
				}
				
				#skip if item off/deleted and admin wants us to skip those
				if ( !$this->settings['eco_interest_on4off'] && !$portCC['cc_on'] )
				{
					continue;
				}
				
				#skip if done more recently than admin wants em to be run
				$lastChange = ( $portCC['p_last_hit'] ) ? $portCC['p_last_hit'] : $portCC['p_purch_date'];
				
				if ( $this->settings['eco_ccs_cycle'] * 86400 > time() - $lastChange )
				{
					continue;
				}				
				
				#anything left needs interst!
				$needsInterestAccts[] 	= $portCC;
				$needsInterestPeeps[] 	= $portCC['p_member_id'];
				
				#missed a payemnt?
				if ( $portCC['p_update_date'] <= $lastChange )
				{
					$missedPaymentsCrds[] 	= $portCC;
				}
			}		
		}

		#need to run member checks?
		if ( is_array( $needsInterestPeeps ) and count( $needsInterestPeeps ) and in_array('cc', explode(',', $this->settings['eco_interest_restrict_on']) ) )
		{
			#query for savings account holders to check against interest restrictions
			$memStats = $this->registry->mysql_ibEconomy->getPpdAndActivity($needsInterestPeeps);		
		}
		
		#member pass above tests?
		if ( is_array( $memStats ) and count( $memStats ) )
		{
			#loop through cards
			foreach ( $needsInterestAccts AS $key => $acct )
			{
				#not over last activity min?
				if ( $this->settings['eco_interest_restrict_act'] && $memStats['actStats'][ $acct['p_member_id'] ] < time() - $this->settings['eco_interest_restrict_act'] * 86400 )
				{
					unset($needsInterestAccts[ $key ]);
					continue;
				}
			}			
		}
		
		#finally add interest up!
		if ( is_array( $needsInterestAccts ) and count ( $needsInterestAccts ) )
		{
			#need to recache later?
			$recache = TRUE;
			
			#which pids are we updating?
			foreach ( $needsInterestAccts AS $pidCard )
			{
				$needsInterestP_ids[] = $pidCard['p_id'];
			}
			
			#add APR!
			$this->registry->mysql_ibEconomy->adjustPortAmt4Interest($needsInterestP_ids, '+', time(), 0);
		}

		#any missed payments?
		if ( is_array( $missedPaymentsCrds ) and count ( $missedPaymentsCrds ) )
		{
			#which pids are we updating?
			foreach ( $missedPaymentsCrds AS $pidCard )
			{
				if ( $pidCard['cc_no_pay_chrg'] > 0 )
				{
					#need to recache later?
					$recache = TRUE;
							
					#new balance (no, not the shoes ya jackass)
					$newBalance = $pidCard['cc_no_pay_chrg'] + $pidCard['p_amount'];
			
					#add penalty late charge
					$this->registry->mysql_ibEconomy->adjustPortAmt4SpecNum($pidCard['p_id'], $newBalance, time());	
				}
			}
		}

		#update portfolio
		if ( $recache )
		{
			$this->registry->ecoclass->acm('portfolios');		
		}
		
		//-----------------------------------------
		// Log to log table - modify but dont delete
		//-----------------------------------------
		
		$this->class->appendTaskLog( $this->task, $this->lang->words['cc_interest_added'] );
		
		//-----------------------------------------
		// Unlock Task: DO NOT MODIFY!
		//-----------------------------------------
		
		$this->class->unlockTask( $this->task );
	}
}