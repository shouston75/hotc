<?php

/**
 * (e32) ibEconomy
 * mySql File
 * @ EVERYWHERE ibEconomy
 */

class ibEconomyMySQL
{
	/**
	 * Constructor
	 */
	public function __construct( ipsRegistry $registry )
	{
		#make objects
		$this->registry = $registry;
		$this->DB       = $this->registry->DB();
		$this->settings =& $this->registry->fetchSettings();
		$this->request  =& $this->registry->fetchRequest();
		$this->lang     = $this->registry->getClass('class_localization');
		$this->member   = $this->registry->member();
		$this->memberData =& $this->registry->member()->fetchMemberData();
		$this->cache    = $this->registry->cache();
		$this->caches   =& $this->registry->cache()->fetchCaches();
		
		#Master Public ibEconomy Class
		if( !isset($this->registry->ecoclass) )
		{
			require_once( IPSLib::getAppDir( 'ibEconomy' ) . "/sources/ecoclass.php" );
			$this->registry->setClass( 'ecoclass', new class_ibEconomy( $this->registry ) );
		}
	}
	
	/**
	 * Query those banks, ya'll
	 */
	public function queryBanks( $howMany, $ids, $type, $start )
	{
		#init
		if ( $howMany == 'all' )
		{
			$where    	= ( is_array($ids) ) ? 'eb.b_id IN (' . implode( ',', $ids ) . ')' : 'eb.b_on = 1';
			$end		= ( $type == 'cache' ) ? 999 : intval($this->settings['eco_general_pp']);
		}
		else
		{
			$where		= 'eb.b_on = 1 AND eb.b_id = '.$ids;
			$end		= 1;
		}
		
		#grab all the banks, and count bank account holders and sum bank funds
		$this->DB->build( array( 	'select'	=> 'eb.*',
									'from'		=> array( 'eco_banks' => 'eb' ),
									'group'		=> 'eb.b_id',
									'order'		=> 'eb.b_position',
									'where'  	=> $where,
									'limit'		=> array( $start, $end ),
									'add_join'	=> array(
														0 => array( 'select' 	=> 'COUNT(epc.p_member_id) as c_total, SUM(epc.p_amount) as c_funds',
																		'from'	=> array( 'eco_portfolio' => 'epc' ),
																		'where'	=> "epc.p_type_id = eb.b_id and epc.p_type_class='checking' and epc.p_type='bank'",
																		'type'	=> 'left',
																  ),
														1 => array( 'select' 	=> 'COUNT(eps.p_member_id) as s_total, SUM(eps.p_amount) as s_funds',
																		'from'	=> array( 'eco_portfolio' => 'eps' ),
																		'where'	=> "eps.p_type_id = eb.b_id and eps.p_type_class='savings' and eps.p_type='bank'",
																		'type'	=> 'left',
																  ),
														2 => array( 'select'	=> 'p.*',
																		'from'	=> array( 'permission_index' => 'p' ),
																		'where' => "p.app = 'ibEconomy' AND p.perm_type='bank' AND p.perm_type_id=eb.b_id",
																		'type'	=> 'left',
																  ),
														3 => array( 'select'	=> 'COUNT(epl.p_member_id) as loaners, SUM(epl.p_amount) as outstanding_loan_amt',
																		'from'	=> array( 'eco_portfolio' => 'epl' ),
																		'where'	=> "epl.p_type_id = eb.b_id and epl.p_type_class='loan'",
																		'type'	=> 'left',
																  )																
														)
							)		);
		$this->DB->execute();

		if ( $howMany == 'one' )
		{
			if ( $this->DB->getTotalRows() )
			{
				while ( $row = $this->DB->fetch() )
				{
					// $row['c_total']  = $row['c_total']/2;
					// $row['s_total']  = $row['s_total']/2;
					// $row['c_funds']  = $row['c_funds']/2;
					// $row['s_funds']  = $row['s_funds']/2;
					// $row['loaners']  = $row['loaners']/4;
					$row['outstanding_loan_amt']  = $row['outstanding_loan_amt'];
					
					$row['total_accts']		= $row['c_total'] + $row['s_total'];
					$row['total_funds']		= $row['c_funds'] + $row['s_funds'];
					
					$bank = $row;
				}
			}
			
			return $bank;
		}
	}
	
	/**
	 * Query those stocks, ya'll
	 */
	public function queryStocks( $howMany, $ids, $type, $start )
	{
		#init
		if ( $howMany == 'all' )
		{
			$where    	= ( is_array($ids) ) ? 'es.s_id IN (' . implode( ',', $ids ) . ')' : 'es.s_on = 1';
			$end		= ( $type == 'cache' ) ? 999 : intval($this->settings['eco_general_pp']);
		}
		else
		{
			$where		= 'es.s_on = 1 AND es.s_id = '.$ids;
			$end		= 1;
		}
		
		#grab all the stocks, and count share holders and sum shares
		$q = $this->DB->build( array( 	'select'	=> 'es.*',
									'from'		=> array( 'eco_stocks' => 'es' ),
									'group'		=> 'es.s_id',
									'order'		=> 'es.s_position',
									'where'  	=> $where,
									'limit'		=> array( $start, $end ),
									'add_join'	=> array(
														0 => array( 'select' 	=> 'COUNT(epc.p_member_id) as share_holders, SUM(epc.p_amount) as total_share_value',
																		'from'	=> array( 'eco_portfolio' => 'epc' ),
																		'where'	=> "epc.p_type_id = es.s_id and epc.p_type='stock'",
																		'type'	=> 'left',
																  ),
														1 => array( 'select' 	=> 'm.members_display_name, m.member_group_id, m.member_id',
																		'from'	=> array( 'members' => 'm' ),
																		'where'	=> "m.member_id = es.s_type_var_value and es.s_type = 'member'",
																		'type'	=> 'left',
																  ),
														2 => array( 'select'	=> 'p.*',
																		'from'	=> array( 'permission_index' => 'p' ),
																		'where' => "p.app = 'ibEconomy' AND p.perm_type='stock' AND p.perm_type_id=es.s_id",
																		'type'	=> 'left',
																  ),																	  
														)
							)		);
		return $this->DB->execute($q);
		
		if ( $howMany == 'one' )
		{
			if ( $this->DB->getTotalRows() )
			{
				while ( $row = $this->DB->fetch() )
				{
					$stock = $row;
				}
			}
			
			return $stock;
		}
	}	
	
	/**
	 * Query those ccs, ya'll
	 */
	public function queryCCs( $howMany, $ids, $type, $start )
	{
		#init
		if ( $howMany == 'all' )
		{
			$where    	= ( is_array($ids) ) ? 'ecc.cc_id IN (' . implode( ',', $ids ) . ')' : 'ecc.cc_on = 1';
			$end		= ( $type == 'cache' ) ? 999 : intval($this->settings['eco_general_pp']);
		}
		else
		{
			$where		= 'ecc.cc_on = 1 AND ecc.cc_id = '.$ids;
			$end		= 1;
		}
		
		#grab all the ccs, and count account holders and sum cc balancesds
		$this->DB->build( array( 	'select'	=> 'ecc.*',
									'from'		=> array( 'eco_credit_cards' => 'ecc' ),
									'group'		=> 'ecc.cc_id',
									'order'		=> 'ecc.cc_position',
									'where'  	=> $where,
									'limit'		=> array( $start, $end ),
									'add_join'	=> array(
														0 => array( 'select' 	=> 'COUNT(epc.p_member_id) as card_holders, SUM(epc.p_amount) as funds',
																		'from'	=> array( 'eco_portfolio' => 'epc' ),
																		'where'	=> "epc.p_type_id = ecc.cc_id and epc.p_type='cc'",
																		'type'	=> 'left',
																  ),
														1 => array( 'select'	=> 'p.*',
																		'from'	=> array( 'permission_index' => 'p' ),
																		'where' => "p.app = 'ibEconomy' AND p.perm_type='cc' AND p.perm_type_id=ecc.cc_id",
																		'type'	=> 'left',
																  ),																	  
														)
							)		);
		$this->DB->execute();
		
		if ( $howMany == 'one' )
		{
			if ( $this->DB->getTotalRows() )
			{
				while ( $row = $this->DB->fetch() )
				{
					$cc = $row;
				}
			}
			
			return $cc;
		}
	}
	
	/**
	 * Query those long-terms, ya'll
	 */
	public function queryLTs( $howMany, $ids, $type, $start )
	{
		#init
		if ( $howMany == 'all' )
		{
			$where    	= ( is_array($ids) ) ? 'elt.lt_id IN (' . implode( ',', $ids ) . ')' : 'elt.lt_on = 1';
			$end		= ( $type == 'cache' ) ? 999 : intval($this->settings['eco_general_pp']);
		}
		else
		{
			$where		= 'elt.lt_on = 1 AND elt.lt_id = '.$ids;
			$end		= 1;
		}
		
		#grab all the long-terms, and count investors and sum investment value
		$this->DB->build( array( 	'select'	=> 'elt.*',
									'from'		=> array( 'eco_long_terms' => 'elt' ),
									'group'		=> 'elt.lt_id',
									'order'		=> 'elt.lt_position',
									'where'  	=> $where,
									'limit'		=> array( $start, $end ),
									'add_join'	=> array(
														0 => array( 'select' 	=> 'COUNT(epc.p_member_id) as investors, SUM(epc.p_amount) as total_invested',
																		'from'	=> array( 'eco_portfolio' => 'epc' ),
																		'where'	=> "epc.p_type_id = elt.lt_id and epc.p_type='lt'",
																		'type'	=> 'left',
																  ),
														1 => array( 'select'	=> 'p.*',
																		'from'	=> array( 'permission_index' => 'p' ),
																		'where' => "p.app = 'ibEconomy' AND p.perm_type='long_term' AND p.perm_type_id=elt.lt_id",
																		'type'	=> 'left',
																  )																	  
														)
							)		);
		$this->DB->execute();
		
		if ( $howMany == 'one' )
		{
			if ( $this->DB->getTotalRows() )
			{
				while ( $row = $this->DB->fetch() )
				{
					$lt = $row;
				}
			}
			
			return $lt;
		}
	}	

