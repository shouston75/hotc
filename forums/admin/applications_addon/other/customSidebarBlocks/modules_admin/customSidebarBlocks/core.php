<?php
/**
 * (e$30) Custom Sidebar Blocks
 * version: 1.1.1
 */

if ( ! defined( 'IN_ACP' ) )
{
	print "<h1>Incorrect access</h1>You cannot access this file directly. If you have recently upgraded, make sure you upgraded 'admin.php'.";
	exit();
}

class admin_customSidebarBlocks_customSidebarBlocks_core extends ipsCommand
{
	public $html;
	public $registry;
	
	public $form_code;
	public $form_code_js;
	
	public $permissions;
	
	/**
	 * Main execution method
	 */	
	public function doExecute( ipsRegistry $registry )
	{
		$this->html         	= $this->registry->output->loadTemplate( 'cp_skin_e_CSB');
		
		$this->form_code 		= $this->html->form_code    = 'module=customSidebarBlocks&amp;section=core';
		$this->form_code_js 	= $this->html->form_code_js = 'module=customSidebarBlocks&section=core';
	
		ipsRegistry::getClass('class_localization')->loadLanguageFile( array( 'admin_customSidebarBlocks' ) );
		ipsRegistry::getClass('class_localization')->loadLanguageFile( array( 'admin_tools' ), 'core' );
		
        require_once( IPS_ROOT_PATH . 'sources/classes/class_public_permissions.php' );
        $this->permissions = new classPublicPermissions( ipsRegistry::instance() );		
		
		switch( $this->request['do'] )
		{
			//******Add Block******//
			case 'block_form':
				$this->registry->getClass('class_permissions')->checkPermissionAutoMsg( 'customSideBarBlocks_add' );
				$this->block_form();
			break;
			case 'add_block':
				$this->registry->getClass('class_permissions')->checkPermissionAutoMsg( 'customSideBarBlocks_add' );
				$this->add_block();
			break;
			//******Reorder Blocks******//		
			case 'reorder':
				$this->registry->getClass('class_permissions')->checkPermissionAutoMsg( 'customSideBarBlocks_add' );
				$this->_reorder();
			break;
			//******Recache Blocks******//		
			case 'recache':
				$this->registry->getClass('class_permissions')->checkPermissionAutoMsg( 'customSideBarBlocks_add' );
				$this->rebuildBlockCache();
			break;
			//******Delete Blocks******//		
			case 'delete':
				$this->registry->getClass('class_permissions')->checkPermissionAutoMsg( 'customSideBarBlocks_delete' );
				$this->delete();
			break;
			//******Settings******//		
			case 'settings':
				$this->registry->getClass('class_permissions')->checkPermissionAutoMsg( 'customSideBarBlocks_settings' );
				$this->showSettings();
			break;
			//******View Blocks******//		
			case 'blocks':
			default:
				$this->registry->getClass('class_permissions')->checkPermissionAutoMsg( 'customSideBarBlocks_view' );
				$this->blocks();
			break;
		}
		
		$this->registry->output->html_main .= $this->registry->output->global_template->global_frame_wrapper();
		$this->registry->output->sendOutput();
	}

	/**
	 * Reorder blocks
	 */
	private function _reorder()
	{
		#init
		require_once( IPS_KERNEL_PATH . 'classAjax.php' );
		$ajax = new classAjax();

		#checks
		if( $this->registry->adminFunctions->checkSecurityKey( $this->request['md5check'], true ) === false )
		{
			$ajax->returnString( $this->lang->words['postform_badmd5'] );
			exit();
		}
		
 		#Save new position
 		$position = 1;
 		
 		if( is_array($this->request['blocks']) AND count($this->request['blocks']) )
 		{
 			foreach( $this->request['blocks'] as $this_id )
 			{
 				$this->DB->update( 'custom_sidebar_blocks', array( 'csb_position' => $position ), 'csb_id=' . $this_id );
 				
 				$position++;
 			}
 		}
 		
		#rebuild da cache
		$this->cache->rebuildCache('custom_sidebar_blocks','customSidebarBlocks');
		
		#a ok
 		$ajax->returnString( 'OK' );
 		exit();
	}
	
