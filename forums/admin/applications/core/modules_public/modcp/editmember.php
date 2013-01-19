<?php
/**
 * @file		editmember.php 	Moderator ability to edit members
 * $Copyright: (c) 2001 - 2011 Invision Power Services, Inc.$
 * $License: http://www.invisionpower.com/company/standards.php#license$
 * $Author: mark $
 * @since		2/16/2011
 * $LastChangedDate: 2011-08-23 10:24:47 -0400 (Tue, 23 Aug 2011) $
 * @version		v3.2.2
 * $Revision: 9404 $
 */

if ( ! defined( 'IN_IPB' ) )
{
	print "<h1>Incorrect access</h1>You cannot access this file directly. If you have recently upgraded, make sure you upgraded all the relevant files.";
	exit();
}

/**
 *
 * @class		public_core_modcp_editmember
 * @brief		Moderator ability to edit members
 * 
 */
class public_core_modcp_editmember extends ipsCommand
{
	/**
	 * Temporary stored output HTML
	 *
	 * @var		string
	 */
	public $output;
	
	/**
	 * Editor engine
	 *
	 * @var		object
	 */
	protected $editor;

	/**
	 * Moderator information
	 *
	 * @var		array
	 */
	protected $moderator		= array();

	/**
	 * Can ban member from warn panel
	 *
	 * @var		bool
	 */
	protected $canSuspend		= 0;

	/**
	 * Can mod queue member from warn panel
	 *
	 * @var		bool
	 */
	protected $canModQueue		= 0;

	/**
	 * Can remove posting rights from member from warn panel
	 *
	 * @var		bool
	 */
	protected $canRemovePostAbility	= 0;

	/**
	 * Number of times per day member can be warned
	 *
	 * @var		integer
	 */
	protected $restrictWarnsPerDay	= 0;

	/**
	 * Type of member
	 *
	 * @var		string
	 */
	protected $type			= 'mod';
	
	/**
	 * Data for the member being warned
	 *
	 * @var		array
	 */
	protected $warn_member	= array();

	/**
	 * Error message
	 *
	 * @var		string
	 */
	protected $_error		= '';

	/**
	 * Class entry point
	 *
	 * @param	object		$registry	Registry reference
	 * @return	@e void
	 */
	public function doExecute( ipsRegistry $registry )
	{
		//-----------------------------------------
		// Init
		//-----------------------------------------
		
		if( $this->request['do'] != 'setAsSpammer' )
		{
			$this->loadData();
		}

		$this->lang->loadLanguageFile( array( 'public_modcp' ) );

		//-----------------------------------------
		// Bouncy, bouncy!
		//-----------------------------------------
		
		switch( $this->request['do'] )
		{
			default:
			case 'index':
				$this->_moderatorIndex();
			break;

			case 'doeditmember':
				$this->_doEditMember();
			break;
			
			case 'setAsSpammer':
				$this->_setAsSpammer();
			break;

			case 'dowarn':
				$this->_doWarn();
			break;
			
			case 'add_note':
				$this->_addNoteform();
				$this->registry->output->addNavigation( $this->lang->words['w_title'], '' );
			break;
			
			case 'save_note':
				$this->_saveNote();
			break;
				
			case 'view':
				$this->viewLog();
				$this->registry->output->addNavigation( $this->lang->words['w_title'], '' );
			break;
		}

		$this->registry->output->addContent( $this->output );

		if( $this->request['popup'] )
		{
			$this->registry->getClass('output')->popUpWindow( $this->output );
		}
		else
		{
			$this->registry->output->sendOutput();
		}
	}
	
	/**
	 * Index page to moderator controls
	 *
	 * @return	@e void
	 */
	protected function _moderatorIndex()
	{
		//-----------------------------------------
		// Get tab contents
		//-----------------------------------------
		
		$editMember	= '';
		$warnForm	= '';
		$logs		= '';
		$noteForm	= '';
		
		if ( $this->memberData['g_is_supmod'] )
		{
			$editMember	= $this->_editMember();
		}

		if ( $this->settings['warn_on'] )
		{
			$warnForm	= $this->_showForm();
			$logs		= $this->viewLog( true );
			$noteForm	= $this->_addNoteForm( true );
		}
		
		if( !$editMember AND !$warnForm AND !$logs AND !$noteForm )
		{
			$this->registry->output->showError( 'warn_no_access', 2025.1, null, null, 403 );
		}
		
		//-----------------------------------------
		// Figure out return URL stuff
		//-----------------------------------------
		
		$return	= array();
		
		if( $this->request['pf'] )
		{
			$return	= $this->warn_member;
		}
		else if( $this->request['t'] )
		{
			$return	= $this->DB->buildAndFetch( array( 'select' => 'tid, title_seo, title', 'from' => 'topics', 'where' => 'tid=' . intval($this->request['t']) ) );
		}
		
		if ( !$return['members_display_name'] )
		{
			$return['members_display_name'] = $this->warn_member['members_display_name'];
		}

		//-----------------------------------------
		// Output
		//-----------------------------------------
		
		$this->output	.= $this->registry->getClass('output')->getTemplate('modcp')->modControls( $editMember, $warnForm, $logs, $noteForm, $return );

		$this->registry->getClass('output')->setTitle( $this->lang->words['cp_em_title'] . ' - ' . $this->settings['board_name'] );
		$this->registry->getClass('output')->addNavigation( sprintf( $this->lang->words['cp_vp_title'], $this->warn_member['members_display_name'] ), "showuser={$this->warn_member['member_id']}", $this->warn_member['members_seo_name'], 'showuser' );
		$this->registry->getClass('output')->addNavigation( $this->lang->words['cp_em_title'], '' );
	}

