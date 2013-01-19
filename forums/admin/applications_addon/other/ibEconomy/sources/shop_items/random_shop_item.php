<?php

/**
 * (e32) ibEconomy
 * Shop Item: Random Shop Item
 */

if ( ! defined( 'IN_IPB' ) )
{
	print "<h1>Incorrect access</h1>You cannot access this file directly. If you have recently upgraded, make sure you upgraded all the relevant files.";
	exit();
}

class class_shop_item implements ibEconomy_shop_item
{
	protected $registry;
	protected $DB;
	protected $settings;
	protected $request;
	protected $lang;
	protected $member;
	protected $cache;	

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
	
	//*************************//
	//($%^   ADMIN STUFF   ^%$)//
	//*************************//	

	/**
	 * Send the "Stock" Title
	 */
	public function title()
	{
		return $this->lang->words['random_shop_item'];
	}
	
	/**
	 * Send the "Stock" Description
	 */
	public function description()
	{
		return $this->lang->words['random_shop_item'];
	}
	
	/**
	 * Need to pick self or others applicable?
	 */
	public function otherOrSelf()
	{
		return FALSE;
	}	

	/**
	 * Send the Extra Settings
	 */
	public function extra_settings()
	{
		$itemSettings = array( 0  => array( 'form_type' => 'formMultiDropdown',
										    'field' 	=> 'si_protected',
										    'words' 	=> $this->lang->words['included_shopitems'],
										    'desc' 		=> $this->lang->words['unselected_items_will_not_be_drawn'],
										    'type'      => 'shopitems'
										 )										 
							 );
		
		return $itemSettings;
	}
	
	//*************************//
	//($%^   PUBLIC STUFF   ^%$)//
	//*************************//	

	/**
	 * Using Item HTML
	 */
	public function usingItem($theItem)
	{
		$itemHtml = array();
		
		return $itemHtml;
	}
	
	/**
	 * Use Item
	 */
	public function useItem($theItem,$myPortItem)
	{
		#init
		$returnMe 	= '';
		
		#pick the item
		$itemCache 	= $this->caches['ibEco_shopitems'];
		
		if (  is_array( $itemCache ) and count( $itemCache ) )
		{
			#only select few are eligable?
			if ( $theItem['si_protected'] )
			{
				$possibleItems = explode(',',$theItem['si_protected']);
				
				#remove empty spots of array
				foreach ($possibleItems as $key => $value)
				{
					if (! intval($value)) 
					{
						unset($possibleItems[$key]);
					}
				}

				$numPossible = count( $possibleItems );
				$numDrawn	 = rand(1, intval($numPossible));
			}
			else
			{
				$numPossible = count( $itemCache );
				$numDrawn	 = rand(1, intval($numPossible));			
			}
			
			#loop through items, stop on random number picked above
			foreach ( $itemCache AS $row )
			{
				if ( !$possibleItems || in_array( $row['si_id'], $possibleItems ) )
				{
					$letsCount++;
					
					if ( $letsCount ==  $numDrawn )
					{
						$selectedItemID = $row['si_id'];
						
						break;
					}
				}
			}
		}
		else
		{
			$returnMe['error'] = $this->lang->words['no_shop_items_in_cache'];
		}
		
		#add 1 if this item is the one picked
		if ( $myPortItem['p_type_id'] == $selectedItemID )
		{
			$myPortItem['p_amount']++;
		}
		
		#no errors, use it!
		if ( ! $returnMe['error'] )
		{
			#use it
			$this->doUseItem($selectedItemID);
			
			#finish up
			$this->registry->ecoclass->finishUpItemUse($theItem,$myPortItem,$this->caches['ibEco_shopitems'][ $selectedItemID ]['si_title']);
			
			#add to redirect text
			$returnMe['redirect_text'] = $this->caches['ibEco_shopitems'][ $selectedItemID ]['si_title'].' '.$this->lang->words['has_been_added_to_your_portfolio'];
		}
		
		return $returnMe;
	}

	/**
	 * Use Item EXECUTION
	 */
	public function doUseItem($selectedItemID)
	{
		#grab from cache
		$daItem = $this->caches['ibEco_shopitems'][ $selectedItemID ];
		
		#pretend its a cart item
		$cartItem					= array();
		$cartItem['c_quantity'] 	= 1;
		$cartItem['c_type_id'] 		= $selectedItemID;
		$cartItem['c_type_class'] 	= '';
		
		#update post count
		$this->registry->ecoclass->addItem2Portfolio( $cartItem, 'shopitem', $daItem, 0, $this->memberData );
	}
}