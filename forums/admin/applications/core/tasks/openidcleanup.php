<?php
/**
 * @file		openidcleanup.php 	Task to cleanup uncleared OpenID caches
 *~TERABYTE_DOC_READY~
 * $Copyright: (c) 2001 - 2011 Invision Power Services, Inc.$
 * $License: http://www.invisionpower.com/company/standards.php#license$
 * $Author: ips_terabyte $
 * @since		-
 * $LastChangedDate: 2011-02-08 17:20:18 -0500 (Tue, 08 Feb 2011) $
 * @version		v3.2.2
 * $Revision: 7750 $
 */

if ( ! defined( 'IN_IPB' ) )
{
	print "<h1>Incorrect access</h1>You cannot access this file directly. If you have recently upgraded, make sure you upgraded all the relevant files.";
	exit();
}

/**
 *
 * @class		task_item
 * @brief		Task to cleanup uncleared OpenID caches
 *
 */
class task_item
{
	/**
	 * Object that stores the parent task manager class
	 *
	 * @var		$class
	 */
	protected $class;
	
	/**
	 * Array that stores the task data
	 *
	 * @var		$task
	 */
	protected $task = array();
	
	/**
	 * Registry Object Shortcuts
	 *
	 * @var		$registry
	 * @var		$DB
	 * @var		$settings
	 * @var		$lang
	 */
	protected $registry;
	protected $lang;
	
	/**
	 * Constructor
	 *
	 * @param	object		$registry		Registry object
	 * @param	object		$class			Task manager class object
	 * @param	array		$task			Array with the task data
	 * @return	@e void
	 */
	public function __construct( ipsRegistry $registry, $class, $task )
	{
		/* Make registry objects */
		$this->registry	= $registry;
		$this->lang		= $this->registry->getClass('class_localization');
		
		$this->class	= $class;
		$this->task		= $task;
	}
	
	/**
	 * Run this task
	 *
	 * @return	@e void
	 */
	public function runTask()
	{
		$this->registry->getClass('class_localization')->loadLanguageFile( array( 'public_global' ), 'core' );
		
		//-----------------------------------------
		// Clean out openid caches older than 24 hours
		// For whatever reason the OpenID libraries
		// seem to have an issue where caches can start
		// to accumulate.  On boards heavily using
		// OpenID, it's possible to eventually fill up
		// the cache directories, which causes all subsequent
		// logins to fail.  This task just clears out those
		// caches once every 24 hours to keep that from happening.
		//-----------------------------------------
		
		try
		{
			if( is_dir( DOC_IPS_ROOT_PATH . 'cache/openid' ) )
			{
				if( is_dir( DOC_IPS_ROOT_PATH . 'cache/openid/associations' ) )
				{
					foreach( new DirectoryIterator( DOC_IPS_ROOT_PATH . 'cache/openid/associations' ) as $cache )
					{
						if( $cache->getMTime() < ( time() - ( 60 * 60 * 24 ) ) )
						{
							@unlink( $cache->getPathname() );
						}
					}
				}
				
				if( is_dir( DOC_IPS_ROOT_PATH . 'cache/openid/nonces' ) )
				{
					foreach( new DirectoryIterator( DOC_IPS_ROOT_PATH . 'cache/openid/nonces' ) as $cache )
					{
						if( $cache->getMTime() < ( time() - ( 60 * 60 * 24 ) ) )
						{
							@unlink( $cache->getPathname() );
						}
					}
				}
				
				if( is_dir( DOC_IPS_ROOT_PATH . 'cache/openid/temp' ) )
				{
					foreach( new DirectoryIterator( DOC_IPS_ROOT_PATH . 'cache/openid/temp' ) as $cache )
					{
						if( $cache->getMTime() < ( time() - ( 60 * 60 * 24 ) ) )
						{
							@unlink( $cache->getPathname() );
						}
					}
				}
			}
		} catch ( Exception $e ) {}

		//-----------------------------------------
		// Log to log table - modify but dont delete
		//-----------------------------------------
		
		$this->class->appendTaskLog( $this->task, $this->lang->words['task_openidcleanup'] );
		
		//-----------------------------------------
		// Unlock Task: DO NOT MODIFY!
		//-----------------------------------------
		
		$this->class->unlockTask( $this->task );
	}
}