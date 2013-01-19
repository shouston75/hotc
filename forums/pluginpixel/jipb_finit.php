<?php
/**
 *
 * @package jipbPlugin
 * @version $Id: jipb_finit.php, v 1.0 2008/08/12 15:37:17 koudanshi Exp $
 * @copyright (c) 2003-2008 BBpixel
 *
 * Minimum Requirement: PHP 4.3.3 | MySQL 4.1
 */

//Turn off clearing password

require_once(ROOT_PATH."pluginpixel/classes/class_db_mysql.php");
require_once(ROOT_PATH."/pluginpixel/jipb_fconfig.php");
require_once(ROOT_PATH."/pluginpixel/jipb_fcore.php");
require_once($jRootPath."/configuration.php");

$jData = new JConfig();

//setup db connection for Joomla
$dbPixel = new dbPixel();
$dbPixel->_db['host'] 	= $jData->host;
$dbPixel->_db['name'] 	= $jData->db;
$dbPixel->_db['user'] 	= $jData->user;
$dbPixel->_db['pwd'] 	= $jData->password;
$dbPixel->_db['prefix'] = $jData->dbprefix;
$dbPixel->connect();

$jipbPixel = new jipb_fcore();
$jipbPixel->_jDB = $dbPixel;
$jipbPixel->_regGroupID = $regGroupID;

?>