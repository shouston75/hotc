<?php

/**
 * (e32) ibEconomy
 * Public Module: Ajax
 * + Stock Pop
 */

class public_ibEconomy_ajax_stock extends ipsAjaxCommand 
{
	/**
	 * Class entry point
	 */
	public function doExecute( ipsRegistry $registry ) 
	{
		$this->registry->class_localization->loadLanguageFile( array( 'public_ibEconomy') );
		
		#init
		$stock_id	= intval( $this->request['id'] );
		
		#can we access?
		if ( !$this->memberData['g_eco'] || !$this->memberData['g_eco_stock'] )
 		{
 			$this->returnString( 'error' );
		}

		#no stock ID?		
		if( !$stock_id )
		{
			$this->returnString( 'error' );
		}

		#let our invest class do the heavy lifting, grab the stock and make it perrrty		
		$stock = $this->registry->class_invest->grabAStock($stock_id);	

		#no stock by that ID?			
		if( !$stock['s_id'] )
		{
			$this->returnString( 'error' );
		}
		
		#no permission to view this stock?  damn you permission matrix!		
		if ( $stock['s_use_perms'] && ! $this->registry->permissions->check( 'view', $stock ) )
		{
			$this->returnString( 'error' );
		}

		#show that sexy ajaxed stock balloon!
		$this->returnHtml( $this->registry->getClass('output')->getTemplate('ibEconomy2')->showStockCard( $stock ) );
	}
}