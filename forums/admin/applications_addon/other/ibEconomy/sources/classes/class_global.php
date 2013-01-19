<?php

/**
 * (e32) ibEconomy
 * Global Class
 * @ Global Economy Tab
 * + Display Stuff
 * + Non-tab-specific things
 */

if ( ! defined( 'IN_IPB' ) )
{
	print "<h1>Incorrect access</h1>You cannot access this file directly. If you have recently upgraded, make sure you upgraded all the relevant files.";
	exit();
}

class class_global
{
	private $showPage	= "";
	
	protected $registry;
	protected $DB;
	protected $settings;
	protected $request;
	protected $lang;
	protected $member;
	protected $cache;	

	public	  $tabNamesAndTitles;
	public	  $areaNamesAndTitles;
	/**
	 * Class entry point
	 */
	public function __construct( ipsRegistry $registry )
	{
        $this->registry     =  ipsRegistry::instance();
        $this->DB           =  $this->registry->DB();
        $this->settings     =& $this->registry->fetchSettings();
        $this->request      =& $this->registry->fetchRequest();
        $this->lang         =  $this->registry->getClass('class_localization');
        $this->member       =  $this->registry->member();
        $this->memberData  	=& $this->registry->member()->fetchMemberData();
        $this->cache        =  $this->registry->cache();
        $this->caches       =& $this->registry->cache()->fetchCaches();
		
		$this->buildTabNamesAndTitles();
		//$this->buildAreaNamesAndTitles();
	}
	
	/**
	* Create tab names and titles map
	*/
	public function buildTabNamesAndTitles()
	{	
		$this->tabNamesAndTitles = array();
		
		$this->tabNamesAndTitles['me'] 		= array('name' => $this->lang->words['my_portfolio'], 	'title' => $this->lang->words['view_my_portfolio']);
		$this->tabNamesAndTitles['global'] 	= array('name' => $this->lang->words['global_eco'], 	'title' => $this->lang->words['view_global_economy']);
		$this->tabNamesAndTitles['invest'] 	= array('name' => $this->lang->words['invest_tab'], 	'title' => $this->lang->words['view_investments_opps']);
		$this->tabNamesAndTitles['shop'] 	= array('name' => $this->lang->words['shop'], 			'title' => $this->lang->words['view_shop_menu']);
		$this->tabNamesAndTitles['cash'] 	= array('name' => $this->lang->words['quick_cash'], 	'title' => $this->lang->words['view_cash_menu']);
		$this->tabNamesAndTitles['buy'] 	= array('name' => $this->lang->words['cashier'], 		'title' => $this->lang->words['view_purchase_menu']);
	}
	
	/**
	* Create area names and titles map
	* Only pertains to "non-standard" areas, like when viewing a specific member or single item, etc
	*/
	public function buildAreaNamesAndTitles()
	{	
		$this->areaNamesAndTitles = array();
		
		switch ($this->registry->ecoclass->active['tab'])
		{
			case ('me'):
				$this->areaNamesAndTitles['single'] 		= array('name' => $this->registry->ecoclass->active['item_name'], 	'title' => $this->registry->ecoclass->active['item_name']);
				$this->areaNamesAndTitles['using_shopitem'] = array('name' => $this->lang->words['using_item'], 				'title' => $this->lang->words['using_item']);
			break;
			
			default:
			case ('global'):
				$this->areaNamesAndTitles['member'] 		= array('name' => $this->registry->ecoclass->active['member_prof'], 'title' => $this->registry->ecoclass->active['member_prof']);
			break;

			case ('invest'):
				$this->areaNamesAndTitles['single'] 		= array('name' => $this->registry->ecoclass->active['item_name'], 	'title' => $this->registry->ecoclass->active['item_name']);
			break;

			case ('shop'):
				$this->areaNamesAndTitles['cat'] 			= array('name' => $this->registry->ecoclass->active['cat_name'], 	'title' => $this->registry->ecoclass->active['cat_name']);
				$this->areaNamesAndTitles['single'] 		= array('name' => $this->registry->ecoclass->active['item_name'], 	'title' => $this->registry->ecoclass->active['item_name']);				
			break;		
		}
	}
	
	/**
	* Build the current tab's appropriate area siderows
	*/
	public function buildMoreURL($areaName, $OGAreaName)
	{
		$moreURL		= "app=ibEconomy&amp;tab={$this->registry->ecoclass->active['tab']}&amp;area={$areaName}";
		if ($this->request['type'] && $areaName == 'single')
		{
			$moreURL		.= "&amp;type={$this->request['type']}";
		}
		if ($this->request['id'] && $areaName == 'single')
		{
			$moreURL		.= "&amp;id={$this->request['id']}";
		}
		if ($this->request['bank_type'] && $areaName == 'single')
		{ 
			$moreURL		.= "&amp;bank_type={$this->request['bank_type']}";
		}
		if ($this->request['cat'] && $areaName == 'items' && $OGAreaName == 'cat')
		{ 
			$moreURL		.= "&amp;cat={$this->request['cat']}";
		}		
		return $moreURL;
	}
	
	/**
	* Build the current tab's appropriate area siderows
	*/
	public function buildAreaHTML()
	{	
		$areas = "";
		$this->buildAreaNamesAndTitles();
		$thisTabsAreas = $this->registry->ecoclass->tabs[ $this->registry->ecoclass->active['tab'] ];
		
		foreach ($thisTabsAreas AS $areaName => $areaArray)
		{
			#$isActive, $curTab, $areaName, $areaLangName, $title
			$isActive 	 = ($this->registry->ecoclass->active['area'] == $areaName) ? true : false;

			#man, I have to add a check for ONE freaking page?  (out of dozens???)
			$isActive 	 	= ($areaName == 'items' && intval($this->request['cat']) > 0) ? false : $isActive;
			$areaLangName 	= $thisTabsAreas[ $areaName ]['name'];
			$title			= $thisTabsAreas[ $areaName ]['title'];
			$moreURL		= $this->buildMoreURL(($this->request['cat'] && $areaName == 'cat') ? 'items' : $areaName, $areaName);
			
			$areas  	   .= $this->registry->output->getTemplate('ibEconomy')->area_side_bar($isActive, $areaLangName, $title, $moreURL);
		}
		
		#need to add a special area, like when using and item or viewing a specific item/member, etc
		$needOneMore = false;
		switch ($this->registry->ecoclass->active['tab'])
		{
			case 'me':
				if ($this->registry->ecoclass->active['area'] == 'using_shopitem')
				{
					$needOneMore 	= true;
					$areaName 		= $this->registry->ecoclass->active['area'];
				}			
			break;
			
			case 'global':
				if ($this->registry->ecoclass->active['area'] == 'member')
				{
					$needOneMore 	= true;
					$areaName 		= $this->registry->ecoclass->active['area'];
				}
			break;

			case 'shop':
				if ($this->registry->ecoclass->active['area'] == 'items' && (intval($this->request['cat']) > 0) )
				{
					$needOneMore 	= true;
					$areaName 		= 'cat';
				}
			break;
		}
		
		if ($needOneMore)
		{
			$moreURL	 = $this->buildMoreURL(($this->request['cat'] && $areaName == 'cat') ? 'items' : $areaName, $areaName);
			$areas 		.= $this->registry->output->getTemplate('ibEconomy')->area_side_bar(true, $this->areaNamesAndTitles[ $areaName ]['name'], $this->areaNamesAndTitles[ $areaName ]['title'], $moreURL);
		}
		
		return $areas;
	}
	
