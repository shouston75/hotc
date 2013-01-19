<?php

/**
 * (e32) ibEconomy
 * Skin File
 * @ ACP... everything ibEconomy
 */
 
class cp_skin_ibEconomy extends output
{

/**
 * Prevent our main destructor being called by this class
 */
public function __destruct()
{
}

//*************************//
//($%^ Sidebar Blocks ^%$)//
//*************************//

/**
 * Sidebar Block form
 */
public function blockForm( $block, $perm_matrix, $buttons ) {

$form	= array();

$title 	= ( $block ) ? $this->lang->words['editing_block']." : ".$block['sb_title'] : $this->lang->words['adding_block'];
$button = ( $block ) ? $this->lang->words['edit_block_button'] : $this->lang->words['add_block_button_title'];

#init
$blockTypes 	= array();
$blockDisTypes 	= array();

#block item type DD
$bTypes = array('bank_savings','bank_checking','stock','cc','lt','shopitem','loan','member', 'live_lotto');

foreach ( $bTypes AS $type )
{
	$blockTypes[] = array( $type, $this->lang->words[ $type ] );
}

#need plugin blocks too?
$plugins = $this->registry->ecoclass->pluginNamesWithBlocks;

if (is_array($plugins) && count($plugins))
{
	foreach ( $plugins AS $pluginKey => $pluginName )
	{
		$blockTypes[] = array( 'plugin_'.$pluginKey, $this->lang->words['Plugin']." ".$pluginName );
	}	
}

#block style DD
$dTypes = array('list','marquee');

foreach ( $dTypes AS $type )
{
	$blockDisTypes[] = array( $type, $this->lang->words[ $type ] );
}

#block order DD
$oTypes = array('random','newest','popular','points_desc','points_asc','worth_desc','worth_asc','welfare_desc','welfare_asc', 'n_a');

foreach ( $oTypes AS $type )
{
	$blockOrdTypes[] = array( $type, $this->lang->words[ $type ] );
}

#custom content
$customContent = $block['sb_custom_content'];

IPSText::getTextClass('bbcode')->parse_html			= 0;
IPSText::getTextClass('bbcode')->parse_nl2br		= 0;
IPSText::getTextClass('bbcode')->parse_smilies  	= 1;
IPSText::getTextClass('bbcode')->parse_bbcode		= 1;
IPSText::getTextClass('bbcode')->parsing_section	= 'global';

if ( $block['sb_raw'] == 1 or !isset($block['sb_raw']) )
{
	$this->member->setProperty( 'members_editor_choice', 'std' );
}
else
{
	if ( IPSText::getTextClass('editor')->method == 'rte' )
	{
		$customContent = IPSText::getTextClass('bbcode')->convertForRTE( $customContent );
	}
	else
	{
		$customContent = IPSText::getTextClass('bbcode')->preEditParse( $customContent );
	}
}

// General Tab:
$form['sb_title']			= $this->registry->output->formInput( "sb_title", $block['sb_title'] );
$form['sb_item_type'] 		= $this->registry->output->formDropdown( "sb_item_type", $blockTypes, $block['sb_item_type'] );
//$form['sb_display_type'] 	= $this->registry->output->formDropdown( "sb_display_type", $blockDisTypes, $block['sb_display_type'] );
$form['sb_display_num']		= $this->registry->output->formSimpleInput( "sb_display_num", $block['sb_display_num'] );
$form['sb_display_order'] 	= $this->registry->output->formDropdown( "sb_display_order", $blockOrdTypes, $block['sb_display_order'] );
$form['sb_pic']				= $this->registry->output->formInput( "sb_pic", $block['sb_pic'] );
$form['sb_font_color']		= $this->registry->output->formInput( "sb_font_color", $block['sb_font_color'] );
$form['sb_bg_color']		= $this->registry->output->formInput( "sb_bg_color", $block['sb_bg_color'] );
$form['sb_boxed']			= $this->registry->output->formYesNo( "sb_boxed", $block['sb_boxed'] );
$form['sb_show_text']		= $this->registry->output->formYesNo( "sb_show_text", $block['sb_show_text'] );
$form['sb_on']				= $this->registry->output->formYesNo( "sb_on", $block['sb_on'] );
$form['sb_on_index']		= $this->registry->output->formYesNo( "sb_on_index", $block['sb_on_index'] );
$form['sb_custom']			= $this->registry->output->formYesNo( "sb_custom", $block['sb_custom'] );
$form['sb_raw']				= $this->registry->output->formYesNo( "sb_raw", $block['sb_raw'] );
$form['sb_custom_content'] 	= IPSText::getTextClass('editor')->showEditor( $customContent, $key="sb_custom_content" );

// Perms Tab
$form['sb_use_perms']		= $this->registry->output->formYesNo( "sb_use_perms", $block['sb_use_perms'] );
	
$IPBHTML = "";
//--starthtml--//

$IPBHTML .= <<<HTML
<div class='section_title'>
	<h2>{$title}</h2>
	{$buttons}	
</div>

<form id='adminform' action='{$this->settings['base_url']}{$this->form_code}&amp;do=do_block' method='post' id='adform' name='adform' onsubmit='return checkform();'>
<input type='hidden' name='sb_id' value='{$block['sb_id']}' />

<div class='acp-box'>
	<h3>{$this->lang->words['block_details']}</h3>
	<div class='ipsTabBar with_left with_right' id='tabstrip_manage_block'>
		<span class='tab_left'>&laquo;</span>
		<span class='tab_right'>&raquo;</span>
		<ul>
			<li id='tab_1'>{$this->lang->words['general_tab_title']}</li>
			<li id='tab_2'>{$this->lang->words['permissions']}</li>
		</ul>
	</div>
	<div class='ipsTabBar_content' id='tabstrip_manage_block_content'>
		<div id='tab_1_content'>
				<table class='ipsTable'>
					<tr>
						<th colspan='2'>{$this->lang->words['gen_settings']}</th>
					</tr>
					<tr>
						<td>
							<label>{$this->lang->words['block_title']}</label>
						</td>
						<td>
							{$form['sb_title']}
						</td>
					</tr>
					<tr>
						<td>
							<label>{$this->lang->words['enabled_title']}?</label>
						</td>
						<td>
							{$form['sb_on']}
						</td>
					</tr>
					<tr>
						<td>
							<label>{$this->lang->words['show_on_index_too']}?</label><br />{$this->lang->words['show_on_index_too_exp']}
						</td>
						<td>
							{$form['sb_on_index']}
						</td>
					</tr>
					<tr>
						<td>
							<label>{$this->lang->words['block_boxed']}?</label><br />{$this->lang->words['block_boxed_exp']}
						</td>
						<td>
							{$form['sb_boxed']}
						</td>
					</tr>
					<tr>
						<td>
							<label>{$this->lang->words['show_descriptive_text']}?</label><br />{$this->lang->words['show_descriptive_text_exp']}
						</td>
						<td>
							{$form['sb_show_text']}
						</td>
					</tr>			
					<tr>
						<td>
							<label>{$this->lang->words['block_img']}</label><br />{$this->lang->words['block_img_exp']}
						</td>
						<td>
							{$form['sb_pic']}
						</td>
					</tr>			
					<tr>
						<th colspan='2'>{$this->lang->words['display_settings']}</th>
					</tr>			
					<tr>
						<td>
							<label>{$this->lang->words['block_type']}</label><br />{$this->lang->words['block_type_exp']}
						</td>
						<td>
							{$form['sb_item_type']}
						</td>
					</tr>
					<tr>
						<td>
							<label>{$this->lang->words['block_num']}</label>
						</td>
						<td>
							{$form['sb_display_num']}
						</td>
					</tr>
					<tr>
						<td>
							<label>{$this->lang->words['block_order']}</label>
						</td>
						<td>
							{$form['sb_display_order']}
						</td>
					</tr>
					<tr>
						<td>
							<label>{$this->lang->words['block_font_color']}</label><br />{$this->lang->words['block_font_color_exp']}
						</td>
						<td>
							{$form['sb_font_color']}
						</td>
					</tr>			
					<tr>
						<td>
							<label>{$this->lang->words['block_bg_color']}</label><br />{$this->lang->words['block_bg_color_exp']}
						</td>
						<td>
							{$form['sb_bg_color']}
						</td>
					</tr>
					<tr>
						<th colspan='2'>{$this->lang->words['custom_mode']}</th>
					</tr>			
					<tr>
						<td>
							<label>{$this->lang->words['customize_block']}?</label><br />{$this->lang->words['customize_block_exp']}
						</td>
						<td>
							{$form['sb_custom']}
						</td>
					</tr>
					<tr>
						<td>
							<label>{$this->lang->words['block_raw']}?</label><br />{$this->lang->words['block_raw_exp']}
						</td>
						<td>
							{$form['sb_raw']}
						</td>
					</tr>
					<tr>
						<td style='width: 40%'>
							<label>{$this->lang->words['custom_content']}</label>
						</td>
						<td style='width: 60%'>
							{$form['sb_custom_content']}
						</td>
					</tr>
				</table>
		</div>
		<div id='tab_2_content'>
			<table class='ipsTable'>
				<tr>
					<td>
						<label>{$this->lang->words['use_perm_matrix']}?</label><br />{$this->lang->words['use_perm_matrix_exp']}
					</td>
					<td>
						{$form['sb_use_perms']}
					</td>
				</tr>
				<tr>
					<td colspan='2'>
						{$perm_matrix}
					</td>
				</tr>				
			</table>
		</div>		
	</div>
	<div class='acp-actionbar'>
		<input type='submit' class='button' value='{$button}' />
	</div>
	</form>
</div>

<script type='text/javascript'>
jQ("#tabstrip_manage_block").ipsTabBar({ tabWrap:
"#tabstrip_manage_block_content" });
</script>
	
HTML;

//--endhtml--//
return $IPBHTML;
}

/**
 * Overview of Sidebar Blocks
 */
public function blocksOverviewWrapper( $content, $buttons ) {

$IPBHTML = "";
//--starthtml--//

$IPBHTML .= <<<HTML

<div class='section_title'>
	<h2>{$this->lang->words['sidebar_blocks']}</h2>
	{$buttons}
</div>

<div class='acp-box'>
 	<h3>{$this->lang->words['blocks']}</h3>
		<table class='ipsTable' id='reorderable_table'>	
			<tr>
				<th class='col_drag'>&nbsp;</th>
				<th>{$this->lang->words['image']}</th>
				<th>{$this->lang->words['title']}</th>
				<th>{$this->lang->words['type']}</th>
				<th>{$this->lang->words['order']}</th>
				<th>{$this->lang->words['boxed']}?</th>
				<th>{$this->lang->words['custom']}?</th>
				<th>{$this->lang->words['enabled_title']}?</th>
				<th>&nbsp;</th>
			</tr>
			{$content}
		</table>
	<div class='acp-actionbar'>
		<form id='adminform' method='post' action='{$this->settings['base_url']}{$this->form_code}&amp;do=block&amp;type=add' onsubmit='return ACPForums.submitModForm()'>
			<input type='submit' class='button' value='{$this->lang->words['add_new_block']}' />
		</form>
	</div>
</div>

<script type='text/javascript'>
	jQ("#reorderable_table").ipsSortable( 'table', {
		url: "{$this->settings['base_url']}&{$this->form_code_js}&do=reorder&md5check={$this->registry->adminFunctions->getSecurityKey()}".replace( /&amp;/g, '&' )
	});
</script>

HTML;

//--endhtml--//
return $IPBHTML;
}

/**
 * Sidebar Block row
 */
public function blockRow( $r="" ) {

$r['sb_on']			= $r['sb_on'] 		? 'accept.png' : 'cross.png';
$r['sb_boxed']		= $r['sb_boxed'] 	? 'accept.png' : 'cross.png';
$r['sb_custom']		= $r['sb_custom'] 	? 'accept.png' : 'cross.png';

#plugin block?
if (strpos($r['sb_item_type'], 'plugin_') !== false)
{
	$pluginKey = str_replace('plugin_', '', $r['sb_item_type']);
	$this->lang->words[ $r['sb_item_type'] ] = $this->lang->words['Plugin']." ".$this->registry->ecoclass->pluginNamesWithBlocks[ $pluginKey ];
}

$IPBHTML = "";
//--starthtml--//

$IPBHTML .= <<<HTML
		<tr class='ipsControlRow isDraggable' id='blocks_{$r['sb_id']}'>
			<td><span class='draghandle'>&nbsp;</span></td>
			<td style='text-align:center'><img src='{$this->settings['img_url']}/eco_images/{$r['sb_pic']}' border='0' /></td>
			<td><span class='larger_text'><a style='text-decoration:none;' href='{$this->settings['base_url']}{$this->form_code}&amp;do=block&amp;type=edit&amp;sb_id={$r['sb_id']}'>{$r['sb_title']}</a></span></td>
			<td>{$this->lang->words[ $r['sb_item_type'] ]}</td>
			<td>{$this->lang->words[ $r['sb_display_order'] ]}</td>
			<td><img src='{$this->settings['skin_acp_url']}/images/icons/{$r['sb_boxed']}' border='0' alt='-' class='ipd' /></td>
			<td><img src='{$this->settings['skin_acp_url']}/images/icons/{$r['sb_custom']}' border='0' alt='-' class='ipd' /></td>
			<td><img src='{$this->settings['skin_acp_url']}/images/icons/{$r['sb_on']}' border='0' alt='-' class='ipd' /></td>	
			<td>
				<ul class='ipsControlStrip'>
					<li class='i_edit' title='{$this->lang->words['edit_block_button']}'><a href='{$this->settings['base_url']}{$this->form_code}&amp;do=block&amp;type=edit&amp;sb_id={$r['sb_id']}'>{$this->lang->words['edit_block_button']}</a></li>
					<li class='i_delete' title='{$this->lang->words['delete_block']}'><a href='{$this->settings['base_url']}{$this->form_code}&amp;do=delete&amp;type=block&amp;id={$r['sb_id']}' onclick="return confirm('Sure?');">{$this->lang->words['delete_block']}</a></li>
				</ul>
			</td>
		</tr>
HTML;

//--endhtml--//
return $IPBHTML;
}

//*************************//
//($%^ Members ^%$)//
//*************************//

/**
 * Sending Item Form
 */
public function sendItemForm( $member, $buttons ) {

#items drop
$itemsDD = $this->registry->output->formDropdown( 'itemtype_id', $this->registry->class_ibEco_CP->getAllItems() );

#items drop
$bankTypesDD = $this->registry->output->formDropdown( 'bank_type', $this->registry->class_ibEco_CP->getBankTypes() );

$IPBHTML = "";
//--starthtml--//

$IPBHTML .= <<<HTML

<div class='section_title'>
	<h2>{$this->lang->words['sending_item_to']} {$member['formatted_name']}</h2>
	{$buttons}
</div>

<div class='acp-box'>
 	<h3>{$this->lang->words['transaction_details']}</h3>
		<table class='ipsTable double_pad'>
			<tr>
				<form id='adminform' action='{$this->settings['base_url']}{$this->form_code}&amp;do=sendEm' method='post' id='adform' name='adform' onsubmit="return confirm('{$this->lang->words['confirm_item_send']}');">
				<input type='hidden' name='member_id' value='{$member['member_id']}' />
				<input type='hidden' name='member_name' value='{$member['members_display_name']}' />
				<td class='tablerow1' style='width: 50%;text-align:right;'><strong>{$this->lang->words['item_recipient']}:</strong></td>
				<td class='tablerow2' style='width: 50%'>{$member['formatted_name']}</td>
			</tr>
			<tr>
				<td class='tablerow1' style='width: 50%;text-align:right;'><strong>{$this->lang->words['select_item_to_send']}:</strong></td>
				<td class='tablerow2' style='width: 50%'>{$itemsDD}</td>
			</tr>
			<tr>
				<td class='tablerow1' style='width: 50%;text-align:right;'><strong>{$this->lang->words['bank_type_if_app']}:</strong></td>
				<td class='tablerow2' style='width: 50%'>{$bankTypesDD}</td>
			</tr>
			<tr>
				<td class='tablerow1' style='width: 50%;text-align:right;'><strong>{$this->lang->words['quantity_amount_balance_debt']}:</strong></td>
				<td class='tablerow2' style='width: 50%'><input type='text' name='amount' size='5' /></td>
			</tr>			
		</table>
	<div class='acp-actionbar'>
		<input type='submit' class='button' value='{$this->lang->words['send']}' />
	</div>
	</form>
</div>

HTML;

//--endhtml--//
return $IPBHTML;
}

/**
 * Find Member
 */
public function findMember( $buttons, $type ) {

if ( $type == 'edit'  )
{
	$title 	= $this->lang->words['find_member_to_edit'];
	$button = $this->lang->words['find_member'];
	$action = 'find_em';
}
else
{
	$title 	= $this->lang->words['find_item_recipient'];
	$button = $this->lang->words['find_recipient'];
	$action = 'send_item';
}
$IPBHTML = "";
//--starthtml--//

$IPBHTML .= <<<HTML

<script type='text/javascript' language='javascript'>
//<![CDATA[
	ipb.templates['ajax_loading'] 	= "<div id='ajax_loading'>Loading...</div>";
	acp = new IPBACP;
//]]>
</script>

<div class='section_title'>
	<h2>{$title}</h2>
	{$buttons}
</div>

<div class='acp-box'>
 	<h3>{$this->lang->words['member_search']}</h3>
		<table width='100%' border='0' cellspacing='0' cellpadding='0' class='double_pad'>
			<tr>
				<form id='adminform' action='{$this->settings['base_url']}{$this->form_code}&amp;do={$action}' method='post' id='adform' name='adform' onsubmit='return checkform();'>
				<td class='tablerow1' style='width: 50%;text-align:right;'><strong>{$this->lang->words['enter_display_name']}:</strong></td>
				<td class='tablerow2' style='width: 50%'><input type="text" class='text_input' id='mem_name1' name="mem_name" size="20" value="{$mem_name}" tabindex="0" /></td>
			</tr>
			<tr>
				<td class='tablerow1' style='width: 50%;text-align:right;'><strong>{$this->lang->words['or_member_id']}:</strong></td>
				<td class='tablerow2' style='width: 50%'><input type="text" class='text_input' id='mem_id' name="mem_id" size="10" tabindex="0" /></td>
			</tr>			
		</table>
	<div class='acp-actionbar'>
		<input type='submit' class='button' value='{$button}' />
	</div>
	</form>
</div>


<script type="text/javascript">
document.observe("dom:loaded", function(){
	var autocomplete= new ipb.Autocomplete( $('mem_name1'), { multibox: false, url: acp.autocompleteUrl, templates: { wrap: acp.autocompleteWrap, item: acp.autocompleteItem } } );
});
</script>

HTML;

//--endhtml--//
return $IPBHTML;
}

/**
 * Member form
 */
public function memberForm( $member, $portTab, $buttons ) {

$form	= array();

$title 	= $this->lang->words['editing'].': '.$member['formatted_name'];
$button = $this->lang->words['edit_member'];

// Economy Stats:

$form['total_points']	= $this->registry->output->formInput( "total_points", $member['total_points'] );
$form['eco_worth']		= $this->registry->output->formInput( "eco_worth", $member['eco_worth'] );
$form['eco_on_welfare']	= $this->registry->output->formYesNo( "eco_on_welfare", $member['eco_on_welfare'] );
$form['eco_welfare']	= $this->registry->output->formInput( "eco_welfare", $member['eco_welfare'] );
	
$IPBHTML = "";
//--starthtml--//

$IPBHTML .= <<<HTML
<div class='section_title'>
	<h2>{$title}</h2>
	{$buttons}	
</div>

<form id='adminform' action='{$this->settings['base_url']}{$this->form_code}&amp;do=edit_member' method='post' id='adform' name='adform' onsubmit='return checkform();'>
<input type='hidden' name='member_id' value='{$member['member_id']}' />
<input type='hidden' name='member_name' value='{$member['members_display_name']}' />

<div class='acp-box'>
	<h3>{$this->lang->words['member_details']}</h3>
	<div class='ipsTabBar with_left with_right' id='tabstrip_manage_member'>
		<span class='tab_left'>&laquo;</span>
		<span class='tab_right'>&raquo;</span>
		<ul>
			<li id='tab_1'>{$this->settings['eco_general_name']} {$this->lang->words['account_stats']}</li>
			<li id='tab_2'>{$this->lang->words['portfolio']}</li>
		</ul>
	</div>
	<div class='ipsTabBar_content' id='tabstrip_manage_member_content'>
		<div id='tab_1_content'>
			<table class='ipsTable'>
				<tr>
					<th colspan='2'>{$this->settings['eco_general_currency']}</th>
				</tr>
				<tr>
					<td style='width: 40%'>
						<label>{$this->lang->words['total_pts']}</label>
					</td>
					<td style='width: 60%'>
						{$form['total_points']}
					</td>
				</tr>
				<tr>
					<th colspan='2'>{$this->lang->words['worth']}</th>
				</tr>
				<tr>
					<td style='width: 40%'>
						<label>{$this->lang->words['total_worth']}</label>
					</td>
					<td style='width: 60%'>
						{$form['eco_worth']}
					</td>
				</tr>
				<tr>
					<th colspan='2'>{$this->lang->words['on_welfare']}</th>
				</tr>
				<tr>
					<td style='width: 40%'>
						<label>{$this->lang->words['cur_on_welfare']}</label>
					</td>
					<td style='width: 60%'>
						{$form['eco_on_welfare']}
					</td>
				</tr>
				<tr>
					<th colspan='2'>{$this->lang->words['welfare']}</th>
				</tr>			
				<tr>
					<td style='width: 40%'>
						<label>{$this->lang->words['total_welfare_rec']}</label>
					</td>
					<td style='width: 60%'>
						{$form['eco_welfare']}
					</td>
				</tr>
				<tr>
					<td style='width: 40%'>
						<label>{$this->lang->words['more']}</label>
					</td>
					<td style='width: 60%'></td>
				</tr>
			</table>
		</div>
		<div id='tab_2_content'>
			{$portTab}
		</div>
	</div>
	<div class='acp-actionbar'>
		<input type='submit' class='button' value='{$button}' />
	</div>
	</form>
</div>

<script type='text/javascript'>
jQ("#tabstrip_manage_member").ipsTabBar({ tabWrap:
"#tabstrip_manage_member_content" });
</script>

HTML;

//--endhtml--//
return $IPBHTML;
}

/**
 * Portfolio List Wrapper (for specific members)
 */
public function memPortfolioListWrapper( $content ) {

$IPBHTML = "";
//--starthtml--//

$IPBHTML .= <<<HTML
		<table class='ipsTable'>
			<tr>
				<th>&nbsp;</td>
				<th>{$this->lang->words['name']}</td>				
				<th>{$this->lang->words['type']}</td>
				<th>{$this->lang->words['Quantity']}</td>
				<th>{$this->lang->words['rate']}</td>
				<th>{$this->lang->words['aquired']}</td>
				<th>{$this->lang->words['delete']}?</td>
				<th>&nbsp;</td>
			</tr>
			{$content}
		</table>
HTML;

//--endhtml--//
return $IPBHTML;
}

/**
 * Portfolio List row (for specific members)
 */
public function memPortfolioListRow( $r="" ) {
$IPBHTML = "";
//--starthtml--//

$IPBHTML .= <<<HTML
<tr class='ipsControlRow'>
	<td><img src='{$this->settings['img_url']}/eco_images/{$r['image']}' border='0' /></td>
	<td><span class='larger_text'><a style='text-decoration:none;' href='{$this->settings['base_url']}{$this->form_code}&amp;do=portfolio_item_form&amp;member_id={$r['p_member_id']}&amp;id={$r['p_id']}'>{$r['title']}</a></span></td>
	<td>{$r['type']}</td>
	<td>{$r['quantity']}</td>
	<td>{$r['rate']}</td>
	<td>{$r['purchase_date']}</td>
	<td><input type='hidden' name='port_items[]' value='{$r['p_id']}' /><input type='checkbox' name='delete_item_{$r['p_id']}' id='delete_item' /> </td>  
		<td>
			<ul class='ipsControlStrip'>
				<li class='i_edit' title='{$this->lang->words['edit_item']}'><a href='{$this->settings['base_url']}{$this->form_code}&amp;do=portfolio_item_form&amp;member_id={$r['p_member_id']}&amp;id={$r['p_id']}'>{$this->lang->words['edit_item']}</a></li>
				<li class='i_delete' title='{$this->lang->words['delete_item']}'><a href='{$this->settings['base_url']}{$this->form_code}&amp;do=delete_port_items&amp;id_to_delete={$r['p_id']}' onclick='return confirm("Are you sure you want to delete?")'>Delete</a></li>
			</ul>
		</td>
</tr>
HTML;

//--endhtml--//
return $IPBHTML;
}

/**
 * Portfolio List Wrapper (for all)
 */
public function portfolioListWrapper( $content, $type, $buttons, $pages ) {

#init
$iTypes = array();
$title 	= sprintf( $this->lang->words['showing_port_items'], $type );
$sw 	= ( $this->request['sw'] == 'DESC' ) ? 'ASC' : 'DESC';

$itemTypes = array('');

if (is_array($this->registry->ecoclass->cartTypes) && count($this->registry->ecoclass->cartTypes))
{
	foreach($this->registry->ecoclass->cartTypes AS $cartType)
	{
		if ($cartType['savedInPortfolio'])
		{
			$itemTypes[] = $cartType['key'];
		}
	}
}

foreach ( $itemTypes AS $type )
{
	$iTypes[] = array( $type, $this->lang->words[ $type.'s' ] );
}

$narrowDD = $this->registry->output->formDropdown( "p_type", $iTypes, $this->request['p_type'] );
	
$IPBHTML = "";
//--starthtml--//

$IPBHTML .= <<<HTML
<div class='section_title'>
	<h2>{$title}</h2>
	{$buttons}	
</div>

<form id='adminform' action='{$this->settings['base_url']}{$this->form_code}&amp;do=delete_port_items' method='post' id='adform' name='adform' onsubmit="return confirm('{$this->lang->words['confirm_port_item_delete']}');">
<input type='hidden' name='p_type' value='{$this->request['p_type']}' />
<input type='hidden' name='sw' value='{$this->request['sw']}' />
<input type='hidden' name='sort' value='{$this->request['sort']}' />
<input type='hidden' name='st' value='{$this->request['st']}' />

<div class='acp-box'>
<h3>{$this->lang->words['portfolio_items']}</h3>
	<table class='ipsTable'>
		<tr>
			<th>&nbsp;</th>
			<th><a style='text-decoration:none' href='{$this->settings['base_url']}{$this->form_code}&amp;do=portfolio&amp;p_type={$this->request['p_type']}&amp;sort=members_display_name&amp;sw={$sw}'>{$this->lang->words['member']}</a></th>	
			<th><a style='text-decoration:none' href='{$this->settings['base_url']}{$this->form_code}&amp;do=portfolio&amp;p_type={$this->request['p_type']}&amp;sort=p_type_id&amp;sw={$sw}'>{$this->lang->words['name']}</a></th>				
			<th><a style='text-decoration:none' href='{$this->settings['base_url']}{$this->form_code}&amp;do=portfolio&amp;p_type={$this->request['p_type']}&amp;sort=p_type&amp;sw={$sw}'>{$this->lang->words['type']}</a></th>
			<th><a style='text-decoration:none' href='{$this->settings['base_url']}{$this->form_code}&amp;do=portfolio&amp;p_type={$this->request['p_type']}&amp;sort=p_amount&amp;sw={$sw}'>{$this->lang->words['Quantity']}</a></th>
			<th><a style='text-decoration:none' href='{$this->settings['base_url']}{$this->form_code}&amp;do=portfolio&amp;p_type={$this->request['p_type']}&amp;sort=p_rate&amp;sw={$sw}'>{$this->lang->words['rate']}</a></th>
			<th><a style='text-decoration:none' href='{$this->settings['base_url']}{$this->form_code}&amp;do=portfolio&amp;p_type={$this->request['p_type']}&amp;sort=p_purch_date&amp;sw={$sw}'>{$this->lang->words['aquired']}</a></th>
			<th>{$this->lang->words['delete']}?</th>
			<th>&nbsp;</th>
		</tr>
		{$content}
	</table>
	<div class='acp-actionbar'>
			<input type='submit' class='button' value='{$this->lang->words['delete_selected']}' />
		</form>
	</div>
</div>

<div style='padding:10px'>
	<form id='adminform' action='{$this->settings['base_url']}{$this->form_code}&amp;do=portfolio' method='post' id='adform' name='adform'">
		{$pages}<div valign='middle' style='float:right;'><strong>{$this->lang->words['show_only']}...</strong>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; {$narrowDD}&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type="submit" class='realbutton' value="{$this->lang->words['go']}" tabindex="0" /></div>
	</form>
</div>		

HTML;

//--endhtml--//
return $IPBHTML;
}

/**
 * Portfolio List row (for all)
 */
public function portfolioListRow( $r="" ) {
$IPBHTML = "";
//--starthtml--//

$IPBHTML .= <<<HTML
	<tr class='ipsControlRow'>
		<td><img src='{$this->settings['img_url']}/eco_images/{$r['image']}' border='0' /></td>
		<td><a style='text-decoration:none;' href='{$this->settings['base_url']}{$this->form_code}&amp;do=find_em&amp;mem_id={$r['p_member_id']}'>{$r['formatted_name']}</a></td> 
		<td><span class='larger_text'><a style='text-decoration:none;' href='{$this->settings['base_url']}{$this->form_code}&amp;do=portfolio_item_form&amp;member_id={$r['p_member_id']}&amp;id={$r['p_id']}'>{$r['title']}</a></span></td> 
		<td>{$r['type']}</td>
		<td>{$r['quantity']}</td>
		<td>{$r['rate']}</td>
		<td>{$r['purchase_date']}</td>
		<td><input type='hidden' name='port_items[]' value='{$r['p_id']}' /><input type='checkbox' name='delete_item_{$r['p_id']}' id='delete_item' /> </td>  
		<td>
			<ul class='ipsControlStrip'>
				<li class='i_edit'><a href='{$this->settings['base_url']}{$this->form_code}&amp;do=portfolio_item_form&amp;member_id={$r['p_member_id']}&amp;id={$r['p_id']}'>{$this->lang->words['edit_item']}</a></li>
				<li class='i_delete'><a href='{$this->settings['base_url']}{$this->form_code}&amp;do=delete_port_items&amp;id_to_delete={$r['p_id']}' onclick='return confirm("Are you sure you want to delete?")'>Delete</a></li>
			</ul>
		</td>
	</tr>
HTML;

//--endhtml--//
return $IPBHTML;
}

/**
 * Item form
 */
public function portfolioItemForm( $member, $item, $buttons ) {

$form	= array();

$title 	= sprintf( $this->lang->words['editing_mems_port_item'], $member['formatted_name'] ).': '.$item['title'].' '.$item['type'];
$button = $this->lang->words['edit_port_item'];

// General Tab:

$form['p_amount']	= $this->registry->output->formInput( "p_amount", $this->registry->ecoclass->makeNumeric($item['p_amount'], false) );
$form['p_max']		= $this->registry->output->formInput( "p_max", $this->registry->ecoclass->makeNumeric($item['p_max'], false) );
$form['p_rate']		= $this->registry->output->formInput( "p_rate", $item['p_rate'] );
	
$IPBHTML = "";
//--starthtml--//

$IPBHTML .= <<<HTML
<div class='section_title'>
	<h2>{$title}</h2>
	{$buttons}	
</div>

<form id='adminform' action='{$this->settings['base_url']}{$this->form_code}&amp;do=edit_port_item' method='post' id='adform' name='adform' onsubmit='return checkform();'>
<input type='hidden' name='p_id' value='{$item['p_id']}' />
<input type='hidden' name='member_name' value='{$member['members_display_name']}' />
<input type='hidden' name='member_id' value='{$member['member_id']}' />
<input type='hidden' name='port_name' value='{$item['title']}' />

<div class='acp-box'>
<h3>{$this->lang->words['port_item']}</h3>
	<table class='ipsTable double_pad'>
		<tr>
			<th colspan='2'>{$this->lang->words['item_inventory_deets']}</th>
		</tr>
		<tr>
			<td style='width: 40%'>
				<label>{$this->lang->words['quantity_amount']}</label>
			</td>
			<td style='width: 60%'>
				{$form['p_amount']}
			</td>
		</tr>
		<tr>
			<td style='width: 40%'>
				<label>{$this->lang->words['max_quantity_amount']}</label><br />{$this->lang->words['leave_blank_for_no_limit']}
			</td>
			<td style='width: 60%'>
				{$form['p_max']}
			</td>
		</tr>			
		<tr>
			<td style='width: 40%'>
				<label>{$this->lang->words['current_rate']}</label><br />{$this->lang->words['leave_blank_for_none']}
			</td>
			<td style='width: 60%'>
				{$form['p_rate']}
			</td>
		</tr>
	</table>
	<div class='acp-actionbar'>
		<input type='submit' class='button' value='{$button}' />
	</div>
	</form>
</div>		
HTML;

//--endhtml--//
return $IPBHTML;
}

//*************************//
//($%^ Shop Items ^%$)//
//*************************//

/**
 * Item form
 */
public function itemForm( $item, $perm_matrix, $buttons ) {

$form	= array();

#buttons and stuff
$title 	= ( $this->request['si_id'] ) ? $this->lang->words['editing_item']." : ".$item['si_title'] : $this->lang->words['adding_item'];
$button = ( $this->request['si_id'] ) ? $this->lang->words['edit_item_button'] : $this->lang->words['add_item_button_title'];

#first time?
$imageInput = ( $item['si_id'] ) ? '' : $this->imageInput();

#Categories dropdown
$cats[''] = array( '','');

$this->DB->build( array( 'select' => '*', 'from' => 'eco_shop_cats' ) );
$this->DB->execute();

while( $row = $this->DB->fetch() )
{
	$cats[] = array( $row['sc_id'], $row['sc_title'] );
}

#General Tab:
$form['si_title']			= $this->registry->output->formInput( "si_title", $item['si_title'] );
$form['si_desc']			= $this->registry->output->formTextarea( "si_desc", $item['si_desc'] );
$form['si_cost']			= $this->registry->output->formsimpleInput( "si_cost", $item['si_cost'] );
$form['si_on']				= $this->registry->output->formYesNo( "si_on", $item['si_on'] );
$form['si_cat']				= $this->registry->output->formDropdown( "si_cat", $cats, $item['si_cat'] );
$form['si_inventory']		= $this->registry->output->formsimpleInput( "si_inventory", $item['si_inventory'] );
$form['si_restock']			= $this->registry->output->formYesNo( "si_restock", $item['si_restock'] );
$form['si_restock_amt']		= $this->registry->output->formsimpleInput( "si_restock_amt", $item['si_restock_amt'] );
$form['si_restock_time']	= $this->registry->output->formsimpleInput( "si_restock_time", $item['si_restock_time'] );
$form['si_limit']			= $this->registry->output->formsimpleInput( "si_limit", $item['si_limit'] );
$form['si_other_users']		= $this->registry->output->formYesNo( "si_other_users", $item['si_other_users'] );
$form['si_allow_user_pm']	= $this->registry->output->formYesNo( "si_allow_user_pm", $item['si_allow_user_pm'] );
$form['si_default_pm']		= $this->registry->output->formTextarea( "si_default_pm", $item['si_default_pm'] );
$form['si_max_daily_buys']	= $this->registry->output->formsimpleInput( "si_max_daily_buys", $item['si_max_daily_buys'] );

#image sidebar
if ( ! $item['si_url_image'] )
{
	$item['si_image']			= str_replace('-thumb.', '.', $item['si_image']);
	$form['si_image']			= ($item['si_image']) ? "{$this->settings['upload_url']}/ibEconomy_images/{$item['si_image']}" : "{$this->settings['public_dir']}/style_images/master/eco_images/nothing.png";
}
else
{
	$form['si_image'] = $item['si_url_image'];
}
$form['si_total_items'] 	= $this->registry->getClass('class_localization')->formatNumber($item['si_sold']);

#Extra Setings Tab
$form['si_min_num']			= $this->registry->output->formInput( "si_min_num", $item['si_min_num'] );
$form['si_max_num']			= $this->registry->output->formInput( "si_max_num", $item['si_max_num'] );
$form['si_extra_settings_1']= $this->registry->output->formInput( "si_extra_settings_1", $item['si_extra_settings_1'] );

#Perms
$form['si_use_perms']	= $this->registry->output->formYesNo( "si_use_perms", $item['si_use_perms'] );

#PM Options

#own or others
if ( $item['own_or_other'])
{
	if ( $item['si_file'] != 'secret_message.php')
	{
		$pmOptions = "			<tr>
					<td style='width: 40%'>
						<label>{$this->lang->words['user_send_pm']}</label><br />{$this->lang->words['user_send_pm_desc']}
					</td>
					<td style='width: 60%'>
						{$form['si_allow_user_pm']}
					</td>
				</tr>
				<tr>
					<td style='width: 40%'>
						<label>{$this->lang->words['default_msg']}</label><br />{$this->lang->words['default_msg_desc']}
					</td>
					<td style='width: 60%'>
						{$form['si_default_pm']}
					</td>
			</tr>";
	}
	
	$ownOrOthers = "			<tr>
				<th colspan='2'>{$this->lang->words['type_settings']}</th>
			</tr>
			<tr>
				<td style='width: 40%'>
					<label>{$this->lang->words['app_to_others_or_self']}</label><br />{$this->lang->words['app_to_others_or_self_exp']}
				</td>
				<td style='width: 60%'>
					{$form['si_other_users']}
				</td>
			</tr>
			{$pmOptions}";			
}
else if ( in_array($item['si_file'], array('steal_points.php', 'steal_rep.php') ) )
{
		$pmOptions = "			<tr>
					<td style='width: 40%'>
						<label>{$this->lang->words['user_send_pm']}</label><br />{$this->lang->words['user_send_pm_desc']}
					</td>
					<td style='width: 60%'>
						{$form['si_allow_user_pm']}
					</td>
				</tr>
				<tr>
					<td style='width: 40%'>
						<label>{$this->lang->words['default_msg']}</label><br />{$this->lang->words['default_msg_desc']}
					</td>
					<td style='width: 60%'>
						{$form['si_default_pm']}
					</td>
			</tr>";
			
	$ownOrOthers = "			<tr>
				<th colspan='2'>{$this->lang->words['type_settings']}</th>
			</tr>
			{$pmOptions}";			
}
	
$IPBHTML = "";
//--starthtml--//

$IPBHTML .= <<<HTML
<div class='section_title'>
	<h2>{$title}</h2>
	{$buttons}	
</div>

{$this->includeJS4ImagePopup()}
<form id='adminform' action='{$this->settings['base_url']}{$this->form_code}&amp;do=do_item' method='post' id='adform' name='adform' onsubmit='return checkform();' enctype='multipart/form-data'>
<input type='hidden' name='si_id' value='{$item['si_id']}' />
<input type='hidden' name='si_name' value='{$item['si_title']}' />
<input type='hidden' name='si_file' value='{$item['si_file']}' />

<div class='acp-box'>
	<h3>{$this->lang->words['shopitem_details']}</h3>
	<div class='ipsTabBar with_left with_right' id='tabstrip_manage_shopitem'>
		<span class='tab_left'>&laquo;</span>
		<span class='tab_right'>&raquo;</span>
		<ul>
			<li id='tab_1'>{$this->lang->words['general_tab_title']}</li>
			<li id='tab_2'>{$this->lang->words['extra_settings']}</li>
			<li id='tab_3'>{$this->lang->words['permissions']}</li>
		</ul>
	</div>
	<div class='ipsTabBar_content' id='tabstrip_manage_shopitem_content'>
		<div id='tab_1_content'>
			<div style='float: left; width: 70%'>
				<table class='ipsTable'>
					<tr>
						<th colspan='2'>{$this->lang->words['gen_settings']}</th>
					</tr>
					<tr>
						<td>
							<label>{$this->lang->words['item_name']}</label>
						</td>
						<td>
							{$form['si_title']}
						</td>
					</tr>
					<tr>
						<td>
							<label>{$this->lang->words['item_description']}</label>
						</td>
						<td>
							{$form['si_desc']}
						</td>
					</tr>
					<tr>
						<td>
							<label>{$this->lang->words['item_cost']}?</label>
						</td>
						<td>
							{$form['si_cost']}
						</td>
					</tr>			
					<tr>
						<td>
							<label>{$this->lang->words['enabled_title']}?</label>
						</td>
						<td>
							{$form['si_on']}
						</td>
					</tr>
					<tr>
						<th colspan='2'>{$this->lang->words['category']}</th>
					</tr>
					<tr>
						<td>
							<label>{$this->lang->words['select_category']}?</label>
						</td>
						<td>
							{$form['si_cat']}
						</td>
					</tr>
					<tr>
						<th colspan='2'>{$this->lang->words['limit']}</th>
					</tr>
					<tr>
						<td>
							<label>{$this->lang->words['how_many_limit']}?<br />{$this->lang->words['zero_unlimited']}</label>
						</td>
						<td>
							{$form['si_limit']}
						</td>
					</tr>
					<tr>
						<td>
							<label>{$this->lang->words['how_many_limit_per_day']}?<br />{$this->lang->words['zero_unlimited']}</label>
						</td>
						<td>
							{$form['si_max_daily_buys']}
						</td>
					</tr>			
					{$ownOrOthers}			
					<tr>
						<th colspan='2'>{$this->lang->words['stock_settings']}</th>
					</tr>
					<tr>
						<td>
							<label>{$this->lang->words['cur_inventory']}</label>
						</td>
						<td>
							{$form['si_inventory']}
						</td>
					</tr>
					<tr>
						<td>
							<label>{$this->lang->words['enable_restocking']}?</label>
						</td>
						<td>
							{$form['si_restock']}
						</td>
					</tr>			
					<tr>
						<td>
							<label>{$this->lang->words['how_many_restock']}?</label>
						</td>
						<td>
							{$form['si_restock_amt']}
						</td>
					</tr>	
					<tr>
						<td>
							<label>{$this->lang->words['restock_time']}?</label>
						</td>
						<td>
							{$form['si_restock_time']}
						</td>
					</tr>	
					{$imageInput}			
				</table>
			</div>
				
			<div style='float: left; width: 30%;background:#849cb7' class='acp-sidebar'>
			
				{$this->image_sidebar($item['si_id'], 'shop_item', $form['si_image'])}
				<div class='sidebar_box' style='width: 65%'> 
					<strong>{$this->lang->words['total_sold']}:&nbsp;</strong>{$form['si_total_items']}<br /><br /> 
				</div> 
			</div> 
			<div style='clear: both;'></div>
		</div>
		<div id='tab_2_content'>
			<table class='ipsTable'>
				<tr>
					<th colspan='2'>{$this->lang->words['extra_settings']}</th>
				</tr>	
				{$item['extra_settings']}				
			</table>
		</div>
		<div id='tab_3_content'>
			<table class='ipsTable'>
				<tr>
					<td>
						<label>{$this->lang->words['use_perm_matrix']}?</label><br />{$this->lang->words['use_perm_matrix_exp']}
					</td>
					<td>
						{$form['si_use_perms']}
					</td>
				</tr>
				<tr>
					<td colspan='2'>
						{$perm_matrix}
					</td>
				</tr>				
			</table>
		</div>		
	</div>

	<div class='acp-actionbar'>
		<input type='submit' class='button' value='{$button}' />
	</div>
	</form>
</div>

<script type='text/javascript'>
jQ("#tabstrip_manage_shopitem").ipsTabBar({ tabWrap:
"#tabstrip_manage_shopitem_content" });
</script>

HTML;

//--endhtml--//
return $IPBHTML;
}

/**
 * Shop Item extra settings
 */
public function itemExtraSettingRow( $setting ) {

$form	= array();

if ( $setting['form_type'] && !in_array( $setting['form_type'], array( 'formDropdown', 'formMultiDropdown') ) )
{
	$form['setting_form'] = $this->registry->output->$setting['form_type']( $setting['field'], $setting['value'] );
}
else if ( $setting['form_type'] == 'formDropdown' )
{
	$form['setting_form'] = $this->registry->output->formDropdown( $setting['field'], $setting['dd'], $setting['value'] );
}
else if ( $setting['form_type'] == 'formMultiDropdown' )
{
	if ( $setting['type'] == 'groups' )
	{
		$form['setting_form'] = $this->registry->output->formMultiDropdown( "si_protected_g[]", $setting['dd'], explode( ",", $setting['value'] ) );
	}
	else if ( $setting['type'] == 'forums' )
	{
		$form['setting_form'] = $this->registry->output->formMultiDropdown( "si_protected[]", $setting['dd'], explode( ",", $setting['value'] ) );	
	}
	else if ( $setting['type'] == 'image_types' )
	{
		$form['setting_form'] = $this->registry->output->formMultiDropdown( $setting['field']."[]", $setting['dd'], explode( ",", $setting['value'] ) );	
	}
	else
	{
		$form['setting_form'] = $this->registry->output->formMultiDropdown( "si_protected[]", $setting['dd'], explode( ",", $setting['value'] ) );
	}
}

$IPBHTML = "";
//--starthtml--//

$IPBHTML .= <<<HTML
	
			<tr>
				<td style='width: 40%'>
					<label>{$setting['words']}</label><br />{$setting['desc']}
				</td>
				<td style='width: 60%'>
					{$form['setting_form']}
				</td>
			</tr>

HTML;

//--endhtml--//
return $IPBHTML;
}

/**
 * Overview of Shop Items
 */
public function itemsOverviewWrapper( $content, $defaultItems, $buttons ) {

#Default Store Item Templates
$itemsDrop = $this->registry->output->formDropdown( "shop_item_file", $defaultItems );

#grab cats from cache
$cats = array('');

if ( is_array($this->caches['ibEco_shopcats'] ) )
{ 		
	foreach ( $this->caches['ibEco_shopcats']  AS $cat )
	{
		$cats[] = array($cat['sc_id'], $cat['sc_title']);
	}
}

$narrowDD = $this->registry->output->formDropdown( "cat_id", $cats, $this->request['cat_id'] );

$IPBHTML = "";
//--starthtml--//

$IPBHTML .= <<<HTML

<div class='section_title'>
	<h2>{$this->lang->words['shop_items']}</h2>
	{$buttons}
</div>

<div class='acp-box'>
 	<h3>{$this->lang->words['items']}</h3>
		<table class='ipsTable' id='reorderable_table'>	
			<tr>
				<th class='col_drag'>&nbsp;</th>
				<th>&nbsp;</th>
				<th>{$this->lang->words['item_name']}</th>
				<th width='20%'>{$this->lang->words['item_description']}</th>
				<th>{$this->lang->words['price']}</th>
				<th>{$this->lang->words['num_owned']}</th>
				<th>{$this->lang->words['enabled_title']}?</th>
				<th>&nbsp;</th>
			</tr>
			{$content}	
		</table>
					
	<div class='acp-actionbar'>
		<form id='adminform' method='post' action='{$this->settings['base_url']}{$this->form_code}&amp;do=item&amp;type=add' onsubmit='return ACPForums.submitModForm()'>
			{$this->lang->words['select_item']}: {$itemsDrop}&nbsp;&nbsp;&nbsp;&nbsp;<input type='submit' class='button' value='{$this->lang->words['create']}' />
		</form>
	</div>
</div>

<div style='padding:10px'>
	<form id='adminform' action='{$this->settings['base_url']}{$this->form_code}&amp;do=items' method='post' id='adform' name='adform'">
		<div valign='middle' style='float:right;'><strong>{$this->lang->words['show_only']}...</strong>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; {$narrowDD}&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type="submit" class='realbutton' value="{$this->lang->words['go']}" /></div>
	</form>
</div>	

<script type='text/javascript'>
	jQ("#reorderable_table").ipsSortable( 'table', {
		url: "{$this->settings['base_url']}&{$this->form_code_js}&do=reorder&md5check={$this->registry->adminFunctions->getSecurityKey()}".replace( /&amp;/g, '&' )
	});
</script>

HTML;

//--endhtml--//
return $IPBHTML;
}

/**
 * Item row
 */
public function itemRow( $r="" ) {

$r['total_items']	= $this->registry->getClass('class_localization')->formatNumber( $r['total_items'] );
$r['si_cost']		= $this->registry->getClass('class_localization')->formatNumber( $r['si_cost'], $this->registry->ecoclass->decimal );
$r['si_on']			= $r['si_on'] 		? 'accept.png' : 'cross.png';

#custom image?
if ( ! $r['si_url_image'] )
{
	$r['si_image_src']	= $r['si_image'] ? "{$this->settings['upload_url']}/ibEconomy_images/{$r['si_image']}"	: "{$this->settings['img_url']}/eco_images/tag_blue.png";
}
else
{
	$r['si_image_src'] = $r['si_url_image'];
	$r['image_style']  = "style='height:50px;width:50px;'";
}


$IPBHTML = "";
//--starthtml--//

$IPBHTML .= <<<HTML
		<tr class='ipsControlRow isDraggable' id='items_{$r['si_id']}'>
			<td><span class='draghandle'>&nbsp;</span></td>
			<td style='text-align:center'><img {$r['image_style']} src='{$r['si_image_src']}' border='0' /></td>
			<td><span class='larger_text'><a style='text-decoration:none;' href='{$this->settings['base_url']}{$this->form_code}&amp;do=item&amp;type=edit&amp;si_id={$r['si_id']}'>{$r['si_title']}</a></span></td>
			<td>{$r['si_desc']}</td>
			<td>{$this->settings['eco_general_cursymb']}{$r['si_cost']}</td>
			<td>{$r['total_items']}</td>
			<td><img src='{$this->settings['skin_acp_url']}/images/icons/{$r['si_on']}' border='0' alt='-' class='ipd' /></td>	
			<td>
				<ul class='ipsControlStrip'>
					<li class='i_edit' title='{$this->lang->words['edit_item']}'><a href='{$this->settings['base_url']}{$this->form_code}&amp;do=item&amp;type=edit&amp;si_id={$r['si_id']}'>{$this->lang->words['edit_item']}</a></li>
					<li class='i_delete' title='{$this->lang->words['delete_item']}'><a href='{$this->settings['base_url']}{$this->form_code}&amp;do=delete&amp;type=item&amp;id={$r['si_id']}' onclick="return confirm('{$this->lang->words['confirm_item_delete']}');">{$this->lang->words['delete_item']}</a></li>
				</ul>
			</td>
		</tr>
HTML;

//--endhtml--//
return $IPBHTML;
}

/**
 * Delete image dhtml window
 */
public function includeJS4ImagePopup()
{

$IPBHTML = "";

$IPBHTML .= <<<HTML
	<!--[if IE]>
		<style type='text/css' media='all'>
			@import url( "{$this->settings['skin_acp_url']}/acp_ie_tweaks.css" );
		</style>
	<![endif]-->


<!--this is needed for image popups-->

<script type="text/javascript" src="{$this->settings['js_main_url']}acp.members.js"></script> 
<ul class='ipbmenu_content' id='member_tasks_menucontent' style='display: none'> 
</ul> 

HTML;

EOF;

return $IPBHTML;
}

//*************************//
//($%^ Shop Categories ^%$)//
//*************************//

/**
 * Category form
 */
public function catForm( $cat, $perm_matrix, $buttons ) {

$form	= array();

#buttons and stuff
$title 	= ( $cat ) ? $this->lang->words['editing_cat']." : ".$cat['sc_title'] : $this->lang->words['adding_cat'];
$button = ( $cat ) ? $this->lang->words['edit_cat_button'] : $this->lang->words['add_cat_button_title'];

#first time?
$imageInput = ( $cat['sc_id'] ) ? '' : $this->imageInput();

// General Tab:
$form['sc_title']		= $this->registry->output->formInput( "sc_title", $cat['sc_title'] );
$form['sc_desc']		= $this->registry->output->formTextarea( "sc_desc", $cat['sc_desc'] );
$form['sc_on']			= $this->registry->output->formYesNo( "sc_on", $cat['sc_on'] );
$cat['sc_image']		= str_replace('-thumb.', '.', $cat['sc_image']);
$form['sc_image']		= ($cat['sc_image']) ? "{$this->settings['upload_url']}/ibEconomy_images/{$cat['sc_image']}" : "{$this->settings['public_dir']}/style_images/master/eco_images/nothing.png";
$form['sc_total_items'] = $this->registry->getClass('class_localization')->formatNumber($this->caches['ibEco_shopcats'][ $cat['sc_id'] ]['total_items']);

// Perms
$form['sc_use_perms']	= $this->registry->output->formYesNo( "sc_use_perms", $cat['sc_use_perms'] );
	
$IPBHTML = "";
//--starthtml--//

$IPBHTML .= <<<HTML
<div class='section_title'>
	<h2>{$title}</h2>
	{$buttons}	
</div>

{$this->includeJS4ImagePopup()}
<form id='adminform' action='{$this->settings['base_url']}{$this->form_code}&amp;do=do_cat' method='post' id='adform' name='adform' onsubmit='return checkform();' enctype='multipart/form-data'>
<input type='hidden' name='sc_id' value='{$cat['sc_id']}' />
<input type='hidden' name='sc_name' value='{$cat['sc_title']}' />

<div class='acp-box'>
	<h3>{$this->lang->words['shopcat_details']}</h3>
	<div class='ipsTabBar with_left with_right' id='tabstrip_manage_shopcat'>
		<span class='tab_left'>&laquo;</span>
		<span class='tab_right'>&raquo;</span>
		<ul>
			<li id='tab_1'>{$this->lang->words['general_tab_title']}</li>
			<li id='tab_2'>{$this->lang->words['permissions']}</li>
		</ul>
	</div>
	<div class='ipsTabBar_content' id='tabstrip_manage_shopcat_content'>
		<div id='tab_1_content'>
			<div style='float: left; width: 70%'>
				<table class='ipsTable'>
					<tr>
						<th colspan='2'>{$this->lang->words['gen_settings']}</th>
					</tr>
					<tr>
						<td>
							<label>{$this->lang->words['cat_name']}</label>
						</td>
						<td>
							{$form['sc_title']}
						</td>
					</tr>
					<tr>
						<td>
							<label>{$this->lang->words['cat_description']}</label>
						</td>
						<td>
							{$form['sc_desc']}
						</td>
					</tr>			
					<tr>
						<td>
							<label>{$this->lang->words['enabled_title']}?</label>
						</td>
						<td>
							{$form['sc_on']}
						</td>
					</tr>
					{$imageInput}
				</table>
			</div>
				
			<div style='float: left; width: 30%;background:#849cb7' class='acp-sidebar'>
				{$this->image_sidebar($cat['sc_id'], 'shop_cat', $form['sc_image'])}

				<div class='sidebar_box' style='width: 65%'> 
					<strong>{$this->lang->words['item_count']}:&nbsp;</strong>{$form['sc_total_items']}<br /><br /> 
				</div>
			</div> 
			<div style='clear: both;'></div>
		</div>
		<div id='tab_2_content'>
			<table class='ipsTable'>
				<tr>
					<td>
						<label>{$this->lang->words['use_perm_matrix']}?</label><br />{$this->lang->words['use_perm_matrix_exp']}
					</td>
					<td>
						{$form['sc_use_perms']}
					</td>
				</tr>
				<tr>
					<td colspan='2'>
						{$perm_matrix}
					</td>
				</tr>				
			</table>
		</div>		
	</div>
	<div class='acp-actionbar'>
		<input type='submit' class='button' value='{$button}' />
	</div>
	</form>
</div>

<script type='text/javascript'>
jQ("#tabstrip_manage_shopcat").ipsTabBar({ tabWrap:
"#tabstrip_manage_shopcat_content" });
</script>
	
HTML;

//--endhtml--//
return $IPBHTML;
}

/**
 * Overview of Shop Categories
 */
public function catsOverviewWrapper( $content, $buttons ) {

$IPBHTML = "";
//--starthtml--//

$IPBHTML .= <<<HTML

<div class='section_title'>
	<h2>{$this->lang->words['shop_cats']}</h2>
	{$buttons}
</div>

<div class='acp-box'>
 	<h3>{$this->lang->words['cats']}</h3>
		<table class='ipsTable' id='reorderable_table'>	
			<tr>
				<th class='col_drag'>&nbsp;</th>
				<th>&nbsp;</th>
				<th>{$this->lang->words['cat_name']}</th>
				<th>{$this->lang->words['cat_description']}</th>
				<th>{$this->lang->words['cat_inventory']}</th>
				<th>{$this->lang->words['enabled_title']}?</th>
				<th>&nbsp;</th>
			</tr>
			{$content}
		</table>			
	<div class='acp-actionbar'>
		<form id='adminform' method='post' action='{$this->settings['base_url']}{$this->form_code}&amp;do=cat&amp;type=add' onsubmit='return ACPForums.submitModForm()'>
			<input type='submit' class='button' value='{$this->lang->words['add_new_cat']}' />
		</form>
	</div>
</div>

<script type='text/javascript'>
	jQ("#reorderable_table").ipsSortable( 'table', {
		url: "{$this->settings['base_url']}&{$this->form_code_js}&do=reorder&md5check={$this->registry->adminFunctions->getSecurityKey()}".replace( /&amp;/g, '&' )
	});
</script>

HTML;

//--endhtml--//
return $IPBHTML;
}

/**
 * Category row
 */
public function catRow( $r="" ) {

$r['delete_cat']	= intval($r['total_items']) == 0	? "<li class='i_delete' title='{$this->lang->words['delete_cat']}'><a href='{$this->settings['base_url']}{$this->form_code}&amp;do=delete&amp;type=cat&amp;id={$r['sc_id']}'>{$this->lang->words['delete_cat']}</a></li>" : "";
$r['total_items']	= $this->registry->getClass('class_localization')->formatNumber( $r['total_items'] );
$r['sc_on']			= $r['sc_on'] 		? 'accept.png' 	: 'cross.png';
$r['sc_image_src']	= $r['sc_image']	? "{$this->settings['upload_url']}/ibEconomy_images/{$r['sc_image']}"	: "{$this->settings['img_url']}/eco_images/application_home.png";
$IPBHTML = "";
//--starthtml--//

$IPBHTML .= <<<HTML
		<tr class='ipsControlRow isDraggable' id='cats_{$r['sc_id']}'>
			<td><span class='draghandle'>&nbsp;</span></td>
			<td style='text-align:center'><img src={$r['sc_image_src']} border='0' /></td>
			<td><span class='larger_text'><a style='text-decoration:none;' href='{$this->settings['base_url']}{$this->form_code}&amp;do=cat&amp;type=edit&amp;sc_id={$r['sc_id']}'>{$r['sc_title']}</a></span></td>
			<td>{$r['sc_desc']}</td>
			<td>{$r['total_items']}</td>
			<td><img src='{$this->settings['skin_acp_url']}/images/icons/{$r['sc_on']}' border='0' alt='-' class='ipd' /></td>	
			<td>
				<ul class='ipsControlStrip'>
					<li class='i_edit' title='{$this->lang->words['edit_cat_button']}'><a href='{$this->settings['base_url']}{$this->form_code}&amp;do=cat&amp;type=edit&amp;sc_id={$r['sc_id']}'>{$this->lang->words['edit_cat_button']}</a></li>
					{$r['delete_cat']}
				</ul>
			</td>
		</tr>
HTML;

//--endhtml--//
return $IPBHTML;
}

//*************************//
//($%^ Long-terms ^%$)//
//*************************//

/**
 * Long-term form
 */
public function ltForm( $long_term, $perm_matrix, $buttons ) {

#init some vars
$levels 	= array();
$lt_types 	= array();
$form		= array();

#risk
for ($i=0; $i<11; $i++)
{
	$risk = $i * 10;
	$levels[] = array($risk, $risk.'%');
}

#buttons and stuff
$title 	= ( $long_term ) ? $this->lang->words['editing_long_term']." : ".$long_term['lt_title'] : $this->lang->words['adding_long_term'];
$button = ( $long_term ) ? $this->lang->words['editing_long_term_button'] : $this->lang->words['adding_long_term_button'];

#image
$long_term['lt_image']		= str_replace('-thumb.', '.', $long_term['lt_image']);
$form['lt_image']			= ($long_term['lt_image']) ? "{$this->settings['upload_url']}/ibEconomy_images/{$long_term['lt_image']}" : "{$this->settings['public_dir']}/style_images/master/eco_images/nothing.png";

#first time?
$imageInput = ( $long_term['lt_id'] ) ? '' : $this->imageInput();

#types
$lt_types[] = array('401K', '401K');
$lt_types[] = array('Anuities', 'Anuities');
$lt_types[] = array('COD', 'COD');
$lt_types[] = array('IRA', 'IRA');
$lt_types[] = array('Mutual Fund', 'Mutual Fund');

#General Tab:
$form['lt_title']			= $this->registry->output->formInput( "lt_title", $long_term['lt_title'] );
$form['lt_min']				= $this->registry->output->formInput( "lt_min", $long_term['lt_min'] );
$form['lt_type']			= $this->registry->output->formDropdown( "lt_type", $lt_types, $long_term['lt_type'] );
$form['lt_risk_level']		= $this->registry->output->formDropdown( "lt_risk_level", $levels, $long_term['lt_risk_level'] );
$form['lt_early_cash']		= $this->registry->output->formYesNo( "lt_early_cash", $long_term['lt_early_cash'] );
$form['lt_early_cash_fee']	= $this->registry->output->formSimpleInput( "lt_early_cash_fee", $long_term['lt_early_cash_fee'] );
$form['lt_min_days']		= $this->registry->output->formInput( "lt_min_days", $long_term['lt_min_days'] );
$form['lt_on']				= $this->registry->output->formYesNo( "lt_on", $long_term['lt_on'] );
$form['lt_use_perms']		= $this->registry->output->formYesNo( "lt_use_perms", $long_term['lt_use_perms'] );
$form['investors'] 			= $this->registry->getClass('class_localization')->formatNumber($this->caches['ibEco_lts'][ $long_term['lt_id'] ]['investors']);
$form['total_invested'] 	= $this->registry->getClass('class_localization')->formatNumber($this->caches['ibEco_lts'][ $long_term['lt_id'] ]['total_invested'], $this->registry->ecoclass->decimal);

$this->lang->words['min_need_4_account'] = str_replace('<%POINTS_NAME%>', $this->settings['eco_general_currency'], $this->lang->words['min_need_4_account']);
$this->lang->words['max_deposit_into_account'] = str_replace('<%POINTS_NAME%>', $this->settings['eco_general_currency'], $this->lang->words['max_deposit_into_account']);
	
$IPBHTML = "";
//--starthtml--//

$IPBHTML .= <<<HTML
<div class='section_title'>
	<h2>{$title}</h2>
	{$buttons}	
</div>

{$this->includeJS4ImagePopup()}
<form id='adminform' action='{$this->settings['base_url']}{$this->form_code}&amp;do=do_long_term' method='post' id='adform' name='adform' onsubmit='return checkform();' enctype='multipart/form-data'>
<input type='hidden' name='lt_id' value='{$long_term['lt_id']}' />
<input type='hidden' name='lt_name' value='{$long_term['lt_title']}' />

<div class='acp-box'>
	<h3>{$this->lang->words['lt_details']}</h3>
	<div class='ipsTabBar with_left with_right' id='tabstrip_manage_lt'>
		<span class='tab_left'>&laquo;</span>
		<span class='tab_right'>&raquo;</span>
		<ul>
			<li id='tab_1'>{$this->lang->words['general_tab_title']}</li>
			<li id='tab_2'>{$this->lang->words['permissions']}</li>
		</ul>
	</div>
	<div class='ipsTabBar_content' id='tabstrip_manage_lt_content'>
		<div id='tab_1_content'>
			<div style='float: left; width: 70%'>
				<table class='ipsTable'>
					<tr>
						<th colspan='2'>{$this->lang->words['long_term_details']}</th>
					</tr>
					<tr>
						<td>
							<label>{$this->lang->words['long_term_name']}</label>
						</td>
						<td>
							{$form['lt_title']}
						</td>
					</tr>
					<tr>
						<td>
							<label>{$this->lang->words['long_term_type']}</label>
						</td>
						<td>
							{$form['lt_type']}
						</td>
					</tr>
					<tr>
						<td>
							<label>{$this->lang->words['enabled_title']}?</label>
						</td>
						<td>
							{$form['lt_on']}
						</td>
					</tr>			
					<tr>
						<th colspan='2'>{$this->lang->words['long_term_policies']}</th>
					</tr>
					<tr>
						<td>
							<label>{$this->lang->words['risk_level_title']}</label>
						</td>
						<td>
							{$form['lt_risk_level']}
						</td>
					</tr>
					<tr>
						<td>
							<label>{$this->lang->words['min_need_4_account']}</label>
						</td>
						<td>
							{$form['lt_min']}
						</td>
					</tr>
					<tr>
						<td>
							<label>{$this->lang->words['min_days_to_wait']}</label>
						</td>
						<td>
							{$form['lt_min_days']}
						</td>
					</tr>			
					<tr>
						<td>
							<label>{$this->lang->words['allow_early_cashout']}?</label>
						</td>
						<td>
							{$form['lt_early_cash']}
						</td>
					</tr>
					<tr>
						<td>
							<label>{$this->lang->words['early_cashout_fee']}</label>
						</td>
						<td>
							{$form['lt_early_cash_fee']}%
						</td>
					</tr>
					{$imageInput}
				</table>
			</div>
				
			<div style='float: left; width: 30%;background:#849cb7' class='acp-sidebar'>
				{$this->image_sidebar($long_term['lt_id'], 'lt', $form['lt_image'])}

				<div class='sidebar_box' style='width: 65%'> 
					<strong>{$this->lang->words['investors']}:&nbsp;</strong>{$form['investors']}<br /><br />			
					<strong>{$this->lang->words['total_invested']}:&nbsp;</strong>{$this->settings['eco_general_cursymb']}{$form['total_invested']}<br /><br />
				</div>  
			</div> 
			<div style='clear: both;'></div>
		</div>
		<div id='tab_2_content'>
			<table class='ipsTable'>
				<tr>
					<td>
						<label>{$this->lang->words['use_perm_matrix']}?</label><br />{$this->lang->words['use_perm_matrix_exp']}
					</td>
					<td>
						{$form['lt_use_perms']}
					</td>
				</tr>
				<tr>
					<td colspan='2'>
						{$perm_matrix}
					</td>
				</tr>				
			</table>
		</div>		
	</div>
	<div class='acp-actionbar'>
		<input type='submit' class='button' value='{$button}' />
	</div>
	</form>
</div>

<script type='text/javascript'>
jQ("#tabstrip_manage_lt").ipsTabBar({ tabWrap:
"#tabstrip_manage_lt_content" });
</script>
	
HTML;

//--endhtml--//
return $IPBHTML;
}

/**
 * Overview of Long-Terms
 */
public function ltsOverviewWrapper( $content, $buttons ) {

$IPBHTML = "";
//--starthtml--//

$IPBHTML .= <<<HTML

<div class='section_title'>
	<h2>{$this->lang->words['lt_items']}</h2>
	{$buttons}
</div>

<div class='acp-box'>
 	<h3>{$this->lang->words['lt_items']}</h3>
		<table class='ipsTable' id='reorderable_table'>	
			<tr>
				<th class='col_drag'>&nbsp;</th>
				<th>&nbsp;</th>
				<th>{$this->lang->words['long_term_title']}</th>
				<th>{$this->lang->words['type_title']}</th>
				<th>{$this->lang->words['minimum_time_title']}</th>
				<th>{$this->lang->words['risk_level_title']}</th>
				<th>{$this->lang->words['early_cashout']}</th>
				<th>{$this->lang->words['enabled_title']}?</th>
				<th>&nbsp;</th>
			</tr>
			{$content}
		</table>
	<div class='acp-actionbar'>
		<form id='adminform' method='post' action='{$this->settings['base_url']}{$this->form_code}&amp;do=long_term&amp;type=add' onsubmit='return ACPForums.submitModForm()'>
			<input type='submit' class='button' value='{$this->lang->words['adding_long_term_button']}' />
		</form>
	</div>
</div>

<script type='text/javascript'>
	jQ("#reorderable_table").ipsSortable( 'table', {
		url: "{$this->settings['base_url']}&{$this->form_code_js}&do=reorder&md5check={$this->registry->adminFunctions->getSecurityKey()}".replace( /&amp;/g, '&' )
	});
</script>

HTML;

//--endhtml--//
return $IPBHTML;
}

/**
 * Long-Term row
 */
public function ltRow( $r="" ) {

$r['lt_early_cash']	= $r['lt_early_cash'] ? 'accept.png' : 'cross.png';
$r['lt_on']			= $r['lt_on'] ? 'accept.png' : 'cross.png';
$r['lt_min_days'] 	= $r['lt_min_days'] ? $r['lt_min_days'].' '.$this->lang->words['days'] : "<img src='{$this->settings['skin_acp_url']}/images/icons/cross.png' border='0' alt='-' class='ipd' />";
$r['lt_image_src']	= $r['lt_image']	? "{$this->settings['upload_url']}/ibEconomy_images/{$r['lt_image']}" : "{$this->settings['img_url']}/eco_images/bar_graph.png";

$IPBHTML = "";
//--starthtml--//

$IPBHTML .= <<<HTML
		<tr class='ipsControlRow isDraggable' id='lts_{$r['lt_id']}'>
			<td><span class='draghandle'>&nbsp;</span></td>
			<td style='text-align:center'><img src='{$r['lt_image_src']}' border='0' /></td>
			<td><span class='larger_text'><a style='text-decoration:none;' href='{$this->settings['base_url']}{$this->form_code}&amp;do=long_term&amp;type=edit&amp;lt_id={$r['lt_id']}'>{$r['lt_title']}</a></span></td>
			<td>{$r['lt_type']}</td>
			<td>{$r['lt_min_days']}</td>
			<td>
				<p title='{$this->lang->words['risk_level']}: {$r['lt_risk_level']}%' style='background-color: #fff;border: 1px solid #d5dde5;height:100%;padding:0px;margin:0px;'>
					<span style='background: #243f5c url({style_images_url}/gradient_bg.png) repeat-x left 50%;color: #fff;font-size: 0em;font-weight: bold;text-align: center;text-indent: -2000em;height:10px;display: block;overflow: hidden;width:{$r['lt_risk_level']}%;'>
						<span style='display: none;'>
						</span>
					</span>
				</p>
			</td>
			<td><img src='{$this->settings['skin_acp_url']}/images/icons/{$r['lt_early_cash']}' border='0' alt='-' class='ipd' /></td>
			<td><img src='{$this->settings['skin_acp_url']}/images/icons/{$r['lt_on']}' border='0' alt='-' class='ipd' /></td>  
			<td>
				<ul class='ipsControlStrip'>
					<li class='i_edit' title='{$this->lang->words['editing_long_term_button']}'><a href='{$this->settings['base_url']}{$this->form_code}&amp;do=long_term&amp;type=edit&amp;lt_id={$r['lt_id']}'>{$this->lang->words['editing_long_term_button']}</a></li>
					<li class='i_delete' title='{$this->lang->words['delete_long_term']}'><a href='{$this->settings['base_url']}{$this->form_code}&amp;do=delete&amp;type=long_term&amp;id={$r['lt_id']}' onclick="return confirm('{$this->lang->words['confirm_item_delete']}');">{$this->lang->words['delete_long_term']}</a></li>
				</ul>
			</td>
		</tr>
HTML;

//--endhtml--//
return $IPBHTML;
}

//*************************//
//($%^ Stocks ^%$)//
//*************************//

/**
 * Stock form
 */
public function stockForm( $stock, $perm_matrix, $buttons ) {

$form				= array();
$levels 			= array();
$stock_types 		= array();
$forum_var_types	= array();
$memgrp_var_types	= array();
$groups				= array();
$check_opts			= array();

#buttons and stuff
$title 	= ( $stock['s_id'] ) ? $this->lang->words['editing_stock']." : ".$stock['s_title'] : $this->lang->words['adding_stock'];
$button = ( $stock['s_id'] ) ? $this->lang->words['editing_stock_button'] : $this->lang->words['adding_stock_button'];

#image
$stock['s_image']			= str_replace('-thumb.', '.', $stock['s_image']);
$form['s_image']			= ($stock['s_image']) ? "{$this->settings['upload_url']}/ibEconomy_images/{$stock['s_image']}" : "{$this->settings['public_dir']}/style_images/master/eco_images/nothing.png";
$form['share_holders'] 		= $this->registry->getClass('class_localization')->formatNumber($this->caches['ibEco_stocks'][ $stock['s_id'] ]['share_holders']);
$form['total_share_value'] 	= $this->registry->getClass('class_localization')->formatNumber($this->caches['ibEco_stocks'][ $stock['s_id'] ]['total_share_value']);

#first time?
$imageInput = ( $stock['s_id'] ) ? '' : $this->imageInput();

#Risk Levels Dropdown
for ($i=0; $i<11; $i++)
{
	$risk = $i * 10;
	$levels[] = array($risk, $risk.'%');
}

#Stock types Dropdown
$stock_types[] 		= array('basic', 'Basic');
$stock_types[] 		= array('forum', 'Forum');
$stock_types[] 		= array('member', 'Member');
$stock_types[] 		= array('group', 'Group');

#Forum Variables Dropdown
$forum_var_types[] 	= array('points', $this->settings['eco_general_currency']);
$forum_var_types[] 	= array('posts', 'Posts');
$forum_var_types[] 	= array('registrations', 'Registrations');

#Member and Groups Variables Dropdown
$memgrp_var_types[] = array('points', $this->settings['eco_general_currency']);
$memgrp_var_types[] = array('posts', 'Posts');

#create group dropdown
$groups = $this->registry->ecoclass->getGroups('fp');
		
#Make those form pieces
$form['s_title']			= $this->registry->output->formInput( "s_title", $stock['s_title'] );
$form['s_title_long']		= $this->registry->output->formInput( "s_title_long", $stock['s_title_long'] );
$form['s_type']				= $this->registry->output->formDropdown( "s_type", $stock_types, $stock['s_type'] );
$form['s_risk_level']		= $this->registry->output->formDropdown( "s_risk_level", $levels, $stock['s_risk_level'] );
$form['s_value']			= $this->registry->output->formInput( "s_value", $stock['s_value'] );
$form['s_limit']			= $this->registry->output->formInput( "s_limit", $stock['s_limit'] );
$form['s_can_trade']		= $this->registry->output->formYesNo( "s_can_trade", $stock['s_can_trade'] );
$form['s_on']				= $this->registry->output->formYesNo( "s_on", $stock['s_on'] );
$form['s_forum_var']		= $this->registry->output->formDropdown( "s_forum_var", $forum_var_types, $stock['s_type_var'] );
$form['s_memgrp_var']		= $this->registry->output->formDropdown( "s_memgrp_var", $memgrp_var_types, $stock['s_type_var'] );
$form['s_grpgrp_var']		= $this->registry->output->formDropdown( "s_grpgrp_var", $memgrp_var_types, $stock['s_type_var'] );
$form['s_mem_var_value']	= $this->registry->output->formInput( "s_mem_var_value", $stock['s_type_var_value'] );
$form['s_grps_var_value']	= $this->registry->output->formDropdown( "s_grp_var_value", $groups, $stock['s_type_var_value'] );
$form['s_use_perms']		= $this->registry->output->formYesNo( "s_use_perms", $stock['s_use_perms'] );

$check_opts['basic'] 	= ( $stock['s_type'] == 'basic' ) ? 'checked' : '';
$check_opts['forum'] 	= ( $stock['s_type'] == 'forum' ) ? 'checked' : '';
$check_opts['member'] 	= ( $stock['s_type'] == 'member' ) ? 'checked' : '';
$check_opts['group'] 	= ( $stock['s_type'] == 'group' ) ? 'checked' : '';

$vars_hidden['basic'] 	= ( $stock['s_type'] == 'basic' ) ? 'show' : 'none';
$vars_hidden['forum'] 	= ( $stock['s_type'] == 'forum' ) ? 'show' : 'none';
$vars_hidden['member'] 	= ( $stock['s_type'] == 'member' ) ? 'show' : 'none';
$vars_hidden['group'] 	= ( $stock['s_type'] == 'group' ) ? 'show' : 'none';
	
$IPBHTML = "";
//--starthtml--//

$IPBHTML .= <<<HTML
<script type="text/javascript"> 
<!-- 
function Reveal (it, box) { 
var vis = (box.checked) ? "block" : "none"; 
document.getElementById(it).style.display = vis;
} 

function Hide (it, box) { 
var vis = (box.checked) ? "none" : "none"; 
document.getElementById(it).style.display = vis;
} 

//--> 
</script>

<div class='section_title'>
	<h2>{$title}</h2>
	{$buttons}	
</div>

{$this->includeJS4ImagePopup()}
<form id='adminform' action='{$this->settings['base_url']}{$this->form_code}&amp;do=do_stock' method='post' id='adform' name='adform' onsubmit='return checkform();' enctype='multipart/form-data'>
<input type='hidden' name='s_id' value='{$stock['s_id']}' />
<input type='hidden' name='s_name' value='{$stock['s_title']}' />

<div class='acp-box'>
	<h3>{$this->lang->words['stock_details']}</h3>
	<div class='ipsTabBar with_left with_right' id='tabstrip_manage_stock'>
		<span class='tab_left'>&laquo;</span>
		<span class='tab_right'>&raquo;</span>
		<ul>
			<li id='tab_1'>{$this->lang->words['general_tab_title']}</li>
			<li id='tab_2'>{$this->lang->words['permissions']}</li>
		</ul>
	</div>
	<div class='ipsTabBar_content' id='tabstrip_manage_stock_content'>
		<div id='tab_1_content'>
			<div style='float: left; width: 70%'>
				<table class='ipsTable'>
					<tr>
						<th colspan='2'>{$this->lang->words['stock_details']}</th>
					</tr>
					<tr>
						<td>
							<label>{$this->lang->words['stock_symbol']}</label>
						</td>
						<td>
							{$form['s_title']}
						</td>
					</tr>
					<tr>
						<td>
							<label>{$this->lang->words['stock_name']}</label>
						</td>
						<td>
							{$form['s_title_long']}
						</td>
					</tr>
					<tr>
						<td>
							<label>{$this->lang->words['current_value']}</label>
						</td>
						<td>
							{$form['s_value']}
						</td>
					</tr>			
					<tr>
						<td>
							<label>{$this->lang->words['enabled_title']}?</label>
						</td>
						<td>
							{$form['s_on']}
						</td>
					</tr>
					<tr>
						<th colspan='2'>{$this->lang->words['stock_type']}</th>
					</tr>
					<tr>
						<td>
							<label>{$this->lang->words['type']}</label><br />{$this->lang->words['type_exp']}
						</td>
						<td>
							<span class='yesno_yes '>
								<input type='radio' name='s_type' value='basic' {$check_opts['basic']} onClick="Hide('forum', this);Hide('member', this);Hide('group', this); Reveal('basic', this)"  />
								<label for='s_on_yes' style='float: none !important; font-weight: normal !important; clear: none !important; width: auto !important; padding: 0 !important;'>{$this->lang->words['Random']}</label>
							</span>
							<span class='yesno_no ' style='border-right:none'>
								<input type='radio'  name='s_type' value='forum' {$check_opts['forum']} onClick="Hide('basic', this);Hide('group', this);Hide('member', this); Reveal('forum', this)" />
								<label for='s_on_no'>{$this->lang->words['forum']}</label>
							</span>
							<span class='yesno_yes ' style='border-left:none;'>
								<input type='radio' name='s_type' value='member' {$check_opts['member']} onClick="Hide('basic', this);Hide('group', this);Hide('forum', this); Reveal('member', this)" />
								<label for='s_on_yes' style='float: none !important; font-weight: normal !important; clear: none !important; width: auto !important; padding: 0 !important;'>{$this->lang->words['member']}</label>
							</span>
							<span class='yesno_no '>
								<input type='radio' name='s_type' value='group' {$check_opts['group']} onClick="Hide('basic', this);Hide('forum', this);Hide('member', this); Reveal('group', this)" />
								<label for='s_on_no'>{$this->lang->words['group']}</label>
							</span>
						</td>
					</tr>
					<tr>
						<td>
							<label>{$this->lang->words['stock_var']}</label><br />{$this->lang->words['stock_var_exp']}
						</td>
						<td>
							<div class="row" id="basic" style="display:{$vars_hidden['basic']}">
								<i>{$this->lang->words['nothing_needed_here']}</i>
							</div>
							<div class="row" id="forum" style="display:{$vars_hidden['forum']}">
								{$this->lang->words['what']}?&nbsp;{$form['s_forum_var']}
							</div>
							<div class="row" id="member" style="display:{$vars_hidden['member']}">
								{$this->lang->words['what']}?&nbsp;{$form['s_memgrp_var']}<br /><br />
								{$this->lang->words['who']}?&nbsp;{$form['s_mem_var_value']}
							</div>
							<div class="row" id="group" style="display:{$vars_hidden['group']}">
								{$this->lang->words['what']}?&nbsp;{$form['s_grpgrp_var']}<br /><br />
								{$this->lang->words['who']}?&nbsp;{$form['s_grps_var_value']}
							</div>
						</td>
					</tr>			
					<tr>
						<th colspan='2'>{$this->lang->words['stock_policies']}</th>
					</tr>			
					<tr>
						<td>
							<label>{$this->lang->words['risk_level_title']}</label>
						</td>
						<td>
							{$form['s_risk_level']}
						</td>
					</tr>
					<tr>
						<td>
							<label>{$this->lang->words['limit_per_user']}</label>
						</td>
						<td>
							{$form['s_limit']}
						</td>
					</tr>
					<tr>
						<td>
							<label>{$this->lang->words['can_trade']}</label>
						</td>
						<td>
							{$form['s_can_trade']}
						</td>
					</tr>
					{$imageInput}
				</table>
			</div>
				
			<div style='float: left; width: 30%;background:#849cb7' class='acp-sidebar'>
				{$this->image_sidebar($stock['s_id'], 'stock', $form['s_image'])}

				<div class='sidebar_box' style='width: 65%'> 
					<strong>{$this->lang->words['share_holders']}:&nbsp;</strong>{$form['share_holders']}<br /><br /> 
					<strong>{$this->lang->words['total_shares_owned']}:&nbsp;</strong>{$form['total_share_value']}<br /><br /> 				
				</div> 
			</div> 
			<div style='clear: both;'></div>
		</div>
		<div id='tab_2_content'>
			<table class='ipsTable'>
				<tr>
					<td>
						<label>{$this->lang->words['use_perm_matrix']}?</label><br />{$this->lang->words['use_perm_matrix_exp']}
					</td>
					<td>
						{$form['s_use_perms']}
					</td>
				</tr>
				<tr>
					<td colspan='2'>
						{$perm_matrix}
					</td>
				</tr>				
			</table>
		</div>		
	</div>
	<div class='acp-actionbar'>
		<input type='submit' class='button' value='{$button}' />
	</div>
	</form>
</div>

<script type='text/javascript'>
jQ("#tabstrip_manage_stock").ipsTabBar({ tabWrap:
"#tabstrip_manage_stock_content" });
</script>
	
HTML;

//--endhtml--//
return $IPBHTML;
}

/**
 * Overview of Stocks
 */
public function stocksOverviewWrapper( $content, $buttons ) {

$IPBHTML = "";
//--starthtml--//

$IPBHTML .= <<<HTML

<div class='section_title'>
	<h2>{$this->lang->words['stock_items']}</h2>
	{$buttons}
</div>

<div class='acp-box'>
 	<h3>{$this->lang->words['stocks_page_title']}</h3>
		<table class='ipsTable' id='reorderable_table'>	
			<tr>
				<th class='col_drag'>&nbsp;</th>
				<th>&nbsp;</th>
				<th>{$this->lang->words['stock_title']}</th>
				<th>{$this->lang->words['type_title']}</th>
				<th>{$this->lang->words['value_title']}</td>
				<th>{$this->lang->words['risk_level_title']}</th>
				<th>{$this->lang->words['limit_title']}</th>
				<th>{$this->lang->words['enabled_title']}?</th>
				<th>&nbsp;</th>
			</tr>
			{$content}
		</table>
	<div class='acp-actionbar'>
		<form id='adminform' method='post' action='{$this->settings['base_url']}{$this->form_code}&amp;do=stock&amp;type=add' onsubmit='return ACPForums.submitModForm()'>
			<input type='submit' class='button' value='{$this->lang->words['add_stock_button']}' />
		</form>
	</div>
</div>

<script type='text/javascript'>
	jQ("#reorderable_table").ipsSortable( 'table', {
		url: "{$this->settings['base_url']}&{$this->form_code_js}&do=reorder&md5check={$this->registry->adminFunctions->getSecurityKey()}".replace( /&amp;/g, '&' )
	});
</script>

HTML;

//--endhtml--//
return $IPBHTML;
}

/**
 * Stock row
 */
public function stockRow( $r="" ) {

#init
$r['s_limit']	= $r['s_limit'] ? $r['s_limit'] : $this->lang->words['none'];
$r['s_on']		= $r['s_on'] ? 'accept.png' : 'cross.png';
$r['s_type'] 	= ucfirst($r['s_type']);

#image
$r['s_image_src']	= $r['s_image']	? "{$this->settings['upload_url']}/ibEconomy_images/{$r['s_image']}" : "{$this->settings['img_url']}/eco_images/chart_curve.png";

$IPBHTML = "";
//--starthtml--//

$IPBHTML .= <<<HTML
		<tr class='ipsControlRow isDraggable' id='stocks_{$r['s_id']}'>
			<td><span class='draghandle'>&nbsp;</span></td>
			<td style='text-align:center'><img src='{$r['s_image_src']}' border='0' /></td>
			<td><span class='larger_text'><a style='text-decoration:none;' href='{$this->settings['base_url']}{$this->form_code}&amp;do=stock&amp;type=edit&amp;s_id={$r['s_id']}'>{$r['s_title']}</a></span> - <a style='text-decoration:none;' href='{$this->settings['base_url']}{$this->form_code}&amp;do=stock&amp;type=edit&amp;s_id={$r['s_id']}'>{$r['s_title_long']}</a></span></td>
			<td>{$r['s_type']}</td>
			<td>{$this->settings['eco_general_cursymb']}{$r['s_value']}</td>
			<td>
				<p title='{$this->lang->words['risk_level']}: {$r['s_risk_level']}%' style='background-color: #fff;border: 1px solid #d5dde5;height:100%;padding:0px;margin:0px;'>
					<span style='background: #243f5c url({style_images_url}/gradient_bg.png) repeat-x left 50%;color: #fff;font-size: 0em;font-weight: bold;text-align: center;text-indent: -2000em;height:10px;display: block;overflow: hidden;width:{$r['s_risk_level']}%;'>
						<span style='display: none;'>
						</span>
					</span>
				</p>
			</td>
			<td>{$r['s_limit']}</td>
			<td><img src='{$this->settings['skin_acp_url']}/images/icons/{$r['s_on']}' border='0' alt='-' class='ipd' /></td>  
			<td>
				<ul class='ipsControlStrip'>
					<li class='i_edit' title='{$this->lang->words['editing_stock_button']}'><a href='{$this->settings['base_url']}{$this->form_code}&amp;do=stock&amp;type=edit&amp;s_id={$r['s_id']}'>{$this->lang->words['editing_stock_button']}</a></li>
					<li class='i_delete' title='{$this->lang->words['delete_stock']}'><a href='{$this->settings['base_url']}{$this->form_code}&amp;do=delete&amp;type=stock&amp;id={$r['s_id']}' onclick="return confirm('{$this->lang->words['confirm_item_delete']}');">{$this->lang->words['delete_stock']}</a></li>
				</ul>
			</td>
		</tr>
HTML;

//--endhtml--//
return $IPBHTML;
}

//*************************//
//($%^ Credid Cards ^%$)//
//*************************//

/**
 * Credit card form
 */
public function ccForm( $cc, $perm_matrix, $buttons ) {

$form	= array();

#buttons and stuff
$title 	= ( $cc ) ? $this->lang->words['editing_cc']." : ".$cc['cc_title'] : $this->lang->words['adding_cc'];
$button = ( $cc ) ? $this->lang->words['edit_cc_button'] : $this->lang->words['add_cc_button_title'];

#first time?
$imageInput = ( $cc['cc_id'] ) ? '' : $this->imageInput();

#image
$cc['cc_image']			= str_replace('-thumb.', '.', $cc['cc_image']);
$form['cc_image']		= ($cc['cc_image']) ? "{$this->settings['upload_url']}/ibEconomy_images/{$cc['cc_image']}" : "{$this->settings['public_dir']}/style_images/master/eco_images/nothing.png";
$form['card_holders'] 	= $this->registry->getClass('class_localization')->formatNumber($this->caches['ibEco_ccs'][ $cc['cc_id'] ]['card_holders']);
$form['funds'] 			= $this->registry->getClass('class_localization')->formatNumber($this->caches['ibEco_ccs'][ $cc['cc_id'] ]['funds'], $this->registry->ecoclass->decimal);

#General Tab:
$form['cc_title']		= $this->registry->output->formInput( "cc_title", $cc['cc_title'] );
$form['cc_apr']			= $this->registry->output->formSimpleInput( "cc_apr", $cc['cc_apr'] );
$form['cc_cost']		= $this->registry->output->formSimpleInput( "cc_cost", $cc['cc_cost'] );
$form['cc_max']			= $this->registry->output->formSimpleInput( "cc_max", $cc['cc_max'] );
$form['cc_apr_max']		= $this->registry->output->formSimpleInput( "cc_apr_max", $cc['cc_apr_max'] );
$form['cc_apr_min']		= $this->registry->output->formSimpleInput( "cc_apr_min", $cc['cc_apr_min'] );
$form['cc_csh_adv']		= $this->registry->output->formYesNo( "cc_csh_adv", $cc['cc_csh_adv'] );
$form['cc_csh_adv_fee']	= $this->registry->output->formSimpleInput( "cc_csh_adv_fee", $cc['cc_csh_adv_fee'] );
$form['cc_allow_od']	= $this->registry->output->formYesNo( "cc_allow_od", $cc['cc_allow_od'] );
$form['cc_od_pnlty']	= $this->registry->output->formSimpleInput( "cc_od_pnlty", $cc['cc_od_pnlty'] );
$form['cc_max_od']		= $this->registry->output->formSimpleInput( "cc_max_od", $cc['cc_max_od'] );
$form['cc_no_pay_chrg']	= $this->registry->output->formSimpleInput( "cc_no_pay_chrg", $cc['cc_no_pay_chrg'] );
$form['cc_on']			= $this->registry->output->formYesNo( "cc_on", $cc['cc_on'] );
$form['cc_use_perms']	= $this->registry->output->formYesNo( "cc_use_perms", $cc['cc_use_perms'] );

#Promo Tab:
$form['cc_bal_trnsfr']		= $this->registry->output->formYesNo( "cc_bal_trnsfr", $cc['cc_bal_trnsfr'] );
$form['cc_bal_trnsfr_apr']	= $this->registry->output->formSimpleInput( "cc_bal_trnsfr_apr", $cc['cc_bal_trnsfr_apr'] );
$form['cc_bal_trnsfr_end']	= $this->registry->output->formSimpleInput( "cc_bal_trnsfr_end", $cc['cc_bal_trnsfr_end'] );
$form['cc_bal_trnsfr_fee']	= $this->registry->output->formSimpleInput( "cc_bal_trnsfr_fee", $cc['cc_bal_trnsfr_fee'] );
	
$IPBHTML = "";
//--starthtml--//

$IPBHTML .= <<<HTML
<div class='section_title'>
	<h2>{$title}</h2>
	{$buttons}	
</div>

{$this->includeJS4ImagePopup()}
<form id='adminform' action='{$this->settings['base_url']}{$this->form_code}&amp;do=do_cc' method='post' id='adform' name='adform' onsubmit='return checkform();' enctype='multipart/form-data'>
<input type='hidden' name='cc_id' value='{$cc['cc_id']}' />
<input type='hidden' name='cc_name' value='{$cc['cc_title']}' />

<div class='acp-box'>
	<h3>{$this->lang->words['cc_details']}</h3>
	<div class='ipsTabBar with_left with_right' id='tabstrip_manage_cc'>
		<span class='tab_left'>&laquo;</span>
		<span class='tab_right'>&raquo;</span>
		<ul>
			<li id='tab_1'>{$this->lang->words['general_tab_title']}</li>
			<li id='tab_2'>{$this->lang->words['promo_tab_title']}</li>
			<li id='tab_3'>{$this->lang->words['permissions']}</li>
		</ul>
	</div>
	<div class='ipsTabBar_content' id='tabstrip_manage_cc_content'>
		<div id='tab_1_content'>
			<div style='float: left; width: 70%'>
				<table class='ipsTable'>
					<tr>
						<th colspan='2'>{$this->lang->words['cc_settings']}</th>
					</tr>
					<tr>
						<td>
							<label>{$this->lang->words['cc_name']}?</label>
						</td>
						<td>
							{$form['cc_title']}
						</td>
					</tr>
					<tr>
						<td>
							<label>{$this->lang->words['enabled_title']}?</label>
						</td>
						<td>
							{$form['cc_on']}
						</td>
					</tr>
					<tr>
						<td>
							<label>{$this->lang->words['cost_new_card']}</label>
						</td>
						<td>
							{$form['cc_cost']} {$this->settings['eco_general_currency']}
						</td>
					</tr>
					<tr>
						<td>
							<label>{$this->lang->words['max_debt_card']}</label>
						</td>
						<td>
							{$form['cc_max']} {$this->settings['eco_general_currency']}
						</td>
					</tr>
					<tr>
						<td>
							<label>{$this->lang->words['fee_for_late_payment']}</label>
						</td>
						<td>
							{$form['cc_no_pay_chrg']} {$this->settings['eco_general_currency']}
						</td>
					</tr>						
					<tr>
						<th colspan='2'>{$this->lang->words['apr_settings']}</th>
					</tr>			
					<tr>
						<td>
							<label>{$this->lang->words['base_apr']}</label>
						</td>
						<td>
							{$form['cc_apr']}%
						</td>
					</tr>
					<tr>
						<td>
							<label>{$this->lang->words['min_apr']}</label>
						</td>
						<td>
							{$form['cc_apr_min']}%
						</td>
					</tr>
					<tr>
						<td>
							<label>{$this->lang->words['max_apr']}</label>
						</td>
						<td>
							{$form['cc_apr_max']}%
						</td>
					</tr>
					<tr>
						<th colspan='2'>{$this->lang->words['cash_advance_policies']}</th>
					</tr>			
					<tr>
						<td>
							<label>{$this->lang->words['allow_cash_advance']}?</label>
						</td>
						<td>
							{$form['cc_csh_adv']}
						</td>
					</tr>
					<tr>
						<td>
							<label>{$this->lang->words['cash_advance_fee']}</label>
						</td>
						<td>
							{$form['cc_csh_adv_fee']}%
						</td>
					</tr>
					<tr>
						<th colspan='2'>{$this->lang->words['overdraft_policies']}</th>
					</tr>			
					<tr>
						<td>
							<label>{$this->lang->words['allow_overdrafts']}?</label>
						</td>
						<td>
							{$form['cc_allow_od']}
						</td>
					</tr>
					<tr>
						<td>
							<label>{$this->lang->words['overdraft_penalty']}?</label>
						</td>
						<td>
							{$form['cc_od_pnlty']}  {$this->settings['eco_general_currency']}
						</td>
					</tr>
					<tr>
						<td>
							<label>{$this->lang->words['max_overdraft']}?</label>
						</td>
						<td>
							{$form['cc_max_od']}  {$this->settings['eco_general_currency']}
						</td>
					</tr>
					{$imageInput}
				</table>
			</div>
				
			<div style='float: left; width: 30%;background:#849cb7' class='acp-sidebar'>
				{$this->image_sidebar($cc['cc_id'], 'cc', $form['cc_image'])}

				<div class='sidebar_box' style='width: 65%'> 
					<strong>{$this->lang->words['card_holders']}:&nbsp;</strong>{$form['card_holders']}<br /><br /> 
					<strong>{$this->lang->words['total_debt']}:&nbsp;</strong>{$this->settings['eco_general_cursymb']}{$form['funds']}<br /><br /> 
				</div> 
			</div> 
			<div style='clear: both;'></div>
		</div>
		<div id='tab_2_content'>
			<table class='ipsTable'>
				<tr>
					<th colspan='2'>{$this->lang->words['promo_settings']}</th>
				</tr>
				<tr>
					<td>
						<label>{$this->lang->words['promo_on']}?</label>
					</td>
					<td>
						{$form['cc_bal_trnsfr']}
					</td>
				</tr>
				<tr>
					<td>
						<label>{$this->lang->words['promotional_apr']}</label>
					</td>
					<td>
						{$form['cc_bal_trnsfr_apr']}%
					</td>
				</tr>
				<tr>
					<td>
						<label>{$this->lang->words['promo_end_date']}</label> <span style='font-decoration:none'>{$this->lang->words['promo_end_date_exp']}</span>
					</td>
					<td>
						{$form['cc_bal_trnsfr_end']}
					</td>
				</tr>
				<tr>
					<td>
						<label>{$this->lang->words['bal_transfer_fee']}</label>
					</td>
					<td>
						{$form['cc_bal_trnsfr_fee']}%
					</td>
				</tr>				
				
			</table>
		</div>
		<div id='tab_3_content'>
			<table class='ipsTable'>
				<tr>
					<td>
						<label>{$this->lang->words['use_perm_matrix']}?</label><br />{$this->lang->words['use_perm_matrix_exp']}
					</td>
					<td>
						{$form['cc_use_perms']}
					</td>
				</tr>
				<tr>
					<td colspan='2'>
						{$perm_matrix}
					</td>
				</tr>				
			</table>
		</div>		
	</div>
	<div class='acp-actionbar'>
		<input type='submit' class='button' value='{$button}' />
	</div>
	</form>
</div>

<script type='text/javascript'>
jQ("#tabstrip_manage_cc").ipsTabBar({ tabWrap:
"#tabstrip_manage_cc_content" });
</script>
	
HTML;

//--endhtml--//
return $IPBHTML;
}

/**
 * Overview of Credit Cards
 */
public function ccsOverviewWrapper( $content, $buttons ) {

$IPBHTML = "";
//--starthtml--//

$IPBHTML .= <<<HTML

<div class='section_title'>
	<h2>{$this->lang->words['cc_items']}</h2>
	{$buttons}
</div>

<div class='acp-box'>
 	<h3>{$this->lang->words['cc_items']}</h3>
		<table class='ipsTable' id='reorderable_table'>	
			<tr>
				<th class='col_drag'>&nbsp;</th>
				<th>&nbsp;</th>
				<th>{$this->lang->words['cc_title']}</th>
				<th>{$this->lang->words['apr_title']}</th>
				<th>{$this->lang->words['cash_advance_title']}</th>
				<th>{$this->lang->words['cc_bal_trnsfr_title']}</th>
				<th>{$this->lang->words['cc_allow_od_title']}</th>
				<th>{$this->lang->words['enabled_title']}?</th>
				<th>&nbsp;</th>
			</tr>			
			{$content}
		</table>
	<div class='acp-actionbar'>
		<form id='adminform' method='post' action='{$this->settings['base_url']}{$this->form_code}&amp;do=cc&amp;type=add' onsubmit='return ACPForums.submitModForm()'>
			<input type='submit' class='button' value='{$this->lang->words['add_new_cc']}' />
		</form>
	</div>
</div>

<script type='text/javascript'>
	jQ("#reorderable_table").ipsSortable( 'table', {
		url: "{$this->settings['base_url']}&{$this->form_code_js}&do=reorder&md5check={$this->registry->adminFunctions->getSecurityKey()}".replace( /&amp;/g, '&' )
	});
</script>

HTML;

//--endhtml--//
return $IPBHTML;
}

/**
 * Credit Card row
 */
public function ccRow( $r="" ) {

$r['cc_csh_adv']	= $r['cc_csh_adv']    	? 'accept.png' : 'cross.png';
$r['cc_bal_trnsfr']	= $r['cc_bal_trnsfr']   ? 'accept.png' : 'cross.png';
$r['cc_allow_od']	= $r['cc_allow_od']    	? 'accept.png' : 'cross.png';
$apr 				= ( $r['cc_apr'] ) 		? $r['cc_apr'].'%' : $this->lang->words['free'];
$r['cc_on']			= $r['cc_on'] 			? 'accept.png' : 'cross.png';

#image
$r['cc_image_src']	= $r['cc_image'] ? "{$this->settings['upload_url']}/ibEconomy_images/{$r['cc_image']}" : "{$this->settings['img_url']}/eco_images/creditcards.png";

$IPBHTML = "";
//--starthtml--//

$IPBHTML .= <<<HTML
		<tr class='ipsControlRow isDraggable' id='ccs_{$r['cc_id']}'>
			<td><span class='draghandle'>&nbsp;</span></td>
			<td style='text-align:center'><img src='{$r['cc_image_src']}' border='0' /></td>
			<td><span class='larger_text'><a style='text-decoration:none;' href='{$this->settings['base_url']}{$this->form_code}&amp;do=cc&amp;type=edit&amp;cc_id={$r['cc_id']}'>{$r['cc_title']}</a></span></td>
			<td>{$apr}</td>
			<td><img src='{$this->settings['skin_acp_url']}/images/icons/{$r['cc_csh_adv']}' border='0' alt='-' class='ipd' /></td>
			<td><img src='{$this->settings['skin_acp_url']}/images/icons/{$r['cc_bal_trnsfr']}' border='0' alt='-' class='ipd' /></td>
			<td><img src='{$this->settings['skin_acp_url']}/images/icons/{$r['cc_allow_od']}' border='0' alt='-' class='ipd' /></td> 
			<td><img src='{$this->settings['skin_acp_url']}/images/icons/{$r['cc_on']}' border='0' alt='-' class='ipd' /></td>
			<td>
				<ul class='ipsControlStrip'>
					<li class='i_edit' title='{$this->lang->words['edit_cc_button']}'><a href='{$this->settings['base_url']}{$this->form_code}&amp;do=cc&amp;type=edit&amp;cc_id={$r['cc_id']}'>{$this->lang->words['edit_cc_button']}</a></li>
					<li class='i_delete' title='{$this->lang->words['delete_cc']}'><a href='{$this->settings['base_url']}{$this->form_code}&amp;do=delete&amp;type=credit_card&amp;id={$r['cc_id']}' onclick="return confirm('{$this->lang->words['confirm_item_delete']}');">{$this->lang->words['delete_cc']}</a></li>
				</ul>
			</td>
		</tr>
HTML;

//--endhtml--//
return $IPBHTML;
}

//*************************//
//($%^ BANKS ^%$)//
//*************************//

/**
 * Bank form
 */
public function bankForm( $bank, $perm_matrix, $buttons ) {

$form	= array();

#buttons and stuff
$title 	= ( $bank ) ? $this->lang->words['editing_bank']." : ".$bank['b_title'] : $this->lang->words['adding_bank'];
$button = ( $bank ) ? $this->lang->words['edit_bank_button'] : $this->lang->words['add_bank_button_title'];

#first time?
$imageInput = ( $bank['b_id'] ) ? '' : $this->imageInput();

#image
$bank['b_image']			= str_replace('-thumb.', '.', $bank['b_image']);
$form['b_image']			= ($bank['b_image']) ? "{$this->settings['upload_url']}/ibEconomy_images/{$bank['b_image']}" : "{$this->settings['public_dir']}/style_images/master/eco_images/nothing.png";
$form['total'] 				= $this->registry->getClass('class_localization')->formatNumber($this->caches['ibEco_banks'][ $bank['b_id'] ]['total_accts']);
$form['funds'] 				= $this->registry->getClass('class_localization')->formatNumber($this->caches['ibEco_banks'][ $bank['b_id'] ]['total_funds'], $this->registry->ecoclass->decimal);

#General Tab:
$form['b_title']			= $this->registry->output->formInput( "b_title", $bank['b_title'] );
$form['b_on']				= $this->registry->output->formYesNo( "b_on", $bank['b_on'] );

#Checking Tab:
$form['b_checking_on']		= $this->registry->output->formYesNo( "b_checking_on", $bank['b_checking_on'] );
$form['b_c_acnt_cost']		= $this->registry->output->formInput( "b_c_acnt_cost", $bank['b_c_acnt_cost'] );
$form['b_c_dep_fee']		= $this->registry->output->formSimpleInput( "b_c_dep_fee", $bank['b_c_dep_fee'] );
$form['b_c_wthd_fee']		= $this->registry->output->formSimpleInput( "b_c_wthd_fee", $bank['b_c_wthd_fee'] );

#Savings Tab:
$form['b_savings_on']		= $this->registry->output->formYesNo( "b_savings_on", $bank['b_savings_on'] );
$form['b_s_acnt_cost']		= $this->registry->output->formInput( "b_s_acnt_cost", $bank['b_s_acnt_cost'] );
$form['b_s_dep_fee']		= $this->registry->output->formSimpleInput( "b_s_dep_fee", $bank['b_s_dep_fee'] );
$form['b_s_wthd_fee']		= $this->registry->output->formSimpleInput( "b_s_wthd_fee", $bank['b_s_wthd_fee'] );
$form['b_sav_interest']		= $this->registry->output->formSimpleInput( "b_sav_interest", $bank['b_sav_interest'] );

#Loans Tab:
$form['b_loans_on']			= $this->registry->output->formYesNo( "b_loans_on", $bank['b_loans_on'] );
$form['b_loans_max']		= $this->registry->output->formInput( "b_loans_max", $bank['b_loans_max'] );
$form['b_loans_app_fee']	= $this->registry->output->formSimpleInput( "b_loans_app_fee", $bank['b_loans_app_fee'] );
$form['b_loans_fee']		= $this->registry->output->formSimpleInput( "b_loans_fee", $bank['b_loans_fee'] );
$form['b_loans_days']		= $this->registry->output->formSimpleInput( "b_loans_days", $bank['b_loans_days'] );
$form['b_loans_pen']		= $this->registry->output->formSimpleInput( "b_loans_pen", $bank['b_loans_pen'] );

#Perms Tab:
$form['b_use_perms']		= $this->registry->output->formYesNo( "b_use_perms", $bank['b_use_perms'] );

$this->lang->words['loan_app_fee_exp'] = str_replace('<%POINTS_NAME%>', $this->settings['eco_general_currency'], $this->lang->words['loan_app_fee_exp']);
	
$IPBHTML = "";
//--starthtml--//

$IPBHTML .= <<<HTML
<div class='section_title'>
	<h2>{$title}</h2>
	{$buttons}	
</div>

{$this->includeJS4ImagePopup()}
<form id='adminform' action='{$this->settings['base_url']}{$this->form_code}&amp;do=do_bank' method='post' id='adform' name='adform' onsubmit='return checkform();'>
<input type='hidden' name='bank_id' value='{$bank['b_id']}' />
<input type='hidden' name='bank_name' value='{$bank['b_title']}' />

<div class='acp-box'>
	<h3>{$this->lang->words['bank_details']}</h3>
	
	<div class='ipsTabBar with_left with_right' id='tabstrip_manage_bank'>
		<span class='tab_left'>&laquo;</span>
		<span class='tab_right'>&raquo;</span>
		<ul>
			<li id='tab_1'>{$this->lang->words['general_tab_title']}</li>
			<li id='tab_2'>{$this->lang->words['checking_tab_title']}</li>
			<li id='tab_3'>{$this->lang->words['savings_tab_title']}</li>
			<li id='tab_4'>{$this->lang->words['bank_loan_tab_title']}</li>
			<li id='tab_5'>{$this->lang->words['permissions']}</li>
		</ul>
	</div>
	<div class='ipsTabBar_content' id='tabstrip_manage_bank_content'>
		<div id='tab_1_content'>
			<div style='float: left; width: 70%'>
				<table class='ipsTable double_pad'>
					<tr>
						<th colspan='2'>{$this->lang->words['bank_settings']}</th>
					</tr>
					<tr>
						<td>
							<label>{$this->lang->words['institution_name']}?</label>
						</td>
						<td>
							{$form['b_title']}
						</td>
					</tr>
					<tr>
						<td>
							<label>{$this->lang->words['enabled_title']}?</label>
						</td>
						<td>
							{$form['b_on']}
						</td>
					</tr>		
					{$imageInput}
				</table>
			</div>
				
			<div style='float: right; width: 30%;background:#849cb7' class='acp-sidebar'>
				{$this->image_sidebar($bank['b_id'], 'bank', $form['b_image'])}

				<div class='sidebar_box' style='width: 65%'> 
					<strong>{$this->lang->words['total_accounts_title']}:&nbsp;</strong>{$form['total']}<br /><br /> 
					<strong>{$this->lang->words['total_funds_title']}:&nbsp;</strong>{$this->settings['eco_general_cursymb']}{$form['funds']}<br /><br />
				</div> 
			</div> 
			<div style='clear: both;'></div>
		</div>
		<div id='tab_2_content'>
			<table class='ipsTable double_pad'>
				<tr>
					<th colspan='2'>{$this->lang->words['checking_settings']}</th>
				</tr>
				<tr>
					<td>
						<label>{$this->lang->words['checking_on']}?</label>
					</td>
					<td>
						{$form['b_checking_on']}
					</td>
				</tr>
				<tr>
					<td>
						<label>{$this->lang->words['cost_for_new_acnt']}</label>
					</td>
					<td>
						{$form['b_c_acnt_cost']}
					</td>
				</tr>	
				<tr>
					<td>
						<label>{$this->lang->words['deposit_fee']}</label>
					</td>
					<td>
						{$form['b_c_dep_fee']}%
					</td>
				</tr>
				<tr>
					<td>
						<label>{$this->lang->words['withdraw_fee']}</label>
					</td>
					<td>
						{$form['b_c_wthd_fee']}%
					</td>
				</tr>				
			</table>
		</div>
		<div id='tab_3_content'>
			<table class='ipsTable double_pad'>
				<tr>
					<th colspan='2'>{$this->lang->words['savings_settings']}</th>
				</tr>
				<tr>
					<td>
						<label>{$this->lang->words['savings_on']}</label>
					</td>
					<td>
						{$form['b_savings_on']}
					</td>
				</tr>
				<tr>
					<td>
						<label>{$this->lang->words['cost_for_new_acnt']}</label>
					</td>
					<td>
						{$form['b_s_acnt_cost']}
					</td>
				</tr>
				<tr>
					<td>
						<label>{$this->lang->words['deposit_fee']}</label>
					</td>
					<td>
						{$form['b_s_dep_fee']}%
					</td>
				</tr>
				<tr>
					<td>
						<label>{$this->lang->words['withdraw_fee']}</label>
					</td>
					<td>
						{$form['b_s_wthd_fee']}%
					</td>
				</tr>
				<tr>
					<td>
						<label>{$this->lang->words['interest_rate']}</label>
					</td>
					<td>
						{$form['b_sav_interest']}%
					</td>
				</tr>			
			</table>
		</div>
		<div id='tab_4_content'>
			<table class='ipsTable double_pad'>
				<tr>
					<th colspan='2'>{$this->lang->words['loan_settings']}</th>
				</tr>
				<tr>
					<td>
						<label>{$this->lang->words['loans_on']}</label>
					</td>
					<td>
						{$form['b_loans_on']}
					</td>
				</tr>
				<tr>
					<td>
						<label>{$this->lang->words['max_loan_amount']}</label>
					</td>
					<td>
						{$form['b_loans_max']}
					</td>
				</tr>
				<tr>
					<td>
						<label>{$this->lang->words['loan_app_fee']}</label><br />{$this->lang->words['loan_app_fee_exp']}
					</td>
					<td>
						{$form['b_loans_app_fee']}
					</td>
				</tr>			
				<tr>
					<td>
						<label>{$this->lang->words['loan_fee']}</label><br />{$this->lang->words['loan_fee_exp']}
					</td>
					<td>
						{$form['b_loans_fee']}%
					</td>
				</tr>
				<tr>
					<td>
						<label>{$this->lang->words['repayment_time']}</label><br />{$this->lang->words['in_days']}
					</td>
					<td>
						{$form['b_loans_days']}
					</td>
				</tr>
				<tr>
					<td>
						<label>{$this->lang->words['overdue_penalty']}</label><br />{$this->lang->words['overdue_penaltye_exp']}
					</td>
					<td>
						{$form['b_loans_pen']}%
					</td>
				</tr>							
			</table>
		</div>
		<div id='tab_5_content'>
			<table class='ipsTable double_pad'>
				<tr>
					<td>
						<label>{$this->lang->words['use_perm_matrix']}?</label><br />{$this->lang->words['use_perm_matrix_exp']}
					</td>
					<td>
						{$form['b_use_perms']}
					</td>
				</tr>
				<tr>
					<td colspan='2'>
						{$perm_matrix}
					</td>
				</tr>				
			</table>
		</div>		
	</div>
	<div class='acp-actionbar'>
		<input type='submit' class='button' value='{$button}' />
	</div>
	</form>
</div>

<script type='text/javascript'>
jQ("#tabstrip_manage_bank").ipsTabBar({ tabWrap:
"#tabstrip_manage_bank_content" });
</script>
	
HTML;

//--endhtml--//
return $IPBHTML;
}

/**
 * Overview of banks
 */
public function banksOverviewWrapper( $content, $buttons ) {

$IPBHTML = "";
//--starthtml--//

$IPBHTML .= <<<HTML

<div class='section_title'>
	<h2>{$this->lang->words['bank_items']}</h2>
	{$buttons}
</div>

<div class='acp-box'>
 	<h3>{$this->lang->words['banks_page_title']}</h3>
		<table class='ipsTable' id='reorderable_table'>	
			<tr>
				<th class='col_drag'>&nbsp;</th>
				<th>&nbsp;</th>
				<th>{$this->lang->words['bank_title']}</th>
				<th>{$this->lang->words['c_price_title']}</th>
				<th>{$this->lang->words['s_price_title']}</th>
				<th>{$this->lang->words['total_accounts_title']}</th>
				<th>{$this->lang->words['total_funds_title']}</th>
				<th>{$this->lang->words['enabled_title']}?</th>
				<th>&nbsp;</th>
			</tr>
			{$content}
		</table>
	<div class='acp-actionbar'>
		<form id='adminform' method='post' action='{$this->settings['base_url']}{$this->form_code}&amp;do=bank&amp;type=add' onsubmit='return ACPForums.submitModForm()'>
			<input type='submit' class='button' value='{$this->lang->words['add_new_bank']}' />
		</form>
	</div>
</div>

<script type='text/javascript'>
	jQ("#reorderable_table").ipsSortable( 'table', {
		url: "{$this->settings['base_url']}&{$this->form_code_js}&do=reorder&md5check={$this->registry->adminFunctions->getSecurityKey()}".replace( /&amp;/g, '&' )
	});
</script>

<br />


HTML;

//--endhtml--//
return $IPBHTML;
}

/**
 * Bank row
 */
public function bankRow( $r="" ) {

#costs
$c_cost 			= ( $r['b_c_acnt_cost'] ) ? $r['b_c_acnt_cost'].' '.$this->settings['eco_general_currency'] : $this->lang->words['free'];
$s_cost 			= ( $r['b_s_acnt_cost'] ) ? $r['b_s_acnt_cost'].' '.$this->settings['eco_general_currency'] : $this->lang->words['free'];
$c_cost 			= ( $r['b_checking_on'] ) ? $c_cost : "<img title='{$this->lang->words['off']}' src='{$this->settings['skin_acp_url']}/images/icons/cross.png' border='0' alt='-' class='ipd' />";
$s_cost 			= ( $r['b_savings_on'] ) ? $s_cost : "<img title='{$this->lang->words['off']}' src='{$this->settings['skin_acp_url']}/images/icons/cross.png' border='0' alt='-' class='ipd' />";

#image
$r['b_image_src']	= $r['b_image']	? "{$this->settings['upload_url']}/ibEconomy_images/{$r['b_image']}" : "{$this->settings['img_url']}/eco_images/building_key.png";

#on
$r['b_on']			= $r['b_on'] ? 'accept.png' : 'cross.png';
$r['funds']			= $this->registry->getClass('class_localization')->formatNumber( $r['funds'], $this->registry->ecoclass->decimal );

$IPBHTML = "";
//--starthtml--//

$IPBHTML .= <<<HTML
		<tr class='ipsControlRow isDraggable' id='banks_{$r['b_id']}'>
			<td><span class='draghandle'>&nbsp;</span></td>
			<td><img src='{$r['b_image_src']}' border='0' /></td>
			<td><span class='larger_text'><a style='text-decoration:none;' href='{$this->settings['base_url']}{$this->form_code}&amp;do=bank&amp;type=edit&amp;bank_id={$r['b_id']}'>{$r['b_title']}</a></span></td>
			<td>{$c_cost}</td>
			<td>{$s_cost}</td>
			<td>{$r['total']}</td>
			<td>{$r['funds']} {$this->settings['eco_general_currency']}</td>
			<td><img src='{$this->settings['skin_acp_url']}/images/icons/{$r['b_on']}' border='0' alt='-' class='ipd' /></td>  
			<td>
				<ul class='ipsControlStrip'>
					<li class='i_edit' title='{$this->lang->words['edit_bank']}'><a href='{$this->settings['base_url']}{$this->form_code}&amp;do=bank&amp;type=edit&amp;bank_id={$r['b_id']}'>{$this->lang->words['edit_bank']}</a></li>
					<li class='i_delete' title='{$this->lang->words['delete_bank']}'><a href='{$this->settings['base_url']}{$this->form_code}&amp;do=delete&amp;type=bank&amp;id={$r['b_id']}' onclick="return confirm('{$this->lang->words['confirm_item_delete']}');">{$this->lang->words['delete_bank']}</a></li>
				</ul>
			</td>
		</tr>
HTML;

//--endhtml--//
return $IPBHTML;
}

//*************************//
//($%^ FORUM POINTS ^%$)//
//*************************//

/**
 * Display forum header
 */
public function renderForumHeader($buttons) {

$IPBHTML = "";
//--starthtml--//

$IPBHTML .= <<<HTML
<div class='section_title'>
	<h2>{$this->lang->words['forum_list_title']}</h2>
	{$buttons}
</div>

<div class='acp-box'>
	<h3>{$this->lang->words['manage_forums']}</h3>
<form id='adminform' method='post' action='{$this->settings['base_url']}{$this->form_code}&amp;do=edit_frm_pts' onsubmit='return ACPForums.submitModForm()'>
	
HTML;

//--endhtml--//
return $IPBHTML;
}

/**
 * Display forum footer
 */
public function renderForumFooter( $button ) {

$IPBHTML = "";
//--starthtml--//

$IPBHTML .= <<<HTML
	<div class='acp-actionbar'>
			<input type='submit' class='button' value='{$button}' />
		</form>
	</div>
</div>
HTML;

//--endhtml--//
return $IPBHTML;
}

/**
 * Forum wrapper
 */
public function forumWrapper( $content, $r ) {

$IPBHTML = "";
//--starthtml--//

$IPBHTML .= <<<HTML

	<table class='ipsTable'>
	<tr>
  		<th width='50%' title='{$r['name']}'>{$r['name']}</th>
		<th>{$this->lang->words['pts_per_tpc']}</th>
		<th>{$this->lang->words['pts_per_rply']}</th>
		<th>{$this->lang->words['pts_per_rply_on_yr_topic']}</th>		
 	</tr>
	</table>
	<table class='ipsTable'>
		<div id='cat_{$r['id']}_container'>
			{$content}
		</div>
	</table> 

HTML;

//--endhtml--//
return $IPBHTML;
}

/**
 * Render a forum row
 */
public function renderForumRow( $r ) {

#format points (with, or without decimal)
$r['eco_tpc_pts'] 		= ($this->settings['eco_general_use_decimal']) ? $r['eco_tpc_pts'] : intval($r['eco_tpc_pts']);
$r['eco_rply_pts'] 		= ($this->settings['eco_general_use_decimal']) ? $r['eco_rply_pts'] : intval($r['eco_rply_pts']);
$r['eco_get_rply_pts'] 	= ($this->settings['eco_general_use_decimal']) ? $r['eco_get_rply_pts'] : intval($r['eco_get_rply_pts']);

$IPBHTML = "";
//--starthtml--//

$IPBHTML .= <<<HTML
	<tr>
		<input type='hidden' name='forum_ids[]' id='forum_ids[]' value='{$r['id']}' />
 		<td width='50%'><span class='larger_text'>{$r['name']}</span</td>
		<td><input type='input' style='text-align:right' name='eco_tpc_pts_{$r['id']}' size='3' value='{$r['eco_tpc_pts']}' /></td>
		<td><input type='input' style='text-align:right' name='eco_rply_pts_{$r['id']}' size='3' value='{$r['eco_rply_pts']}' /></td>
		<td><input type='input' style='text-align:right' name='eco_get_rply_pts_{$r['id']}' size='3' value='{$r['eco_get_rply_pts']}' /></td>	
	</tr>
HTML;

//--endhtml--//
return $IPBHTML;
}

/**
 * Display "no forums" row
 */
public function renderNoForums( $parent_id ) {

$IPBHTML = "";
//--starthtml--//

$IPBHTML .= <<<HTML
<tr>
 <td class='tablerow1' width='100%' colspan='2'>
	{$this->lang->words['frm_noforums']}
 </td>
</tr>
HTML;

//--endhtml--//
return $IPBHTML;
}

//*************************//
//($%^ GROUP PERMS ^%$)//
//*************************//

/**
 * Overview of groups
 */
public function groupsOverviewWrapper( $content, $buttons ) {

$IPBHTML = "";
//--starthtml--//

$IPBHTML .= <<<HTML
<div class='section_title'>
	<h2>{$this->lang->words['group_page_title']}</h2>
	{$buttons}
</div>

<div class='acp-box'>
	<h3>{$this->lang->words['edit_groups_list_title']}</h3>
	<table class='ipsTable'>
		<tr>
			<th width='3%'>&nbsp;</th>
			<th width='14%'>{$this->lang->words['group_title']}</th>
			<th width='6%' style='text-align:center'>{$this->lang->words['can_access_title']}</th>
			<th width='6%' style='text-align:center'>{$this->lang->words['can_shop_title']}</th>
			<th width='6%' style='text-align:center'>{$this->lang->words['can_bank_title']}</th>
			<th width='6%' style='text-align:center'>{$this->lang->words['can_stock_title']}</th>
			<th width='6%' style='text-align:center'>{$this->lang->words['can_lt_title']}</th>
			<th width='6%' style='text-align:center'>{$this->lang->words['can_cc_title']}</th>
			<th width='6%' style='text-align:center'>{$this->lang->words['can_loan_title']}</th>
			<th width='6%' style='text-align:center'>{$this->lang->words['can_welfare_title']}</th>
			<th width='6%' style='text-align:center'>{$this->lang->words['members_title']}</th>
			<th width='18%'>&nbsp;</th>
		</tr>
		{$content}
	</table>
</div>
HTML;

//--endhtml--//
return $IPBHTML;
}

/**
 * Group row
 */
public function groupsOverviewRow( $r="" ) {
$IPBHTML = "";
//--starthtml--//

$r['_title']				= IPSMember::makeNameFormatted( $r['g_title'], $r['g_id'] );

$r['can_eco_access']		= $r['g_eco']    	 	? 'accept.png' : 'cross.png';
$r['can_eco_shop']			= $r['g_eco_shopitem']	? 'accept.png' : 'cross.png';
$r['can_bank_access']		= $r['g_eco_bank']   	? 'accept.png' : 'cross.png';
$r['can_stock_access']		= $r['g_eco_stock']  	? 'accept.png' : 'cross.png';
$r['can_lt_access']			= $r['g_eco_lt']     	? 'accept.png' : 'cross.png';
$r['can_cc_access']			= $r['g_eco_cc']     	? 'accept.png' : 'cross.png';
$r['can_loan_access']		= $r['g_eco_loan']	 	? 'accept.png' : 'cross.png';
$r['can_welfare_access']	= $r['g_eco_welfare']	? 'accept.png' : 'cross.png';

$r['max_eco']				= $r['g_eco_max_pts']    	? $this->lang->words['limit_title'].': '.$this->registry->getClass('class_localization')->formatNumber( $r['g_eco_max_pts'] ).' '.$this->settings['eco_general_currency'] 		: $this->lang->words['no_limit'];
$r['max_eco_bank']			= $r['g_eco_bank_max']    	? $this->lang->words['limit_title'].': '.$this->registry->getClass('class_localization')->formatNumber( $r['g_eco_bank_max'] ).' '.$this->settings['eco_general_currency'] 		: $this->lang->words['no_limit'];
$r['max_eco_stock']			= $r['g_eco_stock_max']    	? $this->lang->words['limit_title'].': '.$this->registry->getClass('class_localization')->formatNumber( $r['g_eco_stock_max'] ).' '.$this->lang->words['shares'] 				: $this->lang->words['no_limit'];
$r['max_eco_lt']			= $r['g_eco_lt_max']    	? $this->lang->words['limit_title'].': '.$this->registry->getClass('class_localization')->formatNumber( $r['g_eco_lt_max'] ).' '.$this->settings['eco_general_currency'] 		: $this->lang->words['no_limit'];
$r['max_eco_csh_adv']		= $r['g_eco_cash_adv_max'] 	? $this->lang->words['limit_title'].': '.$this->registry->getClass('class_localization')->formatNumber( $r['g_eco_cash_adv_max'] ).' '.$this->settings['eco_general_currency']	: $this->lang->words['no_limit'];
$r['max_eco_cc_debt']		= $r['g_eco_max_cc_debt']   ? $this->lang->words['limit_title'].': '.$this->registry->getClass('class_localization')->formatNumber( $r['g_eco_max_cc_debt'] ).' '.$this->settings['eco_general_currency'] 	: $this->lang->words['no_limit'];
$r['max_eco_loan_debt']		= $r['g_eco_max_loan_debt'] ? $this->lang->words['limit_title'].': '.$this->registry->getClass('class_localization')->formatNumber( $r['g_eco_max_loan_debt'] ).' '.$this->settings['eco_general_currency'] : $this->lang->words['no_limit'];
$r['max_eco_welfare']		= $r['g_eco_welfare_max']   ? $this->lang->words['limit_title'].': '.$this->registry->getClass('class_localization')->formatNumber( $r['g_eco_welfare_max'] ).' '.$this->settings['eco_general_currency'] 	: $this->lang->words['no_limit'];

$IPBHTML .= <<<HTML
<tr class='ipsControlRow'>
	<td><img src='{$this->settings['skin_acp_url']}/images/icons/group.png' border='0' /></td>
	<td><span class='larger_text'><a style='text-decoration:none;' href='{$this->settings['base_url']}{$this->form_code}&amp;do=show_group&amp;id={$r['g_id']}'>{$r['_title']}</a></span></td>
	<td align='center'><img src='{$this->settings['skin_acp_url']}/images/icons/{$r['can_eco_access']}' title='{$r['max_eco']}' border='0' alt='-' class='ipd' /></td>
	<td align='center'><img src='{$this->settings['skin_acp_url']}/images/icons/{$r['can_eco_shop']}' border='0' alt='-' class='ipd' /></td>
	<td align='center'><img src='{$this->settings['skin_acp_url']}/images/icons/{$r['can_bank_access']}' title ='{$r['max_eco_bank']}' border='0' alt='-' class='ipd' /></td>
	<td align='center'><img src='{$this->settings['skin_acp_url']}/images/icons/{$r['can_stock_access']}' title ='{$r['max_eco_stock']	}' border='0' alt='-' class='ipd' /></td>
	<td align='center'><img src='{$this->settings['skin_acp_url']}/images/icons/{$r['can_lt_access']}' title ='{$r['max_eco_lt']}' border='0' alt='-' class='ipd' /></td>
	<td align='center'><img src='{$this->settings['skin_acp_url']}/images/icons/{$r['can_cc_access']}' title ='{$r['max_eco_csh_adv']}' border='0' alt='-' class='ipd' /></td>  
	<td align='center'><img src='{$this->settings['skin_acp_url']}/images/icons/{$r['can_loan_access']}' title ='{$r['max_eco_loan_debt']}' border='0' alt='-' class='ipd' /></td>  
	<td align='center'><img src='{$this->settings['skin_acp_url']}/images/icons/{$r['can_welfare_access']}' title ='{$r['max_eco_welfare']}' border='0' alt='-' class='ipd' /></td>
	<td align='center'>
HTML;
if ( $r['g_id'] != $this->settings['auth_group'] and $r['g_id'] != $this->settings['guest_group'] )
{
$IPBHTML .= <<<HTML
	<a href='{$this->settings['_base_url']}app=members&amp;section=members&amp;module=members&amp;__update=1&amp;f_primary_group={$r['g_id']}' title='{$this->lang->words['g_listusers']}'>{$r['count']}</a>
HTML;
}
else
{
$IPBHTML .= <<<HTML
    {$r['count']}
HTML;
}
$IPBHTML .= <<<HTML
  </td>	
	<td>
		<ul class='ipsControlStrip'>
			<li class='i_edit' title='{$this->lang->words['edit_group']}'><a href='{$this->settings['base_url']}{$this->form_code}&amp;do=show_group&amp;id={$r['g_id']}'>{$this->lang->words['edit_group']}</a></li>
			<li class='i_lock' title='{$this->lang->words['grant_no_access']}'><a href='{$this->settings['base_url']}{$this->form_code}&amp;do=group_disable_all&amp;group_id={$r['g_id']}'>{$this->lang->words['grant_no_access']}</a></li>
			<li class='i_unlock' title='{$this->lang->words['grant_full_access']}'><a href='{$this->settings['base_url']}{$this->form_code}&amp;do=group_enable_all&amp;group_id={$r['g_id']}'>{$this->lang->words['grant_full_access']}</a></li>
		</ul>
	</td>
</tr>
HTML;

//--endhtml--//
return $IPBHTML;
}

/**
 * Group form
 */
public function groupsForm( $group ) {

$form						= array();

#any plugins with group settings we need to add?
$pluginGroupSettingsHTML = $this->registry->class_ibEco_CP->buildPluginGroupSettingsHTML($group);
$pluginGroupSettingsHTML = ($pluginGroupSettingsHTML) ? $pluginGroupSettingsHTML : 
"<tr>
<th colspan='2'>{$this->lang->words['no_plugins_installed_or_none_contain_group_settings']}</th>
</tr>";


#Lottery Odds Dropdown
$levels = array();

for ($i=1; $i<=9; $i++)
{
	$levels[] = array($i, $i - 5);
}

#Lottery Tab:
$form['g_eco_lottery']			= $this->registry->output->formYesNo( "g_eco_lottery", $group['g_eco_lottery'] );
$form['g_eco_lottery_tix']		= $this->registry->output->formInput( "g_eco_lottery_tix", $group['g_eco_lottery_tix'] );
$form['g_eco_lottery_odds']		= $this->registry->output->formDropdown( "g_eco_lottery_odds", $levels, $group['g_eco_lottery_odds'] != 0  ? $group['g_eco_lottery_odds'] : 5);

#General Tab:
$form['g_eco']					= $this->registry->output->formYesNo( "g_eco", $group['g_eco'] );
$form['g_eco_max_pts']			= $this->registry->output->formInput( "g_eco_max_pts", $group['g_eco_max_pts'] );
$form['g_eco_frm_ptsx']			= $this->registry->output->formInput( "g_eco_frm_ptsx", $group['g_eco_frm_ptsx'] );
$form['g_eco_transaction']		= $this->registry->output->formYesNo( "g_eco_transaction", $group['g_eco_transaction'] );
$form['g_eco_shopitem']			= $this->registry->output->formYesNo( "g_eco_shopitem", $group['g_eco_shopitem'] );
$form['g_eco_asset']			= $this->registry->output->formYesNo( "g_eco_asset", $group['g_eco_asset'] );

#Bank Tab:
$form['g_eco_bank']				= $this->registry->output->formYesNo( "g_eco_bank", $group['g_eco_bank'] );
$form['g_eco_bank_max']			= $this->registry->output->formInput( "g_eco_bank_max", $group['g_eco_bank_max'] );
$form['g_eco_stock']			= $this->registry->output->formYesNo( "g_eco_stock", $group['g_eco_stock'] );
$form['g_eco_stock_max']		= $this->registry->output->formInput( "g_eco_stock_max", $group['g_eco_stock_max'] );
$form['g_eco_lt']				= $this->registry->output->formYesNo( "g_eco_lt", $group['g_eco_lt'] );
$form['g_eco_lt_max']			= $this->registry->output->formInput( "g_eco_lt_max", $group['g_eco_lt_max'] );

#Loan Tab:
$form['g_eco_loan']				= $this->registry->output->formYesNo( "g_eco_loan", $group['g_eco_loan'] );
$form['g_eco_cc']				= $this->registry->output->formYesNo( "g_eco_cc", $group['g_eco_cc'] );
$form['g_eco_max_cc_debt']		= $this->registry->output->formInput( "g_eco_max_cc_debt", $group['g_eco_max_cc_debt'] );
$form['g_eco_max_loan_debt']	= $this->registry->output->formInput( "g_eco_max_loan_debt", $group['g_eco_max_loan_debt'] );
$form['g_eco_cash_adv_max']		= $this->registry->output->formInput( "g_eco_cash_adv_max", $group['g_eco_cash_adv_max'] );
$form['g_eco_bal_trnsfr_max']	= $this->registry->output->formInput( "g_eco_bal_trnsfr_max", $group['g_eco_bal_trnsfr_max'] );

#Welfare Tab:
$form['g_eco_welfare']			= $this->registry->output->formYesNo( "g_eco_welfare", $group['g_eco_welfare'] );
$form['g_eco_welfare_max']		= $this->registry->output->formInput( "g_eco_welfare_max", $group['g_eco_welfare_max'] );

#Admin Tab
$form['g_eco_edit_pts']			= $this->registry->output->formYesNo( "g_eco_edit_pts", $group['g_eco_edit_pts'] );

$title	= $this->lang->words['editing_title']." ".$group['g_title'];

$this->lang->words['max_group_points'] = str_replace('<%POINTS_NAME%>', $this->settings['eco_general_currency'], $this->lang->words['max_group_points']);

$IPBHTML = "";
//--starthtml--//

$IPBHTML .= <<<HTML
<div class='section_title'>
	<h2>{$title}</h2>
	{$buttons}
</div>

<form id='adminform' action='{$this->settings['base_url']}{$this->form_code}&amp;do=edit_group' method='post' id='adform' name='adform' onsubmit='return checkform();'>
<input type='hidden' name='group_id' value='{$group['g_id']}' />
<input type='hidden' name='group_name' value='{$group['g_title']}' />

<div class='acp-box'>
	<h3>{$this->lang->words['group_details']}</h3>
	<div class='ipsTabBar with_left with_right' id='tabstrip_manage_group_perms'>
		<span class='tab_left'>&laquo;</span>
		<span class='tab_right'>&raquo;</span>
		<ul>
			<li id='tab_1'>{$this->lang->words['general_tab_title']}</li>
			<li id='tab_2'>{$this->lang->words['invest_tab_title']}</li>
			<li id='tab_3'>{$this->lang->words['loan_tab_title']}</li>
			<li id='tab_4'>{$this->lang->words['welfare_tab_title']}</li>
			<li id='tab_5'>{$this->lang->words['lottery']}</li>
			<li id='tab_6'>{$this->lang->words['plugins']}</li>
			<li id='tab_7'>{$this->lang->words['administrate']}</li>
		</ul>
	</div>
	<div class='ipsTabBar_content' id='tabstrip_manage_group_perms_content'>
		<div id='tab_1_content'>
			<table class='ipsTable'>
				<tr>
					<th colspan='2'>{$this->lang->words['general_perms_title']}</th>
				</tr>
				<tr>
					<td>
						<label>{$this->lang->words['can_access_eco']}?</label>
					</td>
					<td>
						{$form['g_eco']}
					</td>
				</tr>
				<tr>
					<td>
						<label>{$this->lang->words['can_view_transactions']}</label>
					</td>
					<td>
						{$form['g_eco_transaction']}
					</td>
				</tr>
				<tr>
					<td>
						<label>{$this->lang->words['can_shop']}</label>
					</td>
					<td>
						{$form['g_eco_shopitem']}
					</td>
				</tr>
				<tr>
					<td>
						<label>{$this->lang->words['can_view_all_inventory']}</label>
					</td>
					<td>
						{$form['g_eco_asset']}
					</td>
				</tr>			
				<tr>
					<td>
						<label>{$this->lang->words['max_group_points']}</label><br />{$this->lang->words['leave_blank_for_no_limit']}
					</td>
					<td>
						{$form['g_eco_max_pts']}
					</td>
				</tr>
				<tr>
					<td>
						<label>{$this->lang->words['forum_point_multplier']}</label><br />{$this->lang->words['forum_point_multplier_exp']}
					</td>
					<td>
						{$form['g_eco_frm_ptsx']}
					</td>
				</tr>			
			</table>
		</div>
		<div id='tab_2_content'>
			<table class='ipsTable'>
				<tr>
					<th colspan='2'>{$this->lang->words['bank_perms_title']}</th>
				</tr>
				<tr>
					<td>
						<label>{$this->lang->words['can_open_bank']}?</label>
					</td>
					<td>
						{$form['g_eco_bank']}
					</td>
				</tr>
				<tr>
					<td>
						<label>{$this->lang->words['max_per_bank_pts']}</label><br />{$this->lang->words['leave_blank_for_no_limit']}
					</td>
					<td>
						{$form['g_eco_bank_max']}
					</td>
				</tr>			
				<tr>
					<td>
						<label>{$this->lang->words['can_buy_stocks']}?</label>
					</td>
					<td>
						{$form['g_eco_stock']}
					</td>
				</tr>
				<tr>
					<td>
						<label>{$this->lang->words['max_per_stock_shares']}</label><br />{$this->lang->words['leave_blank_for_no_limit']}
					</td>
					<td>
						{$form['g_eco_stock_max']}
					</td>
				</tr>			
				<tr>
					<td>
						<label>{$this->lang->words['can_invest_lt']}?</label>
					</td>
					<td>
						{$form['g_eco_lt']}
					</td>
				</tr>
				<tr>
					<td>
						<label>{$this->lang->words['max_per_lt_points']}</label><br />{$this->lang->words['leave_blank_for_no_limit']}
					</td>
					<td>
						{$form['g_eco_lt_max']}
					</td>
				</tr>							
			</table>
		</div>
		<div id='tab_3_content'>
			<table class='ipsTable'>
				<tr>
					<th colspan='2'>{$this->lang->words['credit_perms_title']}</th>
				</tr>
				<tr>
					<td>
						<label>{$this->lang->words['can_get_cc']}?</label>
					</td>
					<td>
						{$form['g_eco_cc']}
					</td>
				</tr>
				<tr>
					<td>
						<label>{$this->lang->words['max_cash_adv']}</label><br />{$this->lang->words['leave_blank_for_no_limit']}
					</td>
					<td>
						{$form['g_eco_cash_adv_max']}
					</td>
				</tr>
				<tr>
					<td>
						<label>{$this->lang->words['max_bal_transfer']}</label><br />{$this->lang->words['leave_blank_for_no_limit']}
					</td>
					<td>
						{$form['g_eco_bal_trnsfr_max']}
					</td>
				</tr>
				<tr>
					<td>
						<label>{$this->lang->words['max_group_cc_debt']}?</label><br />{$this->lang->words['leave_blank_for_no_limit']}
					</td>
					<td>
						{$form['g_eco_max_cc_debt']}
					</td>
				</tr>			
				<tr>
					<th colspan='2'>{$this->lang->words['loan_perms_title']}</th>
				</tr>			
				<tr>
					<td>
						<label>{$this->lang->words['can_apply_loan']}?</label>
					</td>
					<td>
						{$form['g_eco_loan']}
					</td>
				</tr>
				<tr>
					<td>
						<label>{$this->lang->words['max_group_loan_debt']}?</label><br />{$this->lang->words['leave_blank_for_no_limit']}
					</td>
					<td>
						{$form['g_eco_max_loan_debt']}
					</td>
				</tr>						
			</table>
		</div>
		<div id='tab_4_content'>
			<table class='ipsTable'>
				<tr>
					<th colspan='2'>{$this->lang->words['welfare_perms_title']}</th>
				</tr>
				<tr>
					<td>
						<label>{$this->lang->words['can_apply_welfare']}?</label>
					</td>
					<td>
						{$form['g_eco_welfare']}
					</td>
				</tr>
				<tr>
					<td>
						<label>{$this->lang->words['max_group_welfare']}</label>
					</td>
					<td>
						{$form['g_eco_welfare_max']}
					</td>
				</tr>			
						
			</table>
		</div>
		<div id='tab_5_content'>
			<table class='ipsTable'>
				<tr>
					<th colspan='2'>{$this->lang->words['lottery']}</th>
				</tr>
				<tr>
					<td>
						<label>{$this->lang->words['can_purchase_lotto_tix']}?</label>
					</td>
					<td>
						{$form['g_eco_lottery']}
					</td>
				</tr>
				<tr>
					<td>
						<label>{$this->lang->words['max_tickets_to_purchase']}</label><br />{$this->lang->words['zero_default']}
					</td>
					<td>
						{$form['g_eco_lottery_tix']}
					</td>
				</tr>
				<tr>
					<td>
						<label>{$this->lang->words['increased_decreased_lotto_odds']}</label><br />{$this->lang->words['increased_decreased_lotto_odds_exp']}
					</td>
					<td>
						{$form['g_eco_lottery_odds']}
					</td>
				</tr>									
			</table>
		</div>
		<div id='tab_6_content'>
			<table class='ipsTable'>
				{$pluginGroupSettingsHTML}		
			</table>
		</div>
		<div id='tab_7_content'>
			<table class='ipsTable'>
				<tr>
					<th colspan='2'>{$this->lang->words['administrate']}</th>
				</tr>
				<tr>
					<td>
						<label>{$this->lang->words['can_edit']} {$this->settings['eco_general_currency']}?</label>
					</td>
					<td>
						{$form['g_eco_edit_pts']}
					</td>
				</tr>					
			</table>
		</div>		
	</div>
	<div class='acp-actionbar'>
		<input type='submit' class='button' value='{$this->lang->words['edit_group_button']}' />
	</div>
	</form>
</div>

<script type='text/javascript'>
jQ("#tabstrip_manage_group_perms").ipsTabBar({ tabWrap:
"#tabstrip_manage_group_perms_content" });
</script>
	
HTML;

//--endhtml--//
return $IPBHTML;
}

//*************************//
//($%^ FOOTER^%$)//
//*************************//

/**
 * copyright
 */
public function footer() {

$IPBHTML = "";
//--starthtml--//

if (true)
{
$IPBHTML .= <<<HTML
		<br /><br />
		<div style='float:right; margin:10px;'>
			<tr style='text-align:center;'>
				<td style='padding:5px;'>ibEconomy {$this->caches['app_cache']['ibEconomy']['app_version']} &copy; 2011 &nbsp;
				<a style='text-decoration:none;' href='http://emoneycodes.com/forums/' title='emoneyCodes.com - (e$) Mods'><span class='ipsBadge badge_green'  style='text-decoration:none;'>(e$) Mods</span></a></td>
			</tr>
		</div>
HTML;
}

//--endhtml--//
return $IPBHTML;
}

//*************************//
//($%^ BUTTONS ^%$)//
//*************************//

/**
 * ibECO BUTTONS!
 */
public function buttonRow( $buttons ) {

$IPBHTML = "";
//--starthtml--//

$IPBHTML .= <<<HTML
	<div class='ipsActionBar clearfix'>
		<ul  style='float:right'>
			{$buttons}
		</ul>
	</div>
	
HTML;

//--endhtml--//

return $IPBHTML;
}

/**
 * ibECO BUTTON!
 */
public function button( $button ) {

$IPBHTML = "";
//--starthtml--//

$IPBHTML .= <<<HTML

		<li class='ipsActionButton'>
			<a href='{$this->settings['base_url']}module={$button['button_module']}&amp;section={$button['button_section']}&amp;do={$button['button_do']}&amp;url={$this->request['module']}_{$this->request['section']}_{$this->request['do']}'>
				<img src='{$this->settings['img_url']}/eco_images/{$button['button_image']}' alt='' />
				{$button['button_words']}
			</a>
		</li>
	
HTML;

//--endhtml--//

return $IPBHTML;
}

//*************************//
//($%^ FRONTPAGE ^%$)//
//*************************//

/**
 * ibECO Frontpage
 */
public function frontPage($buttons, $FPData, $newsItems) {

#groups drops
$group_dd 		= $this->registry->output->formDropdown( 'id', $FPData['group_dd'] );
$adv_group_dd 	= $this->registry->output->formDropdown( 'id', $FPData['adv_group_dd'] );

#pts field drop
$ptsFieldDD 	= $this->registry->output->formDropdown( 'pts_field', $FPData['pt_fields'] );

#items drop
$items_dd = $this->registry->output->formDropdown( 'shop_item_file', $FPData['stock_items'] );

#items drop
$allStuffDD = $this->registry->output->formDropdown( 'delete_item', $FPData['allStuff'] );

#langs
$this->lang->words['send_points_to'] = sprintf( $this->lang->words['send_points_to'], $this->settings['eco_general_currency'] );
#$this->lang->words['convert_pts_to_eco_points'] = sprintf( $this->lang->words['convert_pts_to_eco_points'], $ptsFieldDD );

#updater?
if( $this->settings['eco_update_checker'] )
{
$updaterAndNewsBoxes = "
	<div style='width:28%; float:left; margin-right:10px;'>
		<div class='acp-box'>
			<h3><img src='{$this->settings['img_url']}/transmit_blue.png' border='0' /> {$this->lang->words['update_checker']}</h3>
			<table class='ipsTable'>
				<tr>
					<th><strong>{$this->lang->words['up_to_date']}?</strong></th>
					<th><img src='{$this->settings['skin_acp_url']}/images/icons/{$FPData['upgrade_image']}' border='0' alt='-' class='ipd' /></th>
				</tr>			
				<tr>
					<td><strong>{$this->lang->words['installed_version']}</strong>:</td>
					<td>{$FPData['instalVer']}</td>
				</tr>
				<tr>
					<td><strong>{$this->lang->words['latest_version']}</strong>:</td>
					<td>{$FPData['latestVer']}</td>
				</tr>
				<tr>
					<td><strong>{$this->lang->words['download_and_support']}</strong>:</td>
					<td>{$FPData['dl_link']}</td>
				</tr>
			</table>
		</div>
	</div>
	<div style='width:70%; float:right'>
		<div class='acp-box'>
			<h3><img src='{$this->settings['img_url']}/feed.png' border='0' /> {$this->lang->words['latest_ibeconomy_news']}</h3>
			<table class='ipsTable'>
				{$newsItems}
			</table>
		</div>
	</div>
<div style='clear: both;'><br />";
}

$IPBHTML = "";
//--starthtml--//

$IPBHTML .= <<<HTML
<div class='section_title'>
	<h2>{$this->settings['eco_general_name']} {$this->lang->words['frontpage_title']}</h2>
	{$buttons}
</div>

HTML;

if ( $FPData['warning_html'] )
{
	$warning = "<div class='warning'>
	<h4><img src='{$this->settings['skin_acp_url']}/images/icons/bullet_error.png' border='0' alt='Error' /> {$this->lang->words['time_for_upgrade_header']}</h4> {$FPData['warning_html']}
</div><br />";
}

$IPBHTML .= <<<HTML
{$warning}
HTML;

		$IPBHTML .= <<<HTML
	{$updaterAndNewsBoxes}
	<div style='width:70%; float:left; margin-right:10px;'>
		<div class='acp-box'>
			<h3><img src='{$this->settings['skin_acp_url']}/images/icons/user_edit.png' border='0' /> {$this->lang->words['quick_tools']}</h3>
			<table class='ipsTable' style='height:144px'>
				<tr>
					<td><strong>{$this->lang->words['find_member']}</strong>
						<form id='adminform' action='{$this->settings['base_url']}module=members&amp;do=find_em' method='post' id='adform' name='adform' onsubmit='return checkform();'>
					</td>
					<td style='text-align:right'>
						<input type="text" class='text_input' id='mem_name1' name="mem_name" size="20" value="{$mem_name}" tabindex="0" />
					</td>
					<td style='text-align:right'>
						<input type="submit" class='realbutton' value="{$this->lang->words['go']}" tabindex="0" />
						</form>
					</td>					
				</tr>
				<tr>
					<td><strong>{$this->lang->words['edit_group']}</strong>
						<form id='adminform' action='{$this->settings['base_url']}module=group_perms&amp;section=group_perms&amp;do=show_group' method='post' id='adform' name='adform' onsubmit='return checkform();'>
					</td>
					<td style='text-align:right'>
						{$group_dd}
					</td>
					<td style='text-align:right'>
						<input type="submit" class='realbutton' value="{$this->lang->words['go']}" tabindex="0" />
						</form>
					</td>
				</tr>
				<tr>
					<td><strong>{$this->lang->words['add_new_item']}</strong>
						<form id='adminform' method='post' action='{$this->settings['base_url']}module=shop&amp;section=shop&amp;do=item&amp;type=add' onsubmit='return ACPForums.submitModForm()'>
					</td>
					<td style='text-align:right'>
						{$items_dd}
					</td>
					<td style='text-align:right'>
						<input type="submit" class='realbutton' value="{$this->lang->words['go']}" tabindex="0" />
						</form>
					</td>
				</tr>
			</table>
		</div>
	</div>
	<div style='width:28%; float:right'>
		<div class='acp-box alt'>
			<h3><img src='{$this->settings['img_url']}/eco_images/money_add.png' border='0' /> {$this->lang->words['donations']}</h3>
			<table class='ipsTable' style='height:144px'>
				<tr>
					<td style='text-align:center'>Because this app is free and has taken years to develop, any donations would be MUCH appreciated!<br /><br />
						<form action="https://www.paypal.com/cgi-bin/webscr" method="post">
							<input type="hidden" name="cmd" value="_s-xclick">
							<input type="hidden" name="hosted_button_id" value="50584">
							<input type="image" src="https://www.paypal.com/en_US/i/btn/btn_donateCC_LG.gif" border="0" name="submit" alt="">
							<img alt="" border="0" src="https://www.paypal.com/en_US/i/scr/pixel.gif" width="1" height="1">
						</form>
					</td>
				</tr>
			</table>
		</div>
	</div>
	<div style='clear: both;'><br />
	<div style='width:28%; float:left; margin-right:10px;'>
		<div class='acp-box'>
			<h3><img src='{$this->settings['img_url']}/eco_images/bar_graph.png' border='0' /> {$this->lang->words['statistics']}</h3>
			<table class='ipsTable' style='height:144px'>
					<tr>
						<td><strong>{$this->lang->words['total_pts']} {$this->settings['eco_general_currency']}:</strong></td>
						<td>{$FPData['total_points']}</td>					
					</tr>
					<tr>
						<td><strong>{$this->lang->words['total_worth']}:</strong></td>
						<td>{$FPData['total_worth']}</td>					
					</tr>
					<tr>
						<td><strong>{$this->lang->words['total_welfare']}:</strong></td>
						<td>{$FPData['total_welfare']}</td>					
					</tr>
					<tr>
						<td><strong>{$this->lang->words['item_count']}:</strong></td>
						<td>{$FPData['item_count']}</td>					
					</tr>
			</table>
		</div>
	</div>
	<div style='width:70%; float:right'>
		<div class='acp-box'>
			<h3><img src='{$this->settings['img_url']}/eco_images/wrench_orange.png' border='0' /> {$this->lang->words['advanced_tools_caution']}</h3>
			<table class='ipsTable' style='height:144px'>
				<tr>
					<td>
						<form id='adminform' action='{$this->settings['base_url']}module=members&amp;do=mass_donate' method='post' id='adform' name='adform' onsubmit="return confirm('{$this->lang->words['confirm_mass_pts_send']}');">
						<strong>{$this->lang->words['send']} <input type='text' name='amount' size='5' /> {$this->lang->words['send_points_to']}</strong>
					</td>
					<td style='text-align:right'>
						{$adv_group_dd}
					</td>
					<td style='text-align:right'>
						<input type="submit" class='realbutton' value="{$this->lang->words['go']}" tabindex="0" />
						</form>
					</td>					
				</tr>
				<tr>
					<td>
						<strong>{$this->lang->words['convert_pts_to_eco_points']}</strong>
						<form id='adminform' action='{$this->settings['base_url']}module=members&amp;do=convert_points' method='post' id='adform' name='adform'  onsubmit="return confirm('{$this->lang->words['confirm_pt_conversion']}');">
					</td>
					<td style='text-align:right'>
						{$ptsFieldDD}
					</td>
					<td style='text-align:right'>
						<input type="submit" class='realbutton' value="{$this->lang->words['go']}" tabindex="0" />
						</form>
					</td>
				</tr>
				<tr>
					<td><strong>{$this->lang->words['delete_all']}</strong>
						<form id='adminform' method='post' action='{$this->settings['base_url']}module=members&amp;do=mass_delete' onsubmit="return confirm('{$this->lang->words['confirm_mass_delete']}');">
					</td>
					<td style='text-align:right'>
						{$allStuffDD}
					</td>
					<td style='text-align:right'>
						<input type="submit" class='realbutton' value="{$this->lang->words['go']}" tabindex="0" />
						</form>
					</td>
				</tr>
			</table>
		</div>
	</div>
	<div style='clear: both;'><br />		
HTML;

$IPBHTML .= <<<HTML

<script type="text/javascript">
document.observe("dom:loaded", function(){
	var autocomplete= new ipb.Autocomplete( $('mem_name1'), { multibox: false, url: acp.autocompleteUrl, templates: { wrap: acp.autocompleteWrap, item: acp.autocompleteItem } } );
});
</script>
	
HTML;

//--endhtml--//
return $IPBHTML;
}

/**
 * ibECO BUTTON!
 */
public function frontpageNewItem( $newsRow ) {

$IPBHTML = "";
//--starthtml--//

$IPBHTML .= <<<HTML

				<tr>
					<td><img src='{$this->settings['skin_acp_url']}/images/icons/ipsnews_item.gif' border='0' /> {$newsRow['ecoNewsTime']}</td>
					<td>{$newsRow['ecoNewsText']}</td>					
				</tr>
	
HTML;

//--endhtml--//

return $IPBHTML;
}

//*************************//
//($%^ Image Input ^%$)//
//*************************//

/**
 * Upload image dhtml window
 */
public function imageInput()
{
$IPBHTML = "";
																	
$IPBHTML .= <<<EOF

			<tr>
				<th colspan='2'>{$this->lang->words['image']}</th>
			</tr>
			<tr>
				<td style='width: 40%'>
					<label>{$this->lang->words['upload_image']}</label>
				</td>
				<td style='width: 60%'>
					<input type="file" name="upload_photo" id='upload_photo' value="" size="40" />
				</td>
			</tr>
EOF;

return $IPBHTML;
}

//*************************//
//($%^ Image Popups ^%$)//
//*************************//

/**
 * Upload image dhtml window
 */
public function inline_form_new_image( $item_id, $item_type )
{
$IPBHTML = "";
																	
$IPBHTML .= <<<EOF

	<div class='acp-box'>
		<h3 style='min-height:37px;'>{$this->lang->words['upload_image']}</h3>
		<form action='{$this->settings['base_url']}&amp;module=shop&amp;section=shop&amp;do=newImage&amp;item_id={$item_id}&amp;item_type={$item_type}' method='post' enctype='multipart/form-data'>
		<table class='ipsTable double_pad'>
			<tr>
				<td>
					<label for='upload_photo'>{$this->lang->words['new_image']}</label>
					<input type='file' size='30' id='upload_photo' name='upload_photo' />
				</td>
			</tr>
		</table>
		<div class='acp-actionbar'>
			<input type='submit' value='{$this->lang->words['save']}' class='button' />
		</div>
		</form>
	</div>

<!--
<div class='inlineFormEntry'>
	<div class='inlineFormLabel'>
		{$this->lang->words['new_image']}
	</div>
	<div class='inlineFormInput'>
		<input type='file' size='30' id='upload_photo' name='upload_photo' />
	</div>
</div>
<div class='inlineFormSubmit'><input type='submit' value='{$this->lang->words['save']}' /></div>
</form>-->
EOF;

return $IPBHTML;
}

/**
 * Delete image dhtml window
 */
public function inline_form_delete_image( $item_id, $item_type )
{
$IPBHTML = "";
																	
$IPBHTML .= <<<EOF

	<div class='acp-box alt'>
		<h3 style='min-height:37px;'>{$this->lang->words['delete_image']}</h3>
		<form action='{$this->settings['base_url']}&amp;module=shop&amp;section=shop&amp;do=newImage&amp;delete_image=YES&amp;item_id={$item_id}&amp;item_type={$item_type}' method='post' enctype='multipart/form-data'>
		<table class='ipsTable double_pad'>
			<tr>
				<td>
					<label for='upload_photo'>{$this->lang->words['delete_image_conf']}</label>
				</td>
			</tr>
		</table>
		<div class='acp-actionbar'>
			<input type='submit' value='{$this->lang->words['delete']}' class='button' />
		</div>
		</form>
	</div>

EOF;

return $IPBHTML;
}

/**
 * View/Upload/Delete Image Sidebar
 */
public function image_sidebar( $item_id, $item_type, $image)
{

$IPBHTML = "";
																	
$IPBHTML .= <<<EOF

			<div style='border:1px solid #369;background:#FFF;width:129px; padding:5px; margin: 10px auto;' id='MF__pp_photo_container'> 
				<img id='MF__pp_photo' src="{$image}" width='129' height='129' /> 
				<br /> 
				<ul class='photo_options'>				
					<li><a class='' style='float:none;width:auto;text-align:center;cursor:pointer' id='MF__removephoto' title='{$this->lang->words['remove_image']}'><img src='{$this->settings['skin_acp_url']}/images/picture_delete.png' alt='{$this->lang->words['remove_image']}' /></a></li> 
					<li><a class='' style='float:none;width:auto;text-align:center;cursor:pointer' id='MF__newphoto' title='{$this->lang->words['upload_new_image']}'><img src='{$this->settings['skin_acp_url']}/images/picture_add.png' alt='{$this->lang->words['upload_new_image']}' /></a></li>				
				</ul> 
				<script type='text/javascript'> 
					$('MF__newphoto').observe('click', acp.members.newPhoto.bindAsEventListener( this, "app=ibEconomy&amp;module=ajax&amp;section=editform&amp;do=show&amp;name=inline_form_new_image&amp;item_id={$item_id}&amp;item_type={$item_type}" ) );
					$('MF__removephoto').observe('click', acp.members.newPhoto.bindAsEventListener( this, "app=ibEconomy&amp;module=ajax&amp;section=editform&amp;do=deleteImage&amp;item_id={$item_id}&amp;item_type={$item_type}" ) );
				</script> 
			</div> 

EOF;

return $IPBHTML;
}

/**
 * New to 2.0, reset all member points form
 */
public function resetPointsForm($buttons)
{
$warning = sprintf( $this->lang->words['mass_recalculate_explained'], $this->settings['eco_general_currency'], $this->settings['eco_general_currency'] , $this->settings['eco_general_currency'] , $this->settings['eco_general_currency']  );

$IPBHTML = "";
																	
$IPBHTML .= <<<EOF
<div class='section_title'>
	<h2>{$this->lang->words['reset_all']} {$this->settings['eco_general_currency']}</h2>
	{$buttons}	
</div>
<form id='adminform' action='{$this->settings['base_url']}{$this->form_code}&amp;do=recalculate_all_points_init' method='post' id='adform' name='adform' onsubmit="return confirm('{$this->lang->words['confirm_mass_recalculate']}');">

<div class='acp-box alt'>
	<h3>{$this->lang->words['recalculate']} {$this->settings['eco_general_currency']}</h3>
	<table class='ipsTable' style='height:144px'>
		<tr>
			<td>
				<div class='warning'>
					{$warning}
					<span class='ipsBadge badge_purple'>{$this->lang->words['proceed_with_caution']}</span>
				</div>
			</td>
		</tr>
	</table>

<div class='acp-actionbar'>
	<input type='submit' class='button' value='{$this->lang->words['recalculate']}' />
</div>
</form>
</div>

EOF;

return $IPBHTML;
}

}