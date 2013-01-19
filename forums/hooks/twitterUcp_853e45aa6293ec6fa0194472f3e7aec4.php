<?php

/**
 *	(IM) Twitter Sidebar
 *
 * @author 		m4rtin
 * @copyright	2008 - 2009 Invision Modding
 * @web: 		http://www.invisionmodding.com
 * @IPB ver.:	IP.Board 3.0
 * @version:	0.9.8  (9009)
 *
 */


class twitterUcp
{
	/**
	 * Get the hook output
	 *
	 * @access	public
	 * @return	html		Hook HTML
	 */
	public function getOutput()
	{
		// Load language file
		ipsRegistry::instance()->getClass( 'class_localization' )->loadLanguageFile( array( 'public_main' ), 'twitterBar' );
		
		return ipsRegistry::instance()->getClass( 'output' )->getTemplate( 'twitterBar' )->ucpSetting();
	}
}
?>