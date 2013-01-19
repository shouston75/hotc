<?php

/**
 * (e32) ibEconomy
 * Task: Shopitem Restock
 * + Restock Shop Items
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
		#init
		$restockThese = array();
		
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
				
		#need stock cache?
		if( !$this->caches['ibEco_shopitems'] )
		{
			$this->caches['ibEco_shopitems'] = $this->cache->getCache('ibEco_shopitems');
		}
		
		#init cached banks
		$shopitems 	= $this->caches['ibEco_shopitems'];

		#lets begin this horrid process... why did I have to include stocks in ibEco?
		if ( $this->settings['eco_shopitems_on'] && is_array( $shopitems ) and count ( $shopitems ) )
		{
			#loopy
			foreach ( $shopitems AS $item )
			{
				#disabled?
				if ( !$item['si_on'] )
				{
					continue;
				}
				
				#restock disabled?
				if ( !$item['si_restock'] )
				{
					continue;
				}	
				
				#restock to 0 (or negative)?
				if ( intval($item['si_restock_amt']) < 1 )
				{
					continue;
				}
				
				#no restock needed?
				if ( $item['si_inventory'] >= $item['si_restock_amt'] )
				{
					continue;
				}				
					
				#last time anything coud've affected the amount
				$lastAdjustment = ( $item['si_last_restock'] ) ? $item['si_last_restock'] : $item['si_added_on'];
				
				#skip rest if we haven't waited long enough since last adjustment
				if ( time() - $lastAdjustment < $item['si_restock_time'] * 86400 )
				{
					continue;
				}				
				
				#restock array
				$restockThese[] = $item['si_id'];				
			}
			
			#restock
			$this->registry->mysql_ibEconomy->restockShopItem( $restockThese, time() );
			
			#update cache
			$this->cache->rebuildCache('ibEco_shopitems', 'ibEconomy');
		}
		
		//-----------------------------------------
		// Log to log table - modify but dont delete
		//-----------------------------------------
		
		$this->class->appendTaskLog( $this->task, $this->lang->words['shop_items_restocked'] );
		
		//-----------------------------------------
		// Unlock Task: DO NOT MODIFY!
		//-----------------------------------------
		
		$this->class->unlockTask( $this->task );
	}
}