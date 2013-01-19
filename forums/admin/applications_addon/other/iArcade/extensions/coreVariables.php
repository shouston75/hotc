<?php
if ( $_REQUEST['act'] == 'arcade' )
{
	$_RESET['app'] == 'iArcade';
}

if ( $_REQUEST['autocom'] == 'arcade' )
{
	$_RESET['app'] = 'iArcade';
}

if( ! isset( $_REQUEST['module'] ) && ( isset( $_REQUEST['app'] ) && $_REQUEST['app'] == 'iArcade' ) )
{
	$_RESET['module'] = 'arcade';
}