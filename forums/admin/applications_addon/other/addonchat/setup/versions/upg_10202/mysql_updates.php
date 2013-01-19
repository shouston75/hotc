<?php

	if(!defined('IN_IPB')) {
		echo "<h1>Uh oh!</h1>This file may not be accessed directly. If you have recently upgraded, make sure you uploaded all relevant files.";
		die;
	}

	$SQL = array();

	$addonchat_group_options = array(
      'addonchat_can_edit_profile'		 => array('b',1),
      'addonchat_can_view_profile'		 => array('b',1),   
   	'addonchat_can_delete_profile'    => array('b',0),
   );
   
   foreach($addonchat_group_options as $sql_name => $parray) {
      list($sql_type, $sql_default) = $parray;
      switch($sql_type) {
         case 'u' : $sql_type = "INT UNSIGNED"; break;
         default  : $sql_type = "TINYINT(1) UNSIGNED";
      }
      $SQL[] = "ALTER TABLE groups ADD $sql_name $sql_type NOT NULL DEFAULT '$sql_default';";
   }   

