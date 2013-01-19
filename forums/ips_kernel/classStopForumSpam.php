<?php

/**
 * <pre>
 * Invision Power Services
 * IP.Board v3.2.2
 * Wrapper for interfacing with stopforumspam.com
 * Class written by Matt Mecham
 * Last Updated: $Date: 2011-05-05 07:03:47 -0400 (Thu, 05 May 2011) $
 * </pre>
 *
 * @author 		$Author: ips_terabyte $
 * @copyright	(c) 2001 - 2009 Invision Power Services, Inc.
 * @license		http://www.invisionpower.com/community/board/license.html
 * @package		IP.Board
 * @subpackage	Kernel
 * @link		http://www.invisionpower.com
 * @since		Tuesday 22nd February 2005 (16:55)
 * @version		$Revision: 8644 $
 */
 
if ( ! defined( 'IPS_KERNEL_PATH' ) )
{
	/**
	 * Define classes path
	 */
	define( 'IPS_KERNEL_PATH', dirname(__FILE__) );
}

class classStopForumSpam
{
	/**
	 * Minimum frequency to check for
	 *
	 * @var		int
	 */
	protected $_minF = 3;
	
	/**
	 * XML object
	 *
	 * @var		object
	 */
	protected $_xml = null;
	
	/**
	 * ClassFileManagement object
	 *
	 * @var		object
	 */
	protected $_cfm = null;
	
	/**
	 * Load XML and classFileManagement libraries
	 *
	 * @return	@e void
	 */
	public function __construct()
	{
		/* CFM */
		require_once( IPS_KERNEL_PATH . 'classFileManagement.php' );/*noLibHook*/
		$this->_cfm = new classFileManagement();
		
		/* XML */
		require_once( IPS_KERNEL_PATH . 'classXML.php' );/*noLibHook*/
		$this->_xml = new classXML('utf-8');
	}
	
	/**
	 * Set a frequency
	 *
	 * @param	int
	 * @return 	void
	 */
	public function setFrequency( $f )
	{
		$this->_minF = $f;
	}
	
	/**
	 * Check to see if IP address is blacklisted
	 *
	 * @param	string		IP Address
	 * @return	boolean		TRUE = blacklisted, FALSE = clean
	 */
	public function checkForIpAddress( $ip )
	{
		$result = $this->_cfm->getFileContents( "http://www.stopforumspam.com/api?ip=" . $ip );

		/* We're only interested in this, currently */
		if ( $result )
		{
			$this->_xml->loadXML( $result );
			
			foreach( $this->_xml->fetchElements( 'response' ) as $_el )
			{
				$data = $this->_xml->fetchElementsFromRecord( $_el );
			
				if ( $data['appears'] AND intval( $data['frequency'] ) > $this->_minF )
				{
					return TRUE;
				}
			}
		}
		
		return FALSE;
	}
	
	/**
	 * Check to see if email address is blacklisted
	 *
	 * @param	string		Email address
	 * @return	boolean		TRUE = blacklisted, FALSE = clean
	 */
	public function checkForEmailAddress( $email )
	{
		$result = $this->_cfm->getFileContents( "http://www.stopforumspam.com/api?email=" . $email );

		/* We're only interested in this, currently */
		if ( $result )
		{
			$this->_xml->loadXML( $result );
			
			foreach( $this->_xml->fetchElements( 'response' ) as $_el )
			{
				$data = $this->_xml->fetchElementsFromRecord( $_el );
			
				if ( $data['appears'] AND intval( $data['frequency'] ) > $this->_minF )
				{
					return TRUE;
				}
			}
		}
		
		return FALSE;
	}
	
}