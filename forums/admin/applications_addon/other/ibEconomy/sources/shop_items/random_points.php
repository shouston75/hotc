<?php

/**
 * (e32) ibEconomy
 * Shop Item: Random Points
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
		return $this->lang->words['Random'].' '.$this->settings['eco_general_currency'];
	}
	
	/**
	 * Send the "Stock" Description
	 */
	public function description()
	{
		return $this->lang->words['Random'].' '.$this->settings['eco_general_currency'];
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
		$itemSettings = array( 0 => array( 'form_type' 	=> 'formsimpleinput',
										   'field' 		=> 'si_min_num',
										   'words' 		=> $this->lang->words['min_num_of'].' '.$this->settings['eco_general_currency'],
										   'desc' 		=> $this->lang->words['min_num_pts_exp']
										 ),
							   1 => array( 'form_type' 	=> 'formsimpleinput',
										   'field' 		=> 'si_max_num',
										   'words' 		=> $this->lang->words['max_num_of'].' '.$this->settings['eco_general_currency'],
										   'desc' 		=> $this->lang->words['max_num_pts_exp']
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
		$theItem['si_min_num'] = ( $theItem['si_min_num'] ) ? $theItem['si_min_num'] : 50;
		$theItem['si_max_num'] = ( $theItem['si_max_num'] ) ? $theItem['si_max_num'] : 100;
		
		#get a random num?
		$numDrawn = rand($theItem['si_min_num'] , $theItem['si_max_num']);

		#if negative, is it allowed?
		if( !$returnMe['error'] && !$this->settings['eco_shopitems_steal_neg'] && $this->memberData[ $this->settings['eco_general_pts_field'] ] + $numDrawn < 0 )
		{
			if ( !$this->settings['eco_shopitems_steal_zero'] )
			{
				$returnMe['error'] = $this->lang->words['would_make_member_negative'];
			}
			else
			{
				$numDrawn = -$this->memberData[ $this->settings['eco_general_pts_field'] ];
			}
		}
		
		#no errors, use it!
		if ( ! $returnMe['error'] )
		{
			#use it
			$this->doUseItem($numDrawn);
			
			#finish up
			$this->registry->ecoclass->finishUpItemUse($theItem,$myPortItem,$this->settings['eco_general_cursymb'].$this->registry->getClass('class_localization')->formatNumber( $numDrawn ));
			
			#add to redirect text
			$returnMe['redirect_text'] = $this->lang->words['you_have_been_awarded'].' '.$this->registry->getClass('class_localization')->formatNumber( $numDrawn ).' '.$this->settings['eco_general_currency'].$this->lang->words['!'];
		}
		
		return $returnMe;
	}

	/**
	 * Use Item EXECUTION
	 */
	public function doUseItem($numDrawn)
	{
		#add points
		$this->registry->mysql_ibEconomy->updateMemberPts($this->memberData['member_id'], $numDrawn, '+', true);	
	}
}