	/**
	 * Load all necessary properties
	 *
	 * @return	@e void
	 */
	public function loadData()
	{
		//-----------------------------------------
		// Set up
		//-----------------------------------------
		
		$this->settings['warn_min']	= $this->settings['warn_min'] ? $this->settings['warn_min'] : 0;
		$this->settings['warn_max']	= $this->settings['warn_max'] ? $this->settings['warn_max'] : 10;

		//-----------------------------------------
		// Get forum libraries
		//-----------------------------------------
		
		ipsRegistry::getAppClass( 'forums' );

		//-----------------------------------------
		// Make sure we're a moderator...
		//-----------------------------------------
		
		$pass = 0;

		if( $this->memberData['member_id'] )
		{
			if( $this->memberData['g_is_supmod'] == 1 )
			{
				$pass				        = 1;
				$this->canSuspend	        = $this->settings['warn_gmod_ban'];
				$this->canModQueue	        = $this->settings['warn_gmod_modq'];
				$this->canRemovePostAbility	= $this->settings['warn_gmod_post'];
				$this->restrictWarnsPerDay	= intval($this->settings['warn_gmod_day']);
				$this->canApprovePosts      = 1;
				$this->canApproveTopics     = 1;
				$this->canDeletePosts       = 1;
				$this->canDeleteTopics      = 1;
				$this->type		   	        = 'supmod';
			}
			else if( $this->memberData['is_mod'] )
			{
				$other_mgroups	= array();
				$_other_mgroups	= IPSText::cleanPermString( $this->memberData['mgroup_others'] );
				
				if( $_other_mgroups )
				{
					$other_mgroups	= explode( ",", $_other_mgroups );
				}
				
				$other_mgroups[] = $this->memberData['member_group_id'];

				$this->DB->build( array( 
										'select' => '*',
										'from'   => 'moderators',
										'where'  => "(member_id='" . $this->memberData['member_id'] . "' OR (is_group=1 AND group_id IN(" . implode( ",", $other_mgroups ) . ")))" 
								)	);
											  
				$this->DB->execute();
				
				while ( $this->moderator = $this->DB->fetch() )
				{
					if ( $this->moderator['allow_warn'] )
					{
						$pass				        = 1;
						$this->canSuspend		    = $this->settings['warn_mod_ban'];
						$this->canModQueue	        = $this->settings['warn_mod_modq'];
						$this->canRemovePostAbility	= $this->settings['warn_mod_post'];
						$this->restrictWarnsPerDay	= intval($this->settings['warn_mod_day']);
						$this->canApprovePosts      = $this->moderator['post_q'];
						$this->canApproveTopics     = $this->moderator['topic_q'];
						$this->canDeletePosts       = $this->moderator['delete_post'];
						$this->canDeleteTopics      = $this->moderator['delete_topic'];
						$this->type			        = 'mod';
					}
				}
			}			
			
			if( $this->settings['warn_show_own'] and $this->memberData['member_id'] == $this->request['mid'] and !$pass )
			{
				$pass				        = 1;
				$this->canSuspend           = 0;
				$this->canModQueue	        = 0;
				$this->canRemovePostAbility	= 0;
				$this->restrictWarnsPerDay	= 0;
				$this->canApprovePosts      = 0;
				$this->canApproveTopics     = 0;
				$this->canDeletePosts       = 0;
				$this->canDeleteTopics      = 0;
				$this->type			        = 'member';
			}			
		}
			
		if ( !$pass )
		{
			$this->registry->output->showError( 'warn_no_access', 2025, null, null, 403 );
		}

		//-----------------------------------------
		// Ensure we have a valid member
		//-----------------------------------------
		
		$mid	= intval($this->request['mid']);
		
		if ( $mid < 1 )
		{
			$this->registry->output->showError( 'warn_no_user', 10249, null, null, 404 );
		}
		
		$this->warn_member	= IPSMember::load( $mid, 'all' );

		if ( ! $this->warn_member['member_id'] )
		{
			$this->registry->output->showError( 'warn_no_user', 10250, null, null, 404 );
		}
		
		//-----------------------------------------
		// Get editor
		//-----------------------------------------
		
		$classToLoad = IPSLib::loadLibrary( IPS_ROOT_PATH . 'sources/classes/editor/composite.php', 'classes_editor_composite' );
		$this->editor	= new $classToLoad();
	}
	
	/**
	 * View the warn logs
	 *
	 * @param	bool	$return	Return output instead of printing
	 * @return	@e mixed
	 */
	public function viewLog( $return=false )
	{
		$perpage	= 50;
		$start		= intval($this->request['st']) >= 0 ? intval($this->request['st']) : 0;
		$row		= $this->DB->buildAndFetch( array( 'select' => 'count(*) as cnt', 'from' => 'warn_logs', 'where' => "wlog_mid={$this->warn_member['member_id']}" ) );
		$rows		= array();
		$match		= array();

		if( $this->request['do'] == 'view' )
		{
			$url	= "app=core&amp;module=modcp&amp;section=editmember&amp;do=view&amp;mid={$this->warn_member['member_id']}";
		}
		else
		{
			$url	= "app=core&amp;module=modcp&amp;section=editmember&amp;_tab=warnlogs&amp;mid={$this->warn_member['member_id']}";
		}
		
		$links		= $this->registry->output->generatePagination( array(
																		'totalItems'		=> $row['cnt'],
																		'itemsPerPage'		=> $perpage,
																		'currentStartValue'	=> $start,
																		'baseUrl'			=> $url,
												 )	  );
				
		if ( $row['cnt'] > 0 )
		{
			$this->DB->build( array( 
									'select'	=> 'l.*',
									'from'		=> array( 'warn_logs' => 'l' ),
									'where'		=> 'l.wlog_mid=' . $this->warn_member['member_id'],
									'order'		=> 'l.wlog_date DESC ',
									'limit'		=> array( $start, $perpage ),
									'add_join'	=> array(
														array( 
															'select'	=> 'p.member_id as punisher_id, p.members_display_name as punisher_name, p.members_seo_name, p.member_group_id, p.mgroup_others',
															'from'		=> array( 'members' => 'p' ),
															'where'		=> 'p.member_id=l.wlog_addedby',
															'type'		=> 'left',
															)
														)
							)		);
			$q =$this->DB->execute();
		
			while ( $r = $this->DB->fetch( $q ) )
			{
				if ( strstr( $r['wlog_notes'], '<content>' ) )
				{
					preg_match( "#<content>(.+?)</content>#is", $r['wlog_notes'], $match );
				}
				else
				{
					$_array = unserialize( $r['wlog_notes'] );
					
					if ( is_array( $_array ) AND $_array['content'] )
					{
						$match[1] = $_array['content'];
					}
				}
				
				IPSText::getTextClass( 'bbcode' )->parse_smilies			= 1;
				IPSText::getTextClass( 'bbcode' )->parse_html				= 0;
				IPSText::getTextClass( 'bbcode' )->parse_bbcode				= 1;
				IPSText::getTextClass( 'bbcode' )->parsing_section			= 'warn';
				IPSText::getTextClass( 'bbcode' )->parsing_mgroup			= $r['member_group_id'];
				IPSText::getTextClass( 'bbcode' )->parsing_mgroup_others	= $r['mgroup_others'];
		
				$r['content'] = IPSText::getTextClass( 'bbcode' )->preDisplayParse( $match[1] );
				
				$rows[] = $r;
			}
		}
		
		$this->registry->getClass('output')->setTitle( $this->lang->words['warn_popup_title'] . ' - ' . ipsRegistry::$settings['board_name'] );
		
		if( $return )
		{
			return $this->registry->getClass('output')->getTemplate('modcp')->warn_view_log( $this->warn_member, $rows, $links );
		}
		else
		{
			$this->output .= $this->registry->getClass('output')->getTemplate('modcp')->warn_view_log( $this->warn_member, $rows, $links );
		}
	}

