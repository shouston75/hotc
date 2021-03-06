<?php

class ibEconomyRepPoints extends public_core_ajax_reputation
{	
	/**
	* Lets get our hook on
	* action overloader style
	*/
	public function _doRating()
	{	
		#init
		$app     = $this->request['app_rate'];
		$type    = $this->request['type'];
		$type_id = intval( $this->request['type_id'] );
		$rating  = intval( $this->request['rating'] );
		
		#guest? SKIP IT!
		if ( !$this->memberData['member_id'] )
		{		
			parent::_doRating();
		}

		#member's group has no access to ibEconomy?
		if ( !$this->memberData['g_eco'] )
		{		
			parent::_doRating();
		}		
		
		#run normal checks to make sure it's all legit (checks #1)
		if( !$this->checkIt() )
		{			
			parent::_doRating();
		}

		#grab receiver's info plus run last rep checks
		$receiver = $this->checkItMore( $type, $type_id, $rating, '', 0, $app );

		#run normal checks to make sure it's all legit
		if( !$receiver )
		{
			parent::_doRating();
		}
		
		#taking into account points per post group factor, and giver's group is disabled?
		if ( $this->memberData['g_eco_frm_ptsx'] < 0 && $this->settings['eco_ppr_grp_adj'] )
		{		
			$skipGiver = true;
		}
		
		#taking into account points per post group factor, and receivers's group is disabled?
		if ( $receiver['g_eco_frm_ptsx'] < 0 && $this->settings['eco_ppr_grp_adj'] )
		{		
			$skipReceiver = true;
		}		
		
		#we're positive repping?
		if ( $rating == 1 )
		{		
			if ( !$this->settings['eco_ppr_pos_giver'] && !$this->settings['eco_ppr_pos_recr'] )
			{
				parent::_doRating();
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
		}
		#nope, negative repping...
		else
		{		
			if ( !$this->settings['eco_ppr_neg_giver'] && !$this->settings['eco_ppr_neg_recr'] )
			{
				parent::_doRating();
			}
			else
			{
				if ( $this->settings['eco_ppr_neg_giver'] && !$skipReceiver )
				{
					$giverPts = ( $this->memberData['g_eco_frm_ptsx'] == 0 || !$this->settings['eco_ppr_grp_adj'] ) ? $this->settings['eco_ppr_neg_giver'] : $this->memberData['g_eco_frm_ptsx'] * $this->settings['eco_ppr_neg_giver'];
				}
				
				if ( $this->settings['eco_ppr_neg_recr'] )
				{
					$receiverPts = ( $receiver['g_eco_frm_ptsx'] == 0 || !$this->settings['eco_ppr_grp_adj'] ) ? $this->settings['eco_ppr_neg_recr'] : $receiver['g_eco_frm_ptsx'] * $this->settings['eco_ppr_neg_recr'];				
				}				
			}		
		}
		
		#grab ibEconomy SQL queries
		require_once( IPSLib::getAppDir( 'ibEconomy' ) . '/sql/mysql_ibEconomy.php' );
		$this->ibEcoSql = new ibEconomyMySQL( $this->registry );

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
		parent::_doRating();
	}
	
	/**
	* Duplicate standard rep checks
	*/
	public function checkIt()
	{
		#INIT
		$app     = $this->request['app_rate'];
		$type    = $this->request['type'];
		$type_id = intval( $this->request['type_id'] );
		$rating  = intval( $this->request['rating'] );
		
		#Check
		if( ! $app || ! $type || ! $type_id || ! $rating )
		{
			return false;
		}
		
		#Check the secure key. Needed here to prevent direct URLs from increasing reps
		if ( $this->request['secure_key'] != $this->member->form_hash )
		{
			return false;		
		}
		
		return true;
	}
	
	/**
	 * Duplicate further rep checks
	 */
	public function checkItMore( $type, $type_id, $rating, $message='', $member_id=0, $app='' )
	{
		ipsRegistry::instance()->getClass('class_localization')->loadLanguageFile( array( 'public_global' ), 'core' );
		
		#Online?
		if( ! ipsRegistry::$settings['reputation_enabled'] )
		{
			return false;
		}
		
		#INIT
		$app       = ( $app ) ? $app : ipsRegistry::$current_application;
		$rating    = intval( $rating );
		
		if( ! ipsRegistry::member()->getProperty( 'member_id' ) )
		{	
			return false;
		}
		
		if( $rating != -1 && $rating != 1 )
		{		
			return false;
		}
		
		#Check the point types
		if( $rating == -1 && ipsRegistry::$settings['reputation_point_types'] == 'positive' )
		{		
			return false;
		}
		
		if( $rating == 1 && ipsRegistry::$settings['reputation_point_types'] == 'negative' )
		{		
			return false;
		}
		
		#Day Cutoff
		$day_cutoff = time() - 86400;

		#Check Max Positive Votes
		if( $rating == 1 )
		{
			if( intval( ipsRegistry::member()->getProperty( 'g_rep_max_positive' ) ) === 0 )
			{			
				return false;				
			}
			
			$total = ipsRegistry::DB()->buildAndFetch( array( 
																'select' => 'count(*) as votes', 
																'from'   => 'reputation_index', 
																'where'  => 'member_id=' . ipsRegistry::member()->getProperty( 'member_id' ) . ' AND rep_rating=1 AND rep_date > ' . $day_cutoff
															)	);
					
			if( $total['votes'] >= ipsRegistry::member()->getProperty( 'g_rep_max_positive' ) )
			{			
				return false;				
			}
		}
		
		#Check Max Negative Votes
		if( $rating == -1 )
		{
			if( intval( ipsRegistry::member()->getProperty( 'g_rep_max_negative' ) ) === 0 )
			{			
				return false;				
			}
			
			$total = ipsRegistry::DB()->buildAndFetch( array( 
																'select' => 'count(*) as votes', 
																'from'   => 'reputation_index', 
																'where'  => 'member_id=' . ipsRegistry::member()->getProperty( 'member_id' ) . ' AND rep_rating=-1 AND rep_date > ' . $day_cutoff
														)	);
													
			if( $total['votes'] >= ipsRegistry::member()->getProperty( 'g_rep_max_negative' ) )
			{			
				return false;
			}
		}		
		
		#If no member id was passted in, we have to query it using the config file
		if( ! $member_id )
		{
			#Reputation Config
			if( file_exists( IPSLib::getAppDir( $app ) . '/extensions/reputation.php' ) )
			{
				require( IPSLib::getAppDir( $app ) . '/extensions/reputation.php' );
			}
			else
			{			
				return false;
			}
			
			if( ! $rep_author_config[$type]['column'] || ! $rep_author_config[$type]['table'] )
			{		
				return false;
			}
			
			#Query the content author
			$content_author = ipsRegistry::DB()->buildAndFetch( array(
																		'select' => "{$rep_author_config[$type]['column']} as id",
																		'from'   => $rep_author_config[$type]['table'],
																		'where'  => "{$type}={$type_id}"
															)	);
			
			$member_id = $content_author['id'];
		}
		
		if( ! ipsRegistry::$settings['reputation_can_self_vote'] && $member_id == ipsRegistry::member()->getProperty( 'member_id' ) )
		{	
			return false;
		}
		
		#get rest of receiver's info
		$receiver = IPSMember::load( $member_id, 'all' );
		
		#Query the member group
		if( ipsRegistry::$settings['reputation_protected_groups'] )
		{	
			if( in_array( $receiver['member_group_id'], explode( ',', ipsRegistry::$settings['reputation_protected_groups'] ) ) )
			{			
				return false;			
			}
		}
		
		return $receiver;
	}
}