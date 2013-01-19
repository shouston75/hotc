<?php

/**
 * (e32) ibEconomy
 * Admin Module: Forum Points
 * @ ACP
 * + Forum Points Per Post Values Setup
 */

if ( ! defined( 'IN_ACP' ) )
{
	print "<h1>Incorrect access</h1>You cannot access this file directly. If you have recently upgraded, make sure you upgraded 'admin.php'.";
	exit();
}

class admin_ibEconomy_forum_points_forum_points extends ipsCommand 
{
	public $html;
	public $registry;
	
	private $form_code;
	private $form_code_js;
	
	private $forum_functions;	
	
	/**
	 * Main execution method
	 */
	public function doExecute( ipsRegistry $registry )
	{
		#load templates
		$this->html               = $this->registry->output->loadTemplate( 'cp_skin_ibEconomy' );
		
		#load lang
		ipsRegistry::getClass('class_localization')->loadLanguageFile( array( 'admin_ibEconomy' ) );		
		
		#do form codes
		$this->html->form_code    = '&amp;module=forum_points&amp;section=forum_points';
		$this->html->form_code_js = '&module=forum_points&section=forum_points';
		
		#Forum functions set up
		require_once( IPSLib::getAppDir( 'forums' ) . "/sources/classes/forums/class_forums.php" );
		require_once( IPSLib::getAppDir( 'forums' ) . '/sources/classes/forums/admin_forum_functions.php' );

		$this->forum_functions = new admin_forum_functions( $registry );
		$this->forum_functions->forumsInit();		
		
		#switcharoo
		switch( $this->request['do'] )
		{
			//******Edit Forum Point Values******//
			case 'edit_frm_pts':
				$this->registry->getClass('class_permissions')->checkPermissionAutoMsg( 'ibEconomy_forum_points_edit' );
				$this->editFrmPts();
			break;
			
			//******Reset ALL Member's Points Form******//
			case 'recalculate_all_points_form':
				$this->registry->getClass('class_permissions')->checkPermissionAutoMsg( 'ibEconomy_forum_points_reset' );
				$this->recalculatePtsForm();
			break;

			//******Reset ALL Member's Points******//
			case 'recalculate_all_points_init':
				$this->registry->getClass('class_permissions')->checkPermissionAutoMsg( 'ibEconomy_forum_points_reset' );
				$this->recalculatePointsInit();
			break;
			
			//******Reset ALL Member's Points******//
			case 'recalculate_all_points_do':
				$this->registry->getClass('class_permissions')->checkPermissionAutoMsg( 'ibEconomy_forum_points_reset' );
				$this->recalculatePoints();
			break;	
			
			//******Show Forum List/Form******//			
			case 'show_forum_points':				
			default:
				$this->registry->getClass('class_permissions')->checkPermissionAutoMsg( 'ibEconomy_forum_points_view' );
				$this->forumList();
			break;
		}
		
		#footer
		$this->registry->output->html .= $this->html->footer();
		
		#output
		$this->registry->output->html_main .= $this->registry->output->global_template->global_frame_wrapper();
		$this->registry->output->sendOutput();			
	}
	
	/**
	 * Update Forum Points
	 */
	public function recalculatePtsForm()
	{
		#buttons
		$buttonRows = $this->registry->class_ibEco_CP->makeButtonRow( array('frm_vals') );
		
		$this->registry->output->html .= $this->html->resetPointsForm($buttonRows);
	}
	
	/**
	 * Reset/Recalculate Forum Points Init
	 */
	public function recalculatePointsInit()
	{
		$url = $this->settings['base_url'].$this->html->form_code.'&amp;do=recalculate_all_points_do&amp;lastDid=0&amp;completed=0';
		
		$this->registry->output->multipleRedirectInit( $url );
	}
	
