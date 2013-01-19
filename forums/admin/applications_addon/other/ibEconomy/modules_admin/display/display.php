<?php

/**
 * (e32) ibEconomy
 * Admin Module: Display
 * @ ACP
 * + Sidebar Blocks
 * + Various Display Settings
 */

if ( ! defined( 'IN_ACP' ) )
{
	print "<h1>Incorrect access</h1>You cannot access this file directly. If you have recently upgraded, make sure you upgraded 'admin.php'.";
	exit();
}

class admin_ibEconomy_display_display extends ipsCommand
{
	public $html;
	public $registry;
	
	public $form_code;
	public $form_code_js;
	
	/**
	 * Main execution method
	 */	
	public function doExecute( ipsRegistry $registry )
	{
		#load template
		$this->html         	= $this->registry->output->loadTemplate( 'cp_skin_ibEconomy');

		#load langs
		ipsRegistry::getClass('class_localization')->loadLanguageFile( array( 'admin_ibEconomy' ) );
		ipsRegistry::getClass('class_localization')->loadLanguageFile( array( 'public_ibEconomy' ) );
		ipsRegistry::getClass('class_localization')->loadLanguageFile( array( 'admin_tools' ), 'core' );
		ipsRegistry::getClass('class_localization')->loadLanguageFile( array( 'admin_forums' ), 'forums' );		
		
		#do form codes
		$this->form_code 		= $this->html->form_code    = 'module=display&amp;section=display';
		$this->form_code_js 	= $this->html->form_code_js = 'module=display&section=display';
		
		#saved message
		if( $this->request['saved'] == 1 )
		{
			$this->registry->output->global_message = $this->lang->words['s_updated'];
		}	
		
		#switcharoo
		switch( $this->request['do'] )
		{
			//******Reorder******//
			case 'reorder':
				$this->registry->getClass('class_permissions')->checkPermissionAutoMsg( 'ibEconomy_blocks_edit' );
				$this->_reorder();
			break;
			
			//******Delete******//
			case 'delete':
				$this->registry->getClass('class_permissions')->checkPermissionAutoMsg( 'ibEconomy_blocks_delete' );
				$this->delete();
			break;
		
			//******Settings******//		
			case 'display_settings':
				$this->registry->getClass('class_permissions')->checkPermissionAutoMsg( 'ibEconomy_block_settings_edit' );			
				$this->registry->class_ibEco_CP->doSettings( $this->request['do'], $this->form_code );
			break;
			
			//******Shop Categories******//
			case 'block':
				$this->registry->getClass('class_permissions')->checkPermissionAutoMsg( 'ibEconomy_blocks_edit' );
				$this->blockForm();
			break;
			case 'do_block':
				$this->registry->getClass('class_permissions')->checkPermissionAutoMsg( 'ibEconomy_blocks_edit' );
				$this->doBlock();
			break;
			case 'blocks':
			default:			
				$this->registry->getClass('class_permissions')->checkPermissionAutoMsg( 'ibEconomy_blocks_view' );
				$this->listBlocks();
			break;			
		}
		
		#footer
		$this->registry->output->html .= $this->html->footer();		
		
		#output
		$this->registry->output->html_main .= $this->registry->output->global_template->global_frame_wrapper();
		$this->registry->output->sendOutput();
	}

	/**
	 * Reorder items
	 */
	public function _reorder()
	{
		#init
		require_once( IPS_KERNEL_PATH . 'classAjax.php' );
		$ajax			= new classAjax();
		
		#checks
		if( $this->registry->adminFunctions->checkSecurityKey( $this->request['md5check'], true ) === false )
		{
			$ajax->returnString( $this->lang->words['postform_badmd5'] );
			exit();
		}
		
 		#Save new position
 		$position	= 1;
 		
 		if( is_array($this->request['blocks']) AND count($this->request['blocks']) )
 		{
 			foreach( $this->request['blocks'] as $this_id )
 			{
 				$this->DB->update( 'eco_sidebar_blocks', array( 'sb_position' => $position ), 'sb_id=' . $this_id );
 				
 				$position++;
 			}
 		}
 		
		#rebuild da cache
		$this->cache->rebuildCache('ibEco_blocks','ibEconomy');
		
		#a ok
 		$ajax->returnString( 'OK' );
 		exit();
	}
	
