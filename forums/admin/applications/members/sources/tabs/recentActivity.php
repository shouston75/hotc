<?php

/**
 * Invision Power Services
 * IP.Board v3.0.0
 * Profile Plugin Library
 * Last Updated: $Date: 2009-02-04 15:03:36 -0500 (Wed, 04 Feb 2009) $
 *
 * @author 		$Author: bfarber $
 * @copyright	(c) 2001 - 2009 Invision Power Services, Inc.
 * @license		http://www.invisionpower.com/community/board/license.html
 * @package		Invision Power Board
 * @subpackage	Members
 * @link		http://www.invisionpower.com
 * @since		20th February 2002
 * @version		$Revision: 3887 $
 *
 */

if ( ! defined( 'IN_IPB' ) )
{
	print "<h1>Incorrect access</h1>You cannot access this file directly. If you have recently upgraded, make sure you upgraded all the relevant files.";
	exit();
}

class profile_recentActivity extends profile_plugin_parent
{
	/**
	 * Attachment object
	 *
	 * @access	protected
	 * @var		object
	 */	
	protected $attach;
	
	/**
	 * return HTML block
	 *
	 * @access	public
	 * @param	array		Member information
	 * @return	string		HTML block
	 */
	public function return_html_block( $member=array() ) 
	{
		//-----------------------------------------
		// Activity Feed
		//-----------------------------------------
		$activity = array();
		
		if( ! in_array( $this->settings['search_method'], array( 'traditional', 'sphinx' ) ) )
		{
			/* Load Search Plugin */
			$search_plugin   = IPSSearchIndex::getSearchPlugin( 'index' );
			$display_plugins = array();
					
			/* Search and store results */
			$search_plugin->setCondition( 'member_id', '=', $member['member_id'] );
			$search_plugin->setCondition( 'type_2'   , '<>', "''" );
			
			foreach( $search_plugin->getSearchResults( '', array( 0, 10 ), 'date' ) as $r )
			{
				/* Display Plugin */
				if( ! isset( $display_plugins[ $r['app'] ] ) )
				{
					$display_plugins[ $r['app'] ] = IPSSearchIndex::getSearchDisplayPlugin( $r['app'] );
				}
				
				if( $display_plugins[ $r['app'] ] && method_exists( $display_plugins[ $r['app'] ], 'formatActivityFeedContent' ) )
				{
					$activity[] = $display_plugins[ $r['app'] ]->formatActivityFeedContent( $r );
				}
				else
				{
					$activity[] = $this->registry->output->getTemplate( 'profile' )->genericActivityFeedResult( $r );
				}
			}
		}
		
		return $this->registry->output->getTemplate( 'profile' )->tab_recentActivity( $activity );
	}
}