<?php

/**
 * (e32) ibEconomy
 * Task: Stocks
 * + Adjust Stock Share Values
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
		
		#need stock cache?
		if( !$this->caches['ibEco_stocks'] )
		{
			$this->caches['ibEco_stocks'] = $this->cache->getCache('ibEco_stocks');
		}
		
		#master ibEconomy SQL Queries
		require_once( IPSLib::getAppDir( 'ibEconomy' ) . "/sql/mysql_ibEconomy.php" );
		$this->registry->setClass( 'mysql_ibEconomy', new ibEconomyMySQL( $this->registry ) );
		
		#master ibEconomy Class
		require_once( IPSLib::getAppDir( 'ibEconomy' ) . "/sources/ecoclass.php" );
		$this->registry->setClass( 'ecoclass', new class_ibEconomy( $this->registry ) );		
		
		#need stat cache?
		if( !$this->caches['ibEco_stats'] )
		{
			$this->caches['ibEco_stats'] = $this->cache->getCache('ibEco_stats');
		}		
		
		#init cached banks
		$stocks 	= $this->caches['ibEco_stocks'];

		#lets begin this horrid process... why did I have to include stocks in ibEco?
		if ( $this->settings['eco_stocks_on'] && is_array( $stocks ) and count ( $stocks ) )
		{
			#create value shifts
			foreach ( $stocks AS $stock )
			{
				#disabled?
				if ( !$stock['s_on'] )
				{
					continue;
				}
				
				#last time anything coud've affected the value
				$lastAdjustment = ( $stock['s_last_run'] ) ? $stock['s_last_run'] : $stock['s_added_on'];
				
				#skip rest if we haven't waited long enough since last adjustment
				if ( $this->settings['eco_stocks_cycle'] * 60 > time() - $lastAdjustment )
				{
					continue;
				}				
				
				$newNum = 0;
				#random or not easy?
				if ( $stock['s_type'] == 'basic' )
				{
					#generate random integer between admin choosen and 10
					$randomNum = rand($this->settings['eco_stocks_curve'], 10);	

					#getting paid?
					$multiplyBy = ($randomNum * $stock['s_risk_level'])/1000;
				}
				else
				{
					#dealing with points?  no way to query for the "latest" points really... so we'll be creative
					if ( $stock['s_type_var'] == 'points' )
					{
						#number reached that will maximize the share increase
						$maxNum = $this->settings['eco_stocks_points_max'];
						
						if ( $stock['s_type'] == 'forum' )
						{
							#grab from our stat cache
							$newNum  = $this->caches['ibEco_stats']['total_points'];
					
							#none found?
							if ( !$newNum )
							{
								continue;
							}							
						}
						else
						{
							#tally up points earned by the group or member since last time
							$newNum = $this->registry->mysql_ibEconomy->tallyPointsByVars($stock['s_type'], $stock['s_type_var_value'] );
						}
					}
					else
					{	
						#number reached that will maximize the share increase
						$maxNum = ( $stock['s_type_var'] == 'posts' ) ? $this->settings['eco_stocks_posts_max'] : $this->settings['eco_stocks_regs_max'];

						#grab latest posts/registrations
						$newNum = $this->registry->mysql_ibEconomy->grabStockVarNum($stock['s_type'], $stock['s_type_var'], $lastAdjustment, $stock['s_type_var_value'] );
					}
						
					#initialize calculation value (first time) and move on?
					#first time this stock value has ever been calculated, 
					#lets just add newNum and set s_last_calc_diff to 1
					if ( $stock['s_last_calc_dif'] == 0 || !in_array( $stock['s_last_calc_dif'], array( 1,  2, 3) ) )
					{
						#add for next time
						$stockMiniUpdates 	= array('s_value' 			=> $stock['s_value'],
													's_last_calc' 		=> $newNum,
													's_last_calc_dif' 	=> 1,
													's_last_run'		=> time()
												   );
											   
						$this->registry->mysql_ibEconomy->adjustStock($stock['s_id'], 'all', $stockMiniUpdates, time() );						

						#skip rest
						continue;
					}
					
					#second time this stock value has ever been calculated, 
					#lets just add newNum (which should be appropriate now and set s_last_calc_diff to 2
					else if ( $stock['s_last_calc_dif'] == 1)
					{
						#add for next time
						$stockMiniUpdates 	= array('s_value' 			=> $stock['s_value'],
													's_last_calc' 		=> $newNum,
													's_last_calc_dif' 	=> 2,
													's_last_run'		=> time()
												   );
											   
						$this->registry->mysql_ibEconomy->adjustStock($stock['s_id'], 'all', $stockMiniUpdates, time() );						

						#skip rest
						continue;
					}

					#if we've made it this far...
					#third or more time this stock value has ever been calculated
					#so we're ready to actually adjust the stock value
					
					#we seeing more or less of the variable?
					if ( $stock['s_type_var'] == 'points' )
					{
						$difBetweenThisRunAndLast = $newNum - $stock['s_last_calc'];
						
						$difBetweenThisRunAndLast = ($difBetweenThisRunAndLast == 0) ? $maxNum/-10 : $difBetweenThisRunAndLast;
					}
					else
					{
						#uh oh, s/he has gone MIA, better decrease value a bit
						if ($stock['s_last_calc'] == 0 && $newNum == 0)
						{
							$difBetweenThisRunAndLast = $maxNum/-10;
						}
						else
						{
							$difBetweenThisRunAndLast = $newNum - $stock['s_last_calc']/2;
						}
					}
					
					#wow, that is quite an improvement, we're gonna max out the increase
					if ($difBetweenThisRunAndLast > $maxNum)
					{
						$multiplyBy = ($stock['s_risk_level']/100);
					}
					#wow, what a bad showing, multiply by maximum decrease
					else if ($difBetweenThisRunAndLast < $maxNum * -1)
					{
						$multiplyBy = ($stock['s_risk_level']/-100);
					}
					#something between -$maxNum and $maxNum, so proceed as normal
					else
					{
						$multiplyBy = $difBetweenThisRunAndLast/$maxNum * $stock['s_risk_level']/100;
					}						
				}

				#don't forget to adjust the stock value
				$newValue = $stock['s_value'] + ($multiplyBy * $stock['s_value']);
				$newValue = ( $newValue > 0 ) ? $newValue : 1;
				$newValue = ( $newValue > $this->settings['eco_stocks_max_value'] ) ? $this->settings['eco_stocks_max_value'] : $newValue;
				$newValue = $this->registry->ecoclass->makeNumeric($newValue, false);

				$stockUpdates 	= array('s_value' 			=> $newValue,
										's_last_calc' 		=> $newNum,
										's_last_calc_dif' 	=> 3,
										's_last_run'		=> time()
									   );
									   
				$this->registry->mysql_ibEconomy->adjustStock($stock['s_id'], 'all', $stockUpdates, time() );				
			}

			#update cache
			$this->registry->ecoclass->acm(array('stocks','portfolios'));
		}
		
		//-----------------------------------------
		// Log to log table - modify but dont delete
		//-----------------------------------------
		
		$this->class->appendTaskLog( $this->task, $this->lang->words['stock_value_adjusted'] );
		
		//-----------------------------------------
		// Unlock Task: DO NOT MODIFY!
		//-----------------------------------------
		
		$this->class->unlockTask( $this->task );
	}
}