	/**
	* Build the Tabs row
	*/
	public function buildTabHTML()
	{	
		$tabs = "";
		
		foreach ($this->registry->ecoclass->tabs AS $tabName => $areaArray)
		{
			$isActive 	= ($this->registry->ecoclass->active['tab'] == $tabName) ? true : false;
			
			$tabs      .= $this->registry->output->getTemplate('ibEconomy')->tab($isActive, $tabName, $this->tabNamesAndTitles[ $tabName ]['name'], $this->tabNamesAndTitles[ $tabName ]['title']);
		}

		return $tabs;
	}
	
	/**
	 * Edit a member's on hand points from his/her board profile
	 */
	public function editMemberPoints()
	{
		#init
		$editReason 	= IPSText::parseCleanValue($this->request['edit_reason']);
		$editAmount 	= $this->registry->ecoclass->makeNumeric( $this->request['edit_amount'], false );
		$newTotalAmt 	= $this->registry->ecoclass->makeNumeric( $this->request['new_point_total'] + $editAmount, false );
		$oldTotalAmt 	= $this->registry->ecoclass->makeNumeric( $this->request['old_point_total'], false );
		$updateAmt		= $newTotalAmt - $oldTotalAmt;
		
		#group permitted to edit member's points?
		$this->registry->ecoclass->canAccess('edit_points', false);	

		#load editee name and such
		$editee = IPSMember::load( $this->request['id'], 'pfields_content' );
		
		#no member ID?
		if ( !$editee['member_id'] )
		{
			$memberName = $this->request['mem_name'];
			
			#load member ID
			$member = IPSMember::load( $memberName, 'pfields_content', 'displayname' );
			
			$this->request['id'] = $member['member_id'];
			
			#no member found by ID OR display name
			if ( !$member['member_id'] )
			{
				$this->registry->output->showError( str_replace("<%TYPE%>", $this->lang->words['member'], $this->lang->words['none_found_show'] ) );
			}
			
			$editee = $member;
		}

		#give points to them
		$this->registry->mysql_ibEconomy->updateMemberPts( $this->request['id'], $updateAmt, '+', true );
		
		#make edit amt pretty for pm, log, redirect
		$updateAmt = $this->registry->getClass('class_localization')->formatNumber( $updateAmt, $this->registry->ecoclass->decimal );	
		
		#add log
		$this->registry->ecoclass->addLog( 'edit', $updateAmt, $this->request['id'], $editee['members_display_name'], $editReason );

		#redirect
		$fromURL = explode('_', $this->request['from_url']);
		
		#where we going back to?
		if ( $fromURL[3] == 'forums' )
		{
			$redirectURL = $this->settings['base_url'] . 'showtopic=' . intval( $fromURL[4] ) . '&amp;st=' . intval( $fromURL[5] );
		}
		else if ( $fromURL[3] == 'members' && $fromURL[5] != 'profile' )
		{
			$redirectURL = $this->settings['base_url'] . "app=members&amp;module=messaging&amp;section=view&amp;do=showConversation&amp;topicID=" . $fromURL[4] . "&st=" . $fromURL[5];
		}
		else if ( $fromURL[3] == 'members' && $fromURL[5] == 'profile' )
		{
			$redirectURL = $this->settings['base_url'] . 'showuser=' . $fromURL[4];			
		}		
		else
		{
			$redirectURL = $this->settings['base_url'] . "app=ibEconomy&amp;tab=global&amp;area=member&amp;id=".$this->request['id'];
		}
		
		if ( $this->request['new_point_total'] != $this->request['old_point_total'] )
		{
			$redirect_message = str_replace( "<%POINTS%>", $newTotalAmt, $this->lang->words['edit_successful'] );
		}
		else
		{
			$redirect_message = str_replace( "<%POINTS%>", $updateAmt, $this->lang->words['edit_successful_add'] );
		}
		
		$redirect_message = str_replace( "<%POINTS_NAME%>", $this->settings['eco_general_currency'], $redirect_message );
		$this->registry->output->redirectScreen( $redirect_message, $redirectURL );					
	}
	
	/**
	 * Donate to your fellow man
	 */
	public function donate()
	{
		#init
		$donateReason 	= IPSText::parseCleanValue($this->request['donation_reason']);
		$donationAmount = $this->registry->ecoclass->makeNumeric( $this->request['donation_amount'], true );

		#permission and enabled?
		$this->registry->ecoclass->canAccess('donations', false);

		#load donatee name and make sure we have a donatee...
		$donatee = IPSMember::load( $this->request['id'], 'pfields_content' );
		
		#no member ID?
		if ( !$donatee['member_id'] )
		{
			$memberName = trim($this->request['mem_name']);

			#load member ID
			$member = IPSMember::load( $memberName, 'pfields_content', 'displayname' );
			
			#no member found by ID OR display name
			if ( !$member['member_id'] )
			{
				$this->registry->output->showError( str_replace("<%TYPE%>", $this->lang->words['member'], $this->lang->words['none_found_show'] ) );
			}
			
			$donatee['member_id']				= $member['member_id'];
			$donatee['members_display_name'] 	= $memberName;			
		}
		
		#trying to donate to yourself for tax writeoffs?
		if ( $this->memberData['member_id'] == $donatee['member_id'] )
		{
			$this->registry->output->showError( $this->lang->words['cant_donate_self'] );
		}
		
		#trying to donate 0?
		if ( ! $donationAmount )
		{
			$error = str_replace( "<%POINTS_NAME%>", $this->settings['eco_general_currency'], $this->lang->words['no_donation'] );
			$this->registry->output->showError( $error );
		}
		
		#trying to donate more than we have on hand?
		if ( $donationAmount > $this->memberData[ $this->settings['eco_general_pts_field'] ] )
		{
			$error = str_replace( "<%POINTS_NAME%>", $this->settings['eco_general_currency'], $this->lang->words['donation_over_my_pts'] );
			$this->registry->output->showError( $error );
		}
		
		#trying to donate a negative amount?
		if ( $donationAmount < 0 )
		{
			$error = str_replace( "<%POINTS_NAME%>", $this->settings['eco_general_currency'], $this->lang->words['negative_donation'] );
			$this->registry->output->showError( $error );
		}	
		
		#take points from me
		$this->registry->mysql_ibEconomy->updateMemberPts( $this->memberData['member_id'], $donationAmount, '-', true );
		
		#give points to them
		$this->registry->mysql_ibEconomy->updateMemberPts( $donatee['member_id'], $donationAmount, '+', true );
		
		#add log
		$this->registry->ecoclass->addLog( 'donation', $donationAmount, $donatee['member_id'], $donatee['members_display_name'], $donateReason );
		
		#make donation amt pretty for pm and redirect
		$donationAmount = $this->registry->getClass('class_localization')->formatNumber( $donationAmount, $this->registry->ecoclass->decimal );	
		
		#send PM
		$this->registry->ecoclass->sendPM( $donatee['member_id'], $donatee['members_display_name'], $donationAmount, $donateReason, 'donation' );
		
		#redirect
		$fromURL = explode('_', $this->request['from_url']);
		
		#where we going back to?
		if ( $fromURL[3] == 'forums' )
		{
			$redirectURL = $this->settings['base_url'] . 'showtopic=' . intval( $fromURL[4] ) . '&amp;st=' . intval( $fromURL[5] );
		}
		else if ( $fromURL[3] == 'members' && $fromURL[5] != 'profile' )
		{
			$redirectURL = $this->settings['base_url'] . "app=members&amp;module=messaging&amp;section=view&amp;do=showConversation&amp;topicID=" . $fromURL[4] . "&st=" . $fromURL[5];
		}
		else if ( $fromURL[3] == 'members' && $fromURL[5] == 'profile' )
		{
			$redirectURL = $this->settings['base_url'] . 'showuser=' . $fromURL[4];			
		}		
		else
		{
			$redirectURL = $this->settings['base_url'] . "app=ibEconomy&amp;tab=global&amp;area=member&amp;id=".$donatee['member_id'];
		}
		
		$redirect_message = str_replace( "<%POINTS%>", $donationAmount, $this->lang->words['donation_successful'] );
		$redirect_message = str_replace( "<%POINTS_NAME%>", $this->settings['eco_general_currency'], $redirect_message );
		$this->registry->output->redirectScreen( $redirect_message, $redirectURL );					
	}

	
	/**
	 * Find a Member!
	 */
	public function findMember()
	{
		$this->showPage = $this->registry->output->getTemplate('ibEconomy')->find_member();
		
		return $this->showPage;	
	}
	
