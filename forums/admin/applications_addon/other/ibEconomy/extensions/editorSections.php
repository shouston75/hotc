<?php

/**
 * (e32) ibEconomy
 * Core Sections
 * @ Global
 * + What is this for again?
 */

if ( ! defined( 'IN_ACP' ) )
{
	print "<h1>Incorrect access</h1>You cannot access this file directly. If you have recently upgraded, make sure you upgraded 'admin.php'.";
	exit();
}

/*
 * An array of key => value pairs
 * When going to parse, the key should be passed to the editor
 *  to determine which bbcodes should be parsed in the section
 *
 */
ipsRegistry::getClass('class_localization')->loadLanguageFile( array( 'admin_ibEconomy' ), 'ibEconomy' );

$BBCODE	= array( 'ibEconomy' => ipsRegistry::getClass('class_localization')->words['ctype__ibEconomy'] );