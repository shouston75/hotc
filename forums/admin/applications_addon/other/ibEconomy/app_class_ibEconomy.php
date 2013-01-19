<?php

/**
 * (e32) ibEconomy
 * Application Master Class
 * + Create Class Shortcuts for ibEconomy
 */

class app_class_ibEconomy
{
	/**
	 * Registry Object Shortcuts
	 */
	protected $registry;
	protected $DB;
	protected $settings;
	protected $request;
	protected $lang;
	protected $member;
	protected $cache;
	
	/**
	 * Constructor
	 */
	public function __construct( ipsRegistry $registry )
	{
		#make object
		$this->registry = $registry;
		$this->DB       = $this->registry->DB();
		$this->settings =& $this->registry->fetchSettings();
		$this->request  =& $this->registry->fetchRequest();
		$this->cache    = $this->registry->cache();
		$this->caches   =& $this->registry->cache()->fetchCaches();
		$this->lang     = $this->registry->getClass('class_localization');
		$this->member   = $this->registry->member();
		$this->memberData =& $this->registry->member()->fetchMemberData();
		

		#global
		
		#Master Public ibEconomy Class
		// require_once( IPSLib::getAppDir( 'ibEconomy' ) . "/sources/ecoclass.php" );
		// $this->registry->setClass( 'ecoclass', new class_ibEconomy( $registry ) );		
	
		#master ibEconomy SQL Queries
		require_once( IPSLib::getAppDir( 'ibEconomy' ) . "/sql/mysql_ibEconomy.php" );
		$this->registry->setClass( 'mysql_ibEconomy', new ibEconomyMySQL( $registry ) );
		
		if ( IN_ACP )
		{
			try
			{
				#ibEconomy ACP Class
				require_once( IPSLib::getAppDir( 'ibEconomy' ) . "/sources/classes/class_ibEco_CP.php" );
				$this->registry->setClass( 'class_ibEco_CP', new class_ibEco_CP( $registry ) );	
				
				#permission matrix!
				require_once( IPS_ROOT_PATH . 'sources/classes/class_public_permissions.php' );
				$this->registry->setClass( 'class_perms', new classPublicPermissions( $registry ) );
				
				#settings
				require_once( IPS_ROOT_PATH . 'applications/core/modules_admin/settings/settings.php' );
				$this->registry->setClass( 'class_settings', new admin_core_settings_settings( $registry ) );
			}
			catch( Exception $error )
			{
				IPS_exception_error( $error );
			}
		}
		else
		{
			try
			{
				#investing
				require_once( IPSLib::getAppDir( 'ibEconomy' ) . "/sources/classes/class_invest.php" );
				$this->registry->setClass( 'class_invest', new class_invest( $registry ) );
				
				#cart/checkout
				require_once( IPSLib::getAppDir( 'ibEconomy' ) . "/sources/classes/class_cart.php" );
				$this->registry->setClass( 'class_cart', new class_cart( $registry ) );
				
				#me and my stuff! hands off!
				require_once( IPSLib::getAppDir( 'ibEconomy' ) . "/sources/classes/class_me.php" );
				$this->registry->setClass( 'class_me', new class_me( $registry ) );
				
				#global eco
				require_once( IPSLib::getAppDir( 'ibEconomy' ) . "/sources/classes/class_global.php" );
				$this->registry->setClass( 'class_global', new class_global( $registry ) );	

				#its my party and I'll shop it I wanna
				require_once( IPSLib::getAppDir( 'ibEconomy' ) . "/sources/classes/class_shop.php" );
				$this->registry->setClass( 'class_shop', new class_shop( $registry ) );	
				
				#welfare/loans, gotta better name for it?
				require_once( IPSLib::getAppDir( 'ibEconomy' ) . "/sources/classes/class_cash.php" );
				$this->registry->setClass( 'class_cash', new class_cash( $registry ) );					
			}
			catch( Exception $error )
			{
				IPS_exception_error( $error );
			}
		}
	}
}