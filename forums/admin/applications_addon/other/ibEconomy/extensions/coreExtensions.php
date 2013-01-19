<?php

/**
 * (e32) ibEconomy
 * Core Extensions
 * @ Global
 * + Permissions Masking
 * + Locating Capabilities
 */

$_PERM_CONFIG = array( 'Stock' );

class ibEconomyPermMappingStock
{
	/**
	 * Mapping of keys to columns
	 */
	private $mapping = array(
								'view'     => 'perm_view',
								'open'     => 'perm_2',
								'close'    => 'perm_3',
							);

	/**
	 * Mapping of keys to names
	 */
	private $perm_names = array(
								'view'     => 'View Stock',
								'open'     => 'Purchase Shares',
								'close'    => 'Sell Shares',//(changed in 1.4.2)
							);

	/**
	 * Mapping of keys to background colors for the form
	 */
	private $perm_colors = array(
								'view'     => '#fff0f2',
								'open'     => '#effff6',
								'close'    => '#f0f1ff',
							);

	/**
	 * Method to pull the key/column mapping
	 */
	public function getMapping()
	{
		return $this->mapping;
	}

	/**
	 * Method to pull the key/name mapping
	 */
	public function getPermNames()
	{
		return $this->perm_names;
	}

	/**
	 * Method to pull the key/color mapping
	 */
	public function getPermColors()
	{
		return $this->perm_colors;
	}
}

$_PERM_CONFIG = array( 'Bank' );

class ibEconomyPermMappingBank
{
	/**
	 * Mapping of keys to columns
	 */
	private $mapping = array(
								'view'     => 'perm_view',
								'open'     => 'perm_2',
								'close'    => 'perm_3',
								'loans'    => 'perm_4',
							);

	/**
	 * Mapping of keys to names
	 */
	private $perm_names = array(
								'view'     => 'View Bank',
								'open'     => 'Open New Account',
								'close'    => 'Close Account (for refund if allowed)',
								'loans'    => 'Request Loan',
							   );

	/**
	 * Mapping of keys to background colors for the form
	 */
	private $perm_colors = array(
								'view'     => '#fff0f2',
								'open'     => '#effff6',
								'close'    => '#f0f1ff',
								'loans'    => '#fffaee',
							    );

	/**
	 * Method to pull the key/column mapping
	 */
	public function getMapping()
	{
		return $this->mapping;
	}

	/**
	 * Method to pull the key/name mapping
	 */
	public function getPermNames()
	{
		return $this->perm_names;
	}

	/**
	 * Method to pull the key/color mapping
	 */
	public function getPermColors()
	{
		return $this->perm_colors;
	}
}

$_PERM_CONFIG = array( 'Long_term' );

class ibEconomyPermMappingLong_term
{
	/**
	 * Mapping of keys to columns
	 */
	private $mapping = array(
								'view'     => 'perm_view',
								'open'     => 'perm_2',
								'close'    => 'perm_3',
							);

	/**
	 * Mapping of keys to names
	 */
	private $perm_names = array(
								'view'     => 'View Investment',
								'open'     => 'Invest',
								'close'    => 'Cashout (early if allowed)',
							   );

	/**
	 * Mapping of keys to background colors for the form
	 */
	private $perm_colors = array(
								'view'     => '#fff0f2',
								'open'     => '#effff6',
								'close'    => '#f0f1ff',
							    );

	/**
	 * Method to pull the key/column mapping
	 */
	public function getMapping()
	{
		return $this->mapping;
	}

	/**
	 * Method to pull the key/name mapping
	 */
	public function getPermNames()
	{
		return $this->perm_names;
	}

	/**
	 * Method to pull the key/color mapping
	 */
	public function getPermColors()
	{
		return $this->perm_colors;
	}
}

$_PERM_CONFIG = array( 'cc' );

class ibEconomyPermMappingCC
{
	/**
	 * Mapping of keys to columns
	 */
	private $mapping = array(
								'view'     => 'perm_view',
								'open'     => 'perm_2',
								'close'    => 'perm_3',
							);

	/**
	 * Mapping of keys to names
	 */
	private $perm_names = array(
								'view'     => 'View Credit-Card',
								'open'     => 'Get New Card',
								'close'    => 'Close Card (sell back if allowed)',
							   );

