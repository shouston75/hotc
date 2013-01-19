<?php
/**
 * <pre>
 * Invision Power Services
 * IP.Board v3.1.3
 * Default calendar section
 * Last Updated: $Date: 2010-04-15 15:46:26 -0400 (Thu, 15 Apr 2010) $
 * </pre>
 *
 * @author 		$author$
 * @copyright	(c) 2001 - 2009 Invision Power Services, Inc.
 * @license		http://www.invisionpower.com/community/board/license.html
 * @package		IP.Board
 * @subpackage	Calendar
 * @link		http://www.invisionpower.com
 * @version		$Rev: 6133 $ 
 */

if ( ! defined( 'IN_IPB' ) )
{
	print "<h1>Incorrect access</h1>You cannot access this file directly. If you have recently upgraded, make sure you upgraded all the relevant files.";
	exit();
}

/**
* Very simply returns the default section if one is not
* passed in the URL
*/

$DEFAULT_SECTION = 'calendars';