	/**
	 * Let me ax u somtin, what I got in my bag aiedy?
	 */
	public function grabShoppingCart()
	{
		#grab all the items in my cart and grab data for each one
		$this->DB->build( array( 	'select'	=> 'ec.*',
									'from'		=> array( 'eco_cart' => 'ec' ),
									'group'		=> 'ec.c_id',
									'order'		=> 'ec.c_type',
									'where'  	=> 'ec.c_member_id = ' . $this->memberData['member_id'],
									'add_join'	=> array(
														0 => array( 'select' 	=> 'eb.*',
																		'from'	=> array( 'eco_banks' => 'eb' ),
																		'where'	=> "eb.b_id = ec.c_type_id and ec.c_type='bank'",
																		'type'	=> 'left',
																  ),
														1 => array( 'select' 	=> 'ecc.*',
																		'from'	=> array( 'eco_credit_cards' => 'ecc' ),
																		'where'	=> "ecc.cc_id = ec.c_type_id and ec.c_type='cc'",
																		'type'	=> 'left',
																  ),
														2 => array( 'select' 	=> 'es.*',
																		'from'	=> array( 'eco_stocks' => 'es' ),
																		'where'	=> "es.s_id = ec.c_type_id and ec.c_type='stock'",
																		'type'	=> 'left',
																  ),
														3 => array( 'select' 	=> 'elt.*',
																		'from'	=> array( 'eco_long_terms' => 'elt' ),
																		'where'	=> "elt.lt_id = ec.c_type_id and ec.c_type='lt'",
																		'type'	=> 'left',
																  )
,
														4 => array( 'select' 	=> 'esi.*',
																		'from'	=> array( 'eco_shop_items' => 'esi' ),
																		'where'	=> "esi.si_id = ec.c_type_id and ec.c_type='shopitem'",
																		'type'	=> 'left',
																  )																  
														)
							)		);
		$this->DB->execute();	
	}
	
	/**
	 * Count portfolio items
	 */
	public function countPortItems( $howMany, $ids, $itemtype, $member )
	{	
		#init
		if ( $howMany == 'all' )
		{
			$where    	= ( is_array($ids) ) ? 'p_id IN (' . implode( ',', $ids ) . ')' : 'p_id > 0';
		}
		else
		{
			$where		= 'p_id = '.$ids;
		}
		if ( $itemtype )
		{
			$where     .= ( $itemtype == 'loan' ) ? " AND p_type_class = 'loan'" : " AND p_type = '$itemtype' AND p_type_class != 'loan'";
		}
		if ( $member )
		{
			$where     .= ' AND p_member_id = '.$member;
		}		
		
		#get count
		$max = $this->DB->buildAndFetch( array( 'select' => 'COUNT( p_id ) as portItemCount',
								 				'from'	 => 'eco_portfolio',
												'where'	 => $where
										) 	   );
		return $max['portItemCount'];
	}	
	
	/**
	 * We is got this stuff
	 */
	public function grabPortfolioItems( $member=0, $howMany='', $type='', $ids=array(), $itemtype='', $start=0, $sorter='', $sw='', $organizeItFirst=false, $organizeByMem=false, $memberWorthTask=false )
	{
		#init
		$myPortItems = array();
		$this->settings['eco_general_pp'] = ( $type == 'acp' ) ? $this->settings['eco_acp_pp'] : $this->settings['eco_general_pp'];
		
		if ( $howMany == 'all' )
		{
			$where    	= ( is_array($ids) && count($ids)) ? 'ep.p_id IN (' . implode( ',', $ids ) . ')' : 'ep.p_id > 0';
			$end		= ( $type == 'cache' ) ? 99999 : intval($this->settings['eco_general_pp']);
		}
		else
		{
			$where		= 'ep.p_id = '.$ids;
			$end		= 1;
		}
		if ( $itemtype )
		{
			$where     .= ( $itemtype == 'loan' ) ? " AND p_type_class = 'loan'" : " AND p_type = '$itemtype' AND p_type_class != 'loan'";
		}
		if ( $member )
		{
			$where     .= ' AND p_member_id = '.$member;
		}
		
		$sorter 	= ( $sorter ) 	? $sorter 	: 'ep.p_purch_date';
		$sw			= ( $sw ) 		? $sw 		: 'DESC';
		
		if ($memberWorthTask)
		{
			$joins = array(
							0 => array( 'select' 	=> 'esi.si_id, esi.si_cost',
											'from'	=> array( 'eco_shop_items' => 'esi' ),
											'where'	=> "esi.si_id = ep.p_type_id and ep.p_type='shopitem'",
											'type'	=> 'left',
									  ),
							1 => array( 'select' 	=> 'es.s_id, es.s_value',
											'from'	=> array( 'eco_stocks' => 'es' ),
											'where'	=> "es.s_id = ep.p_type_id and ep.p_type='stock'",
											'type'	=> 'left',
									  )														  
									  
						   );		
		}
		else
		{
			$joins = array(
							0 => array( 'select' 	=> 'eb.*',
											'from'	=> array( 'eco_banks' => 'eb' ),
											'where'	=> "eb.b_id = ep.p_type_id and ep.p_type='bank'",
											'type'	=> 'left',
									  ),
							1 => array( 'select' 	=> 'ecc.*',
											'from'	=> array( 'eco_credit_cards' => 'ecc' ),
											'where'	=> "ecc.cc_id = ep.p_type_id and ep.p_type='cc'",
											'type'	=> 'left',
									  ),
							2 => array( 'select' 	=> 'es.*',
											'from'	=> array( 'eco_stocks' => 'es' ),
											'where'	=> "es.s_id = ep.p_type_id and ep.p_type='stock'",
											'type'	=> 'left',
									  ),
							3 => array( 'select' 	=> 'elt.*',
											'from'	=> array( 'eco_long_terms' => 'elt' ),
											'where'	=> "elt.lt_id = ep.p_type_id and ep.p_type='lt'",
											'type'	=> 'left',
									  ),
							4 => array( 'select' 	=> 'esi.*',
											'from'	=> array( 'eco_shop_items' => 'esi' ),
											'where'	=> "esi.si_id = ep.p_type_id and ep.p_type='shopitem'",
											'type'	=> 'left',
									  ),
							5 => array( 'select' 	=> 'p.*',
											'from'  => array( 'permission_index' => 'p' ),
											'where' => "p.app = 'ibEconomy' AND p.perm_type=ep.p_type AND p.perm_type_id=ep.p_type_id",
											'type'  => 'left',
									  ),
							6 => array( 'select' 	=> 'm.member_id, m.members_display_name, m.member_group_id, m.members_seo_name, m.member_id AS memTableID',
											'from'  => array( 'members' => 'm' ),
											'where' => "m.member_id=ep.p_member_id",
											'type'  => 'left',
									  ),
							7 => array( 'select' 	=> 'pp.*',
											'from'  => array( 'profile_portal' => 'pp' ),
											'where'	=> 'm.member_id=pp.pp_member_id',
											'type'	=> 'left',
									  )															  
									  
						   );
		}
		#grab all the items in the portfolio and grab data for each one
		$o = $this->DB->build( array( 	'select'	=> 'ep.*',
									'from'		=> array( 'eco_portfolio' => 'ep' ),
									'group'		=> 'ep.p_id',
									'order'		=> $sorter.' '.$sw,
									'where'  	=> $where,
									'limit'		=> array( $start, $end ),
									'add_join'	=> $joins
							)		);
		$oo = $this->DB->execute($o);	
		
		if ($organizeItFirst)
		{	
			if ( $this->DB->getTotalRows() )
			{
				while ( $row = $this->DB->fetch($o) )
				{
					if ($row['p_type_class'] != 'loan')
					{
						$index = ($row['p_type_class']) ? $row['p_type'].'s_'.$row['p_type_class'] : $row['p_type'].'s';
					}
					else
					{
						$index = 'loans';
					}
					
					$myPortItems[$index][] = $row;
				}
			}
			
			return $myPortItems;			
		}
		else if ($organizeByMem)
		{	
			if ( $this->DB->getTotalRows() )
			{			
				while ( $row = $this->DB->fetch() )
				{
					$myPortItems[ $row['p_member_id'] ][] = $row;
				}
			}

			return $myPortItems;			
		}
		
		return $oo;
	}
	
	/**
	 * Grab all portfolio items by a single specific type
	 * and from a specific user, if so desired
	 */
	public function grabPortfolioItemsByType( $itemtype, $member=0, $organizeItFirst=false, $needExtendedInfo=false, $bankType='', $lottoNumber=0 )
	{
		$myPortItems = array();
		$and		 = "";

		$typeAbbr 	= $this->registry->ecoclass->getTypeAbr($itemtype);
		$ecoDBName 	= $this->registry->ecoclass->getDBName($itemtype);
		$portType 	= $this->registry->ecoclass->getPortTypeName($itemtype);
		
		$joins 		= array();

		$andType = ($itemtype == 'bank' && $bankType) ?  " AND p_type_class = '$bankType'" : "";
		
		if ($needExtendedInfo)
		{
			$joins 	= 		array(
									array( 'from'	=> array( $ecoDBName  => 'ei' ),
										   'where'	=> "ei.".$typeAbbr."_id = ep.p_type_id "
										 )
									 );
									 
			$order = "ei.".$typeAbbr."_position ASC";
		}
		
		if ($itemtype)
		{
			$where  = ( $itemtype == 'loan' || $bankType == 'loan' ) ? "ep.p_type_class = 'loan'" : "ep.p_type = '$itemtype' AND ep.p_type_class != 'loan'";
			$and 	= ' AND ';
		}
		
		$where .= ( intval($member) > 0 ) ? $and. 'ep.p_member_id = '.$member : '';
		
		#lotter fix (added in 1.5)
		$where .= ( intval($lottoNumber) > 0 ) ? $and. 'ep.p_type_id = '.$lottoNumber : '';
		
		#grab all the items in the portfolio and grab data for each one
		$this->DB->build( array( 	'select'	=> '*',
									'from'		=> array( 'eco_portfolio' => 'ep' ),
									'where'  	=> $where,
									'order'		=> $order,
									'add_join'	=> $joins,
							)		);
		$this->DB->execute();	
		
		if ( $this->DB->getTotalRows() )
		{
			while ( $row = $this->DB->fetch() )
			{
				if ($organizeItFirst)
				{
					if ($row['p_type_class'] != 'loan')
					{
						$index = ($row['p_type_class']) ? $row['p_type'].'s_'.$row['p_type_class'] : $row['p_type'];
					}
					else
					{
						$index = 'loan';
					}
					
					$myPortItems[$index][] = $row;
				}
				else
				{
					$myPortItems[] = $row;
				}
			}
		}
		
		return $myPortItems;
	}
	
