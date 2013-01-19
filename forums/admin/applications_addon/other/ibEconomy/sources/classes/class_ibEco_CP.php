<?php

/**
 * (e32) ibEconomy
 * ACP Class
 * For All Admin Stuff
 */

if ( ! defined( 'IN_IPB' ) )
{
	print "<h1>Incorrect access</h1>You cannot access this file directly. If you have recently upgraded, make sure you upgraded all the relevant files.";
	exit();
}

class class_ibEco_CP
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
	* Build Plugin Group Settings Save array
	*/
	public function buildPluginGroupSettingsSaver($pluginsInstalled=false)
	{
		$moreGroupSettings = array();
		
		$pluginsInstalled = ($pluginsInstalled) ? $pluginsInstalled : $this->registry->ecoclass->plugins;

		if (is_array($pluginsInstalled) && count($pluginsInstalled))
		{
			foreach($pluginsInstalled AS $tabName => $tabArray)
			{
				foreach($pluginsInstalled[ $tabName ] AS $pluginName => $pluginArray)
				{				
					if ($this->settings[ $pluginArray['on_off_setting'] ] === '0' || $this->settings[ $pluginArray['on_off_setting'] ] === '1')
					{
						$pluginGroupSettings = $pluginArray['group_settings'];
						if (is_array($pluginGroupSettings) && count($pluginGroupSettings))
						{
							foreach ($pluginGroupSettings AS $grpSetting)
							{
								$moreGroupSettings[ $grpSetting['field'] ] = ipsRegistry::$request[ $grpSetting['field'] ];
							}
						}
					}
				}
			}
		}
		
		return $moreGroupSettings;
	}
	
	/**
	* Build Plugin Group Settings HTML
	*/
	public function buildPluginGroupSettingsHTML($group)
	{
		#any plugins with group settings we need to add?
		$pluginsInstalled = $this->registry->ecoclass->plugins;
		$pluginGroupSettingsHTML = "";
		
		if (is_array($pluginsInstalled) && count($pluginsInstalled))
		{
			foreach($pluginsInstalled AS $tabName => $tabArray)
			{
				foreach($pluginsInstalled[ $tabName ] AS $pluginName => $pluginArray)
				{
					if ($this->settings[ $pluginArray['on_off_setting'] ] === '0' || $this->settings[ $pluginArray['on_off_setting'] ] === '1')
					{				
						$pluginGroupSettings = $pluginArray['group_settings'];

						if (is_array($pluginGroupSettings) && count($pluginGroupSettings))
						{
							$pluginGroupSettingsHTML .= "
							<tr>
								<th colspan='2'>{$pluginArray['name']}</th>
							</tr>";

							foreach ($pluginGroupSettings AS $grpSetting)
							{
								$formBit = $this->registry->output->$grpSetting['form_type']( $grpSetting['field'], $group[ $grpSetting['field'] ] );
								$pluginGroupSettingsHTML .= "
								<tr>
									<td>
										<label>{$grpSetting['words']}</label><br />{$grpSetting['desc']}
									</td>
									<td>
										{$formBit}
									</td>
								</tr>";
							}
						}
					}
				}
			}
		}
		
		return $pluginGroupSettingsHTML;
	}
	
	/**
	 * Create Buttons
	 */
	public function createButtons( $buttonTypes )
	{
		$buttons = array();
		
		$buttonMap = array( 'cache' => array( 'button_words' => $this->lang->words['recache_system'],
											  'button_image' => 'database_refresh.png',
											  'button_module' => 'frontpage', 
											  'button_section' => 'frontpage', 
											  'button_do' => 'recache_all' ),
                            'add_bank' => array( 'button_words' => $this->lang->words['add_bank'], 
												 'button_image' => 'add.png', 
												 'button_module' => 'investing',
												 'button_section' => 'investing',
												 'button_do' => 'bank' ),
                            'add_stock' => array( 'button_words' => $this->lang->words['add_stock'], 
												 'button_image' => 'add.png', 
												 'button_module' => 'investing',
												 'button_section' => 'investing',
												 'button_do' => 'stock' ),	
                            'add_cc' => array( 'button_words' => $this->lang->words['add_cc'], 
												 'button_image' => 'add.png', 
												 'button_module' => 'investing',
												 'button_section' => 'investing',
												 'button_do' => 'cc' ),	
                            'add_lt' => array( 'button_words' => $this->lang->words['add_lt'], 
												 'button_image' => 'add.png', 
												 'button_module' => 'investing',
												 'button_section' => 'investing',
												 'button_do' => 'long_term' ),	
                            'tools' => array( 'button_words' => $this->lang->words['tools'], 
												 'button_image' => 'wrench_orange.png', 
												 'button_module' => 'frontpage',
												 'button_section' => 'frontpage',
												 'button_do' => 'frontpage' ),
                            'add_sc' => array( 'button_words' => $this->lang->words['add_sc'], 
												 'button_image' => 'add.png', 
												 'button_module' => 'shop',
												 'button_section' => 'shop',
												 'button_do' => 'cat' ),
                            'send_item' => array( 'button_words' => $this->lang->words['send_item'], 
												 'button_image' => 'user_go.png', 
												 'button_module' => 'members',
												 'button_section' => 'members',
												 'button_do' => 'send_items' ),
                            'reset_pts' => array( 'button_words' => $this->lang->words['recalculate']." ".$this->settings['eco_general_currency'], 
												 'button_image' => 'table_refresh.png', 
												 'button_module' => 'forum_points',
												 'button_section' => 'forum_points',
												 'button_do' => 'recalculate_all_points_form' ),
                            'frm_vals' 	=> array( 'button_words' => sprintf($this->lang->words['forum_point_values'], $this->settings['eco_general_currency'] ), 
												 'button_image' => 'wrench_orange.png', 
												 'button_module' => 'forum_points',
												 'button_section' => 'forum_points',
												 'button_do' => 'show_forum_points' ),												 
						  );
						  
		foreach ( $buttonTypes AS $button )
		{
			$buttons[] = $buttonMap[ $button ];
		}
						  
		return $buttons;
	}
	
	/**
	 * Get Groups Array
	 */	
	public function getPtFields()
	{	
		#init
		$ptFields 		= array();
		
		#do array
		$ptFields[] = array( 'points', 'iPoints Points' );
		$ptFields[] = array( 'points', 'IBstore Points' );
		$ptFields[] = array( 'points', 'ibMarket Points' );
		$ptFields[] = array( 'points', 'Perfect Points' );
		$ptFields[] = array( 'posts', 'Posts' );
		$ptFields[] = array( 'ibbookie_points', 'ibBookie Points' );
		$ptFields[] = array( 'money', 'IBProBattle Money' );
		$ptFields[] = array( 'utr_points', 'Ultimate Referrals' );
		
		#return
		return $ptFields;
	}	
	
	/**
	 * Get Forums Array
	 */	
	public function getForums($protect = true, $password = false)
	{	
		#init
		$forums 		= array();
		$forums['none'] = ($protect) ? array('none', $this->lang->words['no_protect'] ) : '';

		#grab forums and arrayitize em
		$this->DB->build( array( 'select' => '*', 'from' => 'forums' ) );
		$this->DB->execute();

		while( $row = $this->DB->fetch() )
		{
			if ( (!$password || $row['password'])  && $row['parent_id'] != -1)
			$forums[] = array( $row['id'], $row['name'] );
		}
		
		#return
		return $forums;		
	}
	
	/**
	 * Get Bank Types
	 */	
	public function getBankTypes()
	{	
		#init
		$bankTypes 			= array();

		$bankTypes[] = array('', $this->lang->words['none'] );
		$bankTypes[] = array('checking', $this->lang->words['checking'] );
		$bankTypes[] = array('savings', $this->lang->words['savings'] );
		
		#return
		return $bankTypes;		
	}	
	
	/**
	 * Get All ibEconomy Items
	 */	
	public function getAllItems($page='', $justIDs = FALSE)
	{	
		#init
		$items 		= array();
		$itemTypes 	= array();
		
		#only shop items?
		if ( is_array( $page ) and count( $page ) )
		{
			foreach ( $page as $p )
			{
				$itemTypes = array($p);
			}
		}
		else
		{
			$items['none'] 	= array('none', '' );
			$itemTypes 		= array('bank','stock','cc','lt','shopitem');
		}

		#frontpage mass delete
		if ( $page == 'fp' )
		{
			$items[] = array( 'points', $this->lang->words['global'].' '.$this->settings['eco_general_currency'] );
			$items[] = array( 'welfare', $this->lang->words['global'].' '.$this->lang->words['welfare'] );
		}
		
		foreach ( $itemTypes AS $type )
		{	
			#init
			$abr 		= $this->registry->ecoclass->getTypeAbr($type);
			$typeCache 	= $this->caches['ibEco_'.$type.'s' ];
			
			if (  is_array( $typeCache ) and count( $typeCache ) )
			{
				#subtitle
				if ( !$justIDs )
				{
					$items[] = array( $type, $this->lang->words[ $type.'s' ] );
				}
				
				#item
				foreach ( $typeCache AS $row )
				{
					$items[] = (!$justIDs) ? array( $type.'_'.$row[ $abr.'_id'], '&nbsp;&nbsp;-'.$row[ $abr.'_title'] ) : array( $row[ $abr.'_id'], $row[ $abr.'_title'] );
				}
			}
		}

		#return
		return $items;		
	}		
	
	/**
	 * Get image types and create a dropdown
	 */	
	public function getImageTypes()
	{	
		#init
		$image_types = array('gif','jpg','jpeg','png');
		$items		 = array();
		
		foreach ( $image_types AS $type )
		{	
			$items[] =  array( $type, $type );
		}	
		
		#return
		return $items;	
	}
	
	/**
	 * Get awards and create dropdown
	 */	
	public function getAwards()
	{	
		#init
		$items		 = array();
			
		#using inv awards
		if ($this->settings['awds_system_status'] === '0' || $this->settings['awds_system_status'] === '1')
		{
			$items = $this->getInvAwards();
		}				
		#using AMS still?
		else
		{
			require_once( IPSLib::getAppDir( 'awards' ) . '/sources/classes/awards/class_awards.php' );
			$class_awards = new class_awards( ipsRegistry::instance() );
			
			$class_awards->awardsInit();

			if(is_array($class_awards->awards_cat_by_id)) 
			{
				foreach ( $class_awards->awards_cat_by_id as $aCat )
				{
					foreach ( $aCat['awards'] as $anAward	)
					{
						$items[] =  array( $aCat['id'].'_'.$anAward['awards_id'], $anAward['awards_name'] );
					}
				}
			}
		}
		
		#return
		return $items;	
	}
	
	/**
	 * Get awards from the (inv) awards system
	 */	
	public function getInvAwards()
	{	
		$awards = array();
		
		$this->DB->build(array(
							   'select' => '*',
							   'from'   => 'inv_awards_cats',
							   'order'  => 'placement ASC',
							   
		));
		
		$getCats = $this->DB->execute();
		
		while($cat = $this->DB->fetch($getCats))
		{
			# Get awards to show
			$this->DB->build(array(
									'select'   => '*',
									'from'     => 'inv_awards',
									'where'    => 'parent = ' . $cat['cat_id'],
									'order'    => 'placement ASC',
			));
			
			$this->DB->execute();
			
			$count = $this->DB->getTotalRows();
			
			# Verify we have awards
			if($count)
			{
				while($a = $this->DB->fetch())
				{
					$awards[] =  array( $cat['cat_id'].'_'.$a['id'], $a['name'] );
				}
			}
		}
		
		return $awards;
	}
	
	/**
	 * Settings for ibECO!
	 * Added 3rd paramater for ibEco 1.6 so plugins can use this function
	 */	
	public function doSettings( $module, $returnFormCode, $pluginModuleSettingsKey=false)
	{	
		#no eco setting groups passed?
		if( !$pluginModuleSettingsKey && ! in_array ( $module, array('shop_settings','welfare_settings','loan_settings','general_settings','banking_settings','display_settings','lottery_settings' ) ) )
		{
			$this->registry->output->global_message = $this->lang->words['error_no_key'];
			return;
		}
		
		$sizzettings = $this->registry->getClass('class_settings');
		
		#do setting keyword map
		$settingKeywords = array( 'shop_settings' 		=> 'eco_shop',
								  'welfare_settings'	=> 'eco_welfare',
								  'loan_settings'		=> 'eco_loan',
								  'lottery_settings'	=> 'eco_lotto',
								  'general_settings'	=> 'eco_general',
								  'banking_settings'	=> 'eco_banking',
								  'display_settings'	=> 'eco_display'
								);
		
		#do dem shortcuts
		$sizzettings->makeRegistryShortcuts( $this->registry );
		
		#settings templates
		$sizzettings->html					= $this->registry->output->loadTemplate( 'cp_skin_settings', 'core' );	

		#settings form codes
		$sizzettings->form_code				= $sizzettings->html->form_code    = 'module=settings&amp;section=settings';
		$sizzettings->form_code_js			= $sizzettings->html->form_code_js = 'module=settings&amp;section=settings';
		
		#do it
		$this->request['conf_title_keyword']	= (!$pluginModuleSettingsKey) ? $settingKeywords[ $module ] : $pluginModuleSettingsKey;
		
		$this->registry->output->setMessage($this->request['saved'] ? $this->lang->words['s_updated'] : "", 1);
		
		$sizzettings->return_after_save 		= $this->settings['base_url'] . $returnFormCode . '&saved=1&do=' . $module;
		$sizzettings->_viewSettings();				
	}

	/**
	 * Make the button row!
	 */		
	public function makeButtonRow( $buttonArray )
	{
		#load template
		$this->html = $this->registry->output->loadTemplate( 'cp_skin_ibEconomy');	
		
		#make the buttons
		$buttons = $this->createButtons( $buttonArray );
		
		foreach ( $buttons AS $button )
		{
			$theButtons .= $this->html->button( $button );
		}
		
		#make buttons html
		$buttonRows = $this->html->buttonRow( $theButtons );
		
		#return em
		return $buttonRows;
	}

	/**
	 * #Grab Default Shop Item Templates from Server
	 */		
	public function getShopItemFiles()
	{
		#init
		$items = array();

		#shop item directory
		$dir = IPSLib::getAppDir( 'ibEconomy' ) . '/sources/shop_items/';

		if ($dh = opendir($dir)) 
		{
			while (($file = readdir($dh)) !== false)
			{
				#skip non relevant files
				if( in_array( $file, array(".","..","") ) || strpos($file, 'html') )
				{
					continue;
				}
				
				#format filename 
				$fileName = substr($file, 0,strrpos($file,'.'));
				$fileName = str_replace("_", " ",$fileName); 
				$fileName = ucwords($fileName);

				#fill item array to send back
				$items[] = array( $file, $fileName );
			}

			#sort em!
			sort ( $items );
		}
		
		#return items
		return $items; 
	}
	
	/**
	 * Upload a Custom image item for a shop item/cat or investment
	 * Or delete it
	 */		
	public function imageForInitialItem($itemType, $itemID)
	{	
		#upload the image
		$uploadedImage = $this->registry->class_ibEco_CP->uploadImage( $itemID, $itemType );

		if ($uploadedImage['status'] != 'ok')
		{
			return $uploadedImage['status'];
		}
		
		#create url map
		$itemMap 	= $this->registry->class_ibEco_CP->itemMap( $itemType );
		
		#create data array
		$imageName 	= ( $uploadedImage['t_final_image_name'] ) ? $uploadedImage['t_final_image_name'] : $uploadedImage['final_image_name'];
		$datas  	= array($this->registry->ecoclass->getTypeAbr($itemType).'_image' => $imageName);
		
		#insert or update...								  
		$this->DB->update( $itemMap[$itemType]['db'], $datas, $itemMap[$itemType]['db_fld'].'='.$itemID );
	}
	
	/**
	 * Upload shop item/cat image
	 */
	public function uploadImage( $item_id = 0, $item_type )
	{
		#init
		$return		      = array( 'error'            => '',
								   'status'           => '',
								   'final_location'   => '',
								   'final_width'      => '',
								   'final_height'     => '',
								   't_final_location' => '',
								   't_final_width'    => '',
								   't_final_height'   => ''  );
		$delete_image     = $this->request['delete_image'];
		$item_id 		  = intval($item_id);
		$item_type        = trim($item_type);
		$real_name        = '';
		$upload_dir       = '';
		$final_location   = '';
		$final_width      = '';
		$final_height     = '';
		$t_final_location = '';
		$t_final_width    = '';
		$t_final_height   = '';
		$t_real_name      = '';
		$t_height		  = 50;
		$t_width          = 50;
		
		if( !$item_id )
		{
			return array( 'status' => 'no_item_id_specified' );
		}
				
		list($p_max, $p_width, $p_height) = explode( ":", $this->memberData[ 'g_photo_max_vars' ] );
		
		$this->settings[ 'disable_ipbsize'] =  0 ;
		
		#Sort out upload dir
		/* Fix for bug 5075 */
		$this->settings['upload_dir'] = str_replace( '&#46;', '.', $this->settings['upload_dir'] );		

		$upload_path  = $this->settings['upload_dir'];
		
		#Preserve original path
		$_upload_path = $this->settings['upload_dir'];
		
		#Already a dir?
		if ( ! file_exists( $upload_path . "/ibEconomy_images" ) )
		{
			if ( @mkdir( $upload_path . "/ibEconomy_images", 0777 ) )
			{
				@file_put_contents( $upload_path . '/ibEconomy_images/index.html', '' );
				@chmod( $upload_path . "/ibEconomy_images", 0777 );
				
				# Set path and dir correct
				$upload_path .= "/ibEconomy_images";
				$upload_dir   = "ibEconomy_images/";
			}
			else
			{
				# Set path and dir correct
				$upload_dir   = "";
			}
		}
		else
		{
			# Set path and dir correct
			$upload_path .= "/ibEconomy_images";
			$upload_dir   = "ibEconomy_images/";
		}
		
		#Deleting the photo?
		if ( $delete_image )
		{
			//delete em...
			$this->removeUploadedImages( $item_id, $item_type, $upload_path );
			
			$return['status'] = 'deleted';
			return $return;
		}
		
		#Lets check for an uploaded photo..
		if ( $_FILES['upload_photo']['name'] != "" and ($_FILES['upload_photo']['name'] != "none" ) )
		{
			#delete previous
			$this->removeUploadedImages( $item_id, $item_type, $upload_path );
			
			$real_name  = $item_type.'_img-'.$item_id;
			$_real_name = $item_type.'_img-'.$item_id;
			
			#load the lib
			require_once( IPS_KERNEL_PATH.'classUpload.php' );
			$upload    = new classUpload();

			#setup vars
			$upload->out_file_name     = $_real_name;
			$upload->out_file_dir      = $upload_path;
			$upload->max_file_size     = $p_max * 1024;
			$upload->upload_form_field = 'upload_photo';
			
			#Populate allowed extensions
			$upload->allowed_file_ext  = array( 'gif', 'png', 'jpg', 'jpeg' );
			
			#Upload...
			$upload->process();
			
			#Error?
			if ( $upload->error_no )
			{
				switch( $upload->error_no )
				{
					case 1:
						// No upload
						$return['status'] = 'fail';
						$return['error']  = 'upload_failed';
					break;
					case 2:
						// Invalid file ext
						$return['status'] = 'fail';
						$return['error']  = 'invalid_file_extension';
					break;
					case 3:
						// Too big...
						$return['status'] = 'fail';
						$return['error']  = 'upload_to_big';
					break;
					case 4:
						// Cannot move uploaded file
						$return['status'] = 'fail';
						$return['error']  = 'upload_failed';
					break;
					case 5:
						// Possible XSS attack (image isn't an image)
						$return['status'] = 'fail';
						$return['error']  = 'upload_failed';
					break;
				}
				
				return $return;
			}
						
			#Still here?
			$real_name   = $upload->parsed_file_name;
			$t_real_name = $upload->parsed_file_name;

			#Check image size...
			if ( ! $this->settings['disable_ipbsize'] )
			{
				$imageDimensions = getimagesize( $upload_path . '/' . $real_name );
				
				#Main image
				if( $imageDimensions[0] > $p_width OR $imageDimensions[1] > $p_height )
				{
					require_once( IPS_KERNEL_PATH."classImage.php" ); 
					require_once( IPS_KERNEL_PATH."classImageGd.php" );
					$image = new classImageGd();
					
					$image->init( array( 
										'image_path'	  => $upload_path, 
										'image_file'	  => $real_name, 
								)	);
                    
					$return = $image->resizeImage( $p_width, $p_height );
					$image->writeImage( $upload_path . '/' . $_real_name . '.' . $upload->file_extension );
                    
					$t_real_name = $return['thumb_location'] ? $return['thumb_location'] : $real_name;
                    
					$im['img_width']  = $return['newWidth'] ? $return['newWidth'] : $image->cur_dimensions['width'];
					$im['img_height'] = $return['newHeight'] ? $return['newHeight'] : $image->cur_dimensions['height'];

				}
				else
				{
					$im['img_width']  = $imageDimensions[0];
					$im['img_height'] = $imageDimensions[1];
						
				}

				#MINI image
				if( $imageDimensions[0] > $t_width OR $imageDimensions[1] > $t_height )
				{
					require_once( IPS_KERNEL_PATH . "classImage.php" ); 
					require_once( IPS_KERNEL_PATH . "classImageGd.php" );
                    
					$image = new classImageGd();
					
					$image->init( array( 
										'image_path'	  => $upload_path, 
										'image_file'	  => $t_real_name, 
								)	);
                    
					$return = $image->resizeImage( $t_width, $t_height );
					$t_real_name = $_real_name.'-thumb.' . $upload->file_extension;
					$image->writeImage( $upload_path . '/' . $_real_name.'-thumb.' . $upload->file_extension );
                    
					$t_im['img_width']	  = $return['newWidth'];
					$t_im['img_height']	  = $return['newHeight'];
					$t_im['img_location'] = count($return) ? $_real_name.'-thumb.' . $upload->file_extension : $real_name;
				} 
				else 
				{
					$_data = IPSLib::scaleImage( array( 
														'max_height' => $t_height,
														'max_width'  => $t_width,
														'cur_width'  => $im['img_width'],
														'cur_height' => $im['img_height'] 
												)	);
					
					$t_im['img_width']		= $_data['img_width'];
					$t_im['img_height']		= $_data['img_height'];
					$t_im['img_location']	= $real_name;
				}
			}
			else
			{
				#Main photo
				$w = intval($this->request['man_width'])  ? intval($this->request['man_width'])  : $p_width;
				$h = intval($this->request['man_height']) ? intval($this->request['man_height']) : $p_height;
				$im['img_width']  = $w > $p_width  ? $p_width  : $w;
				$im['img_height'] = $h > $p_height ? $p_height : $h;
				
				#Mini photo
				$_data = IPSLib::scaleImage( array( 'max_height' => $t_height,
															  'max_width'  => $t_width,
															  'cur_width'  => $im['img_width'],
															  'cur_height' => $im['img_height'] ) );
															  
				$t_real_name = $_real_name.'-thumb.' . $upload->file_extension ;
				$t_im['img_width']  	= $_data['img_width'];
				$t_im['img_height']		= $_data['img_height'];
				$t_im['img_location']	= $real_name;
			}
			
			#Check the file size (after compression)
			if ( filesize( $upload_path . "/" . $real_name ) > ( $p_max * 1024 ) )
			{
				@unlink( $upload_path . "/" . $real_name );
				
				// Too big...
				$return['status'] = 'fail';
				$return['error']  = 'upload_to_big';
				return $return;
			}
			
			#Main photo
			$final_location = $upload_dir . $real_name;
			$final_width    = $im['img_width'];
			$final_height   = $im['img_height'];
			
			#Mini photo
			$t_final_location = $upload_dir . $t_im['img_location'];
			$t_final_width    = $t_im['img_width'];
			$t_final_height   = $t_im['img_height'];
		}
		else
		{
			$return['status'] = 'no_image';
			return $return;
		}
		
		#Return...
		$return['final_location']   = $final_location;
		$return['final_width']      = $final_width;
		$return['final_height']     = $final_height;
		$return['final_image_name'] = $real_name;
		
		$return['t_final_location'] 	= $t_final_location;
		$return['t_final_width']    	= $t_final_width;
		$return['t_final_height']   	= $t_final_height;
		$return['t_final_image_name']	= $t_real_name;
		
		$return['status'] = 'ok';
		return $return;
	}

	/**
	 * Remove member uploaded photos
	 */
	private function removeUploadedImages( $id, $type, $upload_path='' )
	{
		#INIT
		$upload_path = $upload_path ? $upload_path : $this->settings['upload_dir'];

		if ( !strpos('ibEconomy_images', $upload_path) )
		{
			return false;
		}

		#Go...
		foreach( array( 'swf', 'jpg', 'jpeg', 'gif', 'png' ) as $ext )
		{
			if ( @file_exists( $upload_path."/".$type."_img-".$id.".".$ext ) )
			{
				@unlink( $upload_path."/".$type."_img-".$id.".".$ext );
			}
			
			if ( @file_exists( $upload_path."/".$type."_img-".$id."-thumb.".$ext ) )
			{
				@unlink( $upload_path."/".$type."_img-".$id."-thumb.".$ext );
			}
		}
	}
	
	/**
	 * Create a map of url/db/etc values for different items
	 */
	public function itemMap( $item_type )
	{
		$map 	= array('bank' 			=> array('module' => 'investing', 	'single' => 'bank', 		'list' => 'institutions', 	'type_id' => 'bank_id', 'db' => 'eco_banks', 		'db_fld' => 'b_id', 	'cache_name' => 'banks'),
						'cc' 			=> array('module' => 'investing',  	'single' => 'cc', 			'list' => 'credit_cards', 	'type_id' => 'cc_id', 	'db' => 'eco_credit_cards', 'db_fld' => 'cc_id', 	'cache_name' => 'ccs'),
						'lt' 			=> array('module' => 'investing',  	'single' => 'long_term', 	'list' => 'long_terms', 	'type_id' => 'lt_id', 	'db' => 'eco_long_terms', 	'db_fld' => 'lt_id', 	'cache_name' => 'lts'),
						'stock' 		=> array('module' => 'investing',  	'single' => 'stock', 		'list' => 'stocks', 		'type_id' => 's_id', 	'db' => 'eco_stocks', 		'db_fld' => 's_id', 	'cache_name' => 'stocks'),
						'shop_item' 	=> array('module' => 'shop', 		'single' => 'item', 		'list' => 'items', 			'type_id' => 'si_id', 	'db' => 'eco_shop_items', 	'db_fld' => 'si_id', 	'cache_name' => 'shopitems'),
						'shop_cat' 		=> array('module' => 'shop',  		'single' => 'cat', 			'list' => 'cats', 			'type_id' => 'sc_id', 	'db' => 'eco_shop_cats', 	'db_fld' => 'sc_id', 	'cache_name' => 'shopcats')
					  );
					 
		return $map;
	}

	/**
	 * Fetch skin list
	 */
	public function html_fetchSetsDropDown( $parent=0, $iteration=0 )
	{
		$cache = ipsRegistry::cache()->getCache('skinsets');

		foreach( $cache as $id => $data )
		{
			/* Root skins? */
			if ( count( $data['_parentTree'] ) AND $iteration == 0 )
			{
				continue;
			}
			else if( $iteration > 0 AND (!count( $data['_parentTree'] ) OR $data['_parentTree'][0] != $parent) )
			{
				continue;
			}

			$skins[] =  array( $data['set_id'], $data['set_name'] );
		}	
		
		#return
		return $skins;			
	}
	
	/**
	 * Recalculates all member's points based on the current forum points and their posts
	 */
	public function recalculateMemPointsPerFrmPts()
	{
		//eco_tpc_pts	eco_rply_pts	eco_get_rply_pts
		$forumPointsPerReply 	= array();
		$forumPointsPerTopic 	= array();
		$forumPointsPerGetReply = array();
		
		$membersAllotedPoints	= array();
		
		#grab forums and arrayitize em
		$this->DB->build( array( 'select' => 'eco*', 'from' => 'forums' ) );
		$this->DB->execute();

		while( $row = $this->DB->fetch() )
		{
			if ($row['eco_rply_pts'] > 0)
			{
				$forumPointsPerReply[ $row['id'] ] = $row['eco_rply_pts'];
			}
			if ($row['eco_tpc_pts'] > 0)
			{
				$forumPointsPerTopic[ $row['id'] ] = $row['eco_tpc_pts'];
			}
			if ($row['eco_get_rply_pts'] > 0)
			{
				$forumPointsPerGetReply[ $row['id'] ] = $row['eco_get_rply_pts'];
			}			
		}

		#grab posts
		$this->DB->buildAndFetch( array(
										'select'	=> 'p.pid,p.author_id, p.topic_id, p.new_topic, t.forum_id, t.starter_id',
										'from'		=> array( 'posts' => 'p' ),
										'where'  	=> 'p.pid = '.$pid ,
										'add_join'	=> array(
															array( 'from'	=> array( 'topics' => 't' ),
																   'where'	=> 'p.topic_id=t.tid'
																 )
															)
								)		);	
										
		$this->DB->execute();

		while( $row = $this->DB->fetch() )
		{
			if ( !$row['new_topic'] )
			{
				$membersAllotedPoints[ $row['author_id'] ] 	+= $forumPointsPerReply[ $row['forum_id'] ];
				
				if ( $row['starter_id'] != $row['author_id'])
				{
					$membersAllotedPoints[ $row['starter_id'] ] += $forumPointsPerGetReply[ $row['forum_id'] ];	
				}
			}
			else
			{
				$membersAllotedPoints[ $row['starter_id'] ] += $forumPointsPerTopic[ $row['forum_id'] ];
			}
		}
	}	
	
}