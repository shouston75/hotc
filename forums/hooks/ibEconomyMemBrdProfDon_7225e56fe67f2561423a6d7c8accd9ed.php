<?php

class ibEconomyMemBrdProfDon
{
    public $registry;
    
    public function __construct()
    {
        $this->registry = ipsRegistry::instance();
		$this->settings =& $this->registry->fetchSettings();
		$this->request  =& $this->registry->fetchRequest();
		$this->member   = $this->registry->member();
		$this->memberData =& $this->registry->member()->fetchMemberData();
    }
    
    public function getOutput()
    {
		#init
		$return = "";

		if ( $this->memberData['g_eco'] && $this->request['section'] != 'friends' && $this->request['module'] == 'profile' && $this->request['filter'] != 'ALL' )  
		{
			$return = $this->registry->output->getTemplate('ibEconomy')->memberProfileDonate($this->request['showuser']);
		}

		return $return;
    } 
}