<?php
/*--------------------------------------------------*/
/* FILE GENERATED BY INVISION POWER BOARD 3         */
/* CACHE FILE: Skin set id: 9               */
/* CACHE FILE: Generated: Thu, 13 Dec 2012 15:52:24 GMT */
/* DO NOT EDIT DIRECTLY - THE CHANGES WILL NOT BE   */
/* WRITTEN TO THE DATABASE AUTOMATICALLY            */
/*--------------------------------------------------*/

class skin_printpage_9 extends skinMaster{

/**
* Construct
*/
function __construct( ipsRegistry $registry )
{
	parent::__construct( $registry );
	

$this->_funcHooks = array();
$this->_funcHooks['pp_end'] = array('pagecopyright');
$this->_funcHooks['pp_postentry'] = array('postqueued');


}

/* -- choose_form --*/
function choose_form($fid="",$tid="",$title="") {
$IPBHTML = "";

if ( ! isset( $this->registry->templateStriping['strip'] ) ) {
$this->registry->templateStriping['strip'] = array( FALSE, "row1","row2");
}
$IPBHTML .= "<h2>{$this->lang->words['tvo_title']}&nbsp;{$title}</h2>
<p class='message unspecific'>{$this->lang->words['please_choose']}</p>
<div class='generic_bar'></div><div class='post_form'>
	<ul>
		<li class='field'>
			<strong><a href=\"" . $this->registry->getClass('output')->formatUrl( $this->registry->getClass('output')->buildUrl( "app=forums&amp;module=forums&amp;section=printtopic&amp;client=printer&amp;f={$fid}&amp;t={$tid}", "public",'' ), "", "" ) . "\">{$this->lang->words['o_print_title']}</a></strong><br />{$this->lang->words['o_print_desc']}
		</li>
		<li class='field'>
			<strong><a href=\"" . $this->registry->getClass('output')->formatUrl( $this->registry->getClass('output')->buildUrl( "app=forums&amp;module=forums&amp;section=printtopic&amp;client=html&amp;f={$fid}&amp;t={$tid}", "public",'' ), "", "" ) . "\">{$this->lang->words['o_html_title']}</a></strong><br />{$this->lang->words['o_html_desc']}
		</li>
		<li class='field'>
			<strong><a href=\"" . $this->registry->getClass('output')->formatUrl( $this->registry->getClass('output')->buildUrl( "app=forums&amp;module=forums&amp;section=printtopic&amp;client=wordr&amp;f={$fid}&amp;t={$tid}", "public",'' ), "", "" ) . "\">{$this->lang->words['o_word_title']}</a></strong><br />{$this->lang->words['o_word_desc']}
		</li>
	</ul>
	<p class='submit'>
		<a href=\"" . $this->registry->getClass('output')->formatUrl( $this->registry->getClass('output')->buildUrl( "showtopic={$tid}", "public",'' ), "", "" ) . "\" title='{$this->lang->words['back_topic']}'>{$this->lang->words['_laquo']} {$this->lang->words['back_topic']}</a>
	</p>
</div>";
return $IPBHTML;
}

/* -- pp_end --*/
function pp_end() {
$IPBHTML = "";
if( IPSLib::locationHasHooks( 'skin_printpage', $this->_funcHooks['pp_end'] ) )
{
$count_9b60406cea31d9fa959df3f7a3864f18 = is_array($this->functionData['pp_end']) ? count($this->functionData['pp_end']) : 0;
}
$IPBHTML .= "<!--Copyright-->
" . (($this->settings['ipb_copy_number'] == '') ? ("
<p id='copyright'>{$this->lang->words['powered_by']}Invision Power Board (http://www.invisionboard.com)<br />&copy; Invision Power Services (http://www.invisionpower.com)</p>
") : ("")) . "
	</div>
</body>
</html>";
return $IPBHTML;
}

/* -- pp_header --*/
function pp_header($forum_name="",$topic_title="",$topic_starter="",$fid="",$tid="",$IPS_DOC_CHAR_SET=IPS_DOC_CHAR_SET) {
$IPBHTML = "";
$IPBHTML .= "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Strict//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd\">
<html xml:lang=\"en\" lang=\"en\" xmlns=\"http://www.w3.org/1999/xhtml\">
	<head>
		<meta http-equiv=\"content-type\" content=\"text/html; charset={$IPS_DOC_CHAR_SET}\" />
		<link rel=\"stylesheet\" type=\"text/css\" media=\"all\" href=\"{$this->settings['public_dir']}style_css/{$this->registry->output->skin['_csscacheid']}/ipb_print.css\" />
		<title>{$this->settings['board_name']}</title>
	</head>
	<body id='ipboard_body'>
	<div id=\"content\">
		<h2>{$topic_title} <a href=\"" . $this->registry->getClass('output')->formatUrl( $this->registry->getClass('output')->buildUrl( "showtopic={$tid}", "public",'' ), "", "" ) . "\" title=\"{$this->lang->words['click_toview']}\">{$this->lang->words['view_original']}</a></h2>
		<div id='breadcrumb'>{$this->settings['board_name']} -> {$forum_name} -> {$topic_title}</div>
		<br /><br />";
return $IPBHTML;
}

/* -- pp_postentry --*/
function pp_postentry($poster="",$entry="") {
$IPBHTML = "";
if( IPSLib::locationHasHooks( 'skin_printpage', $this->_funcHooks['pp_postentry'] ) )
{
$count_f0bba4aeb4132d28b49b002c07a8436a = is_array($this->functionData['pp_postentry']) ? count($this->functionData['pp_postentry']) : 0;
$this->functionData['pp_postentry'][$count_f0bba4aeb4132d28b49b002c07a8436a]['poster'] = $poster;
$this->functionData['pp_postentry'][$count_f0bba4aeb4132d28b49b002c07a8436a]['entry'] = $entry;
}
$IPBHTML .= "<div class='post_block first hentry " . (($post['post']['queued']==1) ? ("moderated") : ("")) . "' id='post_id_{$post['post']['pid']}'>
	<div class='post_wrap'>
		<h3>{$entry['members_display_name']}</h3>
		<p class='posted_info'>
			{$this->lang->words['posted_prefix']} {$entry['post_date']}
		</p>
		<div class='post_body'>
			<div class='post entry-content'>
				{$entry['post']}
				{$entry['attachmentHtml']}
				<br />
			</div>
		</div>
	</div>	
</div>";
return $IPBHTML;
}


}


/*--------------------------------------------------*/
/* END OF FILE                                      */
/*--------------------------------------------------*/

?>