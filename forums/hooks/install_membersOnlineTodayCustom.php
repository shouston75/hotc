<?php

class membersOnlineTodayCustom
{
	protected $registry;
	protected $DB;
	protected $cache;
	
	public function __construct( ipsRegistry $registry )
	{
		/* Make registry objects */
		$this->registry =  $registry;
		$this->DB       =  $this->registry->DB();
		$this->cache    =  $this->registry->cache();
	}
	
	public function install()
	{
		/* Delete our unused settings */
		$this->DB->delete( 'core_sys_conf_settings', "conf_key IN ('show_mot', 'mot_show_popup')" );
		
		/* Recount the group */
		$conf_group = $this->DB->buildAndFetch( array( 'select' => 'conf_title_id', 'from' => 'core_sys_settings_titles', 'where' => "conf_title_keyword='mot'" ) );
		$count      = $this->DB->buildAndFetch( array( 'select' => 'COUNT(*) AS cnt', 'from' => 'core_sys_conf_settings', 'where' => 'conf_group=' . $conf_group['conf_title_id'] ) );
		$this->DB->update( 'core_sys_settings_titles', array( 'conf_title_count' => $count['cnt'] ), 'conf_title_id=' . $conf_group['conf_title_id'] );
		
		/* Rebuild our settings */
		$this->cache->rebuildCache( 'settings', 'global' );
	}
}