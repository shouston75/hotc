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

$PORTAL_CONFIG = array();

/**
* Main plug in title
*
*/
$PORTAL_CONFIG['pc_title'] = 'IP.Board Recent Topics';

/**
* Plug in mini description
*
*/
$PORTAL_CONFIG['pc_desc']  = "Shows IP.Board recent topics with topic's first post";

/**
* Exportable tags key must be in the naming format of:
* {file_name_minus_php}-tag. The value *MUST* be the function
* which it corresponds to.
*
* @param array[ TAG ] = array( FUNCTION NAME, DESCRIPTION );
*/
$PORTAL_CONFIG['pc_exportable_tags']['latest_topics_main']    = array( 'latest_topics_main'            , 'Shows the last X topics with full post from the selected forums' );
$PORTAL_CONFIG['pc_exportable_tags']['latest_topics_sidebar'] = array( 'latest_topics_sidebar', 'Shows the last X topic titles from ALL viewable forums in the sidebar'          );