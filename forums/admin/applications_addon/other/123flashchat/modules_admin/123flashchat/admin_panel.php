<?php
/**
 * Invision Power Services
 * IP.Board v3.0.1
 * 123flashchat Management
 *
 * @author 		$Author: TopCMM $
 * @copyright	(c) 2001 - 2010 TopCMM, Inc.
 * @license		http://www.invisionpower.com/community/board/license.html
 * @package		Invision Power Board
 * @subpackage	123flashchat
 * @link		http://www.123flashchat.com
 */

if ( ! defined( 'IN_ACP' ) )
{
	print "<h1>Incorrect access</h1>You cannot access this file directly. If you have recently upgraded, make sure you upgraded 'admin.php'.";
	exit();
}

class admin_123flashchat_123flashchat_admin_panel extends ipsCommand 
{
	/**
	 * Skin file
	 *
	 * @access	public
	 * @var		object
	 */
	public $html;
	
	/**
	 * Main execution method
	 *
	 * @access	public
	 * @param	object		ipsRegistry reference
	 * @return	void
	 */
	public function doExecute( ipsRegistry $registry )
	{
        ini_set("default_socket_timeout",3);
		/* Load Skin and Lang */
		$this->html               = $this->registry->output->loadTemplate( 'cp_skin_123flashchat' );
		$this->html->form_code    = 'module=123flashchat&amp;section=admin_panel';
		$this->html->form_code_js = 'module=123flashchat&section=admin_panel';
		
		$this->lang->loadLanguageFile( array( 'admin_123flashchat' ) );
		
		/* Output */
		$admin_url = $this->settings['fc_client_loc'] . 'admin_123flashchat.swf?init_host='.$this->settings['fc_server_host'].'&init_port='.$this->settings['fc_server_port'] . (($this->settings['fc_server'] == 1) ? "&init_group=" . $this->settings['fc_group'] : "");
		if(!empty($this->memberData['name']) && !empty($this->memberData['members_pass_hash'])){
			$admin_url .= "&init_user=".rawurlencode(htmlspecialchars_decode($this->memberData['name']))."&init_password=".$this->memberData['members_pass_hash'];
		}		
		$this->registry->output->html .= $this->html->flashchat_admin_panel( $admin_url );
		$this->registry->output->html_main .= $this->registry->output->global_template->global_frame_wrapper();
		$this->registry->output->sendOutput();			
	}
}
