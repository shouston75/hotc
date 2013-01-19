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
|   > $Revision: 140 $
|   > $Date: 2006-02-01 12:07:32 +0000 (Wed, 01 Feb 2006) $
+---------------------------------------------------------------------------
|
|   > Captcha action class
|   > Script written by Matt Mecham
|   > Date started: Thursday 2 February (11:53)
|
+---------------------------------------------------------------------------
*/

class captcha
{
	# Global
	var $ipsclass;
	
	/**
	* Captcha class object
	* @var object
	*/
	var $captcha;
	
	/*-------------------------------------------------------------------------*/
	// Run!
	/*-------------------------------------------------------------------------*/
	
    function auto_run()
    {
		//-----------------------------------------
		// Load captcha class
		//-----------------------------------------
		
		require_once( KERNEL_PATH . 'class_captcha.php' );
		$this->captcha           =  new class_captcha( $this->ipsclass, 'default' );
	
		//-----------------------------------------
		// What to do...
		//-----------------------------------------
		
		switch( $this->ipsclass->input['do'] )
		{
			default:
			case 'showImage':
				$this->show_image();
				break;
		}
	}
	
	/*-------------------------------------------------------------------------*/
	// Show the captcha image
	/*-------------------------------------------------------------------------*/
	/**
	* Show the captcha image
	* Shows the captcha image. Good god, that was a waste of time
	*
	* @return NOTHING
	*/
	function show_image()
	{
		//-----------------------------------------
		// INIT
		//-----------------------------------------
		
		$captcha_unique_id = trim( $this->ipsclass->input['regid'] );
		
		//-----------------------------------------
		// Show image...
		//-----------------------------------------
		
		$plugIn = $this->captcha->fetchPlugInClassHandle();
		$plugIn->captcha_show_image( $captcha_unique_id );
	}
	
	
}

?>