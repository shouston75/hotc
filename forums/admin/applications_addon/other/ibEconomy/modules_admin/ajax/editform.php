<?php

/**
 * (e32) ibEconomy
 * Admin Module: Ajax
 * + Add a custom image pop
 */

class admin_ibEconomy_ajax_editform extends ipsAjaxCommand 
{
	/**
	 * Class entry point
	 */
	public function doExecute( ipsRegistry $registry ) 
	{
		$this->registry->class_localization->loadLanguageFile( array( 'admin_ibEconomy') );
		
    	switch( $this->request['do'] )
    	{
			default:
			case 'show':
				$this->show();
			break;
			case 'deleteImage':
				$this->deleteImage();
			break;			
    	}
	}
	
	/**
	 * Show the form
	 */
	protected function deleteImage()
	{
		#init
		$name		= 'inline_form_delete_image';
		$item_id	= intval( $this->request['item_id'] );
		$item_type	= trim($this->request['item_type']);
		$output		= '';
		
		#load skin
		$html = $this->registry->output->loadTemplate('cp_skin_ibEconomy');
				
		#got item?
		if ( ! $item_id )
		{
			$this->returnJsonError( $this->lang->words['please_create_item_before_deleting_image'] );
		}
		
		#get form
		if ( method_exists( $html, $name ) )
		{
			$output = $html->$name( $item_id, $item_type );
		}

		#return it
		$this->returnHtml( $output );		
	}
	/**
	 * Show the form
	 */
	protected function show()
	{
		#init
		$name		= trim( IPSText::alphanumericalClean( $this->request['name'] ) );
		$item_id	= intval( $this->request['item_id'] );
		$item_type	= trim($this->request['item_type']);
		$output		= '';
		
		#load skin
		$html = $this->registry->output->loadTemplate('cp_skin_ibEconomy');
				
		#got item?
		if ( ! $item_id )
		{
			$this->returnJsonError( $this->lang->words['please_create_item_before_uploading_image'] );
		}
		
		#get form
		if ( method_exists( $html, $name ) )
		{
			$output = $html->$name( $item_id, $item_type );
		}

		#return it
		$this->returnHtml( $output );
	}	
}