<?php
/**
 * <pre>
 * Invision Power Services
 * IP.Board v3.1.3
 * Retrieve member warn log
 * Last Updated: $Date: 2010-10-21 07:08:38 -0400 (Thu, 21 Oct 2010) $
 * </pre>
 *
 * @author 		$Author: ips_terabyte $
 * @copyright	(c) 2001 - 2009 Invision Power Services, Inc.
 * @license		http://www.invisionpower.com/community/board/license.html
 * @package		IP.Board
 * @subpackage	Members
 * @link		http://www.invisionpower.com
 * @since		Tuesday 1st March 2005 (11:52)
 * @version		$Revision: 7007 $
 */

if ( ! defined( 'IN_IPB' ) )
{
	print "<h1>Incorrect access</h1>You cannot access this file directly. If you have recently upgraded, make sure you upgraded all the relevant files.";
	exit();
}

class public_members_ajax_warn extends ipsAjaxCommand 
{
	/**
	 * Class entry point
	 *
	 * @param	object		Registry reference
	 * @return	void		[Outputs to screen]
	 */
	public function doExecute( ipsRegistry $registry ) 
	{
		ipsRegistry::getClass( 'class_localization')->loadLanguageFile( array( 'public_mod' ), 'forums' );

		if ( ! $this->settings['warn_on'] )
		{
			$this->returnJsonError( $this->lang->words['ajax_no_warn'] );
		}
		
		switch( $this->request['do'] )
		{
			case 'view':
				$this->_viewWarnLogs();
			break;
		}
	}

	/**
	 * Retieve warn logs and return them
	 *
	 * @return	void
	 */
 	private function _viewWarnLogs()
 	{
 		$classToLoad = IPSLib::loadActionOverloader( IPSLib::getAppDir('members') . '/modules_public/warn/warn.php', 'public_members_warn_warn' );
 		$warn        = new $classToLoad();
		$warn->makeRegistryShortcuts( $this->registry );
		$warn->loadData();
		
		$warnHTML	= $warn->viewLog();
		
		if ( !$warnHTML )
		{
			$this->returnJsonError( $this->lang->words['ajax_bad_html'] );
		}
		else
		{
			$this->returnHtml( $this->registry->getClass('output')->getTemplate('mod')->warnLogsAjaxWrapper( $warnHTML ) );
		}
	}
}