	/**
	 * Execute Member (the finding of him/her I mean)
	 */
	public function doFindMember()
	{
		$memberName = $this->request['mem_name'];
		
		#load member ID
		$member = IPSMember::load( $memberName, 'pfields_content', 'displayname' );
		
		$this->registry->output->silentRedirect( $this->settings['base_url'].'app=ibEconomy&amp;tab=global&amp;area=member&amp;id='.$member['member_id'] );
	}	

	/**
	 * Show a member's ibEco profile!
	 */
	public function member()
	{
		#init return
		$returnThis = array();
		
		#load member ID
		$member = IPSMember::load( $this->request['id'], 'all' );
		
		if ( !$member['member_id'] )
		{	
			$this->registry->output->showError( $this->lang->words['no_member_found_by_id'] );			
		}		
		
		#format most stuff
		$member = IPSMember::buildDisplayData( $member );
		
		#pt field
		$member['eco_points'] 		= $member[ $this->settings['eco_general_pts_field'] ];
		
		#query for all members with points to rank em		
		$rankings = $this->registry->mysql_ibEconomy->rankMembers( ($this->settings['eco_worth_on']) ? 'eco_worth' : 'points', $noid );			
		
		#awfully big query just to grab 1 number (rank)
		//$member['eco_worth_rank'] 	= $this->registry->mysql_ibEconomy->rankMembers( 'eco_worth', $member['member_id'] );			
		$member['eco_worth_rank'] 	= ( $rankings[ $member['member_id'] ] ) ? $this->lang->words['globally_ranked']." #<span style='font-weight:bolder'>".$this->registry->getClass('class_localization')->formatNumber( $rankings[ $member['member_id'] ] )."</span>" : $this->lang->words['unranked'];
		
		#get stats page
		$member['eco_stats'] = $this->registry->class_me->myOverview($member);
		
		#format name
		$member['formatted_name'] 	= IPSMember::makeNameFormatted( $row['members_display_name'], $row['member_group_id'] ); 

		// #do photo
		// $row					= IPSMember::buildProfilePhoto( $row );			
		
		
		#gotta figure out that parseStripping stuff one day
		$member['row_style']		= ($displayed %2) ? 'row2' : 'row1';
		$member['formatted_pts']	= $this->registry->getClass('class_localization')->formatNumber($member['eco_points'], $this->registry->ecoclass->decimal);
		$member['formatted_worth']	= $this->registry->getClass('class_localization')->formatNumber($member['eco_worth'], $this->registry->ecoclass->decimal);
		
		$member_row = $this->registry->output->getTemplate('ibEconomy')->standings_row($member);	
		
		#return
		$returnThis['showPage'] = $this->registry->output->getTemplate('ibEconomy')->member($member_row, $member);
		$returnThis['memName'] 	= $member['members_display_name'];
		$returnThis['memID'] 	= $member['member_id'];
		
		return $returnThis;
	}
	
	/**
	 * Show ALL ibEco Assets
	 */
	public function portfolio()
	{
		#init
		$assetRows = '';
		
		#member?
		if ( $this->request['mem_name'] && !$this->request['mname'] )
		{
			#load member ID
			$member = IPSMember::load( $this->request['mem_name'], 'pfields_content', 'displayname' );
			$this->request['member_id'] = $member['member_id'];
			$this->request['mname']		= $member['members_display_name'];
		}		
		
		#count em
		$numPortItemTotal = $this->registry->mysql_ibEconomy->countPortItems( 'all', $ids='', $this->request['p_type'], $this->request['member_id'] );

		#none to show?
		if ( ! $numPortItemTotal )
		{
			$error = str_replace( "<%TYPE%>", $this->lang->words['assets'], $this->lang->words['none_to_show'] );
			$this->showPage = $this->registry->output->getTemplate('ibEconomy')->error($error);

			return $this->showPage;		
		}
		
		#query em
		$o = $this->registry->mysql_ibEconomy->grabPortfolioItems( $this->request['member_id'], 'all', $type='pub', $ids='', $this->request['p_type'], $this->request['st'], $this->request['sort'], $this->request['sw'] );

		#make loop through assets
		while ( $row = $this->DB->fetch($o) )
		{
			#format for rows
			$row = $this->registry->ecoclass->formatPortRow( $row );
			$row['formatted_name'] 	= IPSMember::makeNameFormatted( $row['members_display_name'], $row['member_group_id'] ); 
			
			$assetRows .= $this->registry->output->getTemplate('ibEconomy')->asset_row( $row );
		}
		
		#other switch
		$otherSW = ( $this->request['sw'] != 'ASC' ) ? 'ASC' : 'DESC';		

		#assetTypesDD
		$assetsDD = "<select name='p_type' class='input_select'><optgroup label='{$this->lang->words['display']}...'>";
					
		if (is_array($this->registry->ecoclass->cartTypes) && count($this->registry->ecoclass->cartTypes))
		{
			foreach($this->registry->ecoclass->cartTypes AS $cartType)
			{
				if ($cartType['savedInPortfolio'])
				{
					$selected = ($this->request['p_type'] == $cartType['key']) ? "selected='SELECTED'" : "";
					$assetsDD .= "<option value='{$cartType['key']}' {$selected}>".$this->lang->words[ $cartType['key'].'s' ]."</option>";
				}
			}
		}

		$selected = (!$this->request['p_type']) ? "selected='SELECTED'" : "";
		$assetsDD .= "<option value='' {$selected}>{$this->lang->words['all']}</option></select>";
				
		#page links
		$pages		= $this->registry->output->generatePagination( array(
					'totalItems'        => $numPortItemTotal,
					'itemsPerPage'  	=> intval($this->settings['eco_general_pp']),
					'currentStartValue'	=> intval($this->request['st']),
					'baseUrl'           => "app=ibEconomy&amp;tab=global&amp;area=portfolio&amp;member_id={$this->request['member_id']}&amp;mname={$this->request['mname']}&amp;p_type={$this->request['p_type']}&amp;sort={$this->request['sort']}&amp;sw={$this->request['sw']}",
		)      );		
		
		#throw rows to wrapper
		$this->showPage = $this->registry->output->getTemplate('ibEconomy')->asset_wrapper( $assetRows, $otherSW, $this->request['mname'], $pages, $assetsDD );

		return $this->showPage;		
	}

