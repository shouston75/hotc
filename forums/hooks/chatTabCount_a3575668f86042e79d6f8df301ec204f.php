<?php

class chatTabCount
{
	/**
	 * Registry Object
	 *
	 * @access	protected
	 * @var		object
	 */
	protected $registry;
	
	public function __construct()
	{
		/* Make registry objects */
		$this->registry	= ipsRegistry::instance();
	}
	
	public function getOutput()
	{
		if( file_exists( IPSLib::getAppDir('ipchat') . '/sources/hooks.php' ) )
		{
			require_once( IPSLib::getAppDir('ipchat') . '/sources/hooks.php' );
			$chatting	= new hooksApi( $this->registry );
			return $chatting->getChatTabCount();
		}
	}
}