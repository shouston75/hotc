<?php

class ibEconomyGetPointFields
{
	public $registry;
	
	public function __construct()
	{
		$this->registry 	= ipsRegistry::instance();
		$this->settings     =& $this->registry->fetchSettings();
		$this->member       =  $this->registry->member();
        $this->memberData   =& $this->registry->member()->fetchMemberData();
		$this->DB           =  $this->registry->DB();
	}
	
	public function handleData($postData)
	{
		$additionalFields = array();
		if ($this->settings['eco_general_on'] && $this->settings['eco_pts_button_on'] && $this->settings['eco_general_pts_field'] != 'eco_points' && $this->memberData['g_eco'])
		{
			$additionalFields['members'] = array($this->settings['eco_general_pts_field']);
		}
				
		return $additionalFields;
	}	
}?>