<?php

/*
+---------------------------------------------------------------------------
|   Invision Power Dynamic v1.0.0
|   ========================================
|   by Matthew Mecham
|   (c) 2004 Invision Power Services
|   http://www.invisionpower.com
|   ========================================
+---------------------------------------------------------------------------
|   INVISION POWER DYNAMIC IS NOT FREE SOFTWARE!
|   http://www.invisionpower.com/dynamic/
+---------------------------------------------------------------------------
|   > $Id$
|   > $Revision: 128 $
|   > $Date: 2006-01-20 12:46:26 +0000 (Fri, 20 Jan 2006) $
+---------------------------------------------------------------------------
|
|   > CAPTCHA ANTI-SPAM CLASS
|   > Script written by Matt Mecham
|   > Date started: Thursday 2 February (11:08)
|
+---------------------------------------------------------------------------
*/

/**
* CAPTCHA CLASS
* ipsclass dependent method of showing an anti-spam image
*
* This creates a unique MD5 hash and stores it in the DB with
* an 8 character string of 0-9a-z
*
* <code>
* CREATE TABLE captcha (
* 	captcha_unique_id	VARCHAR(32) NOT NULL default '',
* 	captcha_string		VARCHAR(100) NOT NULL default '',
* 	captcha_ipaddress	VARCHAR(16) NOT NULL default '',
* 	captcha_date		INT(10) NOT NULL default '',
* 	PRIMARY KEY (captcha_unique_id )
* );
* </code>
* @package		IPDYNAMIC
* @author		Matt Mecham
* @copyright	Invision Power Services, Inc.
* @version		1.0
*/

class class_captcha
{
	/**
	* Global IPS class array
	* @var	object
	*/
	var $ipsclass;
	
	/**
	* Captcha plug in class
	* @var	string
	*/
	var $_plugInClass = '';
	
	/**
	* Return an error if GD is not installed?
	* My most verbose string name. I'm so proud.
	* @var boolean
	*/
	var $show_error_gd_img = FALSE;
	
	/**
	* Error string
	* @var string
	*/
	var $error_string = '';
	
	/**
	* Constructor bah to PHP 4
	*
	* @param	object		IPSClass object
	* @param	string		Captcha plug-in to use
	*/
	function class_captcha( $ipsclass, $plugin )
	{
		$this->ipsclass =& $ipsclass;
		$plugin         = $this->ipsclass->txt_alphanumerical_clean( $plugin );
		
		if ( ! file_exists( KERNEL_PATH . 'class_captcha_plugin/' . $plugin . '.php' ) )
		{
			$plugin = 'default';
		}
	
		require_once( KERNEL_PATH . 'class_captcha_plugin/' . $plugin . '.php' );
		$this->_plugInClass = new captchaPlugIn( $ipsclass );
	}
	
	/*-------------------------------------------------------------------------*/
	// Initializes captcha session and returns the template bit
	/*-------------------------------------------------------------------------*/
	/**
	* Initializes captcha session
	* Clears out "dead" entries, adds new row and returns
	* template bit
	*
	* @return string	HTML Template bit
	*/
	function getTemplate()
	{
		return $this->_plugInClass->getTemplate();
	}
	
	/*-------------------------------------------------------------------------*/
	// Captcha validate
	/*-------------------------------------------------------------------------*/
	
	/**
	* Show the captcha bot image 
	*
	* @return	boolean
	* @since	1.0
	*/
	function validate()
	{
		return $this->_plugInClass->validate();
	}
	
	/**
	* Fetch Plug In Class Handle
	*
	* If you need it...
	*/
	function fetchPlugInClassHandle()
	{
		return $this->_plugInClass;
	}
}

?>