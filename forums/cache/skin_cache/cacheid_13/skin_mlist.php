<?php
/*--------------------------------------------------*/
/* FILE GENERATED BY INVISION POWER BOARD 3         */
/* CACHE FILE: Skin set id: 13               */
/* CACHE FILE: Generated: Sat, 22 Dec 2012 23:42:16 GMT */
/* DO NOT EDIT DIRECTLY - THE CHANGES WILL NOT BE   */
/* WRITTEN TO THE DATABASE AUTOMATICALLY            */
/*--------------------------------------------------*/

class skin_mlist_13 extends skinMaster{

/**
* Construct
*/
function __construct( ipsRegistry $registry )
{
	parent::__construct( $registry );
	

$this->_funcHooks = array();
$this->_funcHooks['member_list_show'] = array('customfields','filterdefault','filter','sortdefault','sort_key','orderdefault','sort_order','limitdefault','max_results','selected','letterdefault','chars','weAreSupmod','addfriend','notus','sendpm','blog','gallery','rate1','rate2','rate3','rate4','rate5','rating','norep','posrep','negrep','repson','filterViews','members','calendarlocale','namebox_begins','namebox_contains','photoonly','rating0','rating1','rating2','rating3','rating4','canFilterRate','hascfields','posts_ltmt_lt','posts_ltmt_mt','joined_ltmt_lt','joined_ltmt_mt','lastpost_ltmt_lt','lastpost_ltmt_mt','lastvisit_ltmt_lt','lastvisit_ltmt_mt','letterquickjump','filtermembers','filterposts','filterjoined','showmembers');


}

/* -- member_list_show --*/
function member_list_show($members, $pages="", $dropdowns=array(), $defaults=array(), $custom_fields=null, $url='') {
$IPBHTML = "";
if( IPSLib::locationHasHooks( 'skin_mlist', $this->_funcHooks['member_list_show'] ) )
{
$count_880bc8fd23587393e5844a0e5a6a2f01 = is_array($this->functionData['member_list_show']) ? count($this->functionData['member_list_show']) : 0;
$this->functionData['member_list_show'][$count_880bc8fd23587393e5844a0e5a6a2f01]['members'] = $members;
$this->functionData['member_list_show'][$count_880bc8fd23587393e5844a0e5a6a2f01]['pages'] = $pages;
$this->functionData['member_list_show'][$count_880bc8fd23587393e5844a0e5a6a2f01]['dropdowns'] = $dropdowns;
$this->functionData['member_list_show'][$count_880bc8fd23587393e5844a0e5a6a2f01]['defaults'] = $defaults;
$this->functionData['member_list_show'][$count_880bc8fd23587393e5844a0e5a6a2f01]['custom_fields'] = $custom_fields;
$this->functionData['member_list_show'][$count_880bc8fd23587393e5844a0e5a6a2f01]['url'] = $url;
}

if ( ! isset( $this->registry->templateStriping['memberStripe'] ) ) {
$this->registry->templateStriping['memberStripe'] = array( FALSE, "row1","row2");
}
$IPBHTML .= "<script type='text/javascript' src='{$this->settings['public_dir']}js/3rd_party/calendar_date_select/calendar_date_select.js'></script>
" . (($this->settings['calendar_date_select_locale'] AND $this->settings['calendar_date_select_locale'] != 'en') ? ("
	<script type='text/javascript' src='{$this->settings['public_dir']}js/3rd_party/calendar_date_select/locale/{$this->settings['calendar_date_select_locale']}.js'></script>
") : ("")) . "
" . $this->registry->getClass('output')->addJSModule("memberlist", "0" ) . "
<!-- SEARCH FORM -->
<h1 class='ipsType_pagetitle'>{$this->lang->words['mlist_header']}</h1>
<div class='topic_controls'>
	{$pages}	
	<ul class='topic_buttons'>
		<li><a href='#filters' title='{$this->lang->words['mlist_adv_filt']}' id='use_filters'>{$this->lang->words['mlist_adv_filt']}</a></li>
	</ul>
</div>
<div id='member_filters' class='general_box alt clear'>
	<form action=\"" . $this->registry->getClass('output')->formatUrl( $this->registry->getClass('output')->buildUrl( "app=members&amp;module=list", "public",'' ), "false", "" ) . "\" method=\"post\">
		<h3 class='bar'>{$this->lang->words['mlist_adv_filt_opt']}</h3>
	
		<ul id='filters_1'>
			<li class='field'>
				<label for='member_name'>{$this->lang->words['s_name']}</label>
				<select name=\"name_box\" class='input_select'>
					<option value=\"begins\"" . (($this->request['name_box'] == 'begins') ? (" selected='selected'") : ("")) . ">{$this->lang->words['s_begins']}</option>
					<option value=\"contains\"" . (($this->request['name_box'] == 'contains') ? (" selected='selected'") : ("")) . ">{$this->lang->words['s_contains']}</option>
				</select>&nbsp;&nbsp;
				<input type=\"text\" size=\"15\" name=\"name\" id='member_name' class='input_text' value=\"" . urldecode($this->request['name']) . "\" />
			</li>
			<li class='field'>
				<label for='photo_only'>{$this->lang->words['photo_only']}</label>
				<input class='input_check' id='photo_only' type=\"checkbox\" value=\"1\" name=\"photoonly\" " . (($defaults['photoonly']) ? ("checked='checked'") : ("")) . " />
			</li>
			" . (($this->settings['pp_allow_member_rate']) ? ("<li class='field'>
					<label for='rating'>{$this->lang->words['m_rating_morethan']}</label>
					<select name='pp_rating_real' id='rating'>
						<option value='0'" . ((! $this->request['pp_rating_real']) ? (" selected='selected'") : ("")) . ">0</option>
						<option value='1'" . (($this->request['pp_rating_real'] == 1) ? (" selected='selected'") : ("")) . ">1</option>
						<option value='2'" . (($this->request['pp_rating_real'] == 2) ? (" selected='selected'") : ("")) . ">2</option>
						<option value='3'" . (($this->request['pp_rating_real'] == 3) ? (" selected='selected'") : ("")) . ">3</option>
						<option value='4'" . (($this->request['pp_rating_real'] == 4) ? (" selected='selected'") : ("")) . ">4</option>
					</select>
					{$this->lang->words['m_stars']}
				</li>") : ("")) . "
			" . ((count( $custom_fields->out_fields )) ? ("
				".$this->__f__41c90193768fd2cc6c1434bcc43af465($members,$pages,$dropdowns,$defaults,$custom_fields,$url)."			") : ("")) . "			
		</ul>
		<ul id='filters_2'>
			<li class='field'>
				<label for='signature'>{$this->lang->words['s_signature']}</label>
				<input type=\"text\" class='input_text' size=\"28\" id='signature' name=\"signature\" value=\"" . urldecode($this->request['signature']) . "\" />
			</li>
			<li class='field'>
				<label for='posts'>{$this->lang->words['s_posts']}</label>
				<select class=\"dropdown\" name=\"posts_ltmt\">
					<option value=\"lt\"" . (($this->request['posts_ltmt'] == 'lt') ? (" selected='selected'") : ("")) . ">{$this->lang->words['s_lessthan']}</option>
					<option value=\"mt\"" . (($this->request['posts_ltmt'] == 'mt') ? (" selected='selected'") : ("")) . ">{$this->lang->words['s_morethan']}</option>
				</select>
				&nbsp;<input type=\"text\" class='input_text' id='posts' size=\"15\" name=\"posts\" value=\"{$this->request['posts']}\" />
			</li>
			<li class='field'>
				<label for='joined'>{$this->lang->words['s_joined']}</label>
				<select class=\"dropdown\" name=\"joined_ltmt\">
					<option value=\"lt\"" . (($this->request['joined_ltmt'] == 'lt') ? (" selected='selected'") : ("")) . ">{$this->lang->words['s_before']}</option>
					<option value=\"mt\"" . (($this->request['joined_ltmt'] == 'mt') ? (" selected='selected'") : ("")) . ">{$this->lang->words['s_after']}</option>
				</select>
				&nbsp;<input type=\"text\" class='input_text' id='joined' size=\"10\" name=\"joined\" value=\"{$this->request['joined']}\" /> <img src='{$this->settings['img_url']}/date.png' alt='{$this->lang->words['generic_date']}' id='joined_date_icon' class='clickable' /> 
				<span class=\"desc\">{$this->lang->words['s_dateformat']}</span>
			</li>
			<li class='field'>
				<label for='last_post'>{$this->lang->words['s_lastpost']}</label>
				<select class=\"dropdown\" name=\"lastpost_ltmt\">
					<option value=\"lt\"" . (($this->request['lastpost_ltmt'] == 'lt') ? (" selected='selected'") : ("")) . ">{$this->lang->words['s_before']}</option>
					<option value=\"mt\"" . (($this->request['lastpost_ltmt'] == 'mt') ? (" selected='selected'") : ("")) . ">{$this->lang->words['s_after']}</option>
				</select>
				&nbsp;<input type=\"text\" class='input_text' id='last_post' size=\"10\" name=\"lastpost\" value=\"{$this->request['lastpost']}\" /> <img src='{$this->settings['img_url']}/date.png' alt='{$this->lang->words['generic_date']}' id='last_post_date_icon' class='clickable' /> 
				<span class=\"desc\">{$this->lang->words['s_dateformat']}</span>
			</li>
			<li class='field'>
				<label for='last_visit'>{$this->lang->words['s_lastvisit']}</label>
				<select class=\"dropdown\" name=\"lastvisit_ltmt\">
					<option value=\"lt\"" . (($this->request['lastvisit_ltmt'] == 'lt') ? (" selected='selected'") : ("")) . ">{$this->lang->words['s_before']}</option>
					<option value=\"mt\"" . (($this->request['lastvisit_ltmt'] == 'mt') ? (" selected='selected'") : ("")) . ">{$this->lang->words['s_after']}</option>
				</select>
				&nbsp;<input type=\"text\" class='input_text' id='last_visit' size=\"10\" name=\"lastvisit\" value=\"{$this->request['lastvisit']}\" /> <img src='{$this->settings['img_url']}/date.png' alt='{$this->lang->words['generic_date']}' id='last_visit_date_icon' class='clickable' />
				<span class=\"desc\">{$this->lang->words['s_dateformat']}</span>
			</li>			
		</ul>
		<fieldset class='other_filters row2 altrow'>
			<select name='filter' class='input_select'>
				".$this->__f__be86d88dfcbc70d326af84a23397cb22($members,$pages,$dropdowns,$defaults,$custom_fields,$url)."			</select>
			{$this->lang->words['sorting_text_by']}
			<select name='sort_key' class='input_select'>
				".$this->__f__7b60b1dd1fd363b5f69f08f27f281aef($members,$pages,$dropdowns,$defaults,$custom_fields,$url)."			</select>
			{$this->lang->words['sorting_text_in']}
			<select name='sort_order' class='input_select'>
				".$this->__f__8655eeebf3fc8831b00bc8b09c1c782f($members,$pages,$dropdowns,$defaults,$custom_fields,$url)."			</select>
			{$this->lang->words['sorting_text_with']}
			<select name='max_results' class='input_select'>
				".$this->__f__8b338721189bb91df3a62cd61a30808e($members,$pages,$dropdowns,$defaults,$custom_fields,$url)."			</select>
			{$this->lang->words['sorting_text_results']}
		</fieldset>
		<fieldset class='submit clear'>
			<input type=\"submit\" value=\"{$this->lang->words['sort_submit']}\" class=\"input_submit\" /> {$this->lang->words['or']} <a href='#j_memberlist' title='{$this->lang->words['cancel']}' id='close_filters' class='cancel'>{$this->lang->words['cancel']}</a>
		</fieldset>
	</form>
</div>
<script type='text/javascript'>
	$('member_filters').hide();
</script>
<br />
<div class='ipsBox ipsVerticalTabbed ipsLayout ipsLayout_withleft ipsLayout_tinyleft clearfix'>
	<div class='ipsVerticalTabbed_tabs ipsVerticalTabbed_minitabs ipsLayout_left' id='mlist_tabs'>
		<ul>
			" . ((!$this->request['quickjump']) ? ("
				<li class='active'><a href='" . $this->registry->getClass('output')->formatUrl( $this->registry->getClass('output')->buildUrl( "app=members&amp;module=list", "public",'' ), "false", "members_list" ) . "' title='{$this->lang->words['members_start_with']}{$letter}'>{$this->lang->words['mlist_view_all_txt']}</a></li>
			") : ("
				<li><a href='" . $this->registry->getClass('output')->formatUrl( $this->registry->getClass('output')->buildUrl( "app=members&amp;module=list", "public",'' ), "false", "members_list" ) . "' title='{$this->lang->words['mlist_view_all_title']}'>{$this->lang->words['mlist_view_all_txt']}</a></li>
			")) . "
			".$this->__f__07d9d88920a6f9dc7c6483cd9cc0abfc($members,$pages,$dropdowns,$defaults,$custom_fields,$url)."		</ul>
	</div>
	<div class='ipsVerticalTabbed_content ipsLayout_content'>
		<div class='maintitle ipsFilterbar clear clearfix'>
			<ul class='ipsList_inline left'>
				<li " . (($this->request['sort_key'] == 'members_display_name' || !$this->request['sort_key']) ? ("class='active'") : ("")) . ">
					<a href='" . $this->registry->getClass('output')->formatUrl( $this->registry->getClass('output')->buildUrl( "app=members&amp;module=list&amp;{$url}&amp;sort_key=members_display_name&amp;sort_order=asc", "public",'' ), "false", "members_list" ) . "' title='{$this->lang->words['sort_by_mname']}'>{$this->lang->words['sort_by_name']}</a>
				</li>
				<li " . (($this->request['sort_key'] == 'posts') ? ("class='active'") : ("")) . ">
					<a href='" . $this->registry->getClass('output')->formatUrl( $this->registry->getClass('output')->buildUrl( "app=members&amp;module=list&amp;{$url}&amp;sort_key=posts&amp;sort_order=desc", "public",'' ), "false", "members_list" ) . "' title='{$this->lang->words['sort_by_posts']}'>{$this->lang->words['pcount']}</a>
				</li>
				<li " . (($this->request['sort_key'] == 'joined') ? ("class='active'") : ("")) . ">
					<a href='" . $this->registry->getClass('output')->formatUrl( $this->registry->getClass('output')->buildUrl( "app=members&amp;module=list&amp;{$url}&amp;sort_key=joined", "public",'' ), "false", "members_list" ) . "' title='{$this->lang->words['sorty_by_jdate']}'>{$this->lang->words['sort_by_joined']}</a>
				</li>
			</ul>
		</div>
		<div class='ipsBox_container ipsPad' id='mlist_content'>
			<ul class='ipsMemberList'>
				" . ((is_array( $members ) and count( $members )) ? ("
										".$this->__f__873f797f2cf97f74be4bde4eb45540f9($members,$pages,$dropdowns,$defaults,$custom_fields,$url)."				") : ("
					<li class='no_messages'>
						{$this->lang->words['no_results']}
					</li>
				")) . "
			</ul>
		</div>
	</div>
</div>
<script type='text/javascript'>
	$(\"mlist_content\").setStyle( { minHeight: $('mlist_tabs').measure('margin-box-height') + 5 + \"px\" } );
</script>
{$pages}";
return $IPBHTML;
}


function __f__41c90193768fd2cc6c1434bcc43af465($members, $pages="", $dropdowns=array(), $defaults=array(), $custom_fields=null, $url='')
{
	$_ips___x_retval = '';
	$__iteratorCount = 0;
	foreach( $custom_fields->out_fields as $id => $field )
	{
		
		$__iteratorCount++;
		$_ips___x_retval .= "
					<li class='field custom'>
						<label for='field_{$id}'>{$custom_fields->field_names[$id]}</label>
						{$field}
					</li>
				
";
	}
	$_ips___x_retval .= '';
	unset( $__iteratorCount );
	return $_ips___x_retval;
}

function __f__be86d88dfcbc70d326af84a23397cb22($members, $pages="", $dropdowns=array(), $defaults=array(), $custom_fields=null, $url='')
{
	$_ips___x_retval = '';
	$__iteratorCount = 0;
	foreach( $dropdowns['filter'] as $k => $v )
	{
		
		$__iteratorCount++;
		$_ips___x_retval .= "
					<option value='{$k}'" . (($k == $defaults['filter']) ? (" selected='selected'") : ("")) . ">{$v}</option>
				
";
	}
	$_ips___x_retval .= '';
	unset( $__iteratorCount );
	return $_ips___x_retval;
}

function __f__7b60b1dd1fd363b5f69f08f27f281aef($members, $pages="", $dropdowns=array(), $defaults=array(), $custom_fields=null, $url='')
{
	$_ips___x_retval = '';
	$__iteratorCount = 0;
	foreach( $dropdowns['sort_key'] as $k => $v )
	{
		
		$__iteratorCount++;
		$_ips___x_retval .= "
					<option value='{$k}'" . (($k == $defaults['sort_key']) ? (" selected='selected'") : ("")) . ">{$this->lang->words[ $v ]}</option>
				
";
	}
	$_ips___x_retval .= '';
	unset( $__iteratorCount );
	return $_ips___x_retval;
}

function __f__8655eeebf3fc8831b00bc8b09c1c782f($members, $pages="", $dropdowns=array(), $defaults=array(), $custom_fields=null, $url='')
{
	$_ips___x_retval = '';
	$__iteratorCount = 0;
	foreach( $dropdowns['sort_order'] as $k => $v )
	{
		
		$__iteratorCount++;
		$_ips___x_retval .= "
					<option value='{$k}'" . (($k == $defaults['sort_order']) ? (" selected='selected'") : ("")) . ">{$this->lang->words[ $v ]}</option>
				
";
	}
	$_ips___x_retval .= '';
	unset( $__iteratorCount );
	return $_ips___x_retval;
}

function __f__8b338721189bb91df3a62cd61a30808e($members, $pages="", $dropdowns=array(), $defaults=array(), $custom_fields=null, $url='')
{
	$_ips___x_retval = '';
	$__iteratorCount = 0;
	foreach( $dropdowns['max_results'] as $k => $v )
	{
		
		$__iteratorCount++;
		$_ips___x_retval .= "
					<option value='{$k}'" . (($k == $defaults['max_results']) ? (" selected='selected'") : ("")) . ">{$v}</option>
				
";
	}
	$_ips___x_retval .= '';
	unset( $__iteratorCount );
	return $_ips___x_retval;
}

function __f__07d9d88920a6f9dc7c6483cd9cc0abfc($members, $pages="", $dropdowns=array(), $defaults=array(), $custom_fields=null, $url='')
{
	$_ips___x_retval = '';
	$__iteratorCount = 0;
	foreach( range(65,90) as $char )
	{
		
		$__iteratorCount++;
		$_ips___x_retval .= "
				" . (($letter = strtoupper(chr($char))) ? ("<li " . ((strtoupper( $this->request['quickjump'] ) == $letter) ? ("class='active'") : ("")) . "><a href='" . $this->registry->getClass('output')->formatUrl( $this->registry->getClass('output')->buildUrl( "{$url}&amp;quickjump={$letter}", "public",'' ), "false", "members_list" ) . "' title='{$this->lang->words['mlist_view_start_title']} {$letter}'>{$letter}</a></li>") : ("")) . "
			
";
	}
	$_ips___x_retval .= '';
	unset( $__iteratorCount );
	return $_ips___x_retval;
}

function __f__873f797f2cf97f74be4bde4eb45540f9($members, $pages="", $dropdowns=array(), $defaults=array(), $custom_fields=null, $url='')
{
	$_ips___x_retval = '';
	$__iteratorCount = 0;
	foreach( $members as $member )
	{
		
		$__iteratorCount++;
		$_ips___x_retval .= "
						<li id='member_id_{$member['member_id']}' class='ipsPad clearfix member_entry " .  IPSLib::next( $this->registry->templateStriping["memberStripe"] ) . "'>
							<a href='" . $this->registry->getClass('output')->formatUrl( $this->registry->getClass('output')->buildUrl( "showuser={$member['member_id']}", "public",'' ), "{$member['members_seo_name']}", "showuser" ) . "' title='{$this->lang->words['view_profile']}' class='ipsUserPhotoLink left'><img src='{$member['pp_small_photo']}' alt=\"" . sprintf($this->lang->words['users_photo'],$member['members_display_name']) . "\" class='ipsUserPhoto ipsUserPhoto_medium' /></a>
							<div class='ipsBox_withphoto'>
								<ul class='ipsList_inline right'>
									" . (($this->memberData['g_is_supmod'] == 1 && $member['member_id'] != $this->memberData['member_id']) ? ("
										<li><a href='" . $this->registry->getClass('output')->formatUrl( $this->registry->getClass('output')->buildUrl( "app=core&amp;module=modcp&amp;do=editmember&amp;auth_key={$this->member->form_hash}&amp;mid={$member['member_id']}&amp;pf={$member['member_id']}", "public",'' ), "", "" ) . "' class='ipsButton_secondary'>{$this->lang->words['edit_member']}</a></li>
									") : ("")) . "
									" . (($this->memberData['member_id'] AND $this->memberData['member_id'] != $member['member_id'] && $this->settings['friends_enabled'] AND $this->memberData['g_can_add_friends']) ? ("" . ((IPSMember::checkFriendStatus( $member['member_id'] )) ? ("
											<li class='mini_friend_toggle is_friend' id='friend_mlist_{$member['member_id']}'><a href='" . $this->registry->getClass('output')->formatUrl( $this->registry->getClass('output')->buildUrl( "app=members&amp;module=list&amp;module=profile&amp;section=friends&amp;do=remove&amp;member_id={$member['member_id']}&amp;secure_key={$this->member->form_hash}", "public",'' ), "", "" ) . "' title='{$this->lang->words['remove_friend']}' class='ipsButton_secondary'>" . $this->registry->getClass('output')->getReplacement("remove_friend") . "</a></li>
										") : ("
											<li class='mini_friend_toggle is_not_friend' id='friend_mlist_{$member['member_id']}'><a href='" . $this->registry->getClass('output')->formatUrl( $this->registry->getClass('output')->buildUrl( "app=members&amp;module=list&amp;module=profile&amp;section=friends&amp;do=add&amp;member_id={$member['member_id']}&amp;secure_key={$this->member->form_hash}", "public",'' ), "", "" ) . "' title='{$this->lang->words['add_friend']}' class='ipsButton_secondary'>" . $this->registry->getClass('output')->getReplacement("add_friend") . "</a></li>								
										")) . "") : ("")) . "
									" . (($this->memberData['g_use_pm'] AND $this->memberData['members_disable_pm'] == 0 AND IPSLib::moduleIsEnabled( 'messaging', 'members' ) && $member['member_id'] != $this->memberData['member_id']) ? ("
										<li class='pm_button' id='pm_xxx_{$member['pp_member_id']}'><a href='" . $this->registry->getClass('output')->formatUrl( $this->registry->getClass('output')->buildUrl( "app=members&amp;module=list&amp;module=messaging&amp;section=send&amp;do=form&amp;fromMemberID={$member['pp_member_id']}", "public",'' ), "", "" ) . "' title='{$this->lang->words['pm_member']}' class='ipsButton_secondary'>" . $this->registry->getClass('output')->getReplacement("send_msg") . "</a></li>
									") : ("")) . "
									<li><a href='" . $this->registry->getClass('output')->formatUrl( $this->registry->getClass('output')->buildUrl( "app=core&amp;module=search&amp;do=user_activity&amp;mid={$member['member_id']}", "public",'' ), "", "" ) . "' title='{$this->lang->words['gbl_find_my_content']}' class='ipsButton_secondary'>" . $this->registry->getClass('output')->getReplacement("find_topics_link") . "</a></li>
									" . (($member['has_blog'] AND IPSLib::appIsInstalled( 'blog' )) ? ("
										<li><a href='" . $this->registry->getClass('output')->formatUrl( $this->registry->getClass('output')->buildUrl( "app=blog&amp;module=display&amp;section=blog&amp;mid={$member['member_id']}", "public",'' ), "", "" ) . "' title='{$this->lang->words['view_blog']}' class='ipsButton_secondary'>" . $this->registry->getClass('output')->getReplacement("blog_link") . "</a></li>
									") : ("")) . "
									" . (($member['has_gallery'] AND IPSLib::appIsInstalled( 'gallery' )) ? ("
										<li><a href='" . $this->registry->getClass('output')->formatUrl( $this->registry->getClass('output')->buildUrl( "app=gallery&amp;user={$member['member_id']}", "public",'' ), "{$member['members_seo_name']}", "useralbum" ) . "' title='{$this->lang->words['view_gallery']}' class='ipsButton_secondary'>" . $this->registry->getClass('output')->getReplacement("gallery_link") . "</a></li>
									") : ("")) . "
								</ul>
								
								<h3 class='ipsType_subtitle'>
									<strong><a href='" . $this->registry->getClass('output')->formatUrl( $this->registry->getClass('output')->buildUrl( "showuser={$member['member_id']}", "public",'' ), "{$member['members_seo_name']}", "showuser" ) . "' title='{$this->lang->words['view_profile']}'>{$member['members_display_name']}</a></strong>
									
									" . (($this->settings['pp_allow_member_rate'] && $this->request['pp_rating_real']) ? ("<span class='rating'> 
											" . (($member['pp_rating_real'] >= 1) ? ("" . $this->registry->getClass('output')->getReplacement("rate_on") . "") : ("" . $this->registry->getClass('output')->getReplacement("rate_off") . "")) . "" . (($member['pp_rating_real'] >= 2) ? ("" . $this->registry->getClass('output')->getReplacement("rate_on") . "") : ("" . $this->registry->getClass('output')->getReplacement("rate_off") . "")) . "" . (($member['pp_rating_real'] >= 3) ? ("" . $this->registry->getClass('output')->getReplacement("rate_on") . "") : ("" . $this->registry->getClass('output')->getReplacement("rate_off") . "")) . "" . (($member['pp_rating_real'] >= 4) ? ("" . $this->registry->getClass('output')->getReplacement("rate_on") . "") : ("" . $this->registry->getClass('output')->getReplacement("rate_off") . "")) . "" . (($member['pp_rating_real'] >= 5) ? ("" . $this->registry->getClass('output')->getReplacement("rate_on") . "") : ("" . $this->registry->getClass('output')->getReplacement("rate_off") . "")) . "
										</span>") : ("")) . "
								</h3>
								" . (($this->settings['reputation_enabled'] && $this->settings['reputation_show_profile'] && $member['pp_reputation_points'] !== null) ? ("" . (($member['pp_reputation_points'] == 0 || !$member['pp_reputation_points']) ? ("
										<p class='reputation zero ipsType_small left' data-tooltip=\"" . sprintf( $this->lang->words['member_has_x_rep'], $member['members_display_name'], $member['pp_reputation_points'] ) . "\">
									") : ("")) . "
									" . (($member['pp_reputation_points'] > 0) ? ("
										<p class='reputation positive ipsType_small left' data-tooltip=\"" . sprintf( $this->lang->words['member_has_x_rep'], $member['members_display_name'], $member['pp_reputation_points'] ) . "\">
									") : ("")) . "
									" . (($member['pp_reputation_points'] < 0) ? ("
										<p class='reputation negative ipsType_small left' data-tooltip=\"" . sprintf( $this->lang->words['member_has_x_rep'], $member['members_display_name'], $member['pp_reputation_points'] ) . "\">
									") : ("")) . "							
											<span class='number'>{$member['pp_reputation_points']}</span>
										</p>") : ("")) . "
								<span class='desc'>
									{$this->lang->words['member_joined']} " . $this->registry->getClass('class_localization')->getDate($member['joined'],"joined", 0) . "<br />
									" . IPSMember::makeNameFormatted( $member['group'], $member['member_group_id'] ) . " &middot;
									" . (($this->request['sort_key'] == 'members_profile_views') ? ("
										" . $this->registry->getClass('class_localization')->formatNumber( $member['members_profile_views'] ) . " {$this->lang->words['m_views']}
									") : ("
										" . $this->registry->getClass('class_localization')->formatNumber( $member['posts'] ) . " {$this->lang->words['member_posts']}
									")) . "
								</span>
							</div>
						</li>						
					
";
	}
	$_ips___x_retval .= '';
	unset( $__iteratorCount );
	return $_ips___x_retval;
}


}


/*--------------------------------------------------*/
/* END OF FILE                                      */
/*--------------------------------------------------*/

?>