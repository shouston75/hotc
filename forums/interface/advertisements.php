<?php
/**
 * @file		advertisements.php		API for retreiving advertisement code
 *
 * $Copyright: $
 * $License: $
 * $Author: mark $
 * $LastChangedDate: 2011-04-06 04:34:47 -0400 (Wed, 06 Apr 2011) $
 * $Revision: 8267 $
 * @since 		16th December 2010
 */

define( 'IPS_ENFORCE_ACCESS', TRUE );
define( 'IPB_THIS_SCRIPT', 'public' );
require_once( '../initdata.php' );/*noLibHook*/

require_once( IPS_ROOT_PATH . 'sources/base/ipsRegistry.php' );/*noLibHook*/
require_once( IPS_ROOT_PATH . 'sources/base/ipsController.php' );/*noLibHook*/

$registry = ipsRegistry::instance();
$registry->init();


$key = array_keys( $_GET );
$key = array_pop( $key );

if ( is_numeric( $key ) )
{
	echo @$registry->getClass('IPSAdCode')->getAdById( $key );
}
else
{
	echo @$registry->getClass('IPSAdCode')->getAdCode( $key );
}