	/**
	 * Show Settings
	 */
	public function settings()
	{	
		#init
		$settingHtml = array();
		
		#setting sections
		$sections = array('bank','stock','cc','lt','shopitem','shopcat','welfare','loan','transaction','asset','lottery');
		
		#loop through sections
		foreach ( $sections  AS $section )
		{
			#plural (damn welfare...)
			$sectionz = ( $section == 'welfare' ) ? $section : $section.'s';
			
			#page off?, show page off and be done with it
			if ( !$this->settings['eco_'.$sectionz.'_on'] )
			{
				continue;
			}
			else
			{
				if ( ! $this->memberData['g_eco_'.$section ] )
				{
					$settingHtml[ $section ]['image'] = 'cross.png';
				}
				else
				{
					$settingHtml[ $section ]['image'] = 'accept.png';
				}
			}
		}	
		
		#loop through plugins to see if we have more settings to display
		$pluginGroupSettingsHTML 	= "";
		$pluginsInstalled 			= $this->registry->ecoclass->plugins;
		
		if (is_array($pluginsInstalled) && count($pluginsInstalled))
		{
			foreach($pluginsInstalled AS $tabName => $tabArray)
			{
				foreach($pluginsInstalled[ $tabName ] AS $pluginName => $pluginArray)
				{
					if (!$pluginArray['enabled'])
					{
						continue;
					}
					$pluginPublicSettings = $pluginArray['public_settings'];

					if (is_array($pluginPublicSettings) && count($pluginPublicSettings))
					{
						if (is_array($pluginPublicSettings['rows']) && count($pluginPublicSettings['rows']))
						{
							$rows = "";
							$numRows = 0;
							
							foreach ($pluginPublicSettings['rows'] AS $pubWords => $pubValue)
							{
								$pubSetting = array();
								$pubSetting['words'] = $pubWords;
								$pubSetting['value'] = $pubValue;
								$pubSetting['style'] = ($numRows % 2 == 0) ? 'row1' : 'row2';
								
								$rows .= $this->registry->output->getTemplate('ibEconomy')->setting_row( $pubSetting );
								$numRows++;
							}
						}
						$pluginPublicSettings['title'] 		= $pluginArray['name'];
						$pluginPublicSettings['image_link'] = $this->settings['img_url']."/eco_images/".$pluginPublicSettings['image'];

						$pluginGroupSettingsHTML .= $this->registry->output->getTemplate('ibEconomy')->setting_group( $pluginPublicSettings, $rows );
					}
				}
			}
		}
		
		#output all
		$this->showPage = $this->registry->output->getTemplate('ibEconomy')->settings( $settingHtml, $pluginGroupSettingsHTML );

		return $this->showPage;		
	}		
	
	/**
	 * Show ALL ibEco Transactions
	 */
	public function transactions()
	{			
        #setup parser
		IPSText::getTextClass('bbcode')->parse_smilies	 = $this->settings['eco_general_dons_emo'];
 		IPSText::getTextClass('bbcode')->parse_nl2br   	 = 1;
 		IPSText::getTextClass('bbcode')->parse_html    	 = 0;
 		IPSText::getTextClass('bbcode')->parse_bbcode    = $this->settings['eco_general_dons_bbc'];
 		IPSText::getTextClass('bbcode')->parsing_section = 'global';
		
		#member?
		if ( $this->request['mem_name'] && !$this->request['mname'] )
		{
			#load member ID
			$member = IPSMember::load( $this->request['mem_name'], 'pfields_content', 'displayname' );
			$this->request['member_id'] = $member['member_id'];
			$this->request['mname']		= $member['members_display_name'];
		}
		
		#only show my transactions? (new to 2.0)
		if ( $this->settings['eco_general_only_my_trans'] && !$this->memberData['g_eco_edit_pts'] )
		{
			$this->request['member_id'] = $this->memberData['member_id'];
			$this->request['mname']		= $this->memberData['members_display_name'];
		}

		#count logs
		$totalLogs = $this->registry->mysql_ibEconomy->countLogs( $this->request['action'], $this->request['member_id'] );

		#no logs?
		if ( ! $totalLogs )
		{
			$this->request['action'] = ( $this->request['action'] == 'all' || ! $this->request['action'] ) ? 'transactions' : $this->request['action'];
			$error = str_replace( "<%TYPE%>", $this->lang->words[ $this->request['action'].'s' ], $this->lang->words['none_to_show'] );
			$this->showPage = $this->registry->output->getTemplate('ibEconomy')->error($error);

			return $this->showPage;		
		}
		
		#query log table
		$this->registry->mysql_ibEconomy->getLogs( $this->request['action'], $this->request['member_id'], $this->request['sort'], $this->request['sw'], $this->request['st'] );
		
		if ( $this->DB->getTotalRows() )
		{
			while ( $row = $this->DB->fetch() )
			{
				#format stuff
				$row = $this->registry->ecoclass->formatLogRow( $row );				
								
				#format rest by action type
				if ( $row['l_action'] == 'donation' )
				{
					$row['l_log'] 		= IPSText::getTextClass('bbcode')->preDisplayParse( $row['l_log'] );
				}
				
				$logs .= $this->registry->output->getTemplate('ibEconomy')->transactions_row( $row );
			}
		}
		
		#other switch
		$otherSW = ( $this->request['sw'] != 'ASC' ) ? 'ASC' : 'DESC';			
		
		#page links
		$pages		= $this->registry->output->generatePagination( array(
					'totalItems'        => $totalLogs,
					'itemsPerPage'  	=> intval($this->settings['eco_general_pp']),
					'currentStartValue'	=> intval($this->request['st']),
					'baseUrl'           => "app=ibEconomy&amp;tab=global&amp;area=transactions&amp;member_id={$this->request['member_id']}&amp;mname={$this->request['mname']}&amp;action={$this->request['action']}&amp;sort={$this->request['sort']}&amp;sw={$this->request['sw']}",
		)      );		
		
		#throw rows to wrapper
		$this->showPage = $this->registry->output->getTemplate('ibEconomy')->transactions_wrapper( $logs, $otherSW, $pages, $this->request['mname'] );

		return $this->showPage;		
	}	
	
