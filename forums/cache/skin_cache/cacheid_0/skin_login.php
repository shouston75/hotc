<?php
/*--------------------------------------------------*/
/* FILE GENERATED BY INVISION POWER BOARD 3         */
/* CACHE FILE: Skin set id: 0               */
/* CACHE FILE: Generated: Tue, 04 Aug 2009 19:56:23 GMT */
/* DO NOT EDIT DIRECTLY - THE CHANGES WILL NOT BE   */
/* WRITTEN TO THE DATABASE AUTOMATICALLY            */
/*--------------------------------------------------*/

class skin_login_0 {

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
	/* -- errors --*/
function errors($data="") {
$IPBHTML = "";
$IPBHTML .= "<p class='message error'>
	{$data}
</p>
<br /><br />";
return $IPBHTML;
}

/* -- showLogInForm --*/
function showLogInForm($message="",$referer="",$extra_form="", $has_openid=false, $facebookOpts=array()) {
$IPBHTML = "";
$IPBHTML .= "" . $this->registry->getClass('output')->addJSModule("signin", "0" ) . "
" . $this->registry->getClass('output')->addJSModule("facebook", "0" ) . "
<div id='login_form' class='clear'>
	
	<div id='member_login' class='left'>
		<h2 class='maintitle'>{$this->lang->words['log_in']}</h2>
		<div class='generic_bar'></div>
		<form action=\"" . $this->registry->getClass('output')->formatUrl( $this->registry->getClass('output')->buildUrl( "app=core&amp;module=global&amp;section=login&amp;do=process", 'public','' ), "", "" ) . "\" method=\"post\" id='login'>
			" . (($referer) ? ("
			<input type=\"hidden\" name=\"referer\" value=\"{$referer}\" />
			") : ("")) . "
			<div id='regular_signin'>
				<a id='_regularsignin'></a>
				<h3 class='bar'>{$this->lang->words['enter_name_and_pass']}</h3>
				<ul>
					<li class='field'>
						<label for='username'>{$this->lang->words['enter_name']}</label>
						<input id='username' type='text' class='input_text' name='username' size='25' />
					</li>
					<li class='field'>
						<label for='password'>{$this->lang->words['enter_pass']}</label>
						<input id='password' type='password' class='input_text' name='password' size='25' /><br />
						<a href='" . $this->registry->getClass('output')->formatUrl( $this->registry->getClass('output')->buildUrl( "app=core&amp;module=global&amp;section=lostpass", 'public','' ), "", "" ) . "' class='desc' title='{$this->lang->words['retrieve_pw']}'>{$this->lang->words['login_forgotten_pass']}</a>
					</li>
				</ul>
				" . (($has_openid) ? ("
				<p class='extra'>" . $this->registry->getClass('output')->getReplacement("openid_small") . " {$this->lang->words['have_openid']} <a href='#_openid' title='{$this->lang->words['use_openid']}' id='openid_open'>{$this->lang->words['sign_in_here']}</a></p>
				") : ("")) . "
				" . ((is_array($extra_form) AND count($extra_form)) ? ("
					".$this->__f__17bf9747a30925a4e76ede187eaca467($message,$referer,$extra_form,$has_openid,$facebookOpts)."				") : ("")) . "
			</div>
			" . (($has_openid) ? ("
			<div id='openid_signin'>
				<a id='_openid'></a>
				<h3 class='bar'>{$this->lang->words['sign_in_openid']}</h3>
				" . $this->registry->getClass('output')->getReplacement("openid_large") . "
				<ul>
					<li class='field'>
						<label for='openid'>{$this->lang->words['openid']}</label>
						<input id='openid' type='text' class='input_text' name='openid_url' value='http://' size='30' /><br />
						<span class='desc'>{$this->lang->words['openid_example']}</span>
					</li>
				</ul>
				<p class='extra'>" . $this->registry->getClass('output')->getReplacement("signin_icon") . " <a href='#_regularsignin' title='{$this->lang->words['regular_signin']}' id='openid_close'>{$this->lang->words['use_regular']}</a></p>
			</div>
			") : ("")) . "
			<hr />
			<fieldset id='signin_options'>
				<legend>{$this->lang->words['sign_in_options']}</legend>
				<ul>
					<li class='field checkbox'>
						<input type='checkbox' id='remember' checked='checked' name='rememberMe' value='1' class='input_check' />
						<label for='remember'>
							{$this->lang->words['rememberme']}<br />
							<span class='desc'>{$this->lang->words['notrecommended']}</span>
						</label>
					</li>
					" . ((!$this->settings['disable_anonymous']) ? ("
						<li class='field checkbox'>
							<input type='checkbox' id='invisible' name='anonymous' value='1' class='input_check' />
							<label for='invisible'>
								{$this->lang->words['form_invisible']}<br />
								<span class='desc'>{$this->lang->words['anon_name']}</span>
							</label>
						</li>
					") : ("")) . "
				</ul>
			</fieldset>
			<fieldset class='submit'>
				<input type='submit' class='input_submit' value='{$this->lang->words['sign_in_button']}' /> {$this->lang->words['or']} <a href='" . $this->registry->getClass('output')->formatUrl( $this->registry->getClass('output')->buildUrl( "", 'public','' ), "", "" ) . "' title='{$this->lang->words['cancel']}' class='cancel'>{$this->lang->words['cancel']}</a>
			</fieldset>
		</form>
	</div>
	<div id='guest_register' class='right general_box'>
		" . (($this->settings['fbc_enable'] AND $this->settings['fbc_api_id']) ? ("
			<h3 class='bar'>{$this->lang->words['fb_login_title']}</h3>
			<p>
				<div id='fbUserBox'>
					{$this->lang->words['login_w_facebook']}
					<br />
					<fb:login-button size=\"medium\" background=\"light\" length=\"long\" onlogin=\"ipb.facebook.login_loadUser();\"></fb:login-button>
				</div>
				<br clear='both' />
			</p>
			<br />
		") : ("")) . "
		<h3 class='bar'>{$this->lang->words['not_a_member']}</h3>
		<p>
			{$this->settings['login_page_info']}
		</p>
		<br />
		<p style='text-align: center'>
			<a href='" . $this->registry->getClass('output')->formatUrl( $this->registry->getClass('output')->buildUrl( "app=core&amp;module=global&amp;section=register", 'public','' ), "", "section=register" ) . "'>{$this->lang->words['register_now']}</a>
		</p>
	</div>
</div>
<!--FB Template-->
<div id='fb-template-main' style='display:none'>
	<div style='float:left; margin-right:4px;margin-bottom:3px'>
		<fb:profile-pic uid=loggedinuser facebook-logo=true></fb:profile-pic>
	</div>
	{$this->lang->words['welcome_prefix']} <strong><fb:name uid=loggedinuser useyou=false></fb:name></strong>
</div>
<div id='fb-template-notlinked' style='display:none'>
	<div class='desc'>
		{$this->lang->words['fb_logged_in']}
		<br clear='both'/>
		<br />{$this->lang->words['fb_no_forum_acct']}
		<br />
		<form action='{$this->settings['base_url']}app=core&amp;module=global&amp;section=login&amp;do=fbc_loginWithNew' method='post' name='linkNewAccForm' id='fbc_linkNewAccForm'>
			<input type='button' class='input_submit' id='fbc_completeNewAcc' value='{$this->lang->words['fb_new_account']}' />
		</form>
		<br />
		<br />
		<strong>{$this->lang->words['or']}</strong> {$this->lang->words['fb_link_account']}
		<div class='message error' id='fbc_linkError' style='display:none'></div>
		<br />
		<form action='{$this->settings['base_url']}app=core&amp;module=global&amp;section=login&amp;do=fbc_login' method='post' name='linkForm' id='fbc_linkForm'>
		{$this->lang->words['fb_link_email']} <input type='text' name='emailaddress' id='fbc_emailAddress' size='25' />
		<br />{$this->lang->words['fb_link_pass']} &nbsp;<input type='password' name='password' id='fbc_password' size='25' />
		<input type='button' class='input_submit' id='fbc_completeWithLink' value='{$this->lang->words['fb_link_complete']}' />
		</form>
	</div>
</div>
<div id='fb-template-linked' style='display:none'>
	<div class='desc'>
		{$this->lang->words['fb_logged_in']}
	</div>
	<br clear='both'/>
	<form action='{$this->settings['base_url']}app=core&amp;module=global&amp;section=login&amp;do=fbc_loginFromLinked' method='post' name='linkAlreadyForm' id='fbc_linkAlreadyForm'>
		<input type='button' class='input_submit' id='fbc_complete' value='{$this->lang->words['fb_complete_login']}' />
	</form>
</div>
" . ((IPSLib::fbc_enabled() === true) ? ("" . (($this->registry->output->isHTTPS) ? ("
		<script src=\"https://ssl.connect.facebook.com/js/api_lib/v0.4/FeatureLoader.js.php\" type=\"text/javascript\"></script>
	") : ("
		<script src=\"http://static.ak.connect.facebook.com/js/api_lib/v0.4/FeatureLoader.js.php\" type=\"text/javascript\"></script>
	")) . "
<script type=\"text/javascript\">
	/* Init Facebook JS */
	try
	{
		FB_RequireFeatures([\"XFBML\"], function()
		{
			FB.init(\"{$this->settings['fbc_api_id']}\", \"{$this->settings['fbc_xdlocation']}\" );
			FB.Facebook.get_sessionState().waitUntilReady(function()
			{
				FB.Connect.ifUserConnected( ipb.facebook.login_loadUser );
			} );
		} );
	}
	catch( error )
	{
		//alert( error );
	}
</script>") : ("")) . "";
return $IPBHTML;
}


function __f__17bf9747a30925a4e76ede187eaca467($message="",$referer="",$extra_form="", $has_openid=false, $facebookOpts=array())
{
	$_ips___x_retval = '';
	foreach( $extra_form as $form_fields )
	{
		
		$_ips___x_retval .= "
						{$form_fields}
					
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