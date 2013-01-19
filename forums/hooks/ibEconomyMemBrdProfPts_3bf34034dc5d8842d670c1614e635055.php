<?php

class ibEconomyMemBrdProfPts
{
    public $registry;
    
    public function __construct()
    {
        $this->registry = ipsRegistry::instance();
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
			#init
			$member = array();

			#load eco queries (which also loads ecoclass)
			if( !isset($this->registry->mysql_ibEconomy) )
			{
				require_once( IPSLib::getAppDir( 'ibEconomy' ) . '/sql/mysql_ibEconomy.php' );
				$this->registry->mysql_ibEconomy = new ibEconomyMySQL( $this->registry );
			}

			$member['pts']  = $this->registry->mysql_ibEconomy->tallyPointsByVars( 'member', $this->request['showuser'] );
			$member['rank'] = $this->registry->mysql_ibEconomy->rankMembers( 'points', $this->request['showuser'] );
			
			$return = $this->registry->output->getTemplate('ibEconomy')->memberProfilePts($member);
		}

		return $return;
    } 
}