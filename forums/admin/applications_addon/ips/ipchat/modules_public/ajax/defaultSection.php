<?php
/**
 * Invision Power Services
 * IP.Board v3.0.4
 * Chat default section
 * Last Updated: $LastChangedDate: 2010-12-17 07:53:02 -0500 (Fri, 17 Dec 2010) $
 *
 * @author 		$Author: ips_terabyte $
 * @copyright	(c) 2001 - 2009 Invision Power Services, Inc.
 * @license		http://www.invisionpower.com/community/board/license.html
 * @package		IP.Chat
 * @link		http://www.invisionpower.com
 * @since		27th January 2004
 * @version		$Rev: 7443 $
 *
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

$DEFAULT_SECTION = 'update';
