<?php

if(!defined('IN_IPB')) {
	echo "<h1>Uh oh!</h1>This file may not be accessed directly. If you have recently upgraded, make sure you upgraded all the relevant files.";
	die;
}

class admin_group_form__addonchat implements admin_group_form
{
   
   public function getDisplayContent($group=array(), $tabsUsed = 2) {

      ipsRegistry::getClass('class_localization')->loadLanguageFile(array('admin_addonchat'), 'addonchat');

      $this->html = ipsRegistry::getClass('output')->loadTemplate('cp_skin_addonchat', 'addonchat');

      $tab = "<li id='tabtab-GROUPS|X' class=''>AddonChat</li>";
      $content = "<div id='tabpane-GROUPS|X' class='acp-box'>Content goes here</div>";

      return array(
            'tabs' => $this->html->acp_group_form_tabs($group, ($tabsUsed+1)),
            'content' => $this->html->acp_group_form_main($group, ($tabsUsed+1)),
            'tabsUsed' => 1,
         );

   }

   public function getForSave() {
      $retval = array();

      $this->html = ipsRegistry::getClass('output')->loadTemplate('cp_skin_addonchat', 'addonchat');
      $addonchat_group_perms = $this->html->get_group_permission_array();
      foreach($addonchat_group_perms as $pname => $parray) {
         list($ptype, $pdefault, $psection) = $parray;

         if($ptype == 'u')
            $retval[$pname] = intval(ipsRegistry::$request[$pname]);
         else
            $retval[$pname] = ipsRegistry::$request[$pname];

      }
      return $retval;
   }

}


