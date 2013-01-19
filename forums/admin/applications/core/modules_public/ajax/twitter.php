<?php

/**
 *	(IM) Twitter Sidebar
 *
 * @author 		m4rtin
 * @copyright	© 2009 Invision Modding
 * @web: 		http://www.invisionmodding.com
 * @IPB ver.:	IP.Board 3.0
 * @version:	0.9.8 (9009)
 *
 */


class public_core_ajax_twitter extends ipsAjaxCommand
{
	private $twitterAuth;
	private $hookClass;
	private $sessionNr = 0;
	
	private $bitly = array( 'login' 	=> 'invisionmodding',
							'apiKey'	=> 'R_a0f2f45284a8d24b02ecae698bffbed9',
						);
	
	/**
	 * Contructor
	 *
	 * @access	public
	 * @param	object	ipsRegistry
	 * @return	void
	 */
	public function doExecute( ipsRegistry $registry )
	{
		if ( ! $this->memberData['member_id'] OR $this->memberData['oauth_state'] != 'returned' )
		{
			return;
		}
		
		// Find the hooks sourcefile
		if ( is_array( $this->caches['hooks']['templateHooks']['im_twitterBar'] ) AND count( $this->caches['hooks']['templateHooks']['im_twitterBar'] ) )
		{
			foreach( $this->caches['hooks']['templateHooks']['im_twitterBar'] as $h )
			{
				if ( $h['className'] == 'twitterSideBar' )
				{
					require_once IPS_HOOKS_PATH . $h['filename'];
					$this->hookClass = twitterSideBar::instance();
					break;
				}
			}
		}
		else
		{
			$this->returnJsonError( 'something went terribly wrong' );
		}
		
		/* Just some 4 random chars used for the lightbox */
		$this->sessionNr = strtoupper( substr( md5( uniqid() ), 0, 4 ) );

		/* Rate limiting */
		$this->rateLimit = $this->hookClass->getRateLimit();
		
		
		// Authenticate with Twitter
		$this->twitterAuth = new myTwitterOAuth( $this->settings['im_twitterApiKey'], $this->settings['im_twitterApiSecretKey'], $this->memberData['oauth_access_token'], $this->memberData['oauth_access_token_secret']);
		
		if ( twitterSideBar::instance()->twitterTroubles == 0 )
		{
			switch ( $this->request['do'] )
			{
				case 'populate':
					$this->checkRateLimit();
					$this->populateSidebar();
					break;
					
				case 'updateStatus':
					$this->updateStatus();
					break;
					
				case 'favorite':
					$this->favorite();
					break;
					
				case 'getMentions':
					$this->checkRateLimit();
					$this->getMentions();
					break;
					
				case 'doSearch':
					$this->checkRateLimit();
					$this->doSearch();
					break;
					
				case 'getUserTweets':
					$this->checkRateLimit();
					$this->getUserTweets();
					break;
					
				case 'getStatus':
					$this->checkRateLimit();
					$this->getSingleStatus();
					break;	
					
				case 'doShorten':
					$this->shortenUrl();
					break;
				
				case 'getFriends':
					$this->checkRateLimit();
					$this->getFriends();
					break;
					
				case 'getMoreFriends':
					$this->checkRateLimit();
					$this->getMoreFriends();
					break;
			}
		}
	}
	