	/**
	 * Show overview of system stats (items)
	 */
	public function overview()
	{
		#init
		$items 		= "";
		$item  		= array();
		$portfolios = array();		
		$itemTypes 	= $this->registry->ecoclass->makeItemList();
		$words 		= $this->registry->ecoclass->makeOverviewMap();
		
		#do points
		$this->registry->mysql_ibEconomy->queryMembers( 'all', $ids='', 0, $noid, $noid, 'overview' );

		if ( $this->DB->getTotalRows() )
		{
			while ( $row = $this->DB->fetch() )
			{
				$item['value1'] = $item['value1'] + $row['eco_points'];
				$item['value2'] = $item['value2'] + $row['eco_worth'];
			}
		}		
		
		#make item vars easy on the eyes and fill the rows
		if ( $item['value1'] or $item['value2'] )
		{
			#for our stats footer...
			$stats = $this->caches['ibEco_stats'];
			$stats['total_points'] 	= $item['value1'];
			$stats['total_worth'] 	= $item['value2'];
			
			$this->cache->setCache( 'ibEco_stats', $stats, array( 'array' => 1, 'deletefirst' => 0 ) );
			
			#format em
			$item['value1'] = $this->settings['eco_general_cursymb'] . $this->registry->getClass('class_localization')->formatNumber( $item['value1'], $this->registry->ecoclass->decimal );
			$item['value2'] = $this->settings['eco_general_cursymb'] . $this->registry->getClass('class_localization')->formatNumber( $item['value2'], $this->registry->ecoclass->decimal );
			
			#really dumbing this down
			$item['value2'] = ($this->settings['eco_worth_on']) ? $item['value2'] : $this->lang->words['n_a'];
			$items .= $this->registry->output->getTemplate('ibEconomy')->overviewRow($item, $words['points']);
		}
		
		#do all other overview items
		foreach ( $itemTypes AS $iType )
		{
			#init
			$item['value1'] = 0;
			$item['value2'] = 0;
			$loans			= array();
			
			#permission and enabled?
			$continue = $this->registry->ecoclass->canAccess($iType.'s', true);

			if ( !$continue )
			{
				continue;
			}
			
			#portfolio cache enabled?
			if ( ! $this->settings['eco_general_cache_portfolio'] )
			{
				$portfolios[ $iType ] = $this->registry->mysql_ibEconomy->grabPortfolioItemsByType( $iType );
			}
			else
			{
				$portfolios[ $iType ] = $this->caches['ibEco_portfolios'][ $iType.'s' ];
			}
			
			if ( $iType == 'bank' )
			{
				#tally up banks
				if ( is_array($portfolios[ $iType ]) && count($portfolios[ $iType ]) )
				{
					foreach ( $portfolios[ $iType ] AS $bank )
					{
						if ( $bank['p_type_class'] == 'savings' )
						{	
							$item['value1'] = $item['value1'] + $bank['p_amount'];
						}
						else if ( $bank['p_type_class'] == 'checking' )
						{	
							$item['value2'] = $item['value2'] + $bank['p_amount'];
						}
						else
						{
							continue;
						}						
					}
				}
				
				#show row if we bank
				if ( $item['value1'] or $item['value2'] )
				{
					$item['value1'] = $this->settings['eco_general_cursymb'] . $this->registry->getClass('class_localization')->formatNumber( $item['value1'], $this->registry->ecoclass->decimal );
					$item['value2'] = $this->settings['eco_general_cursymb'] . $this->registry->getClass('class_localization')->formatNumber( $item['value2'], $this->registry->ecoclass->decimal );

					$items .= $this->registry->output->getTemplate('ibEconomy')->overviewRow($item, $words['banks_']);
				}				
			}
			else
			{					
				#everything but those tricky banks
				if ( is_array($portfolios[ $iType ]) && count($portfolios[ $iType ]) )
				{ 		
					foreach ( $portfolios[ $iType ] AS $row )
					{	
						if ( $iType == 'stock' )
						{
							$item['value1']	= $item['value1'] + $row['p_amount'];
							$item['value2'] = $item['value2'] + $row['p_amount'] * $this->caches['ibEco_'.$iType.'s' ][ $row['p_type_id'] ]['s_value'];
						}
						else if ( $iType == 'cc' )
						{
							$item['value1']	= $item['value1'] + $row['p_amount'];
							$item['value2'] = $item['value2'] + $row['p_max'];
						}
						else if ( $iType == 'lt' )
						{
							$item['value1']++;
							$item['value2'] = $item['value2'] + $row['p_amount'];
						}
						else if ( $iType == 'shopitem' )
						{
							$item['value1'] = $item['value1'] + $row['p_amount'];
							$item['value2'] = $item['value2'] + $row['p_amount'] * $this->caches['ibEco_'.$iType.'s' ][ $row['p_type_id'] ]['si_cost'];
						}
						else if ( $iType == 'loan' )
						{
							$item['value1']++;
							$item['value2'] = $item['value2'] + $row['p_amount'];
						}
					}

					#last bit of formatting
					if ( $iType == 'cc' )
					{
						$item['value1'] = $this->settings['eco_general_cursymb'] . $this->registry->getClass('class_localization')->formatNumber( $item['value1'], $this->registry->ecoclass->decimal );
					}
					else
					{
						$item['value1'] = $this->registry->getClass('class_localization')->formatNumber( $item['value1'] );
					}
						
					$item['value2'] = $this->settings['eco_general_cursymb'] . $this->registry->getClass('class_localization')->formatNumber( $item['value2'], $this->registry->ecoclass->decimal );
						
					$items .= $this->registry->output->getTemplate('ibEconomy')->overviewRow($item, $words[ $iType.'s_']);
				}
			}			
		}
		
		#now those loans...
		if ( is_array($portfolios['bank']) && count($portfolios['bank']) )
		{ 	
			#init
			$item['value1'] = 0;
			$item['value2'] = 0;	
			
			foreach ( $portfolios['bank'] AS $row )
			{
				if ( $row['p_type_class'] == 'loan' )
				{
					$item['value1']++;
					$item['value2'] = $item['value2'] + $row['p_amount'];
				}
			}

			if ($item['value1'] > 0)
			{
				#last bit of formatting
				$item['value1'] = $this->registry->getClass('class_localization')->formatNumber( $item['value1'] );
					
				$item['value2'] = $this->settings['eco_general_cursymb'] . $this->registry->getClass('class_localization')->formatNumber( $item['value2'], $this->registry->ecoclass->decimal );
					
				$items .= $this->registry->output->getTemplate('ibEconomy')->overviewRow($item, $words[ 'loans_']);
			}
		}		
		
		#throw rows to wrapper
		$this->showPage = $this->registry->output->getTemplate('ibEconomy')->overviewWrapper($items);
		
		return $this->showPage;
	}

	/**
	 * Full member breakdown
	 */
	public function memberStandings()
	{
		$this->registry->ecoclass->canAccess('rankings', false);
		
		#init
		$member_rows 	= "";
		$displayed		= 0;
		
		#count members with points
		$totalMembers = $this->registry->mysql_ibEconomy->countMembers();

		#query for all members with points to rank em		
		$rankings = $this->registry->mysql_ibEconomy->rankMembers( ($this->settings['eco_worth_on']) ? 'eco_worth' : 'points', $noid );		
		
		#query for all members with points
		$this->registry->mysql_ibEconomy->queryMembers( 'all', $ids='', $this->request['st'], $this->request['sort'], $this->request['sw'], 'no' );
		
		#no points-yielding members?
		if ( ! $this->DB->getTotalRows() )
		{
			$error = str_replace( "<%TYPE%>", $this->lang->words['members'], $this->lang->words['none_to_show'] );
			$this->showPage = $this->registry->output->getTemplate('ibEconomy')->error($error);

			return $this->showPage;		
		}
		
		#format some lang action
		$this->lang->words['view_eco_profile'] = str_replace( "<%IBECO_NAME%>", $this->settings['eco_general_name'], $this->lang->words['view_eco_profile'] );
		
		#make item vars easy on the eyes and fill the rows
		while ( $row = $this->DB->fetch() )
		{
			#format name
			$row['formatted_name'] 	= IPSMember::makeNameFormatted( $row['members_display_name'], $row['member_group_id'] ); 

			#do photo
			$row					= IPSMember::buildProfilePhoto( $row );			
			
			#rank
			$row['eco_worth_rank'] 	= ( $rankings[ $row['member_id'] ] ) ? $this->lang->words['globally_ranked']." #<span style='font-weight:bolder'>".$this->registry->getClass('class_localization')->formatNumber( $rankings[ $row['member_id'] ] )."</span>" : $this->lang->words['unranked'];
			
			#gotta figure out that parseStripping stuff one day
			$row['row_style']		= ($displayed %2) ? 'row2' : 'row1';
			$row['formatted_pts']	= $this->registry->getClass('class_localization')->formatNumber($row['eco_points'], $this->registry->ecoclass->decimal);
			$row['formatted_worth']	= $this->registry->getClass('class_localization')->formatNumber($row['eco_worth'], $this->registry->ecoclass->decimal);
			
			$member_rows .= $this->registry->output->getTemplate('ibEconomy')->standings_row($row);
			
			$displayed++;
		}
		
		#page links
		$pages		= $this->registry->output->generatePagination( array(
					'totalItems'        => $totalMembers,
					'itemsPerPage'  	=> intval($this->settings['eco_general_pp']),
					'currentStartValue'	=> intval($this->request['st']),
					'baseUrl'           => "app=ibEconomy&amp;tab=global&amp;area=rankings&amp;sort={$this->request['sort']}&amp;sw={$this->request['sw']}",
		)      );		

		#throw member rows to wrapper
		$standingsPage 	= $this->registry->output->getTemplate('ibEconomy')->standings_wrapper($member_rows, $pages, $this->registry->ecoclass->active);
		$this->showPage = $standingsPage;
		
		return $this->showPage;				
	}
	
