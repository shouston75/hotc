<?php

/**
 * (e32) ibEconomy
 * Public Module: Ajax
 * + Long-term Investment Pop
 */

class public_ibEconomy_ajax_lt extends ipsAjaxCommand 
{
	/**
	 * Class entry point
	 */
	public function doExecute( ipsRegistry $registry ) 
	{
		$this->registry->class_localization->loadLanguageFile( array( 'public_ibEconomy') );
		
		#init
		$lt_id	= intval( $this->request['id'] );

		#can we access?
		if ( !$this->memberData['g_eco'] || !$this->memberData['g_eco_lt'] )
 		{
 			$this->returnString( 'error' );
		}

		#no long-term ID?		
		if( !$lt_id )
		{
			$this->returnString( 'error' );
		}

		#let our invest class do the heavy lifting, grab the long-term and make it perrrty		
		$lt = $this->registry->class_invest->grabAnLT($lt_id);	

		#no long-term by that ID?			
		if( !$lt['lt_id'] )
		{
			$this->returnString( 'error' );
		}
		
		#no permission to view this long-term investment?  damn you permission matrix!		
		if ( $lt['lt_use_perms'] && ! $this->registry->permissions->check( 'view', $lt ) )
		{
			$this->returnString( 'error' );
		}

		#show that sexy ajaxed long-term balloon!
		$this->returnHtml( $this->registry->getClass('output')->getTemplate('ibEconomy2')->showLTCard( $lt ) );
	}
}