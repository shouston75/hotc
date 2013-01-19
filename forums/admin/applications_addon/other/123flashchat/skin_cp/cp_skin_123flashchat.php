<?php
/**
 * Invision Power Services
 * IP.Board v3.0.1
 * 123Flashchat Skin File
 *
 * @author 		$Author: TopCMM $
 * @copyright	(c) 2001 - 2010 TopCMM, Inc.
 * @license		http://www.invisionpower.com/community/board/license.html
 * @package		Invision Power Board
 * @subpackage	123flashchat
 * @link		http://www.123flashchat.com
 */
 
class cp_skin_123flashchat extends output
{

/**
 * Prevent our main destructor being called by this class
 *
 * @access	public
 * @return	void
 */
public function __destruct()
{
}

/**
 * Form to add/edit a calendar
 *
 * @access	public
 * @param	array 		Form elements
 * @param	string		Form title
 * @param	string		Action code
 * @param	string		Button text
 * @param	array 		Calendar data
 * @return	string		HTML
 */
public function flashchatForm($form, $valid, $fc_row) {
$CSS_status_block = $fc_row['fc_status'] ? '' : 'style="display: none"';
$CSS_s_own = ($fc_row['fc_server'] == 0) ? '' : 'style="display: none"';
$CSS_s_fc = ($fc_row['fc_server'] == 1) ? '' : 'style="display: none"';
$CSS_s_free = ($fc_row['fc_server'] == 2) ? '' : 'style="display: none"';
$CSS_s_not_free = ($fc_row['fc_server'] == 2) ? 'style="display: none"' : '';
$CSS_s_local_valid = $valid ? '' : 'style="display: none"';
$CSS_c_skin = ($fc_row['fc_client'] == 0) ? '' : 'style="display: none"';

$IPBHTML = "";
//--starthtml--//

$IPBHTML .= <<<HTML
<div class='section_title'>
	<h2>{$this->lang->words['fc_settings']}</h2>
</div>

<script type="text/javascript">
function dE(id, s)
{
	var e = document.getElementById(id);
	if (!s)
	{
		s = (e.style.display == '') ? -1 : 1;
	}
	e.style.display = (s == 1) ? '' : 'none';
}

function serverChange(s)
{
    switch(s)
    {
        case 0:
            dE('room_list', 1);dE('s_own', 1);dE('s_fc', -1);dE('s_free', -1);dE('s_config', 1);dE('c', 1);dE('fc_client_full_span_out', 1);dE('s_auth', 1);
            break;
        case 1:
            dE('room_list', 1);dE('s_own', -1);dE('s_fc', 1);dE('s_free', -1);dE('s_config', -1);dE('c', 1);dE('fc_client_full_span_out', 1);dE('s_auth', 1);
            break;
        case 2:
            dE('room_list', -1);dE('s_own', -1);dE('s_fc', -1);dE('s_free', 1);dE('s_config', -1);dE('c', -1);document.getElementById("fc_client").options[0].selected = true;dE('c_skin', 1);dE('fc_client_full_span_out', -1);document.getElementById('fc_client_wh').checked=true;dE('s_auth', -1);
            break;
    }
}

function clientChange(s)
{
    switch(s)
    {
        case 0:
            dE('c_skin', 1);setClientSize(0);
            break;
        case 1:
            dE('c_skin', -1);setClientSize(1);
            break;
        case 2:
            dE('c_skin', -1);setClientSize(2);
            break;
    }
}

function setClientSize(n)
{ 
    var wh = [[634,476],[725,476],[800,600]];
	var c = {$this->settings['fc_client']};
	var c_w = '{$this->settings['fc_client_width']}';
	var c_h = '{$this->settings['fc_client_height']}';
    if (c_h != '100%')
    {
        wh[c] = [c_w,c_h];
    }
	if (document.getElementById('fc_client_wh').checked == true)
	{
		document.getElementById('fc_client_width').value = wh[n][0];
		document.getElementById('fc_client_height').value = wh[n][1];
	}
} 

</script>

<form id='adminform' action='{$this->settings['base_url']}&amp;{$this->form_code}&amp;do=save' method='post'>
	<input type='hidden' name='_admin_auth_key' value='{$this->registry->getClass('adminFunctions')->_admin_auth_key}' />
	
	<div class='acp-box'>
		<h3>{$this->lang->words['fc_settings']}</h3>
		
		<ul class='acp-form alternate_rows'>
			{$form['fc_error']}
			<li><label class='head'>{$this->lang->words['fc_basic']}</label></li>

			<li>
				<label>{$this->lang->words['fc_server']}<span class='desctext'>{$this->lang->words['fc_server_info']}</span></label>
				{$form['fc_server']}
			</li>
			<span {$CSS_s_local_valid}>
			<li id="s_own" {$CSS_s_own}>
				<span class='desctext' style='color:#7A2929;'>{$this->lang->words['fc_server_own_info']} <a href="{$form['fc_server_own_url']}" target="_blank"> Free Download</a></span>
			</li>
			</span>
			<span id="s_fc" {$CSS_s_fc}>
			<li>
				<span class='desctext' style='color:#7A2929;'>{$this->lang->words['fc_server_fc_info']}<a href="http://www.123flashchat.com/host.html" target="_blank"> Buy Now</a></span>
			</li>
			<li>
				<label>{$this->lang->words['fc_client_loc']}<span class='desctext'>{$this->lang->words['fc_client_loc_info']}</span></label>
				{$form['fc_client_loc']}
			</li>
			</span>
			<li id="s_free" {$CSS_s_free}>
				<label>{$this->lang->words['fc_room']}<span class='desctext'>{$this->lang->words['fc_room_info']}</span></label>
				{$form['fc_room']}
			</li>
			<li id="s_auth" {$CSS_s_not_free}>
				<span class='desctext' style='color:#7A2929;'>{$this->lang->words['fc_auth_url_info']} <br> {$form['fc_auth_url']}</span>
				
			</li>
			<li>
				<label>{$this->lang->words['fc_status']}<span class='desctext'>{$this->lang->words['fc_status_info']}</span></label>
				{$form['fc_status']}
			</li>
			<span id="status_block" {$CSS_status_block}>
			<li id="room_list" {$CSS_s_not_free}>
				<label>{$this->lang->words['fc_room_list']}<span class='desctext'>{$this->lang->words['fc_room_list_info']}</span></label>
				{$form['fc_room_list']}
			</li>
			<li>
				<label>{$this->lang->words['fc_user_list']}<span class='desctext'>{$this->lang->words['fc_user_list_info']}</span></label>
				{$form['fc_user_list']}
			</li>
			</span>

			<li><label class='head'>{$this->lang->words['fc_advanced']}</label></li>
			<span {$CSS_s_local_valid}>
            <input type="hidden" name="fc_server_local" value="{$valid}">
			<span id="s_config" {$CSS_s_own}>
			<li>
				<label>{$this->lang->words['fc_server_host']}</label>
				{$form['fc_server_host']}
			</li>
			<li>
				<label>{$this->lang->words['fc_server_port']}</label>
				{$form['fc_server_port']}
			</li>
			<li>
				<label>{$this->lang->words['fc_http_port']}</label>
				{$form['fc_http_port']}
			</li>
			</span></span>
			<li id="c" {$CSS_s_not_free}>
				<label>{$this->lang->words['fc_client']}<span class='desctext'>{$this->lang->words['fc_client_info']}</span></label>
				{$form['fc_client']}
			</li>
			<li>
				<label>{$this->lang->words['fc_client_present']}<span class='desctext'>{$this->lang->words['fc_client_present_info']}</span></label>
				{$form['fc_client_present']}
			</li>
			<li>
				<label>{$this->lang->words['fc_client_size']}</label>
				{$form['fc_client_size']}
			</li>
			<li>
				<label>{$this->lang->words['fc_client_lang']}</label>
				{$form['fc_client_lang']}
			</li>
			<li id="c_skin" {$CSS_c_skin}>
				<label>{$this->lang->words['fc_client_skin']}</label>
				{$form['fc_client_skin']}
			</li>
			<li>
				<label><a href="http://www.123flashchat.com/ipb-post-notifier.html" target="_blank">{$this->lang->words['fc_post_notifier']}</a><span class='desctext'>{$this->lang->words['fc_post_notifier_info']}</span></label>
				<a href="http://www.123flashchat.com/ipb-post-notifier.html" target="_blank">http://www.123flashchat.com/ipb-post-notifier.html</a>
			</li>
 		</ul>
 		
 		<div class='acp-actionbar'>
 			<div class='centeraction'>
 				<input type='submit' class='button primary' value='save' />
 			</div>
 		</div>
	</div>
</form>
HTML;

//--endhtml--//
return $IPBHTML;
}

/**
 * RSS recurring event
 *
 * @access	public
 * @param	array 		Event data
 * @return	string		HTML
 */
public function flashchat_admin_panel( $url ) {

$IPBHTML = "";
//--starthtml--//

$IPBHTML .= <<<HTML
<div class='section_title'>
	<h2>{$this->lang->words['fc_admin']}</h2>
</div>
HTML;
if ($this->settings['fc_server'] != 2){
$IPBHTML .= <<<HTML
<object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,19,0" id="topcmm_123flashchat"  type="application/x-shockwave-flash" width="100%" height="600">
    <param name=movie value="{$url}">
    <param name=quality value="high">
    <param name="menu" value="false">
    <param name="allowScriptAccess" value="always">
    <embed src="{$url}" quality="high" menu="false" allowScriptAccess="always" width="100%" height="600" type="application/x-shockwave-flash" pluginspace="http://www.macromedia.com/go/getflashplayer" name="topcmm_123flashchat"></embed>
</object>
<script src="{$this->settings['fc_client_loc']}123flashchat.js"></script>
<br />
HTML;
}else{
$IPBHTML .= <<<HTML
<p>{$this->lang->words['fc_admin_free']}</p>
<br />
<a href="http://www.123flashchat.com/admin-panel-free.html" target="_blank">Live Demo</a>
<br />
HTML;
}
//--endhtml--//
return $IPBHTML;
}

}