	/**
	 * Check that we have enough API calls left
	 * Avoiding this limit can get you blacklisted by Twitter
	 * 
	 * @access	private
	 * @return	void
	 */
	public function checkRateLimit()
	{
		if ( $this->rateLimit['remaining_hits'] == 0 )
		{
			$this->returnJsonError( $this->lang->words['noMoreTwitterCalls'] );
		}
	}
	
	
	/**
	 * Populate the sidebar
	 * Relying on data from 3rd party, so we don't want to halt page load
	 *
	 * @access 	private
	 * @param	int			Wheter to skip the cache or not
	 * @return 	void
	 */
	private function populateSidebar( $skipCache=0 )
	{		
		$twitterTabs 	= array();
		$memberCache 	= $this->memberData['_cache'];
		$response		= array();
		
		if ( twitterSideBar::instance()->twitterTroubles == 1 )
		{
			$this->returnJsonError( $this->lang->words['errorTwitterTrouble'] );
		}

		// See if we can't load the default data from cache
		if ( isset( $memberCache['twitterTabs'] ) AND ( $memberCache['twitterCacheAge'] + 120 ) > time() AND $skipCache == 0 )
		{
			$twitterTabs = $memberCache['twitterTabs'];
			
			if ( count( $twitterTabs['friends'] ) < 2 )
			{
				$friendsTL 						= $this->getFriendsTimeline();
				$twitterTabs['friends'] 		= $friendsTL['html'];
				
				if ( isset( $friendsTL['error'] ) AND !empty( $friendsTL['error'] ) )
				{
					$response['error'] = $friendsTL['error'];
				}
				else
				{
					$memberCache['twitterTabs'] 	= $twitterTabs;
			
					IPSMember::packMemberCache( $this->memberData['member_id'], $memberCache );
				}
			}
		}
		else
		{
			$twitterTabs['myStatus'] 		= $this->getMyStatus();
			$friendsTL 						= $this->getFriendsTimeline();
			$twitterTabs['friends'] 		= $friendsTL['html'];
			
			if ( isset( $twitterTabs['myStatus']['error'] ) AND !empty( $twitterTabs['myStatus']['error'] ) )
			{
				$response['error'] = $twitterTabs['myStatus']['error'];
			}
			else if ( isset( $friendsTL['error'] ) AND !empty( $friendsTL['error'] ) )
			{
				$response['error'] = $friendsTL['error'];
			}
			
			$memberCache['twitterTabs'] 	= $twitterTabs;
			$memberCache['twitterCacheAge']	= time();
			
			IPSMember::packMemberCache( $this->memberData['member_id'], $memberCache );
		}
		

		$response['html'] = $this->registry->getClass( 'output' )->getTemplate( 'twitterBar' )->defaultContent( $twitterTabs );
		
		$this->returnJsonArray( $response );
	}
	
	/**
	 * Get my latest tweet
	 *
	 * @access	private
	 * @return	array		Array of my latest tweet
	 */
	private function getMyStatus()
	{
		//-----------------------------------------
		// Get my recent update
		//-----------------------------------------
		
		$myTimeline = $this->twitterAuth->OAuthRequest( 'http://twitter.com/statuses/user_timeline.json', array( 'count' => 1 ), 'GET' );
		$myStatus = json_decode( $myTimeline, 1 );
		$status = array();
		
		if ( isset( $myStatus['error'] ) )
		{
			$status['error'] = $myStatus['error'];
		}
		else
		{
			$status = $myStatus[0];
			
			if ( is_array( $status ) AND count( $status ) AND !isset( $myStatus['error'] ) )
			{
				$this->parseTweets( &$status );
			}
		}
		
		return $status;
	}
	