	/**
	 * Grab all of a member's portfolio items that are checking accounts and ccs
	 */
	public function grabMemBanksAndCCs( $member, $loansWanted=false )
	{
		$myPortItems = array();
		
		if ($loansWanted)
		{
			$where  = "p_member_id = ".$member." AND p_type = 'bank' AND p_type_class = 'loan'";
		}
		else
		{
			$where  = "p_member_id = ".$member." AND (p_type = 'bank' AND p_type_class = 'checking' OR p_type = 'cc')";
		}
		
		#grab all the items in the portfolio and grab data for each one
		$this->DB->build( array( 	'select'	=> 'ep.*',
									'from'		=> array( 'eco_portfolio' => 'ep' ),
									'where'  	=> $where,
							)		);
		$this->DB->execute();	
		
		if ( $this->DB->getTotalRows() )
		{
			while ( $row = $this->DB->fetch() )
			{
				$myPortItems[($loansWanted) ? $row['p_type_id'] : $row['p_id']] = $row;
			}
		}
		
		return $myPortItems;
	}	
	
	/**
	 * Grab a single portfolio item
	 */
	public function grabSinglePortItem( $member, $pid )
	{
		$portItem = $this->DB->buildAndFetch( array( 	'select'	=> '*',
														'from'		=> 'eco_portfolio',
														'where'  	=> "p_member_id = " . $member." AND p_id = " .$pid
											)		);
		return $portItem;
	}

	/**
	 * Grab a single portfolio item plus more info on that item
	 */
	public function grabSinglePortItemExtended( $member, $iid, $type, $bankType='' )
	{
		$typeAbbr 	= $this->registry->ecoclass->getTypeAbr($type);
		$ecoDBName 	= $this->registry->ecoclass->getDBName($type);
		$portType 	= $this->registry->ecoclass->getPortTypeName($type);

		$joins 		= array();

		$andType = ($type == 'bank' && $bankType) ?  " AND p_type_class = '$bankType'" : "";
		
		if ($type == 'stock')
		{
			$joins 	= 		array(
									array( 'from'	=> array( 'eco_portfolio' => 'ep' ),
										   'where'	=> "ep.p_member_id = " .$member." AND ep.p_type_id = ei.".$typeAbbr."_id AND p_type = '$portType'".$andType,
										 ),
									array( 'from'	=> array( 'members' => 'm' ),
										   'where'	=> "m.member_id = ei.s_type_var_value and ei.s_type = 'member'",
										 )
									 );
		}
		else
		{
			$joins	 = 		array(
									array( 'from'	=> array( 'eco_portfolio' => 'ep' ),
										   'where'	=> "ep.p_member_id = " .$member." AND ep.p_type_id = ei.".$typeAbbr."_id AND p_type = '$portType'".$andType,
										 )
									 );
		}
		
		$portItem	= $this->DB->buildAndFetch( array(
												'select'	=> '*',
												'from'		=> array( $ecoDBName => 'ei' ),
												'where'  	=> "ei.".$typeAbbr."_id = " .$iid,
												'add_join'	=> $joins
										)		);										
		return $portItem;
	}
	
	/**
	 * Count members with points and/or worth
	 */
	public function countMembers()
	{
		#init		
		$stats		= $this->cache->getCache('ibEco_stats');
		$ecoPoints 	= $this->settings['eco_general_pts_field'];
		$ptsDB		= ( $ecoPoints == 'eco_points' ) ? 'pc' : 'm';										
		
		#get count
		$max	= $this->DB->buildAndFetch( array(
												'select'	=> 'COUNT( m.member_id ) as memCount',
												'from'		=> array( 'members' => 'm' ),
												'where'  	=> $ptsDB.'.'.$ecoPoints.' > 0 OR pc.eco_worth > 0 OR pc.eco_welfare > 0',
												'add_join'	=> array(
																	array( 'from'	=> array( 'pfields_content' => 'pc' ),
																		   'where'	=> 'm.member_id=pc.member_id'
																		 )
																	)
										)		);										
		return $max['memCount'];
	}	

	/**
	 * Assign rankings to members
	 */
	public function rankMembers( $ranker, $member='' )
	{
		#init
		$rankings 	= array();
		$rankNum 	= 0;
		$pts    	= $this->settings['eco_general_pts_field'];
		$ptsDB		= ( $pts == 'eco_points' ) ? 'pc' : 'm';
		$ranker		= ( $ranker == 'points' ) ? $ptsDB.'.'.$pts : $ranker;
		$sorter 	= ( $ranker ) ? $ranker : 'pc.eco_worth';
		$where    	= $ptsDB.'.'.$pts.' != 0 OR pc.eco_worth != 0 OR pc.eco_welfare != 0 ';
				
		#grab em all
		$this->DB->build( array( 	'select'	=> 'm.member_id,m.name,m.member_group_id,m.members_display_name',
									'from'		=> array( 'members' => 'm' ),
									'group'		=> 'm.member_id',
									'order'		=> $sorter .' DESC',
									'where'  	=> $where,
									'add_join'	=> array(
														0 => array( 'select'	=> $sorter.' AS ranking_item',
																		'from'  => array( 'pfields_content' => 'pc' ),
																		'where' => 'm.member_id=pc.member_id',
																		'type'  => 'left'
																  )															
														)														
							)		);
		$this->DB->execute();
		
		if ( $this->DB->getTotalRows() )
		{
			while ( $row = $this->DB->fetch() )
			{
				$rankNum++;
				
				#assign rankings
				$rankings[ $row['member_id'] ] = $rankNum;
			}
		}
		
		return ( $member ) ? $rankings[ $member ]: $rankings;
	}	
			
	/**
	 * Query those members, ya'll (for wealth distribution models)
	 */
	public function queryMembers( $howMany, $ids, $start, $sort, $sw, $type )
	{
		#init
		$members 	= array();
		$pts 		= $this->settings['eco_general_pts_field'];
		$ptsDB		= ( $pts == 'eco_points' ) ? 'pc' : 'm';
		$points		= $ptsDB.'.'.$pts;
		
		#where and start stop...
		if ( $howMany == 'all' )
		{
			$where    	= ( is_array($ids) ) ? 'm.member_id IN (' . implode( ',', $ids ) . ')' : $points.' > 0 OR pc.eco_worth > 0 OR pc.eco_welfare > 0';
			$end		= ( $type == 'overview' ) ? 999999999 : $this->settings['eco_general_pp'];
		}
		else if ( intval($howMany) > 0 )
		{
			$where    	= $points.' > 0 OR pc.eco_worth > 0 OR pc.eco_welfare > 0';
			$end		= $howMany;
		}		
		else
		{
			$where		= 'm.member_id = '.$ids;
			$end		= 1;
		}
		
		#sorter..
		if ( in_array( $sort, array('eco_worth','eco_welfare') ) )
		{
			$sorter = 'pc.'.$sort;
		}
		else if ( $sort == 'members_display_name' )
		{
			$sorter = 'm.'.$sort;
		}
		else if ( in_array($sort, array('eco_points','points','money','ibbookie_points','utr_points') ) )
		{
			$sorter = $points;
		}
		else 
		{
			$sorter = ($this->settings['eco_worth_on']) ? 'pc.eco_worth' : $points;
		}		
		
		#switcher...
		$switch = ( $sw ) ? $sw : 'DESC'; 
				
		#grab all the members with points or worth
		$this->DB->build( array( 	'select'	=> 'm.member_id,m.name,m.member_group_id,m.joined,m.posts,m.last_visit,m.last_activity,m.login_anonymous,m.title,m.members_display_name,m.members_seo_name',
									'from'		=> array( 'members' => 'm' ),
									'group'		=> 'm.member_id',
									'order'		=> $sorter .' '. $switch,
									'where'  	=> $where,
									'limit'		=> array( intval($start), intval($end) ),
									'add_join'	=> array(
														0 => array( 'select'	=> 'pp.*',
																		'from'  => array( 'profile_portal' => 'pp' ),
																		'where' => 'm.member_id=pp.pp_member_id',
																		'type'  => 'left'
																  ),
														1 => array( 'select'	=> $ptsDB.'.'.$pts.' AS eco_points, pc.eco_worth, pc.eco_welfare',
																		'from'  => array( 'pfields_content' => 'pc' ),
																		'where' => 'm.member_id=pc.member_id',
																		'type'  => 'left'
																  )															
														)														
							)		);
		$this->DB->execute();
		
		if ( $howMany == 'one' )
		{
			if ( $this->DB->getTotalRows() )
			{
				while ( $row = $this->DB->fetch() )
				{
					if ($this->settings['eco_plugin_ppns_on'] && ($row['ibEco_plugin_ppns_prefix'] || $row['ibEco_plugin_ppns_suffix'] || $this->settings['eco_plugin_ppns_use_gf']))
					{
						$row['formatted_name'] 	= IPSMember::makeNameFormatted( $row['members_display_name'], $row['member_group_id'], $row['ibEco_plugin_ppns_prefix'], $row['ibEco_plugin_ppns_suffix'] ); 
					}
					else
					{
						$row['formatted_name'] 	= $row['members_display_name']; 
					}
					$member = $row;
				}
			}
			
			return $member;
		}
	}
	
	/**
	 * Count members with points and/or worth
	 */
	public function countLogs( $action, $member )
	{	
		#where to count
		if ( $action )
		{
			$where  = ( $action == 'loan' ) ? "l_action = 'purchase' AND l_subject_name = 'loan'" : "l_action = '$action' AND l_subject_name != 'loan'";
		}
		
		$where     .= ( $action && $member ) ? ' AND ' : '';
		$where     .= ( $member ) 	? 'l_member_id = '.$member : '';
		
		#get count
		$max = $this->DB->buildAndFetch( array(  'select'	 => 'COUNT( l_id ) as logCount',
								 				 'from'		 => 'eco_logs',
												 'where'     => $where,
										) 	   );
		return $max['logCount'];
	}	
	
