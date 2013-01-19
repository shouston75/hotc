<?php

/**
 * (e32) ibEconomy
 * Public Module: Ajax
 * + Big Pop-up
 */

class public_ibEconomy_ajax_popform extends ipsAjaxCommand 
{
	/**
	 * Class entry point
	 */
	public function doExecute( ipsRegistry $registry ) 
	{
		#load lang
		$this->registry->class_localization->loadLanguageFile( array( 'public_ibEconomy') );
		
		#might generous of you
		if ( $this->request['do'] == 'donate' )
		{
			#can we donate?
			if ( !$this->memberData['g_eco'] || !$this->settings['eco_dons_on'] )
			{
				$this->returnString( 'error' );
			}
 
			#quick donate popup
			return $this->returnJsonArray( array( 'success' => $this->registry->getClass('output')->getTemplate('ibEconomy')->donatePop() ) );
		}
		else if ( $this->request['do'] == 'member' )
		{
			#can we ibEco?
			if ( !$this->memberData['g_eco'] || !$this->request['id'] || !$this->settings['eco_dons_on'])
			{
				$this->returnString( 'error' );
			}
			
			#load member ID
			$member = IPSMember::load( $this->request['id'], 'all' );
			
			if ( !$member['member_id'] )
			{	
				$this->returnString( 'error' );			
			}		
			
			#format most stuff
			$member = IPSMember::buildDisplayData( $member );
			
			#pt field
			$member['eco_points'] 		= $member[ $this->settings['eco_general_pts_field'] ];
			if ($this->settings['eco_plugin_ppns_on'] && ($member['ibEco_plugin_ppns_prefix'] || $member['ibEco_plugin_ppns_suffix'] || $this->settings['eco_plugin_ppns_use_gf']))
			{
				$member['formatted_name'] 	= IPSMember::makeNameFormatted( $member['members_display_name'], $member['member_group_id'], $member['ibEco_plugin_ppns_prefix'], $member['ibEco_plugin_ppns_suffix'] ); 
			}
			else
			{
				$member['formatted_name'] 	= $member['members_display_name']; 
			}
			
			#awfully big query just to grab 1 number (rank)
			$member['eco_worth_rank'] 	= $this->registry->mysql_ibEconomy->rankMembers( 'eco_worth', $member['member_id'] );						
 		
			#format some lang action
			$this->lang->words['view_eco_profile'] = str_replace( "<%IBECO_NAME%>", $this->settings['eco_general_name'], $this->lang->words['view_eco_profile'] );
			
			#where are we?
			$urlNow = $this->request['url'];
			
			#ibEconomy member pane pop
			return $this->returnJsonArray( array( 'success' => $this->registry->getClass('output')->getTemplate('ibEconomy')->memPanePop($member,$urlNow) ) );
		}
		else if ( $this->request['do'] == 'edit' )
		{
			#can we ibEco?
			if ( !$this->memberData['g_eco_edit_pts'] || !$this->request['id'] )
			{
				$this->returnString( 'error' );
			}
			
			#load member ID
			$member = IPSMember::load( $this->request['id'], 'all' );
			
			if ( !$member['member_id'] )
			{	
				$this->returnString( 'error' );			
			}		
			
			#format most stuff
			$member = IPSMember::buildDisplayData( $member );
			
			#pt field
			$member['eco_points'] 		= $member[ $this->settings['eco_general_pts_field'] ];
			if ($this->settings['eco_plugin_ppns_on'] && ($member['ibEco_plugin_ppns_prefix'] || $member['ibEco_plugin_ppns_suffix'] || $this->settings['eco_plugin_ppns_use_gf']))
			{
				$member['formatted_name'] 	= IPSMember::makeNameFormatted( $member['members_display_name'], $member['member_group_id'], $member['ibEco_plugin_ppns_prefix'], $member['ibEco_plugin_ppns_suffix'] ); 
			}
			else
			{
				$member['formatted_name'] 	= $member['members_display_name']; 
			}			
			
			#awfully big query just to grab 1 number (rank)
			$member['eco_worth_rank'] 	= $this->registry->mysql_ibEconomy->rankMembers( 'eco_worth', $member['member_id'] );						
 		
			#format some lang action
			$this->lang->words['view_eco_profile'] = str_replace( "<%IBECO_NAME%>", $this->settings['eco_general_name'], $this->lang->words['view_eco_profile'] );
			
			#where are we?
			$urlNow = $this->request['url'];
			
			#ibEconomy member pane pop
			return $this->returnJsonArray( array( 'success' => $this->registry->getClass('output')->getTemplate('ibEconomy')->editMemPtsPop($member,$urlNow) ) );
		}
		else
		{
			$this->returnString( 'error' );
		}
	}
}