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
* PLUG IN: Default GD image
* @package		IPDYNAMIC
* @author		Matt Mecham
* @copyright	Invision Power Services, Inc.
* @version		1.0
*/

/*
 * This is a PHP library that handles calling reCAPTCHA.
 *    - Documentation and latest version
 *          http://recaptcha.net/plugins/php/
 *    - Get a reCAPTCHA API Key
 *          http://recaptcha.net/api/getkey
 *    - Discussion group
 *          http://groups.google.com/group/recaptcha
 *
 * Copyright (c) 2007 reCAPTCHA -- http://recaptcha.net
 * AUTHORS:
 *   Mike Crawford
 *   Ben Maurer
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */


/**
 * The reCAPTCHA server URL's
 */
define("RECAPTCHA_API_SERVER", "http://api.recaptcha.net");
define("RECAPTCHA_API_SECURE_SERVER", "https://api-secure.recaptcha.net");
define("RECAPTCHA_VERIFY_SERVER", "api-verify.recaptcha.net");

class captchaPlugin
{
	/**
	* Global IPS class array
	* @var	object
	*/
	var $ipsclass;

	
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
	function captchaPlugin( $ipsclass )
	{
		$this->ipsclass =& $ipsclass;
		
		$this->public_key	= $this->ipsclass->vars['recaptcha_public_key'];
		$this->private_key	= $this->ipsclass->vars['recaptcha_private_key'];
		$this->useSSL		= false; //$this->ipsclass->vars['logins_over_https'];	- IPB3 feature...shhhh ;) :P
	}
	
	/*-------------------------------------------------------------------------*/
	// Initializes captcha session
	/*-------------------------------------------------------------------------*/
	/**
	* Initializes captcha session
	* Clears out "dead" entries, adds new row and returns
	* unique ID for use in an image
	*
	* @return string	HTML
	*/
	function getTemplate()
	{
		if ( ! $this->public_key )
		{
			return '';
		}
	
		if ($this->useSSL) 
		{
			$server = RECAPTCHA_API_SECURE_SERVER;
		} 
		else 
		{
			$server = RECAPTCHA_API_SERVER;
		}
		
		$html	= '';
		
		if( $this->ipsclass->vars['recaptcha_language'] )
		{
			$html	.= "<script type='text/javascript'>var RecaptchaOptions = { lang : '{$this->ipsclass->vars['recaptcha_language']}' };</script>";
		}

		$html .=  '<script type="text/javascript" src="'. $server . '/challenge?k=' . $this->public_key . '"></script>
					<noscript>
					<iframe src="'. $server . '/noscript?k=' . $this->public_key . '" height="300" width="500" frameborder="0"></iframe><br/>
					<textarea name="recaptcha_challenge_field" rows="3" cols="40"></textarea>
					<input type="hidden" name="recaptcha_response_field" value="manual_challenge"/>
					</noscript>';
														
		//-----------------------------------------
		// Return Template Bit
		//-----------------------------------------
		
		return $this->ipsclass->compiled_templates['skin_global']->captchaRecaptcha( $html );
	}
	
	/*-------------------------------------------------------------------------*/
	// Captcha validate
	/*-------------------------------------------------------------------------*/
	
	/**
	* Show the captcha bot image 
	*
	* @return	void
	* @since	1.0
	*/
	function validate()
	{
		if ( !$this->private_key )
		{
			return '';
		}
		
		$captcha_unique_id	= $_REQUEST['recaptcha_challenge_field'];
		$captcha_input		= $_REQUEST['recaptcha_response_field'];

		if ( $captcha_input == null || strlen($captcha_input) == 0 || $captcha_unique_id == null || strlen($captcha_unique_id) == 0) 
		{
			return false;
		}
		
		$response = $this->_recaptchaPost( RECAPTCHA_VERIFY_SERVER, "/verify",
																			array (
																				'privatekey'	=> $this->private_key,
																				'remoteip'		=> $this->ipsclass->ip_address,
																				'challenge'		=> $captcha_unique_id,
																				'response'		=> $captcha_input
																			)
												);
		
		$answers	= explode( "\n", $response [1] );

		if ( trim ($answers [0]) == 'true' ) 
		{
			return TRUE;
		}
		else
		{
			return FALSE;
		}
	}
	
	/**
	 * Gets a URL where the user can sign up for reCAPTCHA. If your application
	 * has a configuration page where you enter a key, you should provide a link
	 * using this function.
	 *
	 * @access	public
	 * @param	string	$domain		The domain where the page is hosted
	 * @param	string	$appname	The name of your application
	 */
	function getSignupUrl( $domain = null, $appname = null ) 
	{
		return "http://recaptcha.net/api/getkey?" .  $this->_encodeQueryString( array( 'domain' => $domain, 'app' => $appname ) );
	}
	
	/**
	 * Submits an HTTP POST to a reCAPTCHA server
	 *
	 * @access	private
	 * @param	string 		$host
	 * @param	string		$path
	 * @param	array 		$data
	 * @param	int 		port
	 * @return	array 		response
	 */
	function _recaptchaPost( $host, $path, $data, $port = 80 )
	{
		$req = $this->_encodeQueryString( $data );
		
		$http_request  = "POST $path HTTP/1.0\r\n";
		$http_request .= "Host: $host\r\n";
		$http_request .= "Content-Type: application/x-www-form-urlencoded;\r\n";
		$http_request .= "Content-Length: " . strlen($req) . "\r\n";
		$http_request .= "User-Agent: reCAPTCHA/PHP\r\n";
		$http_request .= "\r\n";
		$http_request .= $req;
		
		$response = '';
		
		if( false == ( $fs = @fsockopen($host, $port, $errno, $errstr, 10) ) ) 
		{
			return false;
		}
		
		fwrite($fs, $http_request);
		
		while ( !feof($fs) )
		{
			$response .= fgets($fs, 1160); // One TCP-IP packet
		}

		fclose($fs);

		$response = explode( "\r\n\r\n", $response, 2 );

		return $response;
	}
	
	/**
	 * Encode array of data into a query string
	 *
	 * @access	private
	 * @param	array 		$data
	 * @return	string 		query string
	 */
	function _encodeQueryString( $data )
	{
		$req = "";
		
		foreach ( $data as $key => $value )
		{
			$req .= $key . '=' . urlencode( stripslashes($value) ) . '&';
		}

		// Cut the last '&'
		$req = substr( $req, 0, strlen($req)-1 );

		return $req;
    }
	
}

?>