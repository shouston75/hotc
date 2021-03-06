<?php
/*--------------------------------------------------*/
/* FILE GENERATED BY INVISION POWER BOARD 3         */
/* CACHE FILE: Skin set id: 0               */
/* CACHE FILE: Generated: Tue, 04 Aug 2009 19:56:23 GMT */
/* DO NOT EDIT DIRECTLY - THE CHANGES WILL NOT BE   */
/* WRITTEN TO THE DATABASE AUTOMATICALLY            */
/*--------------------------------------------------*/

class skin_editors_0 {

/**
* Construct
*/
function __construct( ipsRegistry $registry )
{
	/* Make object */
	$this->registry   =  $registry;
	$this->DB         =  $this->registry->DB();
	$this->settings   =& $this->registry->fetchSettings();
	$this->request    =& $this->registry->fetchRequest();
	$this->lang       =  $this->registry->getClass('class_localization');
	$this->member     =  $this->registry->member();
	$this->memberData =& $this->registry->member()->fetchMemberData();
	$this->cache      =  $this->registry->cache();
	$this->caches     =& $this->registry->cache()->fetchCaches();
}
	/* -- ajaxEditBox --*/
function ajaxEditBox($post="", $pid=0, $error_msg="", $extraData) {
$IPBHTML = "";
$IPBHTML .= "" . (($error_msg) ? ("
	<p id='error_msg_e{$pid}' class='message error'>{$error_msg}</p>
") : ("
	<p id='error_msg_e{$pid}' class='message error' style='display: none'>{$error_msg}</p>
")) . "
<input type='hidden' name='editor_ids[]' value='e{$pid}' />
<div class='ips_editor'>
	<div id='e{$pid}_controls' class='controls'>
		<ul id='e{$pid}_toolbar_1' class='toolbar'>
			<li class='left'>
				<span id='e{$pid}_cmd_fontname' class='rte_control rte_menu rte_font' title='{$this->lang->words['box_font']}'>{$this->lang->words['box_font']}</span>
			</li>
			<li class='left sep'>
				<span id='e{$pid}_cmd_fontsize' class='rte_control rte_menu rte_fontsize' title='{$this->lang->words['box_size']}'>{$this->lang->words['box_size']}</span>
			</li>
		</ul>
		<ul id='e{$pid}_toolbar_2' class='toolbar'>
			<li class='left'>
				<span id='e{$pid}_cmd_bold' class='rte_control rte_button' title='{$this->lang->words['js_tt_bold']}'><img src='{$this->settings['img_url']}/rte_icons/bold.png' alt='{$this->lang->words['js_tt_bold']}' /></span>
			</li>
			<li class='left'>
				<span id='e{$pid}_cmd_italic' class='rte_control rte_button' title='{$this->lang->words['js_tt_italic']}'><img src='{$this->settings['img_url']}/rte_icons/italic.png' alt='{$this->lang->words['js_tt_italic']}' /></span>
			</li>
			<li class='left'>
				<span id='e{$pid}_cmd_underline' class='rte_control rte_button' title='{$this->lang->words['js_tt_underline']}'><img src='{$this->settings['img_url']}/rte_icons/underline.png' alt='{$this->lang->words['js_tt_underline']}' /></span>
			</li>
			<li class='sep'>
				<span id='e{$pid}_cmd_strikethrough' class='rte_control rte_button' title='{$this->lang->words['js_tt_strike']}'><img src='{$this->settings['img_url']}/rte_icons/strike.png' alt='{$this->lang->words['js_tt_strike']}' /></span>
			</li>
		" . (($this->settings['_remove_emoticons']==0) ? ("
			<li>
				<span id='e{$pid}_cmd_emoticons' class='rte_control rte_palette' title='{$this->lang->words['js_tt_emoticons']}'><img src='{$this->settings['img_url']}/rte_icons/emoticons.png' alt='{$this->lang->words['js_tt_emoticons']}' /></span>
			</li>
		") : ("")) . "
			<li>
				<span id='e{$pid}_cmd_link' class='rte_control rte_palette' title='{$this->lang->words['js_tt_link']}'><img src='{$this->settings['img_url']}/rte_icons/link.png' alt='{$this->lang->words['js_tt_link']}' /></span>
			</li>
			<li>
				<span id='e{$pid}_cmd_image' class='rte_control rte_palette' title='{$this->lang->words['js_tt_image']}'><img src='{$this->settings['img_url']}/rte_icons/picture.png' alt='{$this->lang->words['js_tt_image']}' /></span>
			</li>
			<li>
				<span id='e{$pid}_cmd_email' class='rte_control rte_palette' title='{$this->lang->words['js_tt_email']}'><img src='{$this->settings['img_url']}/rte_icons/email.png' alt='{$this->lang->words['js_tt_email']}' /></span>
			</li>
			<li>
				<span id='e{$pid}_cmd_ipb_quote' class='rte_control rte_button' title='{$this->lang->words['js_tt_quote']}'><img src='{$this->settings['img_url']}/rte_icons/quote.png' alt='{$this->lang->words['js_tt_quote']}' /></span>
			</li>
			<li>
				<span id='e{$pid}_cmd_ipb_code' class='rte_control rte_button' title='{$this->lang->words['js_tt_code']}'><img src='{$this->settings['img_url']}/rte_icons/code.png' alt='{$this->lang->words['js_tt_code']}' /></span>
			</li>
			<li>
				<span id='e{$pid}_cmd_media' class='rte_control rte_palette' title='{$this->lang->words['js_tt_media']}'><img src='{$this->settings['img_url']}/rte_icons/media.png' alt='{$this->lang->words['js_tt_media']}' /></span>
			</li>
		</ul>
	</div>
	<div id='e{$pid}_wrap' class='editor'>
		<textarea name=\"Post\" class=\"input_rte\" id=\"e{$pid}_textarea\" rows=\"10\" cols=\"60\" tabindex='0'>{$post}</textarea>
	</div>
	<fieldset class='submit'>	
		" . (($extraData['showEditOptions']) ? ("<div style='text-align: left'>
					" . (($extraData['showReason']) ? ("
						{$this->lang->words['reason_for_edit']} <input type='text' size='35' maxlength='250' class='input_text' id='post_edit_reason' name='post_edit_reason' value='{$extraData['reasonForEdit']}' />
					") : ("")) . "
					<input type='checkbox' name='add_edit' id='add_edit' " . (($extraData['append_edit']) ? ("checked='checked'") : ("")) . " value='1' /> <label for='add_edit'>{$this->lang->words['show_edited_by']}</label>
			</div>
			<br />") : ("")) . "
		<input type='submit' value='{$this->lang->words['save_changes']}' class='input_submit' id='edit_save_e{$pid}' tabindex='1' /> <input type='submit' value='{$this->lang->words['use_full_editor']}' class='input_submit alt' id='edit_switch_e{$pid}' /> {$this->lang->words['or']} <a href='#' title='{$this->lang->words['cancel']}' class='cancel' id='edit_cancel_e{$pid}'>{$this->lang->words['cancel']}</a>
	</fieldset>
</div>";
return $IPBHTML;
}

/* -- editorJS --*/
function editorJS($emoticons='') {
$IPBHTML = "";
$IPBHTML .= "ipb.editor_values.get('templates')['link'] = new Template(\"<label for='#{id}_url'>{$this->lang->words['js_template_url']}</label><input type='text' class='input_text' id='#{id}_url' value='http://' tabindex='10' /><label for='#{id}_urltext'>{$this->lang->words['js_template_link']}</label><input type='text' class='input_text _select' id='#{id}_urltext' value='{$this->lang->words['js_template_default']}' tabindex='11' /><input type='submit' class='input_submit' value='{$this->lang->words['js_template_insert_link']}' tabindex='12' />\");
ipb.editor_values.get('templates')['image'] = new Template(\"<label for='#{id}_img'>{$this->lang->words['js_template_imageurl']}</label><input type='text' class='input_text' id='#{id}_img' value='http://' tabindex='10' /><input type='submit' class='input_submit' value='{$this->lang->words['js_template_insert_img']}' tabindex='11' />\");
ipb.editor_values.get('templates')['email'] = new Template(\"<label for='#{id}_email'>{$this->lang->words['js_template_email_url']}</label><input type='text' class='input_text' id='#{id}_email' tabindex='10' /><label for='#{id}_emailtext'>{$this->lang->words['js_template_link']}</label><input type='text' class='input_text _select' id='#{id}_emailtext' value='{$this->lang->words['js_template_email_me']}' tabindex='11' /><input type='submit' class='input_submit' value='{$this->lang->words['js_template_insert_email']}' tabindex='12' />\");
ipb.editor_values.get('templates')['media'] = new Template(\"<label for='#{id}_media'>{$this->lang->words['js_template_media_url']}</label><input type='text' class='input_text' id='#{id}_media' value='http://' tabindex='10' /><input type='submit' class='input_submit' value='{$this->lang->words['js_template_insert_media']}' tabindex='11' />\");
ipb.editor_values.get('templates')['generic'] = new Template(\"<div class='rte_title'>#{title}</div><strong>{$this->lang->words['js_template_example']}</strong><pre>#{example}</pre><label for='#{id}_option' class='optional'>#{option_text}</label><input type='text' class='input_text optional' id='#{id}_option' tabindex='10' /><label for='#{id}_text'>#{value_text}</label><input type='text' class='input_text _select' id='#{id}_text' tabindex='11' /><input type='submit' class='input_submit' value='{$this->lang->words['js_template_add']}' tabindex='12' />\");
ipb.editor_values.get('templates')['togglesource'] = new Template(\"<fieldset id='#{id}_ts_controls' class='submit' style='text-align: left'><input type='button' class='input_submit' value='{$this->lang->words['js_template_update']}' id='#{id}_ts_update' />&nbsp;&nbsp;&nbsp; <a href='#' id='#{id}_ts_cancel' class='cancel'>{$this->lang->words['js_template_cancel_source']}</a></fieldset>\");
	
ipb.editor_values.get('templates')['toolbar'] = new Template(\"<ul id='#{id}_toolbar_#{toolbarid}' class='toolbar' style='display: none'>#{content}</ul>\");
ipb.editor_values.get('templates')['button'] = new Template(\"<li><span id='#{id}_cmd_custom_#{cmd}' class='rte_control rte_button specialitem' title='#{title}'><img src='{$this->settings['img_url']}/rte_icons/#{img}' alt='{$this->lang->words['icon']}' /></span></li>\");
ipb.editor_values.get('templates')['menu_item'] = new Template(\"<li id='#{id}_cmd_custom_#{cmd}' class='specialitem clickable'>#{title}</li>\");
ipb.editor_values.get('templates')['togglesource'] = new Template(\"<fieldset id='#{id}_ts_controls' class='submit' style='text-align: left'><input type='button' class='input_submit' value='{$this->lang->words['js_template_update']}' id='#{id}_ts_update' />&nbsp;&nbsp;&nbsp; <a href='#' id='#{id}_ts_cancel' class='cancel'>{$this->lang->words['js_template_cancel_source']}</a></fieldset>\");
ipb.editor_values.get('templates')['emoticons_showall'] = new Template(\"<input class='input_submit emoticons' type='button' id='#{id}_all_emoticons' value='{$this->lang->words['show_all_emoticons']}' />\");
ipb.editor_values.get('templates')['emoticon_wrapper'] = new Template(\"<h4><span>{$this->lang->words['emoticons_template_title']}</span></h4><div id='#{id}_emoticon_holder' class='emoticon_holder'></div>\");

// Add smilies into the mix
ipb.editor_values.set( 'show_emoticon_link', false );
" . (($emoticons != '') ? ("
	ipb.editor_values.set( 'emoticons', \$H({ $emoticons }) );
") : ("")) . "
ipb.editor_values.set( 'bbcodes', \$H(" . IPSLib::fetchBbcodeAsJson() . ") );
	ipb.vars['emoticon_url'] = \"{$this->settings['emoticons_url']}\";";
return $IPBHTML;
}

/* -- editorShell --*/
function editorShell($editor_id, $field='Post', $content='', $no_sidebar=1, $lightweight=0) {
$IPBHTML = "";
$IPBHTML .= "<div class='ips_editor " . ((!$no_sidebar) ? ("with_sidebar") : ("no_sidebar")) . "'>
		<div class='sidebar row1 altrow' style='display: none'></div>
		<div id='{$editor_id}_controls' class='controls'>
			" . ((!$lightweight) ? ("<ul id='{$editor_id}_toolbar_1' class='toolbar' style='display: none'>
					<li class='left'>
						<span id='{$editor_id}_cmd_removeformat' class='rte_control rte_button' title='{$this->lang->words['js_tt_noformat']}'><img src='{$this->settings['img_url']}/rte_icons/remove_formatting.png' alt='{$this->lang->words['js_tt_noformat']}' /></span>
					</li>
					<li class='left'>
						<span id='{$editor_id}_cmd_togglesource' class='rte_control rte_button' title='{$this->lang->words['js_tt_htmlsource']}'><img src='{$this->settings['img_url']}/rte_icons/toggle_source.png' alt='{$this->lang->words['js_tt_htmlsource']}' /></span>
					</li>
					<li class='left'>
						<span id='{$editor_id}_cmd_otherstyles' class='rte_control rte_menu rte_special' title='{$this->lang->words['box_other']}' style='display: none'>{$this->lang->words['box_other']}</span>
					</li>
					<li class='left'>
						<span id='{$editor_id}_cmd_fontname' class='rte_control rte_menu rte_font' title='{$this->lang->words['box_font']}'>{$this->lang->words['box_font']}</span>
					</li>
					<li class='left'>
						<span id='{$editor_id}_cmd_fontsize' class='rte_control rte_menu rte_fontsize' title='{$this->lang->words['box_size']}'>{$this->lang->words['box_size']}</span>
					</li>
					<li class='left'>
						<span id='{$editor_id}_cmd_forecolor' class='rte_control rte_palette' title='{$this->lang->words['js_tt_font_col']}'><img src='{$this->settings['img_url']}/rte_icons/font_color.png' alt='{$this->lang->words['js_tt_font_col']}' /></span>
					</li>
					<!--<li class='left'>
						<span id='{$editor_id}_cmd_backcolor' class='rte_control rte_palette' title='{$this->lang->words['js_tt_back_col']}'><img src='{$this->settings['img_url']}/rte_icons/background_color.png' alt='{$this->lang->words['js_tt_back_col']}' /></span>
					</li>-->
					<li class='right'>
						<span id='{$editor_id}_cmd_spellcheck' class='rte_control rte_button' title='{$this->lang->words['js_tt_spellcheck']}'><img src='{$this->settings['img_url']}/rte_icons/spellcheck.png' alt='{$this->lang->words['js_tt_spellcheck']}' /></span>
					</li>
					<li class='right'>
						<span id='{$editor_id}_cmd_help' class='rte_control rte_button' title='{$this->lang->words['js_tt_help']}'><a href='" . $this->registry->getClass('output')->formatUrl( $this->registry->getClass('output')->buildUrl( "app=forums&amp;module=extras&amp;section=legends&amp;do=bbcode", 'public','' ), "", "" ) . "' title='{$this->lang->words['js_tt_help']}'><img src='{$this->settings['img_url']}/rte_icons/help.png' alt='{$this->lang->words['js_tt_help']}' /></a></span>
					</li>
					<li class='right'>
						<span id='{$editor_id}_cmd_undo' class='rte_control rte_button' title='{$this->lang->words['js_tt_undo']}'><img src='{$this->settings['img_url']}/rte_icons/undo.png' alt='{$this->lang->words['js_tt_undo']}' /></span>
					</li>
					<li class='right'>
						<span id='{$editor_id}_cmd_redo' class='rte_control rte_button' title='{$this->lang->words['js_tt_redo']}'><img src='{$this->settings['img_url']}/rte_icons/redo.png' alt='{$this->lang->words['js_tt_redo']}' /></span>
					</li>
					" . (($this->settings['posting_allow_rte'] == 1) ? ("
						<li class='right'>
							<!--<span id='{$editor_id}_cmd_switcheditor' class='rte_control rte_button' title='{$this->lang->words['js_tt_switcheditor']}'><img src='{$this->settings['img_url']}/rte_icons/switch.png' alt='{$this->lang->words['js_tt_switcheditor']}' /></span>-->
						</li>
					") : ("")) . "
				</ul>") : ("")) . "
			<ul id='{$editor_id}_toolbar_2' class='toolbar' style='display: none'>
				<li>
					<span id='{$editor_id}_cmd_bold' class='rte_control rte_button' title='{$this->lang->words['js_tt_bold']}'><img src='{$this->settings['img_url']}/rte_icons/bold.png' alt='{$this->lang->words['js_tt_bold']}' /></span>
				</li>
				<li>
					<span id='{$editor_id}_cmd_italic' class='rte_control rte_button' title='{$this->lang->words['js_tt_italic']}'><img src='{$this->settings['img_url']}/rte_icons/italic.png' alt='{$this->lang->words['js_tt_italic']}' /></span>
				</li>
				<li>
					<span id='{$editor_id}_cmd_underline' class='rte_control rte_button' title='{$this->lang->words['js_tt_underline']}'><img src='{$this->settings['img_url']}/rte_icons/underline.png' alt='{$this->lang->words['js_tt_underline']}' /></span>
				</li>
				<li class='sep'>
					<span id='{$editor_id}_cmd_strikethrough' class='rte_control rte_button' title='{$this->lang->words['js_tt_strike']}'><img src='{$this->settings['img_url']}/rte_icons/strike.png' alt='{$this->lang->words['js_tt_strike']}' /></span>
				</li>
				" . ((!$lightweight) ? ("
					<li>
						<span id='{$editor_id}_cmd_subscript' class='rte_control rte_button' title='{$this->lang->words['js_tt_sub']}'><img src='{$this->settings['img_url']}/rte_icons/subscript.png' alt='{$this->lang->words['js_tt_sub']}' /></span>
					</li>
					<li class='sep'>
						<span id='{$editor_id}_cmd_superscript' class='rte_control rte_button' title='{$this->lang->words['js_tt_sup']}'><img src='{$this->settings['img_url']}/rte_icons/superscript.png' alt='{$this->lang->words['js_tt_sup']}' /></span>
					</li>
					<li>
						<span id='{$editor_id}_cmd_insertunorderedlist' class='rte_control rte_button' title='{$this->lang->words['js_tt_list']}'><img src='{$this->settings['img_url']}/rte_icons/unordered_list.png' alt='{$this->lang->words['js_tt_list']}' /></span>
					</li>
					<li class='sep'>
						<span id='{$editor_id}_cmd_insertorderedlist' class='rte_control rte_button' title='{$this->lang->words['js_tt_list']}'><img src='{$this->settings['img_url']}/rte_icons/ordered_list.png' alt='{$this->lang->words['js_tt_list']}' /></span>
					</li>
				") : ("")) . "
			" . (($this->settings['_remove_emoticons']==0) ? ("
				<li>
					<span id='{$editor_id}_cmd_emoticons' class='rte_control rte_palette' title='{$this->lang->words['js_tt_emoticons']}'><img src='{$this->settings['img_url']}/rte_icons/emoticons.png' alt='{$this->lang->words['js_tt_emoticons']}' /></span>
				</li>
			") : ("")) . "
				<li>
					<span id='{$editor_id}_cmd_link' class='rte_control rte_palette' title='{$this->lang->words['js_tt_link']}'><img src='{$this->settings['img_url']}/rte_icons/link.png' alt='{$this->lang->words['js_tt_link']}' /></span>
				</li>
				<li>
					<span id='{$editor_id}_cmd_image' class='rte_control rte_palette' title='{$this->lang->words['js_tt_image']}'><img src='{$this->settings['img_url']}/rte_icons/picture.png' alt='{$this->lang->words['js_tt_image']}' /></span>
				</li>
				<li>
					<span id='{$editor_id}_cmd_email' class='rte_control rte_palette' title='{$this->lang->words['js_tt_email']}'><img src='{$this->settings['img_url']}/rte_icons/email.png' alt='{$this->lang->words['js_tt_email']}' /></span>
				</li>
				<li>
					<span id='{$editor_id}_cmd_ipb_quote' class='rte_control rte_button' title='{$this->lang->words['js_tt_quote']}'><img src='{$this->settings['img_url']}/rte_icons/quote.png' alt='{$this->lang->words['js_tt_quote']}' /></span>
				</li>
				<li>
					<span id='{$editor_id}_cmd_ipb_code' class='rte_control rte_button' title='{$this->lang->words['js_tt_code']}'><img src='{$this->settings['img_url']}/rte_icons/code.png' alt='{$this->lang->words['js_tt_code']}' /></span>
				</li>
				<li>
					<span id='{$editor_id}_cmd_media' class='rte_control rte_palette' title='{$this->lang->words['js_tt_media']}'><img src='{$this->settings['img_url']}/rte_icons/media.png' alt='{$this->lang->words['js_tt_media']}' /></span>
				</li>
				<!--<li class='right'>
					<span id='{$editor_id}_cmd_justifyfull' class='rte_control rte_button' title='{$this->lang->words['js_tt_jfull']}'><img src='{$this->settings['img_url']}/rte_icons/align_full.png' alt='{$this->lang->words['js_tt_jfull']}' /></span>
				</li>-->
				" . ((!$lightweight) ? ("
					<li class='right'>
						<span id='{$editor_id}_cmd_justifyright' class='rte_control rte_button' title='{$this->lang->words['js_tt_right']}'><img src='{$this->settings['img_url']}/rte_icons/align_right.png' alt='{$this->lang->words['js_tt_right']}' /></span>
					</li>
					<li class='right'>
						<span id='{$editor_id}_cmd_justifycenter' class='rte_control rte_button' title='{$this->lang->words['js_tt_center']}'><img src='{$this->settings['img_url']}/rte_icons/align_center.png' alt='{$this->lang->words['js_tt_center']}' /></span>
					</li>
					<li class='right'>
						<span id='{$editor_id}_cmd_justifyleft' class='rte_control rte_button' title='{$this->lang->words['js_tt_left']}'><img src='{$this->settings['img_url']}/rte_icons/align_left.png' alt='{$this->lang->words['js_tt_left']}' /></span>
					</li>
					<li class='right sep'>
						<span id='{$editor_id}_cmd_indent' class='rte_control rte_button' title='{$this->lang->words['js_tt_indent']}'><img src='{$this->settings['img_url']}/rte_icons/indent.png' alt='{$this->lang->words['js_tt_indent']}' /></span>
					</li>
					<li class='right'>
						<span id='{$editor_id}_cmd_outdent' class='rte_control rte_button' title='{$this->lang->words['js_tt_outdent']}'><img src='{$this->settings['img_url']}/rte_icons/outdent.png' alt='{$this->lang->words['js_tt_outdent']}' /></span>
					</li>
				") : ("")) . "
			</ul>
		</div>
		<div id='{$editor_id}_wrap' class='editor'>
			<textarea name=\"{$field}\" class=\"input_rte\" id=\"{$editor_id}_textarea\" rows=\"10\" cols=\"60\" tabindex=\"0\">{$content}</textarea>
		</div>
		<div id='{$editor_id}_resizer' class='resizer'>
			<ul id='{$editor_id}_toolbar_3' class='toolbar'>
				<li class='right'>
					<span id='{$editor_id}_cmd_r_small' class='rte_control rte_button' title='{$this->lang->words['js_tt_resizesmall']}'><img src='{$this->settings['img_url']}/rte_icons/resize_small.png' alt='{$this->lang->words['js_tt_resizesmall']}' /></span>
				</li>
				<li class='right'>
					<span id='{$editor_id}_cmd_r_big' class='rte_control rte_button' title='{$this->lang->words['js_tt_resizebig']}'><img src='{$this->settings['img_url']}/rte_icons/resize_big.png' alt='{$this->lang->words['js_tt_resizebig']}' /></span>
				</li>
			</ul>
		</div>
	</div>
	<script type='text/javascript'>
		if( $( '{$editor_id}_toolbar_1' ) ){ $( '{$editor_id}_toolbar_1' ).show(); }
		if( $( '{$editor_id}_toolbar_2' ) ){ $( '{$editor_id}_toolbar_2' ).show(); }
	</script>";
return $IPBHTML;
}

/* -- ips_editor --*/
function ips_editor($form_field="",$initial_content="",$images_path="",$rte_mode=0,$editor_id='ed-0',$smilies='',$allow_sidebar=1) {
$IPBHTML = "";
$IPBHTML .= "<!-- RTE ON: $rte_mode -->
" . $this->registry->getClass('output')->addJSModule("editor", "0" ) . "
<!--top-->
<input type='hidden' name='{$editor_id}_wysiwyg_used' id='{$editor_id}_wysiwyg_used' value='0' />
<input type='hidden' name='editor_ids[]' value='{$editor_id}' />
<div class='ips_editor' id='editor_{$editor_id}'>
	<div class='sidebar row1 altrow' style='display: none'></div>
	<div id='{$editor_id}_controls' class='controls'>
		<ul id='{$editor_id}_toolbar_1' class='toolbar' style='display: none'>
			<li class='left'>
				<span id='{$editor_id}_cmd_removeformat' class='rte_control rte_button' title='{$this->lang->words['js_tt_noformat']}'><img src='{$this->settings['img_url']}/rte_icons/remove_formatting.png' alt='{$this->lang->words['js_tt_noformat']}' /></span>
			</li>
			<li class='left'>
				<span id='{$editor_id}_cmd_togglesource' class='rte_control rte_button' title='{$this->lang->words['js_tt_htmlsource']}'><img src='{$this->settings['img_url']}/rte_icons/toggle_source.png' alt='{$this->lang->words['js_tt_htmlsource']}' /></span>
			</li>
			<li class='left'>
				<span id='{$editor_id}_cmd_otherstyles' class='rte_control rte_menu rte_special' title='{$this->lang->words['box_other_desc']}' style='display: none'>{$this->lang->words['box_other']}</span>
			</li>
			<li class='left'>
				<span id='{$editor_id}_cmd_fontname' class='rte_control rte_menu rte_font' title='{$this->lang->words['box_font_desc']}'>{$this->lang->words['box_font']}</span>
			</li>
			<li class='left'>
				<span id='{$editor_id}_cmd_fontsize' class='rte_control rte_menu rte_fontsize' title='{$this->lang->words['box_size_desc']}'>{$this->lang->words['box_size']}</span>
			</li>
			<li class='left'>
				<span id='{$editor_id}_cmd_forecolor' class='rte_control rte_palette' title='{$this->lang->words['js_tt_font_col']}'><img src='{$this->settings['img_url']}/rte_icons/font_color.png' alt='{$this->lang->words['js_tt_font_col']}' /></span>
			</li>
			<!--<li class='left'>
				<span id='{$editor_id}_cmd_backcolor' class='rte_control rte_palette' title='{$this->lang->words['js_tt_back_col']}'><img src='{$this->settings['img_url']}/rte_icons/background_color.png' alt='{$this->lang->words['js_tt_back_col']}' /></span>
			</li>-->
			<li class='right'>
				<span id='{$editor_id}_cmd_spellcheck' class='rte_control rte_button' title='{$this->lang->words['js_tt_spellcheck']}'><img src='{$this->settings['img_url']}/rte_icons/spellcheck.png' alt='{$this->lang->words['js_tt_spellcheck']}' /></span>
			</li>
			<li class='right'>
				<span id='{$editor_id}_cmd_help' class='rte_control rte_button' title='{$this->lang->words['js_tt_help']}'><a href='" . $this->registry->getClass('output')->formatUrl( $this->registry->getClass('output')->buildUrl( "app=forums&amp;module=extras&amp;section=legends&amp;do=bbcode", 'public','' ), "", "" ) . "' title='{$this->lang->words['js_tt_help']}'><img src='{$this->settings['img_url']}/rte_icons/help.png' alt='{$this->lang->words['js_tt_help']}' /></a></span>
			</li>
			
			<li class='right sep'>
				<span id='{$editor_id}_cmd_undo' class='rte_control rte_button' title='{$this->lang->words['js_tt_undo']}'><img src='{$this->settings['img_url']}/rte_icons/undo.png' alt='{$this->lang->words['js_tt_undo']}' /></span>
			</li>
			<li class='right'>
				<span id='{$editor_id}_cmd_redo' class='rte_control rte_button' title='{$this->lang->words['js_tt_redo']}'><img src='{$this->settings['img_url']}/rte_icons/redo.png' alt='{$this->lang->words['js_tt_redo']}' /></span>
			</li>
			
		" . (($this->settings['posting_allow_rte'] == 1) ? ("
			<li class='right'>
				<!--<span id='{$editor_id}_cmd_switcheditor' class='rte_control rte_button' title='{$this->lang->words['js_tt_switcheditor']}'><img src='{$this->settings['img_url']}/rte_icons/switch.png' alt='{$this->lang->words['js_tt_switcheditor']}' /></span>-->
			</li>
		") : ("")) . "
		</ul>
		<ul id='{$editor_id}_toolbar_2' class='toolbar' style='display: none'>
			<li>
				<span id='{$editor_id}_cmd_bold' class='rte_control rte_button' title='{$this->lang->words['js_tt_bold']}'><img src='{$this->settings['img_url']}/rte_icons/bold.png' alt='{$this->lang->words['js_tt_bold']}' /></span>
			</li>
			<li>
				<span id='{$editor_id}_cmd_italic' class='rte_control rte_button' title='{$this->lang->words['js_tt_italic']}'><img src='{$this->settings['img_url']}/rte_icons/italic.png' alt='{$this->lang->words['js_tt_italic']}' /></span>
			</li>
			<li>
				<span id='{$editor_id}_cmd_underline' class='rte_control rte_button' title='{$this->lang->words['js_tt_underline']}'><img src='{$this->settings['img_url']}/rte_icons/underline.png' alt='{$this->lang->words['js_tt_underline']}' /></span>
			</li>
			<li class='sep'>
				<span id='{$editor_id}_cmd_strikethrough' class='rte_control rte_button' title='{$this->lang->words['js_tt_strike']}'><img src='{$this->settings['img_url']}/rte_icons/strike.png' alt='{$this->lang->words['js_tt_strike']}' /></span>
			</li>
			<li>
				<span id='{$editor_id}_cmd_subscript' class='rte_control rte_button' title='{$this->lang->words['js_tt_sub']}'><img src='{$this->settings['img_url']}/rte_icons/subscript.png' alt='{$this->lang->words['js_tt_sub']}' /></span>
			</li>
			<li class='sep'>
				<span id='{$editor_id}_cmd_superscript' class='rte_control rte_button' title='{$this->lang->words['js_tt_sup']}'><img src='{$this->settings['img_url']}/rte_icons/superscript.png' alt='{$this->lang->words['js_tt_sup']}' /></span>
			</li>
			<li>
				<span id='{$editor_id}_cmd_insertunorderedlist' class='rte_control rte_button' title='{$this->lang->words['js_tt_list']}'><img src='{$this->settings['img_url']}/rte_icons/unordered_list.png' alt='{$this->lang->words['js_tt_list']}' /></span>
			</li>
			<li class='sep'>
				<span id='{$editor_id}_cmd_insertorderedlist' class='rte_control rte_button' title='{$this->lang->words['js_tt_list']}'><img src='{$this->settings['img_url']}/rte_icons/ordered_list.png' alt='{$this->lang->words['js_tt_list']}' /></span>
			</li>			
		" . (($this->settings['_remove_emoticons'] == 0) ? ("
			<li>
				<span id='{$editor_id}_cmd_emoticons' class='rte_control rte_palette' title='{$this->lang->words['js_tt_emoticons']}'><img src='{$this->settings['img_url']}/rte_icons/emoticons.png' alt='{$this->lang->words['js_tt_emoticons']}' /></span>
			</li>
		") : ("")) . "
			<li>
				<span id='{$editor_id}_cmd_link' class='rte_control rte_palette' title='{$this->lang->words['js_tt_link']}'><img src='{$this->settings['img_url']}/rte_icons/link.png' alt='{$this->lang->words['js_tt_link']}' /></span>
			</li>
			<li>
				<span id='{$editor_id}_cmd_image' class='rte_control rte_palette' title='{$this->lang->words['js_tt_image']}'><img src='{$this->settings['img_url']}/rte_icons/picture.png' alt='{$this->lang->words['js_tt_image']}' /></span>
			</li>
			<li>
				<span id='{$editor_id}_cmd_email' class='rte_control rte_palette' title='{$this->lang->words['js_tt_email']}'><img src='{$this->settings['img_url']}/rte_icons/email.png' alt='{$this->lang->words['js_tt_email']}' /></span>
			</li>
			<li>
				<span id='{$editor_id}_cmd_ipb_quote' class='rte_control rte_button' title='{$this->lang->words['js_tt_quote']}'><img src='{$this->settings['img_url']}/rte_icons/quote.png' alt='{$this->lang->words['js_tt_quote']}' /></span>
			</li>
			<li>
				<span id='{$editor_id}_cmd_ipb_code' class='rte_control rte_button' title='{$this->lang->words['js_tt_code']}'><img src='{$this->settings['img_url']}/rte_icons/code.png' alt='{$this->lang->words['js_tt_code']}' /></span>
			</li>
			<li>
				<span id='{$editor_id}_cmd_media' class='rte_control rte_palette' title='{$this->lang->words['js_tt_media']}'><img src='{$this->settings['img_url']}/rte_icons/media.png' alt='{$this->lang->words['js_tt_media']}' /></span>
			</li>
			<li class='right'>
				<span id='{$editor_id}_cmd_justifyright' class='rte_control rte_button' title='{$this->lang->words['js_tt_right']}'><img src='{$this->settings['img_url']}/rte_icons/align_right.png' alt='{$this->lang->words['js_tt_right']}' /></span>
			</li>
			<li class='right'>
				<span id='{$editor_id}_cmd_justifycenter' class='rte_control rte_button' title='{$this->lang->words['js_tt_center']}'><img src='{$this->settings['img_url']}/rte_icons/align_center.png' alt='{$this->lang->words['js_tt_center']}' /></span>
			</li>
			<li class='right'>
				<span id='{$editor_id}_cmd_justifyleft' class='rte_control rte_button' title='{$this->lang->words['js_tt_left']}'><img src='{$this->settings['img_url']}/rte_icons/align_left.png' alt='{$this->lang->words['js_tt_left']}' /></span>
			</li>
			<li class='right sep'>
				<span id='{$editor_id}_cmd_indent' class='rte_control rte_button' title='{$this->lang->words['js_tt_indent']}'><img src='{$this->settings['img_url']}/rte_icons/indent.png' alt='{$this->lang->words['js_tt_indent']}' /></span>
			</li>
			<li class='right'>
				<span id='{$editor_id}_cmd_outdent' class='rte_control rte_button' title='{$this->lang->words['js_tt_outdent']}'><img src='{$this->settings['img_url']}/rte_icons/outdent.png' alt='{$this->lang->words['js_tt_outdent']}' /></span>
			</li>
		</ul>
	</div>
	<div id='{$editor_id}_wrap' class='editor'>
		<textarea name=\"{$form_field}\" class=\"input_rte\" id=\"{$editor_id}_textarea\" rows=\"10\" cols=\"60\" tabindex=\"0\">{$initial_content}</textarea>
	</div>
	<div id='{$editor_id}_resizer' class='resizer'>
		<ul id='{$editor_id}_toolbar_3' class='toolbar'>
			<li class='right'>
				<span id='{$editor_id}_cmd_r_small' class='rte_control rte_button' title='{$this->lang->words['js_tt_resizesmall']}'><img src='{$this->settings['img_url']}/rte_icons/resize_small.png' alt='{$this->lang->words['js_tt_resizesmall']}' /></span>
			</li>
			<li class='right'>
				<span id='{$editor_id}_cmd_r_big' class='rte_control rte_button' title='{$this->lang->words['js_tt_resizebig']}'><img src='{$this->settings['img_url']}/rte_icons/resize_big.png' alt='{$this->lang->words['js_tt_resizebig']}' /></span>
			</li>
		</ul>
	</div>
</div>
	
	
<!-- Toolpanes -->
<script type=\"text/javascript\">
//<![CDATA[
$('{$editor_id}_toolbar_1').show();
$('{$editor_id}_toolbar_2').show();
// Rikki: Had to remove <form>... </form> because Opera would see </form> and not pass the topic icons / hidden fields properly. Tried \"</\" + \"form>\" but when it is parsed, it had the same affect
ipb.editor_values.get('templates')['link'] = new Template(\"<label for='#{id}_url'>{$this->lang->words['js_template_url']}</label><input type='text' class='input_text' id='#{id}_url' value='http://' tabindex='10' /><label for='#{id}_urltext'>{$this->lang->words['js_template_link']}</label><input type='text' class='input_text _select' id='#{id}_urltext' value='{$this->lang->words['js_template_default']}' tabindex='11' /><input type='submit' class='input_submit' value='{$this->lang->words['js_template_insert_link']}' tabindex='12' />\");
ipb.editor_values.get('templates')['image'] = new Template(\"<label for='#{id}_img'>{$this->lang->words['js_template_imageurl']}</label><input type='text' class='input_text' id='#{id}_img' value='http://' tabindex='10' /><input type='submit' class='input_submit' value='{$this->lang->words['js_template_insert_img']}' tabindex='11' />\");
ipb.editor_values.get('templates')['email'] = new Template(\"<label for='#{id}_email'>{$this->lang->words['js_template_email_url']}</label><input type='text' class='input_text' id='#{id}_email' tabindex='10' /><label for='#{id}_emailtext'>{$this->lang->words['js_template_link']}</label><input type='text' class='input_text _select' id='#{id}_emailtext' value='{$this->lang->words['js_template_email_me']}' tabindex='11' /><input type='submit' class='input_submit' value='{$this->lang->words['js_template_insert_email']}' tabindex='12' />\");
ipb.editor_values.get('templates')['media'] = new Template(\"<label for='#{id}_media'>{$this->lang->words['js_template_media_url']}</label><input type='text' class='input_text' id='#{id}_media' value='http://' tabindex='10' /><input type='submit' class='input_submit' value='{$this->lang->words['js_template_insert_media']}' tabindex='11' />\");
ipb.editor_values.get('templates')['generic'] = new Template(\"<div class='rte_title'>#{title}</div><strong>{$this->lang->words['js_template_example']}</strong><pre>#{example}</pre><label for='#{id}_option' class='optional'>#{option_text}</label><input type='text' class='input_text optional' id='#{id}_option' tabindex='10' /><label for='#{id}_text' class='tagcontent'>#{value_text}</label><input type='text' class='input_text _select tagcontent' id='#{id}_text' tabindex='11' /><input type='submit' class='input_submit' value='{$this->lang->words['js_template_add']}' tabindex='12' />\");
ipb.editor_values.get('templates')['toolbar'] = new Template(\"<ul id='#{id}_toolbar_#{toolbarid}' class='toolbar' style='display: none'>#{content}</ul>\");
ipb.editor_values.get('templates')['button'] = new Template(\"<li><span id='#{id}_cmd_custom_#{cmd}' class='rte_control rte_button specialitem' title='#{title}'><img src='{$this->settings['img_url']}/rte_icons/#{img}' alt='{$this->lang->words['icon']}' /></span></li>\");
ipb.editor_values.get('templates')['menu_item'] = new Template(\"<li id='#{id}_cmd_custom_#{cmd}' class='specialitem clickable'>#{title}</li>\");
ipb.editor_values.get('templates')['togglesource'] = new Template(\"<fieldset id='#{id}_ts_controls' class='submit' style='text-align: left'><input type='button' class='input_submit' value='{$this->lang->words['js_template_update']}' id='#{id}_ts_update' />&nbsp;&nbsp;&nbsp; <a href='#' id='#{id}_ts_cancel' class='cancel'>{$this->lang->words['js_template_cancel_source']}</a></fieldset>\");
ipb.editor_values.get('templates')['emoticons_showall'] = new Template(\"<input class='input_submit emoticons' type='button' id='#{id}_all_emoticons' value='{$this->lang->words['show_all_emoticons']}' />\");
ipb.editor_values.get('templates')['emoticon_wrapper'] = new Template(\"<h4><span>{$this->lang->words['emoticons_template_title']}</span></h4><div id='#{id}_emoticon_holder' class='emoticon_holder'></div>\");
// Add smilies into the mix
ipb.editor_values.set( 'show_emoticon_link', " . (($allow_sidebar) ? ("true") : ("false")) . " );
ipb.editor_values.set( 'emoticons', \$H({ $smilies }) );
ipb.editor_values.set( 'bbcodes', \$H(" . IPSLib::fetchBbcodeAsJson() . ") );
ipb.vars['emoticon_url'] = \"{$this->settings['emoticons_url']}\";
ipb.editors[ '{$editor_id}' ] = new ipb.editor( '{$editor_id}', USE_RTE );
//]]>
</script>";
return $IPBHTML;
}



}

/*--------------------------------------------------*/
/* END OF FILE                                      */
/*--------------------------------------------------*/

?>