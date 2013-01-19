<?php
class ibEconomyRepPointsLike extends public_core_ajax_like
{	
	/**
	* Lets get our hook on
	* action overloader style
	*/
	public function _save( $relid )
	{	
		#thanks to Dreamscape for finding this bug!
		if ($this->request['like_freq'])
		{
			parent::_save( $relid );
		}
		
		if ( !$this->memberData['member_id'] || !$this->memberData['g_eco'])
		{		
			parent::_save( $relid );
		}
		
		#grab ibEconomy SQL queries
		require_once( IPSLib::getAppDir( 'ibEconomy' ) . '/sql/mysql_ibEconomy.php' );
		$this->ibEcoSql = new ibEconomyMySQL( $this->registry );
		
		#taking into account points per post group factor, and giver's group is disabled?
		if ( $this->memberData['g_eco_frm_ptsx'] < 0 && $this->settings['eco_ppr_grp_adj'] )
		{		
			$skipGiver = true;
		}
		
		$receiver = $this->ibEcoSql->grabPostById($relid);
		
		#taking into account points per post group factor, and receivers's group is disabled?
		if ( $receiver['g_eco_frm_ptsx'] < 0 && $this->settings['eco_ppr_grp_adj'] )
		{		
			// $skipReceiver = true;
		}		
		
		if ( !$this->settings['eco_ppr_pos_giver'] && !$this->settings['eco_ppr_pos_recr'] )
		{
			parent::_save( $relid );
		}
		else
		{
			if ( $this->settings['eco_ppr_pos_giver'] && !$skipGiver )
			{
				if ($this->settings['eco_ppr_pos_giver'] < 0)
				{	
					$giverPts = ( $this->memberData['g_eco_frm_ptsx'] == 0 || !$this->settings['eco_ppr_grp_adj'] ) ? $this->settings['eco_ppr_pos_giver'] : $this->settings['eco_ppr_pos_giver'] / $this->memberData['g_eco_frm_ptsx'];			
				}
				else
				{
					$giverPts = ( $this->memberData['g_eco_frm_ptsx'] == 0 || !$this->settings['eco_ppr_grp_adj'] ) ? $this->settings['eco_ppr_pos_giver'] : $this->memberData['g_eco_frm_ptsx'] * $this->settings['eco_ppr_pos_giver'];	
				}
			}
			
			if ( $this->settings['eco_ppr_pos_recr'] )
			{
				if ($this->settings['eco_ppr_pos_recr'] < 0)
				{	
					$receiverPts = ( $receiver['g_eco_frm_ptsx'] == 0 || !$this->settings['eco_ppr_grp_adj'] ) ? $this->settings['eco_ppr_pos_recr'] : $this->settings['eco_ppr_pos_recr'] / $receiver['g_eco_frm_ptsx'];			
				}
				else
				{
					$receiverPts = ( $receiver['g_eco_frm_ptsx'] == 0 || !$this->settings['eco_ppr_grp_adj'] ) ? $this->settings['eco_ppr_pos_recr'] : $receiver['g_eco_frm_ptsx'] * $this->settings['eco_ppr_pos_recr'];	
				}			
			}				
		}

		#give me my points for giving that sucker some rep (and give that sucker some too perhaps)
		if ( $giverPts )
		{
			$this->ibEcoSql->updateMemberPts( $this->memberData['member_id'], $giverPts, '+', TRUE, TRUE, $this->memberData[ $this->settings['eco_general_pts_field'] ] );
		}
		if ( $receiverPts )
		{
			$this->ibEcoSql->updateMemberPts( $receiver['member_id'], $receiverPts, '+', TRUE, TRUE, $receiver[ $this->settings['eco_general_pts_field'] ] );
		}

		#as you were..
		parent::_save( $relid );
	}
}