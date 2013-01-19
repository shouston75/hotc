<?php

/**
 * (e32) ibEconomy
 * Shop Item: Color Topic Title
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

		$this->registry->class_localization->loadLanguageFile( array( 'public_post' ), 'forums' );
	}
	
	//*************************//
	//($%^   ADMIN STUFF   ^%$)//
	//*************************//	

	/**
	 * Send the "Stock" Title
	 */
	public function title()
	{
		return "(sos32) Topic Title Colored Must Be Installed First";
	}
	
	/**
	 * Send the "Stock" Description
	 */
	public function description()
	{
		return $this->lang->words['add_color_to_topic_title'];
	}
	
	/**
	 * Need to pick self or others applicable?
	 */
	public function otherOrSelf()
	{
		return TRUE;
	}	

	/**
	 * Send the Extra Settings
	 */
	public function extra_settings()
	{
		$itemSettings = array( 0  => array( 'form_type' => 'formMultiDropdown',
										    'field' 	=> 'si_protected',
										    'words' 	=> $this->lang->words['protected_forums'],
										    'desc' 		=> $this->lang->words['cannot_be_done_in_forums'],
										    'type'      => 'forums'
										 ),
							   1  => array( 'form_type' => 'formMultiDropdown',
										    'field' 	=> 'si_protected_g',
										    'words' 	=> $this->lang->words['protected_groups'],
										    'desc' 		=> $this->lang->words['cannot_be_done_to_groups'],
										    'type'      => 'groups'
										 ),
							   2 => array( 'form_type' => 'formYesNo',
											'field'		=> 'si_extra_settings_1',
											'words'		=> $this->lang->words['allow_user_to_pick_styles'],
											'desc'		=> $this->lang->words['allow_user_to_pick_styles_desc'],
										 ),
							   3 => array( 'form_type' 	=> 'formsimpleinput',
										   'field' 		=> 'si_extra_settings_2',
										   'words' 		=> $this->lang->words['text_color'],
										   'desc' 		=> ''
										 ),
							   4 => array( 'form_type' => 'formsimpleinput',
											'field'		=> 'si_extra_settings_3',
											'words'		=> $this->lang->words['background_color'],
											'desc'		=> ''
										 ),
							   5 => array( 'form_type' 	=> 'formYesNo',
										   'field' 		=> 'si_extra_settings_4',
										   'words' 		=> $this->lang->words['bold_text'],
										   'desc' 		=> ''
										 ),
							   6 => array( 'form_type' 	=> 'formYesNo',
										   'field' 		=> 'si_extra_settings_5',
										   'words' 		=> $this->lang->words['italic_text'],
										   'desc' 		=> ''
										 ),										 
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
		#(sos32) Topic Title Colored v1.0.0 not installed?
		if ( !($this->settings['sos32_ttc_profiletab'] == '0' || $this->settings['sos32_ttc_profiletab'] == '1') )
		{
			$returnMe['error'] = $this->lang->words['topic_title_colored_not_installed_compain'];
		}
		
		$itemHtml = array();
		
		$itemHtml[] = array('text' => $this->lang->words['enter_topic_wish_to_add_color'], 'inputs' => "<input type='text' size='30' name='topic_id' id='topic_id' />");
			
		if ($theItem['si_extra_settings_1'])
		{			
			$itemHtml[] = array('text' => "", 'inputs' => "<script type='text/javascript' src='{$this->settings['public_dir']}/js/3rd_party/colorpicker/jscolor.js'></script>
				<fieldset>
					<h3 class='bar'>{$this->lang->words['ttc_title']} </h3>");	
					
			$itemHtml[] = array('text' => "&nbsp;", 'inputs' => "<ul>
						<li class='field'>
							<label for='ttc_fontcolor'>{$this->lang->words['ttc_fontcolor']}</label>
							<input id='ttc_fontcolor' class='color {pickerPosition:'top',hash:true,required:false}' type='text' size='50' maxlength='10' name='ttc_fontcolor' value='{$formData['ttc_fontcolor']}' tabindex='0' />
							<span class='desc'>{$this->lang->words['post_optional']}</span>
						</li>
						<li class='field'>
							<label for='ttc_backgroundcolor'>{$this->lang->words['ttc_backgroundcolor']}</label>
							<input id='topic_bg' class='color {pickerPosition:'top',hash:true,required:false}' type='text' size='50' maxlength='10' name='ttc_backgroundcolor' value='{$formData['ttc_backgroundcolor']}' tabindex='0' />
							<span class='desc'>{$this->lang->words['post_optional']}</span>
						</li>
					</ul>");
			$itemHtml[] = array('text' => "", 'inputs' => "<script type='text/javascript' src='{$this->settings['public_dir']}/js/3rd_party/colorpicker/jscolor.js'></script>
				<fieldset>
					<h3 class='bar'>{$this->lang->words['ttc_styles']} </h3>");
					
			$itemHtml[] = array('text' => "&nbsp;", 'inputs' => "<ul>		

						<li class='field checkbox nodesc'>
							<input type='checkbox' name='ttc_bold' id='ttc_bold' value='1' class='input_check' />
							<label for='ttc_bold'>{$this->lang->words['ttc_bold']}</label>
						</li>
						<li class='field checkbox'>
							<input type='checkbox' name='ttc_italic' id='ttc_italic' value='1' class='input_check' />
							<label for='ttc_italic'>{$this->lang->words['ttc_italic']}</label>
						</li>
					</ul>
				</fieldset>");
		}
		
		#need member name input?
		if ( $theItem['si_other_users'] )
		{
			if ( $theItem['si_allow_user_pm'] )
			{
				$itemHtml[] = array('text' => $this->lang->words['input_message']."<br /><span class='desc'>{$this->lang->words['optional_message']}</span>", 'inputs' => "<textarea size='50' cols='40' rows='5' wrap='soft' name='message' id='message' class='multitext'></textarea>");
			}				
		}
		
		return $itemHtml;
	}
	
	/**
	 * Use Item
	 */
	public function useItem($theItem,$myPortItem)
	{
		#init
		$returnMe 	= '';
		$tid   		= intval($this->request['topic_id']);
		$usrMessage	= trim(IPSText::getTextClass( 'bbcode' )->stripBadWords( $this->request['message'] ));
		$msg2send	= '';		
			
		#(sos32) Topic Title Colored v1.0.0 not installed?
		if ( !($this->settings['sos32_ttc_profiletab'] == '0' || $this->settings['sos32_ttc_profiletab'] == '1') )
		{
			print($this->lang->words['topic_title_colored_not_installed_compain']);
			exit;
		}

		#no ID?
		if ( !$tid )
		{
			$returnMe['error'] = $this->lang->words['no_topic_found'];
		}
		
		#make sure we have this item already
		$topic	= $this->registry->mysql_ibEconomy->grabTopicByID($tid);
		
		#no topic?
		if ( !$topic['tid'] )
		{
			$returnMe['error'] = $this->lang->words['no_topic_found'];
		}

		#your own topic when not allowed?
		if ( !$returnMe['error'] && $theItem['si_other_users'] && $topic['starter_id'] == $this->memberData['member_id'] )
		{
			$returnMe['error'] = $this->lang->words['your_topic_not_allowed'];
		}		
		
		#not your own topic
		if ( !$returnMe['error'] && !$theItem['si_other_users'] && $topic['starter_id'] != $this->memberData['member_id'] )
		{
			$returnMe['error'] = $this->lang->words['not_your_topic'];
		}

		#topic in protected forum?
		if ( !$returnMe['error'] && in_array( $topic['forum_id'], explode(',', $theItem['si_protected']) ) )
		{
			$returnMe['error'] = $this->lang->words['topic_in_protected_forum'];
		}

		#topic created by member in protected?
		if ( !$returnMe['error'] && $theItem['si_other_users'] && in_array( $topic['member_group_id'], explode(',', $theItem['si_protected_g']) ) )
		{
			$returnMe['error'] = $this->lang->words['topic_made_by_mem_in_protected_group'];
		}	
		
		#topic is someone elses, send message?
		if ( $theItem['si_other_users'] )
		{
			#send message about item use?
			if ( $theItem['si_allow_user_pm'] && $usrMessage != "" )
			{
				$msg2send = $usrMessage;
				$sender = $this->memberData['member_id'];
			}
			else if ( trim($theItem['si_default_pm']) != "" )
			{
				$msg2send = trim($theItem['si_default_pm']);
			}
		}
		
		#no errors, use it!
		if ( ! $returnMe['error'] )
		{
			#use it
			$this->doUseItem($topic, $theItem);
						
			#send PM
			if ( $msg2send != '' )
			{			
				$title = sprintf( $this->lang->words['shop_item_used_on_you'], $theItem['si_title'] );
				
				$this->registry->ecoclass->sendPM($topic['starter_id'] , '', 0, '', 'generic', $msg2send, $title, $sender, $topic['title'] );			
			}
			
			#finish up
			$this->registry->ecoclass->finishUpItemUse($theItem,$myPortItem,'#'.$tid);
			
			#add to redirect text
			$returnMe['redirect_text'] = $this->lang->words['topic_has_been_colored'];
		}
		
		return $returnMe;
	}

	/**
	 * Use Item EXECUTION
	 */
	public function doUseItem($topic, $theItem)
	{	
		$styles = array();
		if ($theItem['si_extra_settings_1'])
		{
			foreach( array( 'fontcolor', 'backgroundcolor', 'italic', 'bold' ) as $style )
			{
				$currentInput = $this->myCleanValue( $this->request['ttc_' . $style ]);
				
				$currentInput =( $style == 'fontcolor' || $style == 'backgroundcolor') ? "#".$currentInput : $currentInput;
				$styles['ttc_' . $style ] 	= isset( $this->request['ttc_' . $style ] ) ? $currentInput : '';
			}		
		}
		else
		{
			$styles['ttc_fontcolor'] 		= $theItem['si_extra_settings_2'];
			$styles['ttc_backgroundcolor'] 	= $theItem['si_extra_settings_3'];
			$styles['ttc_bold'] 			= $theItem['si_extra_settings_4'];
			$styles['ttc_italic'] 			= $theItem['si_extra_settings_5'];
		}

		if ( count( $styles ) )
		{
			$this->DB->update( 'topics', $styles, "tid=".$topic['tid'] );
			//$topic = array_merge( $topic, $styles );
		}
	}

	private function myCleanValue( $value )
	{
		$value = IPSText::parseCleanValue( $value );
		$value = trim( IPSText::getTextClass( 'bbcode' )->stripBadWords( IPSText::stripAttachTag( $value ) ) );
		$value = preg_replace( "/&(#{0,}([a-zA-Z0-9]+?)?)?$/", '', $value );
		
		return $value;
	}

/*
		if ( count( $styles ) )
		{
			$this->DB->update( 'topics', $styles, "tid=".$topic['tid'] );
			$topic = array_merge( $topic, $styles );
			
			require_once( IPSLib::getAppDir( 'forums' ) . '/sources/classes/post/classPost.php' );
			$classToLoad = IPSLib::loadLibrary( IPSLib::getAppDir( 'forums' ) . '/sources/classes/post/classPostForms.php', 'classPostForms', 'forums' );
			$this->_postClass = new $classToLoad( $this->registry );

			$this->_postClass->setAuthor( $topic['starter_id'] );
			
			$this->updateForumAndStats( $topic, 'edit' );
		}
	}

	protected function updateForumAndStats( $topic, $type='new')
	{
		$this->registry->cache()->updateCacheWithoutSaving( 'topicData', $topic );
			
		$this->_postClass->_postClassupdateForumAndStats( $topic, $type );
	}
*/	
}