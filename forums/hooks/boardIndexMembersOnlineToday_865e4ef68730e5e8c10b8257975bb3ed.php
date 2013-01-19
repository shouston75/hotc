<?php

class boardIndexMembersOnlineToday
{
	protected $registry;
	protected $DB;
	protected $settings;
	protected $lang;
	protected $memberData;
	protected $cache;
	
	public function __construct()
	{
		/* Make registry objects */
		$this->registry   =  ipsRegistry::instance();
		$this->DB         =  $this->registry->DB();
		$this->settings   =& $this->registry->fetchSettings();
		$this->lang       =  $this->registry->getClass('class_localization');
		$this->memberData =& $this->registry->member()->fetchMemberData();
		$this->cache      =  $this->registry->cache();
	}
	
	public function getOutput()
	{
		/* INIT */
		$count    = 0;
		$state    = 'expanded';
		$members  = array();
		$return   = "";
		$exclude  = "";
		$numcheck = ( $this->settings['mot_style'] == 'today' ) ? 172800 : 86400;
		
		/* Show the list? */
		if ( IPSMember::isInGroup( $this->memberData, explode( ",", $this->settings['g_view_mot'] ) ) )
		{
			/* Let's try not to hose the stats */
			$stats = $this->cache->getCache('stats');
			
			/* Excluding any groups? */
			if ( $this->settings['g_exclude_mot'] != '' )
			{
				$exclude = ' AND m.member_group_id NOT IN ('.$this->settings['g_exclude_mot'].')';
			}
			
			/* Query for the members */
			$this->DB->build( array( 'select'   => 'm.member_id, m.members_display_name, m.member_group_id, m.last_activity, m.members_seo_name',
									 'from'     => array( 'members' => 'm' ),
									 'where'    => "m.member_id <> 0 AND m.members_display_name <> '' AND m.last_activity > ".time()." - {$numcheck}{$exclude}",
									 'add_join' => array( 0 => array( 'from'   => array( 'groups' => 'g' ),
																	  'where'  => 'm.member_group_id=g.g_id',
																	  'type'   => 'left' ) ),
																	  'order'  => "m.members_display_name ASC",
							)	   );
			$outer = $this->DB->execute();
			
			/* Has anyone been online in a while? */
			while ( $user = $this->DB->fetch( $outer ) )
			{
				/* Check if they've been online today */
				if ( $user['last_activity'] > 0 )
				{
					if ( $this->settings['mot_style'] == '24hr' || $this->lang->getDate( $user['last_activity'], 'DATE', 1 ) == $this->lang->getDate( time(), 'DATE', 1 ) )
					{
						$link = IPSMember::makeProfileLink( IPSMember::makeNameFormatted( $user['members_display_name'], $user['member_group_id'] ), $user['member_id'], $user['members_seo_name'] );
						$link = str_replace( "<a ", "<a title=\"" . $this->lang->words['hookLangLastActive'] . ": " . $this->lang->getDate( $user['last_activity'], 'LONG' ) . "\" ", $link );
						
						$members[] = $link;
						$count++;
					}
				}
			}
			
			/* Did we break our record? */
			if ( $count >= $stats['most_mem_day'] )
			{
				$stats['most_mem_day']  = $count;
				$stats['most_mem_date'] = time();
				
				$this->cache->setCache( 'stats', $stats, array( 'array' => 1, 'deletefirst' => 1 ) );
			}
			
			/* Build our language string */
			$this->lang->words['hookLangOnlineMostEver'] = str_replace( array( "<#COUNT#>", "<#DATE#>" ) , array( $stats['most_mem_day'], $this->lang->getDate( $stats['most_mem_date'], 'DATE', 1 ) ) , $this->lang->words['hookLangOnlineMostEver'] );
			
			/* Sort out the expand/collapse */
			if ( $this->settings['expcol_mot'] )
			{
				$state = ( IPSCookie::get( "membersOnlineToday" ) == 1 ) ? 'collapsed' : 'expanded';
			}
			
			/* Build the HTML */
			$return .= $this->registry->output->getTemplate('boards')->hookMembersOnlineToday( $members, $count, $state );
		}
		
		/* Finally, output the skin template */
		return $return;
	}
}