<?php
/**
 * <pre>
 * Invision Power Services
 * IP.Board v3.1.3
 * Task: Gathers birthday and calendar events for a few days and caches them
 * Last Updated: $LastChangedDate: 2010-10-20 13:11:07 -0400 (Wed, 20 Oct 2010) $
 * </pre>
 *
 * @author 		$Author: ips_terabyte $
 * @copyright	(c) 2001 - 2009 Invision Power Services, Inc.
 * @license		http://www.invisionpower.com/community/board/license.html
 * @package		IP.Board
 * @subpackage	Calendar
 * @link		http://www.invisionpower.com
 * @since		27th January 2004
 * @version		$Rev: 7005 $
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
	
	/**#@+
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
	/**#@-*/
	
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
		$this->registry->getClass('class_localization')->loadLanguageFile( array( 'public_global' ), 'core' );

		//-----------------------------------------
		// Load and recache info
		//-----------------------------------------
		
		define( 'IN_ACP', 1 );
		
		$classToLoad = IPSLib::loadActionOverloader( IPSLib::getAppDir('calendar') . '/modules_admin/calendar/calendars.php' , 'admin_calendar_calendar_calendars' );
		$calendars   = new $classToLoad();
		$calendars->makeRegistryShortcuts( $this->registry );
		$calendars->calendarRebuildCache( 0 );
		
		//-----------------------------------------
		// Log to log table - modify but dont delete
		//-----------------------------------------
		
		if ( ! $this->restrict_log )
		{
			$this->class->appendTaskLog( $this->task, $this->lang->words['task_calendars'] );
		}
		
		//-----------------------------------------
		// Unlock Task: DO NOT MODIFY!
		//-----------------------------------------
		
		$this->class->unlockTask( $this->task );
	}
}