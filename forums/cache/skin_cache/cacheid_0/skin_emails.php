<?php
/*--------------------------------------------------*/
/* FILE GENERATED BY INVISION POWER BOARD 3         */
/* CACHE FILE: Skin set id: 0               */
/* CACHE FILE: Generated: Tue, 04 Aug 2009 19:56:23 GMT */
/* DO NOT EDIT DIRECTLY - THE CHANGES WILL NOT BE   */
/* WRITTEN TO THE DATABASE AUTOMATICALLY            */
/*--------------------------------------------------*/

class skin_emails_0 {

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
	/* -- boardRules --*/
function boardRules($title="",$body="") {
$IPBHTML = "";
$IPBHTML .= "<h2>{$title}</h2>
<div class='row1 general_box rules'>
	{$body}
</div>";
return $IPBHTML;
}

/* -- errors --*/
function errors($data="") {
$IPBHTML = "";
$IPBHTML .= "<p class='message error'>
	{$data}
</p>
<br />";
return $IPBHTML;
}

/* -- forward_form --*/
function forward_form($title="",$text="",$lang="") {
$IPBHTML = "";
$IPBHTML .= "<form action=\"" . $this->registry->getClass('output')->formatUrl( $this->registry->getClass('output')->buildUrl( "app=forums&amp;module=extras&amp;section=forward", 'public','' ), "", "" ) . "\" method=\"post\" name='REPLIER'>
	<input type=\"hidden\" name=\"do\" value=\"01\" />
	<input type=\"hidden\" name=\"st\" value=\"{$this->request['st']}\" />
	<input type=\"hidden\" name=\"f\" value=\"{$this->request['f']}\" />
	<input type=\"hidden\" name=\"t\" value=\"{$this->request['t']}\" />
	<input type='hidden' name='k' value='{$this->member->form_hash}' />
	
	<p class='message'>{$this->lang->words['email_friend']}</p><br />
	
	<h2 class='maintitle'>{$this->lang->words['title']}</h2>
	<div class='generic_bar'></div>
	<div class='post_form'>
		<fieldset>
			<h3 class='bar'>{$this->lang->words['email_recepient']}</h3>
			<ul>
				<li class='field'>
					<label for='to_lang'>{$this->lang->words['send_lang']}</label>
					<select name='lang' class='input_select' id='to_lang'>
						".$this->__f__5ce379a6ec94ff75a0378cd0a98a0256($title,$text,$lang)."					</select>
				</li>
				<li class='field'>
					<label for='to_name'>{$this->lang->words['to_name']}</label>
					<input type=\"text\" id='to_name' class='input_text' name=\"to_name\" value=\"\" size=\"30\" maxlength=\"100\" />
				</li>
				<li class='field'>
					<label for='to_email'>{$this->lang->words['to_email']}</label>
					<input type=\"text\" id='to_email' class='input_text' name=\"to_email\" value=\"\" size=\"30\" maxlength=\"100\" />
				</li>
				<li class='field'> 
					<label for='subject'>{$this->lang->words['subject']}</label>
					<input type=\"text\" id=\"subject\" class=\"input_text\" name=\"subject\" value=\"{$title}\" size=\"30\" maxlength=\"120\" />
				</li> 				
				<li class='field'>
					<label for='to_message'>{$this->lang->words['message']}</label>
					<textarea id='to_message' cols=\"60\" rows=\"12\" wrap=\"soft\" name=\"message\" class=\"input_text\">{$text}</textarea>
				</li>
			</ul>
		</fieldset>
		<fieldset class='submit'>
			<input class='input_submit' type=\"submit\" value=\"{$this->lang->words['submit_send']}\" /> {$this->lang->words['or']} <a href='" . $this->registry->getClass('output')->formatUrl( $this->registry->getClass('output')->buildUrl( "showtopic={$this->request['t']}", 'public','' ), "", "" ) . "' title='{$this->lang->words['cancel']}' class='cancel'>{$this->lang->words['cancel']}</a>
		</fieldset>
	</div>
</form>";
return $IPBHTML;
}


function __f__5ce379a6ec94ff75a0378cd0a98a0256($title="",$text="",$lang="")
{
	$_ips___x_retval = '';
	foreach( $this->caches['lang_data'] as $l )
	{
		
		$_ips___x_retval .= "
							<option value='{$l['lang_id']}' " . (($l['lant_id'] == $this->memberData['language']) ? ("selected='selected'") : ("")) . ">{$l['lang_title']}</option>
						
";
	}
	$_ips___x_retval .= '';
	return $_ips___x_retval;
}

/* -- sendMailForm --*/
function sendMailForm($data="") {
$IPBHTML = "";
$IPBHTML .= "<form action=\"" . $this->registry->getClass('output')->formatUrl( $this->registry->getClass('output')->buildUrl( "app=members&module=messaging&section=contact&do=01", 'public','' ), "", "" ) . "\" method=\"post\" id=\"REPLIER\">
	<input type=\"hidden\" name=\"to\" value=\"{$data['TO']}\" />
	<input type='hidden' name='k' value='{$this->member->form_hash}' />
	
	<p class='message unspecific'>{$this->lang->words['imp_text']}</p><br />
	
	<h2 class='maintitle'>{$this->lang->words['emailing_prefix']} {$data['NAME']}</h2>	
	<div class='generic_bar'></div>
	<div class='post_form'>
		<fieldset class='row1'>
			<h3 class='bar'>{$this->lang->words['email_details']}</h3>
			<ul>
				<li class='field'>
					<label for='subject'>{$this->lang->words['subject']}</label>
					<input type=\"text\" name=\"subject\" id='subject' class='input_text' value=\"{$data['subject']}\" size=\"50\" maxlength=\"50\" />
				</li>
				<li class='field'>
					<label for='message'>{$this->lang->words['message']}</label>
					<textarea cols=\"60\" rows=\"8\" wrap=\"soft\" name=\"message\" class=\"input_text\" id='message'>{$data['content']}</textarea><br />
					<span class='desc'>{$this->lang->words['msg_txt']}</span>
				</li>
			</ul>
		</fieldset>
		<fieldset class='submit'>
			<input class='input_submit' type=\"submit\" value=\"{$this->lang->words['submit_send']}\" /> {$this->lang->words['or']} <a href='" . $this->registry->getClass('output')->formatUrl( $this->registry->getClass('output')->buildUrl( "showuser={$data['TO']}", 'public','' ), "", "" ) . "' title='{$this->lang->words['cancel']}' class='cancel'>{$this->lang->words['cancel']}</a>
		</fieldset>
	</div>
</form>";
return $IPBHTML;
}

/* -- sentScreen --*/
function sentScreen($member_name="") {
$IPBHTML = "";
$IPBHTML .= "<h2>{$this->lang->words['email_sent']}</h2>
<p class='message unspecific'>{$this->lang->words['email_sent_txt']} {$member_name}.</p>";
return $IPBHTML;
}

/* -- show_address --*/
function show_address($data="") {
$IPBHTML = "";
$IPBHTML .= "<h2>{$this->lang->words['send_email_to']} {$data['NAME']}</h2>
<div class='general_box'>
	{$this->lang->words['show_address_text']}
	<br />
	<strong><a href=\"mailto:{$data['ADDRESS']}\" class=\"misc\">{$this->lang->words['send_email_to']} {$data['NAME']}</a></strong>
</div>";
return $IPBHTML;
}



}

/*--------------------------------------------------*/
/* END OF FILE                                      */
/*--------------------------------------------------*/

?>