<?php

/**
 * Invision Power Services
 * IP.Board v3.0.5
 * Profile Plugin Library
 * Last Updated: $Date: 2009-11-11 11:00:08 -0500 (Wed, 11 Nov 2009) $
 *
 * @author 		$Author: josh $
 * @copyright	(c) 2001 - 2009 Invision Power Services, Inc.
 * @license		http://www.invisionpower.com/community/board/license.html
 * @package		Invision Power Board
 * @subpackage	IP.Gallery
 * @link		http://www.invisionpower.com
 * @version		$Rev: 5351 $
 *
 */

if ( ! defined( 'IN_IPB' ) )
{
	print "<h1>Incorrect access</h1>You cannot access this file directly. If you have recently upgraded, make sure you upgraded 'admin.php'.";
	exit();
}

class profile_gallery extends profile_plugin_parent
{
	/**
	 * return HTML block
	 *
	 * @access	public
	 * @param	array		Member information
	 * @return	string		HTML block
	 */
	public function return_html_block( $member=array() ) 
	{
		/* Make sure we have gallery */
		if( ! $this->DB->checkForField( 'id', 'gallery_images' ) )
		{
			return $this->lang->words['err_no_posts_to_show'];
		}
		
		/* Can we use gallery? */
		if( ! $this->memberData['g_gallery_use'] )
		{
			return $this->lang->words['err_no_posts_to_show'];
		}
		
		/* Paths */
		define( 'GALLERY_PATH', IPS_ROOT_PATH . '/applications_addon/ips/gallery/' );
		define( 'GALLERY_LIBS', IPS_ROOT_PATH . '/applications_addon/ips/gallery/sources/libs/' );
		
		/* Load Language */
		$this->lang->loadLanguageFile( array( 'public_gallery' ), 'gallery' );

		/* Get gallery library and API */
		require_once( IPS_ROOT_PATH . 'api/api_core.php' );
		require_once( IPS_ROOT_PATH . 'api/gallery/api_gallery.php' );
		
		/* Gallery Object */
		require_once( GALLERY_LIBS . 'lib_gallery.php' );
		$this->registry->setClass( 'glib', new lib_gallery( $this->registry ) );
		
		 /* Load the category object */
		require_once( GALLERY_LIBS . 'lib_categories.php' );
		$this->registry->setClass( 'category', new lib_categories( $this->registry ) );
		$this->registry->category->normalInit();

		/* Create API Object */
		$gal_api 			= new apiGallery;
		$gal_api->glib 		= $this->registry->glib;
		$gal_api->category	= $this->registry->category;

		/* Get images */
		$images = $gal_api->return_gallery_data( $member['member_id'], 6, 0 );
		/* Get Image Library */
		require( GALLERY_LIBS . 'lib_imagelisting.php' );
		$img_list = new lib_imagelisting( $this->registry );
		
		/* Pass some values from API */
		$img_list->res 			= $gal_api->res;
		$img_list->total_images = $gal_api->total;

		$output	= '';
		
		$output .= $img_list->getHtmlListing( array(
													'can_rate'		=> ( $this->settings['gallery_use_rate'] AND $this->memberData['g_rate'] ) ? 1 : 0,
											)	);	
		
		if( $output == '' )
		{
			$output = $this->registry->getClass('output')->getTemplate('profile')->tabNoContent( 'none_found' );
		}
		else
		{
			$output = $this->registry->getClass('output')->getTemplate('gallery_global')->profileWrapper( $member, $output );
		}

		return $output;
	}
	
}