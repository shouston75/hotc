<?php
/**
 * @file		licenses.php		API for using Nexus License Keys
 *
 * $Copyright: $
 * $License: $
 * $Author: mark $
 * $LastChangedDate: 2011-04-13 11:49:36 -0400 (Wed, 13 Apr 2011) $
 * $Revision: 8328 $
 * @since 		30th December 2010
 */

/**
 *
 * @class	licenseKeyApi
 * @brief	API for using Nexus License Keys
 *
 */
class licenseKeyApi
{	
	/**
	 * Constructor
	 *
	 * @return	@e void
	 */
	public function __construct()
	{
		define( 'IPS_ENFORCE_ACCESS', TRUE );
		define( 'IPB_THIS_SCRIPT', 'public' );
		require_once( '../initdata.php' );/*noLibHook*/
		
		require_once( IPS_ROOT_PATH . 'sources/base/ipsRegistry.php' );/*noLibHook*/
		require_once( IPS_ROOT_PATH . 'sources/base/ipsController.php' );/*noLibHook*/
		
		$registry = ipsRegistry::instance();
		$registry->init();
		
		require_once( IPS_KERNEL_PATH . 'classXmlRpc.php' );/*noLibHook*/
		$this->classXmlRpc = new classXmlRpc();
		
		$document = file_get_contents( 'php://input' );
		
		$this->input = $this->classXmlRpc->decodeXmlRpc( $document );
		$this->params = $this->classXmlRpc->getParams( $this->input );
		
		$this->params = $this->params[0];
		
		require_once( IPSLib::getAppDir('nexus') . '/sources/customer.php' );/*noLibHook*/
	}
	
	/**
	 * Load Key
	 *
	 * @return	@e array 		Data
	 *
	 * @throws Exception
	 *  @li BAD_KEY (Key does not exist)
	 *	@li	INACTIVE (Key is inactive or purchase is expired/cancelled)
	 */
	protected function _loadKey( $key )
	{
		//-----------------------------------------
		// Get key
		//-----------------------------------------
				
		$key = ipsRegistry::DB()->addSlashes( $key );
		$key = ipsRegistry::DB()->buildAndFetch( array(
			'select'	=> 'k.*',
			'from'		=> array( 'nexus_licensekeys' => 'k' ),
			'add_join'	=> array( array( 
			  	'select' => 'ps.*',
			  	'from'   => array( 'nexus_purchases' => 'ps' ),
			  	'where'  => "ps.ps_id=k.lkey_purchase",
			  	'type'   => 'left' 
			  ) ),
			'where'		=> "k.lkey_key='{$key}'"
			) );
		if ( !$key['lkey_key'] )
		{
			throw new Exception( 'BAD_KEY', 101 );
		}
		if ( !$key['lkey_active'] )
		{
			throw new Exception( 'INACTIVE', 102 );
		}
		if ( $key['ps_cancelled'] )
		{
			throw new Exception( 'INACTIVE', 103 );
		}
		if ( !$key['ps_active'] )
		{
			throw new Exception( 'INACTIVE', 104 );
		}
		
		$member = customer::load( $key['lkey_member'] )->data;
		$key['customer_id']			= $member['member_id'];
		$key['customer_name']		= $member['_name'];
		$key['customer_email']		= $member['email'];
		$key['customer_username']	= $member['name'];
		
		//-----------------------------------------
		// Sort out identifier
		//-----------------------------------------
		
		if ( $key['lkey_identifier'] )
		{
			switch ( $key['lkey_identifier'] )
			{
				case 'name':
					$key['identifier'] = $key['customer_name'];
					break;
					
				case 'email':
					$key['identifier'] = $key['customer_email'];
					break;
					
				case 'username':
					$key['identifier'] = $key['customer_username'];
					break;
			
				default:
					$cfields = unserialize( $key['ps_custom_fields'] );
					$key['identifier'] = $cfields[ $key['lkey_identifier'] ];
					break;
			}
		}
				
		return $key;
	}
	
