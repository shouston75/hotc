<?php
/**
 * <pre>
 * Invision Power Services
 * IP.Board v1.2.0
 * Sets up SEO templates
 * Last Updated: $Date: 2010-12-17 07:53:02 -0500 (Fri, 17 Dec 2010) $
 * </pre>
 *
 * @author 		$Author: ips_terabyte $
 * @copyright	(c) 2001 - 2009 Invision Power Services, Inc.
 * @license		http://www.invisionpower.com/community/board/license.html
 * @package		IP.Chat
 * @link		http://www.invisionpower.com
 * @version		$Rev: 7443 $
 *
 */

if ( ! defined( 'IN_IPB' ) )
{
	print "<h1>Incorrect access</h1>You cannot access this file directly. If you have recently upgraded, make sure you upgraded all the relevant files.";
	exit();
}

$_SEOTEMPLATES = array(
						'app=ipchat'		=> array( 
											'app'			=> 'ipchat',
											'allowRedirect' => 1,
											'out'			=> array( '#app=ipchat$#i', 'chat/' ),
											'in'			=> array( 
																		'regex'		=> "#/chat(/|$|\?)#i",
																		'matches'	=> array( array( 'app', 'ipchat' ) )
																	) 
														),
					);
