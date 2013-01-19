<?php

/**
 * (e32) ibEconomy
 * Public Module: Ajax
 * + Bank Pop
 */

class public_ibEconomy_ajax_bank extends ipsAjaxCommand 
{
	/**
	 * Class entry point
	 */
	public function doExecute( ipsRegistry $registry ) 
	{
		$this->registry->class_localization->loadLanguageFile( array( 'public_ibEconomy') );
		
		#init
		$bank_id	= intval( $this->request['id'] );
		$type		= $this->request['type'];

		#can we access?
		if ( !$this->memberData['g_eco'] || !$this->memberData['g_eco_bank'] )
 		{
 			$this->returnString( 'error' );
		}
		
		#no bank ID?
		if( !$bank_id )
		{
			$this->returnString( 'error' );
		}
		
		#no bank type?
		if( !$type )
		{
			$this->returnString( 'error' );
		}
		
		#let our invest class do the heavy lifting, grab the bank and make it perrrty
		$bank = $this->registry->class_invest->grabABank($bank_id,$type);	

		#no bank by that ID?		
		if( !$bank['b_id'] )
		{
			$this->returnString( 'error' );
		}
		
		#no permission to view this bank?  damn you permission matrix!
		if ( $bank['b_use_perms'] && ! $this->registry->permissions->check( 'view', $bank ) )
		{
			$this->returnString( 'error' );
		}
		
		#show that sexy ajaxed bank balloon!
		$this->returnHtml( $this->registry->getClass('output')->getTemplate('ibEconomy2')->showBankCard( $bank ) );
	}
}