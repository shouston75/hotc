<?php
/*--------------------------------------------------*/
/* FILE GENERATED BY INVISION POWER BOARD 3         */
/* CACHE FILE: Skin set id: 0               */
/* CACHE FILE: Generated: Tue, 04 Aug 2009 19:56:23 GMT */
/* DO NOT EDIT DIRECTLY - THE CHANGES WILL NOT BE   */
/* WRITTEN TO THE DATABASE AUTOMATICALLY            */
/*--------------------------------------------------*/

class skin_mlist_0 {

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
	/* -- member_list_show --*/
function member_list_show($members, $pages="", $dropdowns=array(), $defaults=array(), $custom_fields=null, $url='') {
$IPBHTML = "";

$this->registry->templateStriping['memberStripe'] = array( FALSE, "row1","row2");
$IPBHTML .= "<script type='text/javascript' src='{$this->settings['public_dir']}js/3rd_party/calendar_date_select/calendar_date_select.js'></script>
" . $this->registry->getClass('output')->addJSModule("memberlist", "0" ) . "
<!-- SEARCH FORM -->
<h2>{$this->lang->words['mlist_header']}</h2>
<div class='topic_controls'>
	{$pages}
	
	<ul class='topic_buttons'>
		<li><a href='#filters' title='{$this->lang->words['mlist_adv_filt']}' id='use_filters'><img src='{$this->settings['img_url']}/cog.png' alt='{$this->lang->words['icon']}' /> {$this->lang->words['mlist_adv_filt']}</a></li>
	</ul>
</div>
<div id='member_filters' class='general_box alt clear'>
	<form action=\"" . $this->registry->getClass('output')->formatUrl( $this->registry->getClass('output')->buildUrl( "app=members&amp;section=view&amp;module=list", 'public','' ), "", "" ) . "\" method=\"post\">
		<h3 class='bar'>{$this->lang->words['mlist_adv_filt_opt']}</h3>
	
		<ul id='filters_1' class=''>
			<li class='field'>
				<label for='member_name'>{$this->lang->words['s_name']}</label>
				<select name=\"name_box\" class='input_select'>
					<option value=\"begins\">{$this->lang->words['s_begins']}</option>
					<option value=\"contains\">{$this->lang->words['s_contains']}</option>
				</select>&nbsp;&nbsp;
				<input type=\"text\" size=\"15\" name=\"name\" id='member_name' class='input_text' value=\"{$this->request['name']}\" />
			</li>
			<li class='field'>
				<label for='photo_only'>{$this->lang->words['photo_only']}</label>
				<input class='input_check' id='photo_only' type=\"checkbox\" value=\"1\" name=\"photoonly\" " . (($defaults['photoonly']) ? ("checked='checked'") : ("")) . " />
			</li>
			<li class='field'>
				<label for='rating'>{$this->lang->words['m_rating_morethan']}</label>
				<select name='pp_rating_real' id='rating'>
					" . ((! $this->request['pp_rating_real']) ? ("
						<option value='0' selected='selected'>0</option>
					") : ("
						<option value='0'>0</option>
					")) . "
					" . (($this->request['pp_rating_real'] == 1) ? ("
						<option value='1' selected='selected'>1</option>
					") : ("
						<option value='1'>1</option>
					")) . "
					" . (($this->request['pp_rating_real'] == 2) ? ("
						<option value='2' selected='selected'>2</option>
					") : ("
						<option value='2'>2</option>
					")) . "
					" . (($this->request['pp_rating_real'] == 3) ? ("
						<option value='3' selected='selected'>3</option>
					") : ("
						<option value='3'>3</option>
					")) . "
					" . (($this->request['pp_rating_real'] == 4) ? ("
						<option value='4' selected='selected'>4</option>
					") : ("
						<option value='4'>4</option>
					")) . "
				</select>
				{$this->lang->words['m_stars']}
			</li>
			
			" . ((count( $custom_fields->out_fields )) ? ("
				".$this->__f__94a193205f0b5cba87ad9b5bdb91d504($members,$pages,$dropdowns,$defaults,$custom_fields,$url)."			") : ("")) . "			
		</ul>
		<ul id='filters_2' class=''>
			<li class='field'>
				<label for='signature'>{$this->lang->words['s_signature']}</label>
				<input type=\"text\" class='input_text' size=\"28\" id='signature' name=\"signature\" value=\"{$this->request['signature']}\" />
			</li>
			<li class='field'>
				<label for='posts'>{$this->lang->words['s_posts']}</label>
				<select class=\"dropdown\" name=\"posts_ltmt\">
					<option value=\"lt\">{$this->lang->words['s_lessthan']}</option>
					<option value=\"mt\">{$this->lang->words['s_morethan']}</option>
				</select>
				&nbsp;<input type=\"text\" class='input_text' id='posts' size=\"15\" name=\"posts\" value=\"{$this->request['posts']}\" />
			</li>
			<li class='field'>
				<label for='joined'>{$this->lang->words['s_joined']}</label>
				<select class=\"dropdown\" name=\"joined_ltmt\">
					<option value=\"lt\">{$this->lang->words['s_before']}</option>
					<option value=\"mt\">{$this->lang->words['s_after']}</option>
				</select>
				&nbsp;<input type=\"text\" class='input_text' id='joined' size=\"10\" name=\"joined\" value=\"{$this->request['joined']}\" /> <img src='{$this->settings['img_url']}/date.png' alt='{$this->lang->words['generic_date']}' id='joined_date_icon' class='clickable' /> 
				<span class=\"desc\">{$this->lang->words['s_dateformat']}</span>
			</li>
			<li class='field'>
				<label for='last_post'>{$this->lang->words['s_lastpost']}</label>
				<select class=\"dropdown\" name=\"lastpost_ltmt\">
					<option value=\"lt\">{$this->lang->words['s_before']}</option>
					<option value=\"mt\">{$this->lang->words['s_after']}</option>
				</select>
				&nbsp;<input type=\"text\" class='input_text' id='last_post' size=\"10\" name=\"lastpost\" value=\"{$this->request['lastpost']}\" /> <img src='{$this->settings['img_url']}/date.png' alt='{$this->lang->words['generic_date']}' id='last_post_date_icon' class='clickable' /> 
				<span class=\"desc\">{$this->lang->words['s_dateformat']}</span>
			</li>
			<li class='field'>
				<label for='last_visit'>{$this->lang->words['s_lastvisit']}</label>
				<select class=\"dropdown\" name=\"lastvisit_ltmt\">
					<option value=\"lt\">{$this->lang->words['s_before']}</option>
					<option value=\"mt\">{$this->lang->words['s_after']}</option>
				</select>
				&nbsp;<input type=\"text\" class='input_text' id='last_visit' size=\"10\" name=\"lastvisit\" value=\"{$this->request['lastvisit']}\" /> <img src='{$this->settings['img_url']}/date.png' alt='{$this->lang->words['generic_date']}' id='last_visit_date_icon' class='clickable' />
				<span class=\"desc\">{$this->lang->words['s_dateformat']}</span>
			</li>			
		</ul>
		<fieldset class='other_filters row2 altrow'>
			<select name='filter' class='input_select'>
				".$this->__f__125e27e4b37c5382656ea4729fd1507c($members,$pages,$dropdowns,$defaults,$custom_fields,$url)."			</select>
			{$this->lang->words['sorting_text_by']}
			<select name='sort_key' class='input_select'>
				".$this->__f__04b67520b462eb9f7231f3a9d8f9f35f($members,$pages,$dropdowns,$defaults,$custom_fields,$url)."			</select>
			{$this->lang->words['sorting_text_in']}
			<select name='sort_order' class='input_select'>
				".$this->__f__2f138a757969f8dd1a7303488ad779f0($members,$pages,$dropdowns,$defaults,$custom_fields,$url)."			</select>
			{$this->lang->words['sorting_text_with']}
			<select name='max_results' class='input_select'>
				".$this->__f__7eef9b640ccdc72b2d3d262bf969cc0f($members,$pages,$dropdowns,$defaults,$custom_fields,$url)."			</select>
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
<div id='member_list' class='clear block_wrap'>
	<ol id='member_alpha' class='tab_bar'>
		" . ((!$this->request['quickjump']) ? ("
			<li class='active'><a href='" . $this->registry->getClass('output')->formatUrl( $this->registry->getClass('output')->buildUrl( "app=members&amp;section=view&amp;module=list", 'public','' ), "", "" ) . "' title='{$this->lang->words['members_start_with']}{$letter}'>{$this->lang->words['mlist_view_all_txt']}</a></li>
		") : ("
			<li><a href='" . $this->registry->getClass('output')->formatUrl( $this->registry->getClass('output')->buildUrl( "app=members&amp;section=view&amp;module=list", 'public','' ), "", "" ) . "' title='{$this->lang->words['mlist_view_all_title']}'>{$this->lang->words['mlist_view_all_txt']}</a></li>
		")) . "".$this->__f__eee7979fd622ea16d0466cd3dc03a09e($members,$pages,$dropdowns,$defaults,$custom_fields,$url)."	</ol>
	<div class='tab_filters'>
		<ul class='right'>
			<li " . (($this->request['sort_key'] == 'members_display_name' || !$this->request['sort_key']) ? ("class='active'") : ("")) . ">
				<a href='" . $this->registry->getClass('output')->formatUrl( $this->registry->getClass('output')->buildUrl( "app=members&amp;section=view&amp;module=list&amp;{$url}&amp;sort_key=members_display_name&amp;sort_order=asc", 'public','' ), "", "" ) . "' title='{$this->lang->words['sort_by_mname']}'>{$this->lang->words['s_name']}</a>
			</li>
			<li " . (($this->request['sort_key'] == 'posts') ? ("class='active'") : ("")) . ">
				<a href='" . $this->registry->getClass('output')->formatUrl( $this->registry->getClass('output')->buildUrl( "app=members&amp;section=view&amp;module=list&amp;{$url}&amp;sort_key=posts&amp;sort_order=desc", 'public','' ), "", "" ) . "' title='{$this->lang->words['sort_by_posts']}'>{$this->lang->words['pcount']}</a>
			</li>
			<li " . (($this->request['sort_key'] == 'joined') ? ("class='active'") : ("")) . ">
				<a href='" . $this->registry->getClass('output')->formatUrl( $this->registry->getClass('output')->buildUrl( "app=members&amp;section=view&amp;module=list&amp;{$url}&amp;sort_key=joined", 'public','' ), "", "" ) . "' title='{$this->lang->words['sorty_by_jdate']}'>{$this->lang->words['sort_by_joined']}</a>
			</li>
		</ul>
	</div>
	
	<div id='member_wrap'>
		<ul class='members'>
			" . ((is_array( $members ) and count( $members )) ? ("
								".$this->__f__df417e231cf985e6654cd6ba293a262a($members,$pages,$dropdowns,$defaults,$custom_fields,$url)."			") : ("
				<p class='no_messages'>{$this->lang->words['no_results']}</p>
			")) . "
		</ul>
	</div>
</div>
<div class='topic_controls'>
	{$pages}
</div>";
return $IPBHTML;
}


function __f__94a193205f0b5cba87ad9b5bdb91d504($members, $pages="", $dropdowns=array(), $defaults=array(), $custom_fields=null, $url='')
{
	$_ips___x_retval = '';
	foreach( $custom_fields->out_fields as $id => $field )
	{
		
		$_ips___x_retval .= "
			<li class='field custom'>
				<label for='field_{$id}'>{$custom_fields->field_names[$id]}</label>
				{$field}
			</li>
				
";
	}
	$_ips___x_retval .= '';
	return $_ips___x_retval;
}

function __f__125e27e4b37c5382656ea4729fd1507c($members, $pages="", $dropdowns=array(), $defaults=array(), $custom_fields=null, $url='')
{
	$_ips___x_retval = '';
	foreach( $dropdowns['filter'] as $k => $v )
	{
		
		$_ips___x_retval .= "
					<option value='{$k}'" . (($k == $defaults['filter']) ? (" selected='selected'") : ("")) . ">{$v}</option>
				
";
	}
	$_ips___x_retval .= '';
	return $_ips___x_retval;
}

function __f__04b67520b462eb9f7231f3a9d8f9f35f($members, $pages="", $dropdowns=array(), $defaults=array(), $custom_fields=null, $url='')
{
	$_ips___x_retval = '';
	foreach( $dropdowns['sort_key'] as $k => $v )
	{
		
		$_ips___x_retval .= "
					<option value='{$k}'" . (($k == $defaults['sort_key']) ? (" selected='selected'") : ("")) . ">{$this->lang->words[ $v ]}</option>
				
";
	}
	$_ips___x_retval .= '';
	return $_ips___x_retval;
}

function __f__2f138a757969f8dd1a7303488ad779f0($members, $pages="", $dropdowns=array(), $defaults=array(), $custom_fields=null, $url='')
{
	$_ips___x_retval = '';
	foreach( $dropdowns['sort_order'] as $k => $v )
	{
		
		$_ips___x_retval .= "
					<option value='{$k}'" . (($k == $defaults['sort_order']) ? (" selected='selected'") : ("")) . ">{$this->lang->words[ $v ]}</option>
				
";
	}
	$_ips___x_retval .= '';
	return $_ips___x_retval;
}

function __f__7eef9b640ccdc72b2d3d262bf969cc0f($members, $pages="", $dropdowns=array(), $defaults=array(), $custom_fields=null, $url='')
{
	$_ips___x_retval = '';
	foreach( $dropdowns['max_results'] as $k => $v )
	{
		
		$_ips___x_retval .= "
					<option value='{$k}'" . (($k == $defaults['max_results']) ? (" selected='selected'") : ("")) . ">{$v}</option>
				
";
	}
	$_ips___x_retval .= '';
	return $_ips___x_retval;
}

function __f__eee7979fd622ea16d0466cd3dc03a09e($members, $pages="", $dropdowns=array(), $defaults=array(), $custom_fields=null, $url='')
{
	$_ips___x_retval = '';
	foreach( range(65,90) as $char )
	{
		
		$_ips___x_retval .= "
	" . (($letter = strtoupper(chr($char))) ? ("<li " . ((strtoupper( $this->request['quickjump'] ) == $letter) ? ("class='active'") : ("")) . "><a href='" . $this->registry->getClass('output')->formatUrl( $this->registry->getClass('output')->buildUrl( "app=members&amp;section=view&amp;module=list&amp;{$url}&amp;quickjump={$letter}", 'public','' ), "", "" ) . "' title='{$this->lang->words['mlist_view_start_title']} {$letter}'>{$letter}</a></li>") : ("")) . "

";
	}
	$_ips___x_retval .= '';
	return $_ips___x_retval;
}

function __f__df417e231cf985e6654cd6ba293a262a($members, $pages="", $dropdowns=array(), $defaults=array(), $custom_fields=null, $url='')
{
	$_ips___x_retval = '';
	foreach( $members as $member )
	{
		
		$_ips___x_retval .= "
					<li id='member_id_{$member['member_id']}' class='general_box clear member_entry " .  IPSLib::next( $this->registry->templateStriping["memberStripe"] ) . "'>
						<h3 class='bar'><a href='" . $this->registry->getClass('output')->formatUrl( $this->registry->getClass('output')->buildUrl( "showuser={$member['member_id']}", 'public','' ), "{$member['members_seo_name']}", "showuser" ) . "' title='{$this->lang->words['view_profile']}'>{$member['members_display_name']}</a>" . $this->registry->getClass('output')->getTemplate('global')->user_popup($member['member_id'], $member['members_seo_name']) . "</h3>
						<a href='" . $this->registry->getClass('output')->formatUrl( $this->registry->getClass('output')->buildUrl( "showuser={$member['member_id']}", 'public','' ), "{$member['members_seo_name']}", "showuser" ) . "' title='{$this->lang->words['view_profile']}'><img src='{$member['pp_thumb_photo']}' width='{$member['pp_thumb_width']}' height='{$member['pp_thumb_height']}' alt=\"{$member['members_display_name']}{$this->lang->words['users_photo']}\" class='photo' /></a>	
					
						<dl class='info'>
							<dt class='clear'>{$this->lang->words['member_joined']}:</dt>
							<dd>" . $this->registry->getClass('class_localization')->getDate($member['joined'],"joined", 0) . "</dd>
							<dt class='clear'>{$this->lang->words['member_group']}:</dt>
							<dd>" . IPSLib::makeNameFormatted( $member['group'], $member['member_group_id'] ) . "</dd>
							<dt class='clear'>{$this->lang->words['member_posts']}:</dt>
							<dd>" . $this->registry->getClass('class_localization')->formatNumber( $member['posts'] ) . "</dd>
							<dt class='clear'>{$this->lang->words['m_views']}:</dt>
							<dd>" . $this->registry->getClass('class_localization')->formatNumber( $member['members_profile_views'] ) . "</dd>
						</dl>
						
						<ul class='user_controls clear'>
							" . (($this->memberData['member_id'] AND $this->memberData['member_id'] != $member['member_id'] && $this->settings['friends_enabled'] AND $this->memberData['g_can_add_friends']) ? ("" . ((IPSMember::checkFriendStatus( $member['member_id'] )) ? ("
									<li class='mini_friend_toggle is_friend' id='friend_mlist_{$member['member_id']}'><a href='" . $this->registry->getClass('output')->formatUrl( $this->registry->getClass('output')->buildUrl( "app=members&amp;module=profile&amp;section=friends&amp;do=remove&amp;member_id={$member['member_id']}", 'public','' ), "", "" ) . "' title='{$this->lang->words['remove_friend']}'>" . $this->registry->getClass('output')->getReplacement("remove_friend") . "</a></li>
								") : ("
									<li class='mini_friend_toggle is_not_friend' id='friend_mlist_{$member['member_id']}'><a href='" . $this->registry->getClass('output')->formatUrl( $this->registry->getClass('output')->buildUrl( "app=members&amp;module=profile&amp;section=friends&amp;do=add&amp;member_id={$member['member_id']}", 'public','' ), "", "" ) . "' title='{$this->lang->words['add_friend']}'>" . $this->registry->getClass('output')->getReplacement("add_friend") . "</a></li>								
								")) . "") : ("")) . "
							" . (($this->memberData['g_use_pm'] AND $this->memberData['members_disable_pm'] == 0 AND IPSLib::moduleIsEnabled( 'messaging', 'members' ) && $member['member_id'] != $this->memberData['member_id']) ? ("
								<li class='pm_button' id='pm_xxx_{$member['pp_member_id']}'><a href='" . $this->registry->getClass('output')->formatUrl( $this->registry->getClass('output')->buildUrl( "app=members&amp;module=messaging&amp;section=send&amp;do=form&amp;fromMemberID={$member['pp_member_id']}", 'public','' ), "", "" ) . "' title='{$this->lang->words['pm_member']}'>" . $this->registry->getClass('output')->getReplacement("send_msg") . "</a></li>
							") : ("")) . "
							<li><a href='" . $this->registry->getClass('output')->formatUrl( $this->registry->getClass('output')->buildUrl( "app=core&amp;module=search&amp;do=user_posts&amp;mid={$member['member_id']}&amp;view_by_title=1&amp;search_filter_app[forums]=1", 'public','' ), "", "" ) . "'>" . $this->registry->getClass('output')->getReplacement("find_topics_link") . " {$this->lang->words['find_topics']}</a></li>
							<li><a href='" . $this->registry->getClass('output')->formatUrl( $this->registry->getClass('output')->buildUrl( "app=core&amp;module=search&amp;do=user_posts&amp;mid={$member['member_id']}" . ((! in_array( $this->settings['search_method'], array( 'traditional', 'sphinx' ) )) ? ("&amp;search_filter_app[forums]=1") : ("")) . "", 'public','' ), "", "" ) . "'>" . $this->registry->getClass('output')->getReplacement("find_posts_link") . " {$this->lang->words['find_posts']}</a></li>
							" . (($member['has_blog'] AND IPSLib::appIsInstalled( 'blog' )) ? ("
								<li><a href='" . $this->registry->getClass('output')->formatUrl( $this->registry->getClass('output')->buildUrl( "app=blog&amp;module=display&amp;section=blog&amp;mid={$member['member_id']}", 'public','' ), "", "" ) . "' title='{$this->lang->words['view_blog']}'>" . $this->registry->getClass('output')->getReplacement("blog_link") . "</a></li>
							") : ("")) . "
							" . (($member['has_gallery'] AND IPSLib::appIsInstalled( 'gallery' )) ? ("
								<li><a href='" . $this->registry->getClass('output')->formatUrl( $this->registry->getClass('output')->buildUrl( "app=gallery&amp;module=user&amp;section=user&amp;user={$member['member_id']}", 'public','' ), "", "" ) . "' title='{$this->lang->words['view_gallery']}'>" . $this->registry->getClass('output')->getReplacement("gallery_link") . "</a></li>
							") : ("")) . "
						</ul>
						" . (($this->settings['pp_allow_member_rate']) ? ("<p class='rating'> 
								" . (($member['pp_rating_real'] >= 1) ? ("" . $this->registry->getClass('output')->getReplacement("rate_on") . "") : ("" . $this->registry->getClass('output')->getReplacement("rate_off") . "")) . "" . (($member['pp_rating_real'] >= 2) ? ("" . $this->registry->getClass('output')->getReplacement("rate_on") . "") : ("" . $this->registry->getClass('output')->getReplacement("rate_off") . "")) . "" . (($member['pp_rating_real'] >= 3) ? ("" . $this->registry->getClass('output')->getReplacement("rate_on") . "") : ("" . $this->registry->getClass('output')->getReplacement("rate_off") . "")) . "" . (($member['pp_rating_real'] >= 4) ? ("" . $this->registry->getClass('output')->getReplacement("rate_on") . "") : ("" . $this->registry->getClass('output')->getReplacement("rate_off") . "")) . "" . (($member['pp_rating_real'] >= 5) ? ("" . $this->registry->getClass('output')->getReplacement("rate_on") . "") : ("" . $this->registry->getClass('output')->getReplacement("rate_off") . "")) . "
							</p>") : ("")) . "
						
						" . (($this->settings['reputation_enabled'] && $this->settings['reputation_show_profile']) ? ("" . (($member['pp_reputation_points'] == 0 || !$member['pp_reputation_points']) ? ("
								<p class='reputation zero'>
							") : ("")) . "
							" . (($member['pp_reputation_points'] > 0) ? ("
								<p class='reputation positive'>
							") : ("")) . "
							" . (($member['pp_reputation_points'] < 0) ? ("
								<p class='reputation negative'>
							") : ("")) . "							
									<span class='number'>{$this->lang->words['reputation']}: {$member['pp_reputation_points']}</span>
								</p>") : ("")) . "						
					</li>
				
";
	}
	$_ips___x_retval .= '';
	return $_ips___x_retval;
}



}

/*--------------------------------------------------*/
/* END OF FILE                                      */
/*--------------------------------------------------*/

?>