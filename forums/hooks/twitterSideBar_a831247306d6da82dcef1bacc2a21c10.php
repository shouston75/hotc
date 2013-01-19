<?php

/**
 *	(IM) Twitter Sidebar
 *
 * @author 		m4rtin
 * @copyright	2008 - 2009 Invision Modding
 * @web: 		http://www.invisionmodding.com
 * @IPB ver.:	IP.Board 3.0
 * @version:	0.9.8 (9009)
 *
 */

require_once IPS_KERNEL_PATH . 'oAuth/twitterAuth.php';

class twitterSideBar
{
	public $registry;
	public $twitterTroubles = 0;
	private $twitterAuth;
	private static $instance;
	public static $css;
	
	/**
	 * Contructor
	 *
	 * @access	public
	 * @param	object	ipsRegistry
	 * @return	void
	 */
	public function __construct()
	{
		$this->registry 	=  ipsRegistry::instance();
		$this->DB       	=  $this->registry->DB();
		$this->settings 	=& $this->registry->fetchSettings();
		$this->request  	=& $this->registry->fetchRequest();
		$this->lang     	=  $this->registry->getClass('class_localization');
		$this->member   	=  $this->registry->member();
		$this->memberData 	=& $this->registry->member()->fetchMemberData();
		$this->cache    	=  $this->registry->cache();
		$this->caches   	=& $this->registry->cache()->fetchCaches();
		
		// Load language file
		$this->registry->getClass( 'class_localization' )->loadLanguageFile( array( 'public_main' ), 'twitterBar' );
	}
	
	static public function instance()
	{
		if ( ! self::$instance )
		{
			self::$instance = new self();
		}

		return self::$instance;
	}
	