	/**
	 * Query logs
	 */
	public function getLogs( $action='', $member=0, $sorter='', $sw='', $start=0 )
	{
		#init
		$sorter 	= ( $sorter ) 	? $sorter : 'el.l_id';
		$sw			= ( $sw ) 		? $sw : 'DESC';
		
		#where
		if ( $action )
		{
			if ( $action == 'everythingButPurchases' )
			{
				$where  = "el.l_action != 'purchase'";
			}
			else
			{
				$where  = ( $action == 'loan' ) ? "el.l_action = 'purchase' AND el.l_subject_name = 'loan'" : "el.l_action = '$action' AND el.l_subject_name != 'loan'";
			}
		}
		$where     .= ( $action && $member ) ? ' AND ' : '';
		$where     .= ( $member ) 	? "el.l_member_id = ".$member : "";
				
		#grab all the logs
		$this->DB->build( array( 	'select'	=> 'el.*',
									'from'		=> array( 'eco_logs' => 'el' ),
									'order'		=> $sorter.' '.$sw,
									'where'  	=> $where,
									'limit'		=> array( intval($start), intval($this->settings['eco_general_pp']) ),
									'add_join'	=> array(
														0 => array( 'select' 	=> 'm.members_display_name AS donator_name, m.member_group_id AS donator_group, m.member_id AS donator_id, m.members_seo_name AS donator_seo_name',
																		'from'	=> array( 'members' => 'm' ),
																		'where'	=> "m.member_id = el.l_member_id",
																		'type'	=> 'left',
																  ),
														1 => array( 'select' 	=> 'm2.members_display_name AS donatee_name, m2.member_group_id AS donatee_group, m2.member_id AS donatee_id, m2.members_seo_name AS donatee_seo_name',
																		'from'	=> array( 'members' => 'm2' ),
																		'where'	=> "m2.member_id = el.l_subject_id and el.l_action = 'donation'",
																		'type'	=> 'left',
																  ),
														2 => array( 'select' 	=> 'pp.*',
																		'from'  => array( 'profile_portal' => 'pp' ),
																		'where'	=> 'm.member_id=pp.pp_member_id',
																		'type'	=> 'left',
																  )																  
														)														
							)		);
		$this->DB->execute();
	}	

	/**
	 * Query those shop categories, ya'll
	 */
	public function queryShopCats( $howMany, $ids, $type, $start )
	{
		#init
		if ( $howMany == 'all' )
		{
			$where    	= ( is_array($ids) ) ? 'esc.sc_id IN (' . implode( ',', $ids ) . ')' : '';
			$end		= ( $type == 'cache' ) ? 999 : intval($this->settings['eco_general_pp']);
		}
		else
		{
			$where		= 'esc.sc_id = '.$ids;
			$end		= 1;
		}
		
		#grab all the categories, and count items within each cat
		$this->DB->build( array( 	'select'	=> 'esc.*',
									'from'		=> array( 'eco_shop_cats' => 'esc' ),
									'group'		=> 'esc.sc_id',
									'order'		=> 'esc.sc_position',
									'where'  	=> $where,
									'limit'		=> array( $start, $end ),									
									'add_join'	=> array(
														0 => array( 'select' 	=> 'COUNT(esi.si_ID) as total_items',
																		'from'	=> array( 'eco_shop_items' => 'esi' ),
																		'where'	=> "esc.sc_id = esi.si_cat",
																		'type'	=> 'left',
															),
														1 => array( 'select' 	=> 'p.*',
																		'from'  => array( 'permission_index' => 'p' ),
																		'where' => "p.app = 'ibEconomy' AND p.perm_type='shop_cat' AND p.perm_type_id=esc.sc_id",
																		'type'  => 'left',
																  ) 														
														)
							)		);
		$this->DB->execute();
		
		if ( $howMany == 'one' )
		{
			if ( $this->DB->getTotalRows() )
			{
				while ( $row = $this->DB->fetch() )
				{
					$shopCat = $row;
				}
			}
			
			return $shopCat;
		}
	}

	/**
	 * Query those shop items, ya'll
	 */
	public function queryShopItems( $howMany, $ids, $type, $start )
	{
		#init
		if ( $howMany == 'all' )
		{
			$where    	= ( is_array($ids) ) ? 'esi.si_id IN (' . implode( ',', $ids ) . ')' : '';
			$end		= ( $type == 'cache' ) ? 999 : intval($this->settings['eco_general_pp']);
		}
		else
		{
			$where		= 'esi.si_id = '.$ids;
			$end		= 1;
		}
		
		#grab all the items, and count items owned
		$this->DB->build( array( 	'select'	=> 'esi.*',
									'from'		=> array( 'eco_shop_items' => 'esi' ),
									'group'		=> 'esi.si_id',
									'order'		=> 'esi.si_position',
									'where'  	=> $where,
									'limit'		=> array( $start, $end ),									
									'add_join'	=> array(
														0 => array( 'select' 	=> 'COUNT(ep.p_id) as total_items, SUM(ep.p_amount) as total_item_num',
																		'from'	=> array( 'eco_portfolio' => 'ep' ),
																		'where'	=> "esi.si_id = ep.p_type_id and p_type = 'shopitem'",
																		'type'	=> 'left',
															),
														1 => array( 'select' 	=> 'p.*',
																		'from'  => array( 'permission_index' => 'p' ),
																		'where' => "p.app = 'ibEconomy' AND p.perm_type='shopitem' AND p.perm_type_id=esi.si_id",
																		'type'  => 'left',
																			 ) 														
														)
							)		);
		$this->DB->execute();
		
		if ( $howMany == 'one' )
		{
			if ( $this->DB->getTotalRows() )
			{
				while ( $row = $this->DB->fetch() )
				{
					$shopItem = $row;
				}
			}
			
			return $shopItem;
		}
	}
	
	/**
	 * Query those sidebar blocks, ya'll
	 */
	public function querySidebarBlocks( $howMany, $ids, $type, $start )
	{
		#init
		if ( $howMany == 'all' )
		{
			$where    	= ( is_array($ids) ) ? 'esb.sb_id IN (' . implode( ',', $ids ) . ')' : '';
			$end		= ( $type == 'cache' ) ? 999 : intval($this->settings['eco_general_pp']);
		}
		else
		{
			$where		= 'esb.sb_id = '.$ids;
			$end		= 1;
		}
		
		#grab all the items, and count items owned
		$this->DB->build( array( 	'select'	=> 'esb.*',
									'from'		=> array( 'eco_sidebar_blocks' => 'esb' ),
									'group'		=> 'esb.sb_id',
									'order'		=> 'esb.sb_position',
									'where'  	=> $where,
									'limit'		=> array( $start, $end ),									
									'add_join'	=> array(
														0 => array( 'select' 	=> 'p.*',
																		'from'  => array( 'permission_index' => 'p' ),
																		'where' => "p.app = 'ibEconomy' AND p.perm_type='block' AND p.perm_type_id=esb.sb_id",
																		'type'  => 'left',
																			 ) 														
														)
							)		);
		$this->DB->execute();
		
		if ( $howMany == 'one' )
		{
			if ( $this->DB->getTotalRows() )
			{
				while ( $row = $this->DB->fetch() )
				{
					$sidebarBlock = $row;
				}
			}
			
			return $sidebarBlock;
		}
	}	

	/**
	 * Quickly check a shopping cart for an item/id
	 */	
	public function checkCart($type, $type_id, $banktype)
	{
		$bank_type_check = ( $type == 'bank' ) ? " AND c_type_class = '$banktype'" : '';
		
		#we already have this item in our cart? if so grab it's details
		$cartItem = $this->DB->buildAndFetch( array( 	'select'	=> '*',
														'from'		=> 'eco_cart',
														'where'  	=> "c_member_id = " . $this->memberData['member_id']." AND c_type = '$type' AND c_type_id = ".$type_id . $bank_type_check
											)		);
		return $cartItem;
	}

	/**
	 * Quickly check a portfolio for an item/id
	 */	
	public function checkFolio($type, $type_id, $banktype)
	{
		$bank_type_check = ( $type == 'bank' ) ? " AND p_type_class = '$banktype'" : '';
		
		#item already in our portfolio? if so grab it's details
		$folioItem = $this->DB->buildAndFetch( array( 	'select'	=> '*',
														'from'		=> 'eco_portfolio',
														'where'  	=> "p_member_id = " . $this->memberData['member_id']." AND p_type = '$type' AND p_type_id = ".$type_id . $bank_type_check
											)		);
		return $folioItem;
	}
	
	/**
	 * Update a member's points and worth (everything goes through this)
	 */	
	public function updateMemberPts( $member_id, $amt, $plusMinus, $worthToo, $check4Negative=false, $memCurPts=-1 )
	{
		#which pts field? (gotta make it difficult, jeez)
		$ptsField 	= $this->settings['eco_general_pts_field'];
		$ptsDB		= ( $ptsField == 'eco_points' ) ? 'pfields_content' : 'members';

		#number should be formatted by now, just need to run it through the decimal "decider" one last time to tie up any loose ends...
		$amt		= $this->registry->ecoclass->makeNumeric($amt, false);		
		
		#can we go negative? (added in 1.5)
		if ( $check4Negative && $this->settings['eco_not_allow_negative'] )
		{
			if ($memCurPts == -1)
			{
				$member 		= IPSMember::load( $member_id, 'all' );
				$memCurPts 		= $member[ $this->settings['eco_general_pts_field'] ];			
			}

			$newTotal			= ( $plusMinus == '+' ) ? $memCurPts + $amt : $memCurPts - $amt;
			$amt 				= ( $newTotal < 0 ) ? $memCurPts * -1 : $amt;
		}
		
		#query specs
		$dbUpdates  = $ptsField.' = '.$ptsField.$plusMinus.$amt;

		#execute points!
		$this->DB->buildAndFetch( array( 'update' => $ptsDB, 'set' => $dbUpdates, 'where' => 'member_id = '.$member_id ) );

		#execute worth!
		if ( $worthToo )
		{
			$worthUpdates = 'eco_worth = eco_worth '.$plusMinus.$amt;
			$this->DB->buildAndFetch( array( 'update' => 'pfields_content', 'set' => $worthUpdates, 'where' => 'member_id = '.$member_id ) );
		}
	}

