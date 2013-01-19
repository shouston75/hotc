<?php

if(!defined('IN_IPB')) {
	echo "<h1>Uh oh!</h1>This file may not be accessed directly. If you have recently upgraded, make sure you upgraded all the relevant files.";
	die;
}

class task_item
{
	protected $class;
	protected $task			= array();
	protected $registry;
   protected $DB;
   protected $settings;

	public function __construct( ipsRegistry $registry, $class, $task ) {
		$this->registry	= $registry;
		$this->class      = $class;
		$this->task       = $task;
      $this->DB         = $this->registry->DB();
      $this->settings   = & $this->registry->fetchSettings();
	}

	public function runTask() {
      $this->DB->delete('addonchat_who');

      if($this->settings['addonchat_wc_enable']==0) {
         $this->class->appendTaskLog($this->task, "Disabled");
         $this->class->unlockTask($this->task);
         return;
      }

      $remote_file = "http://" . $this->settings['addonchat_server_name'] . "/scwho.php?style=0" .
         "&id=" . intval($this->settings['addonchat_account_id']) .
         "&port=" . intval($this->settings['addonchat_server_port']) .
         "&roompw=" . urlencode($this->settings['addonchat_login_md5pass']);

      if( ($raw_user_list = $this->addonchat_geturl($remote_file)) === FALSE)
      {
         $this->class->appendTaskLog($this->task, "Error");
      }
      else
      {
         foreach ($raw_user_list as $user_data)
         {
            list($admin, $username, $subroomname, $uid) = explode("\t", $user_data);

            $this->DB->insert('addonchat_who',
               array(                 
                  'username' => $username,
                  'subroom' => $subroomname,
                  'hidden' => 0,
                  'uid' => $uid,
                  'admin' => $admin,
            ));
         }
         $this->class->appendTaskLog($this->task, "OK");
      }
      
		$this->class->unlockTask($this->task);
	}

   private function addonchat_geturl($rlink) {
      $rlink = trim($rlink);

      if(ini_get('allow_url_fopen') == 0)
      {
         if(!function_exists('curl_init'))
            return FALSE;

         else
         {
            $ch = curl_init($rlink);
            curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
            curl_setopt($ch, CURLOPT_HEADER, FALSE);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
            curl_setopt($ch, CURLOPT_FAILONERROR, TRUE);
            curl_setopt($ch, CURLOPT_MAXREDIRS, 2);
            curl_setopt($ch, CURLOPT_TIMEOUT, 5);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT_MS, 5000);
            curl_setopt($ch, CURLOPT_DNS_CACHE_TIMEOUT, 86400);

            if( ($result = curl_exec($ch)) === FALSE)
               return FALSE;

            curl_close($ch);

            $lines = split("\n", $result);

            // Lets ditch the empty lines here...
            $new_lines = array();
            foreach($lines as $linecheck)
            {
               if(trim($linecheck)=="") {}
               else
                  $new_lines[] = $linecheck . "\n";
            }

            return $new_lines;
         }
      }
      else
         return file($rlink);
   }
}