	/**
	 * Get the hook output
	 *
	 * @access	public
	 * @return	html		Hook HTML
	 */
	public function getOutput()
	{
		if( ! $this->memberData['member_id'] OR $this->memberData['oauth_state'] == 'hide' )
		{
			return;
		}

		// You'll regret this! You bastard!
		if ( $this->request['oauth_state'] == 'hide' )
		{
			IPSMember::save( $this->memberData['member_id'], array( 'core' => 
																	array( 
																			'oauth_state' => 'hide', 
																			'oauth_request_token'	=> NULL,
																			'oauth_request_token_secret' => NULL,
																			'oauth_access_token' => NULL, 
																			'oauth_access_token_secret'	=> NULL,
																) ) );
			return;
		}
		

		// Set up API keys
		$consumerKey 	= $this->settings['im_twitterApiKey'];
		$consumerSecret = $this->settings['im_twitterApiSecretKey'];
		
		if( ! $consumerKey OR ! $consumerSecret )
		{
			return;
		}
		
		/* No cURL? :[ */
		if ( ! function_exists( 'curl_init' ))
		{
			if ( $this->memberData['g_access_cp'] )
			{
				return "Twitter Sidebar Error: This hook require cURL to function. Your host has disabled that.";
			}
			
			return;
		}
		

		/* Set up placeholder */
		$content = '';
		$authorizeLink = '';

		/* Set state if previous session */
		$state = $this->memberData['oauth_state'];
	
		
		/* If oauth_token is missing get it */
		if ( isset( $this->request['oauth_token'] ) AND !empty( $this->request['oauth_token'] ) AND $state == 'start' ) 
		{
			$state = 'returned';
			
			$this->member->setProperty( 'oauth_state', $state );
			IPSMember::save( $this->memberData['member_id'], array( 'core' => array( 'oauth_state' => $state ) ) );
		}

		//-----------------------------------------
		// Have we authorize the app on twitter?
		//-----------------------------------------
		
		if ( $state != 'returned' ) 
		{
			//Create TwitterOAuth object with app key/secret
			$to = new myTwitterOAuth( $consumerKey, $consumerSecret );
		
			//Request tokens from twitter 
			$tok = $to->getRequestToken( array( 'oauth_callback' => ipsRegistry::$settings['base_url'] ) );
			
			if ( $this->settings['twitterDebug'] )
			{
				IPSDebug::addLogMessage( 'Request tokens', 'twitter', $tok, TRUE );
			}
			
			// Save tokens for later
			$this->member->setProperty( 'oauth_request_token', $tok['oauth_token'] ); 
			$this->member->setProperty( 'oauth_request_token_secret', $tok['oauth_token_secret'] ); 

			IPSMember::save( $this->memberData['member_id'], array( 'core' => 
																	array( 	'oauth_state' 				 => 'start', 
																			'oauth_request_token'		 => $tok['oauth_token'],
																			'oauth_request_token_secret' => $tok['oauth_token_secret'],
																			) ) );

			// Build the authorization URL
			$authorizeLink = $to->getAuthorizeURL( $this->memberData['oauth_request_token'] );
			
			$hideLink = $this->settings['base_url'] . 'oauth_state=hide';
			
			$this->lang->words['authorizeLink'] = sprintf( $this->lang->words['authorizeLink'], $authorizeLink, $hideLink );
			
		}
		else
		{
			// If the access tokens are already set skip to the API call
			if ( empty( $this->memberData['oauth_access_token'] ) OR empty( $this->memberData['oauth_access_token_secret'] ) ) 
			{
				// Create TwitterOAuth object with app key/secret and token key/secret from default phase
				$to = new myTwitterOAuth( $consumerKey, $consumerSecret, $this->memberData['oauth_request_token'], $this->memberData['oauth_request_token_secret']);
				
				// Request access tokens from twitter
				$tok = $to->getAccessToken( array( 'oauth_token' => $this->memberData['oauth_request_token'], 'oauth_token_secret' => $this->memberData['oauth_request_token_secret'] ),
											 array( 'oauth_verifier' => $this->request['oauth_verifier'] ) );

				if ( $this->settings['twitterDebug'] )
				{
					IPSDebug::addLogMessage( 'Access tokens', 'twitter', $tok, TRUE );
				}
				
				// Save the access tokens. Normally these would be saved in a database for future use. 
				$this->member->setProperty( 'oauth_access_token', 		 $tok['oauth_token'] );
				$this->member->setProperty( 'oauth_access_token_secret', $tok['oauth_token_secret'] );
				
				IPSMember::save( $this->memberData['member_id'], array( 'core' => 
																	array( 	'oauth_access_token' 		=> $tok['oauth_token'], 
																			'oauth_access_token_secret'	=> $tok['oauth_token_secret'],
																			) ) );
																			

				
				
				$this->registry->getClass( 'output' )->redirectScreen( $this->lang->words['authorizedRedirect'], $this->settings['board_url'] );
			}
		}

		// Make sure our tokens are real
		OAuthServer::verifyToken();
		
		$rateLimits = $this->getRateLimit();
		return $this->registry->getClass( 'output' )->getTemplate( 'twitterBar' )->mainWrapper( $rateLimits );
	}
	
	
	/**
	 * Calls to Twitter are restricted to 150 requests pr hour
	 * Honor that!
	 * 
	 * @access	public
	 * @return	array
	 */
	public function getRateLimit()
	{
		$to = new myTwitterOAuth( $this->settings['im_twitterApiKey'], $this->settings['im_twitterApiSecretKey'], $this->memberData['oauth_access_token'], $this->memberData['oauth_access_token_secret'] );
		
		$res = $to->oAuthRequest( 'http://twitter.com/account/rate_limit_status.json', array(), 'GET' );
		$limits = json_decode( $res, 1 );
		
		//$limits['reset_time_in_seconds'] = gmdate( 'H:i', $limits['reset_time_in_seconds'] );

		return $limits;
	}
	
