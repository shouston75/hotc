<?php
/*
+--------------------------------------------------------------------------
|   Portal 1.0.1
|   =============================================
|   by Michael John
|   Copyright 2011 DevFuse
|   http://www.devfuse.com
+--------------------------------------------------------------------------
|   Based on IP.Board Portal by Invision Power Services
|   Website - http://www.invisionpower.com/
+--------------------------------------------------------------------------
*/

if ( ! defined( 'IN_IPB' ) )
{
	print "<h1>Incorrect access</h1>You cannot access this file directly. If you have recently upgraded, make sure you upgraded 'admin.php'.";
	exit();
}

class ppi_poll extends public_portal_portal_portal 
{
	/**
	 * Initialize module
	 *
	 * @return	void
	 */
	public function init()
 	{
 	}
 	
	/**
	 * Display a poll
	 *
	 * @return	string		HTML content to replace tag with
	 * @todo	We need to update the code below to load the furlTemplates file and check against it, then add a fallback on the normal url as a default.
	 */
	public function portal_show_poll()
	{
 		if ( ! $this->settings['portal_poll_url'] )
 		{
 		     return;	
 		}
        
 		//-----------------------------------------
		// Get the topic ID of the entered URL
		//-----------------------------------------
		
		/* Friendly URL */
		if( $this->settings['use_friendly_urls'] )
		{
			preg_match( "#/topic/(\d+)(.*?)/#", $this->settings['portal_poll_url'], $match );
			$tid = intval( trim( $match[1] ) );
		}
		/* Normal URL */
		else
		{
			preg_match( "/(\?|&amp;)(t|showtopic)=(\d+)($|&amp;)/", $this->settings['portal_poll_url'], $match );
			$tid = intval( trim( $match[3] ) );
		}
		
		if ( !$tid )
		{
			return;
		}
        
        $this->request['t'] = $tid;
        
		$this->registry->class_localization->loadLanguageFile( array( 'public_boards', 'public_topic' ), 'forums' );
		$this->registry->class_localization->loadLanguageFile( array( 'public_editors' ), 'core' );

 		//-----------------------------------------
		// Load forum class
		//-----------------------------------------

		if( !$this->registry->isClassLoaded('class_forums') )
		{
			$classToLoad = IPSLib::loadLibrary( IPSLib::getAppDir( 'forums' ) . "/sources/classes/forums/class_forums.php", 'class_forums', 'forums' );
			$this->registry->setClass( 'class_forums', new $classToLoad( $this->registry ) );
			
			$this->registry->getClass('class_forums')->strip_invisible = 1;
			$this->registry->getClass('class_forums')->forumsInit();
    	}

 		//-----------------------------------------
		// Load topic class
		//-----------------------------------------
		
		if ( ! $this->registry->isClassLoaded('topics') )
		{
			$classToLoad = IPSLib::loadLibrary( IPSLib::getAppDir( 'forums' ) . "/sources/classes/topics.php", 'app_forums_classes_topics', 'forums' );
			$this->registry->setClass( 'topics', new $classToLoad( $this->registry ) );
		}
        		
		try
		{
			$this->registry->getClass('topics')->autoPopulate();
		}
		catch( Exception $crowdCheers )
		{
            return;  
		}        
        
 		//-----------------------------------------
		// We need to get the poll function
		//-----------------------------------------        
        
		$classToLoad = IPSLib::loadActionOverloader( IPSLib::getAppDir( 'forums', 'forums' ) . '/topics.php', 'public_forums_forums_topics', 'forums' );
		$topic = new $classToLoad();
        $topic->forumClass = $this->registry->getClass('class_forums');        
		$topic->makeRegistryShortcuts( $this->registry );

        # Move this over to use above class rather then query.
		$topic->topic = $this->DB->buildAndFetch( array( 'select' => '*', 'from' => 'topics', 'where'  => "tid=" . $tid ) );
		$topic->forum = ipsRegistry::getClass('class_forums')->forum_by_id[ $topic->topic['forum_id'] ];
	
		$this->request['f'] = $topic->forum['id'] ;
        
 		//-----------------------------------------
		// If good, display poll
		//----------------------------------------- 
        		
		if ( $topic->topic['poll_state'] )
		{
 			return $this->registry->getClass('output')->getTemplate('portal')->pollWrapper( $topic->_generatePollOutput(), $topic->topic );
 		}
 		else
 		{
 			return;
 		}        
 
 	}
}