<?php

/**
 * (e32) ibEconomy
 * Skin File
 * @ ACP Edit Extension: Members
 */
 
class cp_skin_ibEconomy_member_form extends output
{

/**
 * Prevent our main destructor being called by this class
 */
public function __destruct()
{
}

/**
 * ibEconomy member form
 */
public function acp_member_form_main( $member  ) {

$form	= array();

$form['total_points']	= $this->registry->output->formInput( "total_points", $member['total_points'] );
$form['eco_worth']		= $this->registry->output->formInput( "eco_worth", $member['eco_worth'] );
$form['eco_on_welfare']	= $this->registry->output->formYesNo( "eco_on_welfare", $member['eco_on_welfare'] );
$form['eco_welfare']	= $this->registry->output->formInput( "eco_welfare", $member['eco_welfare'] );

$IPBHTML = "";

$IPBHTML .= <<<EOF
	
	<div id='tab_MEMBERS_{$tabID}_content'>
		<table class='ipsTable double_pad'>
			<tr>
				<th colspan='2'>{$this->settings['eco_general_currency']}</th>
			</tr>
			<tr>
				<td class='field_title'><strong class='title'>{$this->lang->words['total_pts']}</strong></td></td>
				<td class='field_field'>{$form['total_points']}</td>
			</tr>
			<tr>
				<th colspan='2'>{$this->lang->words['worth']}</th>
			</tr>
			<tr>
				<td class='field_title'><strong class='title'>{$this->lang->words['total_worth']}</strong></td></td>
				<td class='field_field'>{$form['eco_worth']}</td>
			</tr>
			<tr>
				<th colspan='2'>{$this->lang->words['on_welfare']}</th>
			</tr>
			<tr>
				<td class='field_title'><strong class='title'>{$this->lang->words['cur_on_welfare']}</strong></td></td>
				<td class='field_field'>{$form['eco_on_welfare']}</td>
			</tr>
			<tr>
				<th colspan='2'>{$this->lang->words['total_welfare']}</th>
			</tr>			
			<tr>
				<td class='field_title'><strong class='title'>{$this->lang->words['total_welfare_rec']}</strong></td></td>
				<td class='field_field'>{$form['eco_welfare']}</td>
			</tr>
		</table>
	</div>

EOF;

return $IPBHTML;
}

/**
 * ibEconomy member tab
 */
public function acp_member_form_tabs( $member ) {

$IPBHTML = "";

$IPBHTML .= <<<EOF
	<li id='tab_MEMBERS_{$tabID}' class=''>{$this->settings['eco_general_name']}</li>
EOF;

return $IPBHTML;
}

}