	/**
	 * Add/Edit Block Form
	 */	
	private function block_form()
	{
		#init
		$content = array();
		
		if ( $this->request['type'] == 'edit' )
		{
			if ( ! $this->request['csb_id'] )
			{
				$this->registry->output->showError( $this->lang->words['error_no_id'] );		
			}
			
			#grab block from cache
			if ( is_array( $this->caches['custom_sidebar_blocks'][ $this->request['csb_id'] ] ) )
			{
				 $content = $this->caches['custom_sidebar_blocks'][ $this->request['csb_id'] ];
			}
			#get block from db
			else
			{
				$this->DB->build( array( 'select'	=> 'csb.*',
										 'from'		=> array ('custom_sidebar_blocks' => 'csb' ),
										 'where'	=> 'csb.csb_id='.$this->request['csb_id'],
										 'add_join' => array(
															array(																	           
																	'select' => 'p.*',
																	'from'   => array( 'permission_index' => 'p' ),
																	'where'  => "p.app = 'customSidebarBlocks' AND p.perm_type='block' AND perm_type_id=csb.csb_id",
																	'type'   => 'left',
																 )
															)
								)		);			

				$this->DB->execute();

				if ( !$this->DB->getTotalRows() )
				{
					$this->registry->output->showError( $this->lang->words['error_none_found'] );	
				}	
				else
				{
					$content = $this->DB->fetch();
				}
			}
		}
		
		#get permission matrix
		$matrix_html = $this->permissions->adminPermMatrix( 'block', $content );
		
		#output
		$this->registry->output->html .= $this->html->blockForm( $content, $matrix_html );
		$this->registry->output->html .= $this->html->footer();
	}
	
	/**
	 * Add/Edit Block
	 */
	private function add_block()
	{	
		if ( ! $this->request['csb_raw'] )
		{
			#process editor contents first
			IPSText::getTextClass('bbcode')->bypass_badwords	= 1;
			IPSText::getTextClass('bbcode')->parse_smilies		= 1;
			IPSText::getTextClass('bbcode')->parse_html		= 0;
			IPSText::getTextClass('bbcode')->parse_bbcode		= 1;
			IPSText::getTextClass('bbcode')->parse_nl2br    	= 1;
			IPSText::getTextClass('bbcode')->parsing_section 	= 'global';

			if( trim($this->request['csb_content']) == '<br>' OR trim($this->request['csb_content']) == '<br />' )
			{
				$content	= '';
			}
			else
			{
				$content = IPSText::getTextClass('editor')->processRawPost( 'csb_content' );
			}

			$content = str_replace( "&#39;", "'", IPSText::stripslashes( $content ) );

			$content = IPSText::getTextClass('bbcode')->preDbParse( $content );
		}
		else
		{
		$content = $_POST['csb_content'];
		}

		#fill block array
		$csb_datas = array( 'csb_title'		=> $this->request['csb_title'],
							'csb_on' 		=> $this->request['csb_on'],
							'csb_image'	 	=> $this->request['csb_image'],
							'csb_use_perms' => $this->request['csb_use_perms'],
							'csb_use_box' 	=> $this->request['csb_use_box'],
                            'csb_raw' 		=> $this->request['csb_raw'],
							'csb_content' 	=> $content
						   );
		
		#adding block?
		if ( ! $this->request['csb_id'] )
		{					  
			$this->DB->insert( 'custom_sidebar_blocks', $csb_datas );
			$new_id = $this->DB->getInsertId();
		
			$this->registry->output->global_message = $this->lang->words['block_added'];	
		}
		#ok, well then we better update one or this was all a huge waste of time
		else
		{						  
			$this->DB->update( 'custom_sidebar_blocks', $csb_datas, 'csb_id='.$this->request['csb_id'] );
			
			$this->registry->output->global_message = str_replace('<%NAME%>', $this->request['csb_title'], $this->lang->words['block_edited']);			
		}
		
		$csb_id = ( $this->request['csb_id'] ) ? $this->request['csb_id'] : $new_id;
		
		$this->permissions->savePermMatrix( $this->request['perms'], $csb_id, 'block' );

		$this->cache->rebuildCache('custom_sidebar_blocks','customSidebarBlocks');		
		
		#now ya'll mozy on away, ya hear
		$this->registry->output->silentRedirectWithMessage( $this->settings['base_url'].$this->html->form_code.'&amp;do=blocks' );	
	}
	
