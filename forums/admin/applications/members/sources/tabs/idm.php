<?php

/**
 * Invision Power Services
 * IP.Board v3.0.5
 * Profile Plugin Library
 * Last Updated: $Date: 2009-10-23 15:58:47 -0400 (Fri, 23 Oct 2009) $
 *
 * @author 		$Author: bfarber $
 * @copyright	(c) 2001 - 2009 Invision Power Services, Inc.
 * @license		http://www.invisionpower.com/community/board/license.html
 * @package		Invision Power Board
 * @subpackage	IP.Downloads
 * @link		http://www.invisionpower.com
 * @since		20th February 2002
 * @version		$Revision: 5271 $
 *
 * @todo 		[Future] Support read/unread markers in profile
 */

if ( ! defined( 'IN_IPB' ) )
{
	print "<h1>Incorrect access</h1>You cannot access this file directly. If you have recently upgraded, make sure you upgraded 'admin.php'.";
	exit();
}

class profile_idm extends profile_plugin_parent
{
	/**
	 * return HTML block
	 *
	 * @access	public
	 * @param	array		Member information
	 * @return	string		HTML block
	 */
	public function return_html_block( $member=array() ) 
	{
		if( !$this->DB->checkForField( "file_id", "downloads_files" ) )
		{
			return $this->lang->words['err_no_posts_to_show'];
		}
		
		/* Load Language */
		$this->lang->loadLanguageFile( array( 'public_downloads' ), 'downloads' );

		//-----------------------------------------
		// Get downloads library and API
		//-----------------------------------------
		
		require_once( IPS_ROOT_PATH . 'api/api_core.php' );
		require_once( IPSLib::getAppDir('downloads') . '/sources/api/api_idm.php' );
		
		//-----------------------------------------
		// Create API Object
		//-----------------------------------------
		
		$idm_api = new apiDownloads();
		$idm_api->init();

		//-----------------------------------------
		// Get images
		//-----------------------------------------
		
		$files = array();
		$files = $idm_api->returnDownloads( $member['member_id'], 10 );
		
		//-----------------------------------------
		// Ready to pull formatted stuff?
		//-----------------------------------------
		
		if( count($files) )
		{
			$data = array();

			foreach( $files as $row )
			{
				$row['navigation']	= array();
				$navigation			= $this->registry->getClass('categories')->getNav( $row['file_cat'] );
				
				foreach( $navigation as $nav )
				{
					$row['navigation'][]	= "<a href='" . $this->registry->getClass('output')->buildSEOUrl( $nav[1], 'public' ) . "' title='{$nav[0]}'>{$nav[0]}</a>";
				}
				
				$row['_isRead']	= $this->registry->classItemMarking->isRead( array( 'forumID' => $row['file_cat'], 'itemID' => $row['file_id'], 'itemLastUpdate' => $row['file_updated'] ), 'downloads' );

				$data[] = $row;
			}
			
			$output = $this->registry->getClass('output')->getTemplate('downloads_external')->profileDisplay( $data );
		}
		else
		{
			$output = $this->registry->getClass('output')->getTemplate('profile')->tabNoContent( 'no_files_in_category' );
		}
		      
		//-----------------------------------------
		// Macros...
		//-----------------------------------------
		
		$output = $this->registry->output->replaceMacros( $output );

		return $output;
	}
	
}