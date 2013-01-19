<?php

/**
 * Invision Power Services
 * IP.Board v3.0.4
 * Chat services
 * Last Updated: $Date: 2010-12-17 07:53:02 -0500 (Fri, 17 Dec 2010) $
 *
 * @author 		$Author: ips_terabyte $
 * @copyright	(c) 2001 - 2009 Invision Power Services, Inc.
 * @license		http://www.invisionpower.com/community/board/license.html
 * @package		IP.Chat
 * @link		http://www.invisionpower.com
 * @since		Fir 12th Aug 2005
 * @version		$Revision: 7443 $
 */

if ( ! defined( 'IN_IPB' ) )
{
	print "<h1>Incorrect access</h1>You cannot access this file directly. If you have recently upgraded, make sure you upgraded 'admin.php'.";
	exit();
}

class public_ipchat_ajax_block extends ipsAjaxCommand
{
	/**
	 * Main class entry point
	 *
	 * @param	object		ipsRegistry reference
	 * @return	void		[Outputs to screen]
	 */
	public function doExecute( ipsRegistry $registry ) 
	{
		//-----------------------------------------
		// Got sess ID and mem ID?
		//-----------------------------------------
		
		if ( ! $this->member->getProperty('member_id') )
		{
			$this->returnString( "no" );
		}
		
		//-----------------------------------------
		// Get data
		//-----------------------------------------
		
		$block	= $this->request['block'] ? true : false;
		$id		= intval($this->request['id']);
		
		if( !$id )
		{
			$this->returnString( "no" );
		}
		
		//-----------------------------------------
		// Get member record and verify we can ignore
		//-----------------------------------------
		
		$member = IPSMember::load( $id, 'core' );
		
		if ( $member['_canBeIgnored'] !== TRUE )
		{
			$this->returnString( "no" );
	 	}
		
		//-----------------------------------------
		// Get cache
		//-----------------------------------------
		
		if( isset( $this->memberData['_cache']['ignore_chat'] ) )
		{
			$cache		= $this->memberData['_cache']['ignore_chat'];
		}
		else
		{
			$cache		= array();
		}
		
		//-----------------------------------------
		// Block/unblock
		//-----------------------------------------
		
		if( $block )
		{
			$cache[ $id ]	= $id;
		}
		else
		{
			unset( $cache[ $id ] );
		}
		
		//-----------------------------------------
		// Update cache
		//-----------------------------------------
		
		IPSMember::packMemberCache( $this->memberData['member_id'], array( 'ignore_chat' => $cache ), $this->memberData['_cache'] );
		
		//-----------------------------------------
		// Something to return
		//-----------------------------------------
		
		$this->returnString( "ok" );
	}
}