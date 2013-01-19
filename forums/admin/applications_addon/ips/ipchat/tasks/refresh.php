<?php
/**
 * <pre>
 * Invision Power Services
 * IP.Board v1.2.0
 * Task: Downloads latest copy of chat logs
 * Last Updated: $LastChangedDate: 2010-12-17 07:53:02 -0500 (Fri, 17 Dec 2010) $
 * </pre>
 *
 * @author 		$Author: ips_terabyte $
 * @copyright	(c) 2001 - 2009 Invision Power Services, Inc.
 * @license		http://www.invisionpower.com/community/board/license.html
 * @package		IP.Chat
 * @link		http://www.invisionpower.com
 * @since		27th January 2004
 * @version		$Rev: 7443 $
 */

if ( ! defined( 'IN_IPB' ) )
{
	print "<h1>Incorrect access</h1>You cannot access this file directly. If you have recently upgraded, make sure you upgraded all the relevant files.";
	exit();
}

class task_item
{
	/**
	 * Registry Object Shortcuts
	 *
	 * @var		object
	 */
	protected $registry;
	protected $DB;
	protected $settings;
	protected $request;
	protected $lang;
	protected $member;
	protected $memberData;
	protected $cache;
	protected $caches;
	
	/**
	 * Parent task manager class
	 *
	 * @var		object
	 */
	protected $class;

	/**
	 * This task data
	 *
	 * @var		array
	 */
	protected $task			= array();

	/**
	 * Prevent logging
	 *
	 * @var		boolean
	 */
	protected $restrict_log	= false;
	
	/**
	 * Constructor
	 *
	 * @param 	object		ipsRegistry reference
	 * @param 	object		Parent task class
	 * @param	array 		This task data
	 * @return	void
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
	 * @return	void
	 */
	public function runTask()
	{
		$this->registry->getClass('class_localization')->loadLanguageFile( array( 'public_chat' ), 'ipchat' );
		
		$classToLoad = IPSLib::loadActionOverloader( IPSLib::getAppDir( 'ipchat' ) . '/modules_admin/logs/logs.php', 'admin_ipchat_logs_logs' );
		$_logs       = new $classToLoad();
		$_logs->makeRegistryShortcuts( $this->registry );
		
		$_message	= $_logs->refreshLogs( true );
		
		$this->class->appendTaskLog( $this->task, $_message );
		
		//-----------------------------------------
		// Unlock Task: DO NOT MODIFY!
		//-----------------------------------------
		
		$this->class->unlockTask( $this->task );
	}
}