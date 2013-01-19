<?php
/*  --------------------------------------------------------
    jipbPlugin
    (c) 2004-2008 BBpixel.com
	--------------------------------------------------------
    jipbSync: Action event
    $revision: 080513100

    Written by Koudanshi
	--------------------------------------------------------
*/


error_reporting  (E_ERROR | E_WARNING | E_PARSE);
set_magic_quotes_runtime(0);

//get config needed
define ('QUERY_LIMIT', 300);//number of query processon each thread
define ('CMS_VERSION', '1.5.x');
define ('BB_VERSION', '2.3.x');

// Define language
define ('CMS_NAME', 'Joomla');
define ('BB_NAME', 	'IP.Board');
define ('PRODUCT_FULL_NAME', 	'jipbPlugin');
define ('PRODUCT_SHORT_NAME', 	'jipbPlugin');

require_once ("../../conf_global.php");
require_once ("../../sources/ipsclass.php");
require_once ("../../ips_kernel/class_converge.php");
require_once ("../classes/class_template.php");
require_once ("../classes/class_db_mysql.php");
require_once ("class_core.php");
require_once ("../jipb_fconfig.php");
require_once ($jRootPath."/configuration.php");

$jData = new JConfig();
$jipbConfigs = array();

$jipbConfigs['dbhost'] 	 = $jData->host;
$jipbConfigs['dbport'] 	 = "3306";
$jipbConfigs['dbname'] 	 = $jData->db;
$jipbConfigs['dbuser'] 	 = $jData->user;
$jipbConfigs['dbpass'] 	 = $jData->password;
$jipbConfigs['dbprefix'] = $jData->dbprefix;
$jipbConfigs['rootpath'] = $jRootPath;

//init classes
$ipsclass 	= new ipsclass();
$tplPixel 	= new tplPixel();
$dbPixel 	= new dbPixel();

//Parse incomping params
$ipsclass->parse_incoming();

$action 	= $ipsclass->input['action'];
$ipConverge = new class_converge($dbPixel);
$corePixel 	= new corePixel();

switch ($action) {
	case 'increase':
		$corePixel->viewIncrease();
		break;
	case 'doIncrease':
		$corePixel->doIncrease();
		break;
	case 'syncBB':
		$corePixel->viewSyncBB();
		break;
	case 'doSyncBB':
		$corePixel->doSyncBB();
		break;
	case 'syncJ':
		$corePixel->viewSyncJ();
		break;
	case 'doSyncJ':
		$corePixel->doSyncJ();
		break;
	case 'doRebuild':
		$corePixel->doRebuild();
		break;
	case 'final':
		$corePixel->viewFinal();
		break;
	default:
		$corePixel->viewIntro();
		break;
}

?>