	/**
	 * Form to add a new note
	 *
	 * @param	bool	$return	Return output instead of printing
	 * @return	@e mixed
	 */
	protected function _addNoteForm( $return=false )
	{
		//-----------------------------------------
		// INIT
		//-----------------------------------------
		
		if ( $this->type == 'member' )
		{
			if( $return )
			{
				return false;
			}

			$this->registry->output->showError( 'warn_member_notes', 2027, null, null, 403 );
		}
		
		//-----------------------------------------
		// Output
		//-----------------------------------------

		if( $return )
		{
			return $this->registry->getClass('output')->getTemplate('modcp')->warn_add_note_form( $this->warn_member );
		}

		$this->registry->getClass('output')->setTitle( $this->lang->words['warn_popup_title'] . ' - ' . ipsRegistry::$settings['board_name'] );
		$this->registry->getClass('output')->popUpWindow( $this->registry->getClass('output')->getTemplate('modcp')->warn_add_note_form( $this->warn_member ) );
	}

	/**
	 * Save a new note
	 *
	 * @return	@e void
	 */
	protected function _saveNote()
	{
		//-----------------------------------------
		// Init
		//-----------------------------------------
		
		if ( $this->type == 'member' )
		{
			$this->registry->output->showError( 'warn_member_notes', 2026, null, null, 403 );
		}

		if ( $this->request['auth_key'] != $this->member->form_hash )
		{
			$this->registry->output->showError( 'no_permission', 3021, null, null, 403 );
		}

		$content	= '';
		$note		= trim( $this->request['note'] );
		$save		= array();

		if ( $note )
		{
			//-----------------------------------------
			// Ready to save?
			//-----------------------------------------
		
			$save['wlog_notes']		= "<content>{$note}</content>";
			$save['wlog_notes']		.= "<mod></mod>";
			$save['wlog_notes']		.= "<post></post>";
			$save['wlog_notes']		.= "<susp></susp>";
		
			$save['wlog_mid']		= $this->warn_member['member_id'];
			$save['wlog_addedby']	= $this->memberData['member_id'];
			$save['wlog_type']		= 'note';
			$save['wlog_date']		= time();
			
			//-----------------------------------------
			// Enter into warn loggy poos (eeew - poo)
			//-----------------------------------------
		
			$this->DB->insert( 'warn_logs', $save );
		}
		
		//-----------------------------------------
		// Done...
		//-----------------------------------------
		
		$popup	= $this->request['popup'] ? '&amp;popup=1' : '';
						
		$this->registry->output->silentRedirect( $this->settings['base_url'] . "app=core&amp;module=modcp&amp;section=editmember&amp;mid={$this->warn_member['member_id']}" . $popup );
	}

	/**
	 * Show the add new warn form
	 *
	 * @return	@e string
	 */
	protected function _showForm()
	{
		//-----------------------------------------
		// Check permissions
		//-----------------------------------------
		
		if ( ! $this->settings['warn_on'] )
		{
			return '';
		}
		
		if ( $this->type == 'member' )
		{
			$this->registry->output->showError( 'warn_member_notes', 10255, null, null, 403 );
		}

		//-----------------------------------------
		// Editor
		//-----------------------------------------
		
		$this->editor->setAllowBbcode( true );
		$this->editor->setAllowSmilies( true );
		$this->editor->setAllowHtml( true );
		$this->editor->setContent( $this->request['contact'], 'warn' );
		
		//-----------------------------------------
		// Output
		//-----------------------------------------
		
		$this->warn_member['warn_level']	= intval($this->warn_member['warn_level']);
		$this->request['contact']			= $this->request['contact'] ? IPSText::getTextClass( 'bbcode' )->preEditParse( $this->request['contact'] ) : '';

		return $this->registry->getClass('output')->getTemplate('modcp')->warnForm( IPSMember::buildDisplayData( $this->warn_member, '__all__' ),
																							$this->_error ? $this->lang->words[ $this->_error ] : '',
																							$this->canModQueue,
																							$this->canRemovePostAbility,
																							$this->canSuspend,
																							$this->editor->show( 'contact' ) );
	}
	
	/**
	 * Checking method access
	 *
	 * @return	@e void
	 */
	protected function _checkAccess()
	{
		//-----------------------------------------
		// I've already warned you!!
		//-----------------------------------------
		
		if ( $this->restrictWarnsPerDay > 0 )
		{
			$time_to_check = time() - 86400;
			
			$check = $this->DB->buildAndFetch( array( 'select' => 'COUNT(*) as warn_times', 'from' => 'warn_logs', 'where' => "wlog_mid={$this->warn_member['member_id']} AND wlog_date > {$time_to_check}" ) );
			
			if ( $check['warn_times'] >= $this->restrictWarnsPerDay )
			{
				$this->registry->output->showError( 'warned_already', 10257, null, null, 403 );
			}
		}
	}