	/**
	 * Get the latest Tweets from friends
	 *
	 * @access	private
	 * @return	html		Parsed and generated HTML
	 */
	private function getFriendsTimeline()
	{
		//-----------------------------------------
		// Get my friends timeline
		//-----------------------------------------
		
		$friendsTimeline = $this->twitterAuth->OAuthRequest( 'http://twitter.com/statuses/friends_timeline.json', array(), 'GET' );
		
		$statuses = json_decode( $friendsTimeline, 1 );
		$friendsTL = array();
		
		if ( is_array( $statuses ) AND count( $statuses ) AND !isset( $statuses['error'] ) )
		{
			foreach ( $statuses as $status )
			{
				$this->parseTweets( &$status );
				
				$friendsTL['data'][] = $status;
			}
		}
		else if ( isset( $statuses['error'] ) )
		{
			$friendsTL['error'] = $statuses['error'];
		}
		
		$friendsTL['html'] = $this->registry->getClass( 'output' )->getTemplate( 'twitterBar' )->parseTweets( $friendsTL['data'] );
		
		return $friendsTL;
	}
	
	
	private function parseTweets( $status )
	{
		// Stop stealing my characters!
		$status['text'] = html_entity_decode( $status['text'], ENT_QUOTES, IPS_DOC_CHAR_SET );
		
		
		// Find links to twitpic and yfrog, and make pretty lightboxes
		$status['text'] = preg_replace( "#(^|\s|>|\])((?:http|https)://twitpic.com/([A-Za-z0-9]+))#is", "$1<a href='http://twitpic.com/show/full/$3' title='http://twitpic.com/$3' target='_blank' rel='lightbox[twitpic" . $this->sessionNr . "]'>$2</a>", $status['text'] );
		$status['text'] = preg_replace( "#(^|\s|>|\])((?:http|https)://yfrog.com/([A-Za-z0-9]+))#is", "$1<a href='http://yfrog.com/$3:iphone' title='http://yfrog.com/$3' target='_blank' rel='lightbox[twitpic" . $this->sessionNr . "]'>$2</a>", $status['text'] );
		
		// Hotlink URLs
		$status['text'] = preg_replace( "#(^|\s|>|\])((http|https|news|ftp)://(?!yfrog.com|twitpic.com)\w+[^\s\<\[]+)#is", "$1<a href='$2'>$2</a>", $status['text'] );

		
		// is it a reply to someone?
		$status['text'] = preg_replace( '#@([a-zA-Z0-9]+)#is', "@<a id='showuser_\\1' class='findUserTweets' href='https://twitter.com/\\1'>\\1</a> ", $status['text'] );
		
		
		// Hello? Is there a search hashtag in there?!?!?
		$status['text'] = preg_replace( '/#(.+?)(?:(\s|$))/is', "#<a class='searchTag' href='http://search.twitter.com/search?q=%23\\1'>\\1</a> ", $status['text'] );

		
		// Format the time
		$status['time'] = $this->registry->getClass( 'class_localization' )->getDate( strtotime( $status['created_at'] ), 'LONG', 0, 1 );
		
		return $status;
	}
	
	/**
	 * Update my status
	 *
	 * @access	private
	 * @return	void
	 */
	private function updateStatus()
	{
		$parameters = array();
		$response = array();
		
		// Are we replying to anyone?
		if ( isset( $this->request['in_reply_to'] ) AND !empty( $this->request['in_reply_to']) )
		{
			$parameters['in_reply_to_status_id'] = $this->request['in_reply_to'];
		}
		
		// Posting an empty Tweet wont help you with your empty life
		if ( empty( $this->request['status'] ) )
		{
			$this->returnJsonError( $this->lang->words['errorNoStatus'] );		
		}
		// Using $this->request ruined it all :(
		$parameters['status'] = $_POST['status'];
		
		$result = $this->twitterAuth->OAuthRequest( 'http://twitter.com/statuses/update.json', $parameters, 'POST' );
		$result = json_decode( $result, 1 );
		
		if ( $result['error'] )
		{
			$this->returnJsonError( $result['error'] );
		}
		
		$response['html'] = $this->populateSidebar( 1 );
		
		$this->returnJsonArray( $response );
	}

	/**
	 * (Un)favorite a twitter status
	 *
	 * @access	private
	 * @return	void
	 */
	private function favorite()
	{
		$response = array();
		
		// Valid favorite ID?
		if ( is_numeric( $this->request['favoriteID'] ) )
		{
			
			if ( $this->request['type'] == 'add' )
			{
				// Add the status to my favorites
				$this->twitterAuth->OAuthRequest( 'http://twitter.com/favorites/create/' . $this->request['favoriteID'] . '.json', array(), 'POST' );
			
				$response['status'] = 'added';
			}
			else
			{
				// Remove it
				$this->twitterAuth->OAuthRequest( 'http://twitter.com/favorites/destroy/' . $this->request['favoriteID'] . '.json', array(), 'POST' );
			
				$response['status'] = 'removed';
			}
		}
		else
		{
			// Someone ALWAYS have to try and break the system! WHY?!?
			$response['error'] = $this->lang->words['errorInvalidId'];
		}
		
		$this->returnJsonArray( $response );
	}
	
