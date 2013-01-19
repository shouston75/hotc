<?php
/**
 * <pre>
 * Invision Power Services
 * IP.Board v3.1.3
 * Image Ajax
 * Last Updated: $LastChangedDate: 2010-08-18 16:55:30 +0100 (Wed, 18 Aug 2010) $
 * </pre>
 *
 * @author 		$Author: matt $
 * @copyright	(c) 2001 - 2009 Invision Power Services, Inc.
 * @license		http://www.invisionpower.com/community/board/license.html
 * @package		IP.Gallery
 * @link		http://www.invisionpower.com
 * @version		$Rev: 6767 $
 *
 */

if ( ! defined( 'IN_IPB' ) )
{
	print "<h1>Incorrect access</h1>You cannot access this file directly. If you have recently upgraded, make sure you upgraded all the relevant files.";
	exit();
}

class public_core_global_comments extends ipsCommand
{
	/**
	 * Main class entry point
	 *
	 * @access	public
	 * @param	object		ipsRegistry reference
	 * @return	void		[Outputs to screen]
	 */	
	public function doExecute( ipsRegistry $registry )
	{
		/* From App */
		$fromApp = trim( $this->request['fromApp'] );
		
		/* Init some data */
		require_once( IPS_ROOT_PATH . 'sources/classes/comments/bootstrap.php' );
		$this->_comments = classes_comments_bootstrap::controller( $fromApp );

		/* What to do? */
		switch( $this->request['do'] )
		{
			case 'add':
				$this->_add();
			break;
			case 'delete':
				$this->_delete();
			break;
			case 'showEdit':
				$this->_showEdit();
			break;
			case 'saveEdit':
				$this->_saveEdit();
			break;
			case 'fetchReply':
				$this->_fetchReply();
			break;
			case 'moderate':
				$this->_moderate();
			break;
			case 'findLastComment':
				$this->_findLastComment();
			break;
			case 'findComment':
				$this->_findComment();
			break;
        }
    }
		
	/**
	 * Find last page of comments
	 *
	 * @access	protected
	 * @return	void		[Redirects]
	 */
	protected function _findLastComment()
	{
		/* Init */
		$parentId = intval( $this->request['parentId'] );
		
		$this->_comments->redirectToComment( 'last', $parentId );
	}
	
	/**
	 * Find last page of comments
	 *
	 * @access	protected
	 * @return	void		[Redirects]
	 */
	protected function _findComment()
	{
		/* Init */
		$parentId   = intval( $this->request['parentId'] );
		$comment_id = intval( $this->request['comment_id'] );
		
		$this->_comments->redirectToComment( $comment_id, $parentId );
	}
	
   /**
     * Moderate
     *
     * @access	protected
     * @return	void
     */
    protected function _moderate()
    {
    	$parentId   = intval( $this->request['parentId'] );
 		$commentIds = ( is_array( $_POST['commentIds'] ) ) ? IPSLib::cleanIntArray( $_POST['commentIds'] ) : array();
 		$modact	 	= trim( $this->request['modact'] );
 		
 		if ( count( $commentIds ) )
 		{
 			try
			{
 				$this->_comments->moderate( $modact, $parentId, $commentIds, $this->memberData );	
 			
 				$this->returnJsonArray( array( 'msg' => 'ok' ) );
			}
			catch( Exception $error )
			{
				$this->returnJsonError( 'Error ' . $error->getMessage() . ' line: ' . $error->getFile() . '.' . $error->getLine() );
			}
 		}
    }
    
    /**
	 * Reply
	 *
	 * @access	public
	 * @return	void
	 */
	protected function _fetchReply()
	{
		/* INIT */
		$commentId = intval( $this->request['comment_id'] );
		$parentId  = intval( $this->request['parentId'] );
		
		/* Quick error checko */
		if ( ! $commentId OR ! $parentId )
		{
			$this->returnString( 'error' );
		}
		
		# Get Edit form
		try
		{
			$html = $this->_comments->fetchReply( $parentId, $commentId, $this->memberData );

			$this->returnString( $html );
		}
		catch ( Exception $error )
		{
			$this->returnString( 'Error ' . $error->getMessage() );
		}
	}

	
	/**
	 * Deletes a comment
	 *
	 * @access	public
	 * @return	void
	 */
	protected function _delete()
	{
		/* INIT */
		$commentId = intval( $this->request['comment_id'] );
		$parentId  = intval( $this->request['parentId'] );
		
		/* Perm check (looks nice) */
		if ( $this->request['auth_key'] != $this->member->form_hash )
		{
			$this->registry->getClass('output')->showError( 'posting_bad_auth_key', '1-global-comments-_delete-0', null, null, 403 );
		}
		
		/* Quick error checko */
		if ( ! $commentId OR ! $parentId )
		{
			$this->registry->getClass('output')->showError( 'no_permission', '1-global-comments-_delete-2', null, null, 403 );
		}
		
		try
		{
			$this->_comments->delete( $parentId, $commentId, $this->memberData );
			
			/* Redirect to find latest */
			$this->_comments->redirectToComment( 'last', $parentId );
		}
		catch ( Exception $error )
		{
			$this->registry->getClass('output')->showError( 'no_permission', '1-global-comments-_delete-2', null, null, 403 );
		}
	}
	