	/**
	 * Add a new arn entry
	 *
	 * @return	@e void
	 */
	protected function _doWarn()
	{
		//-----------------------------------------
		// Enabled?
		//-----------------------------------------
		
		if ( ! $this->settings['warn_on'] )
		{
			$this->registry->output->showError( 'warn_system_off', 10248 );
		}
		
		//-----------------------------------------
		// INIT
		//-----------------------------------------
		
		$save					= array();
		$err 					= 0;
		$topicPosts_type		= trim( $this->request['topicPosts_type'] );
		$topicPosts_topics		= intval( $this->request['topicPosts_topics'] );
		$topicPosts_replies		= intval( $this->request['topicPosts_replies'] );
		$topicPosts_lastx		= intval( $this->request['topicPosts_lastx'] );
		$topicPosts_lastxunits	= trim( $this->request['topicPosts_lastxunits'] );
		$level_custom			= intval( $this->request['level_custom'] );
		$ban_indef				= intval( $this->request['ban_indef'] );
		$member_banned			= intval( $this->warn_member['member_banned'] );
		$warn_level				= intval( $this->warn_member['warn_level']) ;
		
		$this->request['_tab']	= 'warn';
		
		//-----------------------------------------
		// Protected member? Really? o_O
		//-----------------------------------------
		
		if ( strstr( ',' . $this->settings['warn_protected'] . ',', ',' . $this->warn_member['member_group_id'] . ',' ) )
		{
			$this->registry->output->showError( 'warn_protected_member', 10256, null, null, 403 );
		}

		//--------------------------------------
		// Are we allowed to do this?
		//--------------------------------------
		
		if ( $this->request['level'] == 'add' or ( $this->request['level'] == 'custom' and $level_custom > $warn_level ) )
		{
			$this->_checkAccess();
		}

		if ( $this->type == 'member' )
		{
			$this->registry->output->showError( 'warn_member_notes', 2028, null, null, 403 );
		}

		if ( $this->request['key'] != $this->member->form_hash )
		{
			$this->registry->output->showError( 'warn_bad_key', 3020, null, null, 403 );
		}

		//-----------------------------------------
		// As Celine Dion once squawked, "Show me the reason"
		//-----------------------------------------
		
		if ( trim($this->request['reason']) == "" )
		{
			$this->_error	= 'we_no_reason';
			$this->_moderatorIndex();
			return;
		}
		
		//-----------------------------------------
		// Other checks
		//-----------------------------------------
		
		if ( ! $this->settings['warn_past_max'] && $this->request['level'] != 'nochange' )
		{
			if ( $this->request['level'] == 'custom' )
			{
				if ( $level_custom > $this->settings['warn_max'] )
				{
					$err = 1;
				}
				else if( $level_custom < $this->settings['warn_min'] )
				{
					$err = 2;
				}
			}
			else if ( $this->request['level'] == 'add' )
			{
				if ( $warn_level >= $this->settings['warn_max'] )
				{
					$err = 1;
				}
			}
			else
			{
				if ( $warn_level <= $this->settings['warn_min'] )
				{
					$err = 2;
				}
			}
			
			if ( $err )
			{
				$this->registry->output->showError( $err == 1 ? 'warn_past_max_high' : 'warn_past_max_low', 10251 );
			}
		}

		//-----------------------------------------
		// Load Mod Squad
		//-----------------------------------------
		
		$classToLoad		= IPSLib::loadLibrary( IPSLib::getAppDir( 'forums' ) . '/sources/classes/moderate.php', 'moderatorLibrary', 'forums' );
		$moderatorLibrary	= new $classToLoad( $this->registry );

		//-----------------------------------------
		// Plussy - minussy?
		//-----------------------------------------
		
		if( $this->request['level'] == 'nochange' )
		{
			$save['wlog_type']	= 'nochan';
		}
		else
		{
			$save['wlog_type']	= ( $this->request['level'] == 'custom' ) ? 'custom' : ( ( $this->request['level'] == 'add' ) ? 'neg' : 'pos' );
		}

		$save['wlog_date']	= time();
		
		//-----------------------------------------
		// Contacting the member?
		//-----------------------------------------
		
		$test_content = trim( IPSText::br2nl( $_POST['contact'] ) ) ;

		if ( $test_content != "" )
		{
			if ( trim($this->request['subject']) == "" )
			{
				$this->_error	= 'we_no_subject';
				$this->_moderatorIndex();
				return;
			}
		
			unset($test_content);
			
			$contact	= $this->editor->process( $_POST['contact'] );
		
			IPSText::getTextClass('bbcode')->parse_smilies			= 1;
			IPSText::getTextClass('bbcode')->parse_html				= 0;
			IPSText::getTextClass('bbcode')->parse_bbcode			= 1;
			IPSText::getTextClass('bbcode')->parsing_section		= 'warn';
			IPSText::getTextClass('bbcode')->parsing_mgroup			= $this->memberData['member_group_id'];
			IPSText::getTextClass('bbcode')->parsing_mgroup_others	= $this->memberData['mgroup_others'];
			
			$contact	= IPSText::getTextClass( 'bbcode' )->preDbParse( $contact );

			$save['wlog_contact']			= $this->request['contactmethod'];
			$save['wlog_contact_content']	= "<subject>" . $this->request['subject'] . "</subject><content>" . $contact . "</content>";

			if ( $this->request['contactmethod'] == 'email' )
			{
				IPSText::getTextClass( 'bbcode' )->parse_smilies			= 0;
				IPSText::getTextClass( 'bbcode' )->parse_html				= 1;
				IPSText::getTextClass( 'bbcode' )->parse_bbcode				= 1;
				IPSText::getTextClass( 'bbcode' )->parsing_section			= 'warn';
				IPSText::getTextClass( 'bbcode' )->parsing_mgroup			= $this->memberData['member_group_id'];
				IPSText::getTextClass( 'bbcode' )->parsing_mgroup_others	= $this->memberData['mgroup_others'];
				
				$contact = IPSText::getTextClass( 'bbcode' )->preDisplayParse( $contact );
				
				//-----------------------------------------
				// Send the email
				//-----------------------------------------
				
				IPSText::getTextClass( 'email' )->getTemplate("email_member");
					
				IPSText::getTextClass( 'email' )->buildMessage( array(
																		'MESSAGE'		=> $contact,
																		'MEMBER_NAME'	=> $this->warn_member['members_display_name'],
																		'FROM_NAME'		=> $this->memberData['members_display_name']
																		)
																);

				IPSText::getTextClass( 'email' )->subject	= $this->request['subject'];
				IPSText::getTextClass( 'email' )->to		= $this->warn_member['email'];
				IPSText::getTextClass( 'email' )->from		= $this->settings['email_out'];
				IPSText::getTextClass( 'email' )->sendMail();
			}
			else
			{
				//-----------------------------------------
				// Grab PM class
				//-----------------------------------------
				
				$classToLoad = IPSLib::loadLibrary( IPSLib::getAppDir( 'members' ) . '/sources/classes/messaging/messengerFunctions.php', 'messengerFunctions', 'members' );
				$messengerFunctions = new $classToLoad( $this->registry );
 				
				try
				{
				 	$messengerFunctions->sendNewPersonalTopic( $this->warn_member['member_id'],
															$this->memberData['member_id'], 
															array(), 
															$this->request['subject'], 
															$contact, 
															array( 'origMsgID'			=> 0,
																	'fromMsgID'			=> 0,
																	'postKey'			=> md5(microtime()),
																	'trackMsg'			=> 0,
																	'addToSentFolder'	=> 0,
																	'hideCCUser'		=> 0,
																	'forcePm'			=> 1,
																)
															);
				}
				catch( Exception $error )
				{
					$msg		= $error->getMessage();
					$toMember	= IPSMember::load( $this->warn_member['member_id'], 'core' );
				   
					if ( strstr( $msg, 'BBCODE_' ) )
				    {
						$msg = str_replace( 'BBCODE_', '', $msg );
	
						$this->registry->output->showError( $msg, 10252 );
					}
					else if ( isset($this->lang->words[ 'err_' . $msg ]) )
					{
						$this->lang->words[ 'err_' . $msg ] = $this->lang->words[ 'err_' . $msg ];
						$this->lang->words[ 'err_' . $msg ] = str_replace( '#NAMES#'   , implode( ",", $messengerFunctions->exceptionData ), $this->lang->words[ 'err_' . $msg ] );
						$this->lang->words[ 'err_' . $msg ] = str_replace( '#TONAME#'  , $toMember['members_display_name']    , $this->lang->words[ 'err_' . $msg ] );
						$this->lang->words[ 'err_' . $msg ] = str_replace( '#FROMNAME#', $this->memberData['members_display_name'], $this->lang->words[ 'err_' . $msg ] );
						
						$this->registry->output->showError( 'err_' . $msg, 10253 );
					}
					else if( $msg != 'CANT_SEND_TO_SELF' )
					{
						$_msgString = $this->lang->words['err_UNKNOWN'] . ' ' . $msg;
						$this->registry->output->showError( $_msgString, 10254 );
					}
				}
			}
		}
		else
		{
			unset($test_content);
		}
		
		//-----------------------------------------
		// Right - is we banned or wha?
		//-----------------------------------------
			
		$restrict_post						= '';
		$mod_queue							= '';
		$susp								= '';
		$_notes								= array();
		$_notes['content']					= $this->request['reason'];
		$_notes['mod']						= $this->request['mod_value'];
		$_notes['mod_unit']					= $this->request['mod_unit'];
		$_notes['mod_indef']				= $this->request['mod_indef'];
		$_notes['post']						= $this->request['post_value'];
		$_notes['post_unit']				= $this->request['post_unit'];
		$_notes['post_indef']				= $this->request['post_indef'];
		$_notes['susp']						= $this->request['susp_value'];
		$_notes['susp_unit']				= $this->request['susp_unit'];
		$_notes['ban']						= $ban_indef;
		$_notes['topicPosts_type']			= $topicPosts_type;
		$_notes['topicPosts_topics']		= $topicPosts_topics;
		$_notes['topicPosts_replies']		= $topicPosts_replies;
		$_notes['topicPosts_lastx']			= $topicPosts_lastx;
		$_notes['topicPosts_lastxunits']	= $topicPosts_lastxunits;
		
		$save['wlog_notes']	= serialize( $_notes );

		//-----------------------------------------
		// Member Content
		//-----------------------------------------
		
		if ( $topicPosts_type == 'unapprove' OR $topicPosts_type == 'approve' )
		{
			$time		= ( $topicPosts_lastxunits == 'd' ) ? ( $topicPosts_lastx * 24 ) : $topicPosts_lastx;
			$approve	= ( $topicPosts_type == 'approve' ) ? TRUE : FALSE;
			
			if ( ( $topicPosts_topics AND $this->canApproveTopics ) AND ( $topicPosts_replies AND $this->canApprovePosts ) )
			{
				$moderatorLibrary->toggleApproveMemberContent( $this->warn_member['member_id'], $approve, 'all', $time );
			}
			else if ( $topicPosts_topics AND $this->canApproveTopics )
			{
				$moderatorLibrary->toggleApproveMemberContent( $this->warn_member['member_id'], $approve, 'topics', $time );
			}
			else if ( $topicPosts_replies AND $this->canApprovePosts )
			{
				$moderatorLibrary->toggleApproveMemberContent( $this->warn_member['member_id'], $approve, 'replies', $time );
			}
		}
		else if ( $topicPosts_type == 'delete')
		{
			$time = ( $topicPosts_lastxunits == 'd' ) ? ( $topicPosts_lastx * 24 ) : $topicPosts_lastx;
			
			if ( ( $topicPosts_topics AND $this->canDeleteTopics ) AND ( $topicPosts_replies AND $this->canDeletePosts ) )
			{
				$moderatorLibrary->deleteMemberContent( $this->warn_member['member_id'], 'all', $time );
			}
			else if ( $topicPosts_topics AND $this->canDeleteTopics )
			{
				$moderatorLibrary->deleteMemberContent( $this->warn_member['member_id'], 'topics', $time );
			}
			else if ( $topicPosts_replies AND $this->canDeletePosts )
			{
				$moderatorLibrary->deleteMemberContent( $this->warn_member['member_id'], 'replies', $time );
			}
		}

		//-----------------------------------------
		// Member Suspension
		//-----------------------------------------
		
		if ( $this->canModQueue )
		{
			if ( $this->request['mod_indef'] == 1 )
			{
				$mod_queue = 1;
			}
			elseif ( $this->request['mod_value'] > 0 )
			{
				$mod_queue = IPSMember::processBanEntry( array( 'timespan' => intval($this->request['mod_value']), 'unit' => $this->request['mod_unit']  ) );
			}
		}
		
		if ( $this->canRemovePostAbility )
		{
			if ( $this->request['post_indef'] == 1 )
			{
				$restrict_post = 1;
			}
			elseif ( $this->request['post_value'] > 0 )
			{
				$restrict_post = IPSMember::processBanEntry( array( 'timespan' => intval($this->request['post_value']), 'unit' => $this->request['post_unit']  ) );
			}
		}
		
		if ( $this->canSuspend )
		{
			if ( $ban_indef )
			{
				$member_banned = 1;
			}
			else if ( $this->request['susp_value'] > 0 )
			{
				$susp = IPSMember::processBanEntry( array( 'timespan' => intval($this->request['susp_value']), 'unit' => $this->request['susp_unit']  ) );
			}
			
			//-----------------------------------------
			// Removing ban?
			//-----------------------------------------
			
			if ( ! $ban_indef AND $member_banned )
			{
				$member_banned = 0;
			}
		}
		
		$save['wlog_mid']		= $this->warn_member['member_id'];
		$save['wlog_addedby']	= $this->memberData['member_id'];
		
		//-----------------------------------------
		// Enter into warn loggy poos (eeew - poo)
		//-----------------------------------------

		$this->DB->insert( 'warn_logs', $save );
		
		//-----------------------------------------
		// Update member
		//-----------------------------------------
		
		if( $this->request['level'] != 'nochange' )
		{
			if ( $this->request['level'] == 'custom' )
			{
				$warn_level = $level_custom;
			}
			else if ( $this->request['level'] == 'add' )
			{
				$warn_level++;
			}
			else
			{
				$warn_level--;
			}
			
			if ( $warn_level > $this->settings['warn_max'] )
			{
				$warn_level = $this->settings['warn_max'];
			}
			
			if ( $warn_level < intval($this->settings['warn_min']) )
			{
				$warn_level = intval($this->settings['warn_min']);
			}
		}
		
		IPSMember::save( $this->warn_member['member_id'], array( 'core' => array(	'mod_posts'		=> $mod_queue,
																					'restrict_post'	=> $restrict_post,
																					'temp_ban'		=> $susp,
																					'member_banned'	=> $member_banned,
																					'warn_level'	=> $warn_level,
																					'warn_lastwarn'	=> time() ) ) );
		
		//-----------------------------------------
		// Redirect
		//-----------------------------------------

		$this->_redirect( sprintf( $this->lang->words['w_done_te'], $this->warn_member['members_display_name'] ) );
	}

