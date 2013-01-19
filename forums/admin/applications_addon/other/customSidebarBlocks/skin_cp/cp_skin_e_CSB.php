<?php
/**
 * Custom Sidebar Blocks
  * 1.0.0
 */
 
class cp_skin_e_CSB extends output
{

/**
 * Prevent our main destructor being called by this class
 */
public function __destruct()
{
}

//*************************//
//($%^ Blocks^%$)//
//*************************//

/**
 * Block form
 */
public function blockForm( $block, $perm_matrix ) {

#init some vars
$form = array();

$form['csb_title']		= $this->registry->output->formInput( "csb_title", $block['csb_title'] );
$form['csb_image']		= $this->registry->output->formInput( "csb_image", $block['csb_image'] );
$form['csb_on']			= $this->registry->output->formYesNo( "csb_on", $block['csb_on'] );
$form['csb_use_perms']  = $this->registry->output->formYesNo( "csb_use_perms", $block['csb_use_perms'] );
$form['csb_use_box']    = $this->registry->output->formYesNo( "csb_use_box", $block['csb_use_box'] );
$form['csb_raw']    	= $this->registry->output->formYesNo( "csb_raw", $block['csb_raw'] );

$value = $block['csb_content'];

IPSText::getTextClass('bbcode')->parse_html			= 0;
IPSText::getTextClass('bbcode')->parse_nl2br		= 1;
IPSText::getTextClass('bbcode')->parse_smilies  	= 1;
IPSText::getTextClass('bbcode')->parse_bbcode		= 1;
IPSText::getTextClass('bbcode')->parsing_section	= 'global';

if ( IPSText::getTextClass('editor')->method == 'rte' )
{
	$value = IPSText::getTextClass('bbcode')->convertForRTE( $value );
}
else
{
	$value = IPSText::getTextClass('bbcode')->preEditParse( $value );
}

$form['csb_content'] = IPSText::getTextClass('editor')->showEditor( $value, $key="csb_content" );

$title 	= ( $block ) ? $this->lang->words['editing']." : ".$block['csb_title'] : $this->lang->words['adding_block'];
$button = ( $block ) ? $this->lang->words['edit_block'] : $this->lang->words['add_block'];
	
$IPBHTML = "";
//--starthtml--//

$IPBHTML .= <<<HTML
<div class='section_title'>
	<h2>{$title}</h2>
</div>

<form id='adminform' action='{$this->settings['base_url']}{$this->form_code}&amp;do=add_block&amp;secure_key={$secure_key}' method='post' id='adform' name='adform' onsubmit='return checkform();'>
<input type='hidden' name='csb_id' value='{$block['csb_id']}' />
<input type='hidden' name='csb_title' value='{$block['csb_title']}' />

<ul id='tabstrip_block' class='tab_bar no_title'>
	<li id='tabtab-BLOCKS|1'>{$this->lang->words['general_config']}</li>
	<li id='tabtab-BLOCKS|2'>{$this->lang->words['perms']}</li>
</ul>

<script type="text/javascript">
//<![CDATA[
document.observe("dom:loaded",function() 
{
ipbAcpTabStrips.register('tabstrip_block');
});
 //]]>
</script>

<div class='acp-box'>
	<div id='tabpane-BLOCKS|1'>
		<table width='100%' cellpadding='0' cellspacing='0' class='form_table alternate_rows double_pad'>
			<tr>
				<th colspan='2'>{$this->lang->words['block_details']}</th>
			</tr>
			<tr>
				<td style='width: 40%'>
					<label>{$this->lang->words['block_title']}</label><br />{$this->lang->words['title_exp']}
				</td>
				<td style='width: 60%'>
					{$form['csb_title']}
				</td>
			</tr>
			<tr>
				<td style='width: 40%'>
					<label>{$this->lang->words['enabled']}?</label><br />{$this->lang->words['enabled_exp']}
				</td>
				<td style='width: 60%'>
					{$form['csb_on']}
				</td>
			</tr>			
			<tr>
				<th colspan='2'>{$this->lang->words['display_content']}</th>
			</tr>
			<tr>
				<td style='width: 40%'>
					<label>{$this->lang->words['image_name']}</label><br />{$this->lang->words['image_name_exp']}
				</td>
				<td style='width: 60%'>
					{$form['csb_image']}
				</td>
			</tr>
			<tr>
				<td style='width: 40%'>
					<label>{$this->lang->words['do_raw_html']}</label><br />{$this->lang->words['do_raw_html_exp']}
				</td>
				<td style='width: 60%'>
					{$form['csb_raw']}
				</td>
			</tr>
			<tr>
				<td style='width: 40%'>
					<label>{$this->lang->words['use_box']}?</label><br />{$this->lang->words['use_box_exp']}
				</td>
				<td style='width: 60%'>
					{$form['csb_use_box']}
				</td>
			</tr>
			<tr>
				<td colspan='2' style='width: 100%'>
					<label>{$this->lang->words['enter_content_below']}</label><br />{$this->lang->words['enter_content_below_exp']}
				</td>

			</tr>
			<tr>
				<td colspan='2' style='width: 100%'>
					{$form['csb_content']}
				</td>
			</tr>			
		</table>
 	</div>
	<div id='tabpane-BLOCKS|2'>
		<table class='form_table alternate_rows double_pad' cellspacing='0'>
			<tr>
				<td style='width: 40%'>
					<label>{$this->lang->words['use_perms']}?</label><br />{$this->lang->words['use_perms_exp']}
				</td>
				<td style='width: 60%'>
					{$form['csb_use_perms']}
				</td>
			</tr>
			<tr>
				<td style='width: 100%' colspan='2'>
                    {$perm_matrix}
				</td>
			</tr>		
		</table>
	</div>
    <div class='tableborder'>
		<table cellpadding='2' cellspacing='0' width='100%' border='0' class='tablerow1'>
			<tr style='text-align:center'>
				<td valign='middle'><input type='submit' class='realbutton' value=' {$button} ' /></td> 
			</tr>
		</table>
	</form>
    </div>
</div>

HTML;

//--endhtml--//
return $IPBHTML;
}

/**
 * Overview of Blocks
 */
public function blocksOverviewWrapper( $content ) {

$IPBHTML = "";
//--starthtml--//

$IPBHTML .= <<<HTML

<script type='text/javascript' language='javascript'>
//<![CDATA[
	ipb.templates['ajax_loading'] 	= "<div id='ajax_loading'>Loading...</div>";
	acp = new IPBACP;
//]]>
</script>

<div class='acp-box'>
        <div class='section_title'>
	<h2>{$this->lang->words['custom_sidebar_blocks']}</h2>
	<ul class='context_menu'>
		<li>
			<a href='{$this->settings['base_url']}{$this->form_code}&{$this->form_code_js}&do=block_form'>
				<img src='{$this->settings['skin_acp_url']}/_newimages/icons/application_add.png' alt='' />
				{$this->lang->words['add_block']}
			</a>
		</li>
		<li>
			<a href='{$this->settings['base_url']}{$this->form_code}&{$this->form_code_js}&do=recache&amp;human=yes'>
				<img src='{$this->settings['skin_acp_url']}/_newimages/icons/database_refresh.png' alt='' />
				{$this->lang->words['recache_blocks']}
			</a>
		</li>
	</ul>
        </div>
 	<h3>{$this->lang->words['blocks_page']}</h3>
	<div>
		<table width='100%' border='0' cellspacing='0' cellpadding='0' class='double_pad'>
			<tr>
				<td class='tablesubheader' style='width: 6%;'>&nbsp;</td>
				<td class='tablesubheader' style='width: 6%;'>{$this->lang->words['image']}</td>
				<td class='tablesubheader' style='width: 60%;'>{$this->lang->words['block_title']}</td>
				<td class='tablesubheader' style='width: 10%;'>{$this->lang->words['boxed']}?</td>
                <td class='tablesubheader' style='width: 10%;'>{$this->lang->words['enabled']}?</td>
				<td class='tablesubheader' style='width: 8%;'>{$this->lang->words['options']}</td>
			</tr>
		</table>
	</div>
	<ul id='sortable_handle' class='alternate_rows'>			
		{$content}				
	</ul>
</div>

<div class='tableborder'>
	<form id='adminform' method='post' action='{$this->settings['base_url']}{$this->form_code}&amp;do=block_form&amp;type=add' onsubmit='return ACPForums.submitModForm()'>
		<table cellpadding='2' cellspacing='0' width='100%' border='0' class='tablerow1'>
			<tr style='text-align:center'>
				<td valign='middle'><input type='submit' class='realbutton' value='{$this->lang->words['add_new_block']}' /></td>
			</tr>
		</table>
	</form>
</div>

<script type="text/javascript">
dropItLikeItsHot = function( draggableObject, mouseObject )
{
	var options = {
					method : 'post',
					parameters : Sortable.serialize( 'sortable_handle', { tag: 'li', name: 'blocks' } )
				};

	new Ajax.Request( "{$this->settings['base_url']}&{$this->form_code_js}&do=reorder&md5check={$this->registry->adminFunctions->getSecurityKey()}".replace( /&amp;/g, '&' ), options );

	return false;
};

Sortable.create( 'sortable_handle', { only: 'isDraggable', revert: true, format: 'block_([0-9]+)', onUpdate: dropItLikeItsHot, handle: 'draghandle' } );

</script>

HTML;

//--endhtml--//
return $IPBHTML;
}

/**
 * Block row
 */
public function blockRow( $r="" ) {

$r['csb_on_img']            = $r['csb_on'] ? 'accept.png' : 'cross.png';
$r['csb_use_box_img']       = $r['csb_use_box'] ? 'accept.png' : 'cross.png';
$r['csb_on_title']          = $r['csb_on'] ? $this->lang->words['block_enabled'] : $this->lang->words['block_disabled'];
$r['csb_use_box_title']     = $r['csb_use_box'] ? $this->lang->words['block_boxed'] : $this->lang->words['block_unboxed'];
$r['csb_image']             = $r['csb_image'] ? $r['csb_image'] : 'cross.png';

$IPBHTML = "";
//--starthtml--//

$IPBHTML .= <<<HTML

<li class='isDraggable' id='block_{$r['csb_id']}'>
	<table style='width: 100%' class='double_pad'>
		<tr>
			<td style='width: 6%'><div class='draghandle'><img src='http://testib3.ibbookie.com/admin/skin_cp/_newimages/drag_icon.png' alt='drag' /></div></td>		
			<td style='width: 6%;text-align:center'><a style='text-decoration:none;' href='{$this->settings['base_url']}{$this->form_code}&amp;do=block_form&amp;type=edit&amp;csb_id={$r['csb_id']}'><img src='{$this->settings['img_url']}/{$r['csb_image']}' border='0' class='ipbmenu' /></a></td>
			<td style='width: 57%'><span style='font-weight:bold;text-decoration:none;'><a style='text-decoration:none;' href='{$this->settings['base_url']}{$this->form_code}&amp;do=block_form&amp;type=edit&amp;csb_id={$r['csb_id']}'>{$r['csb_title']}</a></span></td>
			<td style='width: 10%;text-align:center'><img src='{$this->settings['skin_acp_url']}/_newimages/icons/{$r['csb_use_box_img']}' title='{$r['csb_use_box_title']}' border='0' alt='-' class='ipd' /></td> 			
            <td style='width: 10%;text-align:center'><img src='{$this->settings['skin_acp_url']}/_newimages/icons/{$r['csb_on_img']}' title='{$r['csb_on_title']}' border='0' alt='-' class='ipd' /></td> 
			<td style='width: 8%;text-align:center'>
				<img id="menu{$r['csb_id']}" src='{$this->settings['skin_acp_url']}/_newimages/menu_open.png' border='0' alt='{$this->lang->words['c_options']}' class='ipbmenu' />
				<ul class='acp-menu' id='menu{$r['csb_id']}_menucontent'>
					<li class='icon edit'><a href='{$this->settings['base_url']}{$this->form_code}&amp;do=block_form&amp;type=edit&amp;csb_id={$r['csb_id']}'>{$this->lang->words['edit_block']}</a></li>
					<li class='icon delete'><a href='{$this->settings['base_url']}{$this->form_code}&amp;do=delete&amp;csb_id={$r['csb_id']}' onclick="return acp.confirmDelete('');">{$this->lang->words['delete_block']}</a></li>
				</ul>
			</td>
		</tr>
	</table>
</li>

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

$IPBHTML .= <<<HTML
		<br /><br />
		<table cellpadding='2' cellspacing='0' width='100%' border='0' class='tablerow1'>
			<tr style='text-align:center;'>
				<td style='padding:5px;' valign='middle'>(e$30) Custom Sidebar Blocks {$this->caches['app_cache']['customSidebarBlocks']['app_version']} &copy; 2009 &nbsp;<a style='text-decoration:none' href='http://www.ibbookie.com' title='ibBookie.com - (e$) Mods'>emoney - (e$) Mods</a>.</td>
			</tr>
		</table>
HTML;

//--endhtml--//
return $IPBHTML;
}

}