	/**
	 * Our ibEconomyStats!
	 */
	public function doStats()
	{
		#not wanted?
		if ( ! $this->settings['eco_general_show_stats'] )
		{
			return false;
		}
		
		#init
		$stats = array();

		#get active users
		$stats['active'] = $this->getActiveUserDetails();
		
		#get totals
		$stats['totals'] = $this->getTotalTextString();		
		
		#return it
		return $stats;
	}
	
	/**
	 * Returns an array of active users
	 */
	public function getActiveUserDetails()
	{
		#init
		$active = array( 'TOTAL'   => 0 ,
						 'NAMES'   => array(),
						 'GUESTS'  => 0 ,
						 'MEMBERS' => 0 ,
						 'ANON'    => 0 ,
					   );
		
		#we good?
		if ( $this->settings['eco_general_show_active'] )
		{
			if( !$this->settings['eco_general_au_cutoff'] )
			{
				$this->settings['eco_general_au_cutoff'] = 15;
			}
			
			#grab users from DB
			$cut_off = $this->settings['eco_general_au_cutoff'] * 60;
			$time    = time() - $cut_off;
			$rows    = array();
			$ar_time = time();
			
			if ( $this->memberData['member_id'] )
			{
				$rows = array( $ar_time.'.'.md5( microtime() ) => array( 
																		'id'           => 0,
																		'login_type'   => substr( $this->memberData['login_anonymous'], 0, 1),
																		'running_time' => $ar_time,
																		'seo_name'     => $this->memberData['members_seo_name'],
																		'member_id'    => $this->memberData['member_id'],
																		'member_name'  => $this->memberData['members_display_name'],
																		'member_group' => $this->memberData['member_group_id'],
																		'ibEco_plugin_ppns_prefix' => $this->memberData['ibEco_plugin_ppns_prefix'],
																		'ibEco_plugin_ppns_suffix' => $this->memberData['ibEco_plugin_ppns_suffix']

																		) 
							);
			}
			
			// $this->DB->build( array( 
											// 'select' => 'id, member_id, member_name, seo_name, login_type, running_time, member_group, uagent_type',
											// 'from'   => 'sessions',
											// 'where'  => "running_time > $time AND current_appcomponent = 'ibEconomy'",
								// )	);
			$this->DB->build( array( 
									'select' => 's.id, s.member_id, s.member_name, s.seo_name, s.login_type, s.running_time, s.member_group, s.uagent_type',
									'from'   => array( 'sessions' => 's' ),
									'where'  => "running_time > {$time}",
									'add_join'	=> array(
														0 => array( 'select' 	=> 'pp.*',
																		'from'	=> array( 'profile_portal' => 'pp' ),
																		'where'	=> "s.member_id = pp.pp_member_id",
																		'type'	=> 'left',
																  )																	  
														)									
								)	);								
			$this->DB->execute();
			
			#fetch...
			while ( $r = $this->DB->fetch() )
			{
				$rows[ $r['running_time'].'.'.$r['id'] ] = $r;
			}
			
			krsort( $rows );

			#cache all printed members so we don't double print them
			$cached = array();
			
			foreach ( $rows as $result )
			{
				$last_date = $this->registry->getClass('class_localization')->getTime( $result['running_time'] );
				
				#bot?
				if ( isset( $result['uagent_type'] ) && $result['uagent_type'] == 'search' )
				{
					//-----------------------------------------
					// Seen bot of this type yet?
					//-----------------------------------------
					
					if ( ! $cached[ $result['member_name'] ] )
					{
						if ($result['ibEco_plugin_ppns_prefix'] || $result['ibEco_plugin_ppns_suffix'] || ipsRegistry::$settings['eco_plugin_ppns_use_gf'])
						{					
							$active['NAMES'][] = IPSMember::makeNameFormatted( $result['member_name'], $result['member_group'], $result['ibEco_plugin_ppns_prefix'], $result['ibEco_plugin_ppns_suffix'] );
						}
						$cached[ $result['member_name'] ] = 1;
					}
					else
					{
						//-----------------------------------------
						// Yup, count others as guest
						//-----------------------------------------
						
						$active['GUESTS']++;
					}
				}
				
				#Guest?
				else if ( ! $result['member_id'] OR ! $result['member_name'] )
				{
					$active['GUESTS']++;
				}
				
				#Member?
				else
				{
					if ( empty( $cached[ $result['member_id'] ] ) )
					{
						$cached[ $result['member_id'] ] = 1;

						if ($result['ibEco_plugin_ppns_prefix'] || $result['ibEco_plugin_ppns_suffix'] || ipsRegistry::$settings['eco_plugin_ppns_use_gf'])
						{
							$result['member_name'] = IPSMember::makeNameFormatted( $result['member_name'], $result['member_group'], $result['ibEco_plugin_ppns_prefix'], $result['ibEco_plugin_ppns_suffix'] );
						}
						
						if ( ! $this->settings['disable_anonymous'] AND $result['login_type'] )
						{
							if ( $this->memberData['g_access_cp'] and ($this->settings['disable_admin_anon'] != 1) )
							{
								$active['NAMES'][] = "<a href='" . $this->registry->getClass('output')->buildSEOUrl( "showuser={$result['member_id']}", 'public', $result['seo_name'], 'showuser' ) . "' title='$last_date'>{$result['member_name']}</a>*";
								$active['ANON']++;
							}
							else
							{
								$active['ANON']++;
							}
						}
						else
						{
							$active['MEMBERS']++;
							$active['NAMES'][] = "<a href='" . $this->registry->getClass('output')->buildSEOUrl( "showuser={$result['member_id']}", 'public', $result['seo_name'], 'showuser' ) ."' title='$last_date'>{$result['member_name']}</a>";
						}
					}
				}
			}

			#total em up good sir
			$active['TOTAL'] = $active['MEMBERS'] + $active['GUESTS'] + $active['ANON'];
			
			$this->users_online = $active['TOTAL'];
		}
		
		#format text
		$this->lang->words['active_users'] = sprintf( $this->lang->words['active_users'], $this->settings['eco_general_au_cutoff'] );
		
		#return those active peepz
		return $active;
	}