	/**
	 * Update a member's points to specific number
	 */	
	public function updateMemberPts2SpecNum( $member_id, $amt, $worthToo )
	{
		#which pts field?
		$ptsField 	= $this->settings['eco_general_pts_field'];
		$ptsDB		= ( $ptsField == 'eco_points' ) ? 'pfields_content' : 'members';
		
		#format number (replace those commas)
		#removed further parsing in 1.5, by changing second argument to false
		$amt		= $this->registry->ecoclass->makeNumeric($amt, false);
		
		#can we go negative? (added in 1.5)
		$amt = ( $amt < 0 && $this->settings['eco_not_allow_negative'] ) ? 0 : $amt;
		
		#query specs
		$dbUpdates  = $ptsField.' = '.$amt;
		
		#execute points!
		$this->DB->buildAndFetch( array( 'update' => $ptsDB, 'set' => $dbUpdates, 'where' => 'member_id = '.$member_id ) );

		#execute worth!
		if ( $worthToo )
		{
			$worthUpdates = 'eco_worth = '.$amt;
			$this->DB->buildAndFetch( array( 'update' => 'pfields_content', 'set' => $worthUpdates, 'where' => 'member_id = '.$member_id ) );
		}
	}

	/**
	 * Update a member's welfare total and latest welfare check time (gotta be a second query cause my ass used pfields_content for hooks)
	 */	
	public function updateMemberWelfare( $member_id, $amt, $plusMinus, $now )
	{
		#format (mainly for comma decimal users)
		$amt		= $this->registry->ecoclass->makeNumeric($amt, false);
		
		#query stuff
		$dbUpdates  = 'eco_on_welfare = '.$now.', eco_welfare = eco_welfare '.$plusMinus.$amt;
		
		#execute
		$this->DB->buildAndFetch( array( 'update' => 'pfields_content', 'set' => $dbUpdates, 'where' => "member_id = ".$member_id ) );
	}	
	
	/**
	 * Cache those Banks!
	 */
	public function rebuildBankCache()
	{
		#init
		$cache = array();
		
		#query
		$this->queryBanks( 'all', $ids='', $type='cache', 0 );
		
		#got rows?
		if ( $this->DB->getTotalRows() )
		{	
			while ( $r = $this->DB->fetch() )
			{
				#yucky
				// $r['c_total']  = $r['c_total']/2;
				// $r['s_total']  = $r['s_total']/2;
				// $r['c_funds']  = $r['c_funds']/2;
				// $r['s_funds']  = $r['s_funds']/2;			
				
				#totals
				$r['total_accts']		= $r['c_total'] + $r['s_total'];
				$r['total_funds']		= $r['c_funds'] + $r['s_funds'];
				
				$cache[ $r['b_id'] ] 	= $r;
			}
		}
		
		$this->cache->setCache( 'ibEco_banks', $cache, array( 'array' => 1, 'deletefirst' => 0, 'donow' => 1 ) );		
	}
	
	/**
	 * Cache those Stocks!
	 */
	public function rebuildStockCache()
	{
		#init
		$cache = array();
			
		#query
		$this->queryStocks( 'all', $ids='', $type='cache', 0 );
		
		#got rows?
		if ( $this->DB->getTotalRows() )
		{	
			while ( $r = $this->DB->fetch() )
			{
				$cache[ $r['s_id'] ] = $r;
			}
		}
		
		#do it
		$this->cache->setCache( 'ibEco_stocks', $cache, array( 'array' => 1, 'deletefirst' => 0, 'donow' => 1 ) );		
	}

	/**
	 * Cache those Credit-Cards!
	 */
	public function rebuildCCCache()
	{
		#init
		$cache = array();
		
		#query
		$this->queryCCs( 'all', $ids='', $type='cache', 0 );
		
		#got rows?
		if ( $this->DB->getTotalRows() )
		{	
			while ( $r = $this->DB->fetch() )
			{
				$cache[ $r['cc_id'] ] = $r;
			}
		}
		
		#do it
		$this->cache->setCache( 'ibEco_ccs', $cache, array( 'array' => 1, 'deletefirst' => 0, 'donow' => 1 ) );		
	}

	/**
	 * Cache those Long-Term Investments!
	 */
	public function rebuildLTCache()
	{
		#init
		$cache = array();
			
		#query	
		$this->queryLTs( 'all', $ids='', $type='cache', 0 );
		
		#got rows?
		if ( $this->DB->getTotalRows() )
		{	
			while ( $r = $this->DB->fetch() )
			{
				$cache[ $r['lt_id'] ] = $r;
			}
		}
		
		#do it
		$this->cache->setCache( 'ibEco_lts', $cache, array( 'array' => 1, 'deletefirst' => 0, 'donow' => 1 ) );		
	}
	
	/**
	 * Cache member's portfolios (the various purchasable items)!
	 */
	public function rebuildPortfolioCache()
	{
		#init
		$cache = array();

		#portfolio cache enabled?
		if ( !$this->settings['eco_general_cache_portfolio'] )
		{
			$this->cache->setCache( 'ibEco_portfolios', $cache, array( 'array' => 1, 'deletefirst' => 1, 'donow' => 1 ) );
			
			return false;
		}
		
		#query	
		$this->grabPortfolioItems( $mem='', 'all', $type='cache', $ids='', $itemtype='', 0, $sort='', $sw='' );
		
		#got rows?
		if ( $this->DB->getTotalRows() )
		{
			while ( $r = $this->DB->fetch() )
			{
				#remove empty spots of array
				foreach ($r as $key => $value)
				{
					if (is_null($value)) 
					{
						unset($r[$key]);
					}
				}
				
				#by item
				$cache[ $r['p_type'].'s' ][ $r['p_id'] ] 	 										= $r;
				
				#by member
				$cache[ $r['p_member_id'] ][ $r['p_id'] ] 											= $r;
				
				#by member and item
				if ( $r['p_type'] == 'bank' && $r['p_type_class'] != 'loan' )
				{
					$cache[ $r['p_member_id'] ]['banks_'.$r['p_type_class'] ][ $r['p_type_id'] ] 	= $r;
				}
				else if ( $r['p_type'] == 'stock' )
				{
					$cache[ $r['p_member_id'] ]['stocks_'][ $r['p_type_id'] ]  						= $r;
				}
				else if ( $r['p_type'] == 'cc' )
				{
					$cache[ $r['p_member_id'] ]['ccs_'][ $r['p_type_id'] ]  						= $r;
				}
				else if ( $r['p_type'] == 'lt' )
				{
					$cache[ $r['p_member_id'] ]['lts_'][ $r['p_type_id'] ] 							= $r;
				}
				else if ( $r['p_type'] == 'shopitem' )
				{
					$cache[ $r['p_member_id'] ]['shopitems_' ][ $r['p_type_id'] ] 					= $r;
				}
				else if ( $r['p_type'] == 'bank' && $r['p_type_class'] == 'loan' )
				{
					$cache[ $r['p_member_id'] ]['loans_' ][ $r['p_type_id'] ] 						= $r;
				}
				else if ( $r['p_type'] == 'lottery' )
				{
					$cache[ $r['p_member_id'] ]['lotterys_' ][ $r['p_type_id'] ] 						= $r;
				}				
			}
		}
		
		#do it
		$this->cache->setCache( 'ibEco_portfolios', $cache, array( 'array' => 1, 'deletefirst' => 0, 'donow' => 1 ) );		
	}

	/**
	 * Cache those Shop Categories!
	 */
	public function rebuildShopCatCache()
	{
		#init
		$cache = array();
		
		#query		
		$this->queryShopCats( 'all', $ids='', $type='cache', 0 );
		
		#got rows?
		if ( $this->DB->getTotalRows() )
		{
			while ( $r = $this->DB->fetch() )
			{
				$cache[ $r['sc_id'] ] = $r;
			}
		}
		
		#do it
		$this->cache->setCache( 'ibEco_shopcats', $cache, array( 'array' => 1, 'deletefirst' => 0, 'donow' => 1 ) );		
	}

	/**
	 * Cache those Shop Items!
	 */
	public function rebuildShopItemCache()
	{
		#init
		$cache = array();
			
		#query
		$this->queryShopItems( 'all', $ids='', $type='cache', 0 );
		
		#got rows?
		if ( $this->DB->getTotalRows() )
		{
			while ( $r = $this->DB->fetch() )
			{
				$cache[ $r['si_id'] ] = $r;
			}
		}
		
		#do it
		$this->cache->setCache( 'ibEco_shopitems', $cache, array( 'array' => 1, 'deletefirst' => 0, 'donow' => 1 ) );		
	}
	
	/**
	 * Cache those Sidebar Blocks!
	 */
	public function rebuildSidebarBlockCache()
	{
		#init
		$cache = array();
		
		#query		
		$this->querySidebarBlocks( 'all', $ids='', $type='cache', 0 );

		#got rows?
		if ( $this->DB->getTotalRows() )
		{		
			while ( $r = $this->DB->fetch() )
			{
				$cache[ $r['sb_id'] ] = $r;
			}
		}
		
		#do it
		$this->cache->setCache( 'ibEco_blocks', $cache, array( 'array' => 1, 'deletefirst' => 0, 'donow' => 1 ) );		
	}	

