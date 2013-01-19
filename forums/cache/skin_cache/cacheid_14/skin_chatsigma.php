<?php
/*--------------------------------------------------*/
/* FILE GENERATED BY INVISION POWER BOARD 3         */
/* CACHE FILE: Skin set id: 14               */
/* CACHE FILE: Generated: Sun, 09 Dec 2012 17:06:45 GMT */
/* DO NOT EDIT DIRECTLY - THE CHANGES WILL NOT BE   */
/* WRITTEN TO THE DATABASE AUTOMATICALLY            */
/*--------------------------------------------------*/

class skin_chatsigma_14 extends skinMaster{

/**
* Construct
*/
function __construct( ipsRegistry $registry )
{
	parent::__construct( $registry );
	

$this->_funcHooks = array();


}

/* -- chat_inline --*/
function chat_inline($server="",$acc_no="",$lang="",$w="",$h="",$user="",$pass="") {
$IPBHTML = "";
$IPBHTML .= "" . $this->registry->getClass('output')->addJSModule("chat", "0" ) . "
<h2>{$this->lang->words['sigmachat_title']}</h2>
<div class='generic_bar'></div>
<div id='parachat'>
	<applet codebase=\"http://{$server}/current/\" code=\"Client.class\" archive=\"scclient_{$lang}.zip\" width=\"{$w}\" height=\"{$h}\" MAYSCRIPT>
		<param name=\"room\" value=\"{$acc_no}\">
		<param name=\"cabbase\" value=\"scclient_{$lang}.cab\">
		<param name=\"username\" value=\"{$user}\">
		<param name=\"password\" value=\"{$pass}\">
		<param name=\"autologin\" value=\"yes\">
	</applet>
</div>
<h2>{$this->lang->words['sigmachat_help']}</h2>
<div class='generic_bar'></div>
<div id='parachat_help'>
	{$this->lang->words['sigmachat_help_text']}
</div>";
return $IPBHTML;
}


}


/*--------------------------------------------------*/
/* END OF FILE                                      */
/*--------------------------------------------------*/

?>