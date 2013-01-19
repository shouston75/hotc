<?php

class ibEconomySidebarBlocks
{
    public $registry;
    
    public function __construct()
    {
        $this->registry     =  ipsRegistry::instance();
        $this->DB           =  $this->registry->DB();
        $this->settings     =& $this->registry->fetchSettings();
        $this->request      =& $this->registry->fetchRequest();
        $this->lang         =  $this->registry->getClass('class_localization');
        $this->member       =  $this->registry->member();
        $this->memberData   =& $this->registry->member()->fetchMemberData();
        $this->cache        =  $this->registry->cache();
        $this->caches       =& $this->registry->cache()->fetchCaches(); 
 
        IPSText::getTextClass('bbcode')->parse_html		= 1;
		
		#load eco queries (which also loads ecoclass)
		if( !isset($this->registry->mysql_ibEconomy) )
		{
			require_once( IPSLib::getAppDir( 'ibEconomy' ) . '/sql/mysql_ibEconomy.php' );
			$this->registry->mysql_ibEconomy = new ibEconomyMySQL( $this->registry );
		}

		#our global ibEconomy Class
		require_once( IPSLib::getAppDir( 'ibEconomy' ) . "/sources/classes/class_global.php" );

		$this->ecoGlobal = new class_global( $this->registry );
    }
    
	public function getOutput()
	{
		$blocks = "";
		#no eco access?  no soup 4 u!
		if( !$this->memberData['g_eco'] )
		{
			return $blocks;
		}
		if( !$this->caches['ibEco_blocks'] )
		{
			$this->caches['ibEco_blocks'] = $this->cache->getCache('ibEco_blocks');
		}
 
		$blocks = $this->ecoGlobal->blocks();

		#output!
		return $blocks;
	}
}