	/**
	 * Flag a user account as a spammer
	 *
	 * @return	@e void
	 */
	protected function _setAsSpammer()
	{
		//-----------------------------------------
		// Init
		//-----------------------------------------
		
		$member_id	= intval( $this->request['member_id'] );
		$toSave		= array( 'core' => array( 'bw_is_spammer' => 1 ) );
		$topicId	= intval($this->request['t']);
		$topic		= array();
		
		if( $topicId )
		{
			$topic	= $this->DB->buildAndFetch( array( 'select' => 'tid, title_seo, forum_id', 'from' => 'topics', 'where' => 'tid=' . $topicId ) );
		}
		
		//-----------------------------------------
		// Load member
		//-----------------------------------------
		
		$member	= IPSMember::load( $member_id );
		
		if ( ! $member['member_id'] )
		{
			$this->registry->output->showError( 'moderate_no_permission', 10311900, true, null, 404 );
		}
		
		//-----------------------------------------
		// Check permissions
		//-----------------------------------------
		
		if( !$this->memberData['g_is_supmod'] AND !$this->memberData['forumsModeratorData'][ $topic['forum_id'] ]['bw_flag_spammers'] )
		{
			$this->registry->output->showError( 'moderate_no_permission', 103119, true, null, 403 );
		}

		if ( strstr( ',' . $this->settings['warn_protected'] . ',', ',' . $member['member_group_id'] . ',' ) )
		{
			$this->registry->output->showError( 'moderate_no_permission', 10311901, true, null, 403 );
		}
		
		//-----------------------------------------
		// How to treat
		//-----------------------------------------

		if ( $this->settings['spm_option'] )
		{
			switch( $this->settings['spm_option'] )
			{
				case 'disable':
					$toSave['core']['restrict_post']      = 1;
					$toSave['core']['members_disable_pm'] = 2;
				break;

				case 'unapprove':
					$toSave['core']['restrict_post']      = 1;
					$toSave['core']['members_disable_pm'] = 2;
					
					//-----------------------------------------
					// Unapprove posts and topics
					//-----------------------------------------
					
					$this->getModLibrary()->toggleApproveMemberContent( $member_id, FALSE, 'all', intval( $this->settings['spm_post_days'] ) * 24 );
				break;

				case 'ban':
					//-----------------------------------------
					// Unapprove posts and topics
					//-----------------------------------------
					
					$this->getModLibrary()->toggleApproveMemberContent( $member_id, FALSE, 'all', intval( $this->settings['spm_post_days'] ) * 24 );
					
					$toSave	= array(
									'core'				=> array(
																'member_banned'		=> 1,
																'title'				=> '',
																'bw_is_spammer'		=> 1,
																),
									'extendedProfile'	=> array(
																'signature'			=> '',
																'pp_about_me'		=> '',
																)
									);

					//-----------------------------------------
					// Photo
					//-----------------------------------------
					
					$classToLoad	= IPSLib::loadLibrary( IPS_ROOT_PATH . 'sources/classes/member/photo.php', 'classes_member_photo' );
					$photos			= new $classToLoad( $this->registry );
					$photos->remove( $member['member_id'] );

					//-----------------------------------------
					// Profile fields
					//-----------------------------------------
			
					$classToLoad			= IPSLib::loadLibrary( IPS_ROOT_PATH . 'sources/classes/customfields/profileFields.php', 'customProfileFields' );
					$fields					= new $classToLoad();
					$fields->member_data	= $member;
					$fields->initData( 'edit' );
					$fields->parseToSave( array() );
					
					if ( count( $fields->out_fields ) )
					{
						$toSave['customFields']	= $fields->out_fields;
					}

					//-----------------------------------------
					// Update signature content cache
					//-----------------------------------------
					
					IPSContentCache::update( $member['member_id'], 'sig', '' );
				break;
			}
		}
		
		//-----------------------------------------
		// Shut off status imports
		//-----------------------------------------
		
		$bwOptions	= IPSBWOptions::thaw( $member['tc_bwoptions'], 'twitter' );
		$bwOptions['tc_si_status']	= 0;
		$twitter	= IPSBWOptions::freeze( $bwOptions, 'twitter' );

		$bwOptions = IPSBWOptions::thaw( $member['fb_bwoptions'], 'facebook' );
		$bwOptions['fbc_si_status']	= 0;			
		$facebook	= IPSBWOptions::freeze( $bwOptions, 'facebook' );
		
		$toSave['extendedProfile']['tc_bwoptions']	= $twitter;
		$toSave['extendedProfile']['fb_bwoptions']	= $facebook;
		
		//-----------------------------------------
		// Send email if configured to do so
		//-----------------------------------------
		
		if ( $this->settings['spm_notify'] AND ( $this->settings['email_in'] != $this->memberData['email'] ) )
		{
			IPSText::getTextClass('email')->getTemplate( 'possibleSpammer' );

			IPSText::getTextClass('email')->buildMessage( array( 'DATE'			=> $this->registry->class_localization->getDate( $member['joined'], 'LONG', 1 ),
																 'MEMBER_NAME'	=> $member['members_display_name'],
																 'IP'			=> $member['ip_address'],
																 'EMAIL'		=> $member['email'],
																 'LINK'			=> $this->registry->getClass('output')->buildSEOUrl( "showuser=" . $member['member_id'], 'public', $member['members_seo_name'], 'showuser') ) );

			IPSText::getTextClass('email')->subject	= $this->lang->words['new_registration_email_spammer'] . ' ' . $this->settings['board_name'];
			IPSText::getTextClass('email')->to		= $this->settings['email_in'];
			IPSText::getTextClass('email')->sendMail();
		}
		
		//-----------------------------------------
		// Save member
		//-----------------------------------------
		
		IPSMember::save( $member_id, $toSave );
		
		//-----------------------------------------
		// Notify spam service
		//-----------------------------------------
		
		if( $this->settings['spam_service_send_to_ips'] )
		{
			IPSMember::querySpamService( $member['email'], $member['ip_address'], 'markspam' );
		}
		
		//-----------------------------------------
		// Member sync
		//-----------------------------------------
		
		IPSLib::runMemberSync( 'onSetAsSpammer', $member );
		
		//-----------------------------------------
		// Mod log
		//-----------------------------------------
		
		$this->getModLibrary()->addModerateLog( 0, 0, 0, 0, $this->lang->words['flag_spam_done'] . ': ' . $member['member_id'] . ' - ' . $member['email'] );
		
		//-----------------------------------------
		// Redirect
		//-----------------------------------------
		
		if( $topicId )
		{
			$this->registry->output->redirectScreen( $this->lang->words['flag_spam_done'], $this->settings['base_url'] . "showtopic=" . $topic['tid'] . "&amp;st=" . intval($this->request['st']), $topic['title_seo'], 'showtopic' );
		}
		else
		{
			$this->registry->output->redirectScreen( $this->lang->words['flag_spam_done'], $this->settings['base_url'] . "showuser=" . $member['member_id'], $member['members_seo_name'], 'showuser' );
		}
	}
	
