<?php

class topicViewGooglePlusOne
{
	public $registry;
	
	public function __construct()
	{
		$this->registry =  ipsRegistry::instance();
		$this->request  =& $this->registry->fetchRequest();
	}
	
	public function getOutput()
	{
		if(!$this->registry->getClass('class_forums')->guestCanSeeTopic($this->request['f']))
		{
			return '';
		}
		
		$data_url = $this->registry->getClass('output')->fetchRootDocUrl();

		return '<div class="facebook-like google-plus-one" style="margin-top: 1px;"><script type="text/javascript" src="https://apis.google.com/js/plusone.js"></script><g:plusone size="medium" href="'.$data_url.'"></g:plusone></div>';
	}	
}