	/**
	 * Rebuild the stats
	 */
	public function rebuildStatsCache()
	{
		#init
		$stats		= $this->cache->getCache('ibEco_stats');
		$ecoPoints 	= $this->settings['eco_general_pts_field'];
		$ptsDB		= ( $ecoPoints == 'eco_points' ) ? 'pc' : 'm';
		
		#get member stats
		$mems	= $this->DB->buildAndFetch( array(
												'select'	=> 'SUM('.$ptsDB.'.'.$ecoPoints.') as total_points, SUM(pc.eco_worth) as total_worth, SUM(pc.eco_welfare) as total_welfare',
												'from'		=> array( 'members' => 'm' ),
												'where'  	=> $ptsDB.'.'.$ecoPoints.' != 0 OR pc.eco_worth != 0 OR pc.eco_welfare != 0',
												'add_join'	=> array(
																	array( 'from'	=> array( 'pfields_content' => 'pc' ),
																		   'where'	=> 'm.member_id=pc.member_id'
																		 )
																	)
										)		);

		$stats['total_points']  = $this->registry->ecoclass->makeNumeric( $mems['total_points'], false );
		$stats['total_worth'] 	= $this->registry->ecoclass->makeNumeric( $mems['total_worth'], false );
		$stats['total_welfare'] = $this->registry->ecoclass->makeNumeric( $mems['total_welfare'], false );

		$items = $this->DB->buildAndFetch( array( 'select' => 'count(si_id) as itemCount', 'from' => 'eco_shop_items' ) );

		$stats['item_count'] = intval($items['itemCount']);

		#get latest shopitem
		$si = $this->DB->buildAndFetch( array( 'select' => '*',
										  'from'   => 'eco_shop_items',
										  'where'  => "si_on = 1",
										  'order'  => "si_added_on DESC",
										  'limit'  => array(0,1) ) );

		$stats['last_shop_item'] = $si;

		#do cache
		$this->cache->setCache( 'ibEco_stats', $stats, array( 'array' => 1, 'deletefirst' => 1 ) );
	
		return true;
	}

	/**
	 * Cache the current live lottery
	 * (new to ibEco 1.5)
	 */
	public function rebuildLiveLottoCache()
	{
		#init
		$cache = array();
		
		#query		
		$cache = $this->queryLottos( 'live' );

		// #got rows?
		// if ( $this->DB->getTotalRows() )
		// {		
			// while ( $r = $this->DB->fetch() )
			// {
				// $cache[ $r['l_id'] ] = $r;
			// }
		// }
		
		#do it
		$this->cache->setCache( 'ibEco_live_lotto', $cache, array( 'array' => 1, 'deletefirst' => 0, 'donow' => 1 ) );		
	}
	
	/**
	 * Get a member's Posts Per Day
	 */
	public function getPpdAndActivity( $member_ids )
	{
		#init
		$ppdStats = array();
		
		$this->DB->build( array( 	'select'	=> 'member_id, posts, last_activity, joined',
									'from'		=> 'members',
									'where'  	=> 'member_id IN (' . implode( ',', $member_ids ) . ')' 
							)		);
		$this->DB->execute();
		
		if ( $this->DB->getTotalRows() )
		{
			while ( $row = $this->DB->fetch() )
			{
				#ppd
				$ppd = $row['posts'] / ( (time() - $row['joined'])/86400 );
				$ppdStats[ $row['member_id'] ] = $ppd;
				
				#activity
				$actStats[ $row['member_id'] ] = $row['last_activity'];
			}
		}		
		
		#return
		return array('ppdstats' => $ppdStats, 'actStats' => $actStats );
	}
	
	/**
	 * Update a batch of portfolio amount/balance values for interest
	 */	
	public function adjustPortAmt4Interest($p_ids, $plusMinus, $now, $rate )
	{
		#rate?
		$rate = ( $rate ) ? $rate : 'p_rate';
		$roundDownOrUp = $this->registry->ecoclass->roundDirection;

		#query specs
		$dbUpdates  = 'p_amount = p_amount '.$plusMinus.' '.$roundDownOrUp.'(p_amount * '.$rate.'/100), p_last_hit = '.$now.', p_update_date = '.$now;
		
		#update
		$this->DB->buildAndFetch( array( 'update' => 'eco_portfolio', 'set' => $dbUpdates, 'where' => 'p_id IN (' . implode( ',', $p_ids ) . ')' ) );
	}
	
	/**
	 * Add specific amount as penalty to portfolio amount/balance
	 */	
	public function adjustPortAmt4SpecNum( $p_id, $newBalance, $now )
	{
		$newBalance = $this->registry->ecoclass->makeNumeric($newBalance, false);
		
		$dbUpdates  = 'p_amount = '.$newBalance.', p_last_hit = '.$now.', p_update_date = '.$now;
		
		$this->DB->buildAndFetch( array( 'update' => 'eco_portfolio', 'set' => $dbUpdates, 'where' => 'p_id = ' . $p_id ) );
	}

	/**
	 * Add adjust actual ibEco item current value (not portfolio)
	 */	
	public function adjustStock( $id, $field, $newValue, $now )
	{
		
		if (  $field != 'all' )
		{
			$newValue = intval($newValue);
			$dbUpdates  = $field.' = '.$newValue.', s_last_run = '.$now;
			$this->DB->buildAndFetch( array( 'update' => 'eco_stocks', 'set' => $dbUpdates, 'where' => 's_id = ' . $id ) );			
		}
		else
		{
			$this->DB->update( 'eco_stocks', $newValue, 's_id='.$id );
		}
	}

	/**
	 * Query stock variable value for value adjustment cycle
	 */	
	public function grabStockVarNum( $type, $variable, $lastDone, $variableValue )
	{
		switch ( $type )
		{
			case 'forum':
			
				#what we doin?
				if ( $variable == 'posts' )
				{
					$select = 'count(pid) as count';
					$db		= 'posts';
					$where  = 'post_date > '.$lastDone;
				}
				else if ( $variable == 'registrations' )
				{
					$select = 'count(member_id) as count';
					$db		= 'members';
					$where  = 'joined > '.$lastDone;
				}
				
				#do it
				$grab = $this->DB->buildAndFetch( array( 'select' => $select, 'from' => $db, 'where' => $where ) );				
			
			break;
			
			case 'group':
			
				#only have to do posts
				$where  = 'p.post_date > '.$lastDone.' AND m.member_group_id = '.$variableValue;			
			
			break;

			case 'member':
			
				#only have to do posts
				$where  = 'p.post_date > '.$lastDone.' AND p.author_id = '.$variableValue;				
			
			break;			
		}

		if  ( !$grab )
		{
			$grab	= $this->DB->buildAndFetch( array(
													'select'	=> 'count(p.pid) as count',
													'from'		=> array( 'posts' => 'p' ),
													'where'		=> $where,
													'add_join'	=> array(
																		array( 'from'	=> array( 'members' => 'm' ),
																				'where'	=> 'm.member_id=p.author_id'
																			 )
																		)
											)		);
		}
		
		#return it
		return $grab['count'];
	}	
	
	/**
	 * Query the pts db for a member or group's total points 
	 */	
	public function tallyPointsByVars( $type, $variableValue )
	{
		#init 
		$ecoPoints 	= $this->settings['eco_general_pts_field'];
		$ptsDB		= ( $ecoPoints == 'eco_points' ) ? 'pfields_content' : 'members';
		
		switch ( $type )
		{
			case 'group':
			
				#where
				$where  = 'm.member_group_id = '.$variableValue;			
			
			break;

			case 'member':
			
				#where
				$where  = 'm.member_id = '.$variableValue;				
			
			break;			
		}
		
		$grab	= $this->DB->buildAndFetch( array(
												'select'	=> 'sum(db.'.$ecoPoints.') as count',
												'from'		=> array( $ptsDB => 'db' ),
												'where'		=> $where,
												'add_join'	=> array(
																	array( 'from'	=> array( 'members' => 'm' ),
																			'where'	=> 'm.member_id=db.member_id'
																		 )
																	)
										)		);
		
		#return it
		return $grab['count'];
	}
	
	/**
	 * Grab Welfare Low Lifes
	 */
	public function grabWelfareErs($time)
	{
		#init
		$pts    	= $this->settings['eco_general_pts_field'];
		$ptsDB		= ( $pts == 'eco_points' ) ? 'pc' : 'm';
		$where    	= ($time ) ? 'pc.eco_on_welfare = 1 OR pc.eco_on_welfare BETWEEN 2 AND '.$time : 'pc.eco_on_welfare > 0 ';
				
		#grab em all
		$this->DB->build( array( 	'select'	=> $ptsDB.'.'.$pts.', m.member_id, m.name, m.member_group_id, m.members_display_name, m.joined, m.last_activity, m.posts, m.mgroup_others',
									'from'		=> array( 'members' => 'm' ),
									'group'		=> 'm.member_id',
									'where'  	=> $where,
									'add_join'	=> array(
														0 => array( 'select'	=> 'pc.eco_worth, pc.eco_welfare, pc.eco_on_welfare',
																		'from'  => array( 'pfields_content' => 'pc' ),
																		'where' => 'm.member_id=pc.member_id',
																		'type'  => 'left'
																  ),
														1 => array( 'select'	=> 'g.g_eco, g.g_eco_welfare, g.g_eco_welfare_max',
																		'from'  => array( 'groups' => 'g' ),
																		'where' => 'm.member_group_id=g.g_id',
																		'type'  => 'left'
																  )																  
														)														
							)		);
		$this->DB->execute();
	}
	
	/**
	 * Grab Members to Recalculate their total worth
	 */
	public function grabMembers4WorthRecalc()
	{
		#init
		$pts    	= $this->settings['eco_general_pts_field'];
		$ptsDB		= ( $pts == 'eco_points' ) ? 'pc' : 'm';
		$where    	= $ptsDB.'.'.$pts.' != 0 OR pc.eco_welfare != 0 OR pc.eco_worth != 0';
				
		#grab em all
		$this->DB->build( array( 	'select'	=> $ptsDB.'.'.$pts.', m.member_id, m.name, m.member_group_id, m.members_display_name, m.joined, m.last_activity, m.posts, m.mgroup_others',
									'from'		=> array( 'members' => 'm' ),
									'group'		=> 'm.member_id',
									'where'  	=> $where,
									'add_join'	=> array(
														0 => array( 'select'	=> 'pc.eco_worth, pc.eco_welfare',
																		'from'  => array( 'pfields_content' => 'pc' ),
																		'where' => 'm.member_id=pc.member_id',
																		'type'  => 'left'
																  ),
														1 => array( 'select'	=> 'g.g_eco, g.g_eco_max_pts',
																		'from'  => array( 'groups' => 'g' ),
																		'where' => 'm.member_group_id=g.g_id OR g.g_id IN(m.mgroup_others)',
																		'type'  => 'left'
																  )																  
														)														
							)		);
		$this->DB->execute();
	}
	
