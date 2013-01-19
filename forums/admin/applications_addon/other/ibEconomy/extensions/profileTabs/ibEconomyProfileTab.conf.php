<?php
/**
 * (e32) ibEconomy
 * Profile Tab
 */

if ( ! defined( 'IN_IPB' ) )
{
	print "<h1>Incorrect access</h1>You cannot access this file directly. If you have recently upgraded, make sure you upgraded all the relevant files.";
	exit();
}

/**
* Plug in name (Default tab name)
*/
$CONFIG['plugin_name']        = ipsRegistry::$settings['eco_general_name'];

/**
* Language string for the tab
*/
$CONFIG['plugin_lang_bit']    = '';

/**
* Plug in key (must be the same as the main {file}.php name
*/
$CONFIG['plugin_key']         = 'ibEconomyProfileTab';

/**
* Show tab?
*/
$CONFIG['plugin_enabled']     = intval( ipsRegistry::$settings['eco_display_profile_tab_on'] );

/**
* Order
*/
$CONFIG['plugin_order'] = 5;