	/**
	 * Mapping of keys to background colors for the form
	 */
	private $perm_colors = array(
								'view'     => '#fff0f2',
								'open'     => '#effff6',
								'close'    => '#f0f1ff',
							    );

	/**
	 * Method to pull the key/column mapping
	 */
	public function getMapping()
	{
		return $this->mapping;
	}

	/**
	 * Method to pull the key/name mapping
	 */
	public function getPermNames()
	{
		return $this->perm_names;
	}

	/**
	 * Method to pull the key/color mapping
	 */
	public function getPermColors()
	{
		return $this->perm_colors;
	}
}

$_PERM_CONFIG = array( 'shop_cat' );

class ibEconomyPermMappingShop_cat
{
	/**
	 * Mapping of keys to columns
	 */
	private $mapping = array(
								'view'     => 'perm_view',
							);

	/**
	 * Mapping of keys to names
	 */
	private $perm_names = array(
								'view'     => 'View Shop Category',
							   );

	/**
	 * Mapping of keys to background colors for the form
	 */
	private $perm_colors = array(
								'view'     => '#fff0f2',
							    );

	/**
	 * Method to pull the key/column mapping
	 */
	public function getMapping()
	{
		return $this->mapping;
	}

	/**
	 * Method to pull the key/name mapping
	 */
	public function getPermNames()
	{
		return $this->perm_names;
	}

	/**
	 * Method to pull the key/color mapping
	 */
	public function getPermColors()
	{
		return $this->perm_colors;
	}
}

$_PERM_CONFIG = array( 'shopitem' );

class ibEconomyPermMappingShopitem
{
	/**
	 * Mapping of keys to columns
	 */
	private $mapping = array(
								'view'    => 'perm_view',
								'open'    => 'perm_2',
								'sell'    => 'perm_3',
								'trade'   => 'perm_4',
							);

	/**
	 * Mapping of keys to names
	 */
	private $perm_names = array(
								'view'    => 'View Item',
								'open'    => 'Purchase Item',
								'sell'    => 'Sell Back',
								'trade'   => 'Trade Item',
							   );

	/**
	 * Mapping of keys to background colors for the form
	 */
	private $perm_colors = array(
								'view'    => '#fff0f2',
								'open'    => '#effff6',
								'sell'    => '#f0f1ff',
								'trade'   => '#fffaee',
							    );

	/**
	 * Method to pull the key/column mapping
	 */
	public function getMapping()
	{
		return $this->mapping;
	}

	/**
	 * Method to pull the key/name mapping
	 */
	public function getPermNames()
	{
		return $this->perm_names;
	}

	/**
	 * Method to pull the key/color mapping
	 */
	public function getPermColors()
	{
		return $this->perm_colors;
	}
}

$_PERM_CONFIG = array( 'block' );

class ibEconomyPermMappingBlock
{
	/**
	 * Mapping of keys to columns
	 */
	private $mapping = array( 'view'    => 'perm_view' );

	/**
	 * Mapping of keys to names
	 */
	private $perm_names = array('view'    => 'View Block' );
	
	/**
	 * Mapping of keys to background colors for the form
	 */
	private $perm_colors = array('view'   => '#fff0f2');

	/**
	 * Method to pull the key/column mapping
	 */
	public function getMapping()
	{
		return $this->mapping;
	}

	/**
	 * Method to pull the key/name mapping
	 */
	public function getPermNames()
	{
		return $this->perm_names;
	}

	/**
	 * Method to pull the key/color mapping
	 */
	public function getPermColors()
	{
		return $this->perm_colors;
	}
}

class publicSessions__ibEconomy
{
	/**
	* Return session variables for this application
	*
	* current_appcomponent, current_module and current_section are automatically
	* stored. This function allows you to add specific variables in.
	*/
	public function getSessionVariables()
	{
		#init
		$array = array( 'location_1_type'   => '',
						'location_1_id'     => 0,
						'location_2_type'   => '',
						'location_2_id'     => 0, 
						'location_3_type'   => '',
						'location_4_type'   => '',);
												
		$array = array( 
						 'location_1_type'   => ipsRegistry::$request['tab'],
						 'location_1_id'     => intval(ipsRegistry::$request['id']) ? intval(ipsRegistry::$request['id']) : 0,
						 'location_2_type'   => ipsRegistry::$request['area'],
						 'location_2_id'     => 0,
						 'location_3_type'   => ipsRegistry::$request['type'],
						 'location_3_id'   	 => (ipsRegistry::$request['bank_type'] == 'Savings') ? 1 : 2,
						 );	
	
		return $array;
	}

