<?php
/**
 * <pre>
 * Invision Power Services
 * IP.Board v3.1.3
 * Portal plugin: calendar
 * Last Updated: $Date: 2010-10-22 06:13:38 -0400 (Fri, 22 Oct 2010) $
 * </pre>
 *
 * @author 		$Author: ips_terabyte $
 * @copyright	(c) 2001 - 2009 Invision Power Services, Inc.
 * @license		http://www.invisionpower.com/community/board/license.html
 * @package		IP.Board
 * @subpackage	Calendar
 * @link		http://www.invisionpower.com
 * @since		1st march 2002
 * @version		$Revision: 7016 $
 */

if ( ! defined( 'IN_IPB' ) )
{
	print "<h1>Incorrect access</h1>You cannot access this file directly. If you have recently upgraded, make sure you upgraded 'admin.php'.";
	exit();
}

class ppi_calendar extends public_portal_portal_portal 
{
	/**
	 * Initialize module
	 *
	 * @return	void
	 */
	public function init()
 	{
 	}
 	
	/**
	 * Show the current calendar month on the portal
	 *
	 * @return	string		HTML content to replace tag with
	 */
	public function calendar_show_current_month()
	{
		//-----------------------------------------
		// Grab calendar class
		//-----------------------------------------
		
		$classToLoad = IPSLib::loadActionOverloader( IPSLib::getAppDir( 'calendar' ) . '/modules_public/calendar/calendars.php', 'public_calendar_calendar_calendars' );
		$calendar    = new $classToLoad();
		$calendar->makeRegistryShortcuts( $this->registry );
		$calendar->initCalendar(true);

		if( ! is_array( $calendar->calendar ) OR ! count( $calendar->calendar ) OR ! $calendar->can_read )
		{
			return'';
		}

 		//-----------------------------------------
 		// What now?
 		//-----------------------------------------
 		
 		$a = explode( ',', gmdate( 'Y,n,j,G,i,s', time() + ipsRegistry::getClass( 'class_localization')->getTimeOffset() ) );
		
		$now_date = array(
						  'year'    => $a[0],
						  'mon'     => $a[1],
						  'mday'    => $a[2],
						  'hours'   => $a[3],
						  'minutes' => $a[4],
						  'seconds' => $a[5]
						);
							   
 		$content = $calendar->getMiniCalendar( $now_date['mon'], $now_date['year'] );
 		
 		return $this->registry->getClass('output')->getTemplate('portal')->calendarWrap( $content );
  	}
}