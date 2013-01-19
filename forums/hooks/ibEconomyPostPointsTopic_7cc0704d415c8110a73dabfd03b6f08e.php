<?php
class ibEconomyPostPointsTopic
{
	/**
	 * Handle data for a new topic
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
		if ( !$this->memberData['member_id'] || !$this->memberData['g_eco'] || !$this->settings['eco_pts_per_topic'] || $this->memberData['g_eco_frm_ptsx'] < 0)
		{
			return $data;
		}	
	
		#grab ibEconomy SQL queries
		require_once( IPSLib::getAppDir( 'ibEconomy' ) . '/sql/mysql_ibEconomy.php' );
		$this->ibEcoSql = new ibEconomyMySQL( $this->registry );

		#pts per new topic
		if ( $this->forumData['eco_tpc_pts'] && $this->settings['eco_pts_per_topic'] )
		{
			#give me my new topic points
			if ($this->forumData['eco_tpc_pts'] < 0)
			{	
				$points2Add = ( $this->memberData['g_eco_frm_ptsx'] == 0 ) ? $this->forumData['eco_tpc_pts'] : $this->forumData['eco_tpc_pts'] / $this->memberData['g_eco_frm_ptsx'];			
			}
			else
			{
				$points2Add = ( $this->memberData['g_eco_frm_ptsx'] == 0 ) ? $this->forumData['eco_tpc_pts'] : $this->memberData['g_eco_frm_ptsx'] * $this->forumData['eco_tpc_pts'];	
			}			
			
			#not allow the post at all if the member doesn't have enough points and admin chooses to use the new setting(new to 1.6)
			if ($this->settings['eco_pts_disallow_post'] && $points2Add < 0 && $this->memberData[ $this->settings['eco_general_pts_field'] ] + $points2Add < 0)
			{
				$this->lang->words['not_enough_points_to_start_topic_in_this_forum'] = str_replace("<%POINTS_NAME%>", $this->settings['eco_general_currency'], $this->lang->words['not_enough_points_to_start_topic_in_this_forum']);
				$this->lang->words['not_enough_points_to_start_topic_in_this_forum'] = str_replace("<%AMOUNT%>", $points2Add * -1, $this->lang->words['not_enough_points_to_start_topic_in_this_forum']);
				$this->registry->output->showError( $this->lang->words['not_enough_points_to_start_topic_in_this_forum'] );
			}
			
			$this->ibEcoSql->updateMemberPts( $this->memberData['member_id'], $points2Add, '+', TRUE );
		}
		
		return $data;
	}
}