	/**
	 * Returns an array of ibEco Stats
	 */
	public function getTotalTextString()
	{
		#init
		$stats_output = array();
		
		if ( $this->settings['eco_general_show_totals'] )
		{
			if ( ! is_array( $this->caches['ibEco_stats'] ) )
			{
				$this->cache->rebuildCache('ibEco_stats','ibEconomy');
			}
			
			$stats = $this->caches['ibEco_stats'];
			
			#We need to determine if we have the most users ever online if we aren't showing active users in the stats block
			if( !$this->users_online )
			{
				$cut_off = $this->settings['eco_general_au_cutoff'] * 60;
				$time    = time() - $cut_off;
				$total	 = $this->DB->buildAndFetch( array( 'select'	=> 'count(*) as users_online', 'from' => 'sessions', 'where' => "running_time > $time AND current_appcomponent = 'ibEconomy'" ) );

				$this->users_online = $total['users_online'];
			}
			
			#Update the most active count if needed
			if ($this->users_online > $stats['most_count'])
			{
				$stats['most_count'] = $this->users_online;
				$stats['most_date']  = time();
				
				$this->cache->setCache( 'ibEco_stats', $stats, array( 'array' => 1, 'deletefirst' => 1 ) );
			}
			
			#most online
			$stats_output['most_time'] 		= $this->registry->getClass( 'class_localization')->getDate( $stats['most_date'], 'LONG' );
			$stats_output['most_online'] 	= $this->registry->getClass('class_localization')->formatNumber($stats['most_count']);

			#total pts/worth/welfare
			$stats_output['total_points'] 	= $this->registry->getClass('class_localization')->formatNumber($stats['total_points'], $this->registry->ecoclass->decimal);
			$stats_output['total_worth'] 	= $this->registry->getClass('class_localization')->formatNumber($stats['total_worth'], $this->registry->ecoclass->decimal);
			$stats_output['total_welfare'] 	= $this->registry->getClass('class_localization')->formatNumber($stats['total_welfare'], $this->registry->ecoclass->decimal);
			
			#newest and total items
			$stats_output['newest_item']    = ( $stats['last_shop_item'] ) ? $stats['last_shop_item'] : $this->lang->words['n_a'];
			$stats_output['item_count']    	= $this->registry->getClass('class_localization')->formatNumber($stats['item_count']);
		}

		return $stats_output;
	}	
	
	/**
	 * Blocks
	 */
	public function blocks()
	{
		#init
		$blockHtml 	= "";
		$blockCache = $this->caches['ibEco_blocks'];
		$ptsField 	= $this->settings['eco_general_pts_field'];
		
		#lang for index
		if ( $this->request['app'] != 'ibEconomy' )
		{
			$this->registry->class_localization->loadLanguageFile( array( 'public_ibEconomy' ), 'ibEconomy' );		
		}
		
		#sort key map
		$keyMap 	= $this->registry->ecoclass->blockTypeKeyMap();
				  
		#sorting direction map
		$sortDir 	= $this->registry->ecoclass->blockTypeSortMap();
		
		#text map
		$txtMap 	= $this->registry->ecoclass->blockTypeTextMap();
				  
		#setup bbcode parser to use html
		IPSText::getTextClass('bbcode')->parse_html		= 1;
		
		//$this->registry->ecoclass->showVars($blockCache);
		if ( $blockCache )
		{
			foreach ( $blockCache AS $block )
			{
				#init
				$blockRows 	= "";
				$rows		= 0;
				$rowType 	= "";
				$member_rows= "";
				
				#skip it?
				if ( !$block['sb_on'] )
				{
					continue;		
				}			
				if ( $block['sb_use_perms'] && !$this->registry->permissions->check( 'view', $block ) )
				{
					continue;		
				}
				if ( !$block['sb_on_index'] && $this->request['app'] != 'ibEconomy' )
				{
					continue;		
				}
				
				#custom?
				if ( $block['sb_custom'] )
				{
					#format content
					$block['sb_custom_content'] = ( !$block['sb_raw'] ) ? IPSText::getTextClass('bbcode')->preDisplayParse( $block['sb_custom_content'] ) : $block['sb_custom_content'];

					#add block
					$blockHtml .= $this->registry->getClass('output')->getTemplate('ibEconomy')->block( $block['sb_custom_content'], $block );
					
					#rest not needed...
					continue;
				}
				
				#crazy long mapping stuff from... lets shorten it
				$sortDisKey		= ( !in_array( $block['sb_display_order'], array('points_asc','points_desc') ) ) ? $sortDir[ $block['sb_display_order'] ]['key'] : 'points';
				$block['key'] 	= ( $block['sb_display_order'] != 'newest' ) ? $keyMap[ $block['sb_item_type'] ][ $sortDisKey ] : $keyMap[ $block['sb_item_type'] ]['newest'];
				$displayKey 	= ( $block['sb_display_order'] != 'newest' ) ? $keyMap[ $block['sb_item_type'] ][ $sortDisKey ] : $keyMap[ $block['sb_item_type'] ]['points'];
				$block['sort'] 	= $sortDir[ $block['sb_display_order'] ]['sort'];
				$block['text']	= ( $txtMap[ $block['sb_item_type'] ][ $sortDisKey ] != $this->settings['eco_general_currency'] ) ? $this->lang->words[ $txtMap[ $block['sb_item_type'] ][ $sortDisKey ] ] : $this->settings['eco_general_currency'];				

				#do non-member/lottery/plugin based blocks first
				if ( $block['sb_item_type'] != 'member' && $block['sb_item_type'] != 'live_lotto' && strpos($block['sb_item_type'], 'plugin_') === false )
				{
					#skip it if non-item sorter selected for block
					if ( in_array($block['sb_display_order'], array('welfare_desc','welfare_asc' ) ) )
					{
						continue;
					}
					
					#format some vars (stupd loans)
					$XTYPE				= explode('_', $block['sb_item_type']);
					$block['s_type']	= ( $block['sb_item_type'] != 'loan' ) 	? $XTYPE[0] : 'bank';
					$block['type_type']	= ( $block['sb_item_type'] != 'loan' ) 	? $XTYPE[1] : 'loan';
					$block['typeAbr']	= $this->registry->ecoclass->getTypeAbr($block['s_type']);
					$block['type_types']= ( $block['type_type'] != 'loan' ) 	? $block['type_type'] : 'loans';
					$block['permCheck']	= ( $block['type_type'] != 'loan' )   	? 'view' : 'loans';
					
					#get item cache
					if( !$this->caches['ibEco_'.$block['s_type'].'s'] )
					{
						$this->caches['ibEco_'.$block['s_type'].'s'] = $this->cache->getCache('ibEco_'.$block['s_type'].'s');
					}
					$blockTypeCache = $this->caches['ibEco_'.$block['s_type'].'s'];
					
					#no cache to use? skip it!
					if ( !$blockTypeCache )
					{
						continue;
					}					

					#sort needed?
					if ( $block['sb_display_order'] != 'random' )
					{	
						#sort it...
						$blockTypeCache = $this->registry->ecoclass->sortByKeyX($blockTypeCache, $block['key'], $block['sort']);
					}
					else
					{
						#randomize it
						$blockTypeCache = $this->registry->ecoclass->shizuffle($blockTypeCache);
					}

					#loop through the proper amount
					foreach ( $blockTypeCache AS $c )
					{
						if ( $rows < $block['sb_display_num'] )
						{
							if ( ( $c[$block['typeAbr'].'_on'] && ($block['s_type'] != 'bank' || $c['b_'.$block['type_types'].'_on']) ) && ( !$c[ $block['typeAbr'].'_use_perms'] || $this->registry->permissions->check( $block['permCheck'], $c ) ) )
							{
								#format
								$dollarSign		= ( $block['sb_display_order'] != 'popular' ) ? $this->settings['eco_general_cursymb'] : '';
								$c['figure']	= $dollarSign.$this->registry->getClass('class_localization')->formatNumber( $c[ $displayKey ], $this->registry->ecoclass->decimal );
								$c['row_class'] = ($rowType == 'row1' ) ? 'row2 altrow' : 'row1';
								$c 				= $this->registry->ecoclass->formatBlockItemRow($c,$block);
								
								#add block
								$blockRows .= $this->registry->getClass('output')->getTemplate('ibEconomy')->blockRow( $c, $block );
								
								#count em
								$rows++;
								
								#html row class
								$rowType = $c['row_class'];
							}
						}
						else
						{
							break;
						}
					}
					
					#add block if rows to show
					if ( $blockRows )
					{
						$blockHtml .= $this->registry->getClass('output')->getTemplate('ibEconomy')->block( $blockRows, $block );					
					}
				}
				#lottery based
				#added in 1.5
				else if ( $block['sb_item_type'] == 'live_lotto' )
				{
					if($this->settings['eco_lotterys_on'] && $this->memberData['g_eco_lottery' ])
					{
						#need live lotto cache?
						if( !$this->caches['ibEco_live_lotto'] )
						{
							$this->caches['ibEco_live_lotto'] = $this->cache->getCache('ibEco_live_lotto');
						}
						
						#init cached current lotto
						$liveLotto 	= $this->caches['ibEco_live_lotto'];
						$liveLotto = $this->registry->ecoclass->formatRow($liveLotto, 'lottery', false);
						
						if ( $liveLotto['l_id'] > 0 )
						{
							#format some vars (stupid loans)
							$block['s_type']	= 'lottery';
							$block['type_type']	= 'lottery';
							$block['typeAbr']	= $this->registry->ecoclass->getTypeAbr('lottery');
							$block['type_types']= $block['type_type'];
							$block['permCheck']	= 'view';

							if ( $liveLotto['l_id'] > 1 )
							{
								$cartItemType = $this->registry->ecoclass->grabCartTypeClass('lottery');
								$prevLotto	  = $cartItemType->grabItemByID($liveLotto['l_id']-1);
								$prevLotto	  = $cartItemType->format($prevLotto, true);
								
								if ($prevLotto['winner_link'])
								{
									$liveLotto['prev_winner_link'] 	= $prevLotto['winner_link'];
									
									$liveLotto['prevWinnerMemData'] = $prevLotto['winnerMemData'];
									$liveLotto['prevWinnerMemData']	= IPSMember::buildProfilePhoto( $liveLotto['prevWinnerMemData'] );	
									//$this->registry->ecoclass->showVars($liveLotto['prevWinnerMemData']);
									
									
									$liveLotto['prevWinnings'] 		= $prevLotto['winnings'];
								}
							}
							
							$blockRows .= $this->registry->getClass('output')->getTemplate('ibEconomy')->blockRowLotto( $liveLotto, $block );
							
							$blockHtml .= $this->registry->getClass('output')->getTemplate('ibEconomy')->block( $blockRows, $block );
						}
					}
				}
				#plugin block...
				else if ($block['sb_item_type'] != 'member')//(strpos($block['sb_item_type'], 'plugin_') !== false)
				{
					$pluginKey 	= $pluginKey = str_replace('plugin_', '', $block['sb_item_type']);
					
					$plugin 	= $this->registry->ecoclass->grabPluginClass($pluginKey);
					$pluginData = $this->registry->ecoclass->plugins[ $plugin->tab() ][ $pluginKey ];

					#got access and on?
					if ($pluginData['has_block'] && $this->registry->ecoclass->canAccess('plugin', true, $pluginData))
					{
						$blockHtml .= $this->registry->getClass('output')->getTemplate('ibEconomy')->block( $plugin->buildBlock($block), $block );
					}
				}
				else
				#member based block...
				{
					#skip it if non-member sorter selected for block
					if ( ! in_array($block['sb_display_order'], array('points_desc','points_asc','worth_desc','worth_asc','welfare_desc','welfare_asc' ) ) )
					{
						continue;
					}
					
					// #skip it if marquee was selected
					// if ( $block['sb_display_type'] != 'list' )
					// {
						// continue;
					// }					

					#query for all members with points
					$this->registry->mysql_ibEconomy->queryMembers( $block['sb_display_num'], $ids='', 0, $block['key'], $block['sort'], 'no' );
					
					#no points-yielding members?
					if ( ! $this->DB->getTotalRows() )
					{
						continue;	
					}
					
					#make item vars easy on the eyes and fill the rows
					while ( $row = $this->DB->fetch() )
					{
						#format name
						$row['formatted_name'] 	= IPSMember::makeNameFormatted( $row['members_display_name'], $row['member_group_id'] ); 

						#do photo
						$row					= IPSMember::buildProfilePhoto( $row );	
						
						#rank
						if ( in_array($block['key'], array('points','eco_points','money','ibbookie_points','utr_points') ) )
						{
							$toFormat = $row['eco_points'];
						}
						else
						{
							$toFormat = $row[ $block['key'] ];
						}
						
						$row['figure'] 			= $this->settings['eco_general_cursymb'].$this->registry->getClass('class_localization')->formatNumber( $toFormat );
						
						$member_rows .= $this->registry->output->getTemplate('ibEconomy')->blockRowMem($row, $block);
					}
					
					#add block if rows to show
					if ( $member_rows )
					{
						$blockHtml .= $this->registry->getClass('output')->getTemplate('ibEconomy')->blockMem( $member_rows, $block );					
					}
				}
			}
		}	
		
		return $blockHtml;				
	}

