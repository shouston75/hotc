<?php

class cp_skin_addonchat extends output {

   public function __destruct() { }

   public function get_group_permission_array() {
      return array(
         'addonchat_banned'                 => array('b',0,'access'),
         'addonchat_can_login'              => array('b',1,null),
         'addonchat_icon'                   => array('u',0,'icon'),
         'addonchat_can_msg'                => array('b',1,'std'),
         'addonchat_idle_kick'              => array('b',0,null),
         'addonchat_can_action'             => array('b',1,null),
         'addonchat_post_delay'             => array('u',0,null),
         'addonchat_allow_pm'               => array('b',1,null),
         'addonchat_allow_room_create'      => array('b',0,null),
         'addonchat_allow_avatars'          => array('b',1,null),
         'addonchat_can_random'             => array('b',0,null),
         'addonchat_allow_bbcode'           => array('b',1,null),
         'addonchat_allow_color'            => array('b',1,null),
         'addonchat_msg_scroll'             => array('b',1,null),
         'addonchat_max_msg_length'         => array('u',1024,null),
         'addonchat_filter_shout'           => array('b',1,null),
         'addonchat_filter_profanity'       => array('b',1,null),
         'addonchat_filter_word_replace'    => array('b',1,null),
         'addonchat_can_nick'               => array('b',0,null),
      	 'addonchat_can_edit_profile'		=> array('b',1,null),
         'addonchat_can_view_profile'		=> array('b',1,null),
         'addonchat_level'                  => array('u',3,'rank'),
         'addonchat_is_admin'               => array('b',0,'admin'),
         'addonchat_allow_html'             => array('b',0,null),
         'addonchat_can_kick'               => array('b',0,null),
         'addonchat_can_affect_admin'       => array('b',0,null),
         'addonchat_can_grant'              => array('b',0,null),
         'addonchat_can_cloak'              => array('b',0,null),
         'addonchat_can_see_cloak'          => array('b',0,null),
         'addonchat_login_cloaked'          => array('b',0,null),
         'addonchat_can_ban'                => array('b',0,null),
         'addonchat_can_ban_subnet'         => array('b',0,null),
         'addonchat_can_system_speak'       => array('b',0,null),
         'addonchat_can_silence'            => array('b',0,null),
         'addonchat_can_fnick'              => array('b',0,null),
         'addonchat_can_launch_website'     => array('b',0,null),
         'addonchat_can_transfer'           => array('b',0,null),
         'addonchat_can_join_nopw'          => array('b',0,null),
         'addonchat_can_topic'              => array('b',0,null),
         'addonchat_can_close'              => array('b',0,null),
         'addonchat_can_ipquery'            => array('b',0,null),
         'addonchat_can_geo_locate'         => array('b',0,null),
         'addonchat_can_query_ether'        => array('b',0,null),
         'addonchat_can_clear_screen'       => array('b',0,null),
         'addonchat_clear_history'          => array('b',0,null),
         'addonchat_allow_img_tag'          => array('b',0,null),
         'addonchat_can_delete_profile'		=> array('b',0,null),
         'addonchat_can_view_transcripts'   => array('b',0,null),         
         'addonchat_is_speaker'             => array('b',0,'mod'),
         'addonchat_is_super_moderator'     => array('b',0,null),
         'addonchat_can_enable_moderation'  => array('b',0,null),
         'addonchat_is_unmoderated'         => array('b',0,null),
         'addonchat_allow_admin_console'    => array('b',0,'rac'),
      );
   }

   public function acp_group_form_main($group, $tabId) {
      $addonchat_group_perms = $this->get_group_permission_array();
      $form = array();

      foreach($addonchat_group_perms as $pname => $parray) {
         list($perm_type, $perm_default) = $parray;

         if($perm_type == 'b')
            $form[$pname] = $this->registry->output->formYesNo($pname, $group[$pname]);
         else
            $form[$pname] = $this->registry->output->formSimpleInput($pname, $group[$pname], 3);
      }

      /* Form Header */
      $HTML = <<<______EOF
         <div id='tab_GROUPS_{$tabId}_content'>
            <div>
               <table class='ipsTable'>
______EOF;

      /* Form HTML */
      foreach($addonchat_group_perms as $pname => $parray) {
         list($perm_type, $perm_default, $perm_section) = $parray;
         $pnameugp = $pname . '_ugp';
         $pnameugpd = $pnameugp . 'd';

         /* Form Section */
         if($perm_section !== null) {
            $section_name = 'addonchat_' . $perm_section . '_ugps';
            $HTML .= <<<____________EOF
                  <tr>
                     <th colspan='2' class='head'><strong>{$this->lang->words[$section_name]}</strong></th>
                  </tr>
____________EOF;
         }

         /* Form Row */
         $HTML .= <<<_________EOF
                  <tr>
                     <td class='field_title>
                        <strong class=='title'>{$this->lang->words[$pnameugp]}</string>                        
                     </td>
                     <td class='field_field'>
                        {$form[$pname]}
                        <div class='desctext'>{$this->lang->words[$pnameugpd]}</div>
                     </td>
                  </tr>
_________EOF;
      }

      /* Form Footer */
      $HTML .= <<<______EOF
               </table>
            </div>
         </div>
______EOF;

      return $HTML;
   }

   public function acp_group_form_tabs($group, $tabId) {
      return "<li id='tab_GROUPS_{$tabId}' class=''>AddonChat</li>";
   }

   public function acp_member_form_tabs($member, $tabId)
   {   	
      $IPBHTML = "";
      $IPBHTML .= <<<______EOF
         <li id='tab_MEMBERS_{$tabId}' class=''>{$this->lang->words['addonchat_user_tab']}</li>
______EOF;

      return $IPBHTML;
   }

   public function acp_member_form_main($member, $tabId ) {
      $form_chat_banned		= ipsRegistry::getClass('output')->formYesNo( "addonchat_banned", $member['addonchat_banned'] );
      $IPBHTML = "";
      $IPBHTML .= <<<______EOF
         <div id='tab_MEMBERS_{$tabId}_content'>
            <table class='ipsTable double_pad'>
               <tr>
                  <th colspan='2'>{$this->lang->words['addonchat_user_settings']}</th>
               </tr>             
				<tr>
					<td class='field_title'><strong class='title'>{$this->lang->words['addonchat_user_ban']}</strong></td>
					<td class='field_field'>
						<span id='MF__addonchat_chat_banned'>{$form_chat_banned}</span>
					</td>
				</tr>               
            </table>
         </div>
______EOF;
      return $IPBHTML;
   }

}

?>