	public function recreateOAuthKeys()
	{
		$updateArray = array( 	'oauth_state'				 => NULL,
								'oauth_access_token' 		 => NULL, 
								'oauth_access_token_secret'	 => NULL,
								'oauth_request_token'		 => NULL,
								'oauth_request_token_secret' => NULL,
							);

		foreach( $updateArray as $k => $v )
		{
			$this->DB->force_data_type[ $k ] = NULL;
		}
		
		IPSMember::save( $this->memberData['member_id'], array( 'core' => $updateArray ) );
		
		if( IPS_IS_AJAX == TRUE )
		{
			return json_encode( array( 'error' => 'resetOAuthKeys' ) );
		}
		else
		{
			$this->registry->getClass( 'output' )->redirectScreen( $this->lang->words['recreatedOAuthKeys'], $this->settings['board_url'] );
		}
	}
}

class myTwitterOAuth extends TwitterOAuth 
{
	final public function http( $url, $post_data = NULL )
	{
		IPSDebug::startTimer();
		$_NOW 	= IPSDebug::getMemoryDebugFlag();
		
		$ch = curl_init();
		
		curl_setopt( $ch, CURLOPT_URL, $url );
		curl_setopt( $ch, CURLOPT_CONNECTTIMEOUT, 10 );
		curl_setopt( $ch, CURLOPT_TIMEOUT, 10 );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
		
		//-----------------------------------------
		// Set to 1 to verify Twitter's SSL Cert
		//-----------------------------------------
		
		curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, 0 );
		
		if ( isset( $post_data ) ) 
		{
			curl_setopt( $ch, CURLOPT_POST, 1 );
			curl_setopt( $ch, CURLOPT_POSTFIELDS, $post_data );
		}

		$response = curl_exec( $ch );
		
		$this->http_status 		= curl_getinfo( $ch, CURLINFO_HTTP_CODE );
		$this->last_api_call 	= $url;
		
		curl_close( $ch );
		
		if ( $this->http_status != 200 )
		{
			twitterSideBar::instance()->twitterTroubles = 1;
		}
		
		if ( ipsRegistry::$settings['twitterDebug'] )
		{
			IPSDebug::addLogMessage( "Twitter cURL response", 'twitter', array( 'url' => $url, 'http_status' => $this->http_status, 'response' => $response ), TRUE );
		}
		
		//HTTP 401 == Authorization failed
		if( $this->http_status == 401 )
		{
			$_response = json_decode( $response, 1 );
			$_response = is_array( $_response ) ? $_response : (array)$response;
			
			// This one can be good to save, just in case
			IPSDebug::addLogMessage( "Twitter cURL response error", 'twitter', array( 'response' => $_response ), TRUE );
			
			if ( strstr( 'expired Token', $_response['error'] ) OR strstr( 'Consumer Key', $_response['error'] ) )
			{
				return twitterSideBar::instance()->recreateOAuthKeys();
			}
		}
		
		$_end 	= IPSDebug::endTimer();
		$_usage = IPSDebug::setMemoryDebugFlag( "Requested {$url}", $_NOW );

		$_simpleUrl = $this->getSimpleUrl( $url );
		IPSDebug::fireBug( 'info', array( array( 'url' => $_simpleUrl, 'http_status' => $this->http_status, 'time' => $_end, 'usage' => IPSLib::sizeFormat( $_usage ) ) ) );

		return $response;
	}
	
	public function getSimpleUrl( $url ) 
	{
		$parts = parse_url($url);

		$port 	= @$parts['port'];
		$scheme = $parts['scheme'];
		$host 	= $parts['host'];
		$path 	= @$parts['path'];

		$port = $port ? $port : ($scheme == 'https' ? '443' : '80' );

		if ( ( $scheme == 'https' AND $port != '443' ) OR ( $scheme == 'http' AND $port != '80' ) ) 
		{
		  $host = "$host:$port";
		}
		return "$scheme://$host$path";
  }
}
?>