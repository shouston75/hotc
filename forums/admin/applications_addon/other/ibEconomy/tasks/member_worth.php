<?php

/**
 * (e32) ibEconomy
 * Task: Member Worth
 * + Recalculate every member's worth
 * + Adjust member's points to match group max pts
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
		#ibEconomy not on?   ibEconomy worth not on?
		if ( ! $this->settings['eco_general_on'] || ! $this->settings['eco_worth_on'] )
		{
			$this->class->unlockTask( $this->task );
			return;
		}
		
		#init
		$newMemberWorths = array();
		$overMaxPters    = array();
		$portfolios		 = array();
		
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
			$portfolios = $this->registry->mysql_ibEconomy->grabPortfolioItems( 0, 'all', 'cache', array(), '', 0, '', '', false, true, true );
		}
		else
		{
			#need port cache?
			if( !$this->caches['ibEco_portfolios'] )
			{
				$this->caches['ibEco_portfolios'] = $this->cache->getCache('ibEco_portfolios');
			}
		}
		
		#grab members
		$this->registry->mysql_ibEconomy->grabMembers4WorthRecalc();
		
		if ( $this->DB->getTotalRows() )
		{
			while ( $row = $this->DB->fetch() )
			{
				#eco allowed?
				if ( ! $this->registry->ecoclass->checkGroupPerms( 'g_eco', 'no_perm_to_play', $text='', TRUE, $row ) )
				{
					continue;
				}
				
				#init this row's worth
				$newWorth = $row[ $this->settings['eco_general_pts_field'] ];

				#over max pts?
				if ( $row['g_eco_max_pts'] && $newWorth > $row['g_eco_max_pts'] )
				{
					$overMaxPters[ $row['member_id'] ] = $row['g_eco_max_pts'];
				}

				#portfolio cache enabled?
				if ( ! $this->settings['eco_general_cache_portfolio'] )
				{
					$myCache = $portfolios[ $row['member_id'] ];
				}
				else
				{
					#init this row's member's cache				
					$myCache = $this->caches['ibEco_portfolios'][ $row['member_id'] ];
				}
				
				if ( is_array( $myCache ) and count ( $myCache ) )
				{
					foreach ( $myCache AS $myItem )
					{
						#skip if no amount
						if ( $myItem['p_amount'] == 0 )
						{
							continue;
						}
						
						#easy amount addition...
						if ( !in_array($myItem['p_type'], array('cc','shopitem','stock' ) ) && $myItem['p_type_class'] != 'loan' )
						{
							$newWorth = $newWorth + $myItem['p_amount'];
						}
						
						#else easy amount subtraction...
						else if ( $myItem['p_type_class'] == 'loan' || $myItem['p_type'] == 'cc')
						{
							$newWorth = $newWorth - $myItem['p_amount'];
						}
						
						#else stock shares * stock value..
						else if ( $myItem['p_type'] == 'stock')
						{
							$newWorth = $newWorth + ($myItem['p_amount'] * $myItem['s_value']);
						}					
						
						#else tricky shopitem resell value
						else
						{
							$newWorth = $newWorth + $myItem['p_amount'] * $myItem['si_cost'] * (1- ($this->settings['eco_shop_sell_fee']/100));
						}
					}
				}
				
				#number format bug fix, those damn commas! (as of 2.0.8, thanks to InvisionHQ)
				$newWorth = str_replace(',', '.', $newWorth);
				
				#subtract welfare?
				$newWorthMinusWF = $newWorth - $row['eco_welfare'];
				$newWorth = ( $this->settings['eco_worth_incl_wf'] ) ? $newWorth : $newWorthMinusWF;

				#changed worth
				if ( $newWorth != $row['eco_worth'] )
				{
					$newMemberWorths[ $row['member_id'] ] = $newWorth;
				}
			}
		}
		
		#Adjust those member's worths!
		if (  is_array( $newMemberWorths ) and count( $newMemberWorths ) )
		{
			foreach ( $newMemberWorths AS $memberID => $memWorth )
			{
				$memWorth = $this->registry->ecoclass->makeNumeric($memWorth, false);
				IPSMember::save( $memberID, array( 'pfields_content' => array( 'eco_worth' => $memWorth ) ) );		
			}		
		}
 
		#over max points?
		if (  is_array( $overMaxPters ) and count( $overMaxPters ) )
		{
			foreach ( $overMaxPters AS $memberID => $maxPts)
			{
				$this->registry->mysql_ibEconomy->updateMemberPts2SpecNum( $memberID, $maxPts, 0 );
			}		
		}

		#recache?
		if ( ( is_array( $overMaxPters ) and count( $overMaxPters ) ) || ( is_array( $newMemberWorths ) and count( $newMemberWorths ) ) )
		{
			#recache
			$this->cache->rebuildCache('ibEco_stats', 'ibEconomy');			
		}

		//-----------------------------------------
		// Log to log table - modify but dont delete
		//-----------------------------------------
		
		$this->class->appendTaskLog( $this->task, $this->lang->words['member_worth_recalculated'] );
		
		//-----------------------------------------
		// Unlock Task: DO NOT MODIFY!
		//-----------------------------------------
		
		$this->class->unlockTask( $this->task );
	}
}