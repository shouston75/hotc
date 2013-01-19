<?php

define('IPB_THIS_SCRIPT', 'public');
if ( file_exists( './initdata.php' ) )
	require_once( './initdata.php' );
else
	require_once( '../initdata.php' );
define('IPS_ENFORCE_ACCESS', TRUE);
require_once( IPS_ROOT_PATH . 'sources/base/ipsRegistry.php' );
$ipbRegistry = ipsRegistry::instance();
$ipbRegistry->init();

header("Content-Type: text/plain");
print "scras.version = 2.1\n";

/* Is RAS Enabled? */
if(ipsRegistry::$settings['addonchat_ras_enabled']==0) {
   print "ipb.auth.msg = Remote Authentication Disabled\n";
   die;
}

/* GET Parameters */
$username  = IPSText::parseCleanValue(urldecode(trim($_GET['username'])));
$password  = IPSText::parseCleanValue(urldecode(trim($_GET['password'])));
print "ipb.auth.un = $username\n";
print "ipb.auth.pw = $password\n";

/* Find User */
ipsRegistry::DB()->build( array(	'select' 	=> 'm.*',
									'from'		=> array( 'members' => 'm' ),
									'where'		=> "m.members_l_display_name='".ipsRegistry::DB()->addSlashes(strtolower($username))."'",
									'limit'		=> array( 0, 1 ),
									'add_join'	=> array( array( 'select' => 'g.g_access_cp',
																 'from'   => array( 'groups' => 'g' ),
																 'where'  => 'g.g_id=m.member_group_id' ) )
							)		);
ipsRegistry::DB()->execute();

/* Member does not exist */
if ( (!$member = ipsRegistry::DB()->fetch()) || (!$member['member_id']) )
{
   /* Guest Access? */
   if(ipsRegistry::$settings['addonchat_ras_guest']==1) {
      print "user.usergroup.id = 0\n";
      print "user.usergroup.can_login = 1\n";
      print "ipb.auth.msg = Guest access\n";
      exit;
   }
   else {
      print "user.usergroup.can_login = 0\n";
      print "ipb.auth.msg = User not found\n";
      die;
   }
}

$md5_password = md5( md5( $member['members_pass_salt'] ) . md5($password) );

/* Valid password */
if( ($md5_password == $member['members_pass_hash']) || ($password == $member['members_pass_hash']) )
{
   print "user.uid = " . $member['member_id'] . "\n";
}

/* Invalid password */
else
{
   print "user.usergroup.can_login = 0\n";
   print "ipb.auth.msg = Invalid password\n";
   die;
}

/* Load Member Groups */
$group_ids[] = $member['member_group_id'];
$group_ids_other = explode(",", $member['mgroup_others']);
foreach($group_ids_other as $mgroup_id_other) {
   if(!in_array($mgroup_id_other, $group_ids) && is_numeric($mgroup_id_other) )
      $group_ids[] = $mgroup_id_other;
}

/* Load Group Permissions */
ipsRegistry::DB()->build( array(	'select' 	=> 'g.*',
									'from'		=> array( 'groups' => 'g' ),
									'where'		=> "g.g_id IN (" . implode(",", $group_ids) . ")"));
ipsRegistry::DB()->execute();

/* Merge Group Permissions */
$mgroup = 0;
while($group = ipsRegistry::DB()->fetch())
{
   if($mgroup === 0) {
      $mgroup = $group;
   }
   else
   {
      foreach($group as $gpn => $gpv) {
         if($gpv > $mgroup[$gpn])
            $mgroup[$gpn] = $gpv;
      }
   }
}

/* No groups found */
if($mgroup === 0) {
   print "user.usergroup.can_login = 0\n";
   print "ipb.auth.msg = No member groups\n";
   die;
}

print "user.usergroup.id = 0\n";

/* Check if user group is banned */
if($mgroup['addonchat_banned'] > 0)
{
   print "user.usergroup.can_login = 0\n";
   print "ipb.auth.msg = Group Banned\n";
   die;
}

/* Check if user is banned */
if($member['addonchat_banned'] > 0)
{
   print "user.usergroup.can_login = 0\n";
   print "ipb.auth.msg = User Banned\n";
   die;
}

/* Update Who's Chatting System */
if(ipsRegistry::$settings['addonchat_wc_enable']==1) {
	if( ($mgroup['addonchat_can_login'] > 0) && (!$mgroup['addonchat_login_cloaked']) )
   {
   	if($member['member_id'] > 0) 
   	{
   		ipsRegistry::DB()->delete('addonchat_who', 'uid=' . intval($member['member_id']));
   	  	
	      ipsRegistry::DB()->insert('addonchat_who',
	         array(
	            'username' => $username,
	            'hidden' => 0,
	            'uid' => $member['member_id'],
	            'admin' => $mgroup['addonchat_is_admin']));
   	}
   }
}

/* Output Privileges */
foreach($mgroup as $gn => $gv) {
   if(strpos($gn, "addonchat_")===0)
   {
      print "user.usergroup." . substr($gn, 10) . " = $gv\n";
   }
}

/* IPB Profile Link */
if(ipsRegistry::$settings['addonchat_ipbprofile']==1) {
   echo "url.remote.user.0 = \"Profile\", \"" . ipsRegistry::$settings['board_url'] . "/acfunc.php\", \"_blank\", \"false\"\n";
}

/* Avatar & Photo Integration */
if( (ipsRegistry::$settings['addonchat_ipbavatar']==1) || (ipsRegistry::$settings['addonchat_ipbphoto']==1) ) {
	$template = "<table border=0 cellpadding=0 cellspacing=3><tr><td valign=top align=left><img src='" . ipsRegistry::$settings['board_url'] . "/acavatar.php?uid=\$uid&name=\$username_url' height='48'/></td><td align=left valign=top>\$time \$username:<br>\$message</td></tr></table>";
	echo "chatpane.format.public.avatar = $template\n";
	echo "chatpane.format.action.avatar = $template\n";
	echo "chatpane.format.private.avatar = $template\n";
	echo "chatpane.format.recompile = true\n";
}

print "ipb.auth.msg = Success\n";
exit();

?>
