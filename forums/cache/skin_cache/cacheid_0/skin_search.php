<?php
/*--------------------------------------------------*/
/* FILE GENERATED BY INVISION POWER BOARD 3         */
/* CACHE FILE: Skin set id: 0               */
/* CACHE FILE: Generated: Tue, 04 Aug 2009 19:56:24 GMT */
/* DO NOT EDIT DIRECTLY - THE CHANGES WILL NOT BE   */
/* WRITTEN TO THE DATABASE AUTOMATICALLY            */
/*--------------------------------------------------*/

class skin_search_0 {

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
	/* -- activePostsView --*/
function activePostsView($results, $pagination, $total) {
$IPBHTML = "";

$this->registry->templateStriping['searchStripe'] = array( FALSE, "row1","row2");
$IPBHTML .= "<h2>{$this->lang->words['active_content']}</h2>
<p class='message'>
	{$this->lang->words['active_have_been']} <em><strong>{$total}</strong></em> {$this->lang->words['active_entries']}
</p>
<br />
<div class='topic_controls'>
{$pagination}
</div>
<ol id='member_alpha' class='tab_bar no_title'>
	" . ((! in_array( $this->settings['search_method'], array( 'traditional', 'sphinx' ) )) ? ("" . ((isset( $this->request['search_filter_app'] ) && $this->request['search_filter_app']['all'] != 1) ? ("
			<li><a href='" . $this->registry->getClass('output')->formatUrl( $this->registry->getClass('output')->buildUrl( "app=core&amp;module=search&amp;do=active&amp;search_filter_app[all]=1", 'public','' ), "", "" ) . "'>{$this->lang->words['active_all']}</a></li>
		") : ("
			<li class='active'><strong>{$this->lang->words['active_all']}</strong></li>
		")) . "") : ("")) . "
	".$this->__f__36b08950ebd58ef61555b70b778e3157($results,$pagination,$total)."</ol>
" . (($total) ? ("
		<div id='search_results'>
		<ol>
			".$this->__f__f3051033036ec9813a7fb93d3ebde270($results,$pagination,$total)."		</ol>
	</div>
	{$pagination}
") : ("
	<div class='no_messages'>{$this->lang->words['active_none']}</div>
")) . "";
return $IPBHTML;
}


function __f__36b08950ebd58ef61555b70b778e3157($results, $pagination, $total)
{
	$_ips___x_retval = '';
	foreach( $this->registry->getApplications() as $app )
	{
		
		$_ips___x_retval .= "
		" . ((IPSSearchIndex::appIsSearchable( $app['app_directory'] )) ? ("" . (($this->request['search_app'] == $app['app_directory']) ? ("<li class='active'><strong>" . ((strtolower($app['app_title']) == 'system') ? ("{$this->lang->words['livesearch_helpfiles']}") : ("{$app['app_public_title']}")) . "</strong></li>") : ("<li><a href='" . $this->registry->getClass('output')->formatUrl( $this->registry->getClass('output')->buildUrl( "app=core&amp;module=search&amp;do=active&amp;search_filter_app[{$app['app_directory']}]=1", 'public','' ), "", "" ) . "'>" . ((strtolower($app['app_title']) == 'system') ? ("{$this->lang->words['livesearch_helpfiles']}") : ("{$app['app_public_title']}")) . "</a></li>")) . "") : ("")) . "
	
";
	}
	$_ips___x_retval .= '';
	return $_ips___x_retval;
}

function __f__f3051033036ec9813a7fb93d3ebde270($results, $pagination, $total)
{
	$_ips___x_retval = '';
	foreach( $results as $result )
	{
		
		$_ips___x_retval .= "	
				" . (($result['sub']) ? ("
					<li class='" .  IPSLib::next( $this->registry->templateStriping["searchStripe"] ) . " sub clear clearfix'>
						{$result['html']}
					</li>
				") : ("
					<li class='" .  IPSLib::next( $this->registry->templateStriping["searchStripe"] ) . " clear clearfix'>
						{$result['html']}
					</li>
				")) . "
			
";
	}
	$_ips___x_retval .= '';
	return $_ips___x_retval;
}

/* -- calEventRangedSearchResult --*/
function calEventRangedSearchResult($r, $resultAsTitle=false) {
$IPBHTML = "";
$IPBHTML .= "<div class='result_info'>
	<h3><a href='" . $this->registry->getClass('output')->formatUrl( $this->registry->getClass('output')->buildUrl( "app=calendar&amp;module=calendar&amp;cal_id={$r['type_id']}&amp;do=showevent&amp;event_id={$r['type_id_2']}&amp;hl={$this->request['search_higlight']}", 'public','' ), "", "" ) . "'>{$r['content_title']}</a> (" . $this->registry->getClass('class_localization')->getDate($r['updated'],"short", 0) . " {$this->lang->words['to']} " . $this->registry->getClass('class_localization')->getDate($r['misc'],"short", 0) . ")</h3>
	" . ((!$resultAsTitle) ? ("
	<p>
		{$r['content']}
	</p>
	") : ("")) . "
</div>
<div class='result_details desc'>
	<ul>
		<li>{$this->lang->words['search_by']} <a href='" . $this->registry->getClass('output')->formatUrl( $this->registry->getClass('output')->buildUrl( "showuser={$r['member_id']}", 'public','' ), "{$r['members_seo_name']}", "showuser" ) . "' title='{$this->lang->words['view_profile']}'>{$r['members_display_name']}</a>" . $this->registry->getClass('output')->getTemplate('global')->user_popup($r['member_id']) . "</li>
	</ul>
</div>";
return $IPBHTML;
}

/* -- calEventSearchResult --*/
function calEventSearchResult($r, $resultAsTitle=false) {
$IPBHTML = "";
$IPBHTML .= "<div class='result_info'>
	<h3><a href='" . $this->registry->getClass('output')->formatUrl( $this->registry->getClass('output')->buildUrl( "app=calendar&amp;module=calendar&amp;cal_id={$r['type_id']}&amp;do=showevent&amp;event_id={$r['type_id_2']}&amp;hl={$this->request['search_higlight']}", 'public','' ), "", "" ) . "'>{$r['content_title']}</a> (" . $this->registry->getClass('class_localization')->getDate($r['updated'],"short", 0) . " {$this->lang->words['to']} " . $this->registry->getClass('class_localization')->getDate($r['misc'],"short", 0) . ")</h3>
	" . ((!$resultAsTitle) ? ("
	<p>
		{$r['content']}
	</p>
	") : ("")) . "
</div>
<div class='result_details desc'>
	<ul>
		<li>{$this->lang->words['search_by']} <a href='" . $this->registry->getClass('output')->formatUrl( $this->registry->getClass('output')->buildUrl( "showuser={$r['member_id']}", 'public','' ), "{$r['members_seo_name']}", "showuser" ) . "' title='{$this->lang->words['view_profile']}'>{$r['members_display_name']}</a>" . $this->registry->getClass('output')->getTemplate('global')->user_popup($r['member_id']) . "</li>
	</ul>
</div>";
return $IPBHTML;
}

/* -- forumAdvancedSearchFilters --*/
function forumAdvancedSearchFilters($forums) {
$IPBHTML = "";
$IPBHTML .= "<fieldset id='app_filter_forums' class='" .  IPSLib::next( $this->registry->templateStriping["search"] ) . " extra_filter'>
	<ul>
		<li>
			<label for='forums_filter'>{$this->lang->words['find_forum']}:</label>
			<select name='search_app_filters[forums][]' class='input_select' size='6' multiple='multiple'>
				{$forums}
			</select>
		</li>
	</ul>
</fieldset>";
return $IPBHTML;
}

/* -- forumSearchResult --*/
function forumSearchResult($r, $resultAsTitle=false) {
$IPBHTML = "";
$IPBHTML .= "<result>
	<icon>{$this->settings['public_dir']}style_extra/app_icons/comments.png</icon>
	<type>Forum</type>
	<url>" . $this->registry->getClass('output')->formatUrl( $this->registry->getClass('output')->buildUrl( "showforum={$r['type_id']}", 'public','' ), "", "" ) . "</url>
	<title><![CDATA[{$r['content_title']}]]></title>
	<content><![CDATA[{$r['content']}]]></content>
	<date>" . $this->registry->getClass('class_localization')->getDate($r['updated'],"short", 0) . "</date>
	<user>
		<id>{$r['member_id']}</id>
		<name>{$r['members_display_name']}</name>
		<url>" . $this->registry->getClass('output')->formatUrl( $this->registry->getClass('output')->buildUrl( "showuser={$r['member_id']}", 'public','' ), "", "" ) . "</url>
	</user>
</result>";
return $IPBHTML;
}

/* -- galleryCommentSearchResult --*/
function galleryCommentSearchResult($r, $resultAsTitle=false) {
$IPBHTML = "";
$IPBHTML .= "<result>
	<icon>{$this->settings['public_dir']}style_extra/app_icons/gallery.png</icon>
	<type>Gallery Comment</type>
	<url>" . $this->registry->getClass('output')->formatUrl( $this->registry->getClass('output')->buildUrl( "app=gallery&amp;module=images&amp;img={$r['misc']}&amp;hl={$this->request['search_term']}", 'public','' ), "", "" ) . "</url>
	<title><![CDATA[{$r['content_title']}]]></title>
	<content><![CDATA[{$r['content']}]]></content>
	<date>" . $this->registry->getClass('class_localization')->getDate($r['updated'],"short", 0) . "</date>
	<user>
		<id>{$r['member_id']}</id>
		<name>{$r['members_display_name']}</name>
		<url>" . $this->registry->getClass('output')->formatUrl( $this->registry->getClass('output')->buildUrl( "showuser={$r['member_id']}", 'public','' ), "", "" ) . "</url>
	</user>
</result>";
return $IPBHTML;
}

/* -- galleryImageSearchResult --*/
function galleryImageSearchResult($r, $resultAsTitle=false) {
$IPBHTML = "";
$IPBHTML .= "<div class='result_info'>
	<span class='icon'><img src='" . $this->registry->getClass('output')->formatUrl( $this->registry->getClass('output')->buildUrl( "{$r['misc']['directory']}/tn_{$r['misc']['masked_file_name']}", 'upload','' ), "", "" ) . "'></span>
	
	<h3><a href='" . $this->registry->getClass('output')->formatUrl( $this->registry->getClass('output')->buildUrl( "app=gallery&amp;module=images&amp;section=viewimage&amp;img={$r['type_id_2']}", 'public','' ), "", "" ) . "'>{$r['content_title']}</a></h3>
	" . ((!$resultAsTitle) ? ("
		<p>
			{$r['content']}
		</p>
	") : ("")) . "
</div>
<div class='result_details desc'>
	<ul>
		<li>{$r['comments']} {$this->lang->words['gallery_comments']}</li>
		<li>" . $this->registry->getClass('class_localization')->getDate($r['updated'],"short", 0) . "</li>
		<li><a href='" . $this->registry->getClass('output')->formatUrl( $this->registry->getClass('output')->buildUrl( "showuser={$r['member_id']}", 'public','' ), "{$r['members_seo_name']}", "showuser" ) . "' title='{$this->lang->words['view_profile']}'>{$r['members_display_name']}</a>" . $this->registry->getClass('output')->getTemplate('global')->user_popup($r['member_id'], $r['members_seo_name']) . "</li>
	</ul>
</div>";
return $IPBHTML;
}

/* -- helpSearchResult --*/
function helpSearchResult($r, $resultAsTitle=false) {
$IPBHTML = "";
$IPBHTML .= "<div class='result_info'>
	<h3><a href='" . $this->registry->getClass('output')->formatUrl( $this->registry->getClass('output')->buildUrl( "app=core&amp;module=help&amp;do=01&amp;HID={$r['type_id_2']}&amp;hl={$this->request['search_higlight']}", 'public','' ), "", "" ) . "'>{$r['content_title']}</a></h3>
	" . ((!$resultAsTitle) ? ("
	<p>
		{$r['content']}
	</p>
	") : ("")) . "
</div>";
return $IPBHTML;
}

/* -- memberSearchResult --*/
function memberSearchResult($r, $resultAsTitle=false) {
$IPBHTML = "";
$IPBHTML .= "" . ((!$resultAsTitle) ? ("<span class='icon'>
		" . (($r['misc']['pp_thumb_photo']) ? ("
			<img src='" . $this->registry->getClass('output')->formatUrl( $this->registry->getClass('output')->buildUrl( "/{$r['misc']['pp_thumb_photo']}", 'upload','' ), "", "" ) . "' class='photo' />
		") : ("
			<img src='{$this->settings['img_url']}/profile/default_thumb.png' class='photo' />
		")) . "
	</span>") : ("")) . "
<div class='result_info'>
	<h3><a href='" . $this->registry->getClass('output')->formatUrl( $this->registry->getClass('output')->buildUrl( "showuser={$r['type_id_2']}", 'public','' ), "{$r['members_seo_name']}", "" ) . "'>{$r['content_title']}</a>" . $this->registry->getClass('output')->getTemplate('global')->user_popup($r['type_id_2'],$r['members_seo_name']) . "</h3>
	" . ((!$resultAsTitle) ? ("
	<p>
		{$r['misc']['pp_bio_content']}
	</p>
	") : ("")) . "
	<span class='desc breadcrumb'>
		{$this->lang->words['member_joined']} " . $this->registry->getClass('class_localization')->getDate($r['joined'],"short", 0) . "
	</span>
</div>";
return $IPBHTML;
}

/* -- newPostsView --*/
function newPostsView($results, $pagination, $total) {
$IPBHTML = "";

$this->registry->templateStriping['searchStripe'] = array( FALSE, "row1","row2");
$IPBHTML .= "<h2>{$this->lang->words['new_content']}</h2>
" . (($total) ? ("
	<p class='message'>
		{$this->lang->words['new_content_there']} <strong>{$total}</strong> {$this->lang->words['new_content_entries']}
	</p>
	<br />
	<div class='topic_controls'>
		{$pagination}
	</div>
") : ("")) . "
<ol id='member_alpha' class='tab_bar no_title'>
	" . ((! in_array( $this->settings['search_method'], array( 'traditional', 'sphinx' ) )) ? ("" . ((isset( $this->request['search_filter_app'] ) && $this->request['search_filter_app']['all'] != 1) ? ("
			<li><a href='" . $this->registry->getClass('output')->formatUrl( $this->registry->getClass('output')->buildUrl( "app=core&amp;module=search&amp;do=new_posts&amp;search_filter_app[all]=1", 'public','' ), "", "" ) . "'>{$this->lang->words['new_content_all']}</a></li>
		") : ("
			<li class='active'><strong>{$this->lang->words['new_content_all']}</strong></li>
		")) . "") : ("")) . "
	".$this->__f__0a260cd33d1d2a9214e0d538a6ebde18($results,$pagination,$total)."</ol>
" . (($total) ? ("
		<div id='search_results'>
		<ol>
			".$this->__f__49aafb81ddddc79df227344b6084acbe($results,$pagination,$total)."		</ol>
	</div>
	{$pagination}
") : ("
	<p class='no_messages'>{$this->lang->words['new_content_none']}</p>
")) . "";
return $IPBHTML;
}


function __f__0a260cd33d1d2a9214e0d538a6ebde18($results, $pagination, $total)
{
	$_ips___x_retval = '';
	foreach( $this->registry->getApplications() as $app )
	{
		
		$_ips___x_retval .= "
		" . ((IPSSearchIndex::appIsSearchable( $app['app_directory'] )) ? ("" . (($this->request['search_app'] == $app['app_directory']) ? ("<li class='active'><strong>" . ((strtolower($app['app_title']) == 'system') ? ("{$this->lang->words['livesearch_helpfiles']}") : ("{$app['app_public_title']}")) . "</strong></li>") : ("<li><a href='" . $this->registry->getClass('output')->formatUrl( $this->registry->getClass('output')->buildUrl( "app=core&amp;module=search&amp;do=new_posts&amp;search_filter_app[{$app['app_directory']}]=1", 'public','' ), "", "" ) . "'>" . ((strtolower($app['app_title']) == 'system') ? ("{$this->lang->words['livesearch_helpfiles']}") : ("{$app['app_public_title']}")) . "</a></li>")) . "") : ("")) . "
	
";
	}
	$_ips___x_retval .= '';
	return $_ips___x_retval;
}

function __f__49aafb81ddddc79df227344b6084acbe($results, $pagination, $total)
{
	$_ips___x_retval = '';
	foreach( $results as $result )
	{
		
		$_ips___x_retval .= "	
				" . (($result['sub']) ? ("
					<li class='" .  IPSLib::next( $this->registry->templateStriping["searchStripe"] ) . " sub clear clearfix'>
						{$result['html']}
					</li>
				") : ("
					<li class='" .  IPSLib::next( $this->registry->templateStriping["searchStripe"] ) . " clear clearfix'>
						{$result['html']}
					</li>
				")) . "
			
";
	}
	$_ips___x_retval .= '';
	return $_ips___x_retval;
}

/* -- searchAdvancedForm --*/
function searchAdvancedForm($filters='', $msg='', $removed_search_terms=array()) {
$IPBHTML = "";
$IPBHTML .= "<style type='text/css'>
 	@import url('{$this->settings['public_dir']}/style_css/{$this->registry->output->skin['_csscacheid']}/calendar_select.css');
</style>
<script type='text/javascript' src='{$this->settings['public_dir']}js/3rd_party/calendar_date_select/calendar_date_select.js'></script>
" . $this->registry->getClass('output')->addJSModule("search", "0" ) . "
<form action=\"" . $this->registry->getClass('output')->formatUrl( $this->registry->getClass('output')->buildUrl( "app=core&amp;module=search&amp;section=search&amp;do=quick_search&amp;search_app={$this->registry->getCurrentApplication()}", 'public','' ), "", "" ) . "&amp;fromsearch=1\" method=\"post\" id='search-box' >
<input type='hidden' name='search_filter_app[all]' value='1' />
<p class='message'>
	<strong>{$this->lang->words['optional_fields']}</strong>
</p>
" . ((is_array( $removed_search_terms ) && count( $removed_search_terms )) ? ("
<br />
<p class='message error'>{$this->lang->words['removed_search_terms']} <strong>" . implode( ',', $removed_search_terms ) . "</strong></p>
") : ("")) . "
" . (($msg) ? ("
<br />
<p class='message error'>{$msg}</p>
") : ("")) . "
<br />
<h2 class='maintitle'>{$this->lang->words['search']}</h2>
<div id='main_search_form' class='clear'>
	
	<div id='search_info' class='row1'>
		<div class='generic_bar'></div>
		<fieldset id='main_search' class='row2'>
			<ul>
				<li>
					<label for='query'>{$this->lang->words['find_words']}:</label>
					<input type='text' class='input_text' name='search_term' id='query' value='{$this->request['search_term']}' /><br />
				</li>
				" . (($this->settings['search_method'] != 'sphinx') ? ("<li>
					<!--SKINNOTE: This isn't semantic - the text for the checkbox should be wrapped in the label, and label should be \"for\" the element-->
						<label>&nbsp;</label>
						<input type='checkbox' class='input_check' name='content_title_only' value='1'> {$this->lang->words['search_titles_only']}
						" . (($this->settings['enable_show_as_titles']) ? ("
							<label>&nbsp;</label>
							<input type='checkbox' class='input_check' name='show_as_titles' value='1'> {$this->lang->words['show_as_titles']}
						") : ("")) . "
					</li>") : ("")) . "
				<li>
					<label for='author'>{$this->lang->words['find_author']}:</label>
					<input type='text' class='input_text' name='search_author' id='author' value='{$this->request['search_author']}' />
				</li>
				<li>			
					<label for='date_start'>{$this->lang->words['find_date']}:</label>
					<input type='text' class='input_text date' name='search_date_start' id='date_start' value='{$this->request['_search_date_start']}' /><img src='{$this->settings['img_url']}/date.png' alt='{$this->lang->words['icon']}' id='date_start_icon' style='cursor: pointer' /> &nbsp;
					<strong>{$this->lang->words['to']}</strong> &nbsp;<input type='text' class='input_text date' name='search_date_end' id='date_end' value='{$this->request['_search_date_end']}' /><img src='{$this->settings['img_url']}/date.png' alt='{$this->lang->words['icon']}' id='date_end_icon' style='cursor: pointer' />
				</li>
			</ul>
		</fieldset>
		{$filters}
		<fieldset class='submit'>
			<input type='submit' name='submit' class='input_submit' value='{$this->lang->words['do_search']}'> {$this->lang->words['or']} <a href='" . $this->registry->getClass('output')->formatUrl( $this->registry->getClass('output')->buildUrl( "", 'public','' ), "", "" ) . "' title='{$this->lang->words['cancel']}' class='cancel'>{$this->lang->words['cancel']}</a>
		</fieldset>
	</div>
</div>
</form>
<script type='text/javascript'>
	ipb.search.initSearchForm();
</script>";
return $IPBHTML;
}

/* -- searchResults --*/
function searchResults($results, $pagination, $total, $showing, $search_term, $url_string, $current_key, $removed_search_terms=array()) {
$IPBHTML = "";

$sortby_urlstring = preg_replace('/(&|&amp;)search_sort_by=(.+?)(&amp;|&|$)/', '&amp;', $url_string);

$this->registry->templateStriping['searchStripe'] = array( FALSE, "row1","row2");
$IPBHTML .= "<h2 class='hide'>{$this->lang->words['search_results']}</h2>
" . ((is_array( $removed_search_terms ) && count( $removed_search_terms )) ? ("
<p class='message error'>{$this->lang->words['removed_search_terms']} <strong>" . implode( ',', $removed_search_terms ) . "</strong></p>
<br />
") : ("")) . "
	" . (($total > 0 AND $search_term != '') ? ("
		<p class='message'>
			{$this->lang->words['your_search']} <em><strong>{$search_term}</strong></em> {$this->lang->words['your_search_returned']} <strong>{$total}</strong> {$this->lang->words['your_search_results']}
		</p>
	") : ("")) . "
	<br />
	<div class='topic_controls'>
		{$pagination}
	</div>
	
	<ol id='member_alpha' class='tab_bar'>
		" . ((! in_array( $this->settings['search_method'], array( 'traditional', 'sphinx' ) )) ? ("" . (($this->request['search_filter_app']['all'] != 1) ? ("
				<li><a href='" . $this->registry->getClass('output')->formatUrl( $this->registry->getClass('output')->buildUrl( "{$url_string}", 'public','' ), "", "" ) . "'>{$this->lang->words['my_results']}</a></li>
			") : ("
				<li class='active'><strong>{$this->lang->words['my_results']}</strong></li>
			")) . "") : ("")) . "
		
		<!--harcoded forums, members, system apps-->
		" . ((isset( $this->request['search_filter_app']['forums'] ) && $this->request['search_filter_app']['forums'] == 1) ? ("
			<li class='active'><strong>{$this->lang->words['search_tab_forums']}</strong></li>
		") : ("				
			<li><a href='" . $this->registry->getClass('output')->formatUrl( $this->registry->getClass('output')->buildUrl( "{$url_string}&amp;search_filter_app[forums]=1", 'public','' ), "", "" ) . "'>{$this->lang->words['search_tab_forums']}</a></li>
		")) . "
		" . ((isset( $this->request['search_filter_app']['members'] ) && $this->request['search_filter_app']['members'] == 1) ? ("
			<li class='active'><strong>{$this->lang->words['search_tab_members']}</strong></li>
		") : ("
			<li><a href='" . $this->registry->getClass('output')->formatUrl( $this->registry->getClass('output')->buildUrl( "{$url_string}&amp;search_filter_app[members]=1", 'public','' ), "", "" ) . "'>{$this->lang->words['search_tab_members']}</a></li>
		")) . "
		" . ((isset( $this->request['search_filter_app']['core'] ) && $this->request['search_filter_app']['core'] == 1) ? ("
			<li class='active'><strong>{$this->lang->words['livesearch_helpfiles']}</strong></li>
		") : ("				
			<li><a href='" . $this->registry->getClass('output')->formatUrl( $this->registry->getClass('output')->buildUrl( "{$url_string}&amp;search_filter_app[core]=1", 'public','' ), "", "" ) . "'>{$this->lang->words['livesearch_helpfiles']}</a></li>
		")) . "
		".$this->__f__4a6014fd5db305f0d5a0e1cd868b23da($results,$pagination,$total,$showing,$search_term,$url_string,$current_key,$removed_search_terms)."	</ol>
	<div class='tab_filters'>
		<ul class='right'>
						" . (($this->request['search_sort_by'] == 'relevence' || ! $this->request['search_sort_by']) ? ("
				<li class='active'>{$this->lang->words['sort_by_relevance']}</li>
			") : ("
				<li><a href='" . $this->registry->getClass('output')->formatUrl( $this->registry->getClass('output')->buildUrl( "{$sortby_urlstring}&amp;search_sort_by=relevence&amp;search_filter_app[{$current_key}]=1", 'public','' ), "", "" ) . "'>{$this->lang->words['sort_by_relevance']}</a></li>
			")) . "
	
			" . (($this->request['search_sort_by'] == 'date') ? ("
				<li class='active'>{$this->lang->words['sort_by_date']}</li>
			") : ("
				<li><a href='" . $this->registry->getClass('output')->formatUrl( $this->registry->getClass('output')->buildUrl( "{$sortby_urlstring}&amp;search_sort_by=date&amp;search_filter_app[{$current_key}]=1", 'public','' ), "", "" ) . "'>{$this->lang->words['sort_by_date']}</a></li>
			")) . "
		</ul>
	</div>
	
" . (($total) ? ("
		<div id='search_results'>
		<ol>
			".$this->__f__93573805d0b55fef700912dc2c8578bb($results,$pagination,$total,$showing,$search_term,$url_string,$current_key,$removed_search_terms)."		</ol>
	</div>
	<br />
	<div class='topic_controls'>
		{$pagination}
	</div>
") : ("<p class='no_messages'>{$this->lang->words['no_results_found']}" . (($search_term) ? (" {$this->lang->words['no_results_found_for']} '{$search_term}'") : ("")) . ".</p>")) . "";
return $IPBHTML;
}


function __f__4a6014fd5db305f0d5a0e1cd868b23da($results, $pagination, $total, $showing, $search_term, $url_string, $current_key, $removed_search_terms=array())
{
	$_ips___x_retval = '';
	foreach( $this->registry->getApplications() as $app )
	{
		
		$_ips___x_retval .= "
			" . ((IPSSearchIndex::appIsSearchable( $app['app_directory'] ) AND !in_array( $app['app_directory'], array('core','forums','members') )) ? ("" . ((isset( $this->request['search_filter_app'][$app['app_directory']] ) && $this->request['search_filter_app'][$app['app_directory']] == 1) ? ("
					<li class='active'><strong>{$app['app_public_title']}</strong></li>
				") : ("				
					<li><a href='" . $this->registry->getClass('output')->formatUrl( $this->registry->getClass('output')->buildUrl( "{$url_string}&amp;search_filter_app[{$app['app_directory']}]=1", 'public','' ), "", "" ) . "'>{$app['app_public_title']}</a></li>
				")) . "") : ("")) . "
		
";
	}
	$_ips___x_retval .= '';
	return $_ips___x_retval;
}

function __f__93573805d0b55fef700912dc2c8578bb($results, $pagination, $total, $showing, $search_term, $url_string, $current_key, $removed_search_terms=array())
{
	$_ips___x_retval = '';
	foreach( $results as $result )
	{
		
		$_ips___x_retval .= "	
				" . (($result['sub']) ? ("
					<li class='" .  IPSLib::next( $this->registry->templateStriping["searchStripe"] ) . " sub clearfix clear'>
						{$result['html']}
					</li>
				") : ("
					<li class='" .  IPSLib::next( $this->registry->templateStriping["searchStripe"] ) . " clearfix clear'>
						{$result['html']}
					</li>
				")) . "
			
";
	}
	$_ips___x_retval .= '';
	return $_ips___x_retval;
}

/* -- searchRowGenericFormat --*/
function searchRowGenericFormat($r, $resultAsTitle=false) {
$IPBHTML = "";
$IPBHTML .= "<!-- SKINNOTE: Wrap user link in ajax span -->
<div class='result_info'>
	<h3>{$r['content_title']}</h3>
	" . ((!$resultAsTitle) ? ("
	<p>
		{$r['content']}
	</p>
	") : ("")) . "
</div>
<div class='result_details desc'>
	<ul>
		<li>" . $this->registry->getClass('class_localization')->getDate($r['updated'],"short", 0) . "</li>
		<li>{$this->lang->words['search_by']} <a href='" . $this->registry->getClass('output')->formatUrl( $this->registry->getClass('output')->buildUrl( "showuser={$r['member_id']}", 'public','' ), "{$r['members_seo_name']}", "showuser" ) . "'>{$r['members_display_name']}</a>" . $this->registry->getClass('output')->getTemplate('global')->user_popup($r['member_id']) . "</li>
	</ul>
</div>";
return $IPBHTML;
}

/* -- topicPostSearchResult --*/
function topicPostSearchResult($r, $indent, $resultAsTitle=false) {
$IPBHTML = "";
$IPBHTML .= "<div class='result_info'>
	<span class='icon'>" . $this->registry->getClass('output')->getReplacement("{$r['_icon']}") . "</span>
	<h3><a href='" . $this->registry->getClass('output')->formatUrl( $this->registry->getClass('output')->buildUrl( "showtopic={$r['type_id_2']}&amp;view=" . (($this->request['do']=='new_posts' OR $this->request['do']=='active') ? ("getnewpost") : ("" . (($r['misc']) ? ("findpost&amp;p={$r['misc']}") : ("")) . "")) . "&amp;hl={$this->request['search_higlight']}&amp;fromsearch=1", 'public','' ), "{$r['title_seo']}", "showtopic" ) . "' title='{$this->lang->words['view_result']}'>{$r['content_title']}</a></h3>
	" . ((!$resultAsTitle) ? ("
	<p>
		{$r['content']}
	</p>
	") : ("")) . "
	" . (($r['_forum_trail']) ? ("
		<span class='desc breadcrumb'>
			".$this->__f__c6518d59ff1587f2af1e3a6e4f72d130($r,$indent,$resultAsTitle)."		</span>
	") : ("")) . "
</div>
<div class='result_details desc'>
	<ul>
		<li>{$r['posts']} {$this->lang->words['forum_replies']}</li>
		<li>
			" . (($resultAsTitle) ? ("
				" . $this->registry->getClass('class_localization')->getDate($r['last_post'],"short", 0) . " {$this->lang->words['search_by']} <a href='" . $this->registry->getClass('output')->formatUrl( $this->registry->getClass('output')->buildUrl( "showuser={$r['last_poster_id']}", 'public','' ), "{$r['seo_last_name']}", "showuser" ) . "' title='{$this->lang->words['view_profile']}'>{$r['last_poster_name']}</a>" . $this->registry->getClass('output')->getTemplate('global')->user_popup($r['last_poster_id'], $r['seo_last_name']) . "
			") : ("
				" . $this->registry->getClass('class_localization')->getDate($r['updated'],"short", 0) . " {$this->lang->words['search_by']} <a href='" . $this->registry->getClass('output')->formatUrl( $this->registry->getClass('output')->buildUrl( "showuser={$r['member_id']}", 'public','' ), "{$r['members_seo_name']}", "showuser" ) . "' title='{$this->lang->words['view_profile']}'>{$r['members_display_name']}</a>" . $this->registry->getClass('output')->getTemplate('global')->user_popup($r['member_id'], $r['members_seo_name']) . "
			")) . "
		</li>
		" . (($r['topic_hasattach']) ? ("
			<li><a href=\"" . $this->registry->getClass('output')->formatUrl( $this->registry->getClass('output')->buildUrl( "app=forums&amp;module=forums&amp;section=attach&amp;tid={$r['type_id_2']}", 'public','' ), "", "" ) . "\">{$r['topic_hasattach']} {$this->lang->words['attach_header']}</a></li>
		") : ("")) . "
	</ul>
</div>";
return $IPBHTML;
}


function __f__c6518d59ff1587f2af1e3a6e4f72d130($r, $indent, $resultAsTitle=false)
{
	$_ips___x_retval = '';
	foreach( $r['_forum_trail'] as $i => $f )
	{
		
		$_ips___x_retval .= "
			<a href='" . $this->registry->getClass('output')->formatUrl( $this->registry->getClass('output')->buildUrl( "{$f[1]}", 'public','' ), "{$f[2]}", "showforum" ) . "'>{$f[0]}</a> " . (($i+1 != count( $r['_forum_trail'] )) ? ("&gt;") : ("")) . "
			
";
	}
	$_ips___x_retval .= '';
	return $_ips___x_retval;
}

/* -- topicPostSubresultWrap --*/
function topicPostSubresultWrap($content) {
$IPBHTML = "";
$IPBHTML .= "<ol>
		{$content}
	</ol>";
return $IPBHTML;
}

/* -- userPostsView --*/
function userPostsView($results, $pagination, $total, $member) {
$IPBHTML = "";

$this->registry->templateStriping['searchStripe'] = array( FALSE, "row1","row2");
$IPBHTML .= "<h2>{$this->lang->words['user_posts_title_all']} " . (($this->request['view_by_title'] && $this->request['search_app'] == 'forums') ? ("{$this->lang->words['user_posts_title_topics']}") : ("{$this->lang->words['user_posts_title_posts']}")) . " {$this->lang->words['user_posts_title_member']} {$member['members_display_name']}</h2>
" . (($total) ? ("<p class='message'>
		{$this->lang->words['user_posts_have_been']} <em><strong>{$total}</strong></em> " . (($this->request['view_by_title'] && $this->request['search_app'] == 'forums') ? ("{$this->lang->words['user_posts_title_topics']}") : ("{$this->lang->words['user_posts_title_posts']}")) . " {$this->lang->words['user_posts_title_member']} {$member['members_display_name']}
	</p>
	<br />") : ("")) . "
<div class='topic_controls'>
	{$pagination}
</div>
<ol id='member_alpha' class='tab_bar no_title'>
	" . ((! in_array( $this->settings['search_method'], array( 'traditional', 'sphinx' ) )) ? ("" . ((isset( $this->request['search_filter_app'] ) && $this->request['search_filter_app']['all'] != 1) ? ("
			<li><a href='" . $this->registry->getClass('output')->formatUrl( $this->registry->getClass('output')->buildUrl( "app=core&amp;module=search&amp;do=user_posts&amp;mid={$this->request['mid']}&amp;search_filter_app[all]=1&amp;view_by_title={$this->request['view_by_title']}", 'public','' ), "", "" ) . "'>{$this->lang->words['user_posts_all']}</a></li>
		") : ("
			<li class='active'><strong>{$this->lang->words['user_posts_all']}</strong></li>
		")) . "") : ("")) . "
	".$this->__f__bac035d466915cbd67ea99f73941d1ee($results,$pagination,$total,$member)."</ol>
" . (($total) ? ("
		<div id='search_results'>
		<ol>
			".$this->__f__798b09e70c9c8db281a866e0b6b5dadc($results,$pagination,$total,$member)."		</ol>
	</div>
") : ("
	<p class='no_messages'>{$this->lang->words['user_posts_none']}</p>
")) . "
<div class='topic_controls'>
	{$pagination}
</div>";
return $IPBHTML;
}


function __f__bac035d466915cbd67ea99f73941d1ee($results, $pagination, $total, $member)
{
	$_ips___x_retval = '';
	foreach( $this->registry->getApplications() as $app )
	{
		
		$_ips___x_retval .= "
		" . ((IPSSearchIndex::appIsSearchable( $app['app_directory'] ) && ! in_array( $app['app_directory'], array( 'core', 'members' ) )) ? ("" . (($this->request['search_app'] == $app['app_directory']) ? ("
				<li class='active'><strong>{$app['app_public_title']}</strong></li>
			") : ("				
				<li><a href='" . $this->registry->getClass('output')->formatUrl( $this->registry->getClass('output')->buildUrl( "app=core&amp;module=search&amp;do=user_posts&amp;mid={$this->request['mid']}&amp;search_filter_app[{$app['app_directory']}]=1&amp;view_by_title={$this->request['view_by_title']}", 'public','' ), "", "" ) . "'>{$app['app_public_title']}</a></li>
			")) . "") : ("")) . "
	
";
	}
	$_ips___x_retval .= '';
	return $_ips___x_retval;
}

function __f__798b09e70c9c8db281a866e0b6b5dadc($results, $pagination, $total, $member)
{
	$_ips___x_retval = '';
	foreach( $results as $result )
	{
		
		$_ips___x_retval .= "	
				" . (($result['sub']) ? ("
					<li class='" .  IPSLib::next( $this->registry->templateStriping["searchStripe"] ) . " sub clear clearfix'>
						{$result['html']}
					</li>
				") : ("
					<li class='" .  IPSLib::next( $this->registry->templateStriping["searchStripe"] ) . " clear clearfix'>
						{$result['html']}
					</li>
				")) . "
			
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