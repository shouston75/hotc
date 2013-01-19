<?php

/**
 * (e32) ibEconomy
 * Skin File
 * @ ACP Edit Extension: Groups
 */
 
class cp_skin_ibEconomy_group_form extends output
{

/**
 * Prevent our main destructor being called by this class
 */
public function __destruct()
{
}

/**
 * Show forums group form
 */
public function acp_group_form_main( $group, $tabId ) {

#master ibEconomy Class
if ( ! $this->registry->isClassLoaded( 'ecoclass' ) )
{
	require_once( IPSLib::getAppDir( 'ibEconomy' ) . "/sources/ecoclass.php" );
	$this->registry->setClass( 'ecoclass', new class_ibEconomy( $this->registry ) );
}

#ACP ibEconomy Class
if ( ! $this->registry->isClassLoaded( 'class_ibEco_CP' ) )
{
	require_once( IPSLib::getAppDir( 'ibEconomy' ) . "/sources/classes/class_ibEco_CP.php" );
	$this->registry->setClass( 'class_ibEco_CP', new class_ibEco_CP( $this->registry ) );
}

#lang bit format	
$this->lang->words['max_group_points'] = str_replace('<%POINTS_NAME%>', $this->settings['eco_general_currency'], $this->lang->words['max_group_points']);

#any plugins with group settings we need to add?
$pluginGroupSettingsHTML = $this->registry->class_ibEco_CP->buildPluginGroupSettingsHTML($group);

#Lottery Odds Dropdown
$levels = array();

for ($i=1; $i<=9; $i++)
{
	$levels[] = array($i, $i - 5);
}

$form							= array();

#Lottery Tab:
$form['g_eco_lottery']			= $this->registry->output->formYesNo( "g_eco_lottery", $group['g_eco_lottery'] );
$form['g_eco_lottery_tix']		= $this->registry->output->formInput( "g_eco_lottery_tix", $group['g_eco_lottery_tix'] );
$form['g_eco_lottery_odds']		= $this->registry->output->formDropdown( "g_eco_lottery_odds", $levels, $group['g_eco_lottery_odds'] != 0  ? $group['g_eco_lottery_odds'] : 5);

#General:
$form['g_eco']					= $this->registry->output->formYesNo( "g_eco", $group['g_eco'] );
$form['g_eco_max_pts']			= $this->registry->output->formInput( "g_eco_max_pts", $group['g_eco_max_pts'] );
$form['g_eco_frm_ptsx']			= $this->registry->output->formInput( "g_eco_frm_ptsx", $group['g_eco_frm_ptsx'] );
$form['g_eco_transaction']		= $this->registry->output->formYesNo( "g_eco_transaction", $group['g_eco_transaction'] );
$form['g_eco_shopitem']			= $this->registry->output->formYesNo( "g_eco_shopitem", $group['g_eco_shopitem'] );
$form['g_eco_asset']			= $this->registry->output->formYesNo( "g_eco_asset", $group['g_eco_asset'] );

#Bank:
$form['g_eco_bank']				= $this->registry->output->formYesNo( "g_eco_bank", $group['g_eco_bank'] );
$form['g_eco_bank_max']			= $this->registry->output->formInput( "g_eco_bank_max", $group['g_eco_bank_max'] );
$form['g_eco_stock']			= $this->registry->output->formYesNo( "g_eco_stock", $group['g_eco_stock'] );
$form['g_eco_stock_max']		= $this->registry->output->formInput( "g_eco_stock_max", $group['g_eco_stock_max'] );
$form['g_eco_lt']				= $this->registry->output->formYesNo( "g_eco_lt", $group['g_eco_lt'] );
$form['g_eco_lt_max']			= $this->registry->output->formInput( "g_eco_lt_max", $group['g_eco_lt_max'] );

#Loan:
$form['g_eco_loan']			= $this->registry->output->formYesNo( "g_eco_loan", $group['g_eco_loan'] );
$form['g_eco_cc']				= $this->registry->output->formYesNo( "g_eco_cc", $group['g_eco_cc'] );
$form['g_eco_max_cc_debt']		= $this->registry->output->formInput( "g_eco_max_cc_debt", $group['g_eco_max_cc_debt'] );
$form['g_eco_max_loan_debt']	= $this->registry->output->formInput( "g_eco_max_loan_debt", $group['g_eco_max_loan_debt'] );
$form['g_eco_cash_adv_max']		= $this->registry->output->formInput( "g_eco_cash_adv_max", $group['g_eco_cash_adv_max'] );
$form['g_eco_bal_trnsfr_max']	= $this->registry->output->formInput( "g_eco_bal_trnsfr_max", $group['g_eco_bal_trnsfr_max'] );

#Welfare:
$form['g_eco_welfare']			= $this->registry->output->formYesNo( "g_eco_welfare", $group['g_eco_welfare'] );
$form['g_eco_welfare_max']		= $this->registry->output->formInput( "g_eco_welfare_max", $group['g_eco_welfare_max'] );

#Admin Tab
$form['g_eco_edit_pts']			= $this->registry->output->formYesNo( "g_eco_edit_pts", $group['g_eco_edit_pts'] );

$IPBHTML = "";

$IPBHTML .= <<<EOF
<div id='tab_GROUPS_{$tabId}_content'>
	<div>
		<table class='ipsTable'>	
			<tr>
				<th colspan='2'>{$this->lang->words['general_perms_title']}</th>
			</tr>
			<tr>
				<td class='field_title'>
					<strong class='title'>{$this->lang->words['can_access_eco']}?</strong>
				</td>
				<td class='field_field'>
					{$form['g_eco']}
				</td>
			</tr>
			<tr>
				<td class='field_title'>
					<strong class='title'>{$this->lang->words['can_view_transactions']}</strong>
				</td>
				<td class='field_field'>
					{$form['g_eco_transaction']}
				</td>
			</tr>
			<tr>
				<td class='field_title'>
					<strong class='title'>{$this->lang->words['can_shop']}</strong>
				</td>
				<td class='field_field'>
					{$form['g_eco_shopitem']}
				</td>
			</tr>
			<tr>
				<td class='field_title'>
					<strong class='title'>{$this->lang->words['can_view_all_inventory']}</strong>
				</td>
				<td class='field_field'>
					{$form['g_eco_asset']}
				</td>
			</tr>			
			<tr>
				<td class='field_title'>
					<strong class='title'>{$this->lang->words['max_group_points']}</strong>
				</td>
				<td class='field_field'>
					{$form['g_eco_max_pts']}
					<div class='desctext'>{$this->lang->words['leave_blank_for_no_limit']}</div>
				</td>
			</tr>
			<tr>
				<td class='field_title'>
					<strong class='title'>{$this->lang->words['forum_point_multplier']}</strong>
				</td>
				<td class='field_field'>
					{$form['g_eco_frm_ptsx']}
					<div class='desctext'>{$this->lang->words['forum_point_multplier_exp']}</div>
				</td>
			</tr>
			<tr>
				<th colspan='2'>{$this->lang->words['bank_perms_title']}</th>
			</tr>
			<tr>
				<td class='field_title'>
					<strong class='title'>{$this->lang->words['can_open_bank']}?</strong>
				</td>
				<td class='field_field'>
					{$form['g_eco_bank']}
				</td>
			</tr>
			<tr>
				<td class='field_title'>
					<strong class='title'>{$this->lang->words['max_per_bank_pts']}</strong>
				</td>
				<td class='field_field'>
					{$form['g_eco_bank_max']}
					<div class='desctext'>{$this->lang->words['leave_blank_for_no_limit']}</div>
				</td>
			</tr>			
			<tr>
				<td class='field_title'>
					<strong class='title'>{$this->lang->words['can_buy_stocks']}?</strong>
				</td>
				<td class='field_field'>
					{$form['g_eco_stock']}
				</td>
			</tr>
			<tr>
				<td class='field_title'>
					<strong class='title'>{$this->lang->words['max_per_stock_shares']}</strong>
				</td>
				<td class='field_field'>
					{$form['g_eco_stock_max']}
					<div class='desctext'>{$this->lang->words['leave_blank_for_no_limit']}</div>
				</td>
			</tr>			
			<tr>
				<td class='field_title'>
					<strong class='title'>{$this->lang->words['can_invest_lt']}?</strong>
				</td>
				<td class='field_field'>
					{$form['g_eco_lt']}
				</td>
			</tr>
			<tr>
				<td class='field_title'>
					<strong class='title'>{$this->lang->words['max_per_lt_points']}</strong>
				</td>
				<td class='field_field'>
					{$form['g_eco_lt_max']}
					<div class='desctext'>{$this->lang->words['leave_blank_for_no_limit']}</div>
				</td>
			</tr>
			<tr>
				<th colspan='2'>{$this->lang->words['credit_perms_title']}</th>
			</tr>
			<tr>
				<td class='field_title'>
					<strong class='title'>{$this->lang->words['can_get_cc']}?</strong>
				</td>
				<td class='field_field'>
					{$form['g_eco_cc']}
				</td>
			</tr>
			<tr>
				<td class='field_title'>
					<strong class='title'>{$this->lang->words['max_cash_adv']}</strong>
				</td>
				<td class='field_field'>
					{$form['g_eco_cash_adv_max']}
					<div class='desctext'>{$this->lang->words['leave_blank_for_no_limit']}</div>
				</td>
			</tr>
			<tr>
				<td class='field_title'>
					<strong class='title'>{$this->lang->words['max_bal_transfer']}</strong>
				</td>
				<td class='field_field'>
					{$form['g_eco_bal_trnsfr_max']}
					<div class='desctext'>{$this->lang->words['leave_blank_for_no_limit']}</div>
				</td>
			</tr>
			<tr>
				<td class='field_title'>
					<strong class='title'>{$this->lang->words['max_group_cc_debt']}?</strong>
				</td>
				<td class='field_field'>
					{$form['g_eco_max_cc_debt']}
					<div class='desctext'>{$this->lang->words['leave_blank_for_no_limit']}</div>
				</td>
			</tr>			
			<tr>
				<th colspan='2'>{$this->lang->words['loan_perms_title']}</th>
			</tr>			
			<tr>
				<td class='field_title'>
					<strong class='title'>{$this->lang->words['can_apply_loan']}?</strong>
				</td>
				<td class='field_field'>
					{$form['g_eco_loan']}
				</td>
			</tr>
			<tr>
				<td class='field_title'>
					<strong class='title'>{$this->lang->words['max_group_loan_debt']}?</strong>
				</td>
				<td class='field_field'>
					{$form['g_eco_max_loan_debt']}
					<div class='desctext'>{$this->lang->words['leave_blank_for_no_limit']}</div>
				</td>
			</tr>
			<tr>
				<th colspan='2'>{$this->lang->words['welfare_perms_title']}</th>
			</tr>
			<tr>
				<td class='field_title'>
					<strong class='title'>{$this->lang->words['can_apply_welfare']}?</strong>
				</td>
				<td class='field_field'>
					{$form['g_eco_welfare']}
				</td>
			</tr>
			<tr>
				<td class='field_title'>
					<strong class='title'>{$this->lang->words['max_group_welfare']}</strong>
				</td>
				<td class='field_field'>
					{$form['g_eco_welfare_max']}
				</td>
			</tr>
			<tr>
				<th colspan='2'>{$this->lang->words['lottery']}</th>
			</tr>
			<tr>
				<td class='field_title'>
					<strong class='title'>{$this->lang->words['can_purchase_lotto_tix']}?</strong>
				</td>
				<td class='field_field'>
					{$form['g_eco_lottery']}
				</td>
			</tr>
			<tr>
				<td class='field_title'>
					<strong class='title'>{$this->lang->words['max_tickets_to_purchase']}</strong>
				</td>
				<td class='field_field'>
					{$form['g_eco_lottery_tix']}
				</td>
			</tr>
			<tr>
				<td class='field_title'>
					<strong class='title'>{$this->lang->words['increased_decreased_lotto_odds']}</strong>
				</td>
				<td class='field_field'>
					{$form['g_eco_lottery_odds']}
					<div class='desctext'>{$this->lang->words['increased_decreased_lotto_odds_exp']}</div>
				</td>
			</tr>
			{$pluginGroupSettingsHTML}
			<tr>
				<th colspan='2'>{$this->lang->words['administrate']}</th>
			</tr>
			<tr>
				<td class='field_title'>
					<strong class='title'>{$this->lang->words['can_edit']} {$this->settings['eco_general_currency']}?</strong>
				</td>
				<td>
					{$form['g_eco_edit_pts']}
				</td>
			</tr>
		</table>
	</div>
</div>

EOF;

return $IPBHTML;
}

/**
 * Display forum group form tabs
 */
public function acp_group_form_tabs( $group, $tabId ) {

$IPBHTML = "";

$IPBHTML .= <<<EOF
	<li id='tab_GROUPS_{$tabId}' class=''>{$this->settings['eco_general_name']}</li>
EOF;

return $IPBHTML;
}

}