	/**
	 * Grab Members for a Mass Donation
	 */
	public function grabMembers4Donation($groupID)
	{
		#init
		$members 	= array();
		$pts    	= $this->settings['eco_general_pts_field'];
		$ptsDB		= ( $pts == 'eco_points' ) ? 'pc' : 'm';
		$where		= ( $groupID != 'all' ) ? "m.member_group_id = ".$groupID." OR m.mgroup_others LIKE '%,".$groupID.",%'"  : "g.g_view_board = 1";
				
		#grab em all
		$this->DB->build( array( 	'select'	=> $ptsDB.'.'.$pts.', m.member_id, m.name, m.member_group_id, m.members_display_name, m.mgroup_others',
									'from'		=> array( 'members' => 'm' ),
									'group'		=> 'm.member_id',
									'where'  	=> $where,
									'add_join'	=> array(
														0 => array( 'select'	=> 'pc.eco_worth, pc.eco_welfare',
																		'from'  => array( 'pfields_content' => 'pc' ),
																		'where' => 'm.member_id=pc.member_id',
																		'type'  => 'left'
																  ),
														1 => array( 'select'	=> 'g.g_eco, g.g_view_board',
																		'from'  => array( 'groups' => 'g' ),
																		'where' => 'm.member_group_id=g.g_id',
																		'type'  => 'left'
																  )																  
														)														
							)		);
		$this->DB->execute();
		
		if ( $this->DB->getTotalRows() )
		{
			while ( $row = $this->DB->fetch() )
			{
				$members[] = $row['member_id'];
			}
		}
		
		return $members;		
	}
	
	/**
	 * Mass update member's points
	 */	
	public function massUpdateMemberPts( $memberIDs, $amt, $plusMinus, $worthToo )
	{
		#which pts field? (gotta make it difficult, jeez)
		$ptsField 	= $this->settings['eco_general_pts_field'];
		$ptsDB		= ( $ptsField == 'eco_points' ) ? 'pfields_content' : 'members';
		$memberIDs  = implode(',',$memberIDs);
		$amt		= $this->registry->ecoclass->makeNumeric($amt, true);
		
		#query specs
		$dbUpdates  = $ptsField.' = '.$ptsField.$plusMinus.$amt;
		
		#execute points!
		$this->DB->buildAndFetch( array( 'update' => $ptsDB, 'set' => $dbUpdates, 'where' => 'member_id IN ('.$memberIDs.')' ) );

		#execute worth!
		if ( $worthToo )
		{
			$worthUpdates = 'eco_worth = eco_worth '.$plusMinus.$amt;
			$this->DB->buildAndFetch( array( 'update' => 'pfields_content', 'set' => $worthUpdates, 'where' => 'member_id IN ('.$memberIDs.')' ) );
		}
	}

	/**
	 * Mass convert points from a different points system to eco_points
	 */	
	public function convertPts2EcoPts( $ptsField )
	{
		#grab em all
		$this->DB->build( array( 	'select'	=> 'm.'.$ptsField.', m.member_id',
									'from'		=> array( 'members' => 'm' ),
									'group'		=> 'm.member_id',
									'where'  	=> 'm.'.$ptsField.' != 0',														
							)		);
		$thxM4rtin = $this->DB->execute();
		
		#got pts?
		if ( $this->DB->getTotalRows() )
		{
			while ( $row = $this->DB->fetch($thxM4rtin) )
			{
				#convert each
				$this->DB->buildAndFetch( array( 'update' => 'pfields_content', 'set' => 'eco_points = '.$row[$ptsField], 'where' => 'member_id = '.$row['member_id'] ) );
			}
		}
	}

	/**
	 * Mass delete pts/welfare/items
	 */	
	public function massDelete( $thing, $id=0 )
	{
		#pts
		if ( $thing == 'points' )
		{
			#which pts field?
			$ptsField 	= $this->settings['eco_general_pts_field'];
			$ptsDB		= ( $ptsField == 'eco_points' ) ? 'pfields_content' : 'members';
				
			$deleted = $this->settings['eco_general_currency'];
			
			$this->DB->buildAndFetch( array( 'update' => $ptsDB, 'set' => $ptsField.' = 0' ) );		
		}
		
		#welfare
		else if ( $thing == 'welfare' )
		{
			$deleted = $this->lang->words['welfare'];
			
			$this->DB->buildAndFetch( array( 'update' => 'pfields_content', 'set' => 'eco_welfare = 0' ) );
		}
		
		#item
		else if ( in_array($thing, array('bank','stock','cc','lt','shopitem' ) ) and $id > 0 )
		{
			#make sure all cache is loaded
			$this->registry->ecoclass->ecoCacheLoader( $thing.'s' );

			$typeAbr 	= $this->registry->ecoclass->getTypeAbr($thing);
			
			$deleted = $this->caches['ibEco_'.$thing.'s'][$id][ $typeAbr.'_title'];

			$this->DB->delete( 'eco_portfolio', "p_type_id = " . $id." AND p_type = '$thing'" );
		}

		return $deleted;	
	}

	/**
	 * Grab a topic
	 */	
	public function grabTopicByID( $tid )
	{
		#grab topic
		$grab	= $this->DB->buildAndFetch( array(
												'select'	=> 't.tid,t.title,t.starter_id,t.starter_name,t.forum_id,t.pinned,t.state,m.member_group_id',
												'from'		=> array( 'topics' => 't' ),
												'where'  	=> 't.tid = '.$tid ,
												'add_join'	=> array(
																	array( 'from'	=> array( 'members' => 'm' ),
																		   'where'	=> 't.starter_id=m.member_id'
																		 )
																	)
										)		);			
		return $grab;
	}
	
	/**
	 * Grab a topic
	 */	
	public function grabPostByID( $pid )
	{
		#grab topic
		$grab	= $this->DB->buildAndFetch( array(
												'select'	=> 'p.pid,p.author_id',
												'from'		=> array( 'posts' => 'p' ),
												'where'  	=> 'p.pid = '.$pid ,
												'add_join'	=> array(
																	array( 'from'	=> array( 'members' => 'm' ),
																		   'where'	=> 'p.author_id=m.member_id'
																		 )
																	)
										)		);			
		return $grab;
	}	

	/**
	 * Edit a topic in some form (via a shop item)
	 */	
	public function adjustTopicViaItem( $tid, $newValues )
	{
		#execute!
		$this->DB->update( 'topics', $newValues, 'tid='.$tid );
	}

	/**
	 * Shop Item Restocker
	 */	
	public function restockShopItem( $itemIDs, $now )
	{
		#got pts?
		if ( is_array( $itemIDs ) and count( $itemIDs ) )
		{
			#do restock
			$this->DB->buildAndFetch( array( 'update' => 'eco_shop_items', 'set' => 'si_inventory = si_restock_amt, si_last_restock = '.$now, 'where' => 'si_id IN (' . implode( ',', $itemIDs ) . ')' ) );
		}
	}
	
	/**
	 * Check `pfields_content` and resync with `members` if necessary
	 */
	public function resync_pfields_content()
	{
		#grab em all
		$this->DB->build( array( 	'select'	=> 'm.member_id',
									'from'		=> array( 'members' => 'm' ),
									'add_join'	=> array(
														0 => array( 'select'	=> 'pc.member_id AS pcMemberID',
																		'from'  => array( 'pfields_content' => 'pc' ),
																		'where' => 'm.member_id=pc.member_id',
																		'type'  => 'left'
																  )															
														)														
							)		);
		$outer = $this->DB->execute();
		
		if ( $this->DB->getTotalRows() )
		{
			while ( $r = $this->DB->fetch($outer) )
			{
				#no pfields_content row for this member?
				if ( !$r['pcMemberID'] )
				{
					$this->DB->insert( 'pfields_content', array( 'member_id' => $r['member_id'] ) );
				}
			}
		}
	}
	
	/**
	 * Check commenters latest comment time to make sure s/he still gets the points
	 */
	public function getLastComment($commenter)
	{
		$comment	= $this->DB->buildAndFetch( array(
										'select'	=> 'comment_date',
										'from'		=> 'profile_comments',
										'order'		=> 'comment_date DESC',
										'where'  	=> 'comment_by_member_id = '.$commenter,
										'limit'		=> array( 0, 1 )
								)		);
								
		return $comment;
	}

	/**
	 * Check commenters latest comment time to make sure s/he still gets the points
	 */
	public function checkForEmoticons($emo_text)
	{
		#Query to look for existing emoticons with that clickable text		 		
		$this->DB->buildAndFetch( array( 'select' => 'typed', 'from' => 'emoticons', 'where' => "typed='{$emo_text}'" ) );
		
		if ( $this->DB->getTotalRows() )
		{				
			return true;
		}
	}
	
	/**
	 * Count number of lotteries in system
	 * (new to ibEco 1.5)
	 */
	public function countLottos()
	{
		#get count
		$count = $this->DB->buildAndFetch( array(  	'select'	 => 'COUNT( l_id ) as lottoCount',
													'from'		 => 'eco_lotteries'
										) 	   );
		return $count['lottoCount'];
	}	
	
	/**
	 * Query those lottos, ya'll
	 * (new to ibEco 1.5)
	 */
	public function queryLottos( $howMany, $ids=null, $start=0 )
	{
		#init
		if ( $howMany == 'all' )
		{
			$where    	= ( is_array($ids) ) ? 'el.l_id IN (' . implode( ',', $ids ) . ')' : 'el.l_id > 0';
			$end		= intval($this->settings['eco_general_pp']);
		}
		else if ( $howMany == 'live' )
		{
			$where		= 'el.l_winner_id = 0';
			$end		= 1;
		}		
		else
		{
			$where		= 'el.l_id = '.$ids;
			$end		= 1;
		}
		
		#grab all the lotteries, and count tickets and such
		$this->DB->build( array( 	'select'	=> 'el.*',
									'from'		=> array( 'eco_lotteries' => 'el' ),
									'group'		=> 'el.l_id',
									'order'		=> 'el.l_start_date DESC',
									'where'  	=> $where,
									'limit'		=> array( $start, $end ),
									'add_join'	=> array(
														0 => array( 'select' 	=> 'COUNT(elt.ltix_id) as tickets_purchased, SUM(elt.ltix_paid) as total_ticket_value_paid',
																		'from'	=> array( 'eco_lottery_tix' => 'elt' ),
																		'where'	=> "elt.ltix_lotto_id = el.l_id",
																		'type'	=> 'left',
																  )																	  
														)
							)		);
		$this->DB->execute();
		
		if ( $howMany != 'all' )
		{
			if ( $this->DB->getTotalRows() )
			{
				while ( $row = $this->DB->fetch() )
				{
					$lotto = $row;
				}
			}
			
			return $lotto;
		}
	}

