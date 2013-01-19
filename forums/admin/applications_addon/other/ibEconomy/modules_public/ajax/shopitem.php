<?php

/**
 * (e32) ibEconomy
 * Public Module: Ajax
 * + Shop Item Pop
 */

class public_ibEconomy_ajax_shopitem extends ipsAjaxCommand 
{
	/**
	 * Class entry point
	 */
	public function doExecute( ipsRegistry $registry ) 
	{
		$this->registry->class_localization->loadLanguageFile( array( 'public_ibEconomy') );
		
		switch( $this->request['do'] )
		{
			default:
			case 'something':
				$this->shopitem_pop();
			break;
			
			case 'preview':
				$this->_itemPreview();
			break;
		}
	}
	
	/**
	 * View shop item popup
	 *
	 * @return	@e void
	 */
	protected function shopitem_pop()
	{	
			#init
		$si_id	= intval( $this->request['id'] );

		#can we access?
		if ( !$this->memberData['g_eco'] || !$this->memberData['g_eco_shopitem'] )
 		{
 			$this->returnString( 'error' );
		}

		#no shop item ID?		
		if( !$si_id )
		{
			$this->returnString( 'error' );
		}

		#let our shop class do the heavy lifting, grab the shop item and make it perrrty		
		$shopItem = $this->registry->class_shop->grabAShopItem($si_id);	

		#no shop item by that ID?			
		if( !$shopItem['si_id'] )
		{
			$this->returnString( 'error' );
		}
		
		#no permission to view this shop item?  damn you permission matrix!		
		if ( $shopItem['si_use_perms'] && ! $this->registry->permissions->check( 'view', $shopItem ) )
		{
			$this->returnString( 'error' );
		}

		#show that sexy ajaxed shop item balloon!
		$this->returnHtml( $this->registry->getClass('output')->getTemplate('ibEconomy2')->showShopItemCard( $shopItem ) );
	}
	
	/**
	 * Displays an item preview
	 *
	 * @return	@e void
	 */
	protected function _itemPreview()
	{
		#init
		$item_id		= intval( $this->request['itemid'] );
		$item_type		= trim( $this->request['itemtype'] );
		$bank_type		= trim( $this->request['banktype'] );

		$extraHtml = $this->registry->ecoclass->grabMoreInfoHtmlForItem($item_id, $item_type, $bank_type);
		
		return $this->returnHtml( $extraHtml );
	}
}