	/**
	 * Activate License Key
	 *
	 * @return	@e array	Response
	 */
	public function activate()
	{
		if ( defined( 'NEXUS_LKEY_API_ALLOW_IP_OVERRIDE' ) )
		{
			$ip = $this->params['ip'] ? $this->params['ip'] : $_SERVER['REMOTE_ADDR'];
		}
		else
		{
			$ip = $_SERVER['REMOTE_ADDR'];
		}
		
		$key = $this->_loadKey( $this->params['key'] );
		if ( $key['lkey_max_uses'] != -1 and $key['lkey_uses'] >= $key['lkey_max_uses'] )
		{
			throw new Exception( 'MAX_USES', 201 );
		}
		elseif ( $this->params['setIdentifier'] )
		{
			if ( $key['identifier'] and $key['identifier'] != $this->params['identifier'] )
			{
				throw new Exception( 'ID_ALREADY_SET', 202 );
			}
			else
			{
				$cfields = unserialize( $key['ps_custom_fields'] );
				$cfields[ $key['lkey_identifier'] ] = $this->params['identifier'];
				$cfields = serialize( $cfields );
				ipsRegistry::DB()->update( 'nexus_purchases', array( 'ps_custom_fields' => $cfields ), "ps_id={$key['ps_id']}" );
			}
		}
		elseif ( $key['identifier'] and $this->params['identifier'] != $key['identifier'] )
		{
			throw new Exception( 'BAD_ID', 203 );
		}
		
		$activateData = ( $key['lkey_activate_data'] ) ? unserialize( $key['lkey_activate_data'] ) : array();
		$k = empty( $activateData ) ? 0 : max( array_keys( $activateData ) );
		$k++;
		$activateData[ $k ] = array(
			'activated'		=> time(),
			'ip'			=> $ip,
			'last_checked'	=> 0,
			'extra'			=> $this->params['extra'][0],
			);

		ipsRegistry::DB()->update( 'nexus_licensekeys', array(
			'lkey_uses'				=> $key['lkey_uses'] + 1,
			'lkey_activate_data'	=> serialize( $activateData ),
			), "lkey_key='{$key['lkey_key']}'" );
		
		ipsRegistry::DB()->insert( 'nexus_customer_history', array(
			'log_member'	=> $key['customer_id'],
			'log_by'		=> 0,
			'log_type'		=> 'lkey',
			'log_data'		=> serialize( array( 'type' => 'activated', 'key' => $key['lkey_key'], 'ps_id' => $key['lkey_purchase'] ) ),
			'log_date'		=> time()
			) );
				
		return array( 'RESPONSE' => 'OKAY', 'USAGE_ID' => $k );
	}
	
	/**
	 * Check a license key's status
	 *
	 * @return	@e array	Response
	 */
	public function check()
	{
		if ( defined( 'NEXUS_LKEY_API_ALLOW_IP_OVERRIDE' ) )
		{
			$ip = $this->params['ip'] ? $this->params['ip'] : $_SERVER['REMOTE_ADDR'];
		}
		else
		{
			$ip = $_SERVER['REMOTE_ADDR'];
		}
	
		try
		{
			$key = $this->_loadKey( $this->params['key'] );
		}
		catch ( Exception $e )
		{
			if ( $key['identifier'] and $this->params['identifier'] != $key['identifier'] )
			{
				throw new Exception( 'BAD_ID', 301 );
			}
			
			if ( $key['lkey_activate_data'] )
			{
				$activateData = ( $key['lkey_activate_data'] ) ? unserialize( $key['lkey_activate_data'] ) : array();
				if ( isset( $activateData[ $this->params['usage_id'] ] ) )
				{
					$activateData[ $this->params['usage_id'] ]['last_checked'] = time();
				}
				
				ipsRegistry::DB()->update( 'nexus_licensekeys', array( 'lkey_activate_data' => serialize( $activateData ) ), "lkey_key='{$this->params['key']}'" );
			}
		
			switch ( $e->getCode() )
			{
				case 102:
				case 103:
					return array( 'STATUS' => 'INACTIVE' );
				case 104:
					return array( 'STATUS' => 'EXPIRED' );
					
				default:
					throw $e;
			}
		}
		
		if ( $key['identifier'] and $this->params['identifier'] != $key['identifier'] )
		{
			throw new Exception( 'BAD_ID', 301 );
		}
		if ( !$this->params['usage_id'] )
		{
			throw new Exception( 'NO_USAGE_ID', 302 );
		}
		
		$activateData = ( $key['lkey_activate_data'] ) ? unserialize( $key['lkey_activate_data'] ) : array();
		
		if ( !$activateData[ $this->params['usage_id'] ] )
		{
			throw new Exception( 'BAD_USAGE_ID', 303 );
		}
		if ( $activateData[ $this->params['usage_id'] ]['ip'] != $ip )
		{
			throw new Exception( 'BAD_IP', 304 );
		}
		
		$activateData[ $this->params['usage_id'] ]['last_checked'] = time();
		ipsRegistry::DB()->update( 'nexus_licensekeys', array( 'lkey_activate_data' => serialize( $activateData ) ), "lkey_key='{$key['lkey_key']}'" );
		
		return array( 'STATUS' => 'ACTIVE', 'USES' => $key['lkey_uses'], 'MAX_USES' => $key['lkey_max_uses'] );

	}
	
