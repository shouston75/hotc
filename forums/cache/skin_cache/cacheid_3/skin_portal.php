<?php
/*--------------------------------------------------*/
/* FILE GENERATED BY INVISION POWER BOARD 3         */
/* CACHE FILE: Skin set id: 3               */
/* CACHE FILE: Generated: Sat, 22 Dec 2012 18:11:00 GMT */
/* DO NOT EDIT DIRECTLY - THE CHANGES WILL NOT BE   */
/* WRITTEN TO THE DATABASE AUTOMATICALLY            */
/*--------------------------------------------------*/

class skin_portal_3 extends skinMaster{

/**
* Construct
*/
function __construct( ipsRegistry $registry )
{
	parent::__construct( $registry );
	

$this->_funcHooks = array();
$this->_funcHooks['articles'] = array('entryLastPoster','entryHasPosts','articles');
$this->_funcHooks['latestPosts'] = array('topics_hook');
$this->_funcHooks['siteNavigation'] = array('links');
$this->_funcHooks['skeletonTemplate'] = array('disablelightbox');


}

/* -- affiliates --*/
function affiliates($links="") {
$IPBHTML = "";
$IPBHTML .= "<div class='ipsSideBlock'>
    <h3>{$this->lang->words['aff_title']}</h3>
    {$this->settings['portal_fav']}
</div>";
return $IPBHTML;
}

/* -- articles --*/
function articles($articles) {
$IPBHTML = "";
if( IPSLib::locationHasHooks( 'skin_portal', $this->_funcHooks['articles'] ) )
{
$count_939f7c2512e472371425501f4fd3a624 = is_array($this->functionData['articles']) ? count($this->functionData['articles']) : 0;
$this->functionData['articles'][$count_939f7c2512e472371425501f4fd3a624]['articles'] = $articles;
}
$IPBHTML .= "" . $this->registry->getClass('output')->addJSModule("topic", "0" ) . "
<div class='ipsBox clear'>

    ".$this->__f__ffd4c6c299c8b402af1e8c3fb0e3600b($articles)."</div>";
return $IPBHTML;
}


function __f__ffd4c6c299c8b402af1e8c3fb0e3600b($articles)
{
	$_ips___x_retval = '';
	$__iteratorCount = 0;
	foreach( $articles as $topic )
	{
		
		$__iteratorCount++;
		$_ips___x_retval .= "

        <div class='ipsBox_container ipsPad'>        
		
            <a href='" . $this->registry->getClass('output')->formatUrl( $this->registry->getClass('output')->buildUrl( "showuser={$topic['member_id']}", "public",'' ), "{$topic['members_seo_name']}", "showuser" ) . "' class='ipsUserPhotoLink left ipsPad_half'><img src='{$topic['pp_small_photo']}' alt='{$r['members_display_name']} {$this->lang->words['photo']}' class='ipsUserPhoto ipsUserPhoto_medium' /></a>
		
            <h2 class='ipsType_pagetitle'><a href='" . $this->registry->getClass('output')->formatUrl( $this->registry->getClass('output')->buildUrl( "showtopic={$topic['tid']}", "public",'' ), "{$topic['title_seo']}", "showtopic" ) . "'>{$topic['title']}</a></h2>
            <div class='desc'>" . $this->registry->getClass('class_localization')->getDate($topic['start_date'],"DATE", 0) . "</div><br class='clear' />

        
            <div class='desc ipsType_smaller ipsPad_half'>		
                {$this->lang->words['posted_by']} " . (($topic['members_display_name']) ? ("" . ( method_exists( $this->registry->getClass('output')->getTemplate('global'), 'userHoverCard' ) ? $this->registry->getClass('output')->getTemplate('global')->userHoverCard($topic) : '' ) . "") : ("{$this->settings['guest_name_pre']}{$topic['starter_name']}{$this->settings['guest_name_suf']}")) . "
                {$this->lang->words['in']} <a href='" . $this->registry->getClass('output')->formatUrl( $this->registry->getClass('output')->buildUrl( "showforum={$topic['id']}", "public",'' ), "{$topic['name_seo']}", "showforum" ) . "'>{$topic['name']}</a>
            </div>

            <div class='ipsType_textblock ipsPad'>
                {$topic['post']}
                <!--IBF.ATTACHMENT_{$topic['pid']}-->
            </div>
            
        </div>

        <div class='general_box'>
            <h3 class='ipsType_smaller'>
            
                <span class='right'>{$topic['share_links']}</span>

                " . $this->registry->getClass('class_localization')->formatNumber( $topic['views'] ) . " {$this->lang->words['views']} &middot;
                " . $this->registry->getClass('class_localization')->formatNumber( $topic['posts'] ) . " {$this->lang->words['replies']}
	
                " . (($topic['posts']) ? ("( {$this->lang->words['last_reply_by']} " . (($topic['last_poster_id']) ? ("<a href='" . $this->registry->getClass('output')->formatUrl( $this->registry->getClass('output')->buildUrl( "showuser={$topic['last_poster_id']}", "public",'' ), "{$topic['seo_last_name']}", "showuser" ) . "'>{$topic['last_poster_name']}</a>") : ("{$this->settings['guest_name_pre']}{$topic['last_poster_name']}{$this->settings['guest_name_suf']}")) . " )") : ("")) . "
                	
            </h3>
                        
	</div>


";
	}
	$_ips___x_retval .= '';
	unset( $__iteratorCount );
	return $_ips___x_retval;
}

/* -- latestPosts --*/
function latestPosts($topics=array()) {
$IPBHTML = "";
if( IPSLib::locationHasHooks( 'skin_portal', $this->_funcHooks['latestPosts'] ) )
{
$count_b4d23ee126b95bfbfe3ae559c5bb55b5 = is_array($this->functionData['latestPosts']) ? count($this->functionData['latestPosts']) : 0;
$this->functionData['latestPosts'][$count_b4d23ee126b95bfbfe3ae559c5bb55b5]['topics'] = $topics;
}
$IPBHTML .= "<div class='ipsSideBlock'>
     <h3>{$this->lang->words['discuss_title']}</h3>
		<ul class='ipsList_withminiphoto'>
		".$this->__f__5369377f04b74ea79bec991c1eef10d0($topics)."		</ul>
</div>";
return $IPBHTML;
}


function __f__5369377f04b74ea79bec991c1eef10d0($topics=array())
{
	$_ips___x_retval = '';
	$__iteratorCount = 0;
	foreach( $topics as $r )
	{
		
		$__iteratorCount++;
		$_ips___x_retval .= "
		<li class='clearfix'>
			<a href='" . $this->registry->getClass('output')->formatUrl( $this->registry->getClass('output')->buildUrl( "showuser={$r['member_id']}", "public",'' ), "{$r['members_seo_name']}", "showuser" ) . "' title='{$this->lang->words['view_profile']}' class='ipsUserPhotoLink left'><img src='{$r['pp_mini_photo']}' alt=\"{$r['members_display_name']}{$this->lang->words['photo']}\" class='ipsUserPhoto ipsUserPhoto_mini' /></a>
			<div class='list_content'>
				<a href='" . $this->registry->getClass('output')->formatUrl( $this->registry->getClass('output')->buildUrl( "showtopic={$r['tid']}", "public",'' ), "{$r['title_seo']}", "showtopic" ) . "' rel='bookmark' class='ipsType_small' title='{$this->lang->words['view_topic']}'>{$r['topic_title']}</a>
				<p class='desc ipsType_smaller'>
					" . (($r['members_display_name']) ? ("" . ( method_exists( $this->registry->getClass('output')->getTemplate('global'), 'userHoverCard' ) ? $this->registry->getClass('output')->getTemplate('global')->userHoverCard($r) : '' ) . "") : ("{$this->settings['guest_name_pre']}{$r['starter_name']}{$this->settings['guest_name_suf']}")) . "
					- " . $this->registry->getClass('class_localization')->getDate($r['start_date'],"short", 0) . "
				</p>
			</div>
		</li>
		
";
	}
	$_ips___x_retval .= '';
	unset( $__iteratorCount );
	return $_ips___x_retval;
}

/* -- onlineUsers --*/
function onlineUsers($active) {
$IPBHTML = "";
$IPBHTML .= "<div class='ipsSideBlock'>
    <h3><a href=\"" . $this->registry->getClass('output')->formatUrl( $this->registry->getClass('output')->buildUrl( "app=members&amp;module=online&amp;section=online", "public",'' ), "", "" ) . "\">{$this->lang->words['online_title']}</a></h3>
		<span class='desc'>" . sprintf( $this->lang->words['online_split'], intval($active['MEMBERS']), intval($active['visitors']), intval($active['ANON']) ) . "</span>
		<br /><br />
		<p>
			<span class='name'>" . implode( ",</span> <span class='name'>", $active['NAMES'] ) . "</span>					
		</p>
</div>";
return $IPBHTML;
}

/* -- pollWrapper --*/
function pollWrapper($content='',$topic=array()) {
$IPBHTML = "";
$IPBHTML .= "" . $this->registry->getClass('output')->addJSModule("topic", "0" ) . "
<div class='ipsSideBlock'>
    <h3><a href='" . $this->registry->getClass('output')->formatUrl( $this->registry->getClass('output')->buildUrl( "showtopic={$topic['tid']}", "public",'' ), "{$topic['title_seo']}", "showtopic" ) . "'>{$this->lang->words['poll_title']}</a></h3>
<div class='ipsPad'>
    {$content['html']}</div>
</div>";
return $IPBHTML;
}

/* -- siteNavigation --*/
function siteNavigation($links=array()) {
$IPBHTML = "";
if( IPSLib::locationHasHooks( 'skin_portal', $this->_funcHooks['siteNavigation'] ) )
{
$count_4186317cc60e19de728eb724b1a286ab = is_array($this->functionData['siteNavigation']) ? count($this->functionData['siteNavigation']) : 0;
$this->functionData['siteNavigation'][$count_4186317cc60e19de728eb724b1a286ab]['links'] = $links;
}
$IPBHTML .= "<div class='ipsSideBlock'>
        <h3>{$this->lang->words['links_title']}</h3>
	<ul>
		".$this->__f__f207122e9ffe87dd702f88bea41dc904($links)."	</ul>
</div>";
return $IPBHTML;
}


function __f__f207122e9ffe87dd702f88bea41dc904($links=array())
{
	$_ips___x_retval = '';
	$__iteratorCount = 0;
	foreach( $links as $link )
	{
		
		$__iteratorCount++;
		$_ips___x_retval .= "
			<li>&bull; <a href=\"{$link[1]}\">{$link[2]}</a></li>		
		
";
	}
	$_ips___x_retval .= '';
	unset( $__iteratorCount );
	return $_ips___x_retval;
}

/* -- skeletonTemplate --*/
function skeletonTemplate() {
$IPBHTML = "";
if( IPSLib::locationHasHooks( 'skin_portal', $this->_funcHooks['skeletonTemplate'] ) )
{
$count_34998ee52312a7898306092fc59345b8 = is_array($this->functionData['skeletonTemplate']) ? count($this->functionData['skeletonTemplate']) : 0;
}
$IPBHTML .= "" . ((!$this->settings['disable_lightbox']) ? ("
" . ( method_exists( $this->registry->getClass('output')->getTemplate('global'), 'include_lightbox' ) ? $this->registry->getClass('output')->getTemplate('global')->include_lightbox() : '' ) . "
") : ("")) . "

<div class='ipsLayout ipsLayout_withright ipsLayout_largeright clearfix'>
      <div class='ipsLayout_content clearfix'>
    	   <!--::latest_topics_main::-->
      </div>
      <div class='ipsLayout_right'>
	   <!--::portal_sitenav::-->
	   <!--::portal_show_poll::-->
	   <!--::latest_topics_sidebar::-->
	   <!--::portal_affiliates::-->
	   <!--::online_users_show::-->
      </div>
</div>

" . ( method_exists( $this->registry->getClass('output')->getTemplate('global'), 'include_highlighter' ) ? $this->registry->getClass('output')->getTemplate('global')->include_highlighter(1) : '' ) . "";
return $IPBHTML;
}


}


/*--------------------------------------------------*/
/* END OF FILE                                      */
/*--------------------------------------------------*/

?>