	/**
	 * Reset/Recalculate Forum Points
	 */
	public function recalculatePoints()
	{
		#init
		$lastDid 	= intval($this->request['lastDid']);
		$completed  = intval($this->request['completed']);
		$did		= 0;
		$ptsField 	= $this->settings['eco_general_pts_field'];
		$ptsDB		= ( $ptsField == 'eco_points' ) ? 'pfields_content' : 'members';
		$done		= false;
		
		if ($lastDid == 0)
		{
			#delete em
			$asset = $this->registry->mysql_ibEconomy->massDelete( 'points' );			
		}
		
		$bigQuery = $this->registry->mysql_ibEconomy->massRecalculateQuery( $lastDid );
		
		#got pts?
		if ( $this->DB->getTotalRows() )
		{
			while ( $row = $this->DB->fetch($bigQuery) )
			{
				$toAddPoster 	= 0;
				$toAddStarter 	= 0;
				$row['g_eco']					= $this->caches['group_cache'][ $row['member_group_id'] ]['g_eco'];
				$row['g_eco_frm_ptsx']			= $this->caches['group_cache'][ $row['member_group_id'] ]['g_eco_frm_ptsx'];
				$row['starter_g_eco'] 			= $this->caches['group_cache'][ $row['member_group_id'] ]['g_eco'];
				$row['starter_g_eco_frm_ptsx']	= $this->caches['group_cache'][ $row['starter_group_id'] ]['g_eco_frm_ptsx'];
				
				if ($row['g_eco'] && $row['g_eco_frm_ptsx']  >= 0 && $row['queued'] == 0 && ($row['eco_tpc_pts'] != 0 || $row['eco_rply_pts'] != 0 || $row['eco_get_rply_pts'] != 0) )
				{
					//$this->registry->ecoclass->showVars($toAddPoster = $this->forum_functions->forum_cache[1]);
					if ( $row['new_topic'] )
					{
						//$toAddPoster = $this->forum_functions->forum_cache[1][ $row['forum_id'] ]['eco_tpc_pts'];
						$toAddPoster = $row['eco_tpc_pts'];
					}
					else
					{
						//$toAddPoster = $this->forum_functions->forum_cache[1][ $row['forum_id'] ]['eco_rply_pts'];
						$toAddPoster = $row['eco_rply_pts'];
					}
					
					if ( $row['author_id'] != $row['starter_id'] && $row['starter_g_eco'] && $row['starter_g_eco_frm_ptsx'] > -1)
					{
						//$toAddStarter = $this->forum_functions->forum_cache[1][ $row['forum_id'] ]['eco_get_rply_pts'];
						$toAddStarter = $row['eco_get_rply_pts'];
					}
					
					$toAddPoster 	= ($row['g_eco_frm_ptsx'] > 0) 			? $toAddPoster * $row['g_eco_frm_ptsx'] 			: $toAddPoster;
					$toAddStarter 	= ($row['starter_g_eco_frm_ptsx'] > 0) 	? $toAddStarter * $row['starter_g_eco_frm_ptsx'] 	: $toAddStarter;
				}
					
				#give the poster their points
				if ($toAddPoster)
				{				
					$this->DB->buildAndFetch( array( 'update' => $ptsDB, 'set' => $ptsField.' = '.$ptsField.' + '.$toAddPoster, 'where' => 'member_id = '.$row['author_id'] ) );
				}
				
				#give the topic starter their points
				if ($toAddStarter)
				{
					$this->DB->buildAndFetch( array( 'update' => $ptsDB, 'set' => $ptsField.' = '.$ptsField.' + '.$toAddStarter, 'where' => 'member_id = '.$row['starter_id'] ) );							
				}
				
				$lastDid = $row['pid'];
				$did++;
			}
		}
		else
		{
			$done = true;
		}
		
		if ($done)
		{
			#rebuild cache
			$this->registry->ecoclass->acm(array('stats'));	
			
			#"redirect" message
			$this->registry->output->global_message = $this->lang->words['member_points_reset'];
			
			#do log
			$this->registry->adminFunctions->saveAdminLog( $this->lang->words['member_points_reset'] );		
			
			#get out o hear
			$this->registry->output->multipleRedirectFinish();		
		}
		else
		{
			$total 		= $completed+$did;
			$doneSoFar 	= $this->registry->getClass('class_localization')->formatNumber($total);
			$img		= '<img src="' . $this->settings['skin_acp_url'] . '/images/loading_anim.gif" alt="-" /> ';
			$url 		= $this->settings['base_url'].$this->html->form_code.'&amp;do=recalculate_all_points_do&amp;lastDid='.$lastDid.'&amp;completed='.$total;
			$msg		= $img.' '.sprintf($this->lang->words['done_with_x_posts'], $doneSoFar );
			
			$this->registry->output->multipleRedirectHit( $url, $msg );
		}
	}	
	