	/**
	 * Add/Edit Block Form
	 */	
	public function blockForm()
	{
		#init
		$content = array();
		
		if ( $this->request['type'] == 'edit' )
		{
			if ( ! $this->request['sb_id'] )
			{
				$this->registry->output->showError( str_replace('<%TYPE%>', $this->lang->words['cat'], $this->lang->words['error_no_id']) );			
			}
			
			#get bank
			$this->DB->build( array( 'select'	=> 'esb.*',
									 'from'		=> array('eco_sidebar_blocks' => 'esb'),
									 'where'	=> 'esb.sb_id='.$this->request['sb_id'],
												   'add_join' => array(
																		array(
																				'select' => 'p.*',
																				'from'   => array( 'permission_index' => 'p' ),
																				'where'  => "p.app = 'ibEconomy' AND p.perm_type='block' AND perm_type_id=esb.sb_id",
																				'type'   => 'left',
																			 ) 
														               )
							)		);
			$this->DB->execute();
			
			if ( !$this->DB->getTotalRows() )
			{
				$this->registry->output->showError( str_replace('<%TYPE%>', $this->lang->words['sidebar_blocks'], $this->lang->words['error_no_id']) );	
			}	
			else
			{
				$content = $this->DB->fetch();
			}
		}
		
		#buttons
		$buttonRows = $this->registry->class_ibEco_CP->makeButtonRow( array('tools') );
		
		#get permission matrix
		$matrix_html    = $this->registry->getClass('class_perms')->adminPermMatrix( 'block', $content );	
		
		#output
		$this->registry->output->html .= $this->html->blockForm( $content, $matrix_html, $buttonRows );
	}
	
	/**
	 * Add Edit Block
	 */
	public function doBlock()
	{		
		#do custom context if not raw
		if ( ! $this->request['sb_raw'] )
		{
			#process editor contents first
			IPSText::getTextClass('bbcode')->bypass_badwords	= 1;
			IPSText::getTextClass('bbcode')->parse_smilies		= 1;
			IPSText::getTextClass('bbcode')->parse_html			= 0;
			IPSText::getTextClass('bbcode')->parse_bbcode		= 1;
			IPSText::getTextClass('bbcode')->parse_nl2br    	= 1;
			IPSText::getTextClass('bbcode')->parsing_section 	= 'global';

			if( trim($this->request['sb_custom_content']) == '<br>' OR trim($this->request['sb_custom_content']) == '<br />' )
			{
				$content	= '';
			}
			else
			{
				$content = IPSText::getTextClass('editor')->processRawPost( 'sb_custom_content' );
			}

			$content = str_replace( "&#39;", "'", IPSText::stripslashes( $content ) );

			$content = IPSText::getTextClass('bbcode')->preDbParse( $content );
		}
		else
		{
			$content = $_POST['sb_custom_content'];
		}	
		
		#create data array
		$blk_datas  = array('sb_title'			=> $this->request['sb_title'],
							'sb_item_type'		=> $this->request['sb_item_type'],
							//'sb_display_type'	=> $this->request['sb_display_type'],
							'sb_display_num'	=> $this->request['sb_display_num'],
							'sb_display_order'	=> $this->request['sb_display_order'],
							'sb_show_text'		=> $this->request['sb_show_text'],
							'sb_pic'			=> $this->request['sb_pic'],
							'sb_font_color'		=> $this->request['sb_font_color'],
							'sb_bg_color'		=> $this->request['sb_bg_color'],
							'sb_boxed'			=> $this->request['sb_boxed'],
							'sb_custom'			=> $this->request['sb_custom'],								
							'sb_raw'			=> $this->request['sb_raw'],
							'sb_custom_content'	=> $content,
							'sb_use_perms' 		=> $this->request['sb_use_perms'],
							'sb_on_index' 		=> $this->request['sb_on_index'],
							'sb_on'				=> $this->request['sb_on']
						   );
						   
		#insert or update...		
		if ( ! $this->request['sb_id'] )
		{					  
			$this->DB->insert( 'eco_sidebar_blocks', $blk_datas );
			$new_id = $this->DB->getInsertId();
		
			$this->registry->output->global_message = $this->lang->words['sidebar_block_added'];	
		}
		else
		{						  
			$this->DB->update( 'eco_sidebar_blocks', $blk_datas, 'sb_id='.$this->request['sb_id'] );
			
			$this->registry->output->global_message = str_replace('<%BLOCK_NAME%>', $this->request['sb_title'], $this->lang->words['block_edited']);			
		}
				
		$sb_id = ( $this->request['sb_id'] ) ? $this->request['sb_id'] : $new_id;
		
		#do perms
		$this->registry->getClass('class_perms')->savePermMatrix( $this->request['perms'], $sb_id, 'block' );
		
		#rebuild cache
		$this->cache->rebuildCache('ibEco_blocks','ibEconomy');

		#write log
		$logType = ( ! $this->request['sb_id'] ) ? $this->lang->words['added'] : $this->lang->words['edited'];
		$this->registry->adminFunctions->saveAdminLog( sprintf( $this->lang->words['block_done'], $logType, $this->request['sb_title'] ) );				
		
		#get out o hear
		$this->registry->output->silentRedirectWithMessage( $this->settings['base_url'].$this->html->form_code.'&amp;do=blocks' );
		//$this->registry->output->silentRedirect( $this->settings['base_url'].$this->html->form_code.'&amp;do=blocks' );
		// $this->listBlocks();
	}
	
