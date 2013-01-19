<?php

class ibEconomyPostPoints
{
	/**
	 * Handle data for a reply (not for a new topic, need another file for that)
	 **/
    public function handleData($data)
    {
		#construct stuff
		$this->registry     =  ipsRegistry::instance();
		$this->DB           =  $this->registry->DB();
		$this->settings     =& $this->registry->fetchSettings();
		$this->request      =& $this->registry->fetchRequest();
		$this->lang         =  $this->registry->getClass('class_localization');
		$this->member       =  $this->registry->member();
		$this->memberData   =& $this->registry->member()->fetchMemberData();
		$this->cache        =  $this->registry->cache();
		$this->caches       =& $this->registry->cache()->fetchCaches();
		$this->forumData    =  $this->registry->getClass('class_forums')->forum_by_id[ $this->request['f'] ];
				
		#perms and what not...
		if ( !$this->memberData['member_id'] || !$this->memberData['g_eco'] || !$this->settings['eco_pts_per_reply'] || $this->memberData['g_eco_frm_ptsx'] < 0)
		{
			return $data;
		}	
	
		#grab ibEconomy SQL queries
		require_once( IPSLib::getAppDir( 'ibEconomy' ) . '/sql/mysql_ibEconomy.php' );
		$this->ibEcoSql = new ibEconomyMySQL( $this->registry );

		#pts per reply
		if ( $this->forumData['eco_rply_pts'] && $this->settings['eco_pts_per_reply'] )
		{ 
			#give me my reply points
			if ($this->forumData['eco_rply_pts'] < 0)
			{	
				$points2Add = ( $this->memberData['g_eco_frm_ptsx'] == 0 ) ? $this->forumData['eco_rply_pts'] : $this->forumData['eco_rply_pts'] / $this->memberData['g_eco_frm_ptsx'];			
			}
			else
			{
				$points2Add = ( $this->memberData['g_eco_frm_ptsx'] == 0 ) ? $this->forumData['eco_rply_pts'] : $this->memberData['g_eco_frm_ptsx'] * $this->forumData['eco_rply_pts'];	
			}
				
			#not allow the post at all if the member doesn't have enough points and admin chooses to use the new setting(new to 1.6)
			if ($this->settings['eco_pts_disallow_post'] && $points2Add < 0 && $this->memberData[ $this->settings['eco_general_pts_field'] ] + $points2Add < 0)
			{
				$this->lang->words['not_enough_points_to_post_in_this_forum'] = str_replace("<%POINTS_NAME%>", $this->settings['eco_general_currency'], $this->lang->words['not_enough_points_to_post_in_this_forum']);
				$this->lang->words['not_enough_points_to_post_in_this_forum'] = str_replace("<%AMOUNT%>", $this->registry->getClass('class_localization')->formatNumber($points2Add * -1), $this->lang->words['not_enough_points_to_post_in_this_forum']);
				
				#if this is a normal post...
				if ($this->request['attach_post_key'])
				{
					$this->registry->output->showError( $this->lang->words['not_enough_points_to_post_in_this_forum'] );			
				}
				else
				{		
					throw new Exception( $this->lang->words['not_enough_points_to_post_in_this_forum'] );			
				}
			}

			$this->ibEcoSql->updateMemberPts( $this->memberData['member_id'], $points2Add, '+', TRUE );
		}

		#pts to topic starter for replies
		$topic = $this->ibEcoSql->grabTopicByID($data['topic_id']);
		
		if ( $topic['starter_id'] > 0 && $this->forumData['eco_get_rply_pts']  && $topic['starter_id'] != $this->memberData['member_id'] && $this->settings['eco_pts_per_get_reply'] )
		{
			#grab topic starter group
			$topicStarter = $this->DB->buildAndFetch( array( 'select' => 'member_group_id','from' => 'members','where'  => 'member_id='.$topic['starter_id'] ) );

			#topic starter's forum pts multiplier is..
			$topicStarter['g_eco_frm_ptsx'] = $this->caches['group_cache'][ $topicStarter['member_group_id'] ]['g_eco_frm_ptsx'];

			#add points to topic starter
			if ($this->forumData['eco_get_rply_pts'] < 0)
			{	
				$points2Add = ( $topicStarter['g_eco_frm_ptsx'] == 0 ) ? $this->forumData['eco_get_rply_pts'] : $this->forumData['eco_get_rply_pts'] / $topicStarter['g_eco_frm_ptsx'];			
			}
			else
			{
				$points2Add = ( $topicStarter['g_eco_frm_ptsx'] == 0 ) ? $this->forumData['eco_get_rply_pts'] : $topicStarter['g_eco_frm_ptsx'] * $this->forumData['eco_get_rply_pts'];	
			}
			
			$this->ibEcoSql->updateMemberPts( $topic['starter_id'], $points2Add, '+', TRUE );
			
			return $data;
		}
		
		return $data;
	}
}