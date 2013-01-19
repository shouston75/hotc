<?php
/*
+--------------------------------------------------------------------------
|   Portal 1.0.1
|   =============================================
|   by Michael John
|   Copyright 2011 DevFuse
|   http://www.devfuse.com
+--------------------------------------------------------------------------
|   Based on IP.Board Portal by Invision Power Services
|   Website - http://www.invisionpower.com/
+--------------------------------------------------------------------------
*/

if ( ! defined( 'IN_IPB' ) )
{
	print "<h1>Incorrect access</h1>You cannot access this file directly. If you have recently upgraded, make sure you upgraded 'admin.php'.";
	exit();
}

class ppi_portal extends public_portal_portal_portal 
{
	/**
	 * Initialize module
	 *
	 * @return	void
	 */
	public function init()
 	{
 	}
 	
	/**
	 * Show the site navgiational block
	 *
	 * @return	string		HTML content to replace tag with
	 */
	public function portal_sitenav()
	{
 		if ( ! $this->settings['portal_show_nav'] )
 		{
 			return;
 		}
 		
 		$links		= array();
 		$raw_nav	= $this->settings['portal_nav'];
 		
 		foreach( explode( "\n", $raw_nav ) as $l )
 		{
 			$l = str_replace( "&#039;", "'", $l );
 			$l = str_replace( "&quot;", '"', $l );
 			$l = str_replace( '{board_url}', $this->settings['base_url'], $l );
 			
 			preg_match( "#^(.+?)\[(.+?)\]$#is", trim($l), $matches );
 			
 			$matches[1] = trim($matches[1]);
 			$matches[2] = trim($matches[2]);
 			
 			if ( $matches[1] and $matches[2] )
 			{
	 			$matches[1] = str_replace( '&', '&amp;', str_replace( '&amp;', '&', $matches[1] ) );
	 			
	 			$links[] = $matches;
 			}
 		}
 		
 		if( !count($links) )
 		{
 			return;
 		}

 		return $this->registry->getClass('output')->getTemplate('portal')->siteNavigation( $links );
  	}
  	
	/**
	 * Show the affiliates block
	 *
	 * @return	string		HTML content to replace tag with
	 */
	public function portal_affiliates()
	{
 		if ( ! $this->settings['portal_show_fav'] )
 		{
 			return;
 		}
 		
		$this->settings['portal_fav'] = str_replace( "&#039;", "'", $this->settings['portal_fav'] );
		$this->settings['portal_fav'] = str_replace( "&quot;", '"', $this->settings['portal_fav'] );
		$this->settings['portal_fav'] = str_replace( '{board_url}', $this->settings['base_url'], $this->settings['portal_fav'] );
 		
 		return $this->registry->getClass('output')->getTemplate('portal')->affiliates();
 	}
}