<?php

if (ipsRegistry::$applications['forums']['app_long_version'] < 33000)
{
    class tapatalk_classPostForms extends classPostForms
    {
        public function sendOutQuoteNotifications( $post )
        {
            if ($this->settings['tapatalk_push']
                && (function_exists('curl_init') || ini_get('allow_url_fopen'))
                && file_exists( DOC_IPS_ROOT_PATH . $this->settings['tapatalk_directory'] . '/lib/class_push.php' )
                && $this->DB->checkForTable( 'tapatalk_users' ))
            {
                $classToLoad    = IPSLib::loadLibrary( DOC_IPS_ROOT_PATH . $this->settings['tapatalk_directory'] . '/lib/class_push.php', 'tapatalk_push' );
                $notifyLibrary  = new $classToLoad( $this->registry );
                $notifyLibrary->notifyTag( $post );
            }
            else if($this->DB->checkForTable( 'tapatalk_users' ))
            {
            	$notifyLibrary->notifyTag( $post ,array(),false);
            }
            parent::sendOutQuoteNotifications( $post );
        }
    }
}
else
{
    class tapatalk_classPostForms extends classPostForms
    {
        public function sendOutQuoteNotifications( $post, $subscriptionSentTo )
        {
            if ($this->settings['tapatalk_push']
                && (function_exists('curl_init') || ini_get('allow_url_fopen'))
                && file_exists( DOC_IPS_ROOT_PATH . $this->settings['tapatalk_directory'] . '/lib/class_push.php' )
                && $this->DB->checkForTable( 'tapatalk_users' ))
            {
                $classToLoad    = IPSLib::loadLibrary( DOC_IPS_ROOT_PATH . $this->settings['tapatalk_directory'] . '/lib/class_push.php', 'tapatalk_push' );
                $notifyLibrary  = new $classToLoad( $this->registry );
                $notifyLibrary->notifyTag( $post, $subscriptionSentTo );
            }
            else if($this->DB->checkForTable( 'tapatalk_users' ))
            {
            	$notifyLibrary->notifyTag( $post, $subscriptionSentTo , false);
            }
            parent::sendOutQuoteNotifications( $post, $subscriptionSentTo );
        }
    }
}