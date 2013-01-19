<?php
/*--------------------------------------------------*/
/* FILE GENERATED BY INVISION POWER BOARD 3         */
/* CACHE FILE: Skin set id: 15               */
/* CACHE FILE: Generated: Sat, 22 Dec 2012 18:11:15 GMT */
/* DO NOT EDIT DIRECTLY - THE CHANGES WILL NOT BE   */
/* WRITTEN TO THE DATABASE AUTOMATICALLY            */
/*--------------------------------------------------*/

class skin_ipchat_15 extends skinMaster{

/**
* Construct
*/
function __construct( ipsRegistry $registry )
{
	parent::__construct( $registry );
	

$this->_funcHooks = array();
$this->_funcHooks['ajaxNewUser'] = array('formatnameajax','hasname');
$this->_funcHooks['chatRoom'] = array('formatname','modlink','hasname','modendlink','nokickself','cookiesound','soundon','modmenus');
$this->_funcHooks['chatRules'] = array('showPopup');
$this->_funcHooks['ignoredUsersForm'] = array('members');


}

/* -- ajaxNewUser --*/
function ajaxNewUser($data=array()) {
$IPBHTML = "";
if( IPSLib::locationHasHooks( 'skin_ipchat', $this->_funcHooks['ajaxNewUser'] ) )
{
$count_4d0cf62b21d5c1a57c7556c8da43b7d2 = is_array($this->functionData['ajaxNewUser']) ? count($this->functionData['ajaxNewUser']) : 0;
$this->functionData['ajaxNewUser'][$count_4d0cf62b21d5c1a57c7556c8da43b7d2]['data'] = $data;
}
$IPBHTML .= "<div class='chat-photo'><a target='_blank' href=\"" . $this->registry->getClass('output')->formatUrl( $this->registry->getClass('output')->buildUrl( "showuser={$data['member']['member_id']}", "public",'' ), "{$data['member']['members_seo_name']}", "showuser" ) . "\" title='{$this->lang->words['view_profile']}' id='link_{$data['member']['member_id']}'><img src='{$data['member']['pp_mini_photo']}' alt=\"{$data['member']['members_display_name']}{$this->lang->words['users_photo']}\" width='{$data['member']['pp_mini_width']}' height='{$data['member']['pp_mini_height']}' class='photo' /></a></div>
	<span class='names'><a href='#' id='mod_link_{$data['user_id']}' class='ipbmenu'>" . (($data['member']['members_display_name']) ? ("<span title='{$data['member']['members_display_name']}'>" . (($this->settings['ipschat_format_names']) ? ("" . IPSLib::makeNameFormatted( IPSText::truncate( $data['member']['members_display_name'], 16 ), $data['member']['member_group_id'] ) . "") : ("" . IPSText::truncate( $data['member']['members_display_name'], 16 ) . "")) . "</span>") : ("{$data['user_name']}")) . "</a>
	" . ( method_exists( $this->registry->getClass('output')->getTemplate('global'), 'user_popup' ) ? $this->registry->getClass('output')->getTemplate('global')->user_popup($data['member']['pp_member_id'], $data['member']['members_seo_name']) : '' ) . "</span>";
return $IPBHTML;
}

/* -- chatRoom --*/
function chatRoom($options=array(), $chatters=array(), $emoticons) {
$IPBHTML = "";
if( IPSLib::locationHasHooks( 'skin_ipchat', $this->_funcHooks['chatRoom'] ) )
{
$count_8b3e3fd2836c875ddbb5adb619576133 = is_array($this->functionData['chatRoom']) ? count($this->functionData['chatRoom']) : 0;
$this->functionData['chatRoom'][$count_8b3e3fd2836c875ddbb5adb619576133]['options'] = $options;
$this->functionData['chatRoom'][$count_8b3e3fd2836c875ddbb5adb619576133]['chatters'] = $chatters;
$this->functionData['chatRoom'][$count_8b3e3fd2836c875ddbb5adb619576133]['emoticons'] = $emoticons;
}
$IPBHTML .= "<script type='text/javascript'>
        // Basic variables
        var roomId              = {$options['roomId']};
        var userId              = {$options['userId']};
        var userName    = '{$this->memberData['members_display_name']}';
        var accessKey   = '{$options['accessKey']}';
        var serverHost  = '{$options['serverHost']}';
        var serverPath  = '{$options['serverPath']}';
        var ourUrl              = '{$options['ourUrl']}';
        USE_RTE                 = 0;
        // Chat settings        
        ipb.chat.condenseTime           = parseInt({$this->settings['ipchat_limit_time']});
        ipb.chat.maxMessages            = parseInt({$this->settings['ipchat_max_messages']});
        ipb.chat.moderator                      = parseInt({$options['moderator']});
        
        // Set up templates
        ipb.chat.templates['msg-1']     = new Template( \"<li class='post chat-message #{ownclass}'><label>#{username}</label> <div>#{message}</div></li>\" );
        ipb.chat.templates['msg-2']     = new Template( \"<li class='post chat-notice'><label>#{username}</label> <div>#{action}</div></li>\" );
        ipb.chat.templates['msg-3']     = new Template( \"<li class='post chat-me'><label>#{username}</label> <div>**#{message}**</div></li>\" );
        ipb.chat.templates['msg-4']     = new Template( \"<li class='post chat-system'>{$this->lang->words['sys_message_pre']} #{message}</li>\" );
        ipb.chat.templates['msg-5']     = new Template( \"<li class='post chat-moderator'><label>#{username}</label> <div>{$this->lang->words['_kicked']} #{extra}</div></li>\" );
        ipb.chat.templates['msg-K']     = new Template( \"<li class='post chat-moderator'><div>{$this->lang->words['youve_been_kicked']}</div></li>\" );
        
        // Set some language vars
        ipb.lang['time_am']                             = \"{$this->lang->words['time_am']}\";
        ipb.lang['time_pm']                             = \"{$this->lang->words['time_pm']}\";
        ipb.lang['entered_room']                = \"{$this->lang->words['entered_room']}\";
        ipb.lang['left_room']                   = \"{$this->lang->words['left_room']}\";
        ipb.lang['chat_kick_user']              = \"{$this->lang->words['chat_kick_user']}\";
        ipb.lang['chat_ban_user']               = \"{$this->lang->words['chat_ban_user']}\";
        ipb.lang['cant_kick_self']              = \"{$this->lang->words['cant_kick_self']}\";
        ipb.lang['youve_been_kicked']   = \"{$this->lang->words['youve_been_kicked']}\";
        
        // Emoticons
        ipb.chat.emoticons                      = \$H({ " . IPSLib::fetchEmoticonsAsJson( $this->registry->output->skin['set_emo_dir'], true ) . " });
        ipb.vars['emoticon_url']        = \"{$this->settings['emoticons_url']}\";
        ipb.vars['img_url']                     = '{$this->settings['img_url']}';
        
        // Sound
        ipb.chat.soundEnabled           = " . ((IPSCookie::get('chat_sounds') == 'off') ? ("0") : ("1")) . ";
        ipb.chat.soundOnImg                     = \"{$this->settings['img_url']}/bell.png\";
        ipb.chat.soundOffImg            = \"{$this->settings['img_url']}/bell_off.png\";
        
        // Chatters name formatting
        ".$this->__f__a2495310fe6bfc2057269881a07f695b($options,$chatters,$emoticons)."</script>
<script type='text/javascript' src='{$this->settings['public_dir']}js/ips.editor.js'></script>
<h2 class='maintitle'>{$this->lang->words['chat_page_title']}</h2>
<div class='generic_bar'></div>
<div id='ipschat'>
        <div id='chat-container'>
                <div id='scrollbar_container'>
                        <div id='messages-display'><span id='initial_message'>{$this->lang->words['please_wait_chats']}</span></div>
                </div>
                <form id='chat-form' action='#' method='post'>
                        <input type='hidden' name='1_wysiwyg_used' id='1_wysiwyg_used' value='0' />
                        <input type='hidden' name='editor_ids[]' value='1' />
                        <div class='ips_editor' id='editor_message'>
                                <div id='message_controls' class='controls'>
                                        <ul id='message_toolbar_2' class='toolbar' style='display: none'>
                                                <li>
                                                        <span id='message_cmd_bold' class='rte_control rte_button' title='{$this->lang->words['js_tt_bold']}'><img src='{$this->settings['img_url']}/rte_icons/bold.png' alt='{$this->lang->words['js_tt_bold']}' /></span>
                                                </li>
                                                <li>
                                                        <span id='message_cmd_italic' class='rte_control rte_button' title='{$this->lang->words['js_tt_italic']}'><img src='{$this->settings['img_url']}/rte_icons/italic.png' alt='{$this->lang->words['js_tt_italic']}' /></span>
                                                </li>
                                                <li>
                                                        <span id='message_cmd_underline' class='rte_control rte_button' title='{$this->lang->words['js_tt_underline']}'><img src='{$this->settings['img_url']}/rte_icons/underline.png' alt='{$this->lang->words['js_tt_underline']}' /></span>
                                                </li>
                                                <li>
                                                        <span id='emoticons_custom_menu' class='ipbmenu rte_control rte_button' title='{$this->lang->words['js_tt_emoticons']}'><img src='{$this->settings['img_url']}/rte_icons/emoticons.png' alt='{$this->lang->words['js_tt_emoticons']}' /></span>
                                                </li>
                                                <li>
                                                        <span id='message_cmd_link' class='rte_control rte_palette' title='{$this->lang->words['js_tt_link']}'><img src='{$this->settings['img_url']}/rte_icons/link.png' alt='{$this->lang->words['js_tt_link']}' /></span>
                                                </li>
                                        </ul>
                                </div>
                                <div id='message_wrap' class='editor'>
                                        <textarea name=\"message\" class=\"input_rte\" id=\"message_textarea\" rows=\"3\" cols=\"50\" tabindex=\"0\"></textarea>
                                </div>
                        </div>
                        <input type='submit' id='chat-submit' class='input_submit' value='{$this->lang->words['chat_post_button']}' />
                </form>
        </div>
        <div id='chat-online' class='general_box alt'>
                <h3><span id='online-chat-count'>" . (($_count = count($chatters)) ? ("{$_count}") : ("0")) . "</span> {$this->lang->words['whos_chatting_ttl']}</h3>
                <div id='chatters-online-wrap'>
                        <ul id='chatters-online'>
                                ".$this->__f__5c385a759875167295178027d5e7f7ca($options,$chatters,$emoticons)."                        </ul>
                </div>
                <ul class='post_controls' id='chat-controls'>
                        <li><a href='" . $this->registry->getClass('output')->formatUrl( $this->registry->getClass('output')->buildUrl( "module=ipschat&amp;section=chat&amp;do=leave&amp;room={$options['roomId']}&amp;user={$options['userId']}&amp;access_key={$options['accessKey']}", "publicWithApp",'' ), "", "" ) . "' title='{$this->lang->words['leave_room']}'><img src='{$this->settings['img_url']}/cross.png' alt='{$this->lang->words['leave_room']}' /> {$this->lang->words['leave_room']}</a></li>
                        <li><a href='#' title='{$this->lang->words['toggle_sound']}' id='sound_toggle'><img id='sound_toggle_img' src='{$this->settings['img_url']}/" . ((IPSCookie::get('chat_sounds') == 'off') ? ("bell_off.png") : ("bell.png")) . "' alt='{$this->lang->words['toggle_sound']}' /></a></li>
                </ul>
        </div>
</div>
" . (($options['moderator']) ? ("
        <div id='mod-menu-container'>
                ".$this->__f__4f70e0f4182ad0856d9d74292179be01($options,$chatters,$emoticons)."        </div>
") : ("")) . "
<div id='emoticons_custom_menu_menucontent'>
        {$emoticons}
</div>
<script type='text/javascript'>
        if( $( 'message_toolbar_2' ) ){ $( 'message_toolbar_2' ).show(); }
        ipb.editor_values.get('templates')['link'] = new Template(\"<label for='#{id}_url'>{$this->lang->words['js_template_url']}</label><input type='text' class='input_text' id='#{id}_url' value='http://' tabindex='10' /><label for='#{id}_urltext'>{$this->lang->words['js_template_link']}</label><input type='text' class='input_text _select' id='#{id}_urltext' value='{$this->lang->words['js_template_default']}' tabindex='11' /><input type='submit' class='input_submit' value='{$this->lang->words['js_template_insert_link']}' tabindex='12' />\");
        ipb.editor_values.get('templates')['emoticons_showall'] = new Template(\"<input class='input_submit emoticons' type='button' id='#{id}_all_emoticons' value='{$this->lang->words['show_all_emoticons']}' />\");
        ipb.editor_values.get('templates')['emoticon_wrapper'] = new Template(\"<h4><span>{$this->lang->words['emoticons_template_title']}</span></h4><div id='#{id}_emoticon_holder' class='emoticon_holder'></div>\");
        ipb.editor_values.set( 'show_emoticon_link', false );
        ipb.editor_values.set( 'emoticons', ipb.chat.emoticons );
        ipb.editor_values.set( 'bbcodes', \$H(" . IPSLib::fetchBbcodeAsJson() . ") );
        ipb.vars['emoticon_url'] = \"{$this->settings['emoticons_url']}\";
        ipb.editors[ 'message' ] = new ipb.editor( 'message', 0 );
</script>
                        
<!--Iframes used for cross-domain \"AJAX\"-->
<div id='iframeContainer' style=\"display:none\">
</div>";
return $IPBHTML;
}


function __f__a2495310fe6bfc2057269881a07f695b($options=array(), $chatters=array(), $emoticons)
{
	$_ips___x_retval = '';
	$__iteratorCount = 0;
	foreach( $chatters as $data )
	{
		
		$__iteratorCount++;
		$_ips___x_retval .= "
                ipb.chat.nameFormatting.set( {$data['user_id']}, [ \"{$data['member']['prefix']}\", \"{$data['member']['suffix']}\" ] );
        
";
	}
	$_ips___x_retval .= '';
	unset( $__iteratorCount );
	return $_ips___x_retval;
}

function __f__5c385a759875167295178027d5e7f7ca($options=array(), $chatters=array(), $emoticons)
{
	$_ips___x_retval = '';
	$__iteratorCount = 0;
	foreach( $chatters as $data )
	{
		
		$__iteratorCount++;
		$_ips___x_retval .= "
                                        <li id='user_{$data['user_id']}'>
                                                <div class='chat-photo'><a target='_blank' href=\"" . $this->registry->getClass('output')->formatUrl( $this->registry->getClass('output')->buildUrl( "showuser={$data['member']['member_id']}", "public",'' ), "{$data['member']['members_seo_name']}", "showuser" ) . "\" title='{$this->lang->words['view_profile']}'><img src='{$data['member']['pp_mini_photo']}' alt=\"{$data['member']['members_display_name']}{$this->lang->words['users_photo']}\" width='{$data['member']['pp_mini_width']}' height='{$data['member']['pp_mini_height']}' class='photo' /></a></div>
                                                <span class='names'>" . (($options['moderator']) ? ("<a href='#' id='mod_link_{$data['user_id']}' class='ipbmenu'>") : ("")) . "" . (($data['member']['members_display_name']) ? ("<span title='{$data['member']['members_display_name']}'>" . IPSLib::makeNameFormatted( IPSText::truncate( $data['member']['members_display_name'], 16 ), $data['member']['member_group_id'] ) . "</span>") : ("{$data['user_name']}")) . "" . (($options['moderator']) ? ("</a>") : ("")) . "
                                                " . ( method_exists( $this->registry->getClass('output')->getTemplate('global'), 'user_popup' ) ? $this->registry->getClass('output')->getTemplate('global')->user_popup($data['member']['pp_member_id'], $data['member']['members_seo_name']) : '' ) . "</span>
                                        </li>
                                
";
	}
	$_ips___x_retval .= '';
	unset( $__iteratorCount );
	return $_ips___x_retval;
}

function __f__4f70e0f4182ad0856d9d74292179be01($options=array(), $chatters=array(), $emoticons)
{
	$_ips___x_retval = '';
	$__iteratorCount = 0;
	foreach( $chatters as $data )
	{
		
		$__iteratorCount++;
		$_ips___x_retval .= "
                        <ul id='mod_link_{$data['user_id']}_menucontent' class='kickmenu'>
                                " . (($data['user_id'] == $options['userId']) ? ("
                                        <li><em>{$this->lang->words['cant_kick_self']}</em></li>
                                ") : ("
                                        <li><a href='#' title='{$this->lang->words['chat_kick_user']}' class='kick_user' id='kick_user_{$data['user_id']}'><img src='{$this->settings['img_url']}/user_delete.png' alt='{$this->lang->words['icon']}' /> {$this->lang->words['chat_kick_user']}</a></li>
                                        <li><a href='#' title='{$this->lang->words['chat_ban_user']}' class='ban_user' id='ban_user_{$data['user_id']}_{$data['member']['member_id']}'><img src='{$this->settings['img_url']}/user_delete.png' alt='{$this->lang->words['icon']}' /> {$this->lang->words['chat_ban_user']}</a></li>
                                ")) . "
                        </ul>
                
";
	}
	$_ips___x_retval .= '';
	unset( $__iteratorCount );
	return $_ips___x_retval;
}

/* -- chatRules --*/
function chatRules($rules) {
$IPBHTML = "";
if( IPSLib::locationHasHooks( 'skin_ipchat', $this->_funcHooks['chatRules'] ) )
{
$count_99e5c15262a1640aa05e5b7ec6d18014 = is_array($this->functionData['chatRules']) ? count($this->functionData['chatRules']) : 0;
$this->functionData['chatRules'][$count_99e5c15262a1640aa05e5b7ec6d18014]['rules'] = $rules;
}
$IPBHTML .= "<form action='" . $this->registry->getClass('output')->formatUrl( $this->registry->getClass('output')->buildUrl( "app=ipchat", "public",'' ), "false", "app=ipchat" ) . "' method='post'>
	<input type='hidden' name='agree' value='1' />
	" . (($this->request['_popup']) ? ("
		<input type='hidden' name='_popup' value='1' />
	") : ("")) . "	
	<h2>{$this->lang->words['chat_rules_title']}</h2>
	<div class='general_box'>
		<h3>{$this->lang->words['chat_rules_agree']}</h3>
		<p><br />{$rules}</p>
	</div>
	<fieldset class='submit'>
		<input type='submit' value='{$this->lang->words['chat_agree_button']}' class=\"input_submit\" />
	</fieldset>
	</form>";
return $IPBHTML;
}

/* -- ignoredUsersForm --*/
function ignoredUsersForm($members) {
$IPBHTML = "";
if( IPSLib::locationHasHooks( 'skin_ipchat', $this->_funcHooks['ignoredUsersForm'] ) )
{
$count_4b01f4ae24f7aaf4cd66221e2a12d61c = is_array($this->functionData['ignoredUsersForm']) ? count($this->functionData['ignoredUsersForm']) : 0;
$this->functionData['ignoredUsersForm'][$count_4b01f4ae24f7aaf4cd66221e2a12d61c]['members'] = $members;
}

if ( ! isset( $this->registry->templateStriping['members'] ) ) {
$this->registry->templateStriping['members'] = array( FALSE, "row1","row2");
}
$IPBHTML .= "<script type='text/javascript'>
//<![CDATA[
	ipb.templates['autocomplete_wrap'] = new Template(\"<ul id='#{id}' class='ipbmenu_content' style='width: 250px;'></ul>\");
	ipb.templates['autocomplete_item'] = new Template(\"<li id='#{id}'><img src='#{img}' alt='' width='#{img_w}' height='#{img_h}' />&nbsp;&nbsp;#{itemvalue}</li>\");
//]]>
</script>
<fieldset class='row1'>
	<h3 class='maintitle'>{$this->lang->words['mi5_title']}</h3>
	<table class='ipb_table' summary=\"{$this->lang->words['ucp_ignored_users']}\">
		<tr class='header'>
			<th scope='col' width=\"50%\">{$this->lang->words['mi5_name']}</th>
			<th scope='col' width=\"30%\">{$this->lang->words['mi5_group']}</th>
			<th scope='col' width=\"10%\">{$this->lang->words['mi5_posts']}</th>
			<th scope='col' width=\"10%\">&nbsp;</th>
		</tr>
				".$this->__f__faa36856aa9ee843695aa8109e9a11d1($members)."	</table>
</fieldset>
<fieldset class=\"row2\">
	<h3>{$this->lang->words['mi5_addem']}</h3>
	<ul>
		<li class='field'>
			<label for='newbox_1'>{$this->lang->words['ucp_members_name']}</label>
			<input type=\"text\" size='40' name=\"newbox_1\" id=\"newbox_1\" value=\"{$this->request['newbox_1']}\" />
		</li>
	</ul>
</fieldset>
<script type=\"text/javascript\">
	document.observe(\"dom:loaded\", function(){
		var url = ipb.vars['base_url'] + 'app=core&module=ajax&section=findnames&do=get-member-names&secure_key=' + ipb.vars['secure_hash'] + '&name=';
		new ipb.Autocomplete( $('newbox_1'), { multibox: false, url: url, templates: { wrap: ipb.templates['autocomplete_wrap'], item: ipb.templates['autocomplete_item'] } } );
	});
</script>";
return $IPBHTML;
}


function __f__faa36856aa9ee843695aa8109e9a11d1($members)
{
	$_ips___x_retval = '';
	$__iteratorCount = 0;
	foreach( $members as $member )
	{
		
		$__iteratorCount++;
		$_ips___x_retval .= "
			<tr class='" .  IPSLib::next( $this->registry->templateStriping["members"] ) . "'>
				<td>
					<img src='{$member['pp_thumb_photo']}' width='{$member['pp_thumb_width']}' height='{$member['pp_thumb_height']}' border='0' />
					<strong><a href=\"" . $this->registry->getClass('output')->formatUrl( $this->registry->getClass('output')->buildUrl( "showuser={$member['member_id']}", "public",'' ), "{$member['members_seo_name']}", "showuser" ) . "\">{$member['members_display_name']}</a></strong>
					<p>{$this->lang->words['m_joined']} " . $this->registry->getClass('class_localization')->getDate($member['joined'],"joined", 0) . "</p>
				</td>
				<td>{$member['g_title']}</td>
				<td>{$member['posts']}</td>
				<td><a href=\"" . $this->registry->getClass('output')->formatUrl( $this->registry->getClass('output')->buildUrl( "app=core&amp;module=usercp&amp;tab=ipchat&amp;area=removeIgnoredUser&amp;do=saveIt&amp;id={$member['member_id']}", "public",'' ), "", "" ) . "\">{$this->lang->words['mi5_remove']}</a></td>
			</tr>
		
";
	}
	$_ips___x_retval .= '';
	unset( $__iteratorCount );
	return $_ips___x_retval;
}

/* -- newWindow --*/
function newWindow() {
$IPBHTML = "";
$IPBHTML .= "<script type='text/javascript'>
// And then move
document.observe(\"dom:loaded\", function(){
	$('nav_app_ipchat').down('a').observe( 'click', function(e) {
		var _chatUrl	= $('nav_app_ipchat').down('a').href;
	
		if( _chatUrl.charAt( _chatUrl.length - 1 ) == '#' )
		{
			_chatUrl	= _chatUrl.substr( 0, _chatUrl.length - 1 );
		}
	
		if( _chatUrl.indexOf('?') != '-1' )
		{
			_chatUrl	+= '&_popup=1';
		}
		else
		{
			_chatUrl	+= '?_popup=1';
		}
		
		window.open( _chatUrl, \"chatpopout\", \"status=0,toolbar=0,location=1,menubar=0,directories=0,resizable=1,scrollbars=1,height=550,width=750\" );
		
		Event.stop(e);
		return false;
	});
});
</script>";
return $IPBHTML;
}

/* -- tabCount --*/
function tabCount($count) {
$IPBHTML = "";
$IPBHTML .= "" . (((!$this->settings['ipchat_htc_view'] OR IPS_APP_COMPONENT != 'ipchat') AND (!$this->settings['ipchat_htc_zero'] OR $count > 0)) ? ("
<div id='chat-tab-count-wrap'><span id='chat-tab-count' title='" . sprintf( $this->lang->words['chattab_count'], $count ) . "' style='display:none;'>{$count}</span></div>
<script type='text/javascript'>
// And then move
document.observe(\"dom:loaded\", function(){
	var _thisHtml	= $('nav_app_ipchat').innerHTML;
	_thisHtml = _thisHtml.replace( /\\<\\/a\\>/ig, '' ) + \"&nbsp;&nbsp;\" + $('chat-tab-count-wrap').innerHTML + \"</a>\";
	$('nav_app_ipchat').update( _thisHtml );
	$('chat-tab-count-wrap').remove();
	$('chat-tab-count').show();
});
</script>
") : ("")) . "";
return $IPBHTML;
}


}


/*--------------------------------------------------*/
/* END OF FILE                                      */
/*--------------------------------------------------*/

?>