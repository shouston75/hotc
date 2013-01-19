<?php
/**
 * <pre>
 * Invision Power Services
 * IP.Board v3.2.2
 * Moderator actions
 * Last Updated: $Date: 2011-05-05 07:03:47 -0400 (Thu, 05 May 2011) $
 * </pre>
 *
 * @author 		$Author: ips_terabyte $
 * @copyright	(c) 2001 - 2009 Invision Power Services, Inc.
 * @license		http://www.invisionpower.com/community/board/license.html
 * @package		IP.Board
 * @subpackage	Forums
 * @link		http://www.invisionpower.com
 * @version		$Revision: 8644 $
 */

if ( ! defined( 'IN_IPB' ) )
{
	print "<h1>Incorrect access</h1>You cannot access this file directly.";
	exit();
}

class digestLibrary
{
	/**
	 * Digest time
	 *
	 * @var		string		daily|weekly
	 */
	public $digest_time		= 'daily';

	/**
	 * Digest type
	 *
	 * @var		string		topic|forum
	 */
	public $digest_type		= 'topic';
	
	/**#@+
	 * Registry Object Shortcuts
	 *
	 * @var		object
	 */
	protected $registry;
	protected $DB;
	protected $settings;
	protected $request;
	protected $lang;
	protected $member;
	protected $memberData;
	protected $cache;
	protected $caches;
	/**#@-*/

	/**
	 * Constructor
	 *
	 * @param	object		Registry reference
	 * @return	@e void
	 */
	public function __construct( ipsRegistry $registry )
	{
		/* Make objects */
		$this->registry = $registry;
		$this->DB	    = $this->registry->DB();
		$this->settings =& $this->registry->fetchSettings();
		$this->request  =& $this->registry->fetchRequest();
		$this->lang	    = $this->registry->getClass('class_localization');
		$this->member   = $this->registry->member();
		$this->memberData =& $this->registry->member()->fetchMemberData();
		$this->cache	= $this->registry->cache();
		$this->caches   =& $this->registry->cache()->fetchCaches();
		
		/* Check for class_forums */
		if( ! $this->registry->isClassLoaded( 'class_forums' ) )
		{
			$classToLoad = IPSLib::loadLibrary( IPSLib::getAppDir( 'forums' ) . "/sources/classes/forums/class_forums.php", 'class_forums', 'forums' );
			$this->registry->setClass( 'class_forums', new $classToLoad( $registry ) );
			$this->registry->strip_invisible = 0;
			$this->registry->class_forums->forumsInit();
		}
		
		/* Load language files */
		ipsRegistry::getClass('class_localization')->loadLanguageFile( array( 'public_emails', 'public_email_content' ), 'core' );
	}
	
	/**
	 * Run the digest
	 * @param	string	Frequency (daily/weekly)
	 * @param	string	Type (topic/forum)
	 * @return	@e void
	 */
	public function runDigest( $frequency='daily', $type='topic' )
	{
		if ( $type == 'topic' )
		{
			$this->_sendTopicDigest( $frequency );
		}
		else
		{
			$this->_sendForumDigest( $frequency );
		}
	}
	
	/**
	 * Run the digest
	 * @param	string	Frequency (daily/weekly)
	 * @return	@e void
	 */
	protected function _sendTopicDigest( $frequency )
	{
		require_once( IPS_ROOT_PATH . 'sources/classes/like/composite.php' );/*noLibHook*/
		$_like = classes_like::bootstrap( 'forums','topics' );
			
		$_like->sendDigestNotifications( ( $frequency == 'daily' ) ? 'daily' : 'weekly' );	
	}
	
	/**
	 * Run the digest
	 *
	 * @return	@e void
	 */
	protected function _sendForumDigest( $frequency )
	{ 
		require_once( IPS_ROOT_PATH . 'sources/classes/like/composite.php' );/*noLibHook*/
		$_like = classes_like::bootstrap( 'forums', 'forums' );
			
		$_like->sendDigestNotifications( ( $frequency == 'daily' ) ? 'daily' : 'weekly' );
	}
}