	/**
	 * Save the member updates
	 *
	 * @return	@e void
	 * @todo 	[Future] Determine what items should be editable and allow moderators to edit them
	 */
	protected function _doEditMember()
	{
		//-----------------------------------------
		// Check permissions
		//-----------------------------------------
		
		if ( ! $this->memberData['g_is_supmod'] )
		{
			$this->registry->output->showError( 'mod_only_supermods', 10370, true, null, 403 );
		}

		if ( ! $this->memberData['g_access_cp'] AND $this->warn_member['g_access_cp'] )
		{
			$this->registry->output->showError( 'mod_admin_edit', 3032, true, null, 403 );
		}

		if ( $this->request['auth_key'] != $this->member->form_hash )
		{
			$this->registry->output->showError( 'no_permission', 3032.1, null, null, 403 );
		}
		
		//-----------------------------------------
		// Init
		//-----------------------------------------
		
		$editable	= array();

		//-----------------------------------------
		// Signature and about me
		//-----------------------------------------
		
		$signature	= $this->editor->process( $_POST['Post'] );
		$aboutme	= $this->editor->process( $_POST['aboutme'] );

		//-----------------------------------------
		// Parse signature
		//-----------------------------------------
		
		IPSText::getTextClass('bbcode')->parse_smilies			= 0;
		IPSText::getTextClass('bbcode')->parse_html				= $this->caches['group_cache'][ $this->warn_member['member_group_id'] ]['g_dohtml'];
		IPSText::getTextClass('bbcode')->parse_bbcode			= 1;
		IPSText::getTextClass('bbcode')->parsing_section		= 'signatures';
		IPSText::getTextClass('bbcode')->parsing_mgroup			= $this->warn_member['member_group_id'];
		IPSText::getTextClass('bbcode')->parsing_mgroup_others	= $this->warn_member['mgroup_others'];

		$signature		= IPSText::getTextClass('bbcode')->preDbParse( $signature );
		$signatureCache	= IPSText::getTextClass('bbcode')->preDisplayParse( $signature );
		
		//-----------------------------------------
		// Parse about me
		//-----------------------------------------
		
		IPSText::getTextClass('bbcode')->parse_smilies			= 1;
		IPSText::getTextClass('bbcode')->parse_html				= $this->caches['group_cache'][ $this->warn_member['member_group_id'] ]['g_dohtml'];
		IPSText::getTextClass('bbcode')->parse_bbcode			= 1;
		IPSText::getTextClass('bbcode')->parsing_section		= 'aboutme';
		IPSText::getTextClass('bbcode')->parsing_mgroup			= $this->warn_member['member_group_id'];
		IPSText::getTextClass('bbcode')->parsing_mgroup_others	= $this->warn_member['mgroup_others'];

		$aboutme	= IPSText::getTextClass('bbcode')->preDbParse( $aboutme );	
		
		//-----------------------------------------
		// Add to array to save
		//-----------------------------------------
		
		$save['extendedProfile']	= array( 'signature' => $signature, 'pp_about_me' => $aboutme );
		$save['members']			= array( 'title' => $this->request['title'] );

		if ( $this->request['photo'] == 1 )
		{
			$classToLoad	= IPSLib::loadLibrary( IPS_ROOT_PATH . 'sources/classes/member/photo.php', 'classes_member_photo' );
			$photos			= new $classToLoad( $this->registry );
			$photos->remove( $this->warn_member['member_id'] );
		}
		
		//-----------------------------------------
		// Profile fields
		//-----------------------------------------

		$classToLoad			= IPSLib::loadLibrary( IPS_ROOT_PATH . 'sources/classes/customfields/profileFields.php', 'customProfileFields' );
		$fields					= new $classToLoad();
		$fields->member_data	= $this->warn_member;
		$fields->initData( 'edit' );
		$fields->parseToSave( $_POST );
		
		if ( count( $fields->out_fields ) )
		{
			$save['customFields']	= $fields->out_fields;
		}

		//-----------------------------------------
		// Write it to the DB.
		//-----------------------------------------
		
		IPSMember::save( $this->warn_member['member_id'], $save );
		
		//-----------------------------------------
		// Update signature content cache
		//-----------------------------------------
		
		IPSContentCache::update( $this->warn_member['member_id'], 'sig', $signatureCache );

		//-----------------------------------------
		// Add a mod log entry and redirect
		//-----------------------------------------
		
		$this->getModLibrary()->addModerateLog( 0, 0, 0, 0, $this->lang->words['acp_edited_profile'] . " " . $this->warn_member['members_display_name'] );

		$this->_redirect( $this->lang->words['acp_edited_profile'] . " " . $this->warn_member['members_display_name'] );
	}
	