	protected $registry;
	
	/**
	* Parse/format the online list data for the records
	*/
	public function parseOnlineEntries( $rows )
	{
		if( !is_array($rows) OR !count($rows) )
		{
			return $rows;
		}
		
		#init
		$final = array();
		$varChar10FixMap = array(   'find_membe' 	=> 'find_member',
									'transactio' 	=> 'transactions',
									'my_overvie' 	=> 'my_overview',
									'my_shopite' 	=> 'my_shopitems',
									'find_membe' 	=> 'find_member',
									'my_overvie' 	=> 'my_overview'
								);
		
		#load lang
		ipsRegistry::getClass('class_localization')->loadLanguageFile( array( 'public_ibEconomy' ), 'ibEconomy' );		
		
		#create where he be links and texts for Economers!
		foreach( $rows as $row )
		{
			$row['location_1_type'] = ( $varChar10FixMap[ $row['location_1_type'] ] ) ? $varChar10FixMap[ $row['location_1_type'] ] : $row['location_1_type'];
			$row['location_2_type'] = ( $varChar10FixMap[ $row['location_2_type'] ] ) ? $varChar10FixMap[ $row['location_2_type'] ] : $row['location_2_type'];
			
			if( $row['current_appcomponent'] == 'ibEconomy' )
			{
				if ( $row['location_1_type'] )
				{
					$middleText = ( $row['location_1_type'] == 'me' ) ? ipsRegistry::getClass('class_localization')->words['me_where_pre'] : '';
					
					if ( $row['location_2_type'] )
					{
						if ( $row['location_1_id'] && $row['location_3_type'] )
						{
							if ( $row['location_3_type'] == 'bank' )
							{
								$bankType = ( $row['location_3_id'] == 1 ) ? '&amp;bank_type=Savings': '&amp;bank_type=Checking';
							}
							$row['where_line']		= ipsRegistry::getClass('class_localization')->words['viewing'].' '.$middleText.' '.ipsRegistry::$settings['eco_general_name'];
							$row['where_line_more']	= ipsRegistry::getClass('class_localization')->words[ $row['location_3_type'] ];
							$row['where_link']		= 'app=ibEconomy&amp;tab='.$row['location_1_type'].'&amp;area='.$row['location_2_type'].'&amp;type='.$row['location_3_type'].'&amp;id='.$row['location_1_id'].$bankType;						
						}
						else
						{
							$row['where_line']		= ipsRegistry::getClass('class_localization')->words['viewing'].' '.$middleText.' '.ipsRegistry::$settings['eco_general_name'];
							$row['where_line_more']	= ipsRegistry::getClass('class_localization')->words[ $row['location_2_type'].'_area'];
							$row['where_link']		= 'app=ibEconomy&amp;tab='.$row['location_1_type'].'&amp;area='.$row['location_2_type'];
						}
					}
					else
					{
						$row['where_line']		= ipsRegistry::getClass('class_localization')->words['browsing'].' '.$middleText.' '.ipsRegistry::$settings['eco_general_name'];
						$row['where_line_more']	= ipsRegistry::getClass('class_localization')->words[ $row['location_1_type'].'_tab'];
						$row['where_link']		= 'app=ibEconomy&amp;tab='.$row['location_1_type'];
					}
				}
				else
				{
					$row['where_line']		= ipsRegistry::getClass('class_localization')->words['viewing'].' '.ipsRegistry::$settings['eco_general_name'];
					$row['where_line_more']	= ipsRegistry::getClass('class_localization')->words['frontpage'];
					$row['where_link']		= 'app=ibEconomy';
				}
			}
			
			$final[ $row['id'] ] = $row;
		}

		return $final;
	}
}