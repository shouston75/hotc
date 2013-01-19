<?php

/*
+--------------------------------------------------------------------------
|   Invision Power Board
|   =============================================
|   by Matthew Mecham
|   (c) 2001 - 2006 Invision Power Services, Inc.
|   http://www.invisionpower.com
|   =============================================
|   Web: http://www.invisionboard.com
|   Licence Info: http://www.invisionboard.com/?license
+---------------------------------------------------------------------------
|   > $Date: 2006-08-01 17:02:55 +0100 (Tue, 01 Aug 2006) $
|   > $Revision: 425 $
|   > $Author: matt $
+---------------------------------------------------------------------------
|
|   > Personal Profile Portal Class: Posts
|   > Module written by Matt Mecham
|   > Date started: 2nd August 2006
|
+--------------------------------------------------------------------------
*/

/**
* Main content
*
*/

if ( ! defined( 'IN_IPB' ) )
{
	print "<h1>Incorrect access</h1>You cannot access this file directly. If you have recently upgraded, make sure you upgraded 'admin.php'.";
	exit();
}

class profile_aboutme
{
	/**
	* Global IPSCLASS
	* @var	object
	*/
	var $ipsclass;
	
	/*-------------------------------------------------------------------------*/
	// Return data
	/*-------------------------------------------------------------------------*/
	
	/**
	* Returns a block of HTML back to the ajax handler
	* which then replaces the inline content with the HTML
	* returned.
	*
	*/
	function return_html_block( $member=array() ) 
	{
		//-----------------------------------------
		// Skin set loaded?
		//-----------------------------------------
		
		if ( ! is_object( $this->ipsclass->compiled_templates['skin_profile'] ) )
		{
			$this->ipsclass->load_template( 'skin_profile' );
		}

		//-----------------------------------------
		// Got a member?
		//-----------------------------------------
		
		if ( ! is_array( $member ) OR ! count( $member ) )
		{
			return $this->ipsclass->compiled_templates['skin_profile']->personal_portal_no_content( 'err_no_aboutme_to_show' );
		}
		
		if( $member['signature'] )
		{
			$member['signature'] = $this->ipsclass->compiled_templates['skin_global']->signature_separator( $member['signature'] );
		}
		
		if( !$member['signature'] AND !$member['pp_about_me'] )
		{
			return $this->ipsclass->compiled_templates['skin_profile']->personal_portal_no_aboutme( 'err_no_aboutme_to_show', $member['id'] );
		}
		
		$content = $this->ipsclass->compiled_templates['skin_profile']->personal_portal_aboutme( $member );
		
		//-----------------------------------------
		// Macros...
		//-----------------------------------------
		
		if ( ! is_array( $this->ipsclass->skin['_macros'] ) OR ! count( $this->ipsclass->skin['_macros'] ) )
    	{
    		$this->ipsclass->skin['_macros'] = unserialize( stripslashes($this->ipsclass->skin['_macro']) );
    	}
		
		if ( is_array( $this->ipsclass->skin['_macros'] ) )
      	{
			foreach( $this->ipsclass->skin['_macros'] as $row )
			{
				if ( $row['macro_value'] != "" )
				{
					$content = str_replace( "<{".$row['macro_value']."}>", $row['macro_replace'], $content );
				}
			}
		}
		
		$content = str_replace( "<#IMG_DIR#>", $this->ipsclass->skin['_imagedir'], $content );
		$content = str_replace( "<#EMO_DIR#>", $this->ipsclass->skin['_emodir']  , $content );
		
		//-----------------------------------------
		// Return content..
		//-----------------------------------------
		
		return $content ? $content : $this->ipsclass->compiled_templates['skin_profile']->personal_portal_no_aboutme( 'err_no_aboutme_to_show', $member['id'] );
	}
	
}


?>