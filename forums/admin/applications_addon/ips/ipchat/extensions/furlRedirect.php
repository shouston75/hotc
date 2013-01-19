<?php

/**
 * <pre>
 * Invision Power Services
 * IP.Board v1.2.0
 * RSS output plugin :: posts
 * Last Updated: $Date: 2010-12-17 07:53:02 -0500 (Fri, 17 Dec 2010) $
 * </pre>
 *
 * @author 		$Author: ips_terabyte $
 * @copyright	(c) 2001 - 2009 Invision Power Services, Inc.
 * @license		http://www.invisionpower.com/community/board/license.html
 * @package		IP.Chat
 * @link		http://www.invisionpower.com
 * @since		6/24/2008
 * @version		$Revision: 7443 $
 */

if ( ! defined( 'IN_IPB' ) )
{
	print "<h1>Incorrect access</h1>You cannot access this file directly. If you have recently upgraded, make sure you upgraded all the relevant files.";
	exit();
}

class furlRedirect_ipchat
{	
	/**
	 * Key type: Type of action (topic/forum)
	 *
	 * @var		string
	 */
	private $_type = '';
	
	/**
	 * Key ID
	 *
	 * @var		int
	 */
	private $_id = 0;
	
	/**
	 * Constructor
	 *
	 * @param	object	Registry
	 * @return	void
	 */
	function __construct( ipsRegistry $registry )
	{
		$this->registry =  $registry;
		$this->DB       =  $registry->DB();
		$this->settings =& $registry->fetchSettings();
	}

	/**
	 * Set the key ID
	 * <code>furlRedirect_forums::setKey( 'topic', 12 );</code>
	 *
	 * @param	string	Type
	 * @param	mixed	Value
	 * @return	void
	 */
	public function setKey( $name, $value )
	{
		$this->_type = $name;
		$this->_id   = $value;
	}
	
	/**
	 * Set up the key by URI
	 *
	 * @param	string		URI (example: index.php?showtopic=5&view=getlastpost)
	 * @return	bool
	 */
	public function setKeyByUri( $uri )
	{
		return FALSE;
	}
	
	/**
	 * Return the SEO title
	 *
	 * @return	string
	 */
	public function fetchSeoTitle()
	{
		return 'false';
	}
}