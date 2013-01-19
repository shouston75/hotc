<?php

   /* Member Specific Options */
   $TABLE[] = "ALTER TABLE members ADD addonchat_banned TINYINT(1) NOT NULL DEFAULT '0';";

   /* Group Options */
   $addonchat_group_options = array(
      'addonchat_banned'                 => array('b',0),
      'addonchat_can_login'              => array('b',1),
      'addonchat_icon'                   => array('u',0),
      'addonchat_can_msg'                => array('b',1),
      'addonchat_idle_kick'              => array('b',0),
      'addonchat_can_action'             => array('b',1),
      'addonchat_post_delay'             => array('u',0),
      'addonchat_allow_pm'               => array('b',1),
      'addonchat_allow_room_create'      => array('b',0),
      'addonchat_allow_avatars'          => array('b',1),
      'addonchat_can_random'             => array('b',0),
      'addonchat_allow_bbcode'           => array('b',1),
      'addonchat_allow_color'            => array('b',1),
      'addonchat_msg_scroll'             => array('b',1),
      'addonchat_max_msg_length'         => array('u',1024),
      'addonchat_filter_shout'           => array('b',1),
      'addonchat_filter_profanity'       => array('b',1),
      'addonchat_filter_word_replace'    => array('b',1),
      'addonchat_can_nick'               => array('b',0),
      'addonchat_level'                  => array('u',3),
      'addonchat_is_admin'               => array('b',0),
      'addonchat_allow_html'             => array('b',0),
      'addonchat_can_kick'               => array('b',0),
      'addonchat_can_affect_admin'       => array('b',0),
      'addonchat_can_grant'              => array('b',0),
      'addonchat_can_cloak'              => array('b',0),
      'addonchat_can_see_cloak'          => array('b',0),
      'addonchat_login_cloaked'          => array('b',0),
      'addonchat_can_ban'                => array('b',0),
      'addonchat_can_ban_subnet'         => array('b',0),
      'addonchat_can_system_speak'       => array('b',0),
      'addonchat_can_silence'            => array('b',0),
      'addonchat_can_fnick'              => array('b',0),
      'addonchat_can_launch_website'     => array('b',0),
      'addonchat_can_transfer'           => array('b',0),
      'addonchat_can_join_nopw'          => array('b',0),
      'addonchat_can_topic'              => array('b',0),
      'addonchat_can_close'              => array('b',0),
      'addonchat_can_ipquery'            => array('b',0),
      'addonchat_can_geo_locate'         => array('b',0),
      'addonchat_can_query_ether'        => array('b',0),
      'addonchat_can_clear_screen'       => array('b',0),
      'addonchat_clear_history'          => array('b',0),
      'addonchat_allow_img_tag'          => array('b',0),
      'addonchat_is_speaker'             => array('b',0),
      'addonchat_is_super_moderator'     => array('b',0),
      'addonchat_can_enable_moderation'  => array('b',0),
      'addonchat_is_unmoderated'         => array('b',0),
      'addonchat_allow_admin_console'    => array('b',0),
      'addonchat_can_view_transcripts'   => array('b',0),
      'addonchat_can_edit_profile'		  => array('b',1),
      'addonchat_can_view_profile'		  => array('b',1),   
   	'addonchat_can_delete_profile'     => array('b',0),   
   );

   foreach($addonchat_group_options as $sql_name => $parray) {
      list($sql_type, $sql_default) = $parray;
      switch($sql_type) {
         case 'u' : $sql_type = "INT UNSIGNED"; break;
         default  : $sql_type = "TINYINT(1) UNSIGNED";
      }
      $TABLE[] = "ALTER TABLE groups ADD $sql_name $sql_type NOT NULL DEFAULT '$sql_default';";
   }

   /* AddonChat Settings */
   $TABLE[] = "CREATE TABLE addonchat_settings
      (account_id INT UNSIGNED NOT NULL DEFAULT 0,
         login_md5pass VARCHAR(32) NOT NULL DEFAULT '',
         language VARCHAR(8) NOT NULL DEFAULT 'en',
         settings_retrieved TINYINT(1) NOT NULL DEFAULT 0,
         login_email VARCHAR(64) NOT NULL DEFAULT '',
         edition INT UNSIGNED NOT NULL DEFAULT 0,
         modules VARCHAR(128) NOT NULL DEFAULT '',
         ras_capable TINYINT(1) NOT NULL DEFAULT 0,
         edition_name VARCHAR(128) NOT NULL DEFAULT 'Unknown',
         expire_date DATE NOT NULL DEFAULT 0,
         ras_enabled TINYINT(1) NOT NULL DEFAULT 0,
         ras_url VARCHAR(128) NOT NULL DEFAULT 'http://',
         server_name VARCHAR(64) NOT NULL DEFAULT 'client0.addonchat.com',
         server_port INT UNSIGNED NOT NULL DEFAULT 8000,
         cpanel_login VARCHAR(255) NOT NULL DEFAULT 'http://',
         title VARCHAR(48) NOT NULL DEFAULT 'My Chat Room',
         account_id_fq VARCHAR(32) NOT NULL DEFAULT 'SC-0',
         customer_id_fq VARCHAR(32) NOT NULL DEFAULT 'RC-0',
         enable_auto_login TINYINT(1) NOT NULL DEFAULT 1,
         allow_unregistered_guests TINYINT(1) NOT NULL DEFAULT 0,
         url_exit VARCHAR(128) NOT NULL DEFAULT 'http://',
         width VARCHAR(8) NOT NULL DEFAULT '100%',
         height VARCHAR(8) NOT NULL DEFAULT 450,
         width_float INT UNSIGNED DEFAULT 620,
         height_float INT UNSIGNED DEFAULT 420,
         applet_parameters TEXT NOT NULL,
         code_parameters TEXT NOT NULL,
         direct_settings TEXT NOT NULL)";

   /* AddonChat Who's Chatting Table */
   $TABLE[] = "CREATE TABLE addonchat_who
      (id INT UNSIGNED PRIMARY KEY NOT NULL AUTO_INCREMENT,
         username CHAR(48) NOT NULL,
         subroom CHAR(48) NOT NULL,
         hidden TINYINT(1) NOT NULL DEFAULT 0,
         uid INT UNSIGNED NOT NULL DEFAULT 0,
         admin TINYINT(1) NOT NULL DEFAULT 0)";