	/**
	 * List Blocks
	 */	
	public function listBlocks()
	{
		#init
		$content = "";
		
		#get cats
		$this->DB->build( array( 	'select'	=> 'esb.*',
									'from'		=> array( 'eco_sidebar_blocks' => 'esb' ),
									'group'		=> 'esb.sb_id',
									'order'		=> 'esb.sb_position'
							)		);
		$this->DB->execute();
		
		if ( $this->DB->getTotalRows() )
		{
			while ( $row = $this->DB->fetch() )
			{
				#add row
				$content .= $this->html->blockRow( $row );
			}
		}
		
		#buttons
		$buttonRows = $this->registry->class_ibEco_CP->makeButtonRow( array('tools', 'cache') );		
		
		#output
		$this->registry->output->html .= $this->html->blocksOverviewWrapper( $content, $buttonRows );
	}
	
	/**
	 * Delete!
	 */		
	private function delete()
	{
		#no eco items passed?
		if(  $this->request['type'] != 'block' )
		{
			$this->registry->output->showError( $this->lang->words['error_no_type_to_delete'] );
		}
		#no id?
		if( ! $this->request['id'] )
		{
			$this->registry->output->showError( str_replace('<%TYPE%>', $this->request['type'], $this->lang->words['error_no_id']) );
		}
		
		#you had to do all items in this one function?
		$blockName 	= $this->caches['ibEco_blocks'][ $this->request['id'] ][ 'sb_title' ];
		
		#delete it
		$this->DB->delete( 'eco_sidebar_blocks', 'sb_id = ' . $this->request['id'] );
		
		#rebuild da cache
		$this->cache->rebuildCache('ibEco_blocks','ibEconomy');
		
		#message
		$this->registry->output->global_message = str_replace('<%TYPE%>', $this->lang->words['sidebar_block'], $this->lang->words['type_deleted']);
		
		#save log
		$this->registry->adminFunctions->saveAdminLog( sprintf( $this->lang->words['item_deleted'], $this->lang->words['sidebar_block'], $blockName ) );		
		
		#get out o hear
		$this->registry->output->silentRedirectWithMessage( $this->settings['base_url'].$this->html->form_code.'&amp;do=blocks' );	
	}
	
}