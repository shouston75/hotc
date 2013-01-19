<?php
/*
+--------------------------------------------------------------------------
|   Portal 1.0.1
|   =============================================
|   by Michael John
|   Copyright 2011 DevFuse
|   http://www.devfuse.com
+--------------------------------------------------------------------------
|   Based on IP.Board Portal by Invision Power Services
|   Website - http://www.invisionpower.com/
+--------------------------------------------------------------------------
*/

if ( ! defined( 'IN_IPB' ) )
{
	print "<h1>Incorrect access</h1>You cannot access this file directly. If you have recently upgraded, make sure you upgraded all the relevant files.";
	exit();
}

/**
* This file must be named {file_name_minus_php}-cfg.php
*
* Please see each variable for more information
* $PORTAL_CONFIG is OK for each file, do not change
* this array name.
*/

$PORTAL_CONFIG = array();

/**
* Main plug in title
*
*/
$PORTAL_CONFIG['pc_title'] = 'IP.Board Online Users';

/**
* Plug in mini description
*
*/
$PORTAL_CONFIG['pc_desc']  = "Shows the current name and number of online users";

/**
* Exportable tags key must be in the naming format of:
* {file_name_minus_php}-tag. The value *MUST* be the function
* which it corresponds to.
*
* @param array[ TAG ] = array( FUNCTION NAME, DESCRIPTION );
*/
$PORTAL_CONFIG['pc_exportable_tags']['online_users_show'] = array( 'online_users_show', 'Shows the online users' );