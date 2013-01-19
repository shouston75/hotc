<?php

/**
 * (e32) ibEconomy
 * Task: Welfare
 * + Hand outs to the poor
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
		
		#load lang
		ipsRegistry::getClass('class_localization')->loadLanguageFile( array( 'admin_ibEconomy' ), 'ibEconomy' );
		
		#init
		$needsWelfare = array();
		
		#shall we get to tasking?
		if ( $this->settings['eco_welfare_on'] )
		{
			#time 4 welfare checks again?
			$timeAgo = time() - ($this->settings['eco_welfare_cycle'] * 86400);			
			
			#grab all members needing welfare checks (or at least possibly)
			$this->registry->mysql_ibEconomy->grabWelfareErs( $timeAgo );
			
			if ( $this->DB->getTotalRows() )
			{
				while ( $row = $this->DB->fetch() )
				{
					#allowed
					if ( ! $this->registry->ecoclass->checkGroupPerms( 'g_eco_welfare', 'no_perm_to_play', $this->lang->words['welfare'], TRUE, $row ) )
					{
						continue;
					}
					
					#0 for group welfare check amt?
					if ( !$row['g_eco_welfare_max'] )
					{
						continue;
					}					
					
					#restrictions on for checks?							
					if ( $this->settings['eco_welfare_res_alwys'] )
					{
						#max points
						if ( $this->settings['eco_welfare_max_pts'] && $row[ $this->settings['eco_general_pts_field'] ] > $this->settings['eco_welfare_max_pts'] )
						{
							continue;
						}					
						
						#max worth
						if ( $this->settings['eco_welfare_max_worth'] && $row['eco_worth'] > $this->settings['eco_welfare_max_worth'] )
						{
							continue;
						}						
							
						#posts
						if ( $this->settings['eco_welfare_max_wf'] && $row['eco_welfare'] > $this->settings['eco_welfare_max_wf'] )
						{
							continue;
						}
						
						#posts
						if ( $this->settings['eco_welfare_min_posts'] && $row['posts'] < $this->settings['eco_welfare_min_posts'] )
						{
							continue;
						}					
						
						#posts per day
						if ( $this->settings['eco_welfare_min_ppd'] && $row['posts']/((time() - $row['joined'])/86400) < $this->settings['eco_welfare_min_ppd'] )
						{
							continue;
						}
	
						#joined
						if ( $this->settings['eco_welfare_min_join'] && (time() - $row['joined'])/86400 < $this->settings['eco_welfare_min_join'] )
						{
							continue;
						}						
						
						#activity
						if ( $this->settings['eco_welfare_act'] && (time() - $row['last_activity'])/86400 > $this->settings['eco_welfare_act'] )
						{
							continue;
						}
					}
					
					#still here?  hold out yur hands
					$needsWelfare[] = $row;
				}
			}
			
			#loop through membs, granting welfare checks
			if ( is_array( $needsWelfare ) && count( $needsWelfare ) )
			{
				foreach ( $needsWelfare AS $prawn )
				{	
					#add pts but not worth to each leacher
					$this->registry->mysql_ibEconomy->updateMemberPts($prawn['member_id'], $prawn['g_eco_welfare_max'], '+', false);
					
					#add pts to welfare total and assign current time to last check received time
					$this->registry->mysql_ibEconomy->updateMemberWelfare($prawn['member_id'], $prawn['g_eco_welfare_max'], '+', time());
				}
			}
		}
		
		//-----------------------------------------
		// Log to log table - modify but dont delete
		//-----------------------------------------
		
		$this->class->appendTaskLog( $this->task, $this->lang->words['welfare_check_handed_out'] );
		
		//-----------------------------------------
		// Unlock Task: DO NOT MODIFY!
		//-----------------------------------------
		
		$this->class->unlockTask( $this->task );
	}
}