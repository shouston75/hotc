<?php
/**
 * <pre>
 * Invision Power Services
 * IP.Board v3.4.1
 * Twitter plug in for share links library.
 * This is just the basic fallback twitter share, the front end has JS to do something more fancy
 *
 * Created by Matt Mecham
 * Last Updated: $Date: 2012-09-19 10:57:30 -0400 (Wed, 19 Sep 2012) $
 * </pre>
 *
 * @author 		$Author: mmecham $
 * @copyright	(c) 2001 - 2009 Invision Power Services, Inc.
 * @license		http://www.invisionpower.com/company/standards.php#license
 * @package		IP.Board
 * @link		http://www.invisionpower.com
 * @version		$Rev: 11350 $
 *
 */

/* Class name must be in the format of:
   sl_{key}
   Where {key}, place with the value of: core_share_links.share_key
 */
class sl_twitter
{
	/**#@+
	* Registry Object Shortcuts
	*
	* @access	protected
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
	 * Construct.
	 * @access	public
	 * @param	object		Registry
	 * @return	@e void
	 */
	public function __construct( $registry )
	{
		/* Make object */
		$this->registry   =  $registry;
		$this->DB         =  $this->registry->DB();
		$this->settings   =& $this->registry->fetchSettings();
		$this->request    =& $this->registry->fetchRequest();
		$this->lang       =  $this->registry->getClass('class_localization');
		$this->member     =  $this->registry->member();
		$this->memberData =& $this->registry->member()->fetchMemberData();
		$this->cache      =  $this->registry->cache();
		$this->caches     =& $this->registry->cache()->fetchCaches();
	}
	
	/**
	 * Requires a permission check
	 *
	 * @access	public
	 * @param	array		Data array
	 * @return	boolean
	 */
	public function requiresPermissionCheck( $array )
	{
		return false;
	}
	
	/**
	 * Redirect to Twitter
	 * Exciting, isn't it.
	 *
	 * @access	private
	 * @param	string		Plug in
	 */
	public function share( $title, $url )
	{
		$title   = IPSText::convertCharsets( $title, IPS_DOC_CHAR_SET, 'utf-8' );
		$hashMan = $this->settings['twitter_hashtag'];
		
		if ( $hashMan && substr( $hashMan, 0, 1 ) != '#' )
		{
			$hashMan = urlencode('#' . $hashMan);
		}
		
		$hashMan = ( $hashMan ) ? '%20' . $hashMan : '';
		
		$url     = "http://twitter.com/intent/tweet?status=" . urlencode( $title ) . '%20-%20' . urlencode( $url ) . $hashMan;
		
		$this->registry->output->silentRedirect( $url );
	}
}