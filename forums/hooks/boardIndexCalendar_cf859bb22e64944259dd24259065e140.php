<?php

class boardIndexCalendar
{
	public $registry;
	
	public function __construct()
	{
        /* Make registry objects */
		$this->registry		=  ipsRegistry::instance();
		$this->DB			=  $this->registry->DB();
		$this->settings		=& $this->registry->fetchSettings();
		$this->lang			=  $this->registry->getClass('class_localization');
	}
	
	public function getOutput()
	{
		/* Make sure the calednar is installed and enabled */
		if( ! IPSLib::appIsInstalled( 'calendar' ) )
		{
			return '';
		}
		
		/* Load language  */
		$this->registry->class_localization->loadLanguageFile( array( 'public_calendar' ), 'calendar' );

		/* Load calendar library */		
		require_once( IPSLib::getAppDir( 'calendar' ) .'/modules_public/calendar/calendars.php' );
		$cal = new public_calendar_calendar_calendars( $this->registry );
		$cal->makeRegistryShortcuts( $this->registry );
		$cal->initCalendar( true );
		
		if( ! is_array( $cal->calendar ) OR ! count( $cal->calendar ) OR ! $cal->can_read )
		{
			return'';
		}

		/* Return calendar */
		return "<div id='mini_calendars' class='calendar_wrap'>". $cal->getMiniCalendar( date('n'), date('Y') ) . '</div><br />';
	}
}