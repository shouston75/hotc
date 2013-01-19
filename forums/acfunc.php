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

/* Is RAS Enabled? */
if(ipsRegistry::$settings['addonchat_ras_enabled']==0) {
   print "Remote Authentication Disabled\n";
   die;
}

/*
 * Parameters:
 * http://support.addoninteractive.com/index.php?action=kb&article=44
 * username : Target user name of function
 * srcusername : Source User Name (Name of user who initiated call to user function)
 * uid : Target User ID
 * srcuid : Source User ID (ID of user who initiated call to user function)
 * roomname : Name of room that the source (initiating) user is currently in
 */

/*echo "<pre>" . $_SERVER["SERVER_NAME"] . $_SERVER["REQUEST_URI"] . "\n";
echo print_r($_GET, true) . "\n";
echo print_r($_REQUEST, true) . "\n";
die;*/

$url = "index.php?showuser=" . intval($_GET['uid']);
header("location: $url");
exit;

?>