	/**
	 * Update Forum Points
	 */
	public function editFrmPts()
	{
		#no forums?
		if ( ! $this->request['forum_ids'] )
		{
			$this->registry->output->global_message = $this->lang->words['no_forum_ids_sent'];
			$this->forumList();
			return;		
		}
		
		#loop through, making pt value adjustments as per needed
		foreach ( $this->request['forum_ids'] AS $forum_id )
		{
			$thisForumTopicPoints = $this->registry->ecoclass->makeNumeric( $this->request['eco_tpc_pts_'.$forum_id], false);
			$thisForumReplyPoints = $this->registry->ecoclass->makeNumeric( $this->request['eco_rply_pts_'.$forum_id], false);
			$thisForumGetReplyPoints = $this->registry->ecoclass->makeNumeric( $this->request['eco_get_rply_pts_'.$forum_id], false);
			
			$this->DB->update( 'forums', array( 'eco_tpc_pts' => $thisForumTopicPoints ), 'id='.$forum_id );
			$this->DB->update( 'forums', array( 'eco_rply_pts' => $thisForumReplyPoints ), 'id='.$forum_id );
			$this->DB->update( 'forums', array( 'eco_get_rply_pts' => $thisForumGetReplyPoints ), 'id='.$forum_id );
		}
		
		#recache em
		$this->forum_functions->forumsInit();
		
		#"redirect" message
		$this->registry->output->global_message = $this->lang->words['forum_points_edited'];
		
		#do log
		$this->registry->adminFunctions->saveAdminLog( $this->lang->words['forum_pts_edited'] );		
		
		#get out o hear
		$this->registry->output->silentRedirectWithMessage( $this->settings['base_url'].$this->html->form_code.'&amp;do=show_forum_points' );
	}
	
			
	/**
	 * List Forums
	 */
	public function forumList()
	{
		#buttons
		$buttonRows = $this->registry->class_ibEco_CP->makeButtonRow( array('tools', 'reset_pts') );
		
		#list header
		$this->registry->output->html .= $this->html->renderForumHeader($buttonRows);
		
		#loop through forums, create form list
		foreach( $this->forum_functions->forum_cache['root'] as $forum_data )
		{
			$cat_data    = $forum_data;
			$depth_guide = "";
			$temp_html 	 = "";
			
			if ( isset($this->forum_functions->forum_cache[ $forum_data['id'] ]) AND is_array( $this->forum_functions->forum_cache[ $forum_data['id'] ] ) )
			{
				foreach( $this->forum_functions->forum_cache[ $forum_data['id'] ] as $forum_data )
				{
					if ( $forum_data['sub_can_post'] )
					{
						$temp_html .= $this->renderForum( $forum_data );
					}
					
					$temp_html .= $this->forumsGetChildren( $forum_data['id'], $level=0 );
				}
			}
			
			if( !$temp_html )
			{
				$temp_html = $this->html->renderNoForums( $cat_data['id'] );
			}
			
			$this->registry->output->html .= $this->forumShowCat( $temp_html, $cat_data );
			unset($temp_html);
		}	
		
		#output
		$this->registry->output->html .= $this->html->renderForumFooter( $this->lang->words['update_frm_pts'] );	
	}
	
	/**
	 * Get all the children
	 **/
	public function forumsGetChildren( $root_id, $level )
	{
		#have forum kids?
		if ( isset( $this->forum_functions->forum_cache[ $root_id ]) AND is_array( $this->forum_functions->forum_cache[ $root_id ] ) )
		{
			foreach( $this->forum_functions->forum_cache[ $root_id ] as $forum_data )
			{
				$level++;
				
				if ($level)
				{
					for ($i=0; $i<$level; $i++)
					{
						$spacing .= '--';
					}
						
				}
				
				$forum_data['name'] = $spacing.$forum_data['name'];
				
				$temp_html .= $this->renderForum( $forum_data );
				
				$temp_html .= $this->forumsGetChildren($forum_data['id'], $level, $ids);
			}
		}
		
		#return html
		return $temp_html;
	}	

	/**
	 * Show Cat
	 */	
	public function forumShowCat( $content, $r )
	{
		$this->printed++;
		
		$no_root = count( $this->forum_cache['root'] );

		$this->registry->output->html .= $this->html->forumWrapper( $content, $r );
	}

	/**
	 * Build forum
	 */	
	public function renderForum( $r )
	{		
		return $this->html->renderForumRow( $r );
	}	
	
}