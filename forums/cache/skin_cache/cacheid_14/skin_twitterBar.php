<?php
/*--------------------------------------------------*/
/* FILE GENERATED BY INVISION POWER BOARD 3         */
/* CACHE FILE: Skin set id: 14               */
/* CACHE FILE: Generated: Sun, 09 Dec 2012 17:06:50 GMT */
/* DO NOT EDIT DIRECTLY - THE CHANGES WILL NOT BE   */
/* WRITTEN TO THE DATABASE AUTOMATICALLY            */
/*--------------------------------------------------*/

class skin_twitterBar_14 extends skinMaster{

/**
* Construct
*/
function __construct( ipsRegistry $registry )
{
	parent::__construct( $registry );
	

$this->_funcHooks = array();


}

/* -- defaultContent --*/
function defaultContent($data=array()) {
$IPBHTML = "";
$IPBHTML .= "" . ((is_array( $data['myStatus'] ) && count( $data['myStatus'] ) && !isset( $data['myStatus']['error'] )) ? ("<li class='desc'>{$this->lang->words['myStatus']}</li>
	<li class='row2' id=\"myStatus\">
		<span class='status-body-nomargin'>
			<span class=\"statusText\">{$data['myStatus']['text']}</span>
			<span class=\"meta\">
				<a id=\"reply_{$data['myStatus']['id']}\" class=\"replyTo\" href='http://twitter.com/{$data['myStatus']['user']['screen_name']}/statuses/{$data['myStatus']['id']}'>{$data['myStatus']['time']}</a>
				<span>
					{$this->lang->words['fromSource']} {$data['myStatus']['source']}
					" . (($data['myStatus']['in_reply_to_status_id'] AND $data['myStatus']['in_reply_to_screen_name']) ? ("
						<a id=\"reply_{$data['myStatus']['in_reply_to_status_id']}-{$data['myStatus']['id']}\" class=\"replyTo\" href='http://twitter.com/{$data['myStatus']['in_reply_to_screen_name']}/statuses/{$data['myStatus']['in_reply_to_status_id']}'>" . sprintf( $this->lang->words['inReplyTo'], $data['myStatus']['in_reply_to_screen_name'] ) . "</a>
					") : ("")) . "
				</span>
			</span>
		</span>
	</li>") : ("")) . "
" . (($data['friends']) ? ("
	<li class='desc'>{$this->lang->words['timeline']}</li>
	{$data['friends']}
") : ("")) . "";
return $IPBHTML;
}

/* -- friendsPopup --*/
function friendsPopup($data, $page) {
$IPBHTML = "";
$IPBHTML .= "<h3 id=\"friendsTitle\">&nbsp;</h3>
<table class='ipb_table'>
	<tr class='header'>
		<th>&nbsp;</th>
	</tr>
</table>
<div class='row1 twitterPop user-info' id=\"friendsWrapper\" style=\"height: 500px; overflow: auto;\">
	<ul>
		<div id=\"friendsList\">
			<div id='friendsResult_{$page}'>
				".$this->__f__9570dd013a38ba4bcb7af0a745ff9013($data,$page)."			</div>
		</div>
		<li class=\"loadMore\" id=\"friendsLoadMore\" style=\"display:none;\"><span id=\"friendsLoadMoreLabel\">{$this->lang->words['loadMore']}</span><span id=\"friendsLoadMoreLoading\" class=\"loadingMore\" style=\"display:none;\">{$this->lang->words['loadMoreLoading']}</span></li>
	</ul>
</div>";
return $IPBHTML;
}


function __f__9570dd013a38ba4bcb7af0a745ff9013($data, $page)
{
	$_ips___x_retval = '';
	$__iteratorCount = 0;
	foreach( $data as $u )
	{
		
		$__iteratorCount++;
		$_ips___x_retval .= "
					<li>
						<div class=\"thumb\">
							<a id='showuser_{$u['screen_name']}' class='findUserTweets' href='http://twitter.com/{$u['screen_name']}'><img src='{$u['profile_image_url']}' alt='{$u['screen_name']}' height=\"48\" width=\"48\" /></a> 
						</div>
						<div class=\"screen-name\">
							<a id='showuser_{$u['screen_name']}' class='findUserTweets' href='http://twitter.com/{$u['screen_name']}'>{$u['screen_name']}</a>
						</div>
						<div class=\"full-name\">
							{$u['name']}
						</div>
					</li>
				
";
	}
	$_ips___x_retval .= '';
	unset( $__iteratorCount );
	return $_ips___x_retval;
}

/* -- getCSS --*/
function getCSS() {
$IPBHTML = "";
$IPBHTML .= "<style type=\"text/css\">
" . (($this->memberData['oauth_state'] == 'returned' AND twitterSideBar::instance()->twitterTroubles == 0) ? ("
	div#categories, 
	#ipboard_body div#categories.with_sidebar 
	{
		width: 68%;
	}
	
	#index_stats
	{
		width: 30%;
	}
") : ("")) . "
	.twitterPop
	{
		padding:5px 10px 15px;
	}
		.twitterPop .status-body
		{
			display:block;
			font-size:1.2em;
			margin-right:30px;
			padding-bottom:15px;
		}
		
		.twitterPop .entry-content
		{
			display:block;
			font-family:georgia;
			line-height:1.25em;
			overflow:hidden;
			padding:0;
		}
		
		.twitterPop .user-info
		{
			border-top:1px solid #E6E6E6;
			height:73px;
			line-height:1;
			margin-top:0;
			padding-top:15px;
		}
		
		.twitterPop .thumb 
		{
			float:left;
			margin-right:20px;
		}
		
		.twitterPop .screen-name
		{
			font-size:2.3em;
		}
		
		.twitterPop .full-name
		{
			font-size: 1.2em;
			margin: 3px 0 0 2px;
		}	
		
	#twitterTabs .tab_bar
	{
		padding-left: 5px;
		padding-right: 5px;
	}
	
		#twitterTabs .tab_bar li
		{
			padding-left: 5px;
			padding-right: 5px;
		}
		
		#twitterTabs .tab_bar li.active
		{
			margin: -5px 0 0;
			
		}
		
	ol.statuses 
	{
		list-style-image: none;
		list-style-position: outside;
		list-style-type: none;
	}	
		ol.statuses li 
		{
			border-bottom: 2px dashed #D2DADA;
			line-height: 1.1em;
			padding: 0.7em 0 0.6em;
			position: relative;
		}
		
		ol.statuses .thumb 
		{
			display: block;
			height: 45px;
			margin: 0 10px 0 5px;
			overflow: hidden;
			position: absolute;
			width: 45px;
		}
		
		ol.statuses span.status-body 
		{
			display: block;
			margin-left: 55px;
			min-height: 50px;
			overflow: hidden;
		}
		
		ol.statuses span.status-body-nomargin
		{
			display: block;
			margin-left: 10px;
			min-height: 30px;
			overflow: hidden;
		}
		
		ol.statuses span.meta,
		.twitterPop span.meta
		{
			color: #999999;
			display: block;
			font-family: georgia;
			font-size: 0.8em;
			font-style: italic;
			margin: 3px 0 0;
		}
		
		ol.statuses #myStatus
		{
			border-bottom: 3px solid #243F5C;
			margin-bottom: 3px;
		}
		
		ol.statuses .screen-name 
		{
			margin-right: 5px;
		}
		
		ol.statuses .actions,
		.twitterPop .actions
		{
			border-width: 0;
			line-height: 1.25em;
			position: absolute;
			/*right: 5px;
			top: 0.5em;*/
			margin-left: 5px;
		}
		ol.statuses .actions .non-fav, 
		.twitterPop .actions .non-fav
		{
			background-image: url(http://static.twitter.com/images/icon_star_empty.gif);
		}
		
		ol.statuses .actions a.fav,
		.twitterPop .actions a.fav
		{
			visibility:visible;
		}
		
		ol.statuses .actions .fav,
		.twitterPop .actions .fav
		{
			background-image:url(http://static.twitter.com/images/icon_star_full.gif);
		}
		
		ol.statuses .actions .reply,
		.twitterPop .actions .reply
		{
			background-image: url(http://static.twitter.com/images/icon_reply.gif);
		}
		
		ol.statuses .actions a,
		.twitterPop .actions a			
		{
			background-position: 50% 50%;
			background-repeat: no-repeat;
			cursor: pointer;
			outline-color: -moz-use-text-color;
			outline-style: none;
			outline-width: medium;
			padding: 3px 8px;
			text-decoration: none;
			visibility: hidden;
			/*display: block;*/
		}
		
		ol.statuses li:hover .actions a,
		ol.statuses li.hover .actions a,
		.twitterPop .status-body:hover .actions a,			
		.twitterPop .status-body .hover .actions a			
		{
			visibility:visible;
		}
		
	#twitterSearch
	{
		margin-top: 5px;
	}
		#twitterSearch input 
		{
			border-color: #B4B4B4 #CCCCCC #CCCCCC #B4B4B4;
			border-style: solid none solid solid;
			border-width: 1px 0 1px 1px;
			font-size:1em;
			margin:0.25em -4px 0.25em 0.75em;
			outline-color: -moz-use-text-color;
			outline-style: none;
			outline-width: medium;
			padding: 0.4em;
			padding-left: 6px;
			width: 150px;
		}
		
		#twitterSearch input, 
		#twitterSearch_submit 
		{
			border-color: #999999 !important;
			padding-bottom: 5px !important;
			padding-top: 5px !important;
			vertical-align: middle;
		}
		
		#twitterSearch_submit
		{
			border-style: solid;
			border-width: 1px;
			cursor: pointer;
			padding: .4em .9em;
		}
		
			#twitterSearch_submit.submit
			{
				background-color: #EEE;
				background-position: center top;
				background: url(http://twitter.com/images/nav_search_submit.png) -2px 0;
			}
		
			#twitterSearch_submit.submit:hover
			{
				background: url(http://twitter.com/images/nav_search_submit.png) -2px -25px;
			}
			
			#twitterSearch_submit.submit:active
			{
				background: url(http://twitter.com/images/nav_search_submit.png) -2px -50px;
			}
			
			#twitterSearch_submit.loading, 
			#twitterSearch_submit.loading:hover, 
			#twitterSearch_submit.loading:active 
			{
				background: #EEE url({$this->settings['img_url']}/loading.gif) no-repeat center !important;
			}
	.round-left
	{
		-moz-border-radius-topleft: 5px;
		-moz-border-radius-bottomleft: 5px;
		-webkit-border-top-left-radius: 5px;
		-webkit-border-bottom-left-radius: 5px;
	}
	
	.round-right
	{
		-moz-border-radius-topright: 5px;
		-moz-border-radius-bottomright: 5px;
		-webkit-border-top-right-radius: 5px;
		-webkit-border-bottom-right-radius: 5px;
	}
	
	fieldset.common-form 
	{
		margin: 10px 0;
	}
	
	fieldset.common-form  div.whatchadoin 
	{
		line-height: 1.9em;
		padding: 0 10px;
		position: relative;
	}
	
		fieldset.common-form  div.whatchadoin p 
		{
			color: #000000;
			font-weight: normal;
			letter-spacing: -1px;
			padding-right: 170px;
		}
		
		fieldset.common-form  div.whatchadoin span 
		{
			display: block;
			font-size: 0.92em;
			position: absolute;
			right: 10px;
			top: 0;
		}
	
	p.doing
	{
		margin-bottom:	10px;
	}
	
		p.doing label
		{
			font-size: 1.2em;
			line-height: 1.1;
			width: 50%;
	}
	
	
	#chars_left_notice 
	{
		color: #CCCCCC;
		font-size: 22pt !important;
	}
	
	.numeric 
	{
		font-family: 'Georgia', 'Serif';
	}
	
	#twitterUpdate .info 
	{
		padding-top: 3px;
		text-align: left;
	}
		#twitterUpdate .input_submit
		{
			margin: 5px 10px 0 5px;
		}
	
	#userImage
	{
		float: left;
		margin: 6px 10px 6px 6px;
	}
		#userImage .photo
		{
			border: 1px solid #1D3652;
		}
	
	.userInfo 
	{
		margin-left: 90px;
		margin-right: 15px;
	}
	
		.userInfo dl 
		{ 
			color: #444; 
			margin-bottom: 10px; 
		}
		
		.userInfo dt, dd 
		{ 
			padding: 3px 0; 
			border-top: 1px solid #e1e1e1; 
		}
		
		.userInfo dt 
		{ 
			float: left; 
			font-weight: bold; 
		}
		
		.userInfo dd 
		{ 
			text-align: right; 
		}
		
	#userInfoBlock p 
	{
		margin-top: 6px;
		white-space: nowrap;
	}
	
	.followButton
	{
		background: #009d25 url({$this->settings['img_url']}/user_add.png) no-repeat left;
		padding: 5px 20px 5px 10px;
		font-size: 12px;
		color: #DADADA;
	}
	
	.unFollowButton
	{
		background: #EF3C39 url({$this->settings['img_url']}/user_add.png) no-repeat left;
		padding: 5px 20px 5px 10px;
		font-size: 12px;
		color: #DADADA;
	}
	
	.twitterCopyright
	{
		float: right;
		padding-top: 8px;
		font-size: 0.8em;
		color: #606060;
	}
	
	.general_box
	{
		padding-bottom: 4px;
	}
	
	.loadMore
	{
		border-bottom: 1px solid #979797 !important;
		border: 1px solid #979797;
		padding: 10px !important;
		background-color: #e1e1e1;
		-moz-border-radius: 6px;
		-webkit-border-radius: 6px;
		width: 75%;;
		text-align: center;
		font-size: 14px;
		font-weight: bold;
		margin: 10px auto;
	}
	
		.loadMore:hover
		{
			background-color: #f6f6f6;
		}
	
	.loadingMore
	{
		background: #EEE url({$this->settings['img_url']}/loading.gif) no-repeat left !important;
		padding-left: 20px;
	}
</style>
" . (($css = twitterSidebar::$css) ? ("
	{$css}
") : ("")) . "";
return $IPBHTML;
}

/* -- mainWrapper --*/
function mainWrapper($rateLimits) {
$IPBHTML = "";
$IPBHTML .= "" . ( method_exists( $this->registry->getClass('output')->getTemplate('global'), 'include_lightbox_real' ) ? $this->registry->getClass('output')->getTemplate('global')->include_lightbox_real() : '' ) . "
" . (($this->memberData['oauth_state'] == 'returned' AND twitterSideBar::instance()->twitterTroubles == 0) ? ("
	<script src=\"{$this->settings['public_dir']}js/ips.twitter.js\" type=\"text/javascript\"></script>
	<script type=\"text/javascript\">
		ipb.twitter.lang['OAuthReset'] = \"{$this->lang->words['recreatedOAuthKeys']}\";
		ipb.twitter.lang['friendsTitle'] = \"{$this->lang->words['friendsTitle']}\";
		ipb.twitter.lang['followersTitle'] = \"{$this->lang->words['followersTitle']}\";
	</script>
") : ("")) . "
" . ( method_exists( $this->registry->getClass('output')->getTemplate('twitterBar'), 'getCSS' ) ? $this->registry->getClass('output')->getTemplate('twitterBar')->getCSS() : '' ) . "
<div class='general_box'>
	<h3>
		" . ((is_array( $rateLimits ) AND count( $rateLimits )) ? ("
			<span class=\"desc right\">
				{$this->lang->words['remainingApiCalls']} {$rateLimits['remaining_hits']}/{$rateLimits['hourly_limit']} {$this->lang->words['apiCallsReset']} " . $this->registry->getClass('class_localization')->getDate($rateLimits['reset_time_in_seconds'],"manual{%H:%M}", 1) . "
			</span>
		") : ("")) . "
			
		<img src='{$this->settings['img_url']}/comment_new.png' alt='{$this->lang->words['icon']}' /> {$this->lang->words['hookTitle']}</h3>
	<div>
		" . ((twitterSideBar::instance()->twitterTroubles == 0) ? ("" . (($this->memberData['oauth_state'] == 'returned') ? ("
				<div id=\"twitterTabs\">
					<ol class='tab_bar no_title mini'>
						<li id='twitterTab_link_friends' class='twitterTab_toggle active clickable'>{$this->lang->words['tabTimeline']}</li>
						<li id='twitterTab_link_mentions' class='twitterTab_toggle clickable'>{$this->lang->words['tabMentions']}</li>
						<li id='twitterTab_link_search' class='twitterTab_toggle clickable'>{$this->lang->words['tabSearch']}</li>
						<li id='twitterTab_link_user' class='twitterTab_toggle clickable' style='display:none;'>{$this->lang->words['tabUser']}</li>
						<li id='twitterTab_link_update' class='twitterTab_toggle clickable'>{$this->lang->words['tabUpdate']}</li>
					</ol>
					
					<div id='twitterTab_content_friends' class='twitterTab_toggle_content'>
						<ol class=\"statuses\">
							<div id=\"defaultContent\" style=\"display: none;\"></div>
						</ol>
					</div>
					
					<div id='twitterTab_content_update' class='twitterTab_toggle_content' style='display:none;'>
						<form id=\"twitterUpdate\" action=\"\" onsubmit=\"ipb.twitter.ajaxUpdateStatus(); return false;\">
							<input id=\"in_reply_to\" name=\"in_reply_to\" value=\"\" type=\"hidden\" />
							<fieldset class=\"common-form\">
								<div class=\"row2 whatchadoin\">
									<p class=\"doing\"><label for=\"status\">{$this->lang->words['whatchaDoin']}</label>
									<span id=\"chars_left_notice\" class=\"numeric\">
										<strong id=\"status-field-char-counter\" class=\"char-counter\">140</strong>
									</span></p>
								</div>
								<div class=\"info row2\">
									<ul>
										<li><textarea cols=\"40\" rows=\"3\" id=\"status\" name=\"status\" accesskey=\"u\"></textarea></li>
										<li>{$this->lang->words['shortenUrl']}: <input type=\"text\" id=\"shortenURL\" name=\"shortenURL\" value=\"\" /> <span class=\"input_submit\" id=\"shorten\">{$this->lang->words['shorten']}</span</li>
										<li><input type=\"submit\" name=\"update\" value=\"{$this->lang->words['update']}\" class=\"input_submit\" /></li>
									</ul>
								</div>
							</fieldset>
						</form>
					</div>
					<div id='twitterTab_content_mentions' class='twitterTab_toggle_content' style='display:none;'>
						<ol class=\"statuses\">
							<div id=\"mentions\" style=\"display:none;\"></div>
						</ol>
					</div>
					<div id='twitterTab_content_search' class='twitterTab_toggle_content' style='display:none;'>
						<form id=\"twitterSearch\" action=\"\">
								<input type=\"text\" name=\"searchQuery\" id=\"searchQuery\" class=\"round-left\" value=\"\" />
								<span id=\"twitterSearch_submit\" class=\"submit round-right\" title=\"Search\">&nbsp;</span>
						</form>
						<ol class=\"statuses\">
							<li class='desc'>{$this->lang->words['searchResult']}</li>
							<div id=\"searchResults\"></div>
							<li class=\"loadMore\" id=\"searchLoadMore\" style=\"display:none;\"><span id=\"searchLoadMoreLabel\">{$this->lang->words['loadMore']}</span><span id=\"searchLoadMoreLoading\" class=\"loadingMore\" style=\"display:none;\">{$this->lang->words['loadMoreLoading']}</span></li>
						</ol>
					</div>
					
					<div id='twitterTab_content_user' class='twitterTab_toggle_content' style='display:none;'>
						<ol class=\"statuses\">
							<div id=\"userTweets\" style=\"display:none;\"></div>
						</ol>
					</div>
				</div>
			") : ("
				<ol class=\"hfeed\">
					<li class=\"row2\">{$this->lang->words['authorizeLink']}</li>
				</ol>
			")) . "") : ("
			<ol class=\"hfeed\">
				<li class=\"row2\">{$this->lang->words['errorTwitterTrouble']}</li>
			</ol>
		")) . "
	</div>
	<span class=\"twitterCopyright\">(IM) Twitter Sidebar &copy 2009, <a href=\"http://www.invisionmodding.com/\" target=\"_blank\">Invision Modding</a></span>
</div>";
return $IPBHTML;
}

/* -- moreFriends --*/
function moreFriends($data, $page) {
$IPBHTML = "";
$IPBHTML .= "<div id='friendsResult_{$page}' style=\"display:none;\">
	".$this->__f__4ba6c4149cf265c9492b2542f19d6467($data,$page)."</div>";
return $IPBHTML;
}


function __f__4ba6c4149cf265c9492b2542f19d6467($data, $page)
{
	$_ips___x_retval = '';
	$__iteratorCount = 0;
	foreach( $data as $u )
	{
		
		$__iteratorCount++;
		$_ips___x_retval .= "
		<li>
			<div class=\"thumb\">
				<a id='showuser_{$u['screen_name']}' class='findUserTweets' href='http://twitter.com/{$u['screen_name']}'><img src='{$u['profile_image_url']}' alt='{$u['screen_name']}' height=\"48\" width=\"48\" /></a> 
			</div>
			<div class=\"screen-name\">
				<a id='showuser_{$u['screen_name']}' class='findUserTweets' href='http://twitter.com/{$u['screen_name']}'>{$u['screen_name']}</a>
			</div>
			<div class=\"full-name\">
				{$u['name']}
			</div>
		</li>
	
";
	}
	$_ips___x_retval .= '';
	unset( $__iteratorCount );
	return $_ips___x_retval;
}

/* -- parseSearchResult --*/
function parseSearchResult($data=array(), $page) {
$IPBHTML = "";
$IPBHTML .= "" . ((is_array( $data ) && count( $data )) ? ("
	<div id='searchResult_{$page}' style=\"display:none;\">
		".$this->__f__e275db20baf4af38b40ec5f0ff6cd6f4($data,$page)."	</div>
") : ("
	<li class='row1'>{$this->lang->words['errorNoResults']}</li>
")) . "";
return $IPBHTML;
}


function __f__e275db20baf4af38b40ec5f0ff6cd6f4($data=array(), $page)
{
	$_ips___x_retval = '';
	$__iteratorCount = 0;
	foreach( $data as $r )
	{
		
		$__iteratorCount++;
		$_ips___x_retval .= "
			<li class='row1 twitterStatus' id=\"status_{$r['id']}\">
				<span class='thumb'>
					<a href='https://twitter.com/{$r['from_user']}'><img src='{$r['profile_image_url']}' alt='{$r['from_user']}' height=\"48\" width=\"48\" /></a> 
				</span>
				<span class='status-body'>
					<strong class='screen-name'><a title='{$r['author']['screen_name']}' href='http://twitter.com/{$r['from_user']}'>{$r['from_user']}</a>:</strong>
					<span class=\"statusText\">{$r['text']}</span>
					<span class=\"meta\">
						<a id=\"reply_{$r['id']}\" class=\"replyTo\" href='http://twitter.com/{$r['from_user']}/statuses/{$r['id']}'>{$r['time']}</a>
						<span>
							{$this->lang->words['fromSource']} {$r['source']}
							" . (($r['in_reply_to_status_id'] AND $r['in_reply_to_screen_name']) ? ("
								<a id=\"reply_{$r['in_reply_to_status_id']}\" class=\"replyTo\" href='http://twitter.com/{$r['in_reply_to_screen_name']}/statuses/{$r['in_reply_to_status_id']}'>" . sprintf( $this->lang->words['inReplyTo'], $r['in_reply_to_screen_name'] ) . "</a>
							") : ("")) . "
						</span>
						<span class=\"actions\" id=\"action_{$r['id']}\">
							<div>
								" . (($r['favorited']) ? ("
									<a title=\"{$this->lang->words['unFavorite']}\" id=\"status_star_{$r['id']}\" class=\"fav-action fav\">&nbsp;</a>
								") : ("
									<a title=\"{$this->lang->words['favorite']}\" id=\"status_star_{$r['id']}\" class=\"fav-action non-fav\">&nbsp;</a>
								")) . "
								<a title=\"{$this->lang->words['replyTo']}{$r['from_user']}\" id=\"reply_to_{$r['from_user']}_{$r['id']}\" class=\"reply\">&nbsp;</a>
							</div>
						</span>
					</span>
				</span>
			</li>
		
";
	}
	$_ips___x_retval .= '';
	unset( $__iteratorCount );
	return $_ips___x_retval;
}

/* -- parseTweets --*/
function parseTweets($data=array()) {
$IPBHTML = "";
$IPBHTML .= "" . ((is_array( $data ) && count( $data ) && !isset( $data['error'] )) ? ("".$this->__f__20cded16e4be2b700b16ac2bf8aa33fd($data)."") : ("<li class='row1'>
	" . ((isset( $data['error'] )) ? ("
		{$data['error']}
	") : ("
		{$this->lang->words['errorNoTweets']}
	")) . "
	</li>")) . "";
return $IPBHTML;
}


function __f__20cded16e4be2b700b16ac2bf8aa33fd($data=array())
{
	$_ips___x_retval = '';
	$__iteratorCount = 0;
	foreach( $data as $r )
	{
		
		$__iteratorCount++;
		$_ips___x_retval .= "
		<li class='row1 twitterStatus' id=\"status_{$r['id']}\">
			<span class='thumb'>
				<a id='showuser_{$r['user']['screen_name']}' class='findUserTweets' href='https://twitter.com/{$r['user']['screen_name']}'><img src='{$r['user']['profile_image_url']}' alt='{$r['user']['screen_name']}' height=\"48\" width=\"48\" /></a> 
			</span>
			<span class='status-body'>
				<strong class='screen-name'><a id='showuser_{$r['user']['screen_name']}' class='findUserTweets' title='{$r['user']['screen_name']}' href='http://twitter.com/{$r['user']['screen_name']}'>{$r['user']['screen_name']}</a>:</strong>
				<span class=\"statusText\">{$r['text']}</span>
				<span class=\"meta\">
					<a id=\"reply_{$r['id']}\" class=\"replyTo\" href='http://twitter.com/{$r['user']['screen_name']}/statuses/{$r['id']}'>{$r['time']}</a>
					<span>
						{$this->lang->words['fromSource']} {$r['source']}
						" . (($r['in_reply_to_status_id'] AND $r['in_reply_to_screen_name']) ? ("
							<a id=\"reply_{$r['in_reply_to_status_id']}-{$r['id']}\" class=\"replyTo\" href='http://twitter.com/{$r['in_reply_to_screen_name']}/statuses/{$r['in_reply_to_status_id']}'>" . sprintf( $this->lang->words['inReplyTo'], $r['in_reply_to_screen_name'] ) . "</a>
						") : ("")) . "
					</span>
					<span class=\"actions\" id=\"action_{$r['id']}\">
						<div>
							" . (($r['favorited']) ? ("
								<a title=\"{$this->lang->words['unFavorite']}\" id=\"status_star_{$r['id']}\" class=\"fav-action fav\">&nbsp;</a>
							") : ("
								<a title=\"{$this->lang->words['favorite']}\" id=\"status_star_{$r['id']}\" class=\"fav-action non-fav\">&nbsp;</a>
							")) . "
							<a title=\"{$this->lang->words['replyTo']}{$r['user']['screen_name']}\" id=\"reply_to_{$r['user']['screen_name']}_{$r['id']}\" class=\"reply\">&nbsp;</a>
						</div>
					</span>
				</span>
			</span>
		</li>
	
";
	}
	$_ips___x_retval .= '';
	unset( $__iteratorCount );
	return $_ips___x_retval;
}

/* -- parseUserTweets --*/
function parseUserTweets($data, $userData) {
$IPBHTML = "";
$IPBHTML .= "" . ((is_array( $data ) && count( $data ) && !isset( $data['error'] )) ? ("<div id=\"userInfoBlock\">
		<div id=\"userImage\">
			<a id='showuser_{$userData['screen_name']}' class='findUserTweets' href='https://twitter.com/{$userData['screen_name']}'><img class='photo' src='{$userData['profile_image_url']}' alt='{$userData['screen_name']}' height=\"73\" width=\"73\" /></a> 
			<!-- Twitter API issue #474
			" . ((! $userData['following']) ? ("
				<p class=\"followButton\">Follow</p>
			") : ("
				<p class=\"unFollowButton\">Stop following</p>
			")) . "-->
		</div>
		<div class=\"userInfo\" id='userid_{$userData['id']}'>
			<dl>
				<dt>{$this->lang->words['userTabName']}</dt>
					<dd>{$userData['name']}</dd>
				<dt>{$this->lang->words['userTabLocation']}</dt>
					<dd>{$userData['location']}</dd>
				<dt>{$this->lang->words['userTabDesc']}</dt>
					<dd>{$userData['description']}</dd>
				<dt>{$this->lang->words['userTabFollowing']}</dt>
					<dd><a id='friendsPopup' href='https://twitter.com/{$userData['screen_name']}/friends' target='_blank'>{$userData['friends_count']}</a></dd>
				<dt>{$this->lang->words['userTabFollowers']}</dt>
					<dd><a id='followersPopup' href='https://twitter.com/{$userData['screen_name']}/followers' target='_blank'>{$userData['followers_count']}</a></dd>
			</dl>
		</div>
	</div>
	<li class='desc'>{$this->lang->words['userTabTweets']}</li>
	".$this->__f__2cb90af0087864360d23c677481d29c4($data,$userData)."") : ("<li class='row1'>
	" . ((isset( $data['error'] )) ? ("
		{$data['error']}
	") : ("
		{$this->lang->words['errorNoTweets']}
	")) . "
	</li>")) . "";
return $IPBHTML;
}


function __f__2cb90af0087864360d23c677481d29c4($data, $userData)
{
	$_ips___x_retval = '';
	$__iteratorCount = 0;
	foreach( $data as $r )
	{
		
		$__iteratorCount++;
		$_ips___x_retval .= "
		<li class='row1 twitterStatus' id=\"status_{$r['id']}\">
			<span class='status-body-nomargin'>
				<span class=\"statusText\">{$r['text']}</span>
				<span class=\"meta\">
					<a id=\"reply_{$r['id']}\" class=\"replyTo\" href='http://twitter.com/{$r['user']['screen_name']}/statuses/{$r['id']}'>{$r['time']}</a>
					<span>
						{$this->lang->words['fromSource']} {$r['source']}
						" . (($r['in_reply_to_status_id'] AND $r['in_reply_to_screen_name']) ? ("
							<a id=\"reply_{$r['in_reply_to_status_id']}-{$r['id']}\" class=\"replyTo\" href='http://twitter.com/{$r['in_reply_to_screen_name']}/statuses/{$r['in_reply_to_status_id']}'>" . sprintf( $this->lang->words['inReplyTo'], $r['in_reply_to_screen_name'] ) . "</a>
						") : ("")) . "
					</span>
					<span class=\"actions\" id=\"action_{$r['id']}\">
						<div>
							" . (($r['favorited']) ? ("
								<a title=\"{$this->lang->words['unFavorite']}\" id=\"status_star_{$r['id']}\" class=\"fav-action fav\">&nbsp;</a>
							") : ("
								<a title=\"{$this->lang->words['favorite']}\" id=\"status_star_{$r['id']}\" class=\"fav-action non-fav\">&nbsp;</a>
							")) . "
							<a title=\"{$this->lang->words['replyTo']}{$r['user']['screen_name']}\" id=\"reply_to_{$r['user']['screen_name']}_{$r['id']}\" class=\"reply\">&nbsp;</a>
						</div>
					</span>
				</span>
			</span>
		</li>
	
";
	}
	$_ips___x_retval .= '';
	unset( $__iteratorCount );
	return $_ips___x_retval;
}

/* -- singleStatusPopUp --*/
function singleStatusPopUp($data=array()) {
$IPBHTML = "";
$IPBHTML .= "" . ((is_array( $data ) && count( $data )) ? ("<h3>" . sprintf( $this->lang->words['singleStatusTitle'], $data['user']['screen_name'] ) . "</h3>
	<table class='ipb_table'>
		<tr class='header'>
			<th>&nbsp;</th>
		</tr>
	</table>
	<div class='row2 twitterPop'>
		<span class='status-body twitterStatus' id=\"status_{$data['id']}\">
			<span class=\"entry-content statusText\">{$data['text']}</span>
			<span class=\"meta\">
				{$data['time']} 
				<span>
					{$this->lang->words['fromSource']} {$data['source']}
					" . (($data['in_reply_to_status_id'] AND $data['in_reply_to_screen_name']) ? ("
						<a id=\"reply_{$data['in_reply_to_status_id']}-{$r['id']}\" class=\"replyTo\" href='http://twitter.com/{$data['in_reply_to_screen_name']}/statuses/{$data['in_reply_to_status_id']}'>" . sprintf( $this->lang->words['inReplyTo'], $data['in_reply_to_screen_name'] ) . "</a>
					") : ("")) . "
				</span>
				<span class=\"actions\" id=\"action_{$data['id']}\">
					<div>
						" . (($data['favorited']) ? ("
							<a title=\"{$this->lang->words['unFavorite']}\" id=\"status_star_{$data['id']}\" class=\"fav-action fav\">&nbsp;</a>
						") : ("
							<a title=\"{$this->lang->words['favorite']}\" id=\"status_star_{$data['id']}\" class=\"fav-action non-fav\">&nbsp;</a>
						")) . "
						<a title=\"{$this->lang->words['replyTo']}{$data['user']['screen_name']}\" id=\"reply_to_{$data['user']['screen_name']}_{$data['id']}\" class=\"reply\">&nbsp;</a>
					</div>
				</span>
			</span>
		</span>
		<div class=\"user-info clear\">
			<div class=\"thumb\">
				<a id='showuser_{$data['user']['screen_name']}' class='findUserTweets' href='http://twitter.com/{$data['user']['screen_name']}'><img src='{$data['user']['profile_image_url']}' alt='{$data['user']['screen_name']}' height=\"73\" width=\"73\" /></a> 
			</div>
			<div class=\"screen-name\">
				<a id='showuser_{$data['user']['screen_name']}' class='findUserTweets' href='http://twitter.com/{$data['user']['screen_name']}'>{$data['user']['screen_name']}</a>
			</div>
			<div class=\"full-name\">
				{$data['user']['name']}
			</div>
		</div>
	</div>") : ("")) . "";
return $IPBHTML;
}

/* -- ucpSetting --*/
function ucpSetting() {
$IPBHTML = "";
$IPBHTML .= "<fieldset class='row2'>
	<h3>{$this->lang->words['ucpTwitter']}</h3>
	<ul>
		<li class='field checkbox'>
			<input type='hidden' name='twitter' value='1' />
			<input class='input_check' type=\"checkbox\" name=\"resetOAuth\" id=\"resetOAuth\" value=\"1\" /> <label for='hide_email'>{$this->lang->words['ucpTwitterReset']}</label><br />
			<span class='desc'>{$this->lang->words['ucpTwitterResetDesc']}</span>
		</li>
	</ul>
</fieldset>";
return $IPBHTML;
}


}


/*--------------------------------------------------*/
/* END OF FILE                                      */
/*--------------------------------------------------*/

?>