	/**
	 * Redirect back to where we came from
	 *
	 * @param	string	$message	Redirect message
	 * @return	@e void
	 */
	protected function _redirect( $message )
	{
		if( $this->request['pf'] )
		{
			$this->registry->output->redirectScreen( $message, $this->settings['base_url'] . "showuser=" . $this->warn_member['member_id'], $this->warn_member['members_seo_name'], 'showuser' );
		}
		else if( $this->request['t'] )
		{
			$topic	= $this->DB->buildAndFetch( array( 'select' => 'tid, title_seo', 'from' => 'topics', 'where' => 'tid=' . intval($this->request['t']) ) );

			$this->registry->output->redirectScreen( $message, $this->settings['base_url'] . "showtopic=" . $topic['tid'] . '&amp;st=' . $this->request['_st'], $topic['title_seo'], 'showtopic' );
		}
		else
		{
			$this->registry->output->redirectScreen( $message, $this->settings['base_url'] . "app=core&amp;module=modcp" );
		}
	}

	/**
	 * Form to edit a member
	 *
	 * @return	@e void
	 * @todo 	[Future] Determine what items should be editable and allow moderators to edit them
	 */
	protected function _editMember()
	{
		//-----------------------------------------
		// Check permissions
		//-----------------------------------------

		if ( ! $this->memberData['g_is_supmod'] )
		{
			return '';
		}

		if ( ! $this->memberData['g_access_cp'] AND $this->warn_member['g_access_cp'] )
		{
			return '';
		}
		
		//-----------------------------------------
		// Init
		//-----------------------------------------
		
		$editable	= array();
		
		//-----------------------------------------
		// Show about me and signature editors
		//-----------------------------------------
		
		$this->editor->setAllowBbcode( true );
		$this->editor->setAllowSmilies( false );
		$this->editor->setAllowHtml( $this->caches['group_cache'][ $this->warn_member['member_group_id'] ]['g_dohtml'] );
		$this->editor->setContent( $this->warn_member['signature'], 'signatures' );
		$editable['signature']	= $this->editor->show( 'Post' );

		$this->editor->setAllowBbcode( true );
		$this->editor->setAllowSmilies( false );
		$this->editor->setAllowHtml( $this->caches['group_cache'][ $this->warn_member['member_group_id'] ]['g_dohtml'] );
		$this->editor->setContent( $this->warn_member['pp_about_me'], 'aboutme' );
		$editable['aboutme']	= $this->editor->show( 'aboutme' );

		//-----------------------------------------
		// Other fields
		//-----------------------------------------
		
		$editable['member_id']				= $this->warn_member['member_id'];
		$editable['members_display_name']	= $this->warn_member['members_display_name'];
		$editable['title']					= $this->warn_member['title'];

		//-----------------------------------------
		// Profile fields
		//-----------------------------------------

		$classToLoad			= IPSLib::loadLibrary( IPS_ROOT_PATH . 'sources/classes/customfields/profileFields.php', 'customProfileFields' );
		$fields					= new $classToLoad();
		$fields->member_data	= $this->warn_member;
		$fields->initData( 'edit' );
		$fields->parseToEdit();
		
		$editable['_parsedMember']	= IPSMember::buildDisplayData( $this->warn_member );

		//-----------------------------------------
		// Return the HTML
		//-----------------------------------------
		
		return $this->registry->getClass('output')->getTemplate('modcp')->editUserForm( $editable, $fields );
	}
	
	/**
	 * Get the moderator library and return it
	 *
	 * @return	@e object
	 */
	protected function getModLibrary()
	{
		static $modLibrary	= null;
		
		if( !$modLibrary )
		{
			$classToLoad	= IPSLib::loadLibrary( IPSLib::getAppDir( 'forums' ) . '/sources/classes/moderate.php', 'moderatorLibrary', 'forums' );
			$modLibrary		= new $classToLoad( $this->registry );
			$modLibrary->init( array() );
		}
		
		return $modLibrary;
	}
}