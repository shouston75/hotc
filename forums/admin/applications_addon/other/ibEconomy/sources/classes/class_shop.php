<?php

/**
 * (e32) ibEconomy
 * Shop Class
 * @ Shop Tab
 * For All Your Shop Item Needs
 */

if ( ! defined( 'IN_IPB' ) )
{
	print "<h1>Incorrect access</h1>You cannot access this file directly. If you have recently upgraded, make sure you upgraded all the relevant files.";
	exit();
}

class class_shop
{
	private $showPage	= "";
	
	protected $registry;
	protected $DB;
	protected $settings;
	protected $request;
	protected $lang;
	protected $member;
	protected $cache;
	protected $caches;

	/**
	 * Class entry point
	 */
	public function __construct( ipsRegistry $registry )
	{
        $this->registry     =  ipsRegistry::instance();
        $this->DB           =  $this->registry->DB();
        $this->settings     =& $this->registry->fetchSettings();
        $this->request      =& $this->registry->fetchRequest();
        $this->lang         =  $this->registry->getClass('class_localization');
        $this->member       =  $this->registry->member();
        $this->memberData  	=& $this->registry->member()->fetchMemberData();
        $this->cache        =  $this->registry->cache();
        $this->caches       =& $this->registry->cache()->fetchCaches();		
	}

	/**
	 * Show All Categories
	 */
	public function showCats()
	{		
		#init vars
		$catsShown 	= 0;
		$catRows 	= '';
		
		#grab cats from cache
		if ( is_array($this->caches['ibEco_shopcats'] ) )
		{ 		
			foreach ( $this->caches['ibEco_shopcats']  AS $cat )
			{
				#disabled? skip it!
				if ( !$cat['sc_on'] )
				{
					continue;
				}
				#no permission?  skip it!
				if ( $cat[ 'sc_use_perms'] && !$this->registry->permissions->check( 'view', $cat ) )
				{
					continue;
				}
				
				#total...
				$totalCats++;
				
				#earlier than this current page?
				if ( $totalCats <= $this->request['st'] )
				{
					continue;
				}				
				
				#done with current page?
				if ( $catsShown >= $this->settings['eco_general_pp'] )
				{
					continue;
				}
				
				#add row
				$cat 	  = $this->registry->ecoclass->formatShopCatRow($cat);
				$catRows .= $this->registry->output->getTemplate('ibEconomy2')->shop_cats_row($cat);
				
				#num shown
				$catsShown++;				
			}
		}

		#I don't have any items of this type. :(
		if ( !$catsShown )
		{
			$error = str_replace( "<%TYPE%>", $this->lang->words['shop_categories'], $this->lang->words['none_to_show'] );
			$this->showPage = $this->registry->output->getTemplate('ibEconomy')->error($error);

			return $this->showPage;
		}		
		
		#page links
		$pages		= $this->registry->output->generatePagination( array(
					'totalItems'        => $totalCats,
					'itemsPerPage'  	=> intval($this->settings['eco_general_pp']),
					'currentStartValue'	=> intval($this->request['st']),
					'baseUrl'           => "app=ibEconomy&amp;tab=shop&amp;area=categories",
		)      );	
		
		#throw rows to wrapper
		$cat_list 		= $this->registry->output->getTemplate('ibEconomy2')->shop_cats_wrapper($catRows, $pages);
		$this->showPage = $cat_list;
		
		return $this->showPage;
	}
	
