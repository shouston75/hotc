<?php

/**
 * (e32) ibEconomy
 * Public Module: Ajax
 * + Shop Item Pop
 */

class public_ibEconomy_ajax_lottery extends ipsAjaxCommand 
{
	/**
	 * Class entry point
	 */
	public function doExecute( ipsRegistry $registry ) 
	{
		$this->registry->class_localization->loadLanguageFile( array( 'public_ibEconomy') );
		
		#init
		$l_id	= intval( $this->request['id'] );

		#can we access?
		if ( !$this->memberData['g_eco'] || !$this->memberData['g_eco_lottery'] )
 		{
 			$this->returnString( 'error' );
		}

		#no lottery ID?		
		if( !$l_id )
		{
			$this->returnString( 'error' );
		}

		#let our shop class do the heavy lifting, grab the shop item and make it perrrty		
		$lottery = $this->registry->class_cash->grabALotto($l_id);	
		$lottery = $this->registry->ecoclass->formatRow($lottery, 'lottery', true);
		
		#no lottery by that ID?			
		if( !$lottery['l_id'] )
		{
			$this->returnString( 'error' );
		}
		
		#show that sexy ajaxed shop item balloon!
		$this->returnHtml( $this->registry->getClass('output')->getTemplate('ibEconomy2')->showLotteryCard( $lottery ) );
	}
}