<?php

/**
 * Invision Power Services
 * IP.Board v3.0.5
 * Profile Plugin Library
 * Last Updated: $Date: 2009-09-15 05:56:15 -0400 (Tue, 15 Sep 2009) $
 *
 * @author 		$Author: matt $
 * @copyright	(c) 2001 - 2009 Invision Power Services, Inc.
 * @license		http://www.invisionpower.com/community/board/license.html
 * @package		Invision Power Board
 * @subpackage	Members
 * @link		http://www.invisionpower.com
 * @since		20th February 2002
 * @version		$Revision: 5112 $
 *
 */

if ( ! defined( 'IN_IPB' ) )
{
	print "<h1>Incorrect access</h1>You cannot access this file directly. If you have recently upgraded, make sure you upgraded all the relevant files.";
	exit();
}

class profile_topics extends profile_plugin_parent
{
	/**
	 * Attachment object
	 *
	 * @access	protected
	 * @var		object
	 */	
	protected $attach;
	
	/**
	 * Return HTML block
	 *
	 * @access	public
	 * @param	array		Member information
	 * @return	string		HTML block
	 */
	public function return_html_block( $member=array() ) 
	{
		//-----------------------------------------
		// INIT
		//-----------------------------------------
		
		$content		= '';
		$last_x			= 5;
		$forum_ids		= array();
		$date_cut		= '';
		//-----------------------------------------
		// Got a member?
		//-----------------------------------------
		
		if ( ! is_array( $member ) OR ! count( $member ) )
		{
			return $this->registry->getClass('output')->getTemplate('profile')->tabNoContent( 'err_no_posts_to_show' );
		}
		
		//-----------------------------------------
		// Some words
		//-----------------------------------------
		
		$this->registry->class_localization->loadLanguageFile( array( 'public_topic' ), 'forums' );

		//-----------------------------------------
		// Can view other member's topics?
		//-----------------------------------------
		
		if( !$this->memberData['g_other_topics'] AND $this->memberData['member_id'] != $member['member_id'] )
		{
			return $this->registry->getClass('output')->getTemplate('profile')->tabNoContent( 'err_no_posts_to_show' );
		}
		
		//-----------------------------------------
		// And limit by post count...
		//-----------------------------------------
		
		$posts	= intval($this->memberData['posts']);
		
		//-----------------------------------------
		// Will we need to parse attachments?
		//-----------------------------------------
		
		$parseAttachments	= false;
		
		/* Get list of good forum IDs */
		$forumIdsOk = $this->registry->class_forums->fetchSearchableForumIds();
		
		/* Set up joins */
		$_post_joins = array( array(
									'select' => 'p.*',
									'from'   => array( 'posts' => 'p' ),
									'where'  => 't.topic_firstpost=p.pid',
									'type'   => 'left' 
								),
							 array(
									'select'	=> 'm.member_group_id, m.mgroup_others',
									'from'		=> array( 'members' => 'm' ),
									'where'		=> 'm.member_id=p.author_id',
									'type'		=> 'left' 
								) );
		
		/* Cache? */
		if ( IPSContentCache::isEnabled() )
		{
			if ( IPSContentCache::fetchSettingValue('post') )
			{
				$_post_joins[] = IPSContentCache::join( 'post', 'p.pid' );
			}
		}
		
		if ( $this->settings['search_ucontent_days'] )
		{
			$date_cut = ( $this->memberData['last_post'] ? $this->memberData['last_post'] : time() ) - 86400 * intval( $this->settings['search_ucontent_days'] );
			$date_cut = ' AND t.start_date > ' . $date_cut;
		}

		//-----------------------------------------
		// Get last X posts
		//-----------------------------------------

		$this->DB->build( array( 'select'	=> 't.*',
								 'from'		=> array( 'topics' => 't' ),
								 'where'	=> "t.starter_id={$member['member_id']} AND t.approved=1 AND t.forum_id IN (" . implode( ",", $forumIdsOk ) . ") " . $date_cut,
								'order'		=> 't.start_date DESC',
								'limit'		=> array( 0, $last_x ),
								'add_join'	=> $_post_joins ) );
		$o = $this->DB->execute();
		
		while( $row = $this->DB->fetch( $o ) )
		{
			//-----------------------------------------
			// Ghost topics... BOO
			//-----------------------------------------
			
			if( !$row['pid'] )
			{
				continue;
			}

			$pids[ $row['pid'] ]	= $row['pid'];
			
			if( $row['topic_hasattach'] )
			{
				$parseAttachments	= true;
			}
			
			if ( ! $row['cache_content'] )
			{
				IPSText::getTextClass( 'bbcode' )->parse_smilies			= $row['use_emo'];
				IPSText::getTextClass( 'bbcode' )->parse_html				= ( $row['use_html'] and $this->memberData['g_dohtml'] and $row['post_htmlstate'] ) ? 1 : 0;
				IPSText::getTextClass( 'bbcode' )->parse_nl2br				= $row['post_htmlstate'] == 2 ? 1 : 0;
				IPSText::getTextClass( 'bbcode' )->parse_bbcode				= 1;
				IPSText::getTextClass( 'bbcode' )->parsing_section			= 'topics';
				IPSText::getTextClass( 'bbcode' )->parsing_mgroup			= $row['member_group_id'];
				IPSText::getTextClass( 'bbcode' )->parsing_mgroup_others	= $row['mgroup_others'];
	
				$row['post']	= IPSText::getTextClass( 'bbcode' )->preDisplayParse( $row['post'] );
				
				IPSContentCache::update( $row['pid'], 'post', $row['post'] );
			}
			else
			{
				$row['post'] = $row['cache_content'];
			}
			
			$row['post']	= IPSLib::memberViewImages( $row['post'] );
			
			
			$row['_post_date']  = ipsRegistry::getClass( 'class_localization')->getDate( $row['post_date'], 'SHORT' );
			$row['_date_array'] = IPSTime::date_getgmdate( $row['post_date'] + ipsRegistry::getClass( 'class_localization')->getTimeOffset() );
			
			$row['post'] .= "\n<!--IBF.ATTACHMENT_". $row['pid']. "-->";

			$content .= $this->registry->getClass('output')->getTemplate('profile')->tabSingleColumn( $row, $this->lang->words['profile_read_topic'], $this->settings['base_url'].'app=forums&amp;module=forums&amp;section=findpost&amp;pid='.$row['pid'], $row['title'] );
		}
		
		//-----------------------------------------
		// Attachments (but only if necessary)
		//-----------------------------------------
		
		if ( $parseAttachments AND !is_object( $this->class_attach ) )
		{
			require_once( IPSLib::getAppDir( 'core' ) . '/sources/classes/attach/class_attach.php' );
			$this->class_attach           =  new class_attach( $this->registry );

			$this->class_attach->type  = 'post';
			$this->class_attach->init();
			
			if ( IPSMember::checkPermissions('download') === false )
			{
				$this->settings['show_img_upload'] = 0;
			}
			
			$content = $this->class_attach->renderAttachments( $content, $pids );
			$content = $content[0]['html'];
		}

		//-----------------------------------------
		// Macros...
		//-----------------------------------------
		
		$content = $this->registry->output->replaceMacros( $content );
		
		//-----------------------------------------
		// Return content..
		//-----------------------------------------
		
		return $content ? $this->registry->getClass('output')->getTemplate('profile')->tabTopics( $content ) : $this->registry->getClass('output')->getTemplate('profile')->tabNoContent( 'err_no_posts_to_show' );
	}
	
}