	/**
	 * Show Items
	 */
	public function showItems()
	{		
		#init vars
		$itemsShown 	= 0;
		$itemRows 		= '';
		$catID 			= $this->request['cat'];
		
		#grab cats from cache
		if ( is_array($this->caches['ibEco_shopitems'] ) )
		{ 		
			foreach ( $this->caches['ibEco_shopitems']  AS $item )
			{
				#can't view item for some reason? skip it!
				if ( ! $this->checkItem( $item, $catID ) )
				{
					continue;
				}				
				
				#total...
				$totalItems++;
				
				#earlier than this current page?
				if ( $totalItems <= $this->request['st'] )
				{
					continue;
				}			
				
				#done with current page?
				if ( $itemsShown >= $this->settings['eco_general_pp'] )
				{
					continue;
				}
				
				#add row
				$item = $this->registry->ecoclass->formatRow($item, 'shopitem', false);
				
				$itemRows .= $this->registry->output->getTemplate('ibEconomy2')->item_row($item);
				
				#num shown
				$itemsShown++;
			}
		}

		#I don't have any items of this type. :(
		if ( !$itemsShown )
		{
			$error = str_replace( "<%TYPE%>", $this->lang->words['shop_items'], $this->lang->words['none_to_show'] );
			$this->showPage = $this->registry->output->getTemplate('ibEconomy')->error($error);

			return $this->showPage;
		}		
		
		#page links
		$pages		= $this->registry->output->generatePagination( array(
					'totalItems'        => $totalItems,
					'itemsPerPage'  	=> intval($this->settings['eco_general_pp']),
					'currentStartValue'	=> intval($this->request['st']),
					'baseUrl'           => "app=ibEconomy&amp;tab=shop&amp;area=items&amp;cat=".$catID,
		)      );	
		
		#throw rows to wrapper
		$item_list 		= $this->registry->output->getTemplate('ibEconomy2')->items_wrapper($itemRows, $this->lang->words['shopitems'], $pages );
		$this->showPage = $item_list;
		
		return $this->showPage;
	}
	
	/**
	 * Show 1 Item
	 */
	public function item()
	{		
		#init var
		$itemID 	= $this->request['id'];
		
		#grab items from cache
		if ( is_array($this->caches['ibEco_shopitems'][ $itemID ] ) )
		{ 		
			#init
			$item = $this->caches['ibEco_shopitems'][ $itemID ];
			
			#check item
			if ( $this->checkItem( $item, $noid=0 ) );				
			{	
				#format row
				$item = $this->registry->ecoclass->formatRow($item, 'shopitem', false);					
				$itemPage 	= $this->registry->output->getTemplate('ibEconomy2')->item_row($item);
			}
		}

		#I don't have any items of this type. :(
		if ( !$itemPage )
		{
			$error = str_replace( "<%TYPE%>", $this->lang->words['shop_item'], $this->lang->words['no_perm_to_show_it'] );
			$this->showPage = $this->registry->output->getTemplate('ibEconomy')->error($error);

			return $this->showPage;
		}		
		
		#throw rows to wrapper
		$this->showPage		= $this->registry->output->getTemplate('ibEconomy2')->items_wrapper($itemPage, $item['si_title'], '' );
		
		return $this->showPage;
	}

	/**
	 * Check to see if item is good to view eh
	 */
	public function checkItem( $item, $catID )
	{
		#init check
		$itemIsGood = 1;
		
		#disabled? skip it!
		if ( ! $item['si_on'] )
		{
			$itemIsGood = 0;
		}
		#no permission?  skip it!
		if ( $item[ 'si_use_perms'] && !$this->registry->permissions->check( 'view', $item ) )
		{
			$itemIsGood = 0;
		}
		#only need item from 1 specific category?
		if ( $catID > 0 && $item['si_cat'] != $catID )
		{
			$itemIsGood = 0;
		}
		#category parent current disabled?
		if ( !$this->caches['ibEco_shopcats'][ $item['si_cat'] ]['sc_on'] )
		{
			$itemIsGood = 0;
		}
		#no permission to view parent category?
		if ( $this->caches['ibEco_shopcats'][ $item['si_cat'] ]['sc_use_perms'] && !$this->registry->permissions->check( 'view', $this->caches['ibEco_shopcats'][ $item['si_cat'] ] ) )
		{
			$itemIsGood = 0;
		}
		
		return $itemIsGood;
	}
	
	/**
	 * Grab a Shop Item
	 */
	public function grabAShopItem($si_id)	
	{
		#grab the shop item and return that crap
		$si = $this->caches['ibEco_shopitems'][ $si_id ] ;			
		
		#format the array vals
		$si = $this->registry->ecoclass->formatRow($si, 'shopitem', false);	
		return $si;
	}	
}