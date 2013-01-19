<?php

/**
 * (e32) ibEconomy
 * Public Module: Ajax
 * + Credit-Card Pop
 */

class public_ibEconomy_ajax_cc extends ipsAjaxCommand 
{
	/**
	 * Class entry point
	 */
	public function doExecute( ipsRegistry $registry ) 
	{
		$this->registry->class_localization->loadLanguageFile( array( 'public_ibEconomy') );
		
		#init
		$cc_id	= intval( $this->request['id'] );

		#can we access?
		if ( !$this->memberData['g_eco'] || !$this->memberData['g_eco_cc'] )
 		{
 			$this->returnString( 'error' );
		}

		#no credit-card ID?		
		if( !$cc_id )
		{
			$this->returnString( 'error' );
		}

		#let our invest class do the heavy lifting, grab the credit-card and make it perrrty		
		$cc = $this->registry->class_invest->grabACC($cc_id);	

		#no credit-card by that ID?			
		if( !$cc['cc_id'] )
		{
			$this->returnString( 'error' );
		}
		
		#no permission to view this credit-card?  damn you permission matrix!		
		if ( $cc['cc_use_perms'] && ! $this->registry->permissions->check( 'view', $cc ) )
		{
			$this->returnString( 'error' );
		}

		#show that sexy ajaxed credit-card balloon!
		$this->returnHtml( $this->registry->getClass('output')->getTemplate('ibEconomy2')->showCCCard( $cc ) );
	}
}