	/**
	 * Get information about a license key
	 *
	 * @return	@e array	Response
	 */
	public function info()
	{
		/* Init */
		$key = $this->_loadKey( $this->params['key'] );
		if ( $key['identifier'] and $this->params['identifier'] != $key['identifier'] )
		{
			throw new Exception( 'BAD_ID', 401 );
		}
		
		$cfields = ipsRegistry::cache()->getCache('package_fields');
		
		/* Get Children */
		$children = array();
		ipsRegistry::DB()->build( array( 
			'select'	=> 'p.*',
			'from'		=> array( 'nexus_purchases' => 'p'),
			'add_join'	=> array( array(
				'select'	=> 'l.*',
				'from'		=> array( 'nexus_licensekeys' => 'l' ),
				'where'		=> "l.lkey_purchase=p.ps_id"
				) ),
			'where'		=> "p.ps_parent='{$key['ps_id']}'"
			) );
		ipsRegistry::DB()->execute();
		while ( $row = ipsRegistry::DB()->fetch() )
		{
			$fields = array();
			if ( $row['ps_app'] == 'nexus' and $row['ps_type'] == 'package' )
			{
				$_cfields = unserialize( $row['ps_custom_fields'] );
				foreach ( $cfields[ $key['ps_item_id'] ] as $field )
				{
					$fields[ $field['cf_name'] ] = $_cfields[ $field['cf_id'] ];
				}
			}
		
			$children[ $row['ps_id'] ] = serialize( array(
				'id'		=> $row['ps_id'],
				'name'		=> $row['ps_name'],
				'app'		=> $row['ps_app'],
				'type'		=> $row['ps_type'],
				'item_id'	=> $row['ps_item_id'],
				'active'	=> $row['ps_cancelled'] ? 0 : $row['ps_active'],
				'start'		=> $row['ps_start'],
				'expire'	=> $row['ps_expire'],
				'options'	=> $fields,
				'lkey'		=> $row['lkey_key'],
				) );
		}
				
		/* Sort out custom fields */
		$fields = array();
		$_cfields = unserialize( $key['ps_custom_fields'] );
		if ( $cfields[ $key['ps_item_id'] ] )
		{
			foreach ( $cfields[ $key['ps_item_id'] ] as $field )
			{
				$fields[ $field['cf_name'] ] = $_cfields[ $field['cf_id'] ];
			}
		}

		/* Return */
		return array(
			'key'				=> $key['lkey_key'],
			'identifier'		=> $key['identifier'],
			'generated'			=> $key['lkey_generated'],
			'expires'			=> $key['ps_expire'],
			'usage_data'		=> $key['lkey_activate_data'],
			'purchase_id'		=> $key['ps_id'],
			'purchase_name'		=> $key['ps_name'],
			'purchase_pkg'		=> $key['ps_item_id'],
			'purchase_active'	=> $key['ps_cancelled'] ? 0 : $key['ps_active'],
			'purchase_start'	=> $key['ps_start'],
			'purchase_expire'	=> $key['ps_expire'],
			'purchase_options'	=> serialize( $fields ),
			'purchase_children'	=> $children,
			'customer_name'		=> $key['customer_name'],
			'customer_email'	=> $key['customer_email'],
			'uses'				=> $key['lkey_uses'],
			'max_uses'			=> $key['lkey_max_uses']
			);
	}
	
	/**
	 * Update Extra
	 *
	 * @return	@e array	Response
	 */
	public function updateExtra()
	{
		/* Init */
		$key = $this->_loadKey( $this->params['key'] );
		if ( $key['identifier'] and $this->params['identifier'] != $key['identifier'] )
		{
			throw new Exception( 'BAD_ID', 501 );
		}
		
		/* Get our usage ID */
		$activateData = ( $key['lkey_activate_data'] ) ? unserialize( $key['lkey_activate_data'] ) : array();
		
		if ( !$activateData[ $this->params['usage_id'] ] )
		{
			throw new Exception( 'BAD_USAGE_ID', 502 );
		}
		if ( $activateData[ $this->params['usage_id'] ]['ip'] != $_SERVER['REMOTE_ADDR'] )
		{
			throw new Exception( 'BAD_IP', 503 );
		}
		
		/* Update */
		$activateData[ $this->params['usage_id'] ]['extra'] = $this->params['extra'][0];
		ipsRegistry::DB()->update( 'nexus_licensekeys', array( 'lkey_activate_data' => serialize( $activateData ) ), "lkey_key='{$key['lkey_key']}'" );
		
		/* Return */
		return array( 'RESPONSE' => 'OKAY' );
	}
}

if ( defined( 'NEXUS_LKEY_API_DISABLE' ) )
{
	exit;
}

$api = new licenseKeyApi();

$method = $api->classXmlRpc->getMethodName( $api->input );

if ( method_exists( $api, $method ) )
{
	try
	{
		$response = @$api->$method();
		
		echo $api->classXmlRpc->buildDocument( $response );
	}
	catch ( Exception $e )
	{
		$api->classXmlRpc->returnError( $e->getCode(), $e->getMessage() );
	}
}
else
{
	$api->classXmlRpc->returnError( 0, 'BAD_METHOD' );
}