	/**
	 * Get my replies
	 *
	 * @access	private
	 * @return	void
	 */
	private function getMentions()
	{
		$replies = $this->twitterAuth->OAuthRequest( 'http://twitter.com/statuses/mentions.json', array(), 'GET' );
		
		$statuses = json_decode( $replies, 1 );
		$replyTL = array();
		$response = array();
				
		if ( is_array( $statuses ) AND count( $statuses ) AND !isset( $statuses['error'] ) )
		{
			foreach ( $statuses as $status )
			{
				$this->parseTweets( &$status );
				$replyTL[] = $status;
			}
			
			$response['html'] = $this->registry->getClass( 'output' )->getTemplate( 'twitterBar' )->parseTweets( $replyTL );
		}
		else if ( isset( $statuses['error'] ) )
		{
			$response['error'] = $statuses['error'];
		}
		else
		{
			$response['error'] = $this->lang->words['errorNoReplies'];
		}
		
		$this->returnJsonArray( $response );
	}	
	
	/**
	 * Get a users tweets
	 *
	 * @access	private
	 * @return	void
	 */
	private function getUserTweets()
	{
		$userTweets = $this->twitterAuth->OAuthRequest( 'http://twitter.com/statuses/user_timeline.json', array( 'screen_name' => $this->request['userName'] ), 'GET' );

		$statuses 	= json_decode( $userTweets, 1 );
		$userTL 	= array();
		$userData 	= array();
		$response 	= array();

		if ( is_array( $statuses ) AND count( $statuses ) AND ! isset( $statuses['error'] ) )
		{
			foreach ( $statuses as $status )
			{
				if ( ! count( $userData ) )
				{
					$status['user']['name'] 		= !empty( $status['user']['name'] ) 		? $status['user']['name'] 			: $status['user']['screen_name'];
					$status['user']['location'] 	= !empty( $status['user']['location'] ) 	? $status['user']['location'] 		: 'N/A';
					$status['user']['description'] 	= !empty( $status['user']['description'] ) 	? $status['user']['description'] 	: 'N/A';
					
					$userData = $status['user'];
					
					$response['userData']['id'] 		= $status['user']['id'];
					$response['userData']['screenName'] = $status['user']['screen_name'];
					$response['userData']['name'] 		= $status['user']['name'];
				}
				
				$this->parseTweets( &$status );
				
				$userTL[] = $status;
			}
			
			$response['html'] = $this->registry->getClass( 'output' )->getTemplate( 'twitterBar' )->parseUserTweets( $userTL, $userData );
		}
		else if ( isset( $statuses['error'] ) )
		{
			$response['error'] = $statuses['error'];
		}
		else
		{
			$response['error'] = $this->lang->words['errorNoUpdates'];
		}
				
		$this->returnJsonArray( $response );
	}	
	
	/**
	 * Get search result
	 *
	 * @access	private
	 * @return	void
	 */
	private function doSearch()
	{
		if ( $this->request['search'] )
		{
			$searchKeyword = urlencode( $this->request['search'] );
			
			$page = isset( $this->request['currentPage'] ) ? intval( $this->request['currentPage'] ) + 1 : 1;
			$search = $this->twitterAuth->http( 'http://search.twitter.com/search.json?rpp=50&page=' . $page . '&q=' . $searchKeyword );
			
			$result = json_decode( $search, 1 );
			$results = array();
			$response = array();
			
			if ( is_array( $result['results'] ) AND count( $result['results'] ) )
			{
				foreach ( $result['results'] as $r )
				{
					// Search & Main API doesn't use the same name on things
					$r['user'] = $r['author'];

					// Highlight the search terms
					$r['text'] = IPSText::searchHighlight( $r['text'], $this->request['search'] );
					
					$this->parseTweets( &$r );

					$r['source'] = html_entity_decode( $r['source'], ENT_QUOTES, IPS_DOC_CHAR_SET );
					
					$results[] = $r;
				}
				
				// Thansk God the search API return different values than the rest...
				$response['html'] = $this->registry->getClass( 'output' )->getTemplate( 'twitterBar' )->parseSearchResult( $results, $page );
			}
			else
			{
				$response['error'] = $this->lang->words['errorNoResults'];
			}
		}
		else
		{
			$response['error'] = $this->lang->words['errorSearchQuery'];
		}
		
		$this->returnJsonArray( $response );
	}
	
