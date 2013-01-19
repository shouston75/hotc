<?php

if(!defined('IN_IPB')) {
	echo "<h1>Uh oh!</h1>This file may not be accessed directly. If you have recently upgraded, make sure you uploaded all relevant files.";
	die;
}

class public_addonchat_addonchat_addonchat extends ipsCommand {

   public function doExecute(ipsRegistry $registry) {
      ipsRegistry::getClass('class_localization')->loadLanguageFile(array('public_addonchat'), 'addonchat');

      $this->registry->output->addNavigation($this->lang->words['addonchat_title'], '');
      $this->registry->output->setTitle($this->lang->words['addonchat_title']);

      //print_r($this->memberData);
      //print_r($this->settings);

      /*
       * memberData.addonchat_banned
       * memberData.addonchat_can_login
       */
      if( ($this->memberData['addonchat_banned'] != 0) || ($this->memberData['addonchat_can_login'] != 1) )
         $this->registry->output->showError('addonchat_err_noperm', 'ADDONCHAT-01');

      /**
       * settings.addonchat_account_id
       * settings.addonchat_server_name
       * settings.addonchat_server_port
       * settings.addonchat_language
       * settings.addonchat_width
       * settings.addonchat_height
       * settings.addonchat_autologin
       * settings.addonchat_signed
       * memberData.members_display_name
       * memberData.members_pass_hash
       * memberData.members_pass_salt
       * memberData.addonchat_can_login
       * memberData.addonchat_banned
       */

      $addonchat = array();
      if(preg_match("/client(\d+).*/i", $this->settings['addonchat_server_name'], $ac_matches))
         $addonchat['server_id'] = $ac_matches[1];
      else if($this->settings['addonchat_server_name'] == 'betaclient.addonchat.com')
          $addonchat['server_id'] = 12;
      else
         $addonchat['server_id'] = 0;
      if( (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']=='on') || (@strpos(@strtolower($_SERVER['SCRIPT_URI']),"https")===0) )
      {
         $addonchat['protocol'] = "https";
         $addonchat['ssl'] = "true";
      }
      else
      {
         $addonchat['protocol'] = "http";
         $addonchat['ssl'] = "false";
      }

      $addonchat['signed'] = ($this->settings['addonchat_signed']==1) ? "true" : "false";

       $HTML = <<<______EOF
<table class="tborder" border="0" width="100%" align="center" style="padding:0px; margin:0px;">
<tbody>
	<tr>
		<td align="center"><script type="text/javascript">
   /*<![CDATA[*/
      var addonchat = {
         server: "{$addonchat['server_id']}",
         id: {$this->settings['addonchat_account_id']},
         width: "{$this->settings['addonchat_width']}",
         height: "{$this->settings['addonchat_height']}",
         language: "{$this->settings['addonchat_language']}",
         signed: {$addonchat['signed']},
         ssl: {$addonchat['ssl']}
      }
      var addonchat_param = {
         <!--raw addonchat.code_parameters-->
         autologin: {$this->settings['addonchat_autologin']},
         username: "{$this->memberData['members_display_name']}",
         password: "{$this->memberData['members_pass_hash']}"
      }
   /* ]]> */
</script><script type="text/javascript"
src="{$addonchat['protocol']}://{$this->settings['addonchat_server_name']}/chat.js"></script><noscript>
   This forum uses <a href="http://www.addonchat.com/">AddonChat Chat Room Software</a>.<br /> <br />
   To use this chat room, please enable JavaScript in your browser.
</noscript>

		</td>
	</tr>
</tbody>
</table>
______EOF;

      $this->registry->output->addContent($HTML);
      //$this->registry->output->addContent("<pre>Settings:\n" . print_r($this->settings, true) . "memberData:\n" . print_r($this->memberData, true) . "<pre>");
      $this->registry->output->sendOutput();
   }

}