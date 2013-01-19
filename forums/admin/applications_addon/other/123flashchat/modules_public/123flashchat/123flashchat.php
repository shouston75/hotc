<?php
/**
 * Invision Power Services
 * IP.Board v3.0.1
 * 123flashchat Public
 *
 * @author 		$Author: TopCMM $
 * @copyright	(c) 2001 - 2010 TopCMM, Inc.
 * @license		http://www.invisionpower.com/community/board/license.html
 * @package		Invision Power Board
 * @subpackage	123flashchat
 * @link		http://www.123flashchat.com
 */

if ( ! defined( 'IN_IPB' ) )
{
	print "<h1>Incorrect access</h1>You cannot access this file directly. If you have recently upgraded, make sure you upgraded all the relevant files.";
	exit();
}

class public_123flashchat_123flashchat_123flashchat extends ipsCommand
{
	/**
	 * Temporary HTML output
	 *
	 * @access	private
	 * @var		string
	 */
	private $output				= "";

	/**
	 * Page title
	 *
	 * @access	private
	 * @var		string
	 */
	private $page_title			= "";


	/**
	 * Class entry point
	 *
	 * @access	public
	 * @param	object		Registry reference
	 * @return	void		[Outputs to screen/redirects]
	 */
	public function doExecute( ipsRegistry $registry )
	{
		/* Make sure the calednar is installed and enabled */
		if( ! IPSLib::appIsInstalled( '123flashchat' ) )
		{
			$this->registry->output->showError( 'no_permission', 1076 );
		}
		
		/* Load language  */
		$this->registry->class_localization->loadLanguageFile( array( 'public_123flashchat' ) );

		/* Page Title */
		if( $this->page_title == "" )
		{
			$this->page_title = $this->settings['board_name'] . " " . $this->lang->words['page_title'];
		}

		$this->registry->output->setTitle( $this->page_title );

		$this->output .= $this->registry->output->getTemplate('123flashchat')->chat($this->fc_get_chat_url());
		/* Navigation */
		if( ! is_array( $this->registry->output->_navigation ) )
		{
			$this->registry->output->addNavigation( $this->lang->words['page_title'], 'app=123flashchat&amp;module=123flashchat' );
			$this->registry->output->addNavigation( $this->lang->words['page_title'], 'app=123flashchat&amp;module=123flashchat' );
		}
		/* Output */
		$this->registry->output->addContent( str_replace('height="100%"', 'height="600"',str_replace('height:100%', 'height:600px', str_replace('height=100%', 'height=600', $this->output))) );
		if ($this->settings['fc_client_embed'] or !$this->request['room'])
		{
			$this->registry->output->sendOutput();
		}
		else
		{
			echo $this->output;
		}
	}

	function fc_get_chat_url()
	{
		$client = $this->settings['fc_client'];
		$server = $this->settings['fc_server'];
		$client_name = $client ? (($client == 1) ? 'htmlchat/123flashchat.html' : 'avatarchat.swf') : '123flashchat.swf';
		$chat_url = $this->settings['fc_client_loc'] . $client_name . "?init_host=" . $this->settings['fc_server_host'] . "&init_port=" . $this->settings['fc_server_port'] . "&init_lang=" . $this->settings['fc_client_lang'];
		if ($client == 0)
		{
			$chat_url .= "&init_skin=" . $this->settings['fc_client_skin'];
		}
		if ($server == 1)
		{
			$chat_url .= "&init_group=" . $this->settings['fc_group'];
		}
		if ($server == 2)
		{
			$chat_url = "http://free.123flashchat.com/js.php?room=" . $this->settings['fc_room'] . "&skin="  . $this->settings['fc_client_skin'] . "&lang="  . $this->settings['fc_client_lang'];
		}
		if ($this->memberData['member_id'])
		{
			if(!empty($this->memberData['name']) && !empty($this->memberData['members_pass_hash']) )
			{
				$chat_url .= "&init_user=" . rawurlencode(htmlspecialchars_decode($this->memberData['name'])) . "&init_password=" . $this->memberData['members_pass_hash'];
			}
		}
		if ($this->request['room'])
		{
			$chat_url .= "&init_room=" . $this->request['room'];
		}
		return $chat_url;
	}

}
