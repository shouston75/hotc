<?php

/*
+--------------------------------------------------------------------------
|   Invision Power Board
|   =============================================
|   by Matthew Mecham
|   (c) 2001 - 2006 Invision Power Services, Inc.
|   http://www.invisionpower.com
|   =============================================
|   Web: http://www.invisionboard.com
|   Licence Info: http://www.invisionboard.com/?license
+---------------------------------------------------------------------------
|   > $Date: 2006-09-22 06:28:54 -0400 (Fri, 22 Sep 2006) $
|   > $Revision: 567 $
|   > $Author: matt $
+---------------------------------------------------------------------------
|
|   > IPS Remote Call thingy
|   > Module written by Matt Mecham
|   > Date started: 17th October 2002
|
|	> Module Version Number: 1.0.0
|   > DBA Checked: Tue 25th May 2004
+--------------------------------------------------------------------------
*/


if ( ! defined( 'IN_ACP' ) )
{
	print "<h1>Incorrect access</h1>You cannot access this file directly. If you have recently upgraded, make sure you upgraded 'admin.php'.";
	exit();
}


class ad_ips {

	var $base_url;
	
	var $colours = array();
	
	var $url = "http://www.invisionboard.com/acp/";
	
	var $version = "1.1";

	function auto_run()
	{
		//-----------------------------------------
		
		switch($this->ipsclass->input['code'])
		{
		
			case 'news':
				$this->news();
				break;
				
			case 'updates':
				$this->updates();
				break;
				
			case 'docs':
				$this->docs();
				break;
				
			case 'support':
				$this->support();
				break;
			
			case 'host':
				$this->host();
				break;
				
			case 'purchase':
				$this->purchase();
				break;
				
			//-----------------------------------------
			default:
				exit();
				break;
		}
		
	}
	


	
	function news()
	{
		@header("Location: ".$this->url."?news");
		exit();
	}
	
	function updates()
	{
		//@header("Location: ".$this->url."?updates&version=".$this->version);
		@header("Location: ".$this->url."?updates");
		exit();
	}
	
	function docs()
	{
		@header("Location: http://www.invisionpower.com/documentation/showdoc.php");
		exit();
	}
	
	function support()
	{
		@header("Location: ".$this->url."?support");
		exit();
	}
	
	function host()
	{
		@header("Location: ".$this->url."?host");
		exit();
	}
	
	function purchase()
	{
		@header("Location: ".$this->url."?purchase");
		exit();
	}
}
?>