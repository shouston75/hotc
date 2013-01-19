class ibEconomyMemberPane extends skin_global(~id~)
{
	public function userInfoPane( $author, $contentid, $options )
	{	
		$output = parent::userInfoPane( $author, $contentid, $options );
		
		#need to add new setting to show shop item box in topics or not
		#perms and what not...
		if ( !$author['member_id'] || !$this->settings['eco_general_on'] || !$this->settings['eco_shopitems_on'] || !$author['g_eco'] || !$this->memberData['g_eco'] || !$this->settings['eco_show_my_items_on'])
		{
			return $output;	
		}
		
		#load eco queries (which also loads ecoclass)
		if( !isset($this->registry->mysql_ibEconomy) )
		{
			require_once( IPSLib::getAppDir( 'ibEconomy' ) . "/sql/mysql_ibEconomy.php" );
			$this->registry->setClass( 'mysql_ibEconomy', new ibEconomyMySQL( $this->registry ) );	
		}
		
		
		$zeroOrTwo 				= ( $this->settings['eco_pts_button_decimal'] ) ? 2 : 0;
		$author['eco_points'] 	= ( $this->request['app'] == 'members' ) ? $author[$this->settings['eco_general_pts_field']] : $author['eco_points'];
		$author['eco_points'] 	= $this->registry->getClass('class_localization')->formatNumber($author['eco_points'], $zeroOrTwo);	
		
		#this is where we will display shop items....................
		$miniShopItemsBox = $this->registry->ecoclass->miniBoxedShopItems($author['member_id'], $this->settings['eco_show_my_items_max']);
		
		$output .= $this->registry->output->getTemplate('ibEconomy')->memberInfoPane($author, $miniShopItemsBox);

		return $output;		
	}

	public function includeJS( $jsModules )
	{	
		$output = parent::includeJS ( $jsModules );

		$output .= "<script type='text/javascript' src='{$this->settings['public_dir']}js/3rd_party/ibEconomy/hookJS/ips.ibEconomyMemPane.js'></script>";

		return $output;
	}
}