	/**
	 * List Blocks
	 */	
	private function blocks()
	{
		#init
		$content = "";

		#grab blocks from cache
		if ( is_array( $this->caches['custom_sidebar_blocks'] ) )
		{
			 foreach ( $this->caches['custom_sidebar_blocks'] AS $block )
			 {
				#add row
				$content .= $this->html->blockRow( $block );
			 }
		}
		#get blocks from db
		else
		{
			$this->DB->build( array( 'select'	=> '*',
									 'from'		=> 'custom_sidebar_blocks',
									 'group'	=> 'csb_id',
									 'order'	=> 'csb_position',
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
		}
		
		#output
		$this->registry->output->html .= $this->html->blocksOverviewWrapper( $content );
		$this->registry->output->html .= $this->html->footer();
	}
	
	/**
	 * Show Settings
	 */		
	private function showSettings()
	{
		require_once( IPSLib::getAppDir( 'core' ).'/modules_admin/tools/settings.php' );
		$settings =  new admin_core_tools_settings();
		$settings->makeRegistryShortcuts( $this->registry );
				
		$settings->html		= $this->registry->output->loadTemplate( 'cp_skin_tools', 'core' );
		$settings->form_code	= $settings->html->form_code    = 'module=tools&amp;section=settings';
		$settings->form_code_js	= $settings->html->form_code_js = 'module=tools&amp;section=settings';

		$this->request['conf_title_keyword'] 	= 'e_CSB';
		$settings->return_after_save     	= $this->settings['base_url'] . $this->form_code . '&saved=1&do=settings';
		$settings->_viewSettings();
		$this->registry->output->html .= $this->html->footer();
	}
	
	/**
	 * Delete!
	 */		
	private function delete()
	{
		if( ! $this->request['csb_id'] )
		{
			$this->registry->output->showError( $this->lang->words['error_no_id'] );
		}
		
		#delete it!
		$this->DB->delete( 'custom_sidebar_blocks', 'csb_id = ' . $this->request['csb_id'] );
		
		#add message
		$this->registry->output->global_message = $this->lang->words['block_deleted'];

		#rebuild da cache
		$this->cache->rebuildCache('custom_sidebar_blocks','customSidebarBlocks');

		#redirect
		$this->blocks();		
	}

	/**
	 * Cache those Blocks!
	 */
	public function rebuildBlockCache()
	{
		$cache = array();
			
        #get block
		$this->DB->build( array( 'select'	=> 'csb.*',
								 'from'		=> array ('custom_sidebar_blocks' => 'csb' ),
								 'order'    => 'csb.csb_position ASC',
								 'add_join' => array(
													 array(	'select' => 'p.*',			
															'from'   => array( 'permission_index' => 'p' ),
															'where'  => "p.app = 'customSidebarBlocks' AND p.perm_type='block' AND perm_type_id=csb.csb_id",
															'type'   => 'left',
														  )  
													)
						)		);			

		$this->DB->execute();
	
		while ( $r = $this->DB->fetch() )
		{
			$cache[ $r['csb_id'] ] = $r;
		}
		
		#do it!
		$this->cache->setCache( 'custom_sidebar_blocks', $cache, array( 'array' => 1, 'deletefirst' => 0, 'donow' => 1 ) );

		if ( $this->request['human'] == 'yes' )
		{
			#is someone there?  better redirect...
			$this->registry->output->global_message = $this->lang->words['blocks_recached'];

			#redirect
			$this->blocks();
		}	
	}
}