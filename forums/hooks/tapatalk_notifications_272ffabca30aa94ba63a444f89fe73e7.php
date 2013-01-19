<?php

class tapatalk_notifications extends notifications
{
    static public $post = array();
    static public $alreadyNotifiedUids = array();
    
    public function sendNotification()
    {
    	$push_status = false;
    	if(!empty($this->settings['tapatalk_push']) && (function_exists('curl_init') || ini_get('allow_url_fopen'))
            && file_exists( DOC_IPS_ROOT_PATH . $this->settings['tapatalk_directory'] . '/lib/class_push.php' ))
    	{
    		$push_status = true;
    	}
        if ( $this->DB->checkForTable( 'tapatalk_users' ))
        {
            $classToLoad    = IPSLib::loadLibrary( DOC_IPS_ROOT_PATH . $this->settings['tapatalk_directory'] . '/lib/class_push.php', 'tapatalk_push' );
            $notifyLibrary  = new $classToLoad( $this->registry );
            
            $touids = array();
            $recipients = empty( $this->_recipients ) ? array( $this->_member ) : $this->_recipients;
            
            foreach ( $recipients as $r )
            {
                if ( is_array( $r ) )
                {
                    if( $r['member_banned'] || ! $r['member_id']) continue;
                    if (in_array($r['member_id'], self::$alreadyNotifiedUids)) continue;
                    $touids[] = $r['member_id'];
                    self::$alreadyNotifiedUids[] = $r['member_id'];
                }
            }
            
            if (!empty($touids))
            {
                switch ($this->_notificationKey)
                {
                    case 'new_likes':
                        if ($this->request['type'] == 'pid')
                        {
                            $postid = intval( $this->request['type_id'] );
                            $post = $this->registry->getClass('topics')->getPostById( $postid );
                            $notifyLibrary->notifyPost( $post, $touids, 'like' ,$push_status);
                        }
                        break;
                    case 'new_reply':
                    case 'followed_topics':
                        $notifyLibrary->notifyPost( $this->getCurrentPost(), $touids, 'sub' ,$push_status);
                        break;
                    case 'post_quoted':
                        // user got tag notification don't need to get quoted notification again
                        $touids = array_diff($touids, $notifyLibrary->getTagUids());
                        $notifyLibrary->notifyPost( $this->getCurrentPost(), $touids, 'quote' ,$push_status);
                        break;
                    case 'new_private_message':
                    case 'reply_private_message':
                        $msg_topic_id = $this->_metaData['meta_id'];
                        if (empty($msg_topic_id))
                        {
                            preg_match('/topicID=(\d+)/', $this->_notificationUrl, $match);
                            $msg_topic_id = $match[1];
                        }
                        
                        if ($msg_topic_id)
                        {
                            $GLOBALS['new_conv_id'] = $msg_topic_id;
                            $convData = $this->DB->buildAndFetch( array( 'select' => '*',
                                                                         'from'   => 'message_topics',
                                                                         'where'  => 'mt_id=' . intval( $msg_topic_id ) ) );
                            $notifyLibrary->notifyConv( $convData, $touids ,'conv',$push_status);
                        }
                        break;
                    case 'new_topic':
                    case 'followed_forums':
                        $notifyLibrary->notifyPost( $this->getCurrentPost(), $touids, 'newtopic',$push_status);
                        break;
                }
            }
        }

        parent::sendNotification( $post, $subscriptionSentTo );
    }
    
    public function getCurrentPost()
    {
        if (empty(self::$post))
        {
            if ($this->request['t'])
            {
                $topic_id = intval( $this->request['t'] );
                $topic = $this->DB->buildAndFetch( array( 'select' => '*', 'from' => 'topics', 'where' => 'tid=' . $topic_id ) );
                $post = $this->DB->buildAndFetch( array(
                                                        'select'    => '*',
                                                        'from'      => 'posts',
                                                        'where'     => 'topic_id=' . $topic_id . ' and author_id=' . $this->memberData['member_id'],
                                                        'order'     => 'post_date desc',
                                                        'limit'     => array( 1 ) ));
                self::$post = array(
                    'topic_id'  => $topic_id,
                    'title'     => $topic['title'],
                    'pid'       => $post['pid'],
                    'post_date' => $post['post_date'],
                );
            }
            else if ($this->request['f'])
            {
                $forum_id = intval( $this->request['f'] );
                $topic = $this->DB->buildAndFetch( array(
                                                        'select'    => '*',
                                                        'from'      => 'topics',
                                                        'where'     => 'forum_id=' . $forum_id . ' and starter_id=' . $this->memberData['member_id'],
                                                        'order'     => 'start_date desc',
                                                        'limit'     => array( 1 ) ));
                self::$post = array(
                    'topic_id'  => $topic['tid'],
                    'title'     => $topic['title'],
                    'pid'       => 0,
                    'post_date' => $topic['start_date'],
                );
            }
        }
        
        return self::$post;
    }
}