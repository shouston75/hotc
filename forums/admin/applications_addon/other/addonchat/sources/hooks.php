<?php

if(!defined('IN_IPB')) {
	echo "<h1>Uh oh!</h1>This file may not be accessed directly. If you have recently upgraded, make sure you upgraded all the relevant files.";
	die;
}

class addonchat_hooks
{
	protected $registry;
	protected $DB;
	protected $settings;
	protected $request;
	protected $lang;
	protected $member;
	protected $cache;

	public function __construct( ipsRegistry $registry )
	{
		$this->registry   = $registry;
		$this->DB         = $this->registry->DB();
		$this->settings   =& $this->registry->fetchSettings();
		$this->request    =& $this->registry->fetchRequest();
		$this->lang       = $this->registry->getClass('class_localization');
		$this->member     = $this->registry->member();
		$this->memberData =& $this->registry->member()->fetchMemberData();
		$this->cache      = $this->registry->cache();
		$this->caches     =& $this->registry->cache()->fetchCaches();

      $this->lang->loadLanguageFile( array( 'public_addonchat' ), 'addonchat' );
	}

   /*
    * Board Index (Active Content) Who's Chatting
    */
   public function whosChatting() {

      /* Load AddonChat Who's Chatting List */
      if($this->settings['addonchat_wc_enable']==0) return "";

      $this->DB->build(array('select' 	=> '*',
                             'from'		=> array('addonchat_who' => 'aw'),
                             'order'   => 'admin DESC,username'));
      $this->DB->execute();

      $user_count = 0;
      $userlist = array();
      $mids = array();
      while($chatuser = $this->DB->fetch()) {
         if($chatuser['hidden'] == 0) {
            $userlist[$chatuser['uid']] = $chatuser['username'];
            $user_count++;
            if($chatuser['uid'] > 0) $mids[$chatuser['uid']] = $chatuser['uid'];
         }
      }

      /* Display nothing if chat room is vacant */
      if($user_count <= 0) {
         return "";
      }

      /* Load Associated IP.Board User Data */
      $anonusers = array();
      if(count($mids)>0)
      {
			$this->DB->build( array( 'select' => 'm.member_id, m.members_display_name, m.member_group_id, m.members_seo_name, m.login_anonymous',
												     'from'   => array( 'members' => 'm' ),
												     'where'  => "m.member_id IN(" . implode(",", $mids) . ")"));
			$this->DB->execute();

			while ($m = $this->DB->fetch())
			{
            list($is_anon, $loggedin) = explode('&', $m['login_anonymous']);
            if($is_anon) $anonusers[] = $m['member_id'];
            $m['members_display_name'] = IPSMember::makeNameFormatted( $m['members_display_name'], $m['member_group_id'] );
            $member_ids[$m['member_id']] = "<a href='" . $this->registry->getClass('output')->buildSEOUrl( "showuser={$m['member_id']}", 'public', $m['members_seo_name'], 'showuser' ) . "'>{$m['members_display_name']}</a>";
			}
      }

      /* Prepare Comma Separated User List */
      $htmlusers = "";
      $user_count = 0;
      foreach($userlist as $uid => $uname) {
         if(!in_array($uid, $anonusers)) {
            if($user_count > 0) $htmlusers .= ", ";
            if(array_key_exists($uid, $member_ids))
               $htmlusers .= "<span class='name'>" . $member_ids[$uid] . "</span>";
            else
               $htmlusers .= "<span class='name'>$uname</span>";
            $user_count++;
         }
      }

      /* Prepare for Display */
      $retval = <<<______EOT
				<br>
            <h4 class='statistics_head'>
               {$this->lang->words['addonchat_wcb_title']} <span>{$this->lang->words['addonchat_wcb_title_paren']}</span>
            </h4>
            <br />
            <p>
               {$htmlusers}
            </p>
______EOT;

      return $retval;
   }

   /*
    * Chat Room Tab Count Indicator
    */
	public function getChatTabCount()
	{
      $this->DB->build(array(	'select' 	=> 'COUNT(*)',
                                 'from'		=> array( 'addonchat_who' => 'aw' )));
      $this->DB->execute();

      /* Member does not exist */
      if ( (!$count = ipsRegistry::DB()->fetch()) )
         return "";

      $count = $count['COUNT(*)'];

      if($count == 0) return "";
      
      $retval = <<<______EOF
      <div style='display:none;' id='chat-tab-count-wrap'><span id='chat-tab-count' class='ipsHasNotifications' title='$count'>$count</span></div>
      <script type='text/javascript'>
      document.observe("dom:loaded", function(){
         var _thisHtml	= $('nav_app_addonchat').innerHTML;
         _thisHtml = _thisHtml.replace( /\<\/a\>/ig, '' ) + "&nbsp;&nbsp;" + $('chat-tab-count-wrap').innerHTML + "</a>";
         $('nav_app_addonchat').update( _thisHtml );
         $('chat-tab-count-wrap').remove();
         $('chat-tab-count').show();
      });
      </script>
______EOF;

      return $retval;
	}
}
