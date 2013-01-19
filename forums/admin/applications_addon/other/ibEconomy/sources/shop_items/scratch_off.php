<?php

/**
 * (e32) ibEconomy
 * Shop Item: Scratch Offs
 * Created by jose.rob.jr
 * member of emoneyCodes.com
 */

if ( ! defined( 'IN_IPB' ) )
{
	print "<h1>Incorrect access</h1>You cannot access this file directly. If you have recently upgraded, make sure you upgraded all the relevant files.";
	exit();
}

class Scratch_off_Item
{
	public $type, $value;
	function __construct($type, $value)
	{
		$this->type = $type;
		$this->value = $value;
	}
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
		return $this->lang->words['scratch_off'];
	}
	
	/**
	 * Send the "Stock" Description
	 */
	public function description()
	{
		return $this->lang->words['scratch_off'];
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
		
		/* Workaround for the predefined field names.
		     si_protected = drawn items
			 si_max_num = num_rows
			 si_min_num = num_cols
			 si_extra_settings_1 = repetitions_to_win
			 si_extra_settings_2 = items_chances
			 si_extra_settings_3 = drawn_money
			 si_extra_settings_4 = max_wins
		*/
	
		$itemSettings = array( 0 => array( 'form_type' 	=> 'formTextarea',
										   'field' 		=> 'si_extra_settings_3',
										   'words' 		=> $this->settings['eco_general_currency'] . ' '.$this->lang->words['raffled'], //blah translate it :P
										   'desc' 		=> $this->lang->words['type_the_amt_of'].' '.$this->settings['eco_general_currency'] .' '.$this->lang->words['chances_desc']
										 ),
							   1 => array( 'form_type' 	=> 'formMultiDropdown',
										   'field' 		=> 'si_protected',
										   'words' 		=> $this->lang->words['raffled_items'],
										   'desc' 		=> $this->lang->words['select_items_raffle'],
										   'type'      => 'shopitems'
										 ),
								2 => array( 'form_type' => 'formTextarea',
											'field'		=> 'si_extra_settings_2',
											'words'		=> $this->lang->words['items_chances'],
											'desc'		=> $this->lang->words['items_chances_desc'],
										 ),
								3 => array( 'form_type' 	=> 'formsimpleinput',
										   'field' 		=> 'si_extra_settings_1',
										   'words' 		=> $this->lang->words['num_reps_win'],
										   'desc' 		=> $this->lang->words['num_times_display_win']
										 ),
								4 => array( 'form_type' 	=> 'formsimpleinput',
										   'field' 		=> 'si_extra_settings_4',
										   'words' 		=> $this->lang->words['max_num_vics'],
										   'desc' 		=> $this->lang->words['this_limits_raffle_desc']
										 ),
								5 => array( 'form_type' 	=> 'formsimpleinput',
										   'field' 		=> 'si_max_num',
										   'words' 		=> $this->lang->words['num_rows']
										 ),	
								6 => array( 'form_type' 	=> 'formsimpleinput',
										   'field' 		=> 'si_min_num',
										   'words' 		=> $this->lang->words['num_cols']
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
		$fields 		= array();
		$returnMe 		= '';
		$drawn_items 	= ( $theItem['si_protected'] ) ? $theItem['si_protected'] : '';
		$num_rows 		= ( $theItem['si_max_num'] ) ? intval($theItem['si_max_num']) : 2;
		$num_cols 		= ( $theItem['si_min_num'] ) ? intval($theItem['si_min_num']) : 3;
		$repetitions_to_win = ( $theItem['si_extra_settings_1'] ) ? intval($theItem['si_extra_settings_1']) : 3;
		$items_chances 	= ( $theItem['si_extra_settings_2'] ? $theItem['si_extra_settings_2'] : '');
		$drawn_money 	= ( $theItem['si_extra_settings_3'] ? $theItem['si_extra_settings_3'] : '');
		$max_wins		= ( $theItem['si_extra_settings_4'] ? $theItem['si_extra_settings_4'] : 1);
		$items			= array(array(new Scratch_off_Item('money', 0),0));
		
		#invalid configs
		if($num_rows <= 0) $num_rows = 2;
		if($num_cols <= 0) $num_cols = 3;
		if($repetitions_to_win <= 1) $repetitions_to_win = 3;
		
		#money
		$moneyParts = explode("\n", $drawn_money);
		if(!empty($moneyParts))
		{
			foreach($moneyParts as $part)
			{
				$values = @explode('=', $part, 2);
				$money = @intval($values[0]);
				$chance = @intval($values[1]); //syntax: 100%; 55%... it's not float!
				#no negative and zero values
				if($money <= 0 || $chance <= 0) continue;
				$fields[] = new Scratch_off_Item('money', $money);
				$item = &$fields[count($fields)-1]; //0% of chance is not valid and will be changed to 1%!
				$items[] = array(&$item, 0);
				for($i = 1; $i < $chance; $i++)
				{
					$fields[] = &$item;
				}
			}
		}
				
		#items
		if(!empty($drawn_items))
		{
			#pick the item
			$itemCache 	= $this->caches['ibEco_shopitems'];
			
			$items_to_add = array();
			
			#validate the items, removes keys without value and deleted items
			if (  is_array( $itemCache ) and count( $itemCache ) )
			{
				$drawn_items = explode(',', $drawn_items);
				array_shift($drawn_items); // this first is always null
				foreach($drawn_items as $k=>$drawn_item)
				{
					# invalid items
					if(empty($drawn_item)) continue;
					if(!isset($itemCache[$drawn_item])) continue;
					
					$items_to_add[$k] = $drawn_item;
				}
				
			}
			else
			{
				$returnMe['error'] = $this->lang->words['no_shop_items_in_cache'];
				return $returnMe;
			}
			
			if(!empty($items_to_add))
			{
				if(empty($items_chances))
				{
					$count_fields = count($fields);
					$percent = ($count_fields >= 100? $count_fields : 100 - $count_fields);
					$items_chances = str_repeat( intval($percent / count($items_to_add))."%\n", count($items_to_add));
				}
				else
				{
					$items_chances = explode("\n",$items_chances);
				}
				
				foreach($items_to_add as $k=>$item_to_add)
				{
					$chances = (!empty($items_chances[$k]) ? $items_chances[$k] : 1);
					$fields[] = new Scratch_off_Item('item', $item_to_add);
					$item = &$fields[count($fields)-1]; //0% of chance is not valid and will be changed to 1%!
					$items[] = array(&$item, 0);
					for($i = 1; $i < $chances; $i++)
					{
						$fields[] = &$item;
					}
				}
			}
		}
		
		if(empty($fields))
		{
			#TODO Change the hard-coded string to a translatable way
			$returnMe['error'] = $this->lang->words['nothing_to_be_raffled'];
			return $returnMe;
		}
		
		# raffle begins
		shuffle($fields);
		$wins = array();
		
		$game = array();
		for($i = 0; $i < $num_rows; $i++)
		{
			$game[$i] = array();
			for($j = 0; $j < $num_cols; $j++)
			{
				//$num = rand(0, count($fields));
				$num = array_rand($fields);
				$game[$i][$j] = $fields[$num];
				foreach($items as &$item)
				{
					if($item[0] === $fields[$num])
					{
						if( ($item[1] + 1 >= $repetitions_to_win && $max_wins != 0 && count($wins) >= $max_wins) || in_array($item[0], $wins) )
						{
							#can't win more!
							$items[0][1]++;
							$game[$i][$j] = &$items[0][0];
							break;
						}
						else
						{
							$item[1]++;
						}
						
						if($item[1] == $repetitions_to_win)
						{
							$wins[] = $item[0];
						}
						break;
					}
				}
				unset($fields[$num]);
			}
		}
		
		# use it 
		$money = 0;
		$item_won = array();
		if(!empty($wins))
		{
			foreach($wins as $won)
			{
				$this->doUseItem($won->type, $won->value);
				if($won->type == 'money') $money += $won->value;
				else $item_won[] = $itemCache[ $won->value ]['si_title'];
			}
		}
		
		#add to redirect text
		if(!empty($wins))
		{
			$award = '';
			$returnMe['error'] = $this->lang->words['you_have_been_awarded_with'].' ';
			if($money > 0)
			{
				$award .= $this->settings['eco_general_cursymb'] . ''. $this->registry->getClass('class_localization')->formatNumber( $money );
				if(!empty($item_won)) $award .= (count($item_won) > 1 ? ', ' : ' '.$this->lang->words['and'] . ' ');
			}
			
			if(!empty($item_won))
			{
				if(count($item_won) > 1)
				{
					$last = array_pop($item_won);
					#TODO Change the hard-coded string to a translatable way
					$award .= implode(', ', $item_won);
					$award .= ' '.$this->lang->words['and'].' ' . $last;
				}
				else
				{
					$award .= $item_won[0];
				}
			}
			$returnMe['error'] .= $award;
			$returnMe['error'] .= $this->lang->words['!'];
		}
		else
		{
			$award = 'Ø';
			$returnMe['error'] .= $this->lang->words['you_did_not_win'];
		}
		
		#finish up
		$this->registry->ecoclass->finishUpItemUse($theItem,$myPortItem,$award);
		
		$returnMe['error'] .= '</div><div style="width: 100%; text-align: center;"><table class="ipb_table border" style="width: auto; text-align: center; margin: 5px auto;">';
		$scratchOFfText = $this->lang->words['scratch_off'];
		$returnMe['error'] .= '<tr class="header"><th colspan="'.$num_cols.'">'.$scratchOFfText.'</th></tr>';
		foreach($game as $row)
		{
			$returnMe['error'] .= '<tr class="row1">';
			foreach($row as $k=>$col)
			{
				$returnMe['error'] .= '<td style="width: 100px; height: 50px" class="'. (($k%2)==1 ? 'altrow' : '') .'">';
				if(in_array($col, $wins)) $returnMe['error'] .= "<strong>";
				if($col->type == 'money')
				{
					$returnMe['error'] .= $this->settings['eco_general_cursymb'] . ' ' . $col->value;
				}
				else
				{
					$returnMe['error'] .= $itemCache[ $col->value ]['si_title'];
				}
				if(in_array($col, $wins)) $returnMe['error'] .= "</strong>";
				$returnMe['error'] .= '</td>';
			}
			$returnMe['error'] .= '</tr>';
		}
		$returnMe['error'] .= '</table></div>';
		
		return $returnMe;
	}
	
	/**
	 * Use Item EXECUTION (item)
	 */
	public function doUseItem($type, $selectedItemID)
	{
		if($type == 'money')
		{
			#add points
			$this->registry->mysql_ibEconomy->updateMemberPts($this->memberData['member_id'], $selectedItemID, '+', true);	
		}
		else
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
}