	/**
	 * Insert a new lottery
	 * (new to ibEco 1.5)
	 */
	public function newLotto($rollOverMoney=0)
	{
		$this->DB->insert( 'eco_lotteries', array(  'l_start_date' 		=> time(),
													'l_draw_date' 		=> time() + 86400 * intval($this->settings['eco_lotto_cycle']),
													'l_initial_pot' 	=> intval($this->settings['eco_lotto_init_pot_size'] + $rollOverMoney),
													'l_final_pot_size' 	=> intval($this->settings['eco_lotto_init_pot_size'] + $rollOverMoney),
													'l_tix_price' 		=> intval($this->settings['eco_lotto_ticket_price']),
													'l_num_balls' 		=> intval($this->settings['eco_lotto_num_balls']),
													'l_top_num' 		=> intval($this->settings['eco_lotto_top_num'])) );
	}
	
	/**
	 * Find any lottery tickets already input (number selections made) for a lotto (live most likely)
	 * (new to ibEco 1.5)
	 */
	public function grabUsersPickedLottoTix($memberID, $lottoID=0)
	{
		#any crazy group odds to deal with?
		if (intval($lottoID) == 0)
		{
			#grab items from cache
			if ($this->caches['ibEco_live_lotto']['l_id'] > 0)
			{ 		
				$lotto = $this->caches['ibEco_live_lotto'];
			}
			
			$lottoID = $lotto['l_id'];
		}
		
		if (intval($lottoID) > 0)
		{
			$alreadyPickedTickets = array();
			
			$count = 1;
			
			if ($memberID > 0)
			{
				$where		= 'elt.ltix_member_id = '.$memberID.' AND elt.ltix_lotto_id = '.$lottoID;		
			}
			else
			{
				$where		= 'elt.ltix_lotto_id = '.$lottoID;		
			}
			
			#grab all the items in the portfolio and grab data for each one
			$this->DB->build( array( 	'select'	=> 'elt.ltix_id, elt.ltix_purch_date, elt.ltix_lotto_id, elt.ltix_paid, elt.ltix_member_id, elt.ltix_numbers',
										'from'		=> array( 'eco_lottery_tix' => 'elt' ),
										'where'  	=> $where,
										//'order'		=> 'group_odds DESC',
										'add_join'	=> array(
															0 => array( 'select'	=> 'm.member_id, m.member_group_id',
																			'from'  => array( 'members' => 'm' ),
																			'where' => 'm.member_id=elt.ltix_member_id',
																			'type'  => 'left'
																	  ),
															1 => array( 'select'	=> 'g.g_eco_lottery_odds AS group_odds',
																			'from'  => array( 'groups' => 'g' ),
																			'where' => 'm.member_group_id=g.g_id',
																			'type'  => 'left'
																	  )																  
															)
								)		);
			$this->DB->execute();	
			
			if ( $this->DB->getTotalRows() )
			{
				while ( $row = $this->DB->fetch() )
				{				
					$alreadyPickedTickets[$count] = $row;
					$count++;
				}
			}
			
			return $alreadyPickedTickets;
		}
		return false;
	}
	
	//$this->registry->ecoclass->showVars($inserts);
	/**
	* Insert new lottery picks into the lotto_tix database
	*/
	public function insertLottoTicketPicks($picks, $lotto, $member = 0)
	{
		$member = ($member == 0) ? $this->memberData : $member;
		
		#go through each lottery ticket and add it's picks to the DB
		if ( is_array($picks) && count($picks) )
		{
			foreach ($picks as $pick)
			{
				sort($pick);
				$inserts = array( 'ltix_member_id' 	=> $member['member_id'],
								  'ltix_purch_date' => time(),
								  'ltix_lotto_id' 	=> $lotto['l_id'],
								  'ltix_paid' 		=> $lotto['l_tix_price'],
								  'ltix_numbers' 	=> implode(",", $pick)
								);

				$this->DB->insert( 'eco_lottery_tix', $inserts );
			}
		}
		
		#update lottery
		$totalPaid = count($picks) * $lotto['l_tix_price'];
		$lottoUpdates = 'l_tix_purchased = l_tix_purchased + '.count($picks).', l_final_pot_size = l_final_pot_size + '.$totalPaid;
		$this->DB->buildAndFetch( array( 'update' => 'eco_lotteries', 'set' => $lottoUpdates, 'where' => "l_id = ".$lotto['l_id']) );
		
		#remove the tickets that were just entered from this member's portfolio
		$this->deleteItemFromPortfolio("lottery", $lotto['l_id'], $member['member_id']);
	}
	
	/**
	* Delete item from a user's portfolio
	*/
	public function deleteItemFromPortfolio($thing, $thingID, $memberID, $typeClass=false)
	{
		$p_type_class =	($typeClass) ? "AND p_type_class = ".$typeClass : "";
		
		$this->DB->delete( 'eco_portfolio', "p_member_id 	= ".$memberID." AND 
											 p_type_id 		= ".$thingID." AND 
											 p_type 		= '$thing'".
											$p_type_class);
	}
	
	/**
	* Delete old lotto tickets from cart
	*/
	public function deleteOldLottoTicketsFromCart($lottoID)
	{
		$this->DB->delete( 'eco_cart', "c_type_id = ".$lottoID." AND c_type = 'lottery'" );
	}
	
	/**
	* Find unused lottery tickets that need to be deleted and refunded
	*/
	public function findUnusedOldLottoTickets()
	{
		$unusedTickets = array();
		
		$this->DB->build( array( 	'select'	=> '*',
									'from'		=> 'eco_portfolio',
									'where'		=> "p_type = 'lottery'"
							)		);
		$this->DB->execute();	
		
		if ( $this->DB->getTotalRows() )
		{
			while ( $row = $this->DB->fetch() )
			{
				$unusedTickets[ $row['p_member_id'] ] = $row;
			}
		}
		
		return $unusedTickets;
	}
	
	/**
	* Delete old unused lotto tickets from portfolio
	*/
	public function deleteOldLottoTicketsFromPortfolio()
	{
		$this->DB->delete( 'eco_portfolio', "p_type = 'lottery'" );	
	}
	
	/**
	* Finalize lottery (by adding winners)
	*/
	public function finalizeLottery($winningPlayers, $numWinners, $lottoID, $winningNums)
	{
		if ($numWinners == 1)
		{
			$lottoUpdates = 'l_winner_id = '.$winningPlayers[0].", l_winning_nums = '$winningNums'";

			$this->DB->buildAndFetch( array( 'update' => 'eco_lotteries', 'set' => $lottoUpdates, 'where' => 'l_id = '.$lottoID ) );
		}
		else if ($numWinners > 1)
		{
			$winners = implode(",", $winningPlayers);

			$lottoUpdates = "l_winners = '$winners', l_winning_nums = '$winningNums', l_winner_id = -1";

			$this->DB->buildAndFetch( array( 'update' => 'eco_lotteries', 'set' => $lottoUpdates, 'where' => 'l_id = '.$lottoID ) );		
		}
		else
		{
			#no winners......
			$lottoUpdates = "l_winning_nums = '$winningNums', l_winner_id = -1";
			
			//$this->registry->ecoclass->showVars($lottoUpdates);	
			
			$this->DB->buildAndFetch( array( 'update' => 'eco_lotteries', 'set' => $lottoUpdates, 'where' => 'l_id = '.$lottoID ) );			
		}
	}
	
	/**
	* Simple query to adjust a member's lottery ticket numbers
	*/
	public function adjustLottoTicketsNumbers($ticketID, $newNumbers)
	{
		$ticketUpdates = "ltix_numbers = '$newNumbers'";
		
		$this->DB->buildAndFetch( array( 'update' => 'eco_lottery_tix', 'set' => $ticketUpdates, 'where' => 'ltix_id = '.$ticketID ) );	
	}
	
	/**
	 * Count number of times a user has purchased a particular item over the past 24 hours
	 * (new to ibEco 1.5)
	 */
	public function countNumItemPurchasesToday($memberID, $itemID)
	{
		$todayStartedOn = time() - 86400;
		
		#get count
		$count = $this->DB->buildAndFetch( array(  	'select'	 => 'SUM( l_amount ) as numPurchases',
													'from'		 => 'eco_logs',
													'where' 	 => "l_member_id=".$memberID." AND l_action='purchase' AND l_date > ".$todayStartedOn." 
																	 AND l_subject_id=".$itemID." AND l_subject_name = 'shopitem'"
										) 	   );
		return intval($count['numPurchases']);
	}
	
	/**
	 * Mass Recalculate Points query
	 * (new to ibEco 2.0)
	 */
	public function massRecalculateQuery($lastDid=0)
	{
		$this->DB->build( array( 	'select'	=> 'p.pid, p.topic_id, p.author_id, p.new_topic, p.queued',
									'from'		=> array( 'posts' => 'p' ),
									'where'  	=> "p.pid > ".$lastDid,
									'limit'		=> array( 0, 100 ),
									'order'		=> 'p.pid',
									'add_join'	=> array(
														0 => array( 'select'	=> 't.forum_id, t.starter_id',
																		'from'  => array( 'topics' => 't' ),
																		'where' => 't.tid=p.topic_id',
																		'type'  => 'left'
																  ),
														1 => array( 'select'	=> 'f.eco_tpc_pts, f.eco_rply_pts, f.eco_get_rply_pts',
																		'from'  => array( 'forums' => 'f' ),
																		'where' => 'f.id=t.forum_id',
																		'type'  => 'left'
																  ),
														2 => array( 'select'	=> 'm.member_group_id',
																		'from'  => array( 'members' => 'm' ),
																		'where' => 'm.member_id=p.author_id',
																		'type'  => 'left'
																  ),
														3 => array( 'select'	=> 'ms.member_group_id AS starter_group_id',
																		'from'  => array( 'members' => 'ms' ),
																		'where' => 'ms.member_id=t.starter_id',
																		'type'  => 'left'
																  )															  
														)														
							)		);
		$bigQuery = $this->DB->execute();
		
		return $bigQuery;
	}
}