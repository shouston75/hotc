<?php
/**
 * (e32) ibEconomy
 * Profile Tab
 */

if ( ! defined( 'IN_IPB' ) )
{
	print "<h1>Incorrect access</h1>You cannot access this file directly. If you have recently upgraded, make sure you upgraded all the relevant files.";
	exit();
}

class profile_ibEconomyProfileTab extends profile_plugin_parent
{
	/**
	 * Feturn HTML block
	 *
	 * @access	public
	 * @param	array		Member information
	 * @return	string		HTML block
	 */
	public function return_html_block( $member=array() ) 
	{
		#load lang
		ipsRegistry::getClass('class_localization')->loadLanguageFile( array( 'public_ibEconomy' ), 'ibEconomy' );
			
		if ( ! is_array( $member ) OR ! count( $member ) OR !$member['g_eco'] )
		{
			return $this->registry->getClass('output')->getTemplate('profile')->tabNoContent( 'err_no_ibEconomy_profile_to_show' );
		}

		#master ibEconomy Class
		if ( ! $this->registry->isClassLoaded( 'ecoclass' ) )
		{
			require_once( IPSLib::getAppDir( 'ibEconomy' ) . "/sources/ecoclass.php" );
			$this->registry->setClass( 'ecoclass', new class_ibEconomy( $this->registry ) );
		}
		
		#master ibEconomy SQL Queries
		if ( ! $this->registry->isClassLoaded( 'mysql_ibEconomy' ) )
		{
			require_once( IPSLib::getAppDir( 'ibEconomy' ) . "/sql/mysql_ibEconomy.php" );
			$this->registry->setClass( 'mysql_ibEconomy', new ibEconomyMySQL( $this->registry ) );
		}

		#awfully big query just to grab 1 number (rank)
		$member['eco_rank'] 	= $this->registry->mysql_ibEconomy->rankMembers( ($this->settings['eco_worth_on']) ? 'eco_worth' : 'points', $member['member_id'] );
		
		#load last 10 items
		if ( $this->settings['eco_assets_on'] && $this->memberData['g_eco_asset'] )
		{
			$assetRows = "";
			
			#make sure we have item cache
			$this->registry->ecoclass->acm( array('banks', 'stocks', 'lts', 'ccs', 'shopitems') );
			#query em
			$o = $this->registry->mysql_ibEconomy->grabPortfolioItems( $member['member_id'], 'all', $type='pub', $ids='', '', '', '', '' );

			#make loop through assets
			if ( $this->DB->getTotalRows() )
			{
				while ( $row = $this->DB->fetch($o) )
				{
					#format for rows
					$row = $this->registry->ecoclass->formatPortRow( $row );
					
					$assetRows .= $this->registry->output->getTemplate('ibEconomy')->profileTabItemRow( $row );
				}
			}
			
			if ( !$assetRows )
			{
				$this->lang->words['none_to_show_ass'] = str_replace( "<%TYPE%>", $this->lang->words['assets'], $this->lang->words['none_to_show'] );
			}
		}
		else
		{
			$this->lang->words['none_to_show_ass'] = $this->lang->words['assets_off'];
		}
		
		#load last 10 transactions
		if ( $this->settings['eco_transactions_on'] && $this->memberData['g_eco_transaction'] && (!$this->settings['eco_general_only_my_trans'] || $this->memberData['g_eco_edit_pts'] || $this->memberData['member_id'] == $member['member_id']) )
		{
			$transactionRows = "";
			$rowNum			 = 0;
			
			#image action map
			$actionImg = $this->registry->ecoclass->grabActionMap();

			#query log table
			$this->registry->mysql_ibEconomy->getLogs( 'everythingButPurchases', $member['member_id'] );
		
			if ( $this->DB->getTotalRows() )
			{
				while ( $row = $this->DB->fetch() )
				{
					#format for rows
					$row = $this->registry->ecoclass->formatLogRow( $row, $rowNum );
					
					$transactionRows .= $this->registry->output->getTemplate('ibEconomy')->profileTabLogRow( $row );
					
					$rowNum++;
				}
			}
			
			if ( !$transactionRows )
			{
				$this->lang->words['none_to_show_trans'] = str_replace( "<%TYPE%>", $this->lang->words['transactions'], $this->lang->words['none_to_show'] );
			}
		}
		else
		{
			$this->lang->words['none_to_show_trans'] = $this->lang->words['transactions_off'];
		}
		
		#new to 1.6
		$shopItems = $this->registry->ecoclass->miniBoxedShopItems($member['member_id']);
		
		$content = $this->registry->getClass('output')->getTemplate('ibEconomy')->profileTab( $assetRows, $transactionRows, $member, $shopItems );
		
		#Replace Macros and return
		$content = $this->registry->output->replaceMacros( $content );
		
		return $content ? $content : $this->registry->getClass('output')->getTemplate('profile')->tabNoContent( 'err_no_ibEconomy_profile_to_show' );
	}
	
}