	private function getSingleStatus()
	{
		$id 		= is_numeric( $this->request['statusID'] ) ? $this->request['statusID'] : 0;
		$status 	= array();
		
		if ( ! $id )
		{
			$return = $this->lang->words['errorInvalidId'];
		}
		else
		{
			
			$singleStatus = $this->twitterAuth->OAuthRequest( 'http://twitter.com/statuses/show/' . $id . '.json', array(), 'GET' );

			$status = json_decode( $singleStatus, 1 );

			if ( is_array( $status ) AND count( $status ) AND !isset( $status['error'] ) )
			{
				$this->parseTweets( &$status );
				
				$return = $this->registry->getClass( 'output' )->getTemplate( 'twitterBar' )->singleStatusPopUp( $status );
			}
			else
			{
				$return = $status['error'];
			}
		}
		
		echo $return;
	}
	

	/**
	 * Get friends
	 * Yes, it's that simple!
	 *
	 * @return void
	 */
	private function getFriends()
	{
		$id 		= $this->request['userID'];
		$status 	= array();
		$type		= $this->request['type'] == 'friends' ? 'friends' : 'followers';
		
		if ( ! is_numeric( $id ) )
		{
			$return = $this->lang->words['errorInvalidId'];
		}
		else
		{
			$response = $this->twitterAuth->OAuthRequest( "http://twitter.com/statuses/{$type}.json", array( 'user_id' => $id, 'page' => 1 ), 'GET' );
			
			$friends = json_decode( $response, 1 );

			if ( is_array( $friends ) AND count( $friends ) AND !isset( $friends['error'] ) )
			{							
				$return = $this->registry->getClass( 'output' )->getTemplate( 'twitterBar' )->friendsPopup( $friends, 1 );
			}
			else
			{
				$return = $friends['error'];
			}
		}
		
		$this->returnHtml( $return );
	}
	
	/**
	 * Get MORE friends
	 *
	 * @return void
	 */
	private function getMoreFriends()
	{
		$id 		= $this->request['userID'];
		$page 		= isset( $this->request['currentPage'] ) ? intval( $this->request['currentPage'] ) + 1 : 1;
		$type		= $this->request['type'] == 'friends' ? 'friends' : 'followers';
	
		if ( ! is_numeric( $id ) )
		{
			$return = $this->lang->words['errorInvalidId'];
		}
		else
		{
			$response = $this->twitterAuth->OAuthRequest( "http://twitter.com/statuses/{$type}.json", array( 'user_id' => $id, 'page' => $page ), 'GET' );
			
			$friends = json_decode( $response, 1 );

			if ( is_array( $friends ) AND count( $friends ) AND !isset( $friends['error'] ) )
			{
				$return = $this->registry->getClass( 'output' )->getTemplate( 'twitterBar' )->moreFriends( $friends, $page );
				$this->returnJsonArray( array( 'html' => $return) );
			}
			else
			{
				$this->returnJsonError( $friends['error'] );
			}
		}
	}
	
	
	private function shortenUrl()
	{
		$return = array();
		$url = trim( $this->request['url'] );
		
		if ( stristr( $url, 'http' ) )
		{
			$url = urlencode( $url );
			
			$response = $this->twitterAuth->http( "http://api.bit.ly/shorten?version=2.0.1&longUrl={$url}&login={$this->bitly['login']}&apiKey={$this->bitly['apiKey']}" );
			$response = json_decode( $response, 1 );
			
			
			if ( $response['errorCode'] )
			{
				$return['error'] = $response['errorMessage'];
			}
			else
			{
				foreach ( $response['results'] as $url => $r )
				{
					$return['url'] = $r['shortUrl'];
					continue;
				}
			}
		}
		else
		{
			$return['error'] = $this->lang->words['invalidUrl'];
		}

		$this->returnJsonArray( $return );
	}
}