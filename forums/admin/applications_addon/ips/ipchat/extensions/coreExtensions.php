<?php

/**
 * Invision Power Services
 * IP.Board v3.0.4
 * Library: Handle public session data
 * Last Updated: $Date: 2010-12-17 07:53:02 -0500 (Fri, 17 Dec 2010) $
 *
 * @author 		$Author: ips_terabyte $
 * @copyright	(c) 2001 - 2009 Invision Power Services, Inc.
 * @license		http://www.invisionpower.com/community/board/license.html
 * @package		IP.Chat
 * @link		http://www.invisionpower.com
 * @since		12th March 2002
 * @version		$Revision: 7443 $
 *
 */

if ( ! defined( 'IN_IPB' ) )
{
	print "<h1>Incorrect access</h1>You cannot access this file directly. If you have recently upgraded, make sure you upgraded all the relevant files.";
	exit();
}

class publicSessions__ipchat
{
	/**
	 * Return session variables for this application
	 *
	 * current_appcomponent, current_module and current_section are automatically
	 * stored. This function allows you to add specific variables in.
	 *
	 * @return	array
	 */
	public function getSessionVariables()
	{
		return array( '1_type' => 'ipchat',
					  '1_id'   => 1 );
	}

	/**
	 * Parse/format the online list data for the records
	 *
	 * @author	Brandon Farber
	 * @param	array 			Online list rows to check against
	 * @return	array 			Online list rows parsed
	 */
	public function parseOnlineEntries( $rows )
	{
		ipsRegistry::getClass('class_localization')->loadLanguageFile( array( 'public_chat' ), 'ipchat' );
		
		if( !is_array($rows) OR !count($rows) )
		{
			return $rows;
		}

		$final = $rows;
		
		//-----------------------------------------
		// Extract the chat data
		//-----------------------------------------
		
		foreach( $rows as $row )
		{
			if( $row['current_appcomponent'] == 'ipchat' )
			{
				$row['where_line']		= ipsRegistry::getClass('class_localization')->words['chat_online'];
				$row['where_link']		= 'app=ipchat';
				$row['_whereLinkSeo']	= ipsRegistry::getClass('output')->formatUrl( ipsRegistry::getClass('output')->buildUrl( 'app=ipchat', 'public' ), 'false', 'app=ipchat' );
				
				$final[ $row['id'] ] = $row;
			}
		}

		return $final;
	}
}