	/**
	 * Shows the edit box
	 *
	 * @access	public
	 * @return	void
	 */
	protected function _showEdit()
	{
		/* INIT */
		$commentId = intval( $this->request['comment_id'] );
		$parentId  = intval( $this->request['parentId'] );
		
		/* Quick error checko */
		if ( ! $commentId OR ! $parentId )
		{
			$this->returnString( 'error' );
		}
		
		array( 'parent_id'			=> 'id',
							 	   'parent_owner_id'	=> 'member_id',
							       'parent_parent_id'   => 'img_album_id',
							       'parent_title'	    => 'caption',
							       'parent_seo_title'   => 'caption_seo',
							       'parent_date'	    => 'idate' );
		
		# Get Edit form
		try
		{
			$html = $this->_comments->displayEditForm( $parentId, $commentId, $this->memberData );
			
			/* Get nav */
			$parent = $this->_comments->remapFromLocal( $this->_comments->fetchParent( $parentId ), 'parent' );
		
			/* Output */
			$this->registry->getClass('output')->setTitle( $this->title );
			$this->registry->getClass('output')->addContent( $html );
			
			$this->registry->getClass('output')->addNavigation( $this->lang->words['edit_comment'] . $parent['parent_title'], sprintf( $this->_comments->fetchSetting('urls-showParent'), $parentId ), $parent['parent_seo_title'] );
	
			$this->registry->getClass('output')->sendOutput();
		}
		catch ( Exception $error )
		{
			$this->registry->getClass('output')->showError( 'no_permission', '1-global-comments-_showEdit-0', null, null, 403 );
		}
	}
	
		/**
	 * Saves the post
	 *
	 * @access	public
	 * @return	void
	 */
	protected function _saveEdit()
	{
		/* INIT */
		$commentId = intval( $this->request['comment_id'] );
		$parentId  = intval( $this->request['parentId'] );
		$post      = IPSText::parseCleanValue( $_POST['Post'] );
		
		/* Perm check (looks nice) */
		if ( $this->request['auth_key'] != $this->member->form_hash )
		{
			$this->registry->getClass('output')->showError( 'posting_bad_auth_key', '1-global-comments-_saveEdit-0', null, null, 403 );
		}
		
		/* Quick error checko */
		if ( ! $parentId OR ! $commentId )
		{
			$this->registry->getClass('output')->showError( 'no_permission', '1-global-comments-_saveEdit-1', null, null, 403 );
		}

		/* Edit */
		try
		{
			$this->_comments->edit( $parentId, $commentId, $_POST['Post'], $this->memberData );
			
			$this->_comments->redirectToComment( $commentId, $parentId );
		}
		catch ( Exception $error )
		{
			$this->registry->getClass('output')->showError( 'no_permission', '1-global-comments-_saveEdit-2', null, null, 403 );
		}
	}
	
	/**
	 * Add a comment via the magic and mystery of NORMAL POSTING FOOL
	 *
	 * @access	protected
	 * @return	void
	 */
	protected function _add()
	{
		/* init */
		$post     = IPSText::parseCleanValue( $_POST['Post'] );
		$parentId = intval( $this->request['parentId'] );
		
		/* Perm check (looks nice) */
		if ( $this->request['auth_key'] != $this->member->form_hash )
		{
			$this->registry->getClass('output')->showError( 'posting_bad_auth_key', '1-global-comments-_add-0', null, null, 403 );
		}
			
		if ( $post AND $parentId )
		{
			try
			{
				$newCommentId = $this->_comments->add( $parentId, $_POST['Post'] );
				
				/* Redirect to find latest */
				$this->_comments->redirectToComment( 'last', $parentId );
			}
			catch( Exception $e )
			{
				$this->registry->output->showError( 'no_permission', '1-global-comments-_add-1', null, null, 403 );
			}
		}
		else
		{
			$this->registry->output->showError( 'no_permission', '1-global-comments-_add-2', null, null, 403 );
		}
	}
	
	
}
