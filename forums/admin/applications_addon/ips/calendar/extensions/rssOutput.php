<?php
/**
 * <pre>
 * Invision Power Services
 * IP.Board v3.1.3
 * RSS output plugin :: calendar
 * Last Updated: $Date: 2010-10-20 13:11:07 -0400 (Wed, 20 Oct 2010) $
 * </pre>
 *
 * @author 		$Author: ips_terabyte $
 * @copyright	(c) 2001 - 2009 Invision Power Services, Inc.
 * @license		http://www.invisionpower.com/community/board/license.html
 * @package		IP.Board
 * @subpackage	Calendar
 * @link		http://www.invisionpower.com
 * @since		6/24/2008
 * @version		$Revision: 7005 $
 */

if ( ! defined( 'IN_IPB' ) )
{
	print "<h1>Incorrect access</h1>You cannot access this file directly. If you have recently upgraded, make sure you upgraded all the relevant files.";
	exit();
}

class rss_output_calendar
{
	/**
	 * Expiration date
	 * 
	 * @var		integer			Expiration timestamp
	 */
	private $expires			= 0;
	
	/**
	 * Grab the RSS links
	 * 
	 * @return	string		RSS document
	 */
	public function getRssLinks()
	{
		$return	= array();

		$_calendarCache	= ipsRegistry::cache()->getCache('calendars');
		
		if( is_array($_calendarCache) AND count($_calendarCache) )
		{
			foreach( $_calendarCache as $r )
			{
				/* Got a perm */
				if( ! ipsRegistry::getClass( 'permissions' )->check( 'view', $r ) || !$r['cal_rss_export'] )
				{
					continue;
				}
				
				$return[] = array( 'title' => $r['cal_title'], 'url' => ipsRegistry::getClass('output')->formatUrl( ipsRegistry::$settings['board_url'] . "/index.php?app=core&amp;module=global&amp;section=rss&amp;type=calendar&amp;id=" . $r['cal_id'], '%%' . $r['cal_title'] . '%%', 'section=rss2' ) );
			}
		}

		return $return;
	}
	
	/**
	 * Grab the RSS document content and return it
	 * 
	 * @return	string		RSS document
	 */
	public function returnRSSDocument()
	{
		//-----------------------------------------
		// INIT
		//-----------------------------------------
		
		$cal_id			= intval( ipsRegistry::$request['id'] );
		$rss_data		= array();
		$to_print		= '';
		$this->expires	= time();
		
		//-----------------------------------------
		// Get RSS export
		//-----------------------------------------
		
		$rss_data = ipsRegistry::DB()->buildAndFetch( array( 'select'	=> '*',
															'from'		=> 'cal_calendars',
															'where'		=> 'cal_id=' . $cal_id ) );
		
		//-----------------------------------------
		// Got one?
		//-----------------------------------------

		if ( $rss_data['cal_id'] AND $rss_data['cal_rss_export'] )
		{
			//-----------------------------------------
			// Correct expires time
			//-----------------------------------------
			
			$this->expires = $this->expires + ($rss_data['cal_rss_update'] * 60);
			
			//-----------------------------------------
			// Need to recache?
			//-----------------------------------------
			
			$time_check = time() - ( $rss_data['cal_rss_update'] * 60 );
			
			if ( ( ! $rss_data['cal_rss_cache'] ) OR $time_check > $rss_data['cal_rss_update_last'] )
			{
				//-----------------------------------------
				// Yes
				//-----------------------------------------
				
				define( 'IN_ACP', 1 );
				
				$classToLoad = IPSLib::loadActionOverloader( IPSLib::getAppDir( 'calendar' ) . '/modules_admin/calendar/calendars.php', 'admin_calendar_calendar_calendars' );
				$rss_export  = new $classToLoad();
				$rss_export->makeRegistryShortcuts( ipsRegistry::instance() );
				
				
				return $rss_export->calendarRSSCache( $rss_data['cal_id'], 0 );
			}
			else
			{
				//-----------------------------------------
				// No
				//-----------------------------------------
				
				return $rss_data['cal_rss_cache'];
			}
		}
	}
	
	/**
	 * Grab the RSS document expiration timestamp
	 * 
	 * @return	integer		Expiration timestamp
	 */
	public function grabExpiryDate()
	{
		return $this->expires ? $this->expires : time();
	}
}