	/**
	 * Returns an array of leftside buttons
	 */
	public function getLeftButtons()
	{
		#init
		$buttons = "";		
		
		$buttonTypes = array('bank' 	=> array('name' => 'bank',		'image'		=> 'building_key.png', 'text' => 'open_bank_account', 	'tab' => 'invest', 	'area' => 'banks'),
							 'stocks' 	=> array('name' => 'stock',		'image' 	=> 'chart_curve.png', 	'text' => 'buy_stock', 			'tab' => 'invest', 	'area' => 'stocks'),
							 'ccs' 		=> array('name' => 'cc',		'image' 	=> 'creditcards.png', 	'text' => 'buy_credit_card', 	'tab' => 'invest', 	'area' => 'ccs'),
							 'lts' 		=> array('name' => 'lt',		'image' 	=> 'bar_graph.png', 	'text' => 'invest', 			'tab' => 'invest',  'area' => 'lts'),
							 'loans' 	=> array('name' => 'loan',		'image' 	=> 'money_add.png', 	'text' => 'apply_for_loan', 	'tab' => 'cash', 	'area' => 'loans'),
							 'shopitems'=> array('name' => 'shopitem',	'image' 	=> 'tag_blue.png', 		'text' => 'shop', 				'tab' => 'shop',	'area' => 'items'),
							 'lottery'	=> array('name' => 'lottery',	'image' 	=> 'lotto.png', 		'text' => 'lottery', 			'tab' => 'cash',	'area' => 'lottery')
							);
		
		#loop through possible buttons
		foreach ( $buttonTypes AS $button )
		{
			#want us to show it?
			if ( !in_array( $button['name'], explode(',', $this->settings['eco_left_buttons']) ) )
			{
				continue;
			}			
			#CAN we show it?
			if ( !$this->memberData['g_eco_'.$button['name'] ] || !$this->settings['eco_'.$button['name'].'s_on'] )
			{
				continue;
			}
			
			#lang
			$button['text'] = $this->lang->words[ $button['text'] ];
			
			#add button
			$buttons .= $this->registry->output->getTemplate('ibEconomy')->leftButton($button);		
		}
		
		#return
		return $buttons;
	}	
		
}