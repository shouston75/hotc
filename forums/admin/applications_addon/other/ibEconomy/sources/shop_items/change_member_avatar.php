<?php

/**
 * (e32) ibEconomy
 * Shop Item: Change Member PHOTO (avatar not anymore since 3.2, but too much trouble to change item name)
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
    protected $photo;
    protected $maxPhotoSize;
	
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
		
		$this->registry->class_localization->loadLanguageFile( array( 'public_usercp' ), 'core' );
	}
	
	//*************************//
	//($%^   ADMIN STUFF   ^%$)//
	//*************************//	

	/**
	 * Send the "Stock" Title
	 */
	public function title()
	{
		return $this->lang->words['change_member_avatar'];
	}
	
	/**
	 * Send the "Stock" Description
	 */
	public function description()
	{
		return $this->lang->words['change_member_avatar'];
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
		$itemSettings = array( 0 => array( 'form_type' 	=> 'formMultiDropdown',
										    'field' 	=> 'si_protected_g',
										    'words' 	=> $this->lang->words['protected_groups'],
										    'desc' 		=> $this->lang->words['cannot_be_done_to_groups'],
										    'type'      => 'groups'
										 ),
							   1 => array( 'form_type' 	=> 'formSimpleInput',
										    'field' 	=> 'si_extra_settings_1',
										    'words' 	=> $this->lang->words['max_size'],
										    'desc' 		=> $this->lang->words['in_kb'],
										    'type'      => 'groups'
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
            
            #need member name input?
            if ( $theItem['si_other_users'] )
            {
                    $itemHtml[] = array('text' => $this->lang->words['input_member_name'], 'inputs' => "<input type='text' size='30' name='mem_name' id='mem_name1' />");
                    
                    if ( $theItem['si_allow_user_pm'] )
                    {
                            $itemHtml[] = array('text' => $this->lang->words['input_message']."<br /><span class='desc'>{$this->lang->words['optional']}</span>", 'inputs' => "<textarea size='50' cols='40' rows='5' wrap='soft' name='message' id='message' class='multitext'></textarea>");
                    }				
            }
            
            $itemHtml[] = array('text' => 'Select photo', 'inputs' => "<input type='file' name='upload_photo' id='upload_photo' class='input_text' size='16' title='Formats: JPEG, PNG, GIF' />");
            
            return $itemHtml;
	}
	/**
	 * Steal Avatar Form
	 */
	public function getAvatarForm($theItem)	
	{	
            #init		
            #$member = IPSMember::load( !$theItem['si_other_users'] ? $this->memberData['member_id'] : '', 'all' );
            #$member['g_avatar_upload'] = ($theItem['si_other_users']) ? 1 : $member['g_avatar_upload'];		
                    
            /* Load lang file */
            $this->registry->class_localization->loadLanguageFile( array( 'public_profile' ), 'members' );
            
            /* Load library */
            $classToLoad  = IPSLib::loadLibrary( IPS_ROOT_PATH . 'sources/classes/member/photo.php', 'classes_member_photo' );
            $this->photo = new $classToLoad( $this->registry );

            return $this->photo->getEditorHtml( $this->memberData );
	}
	
	/**
	 * Use Item
	 */
	public function useItem($theItem,$myPortItem)
	{
		#init
		$returnMe 	= '';
		$memName  	= trim($this->request['mem_name']);
		$usrMessage	= trim(IPSText::getTextClass( 'bbcode' )->stripBadWords( $this->request['message'] ));
		$msg2send	= '';
		
        #no member?
		if ( $theItem['si_other_users'] )
		{
			#input?
			if ( !$memName )
			{
				$returnMe['error'] = $this->lang->words['no_member_entered'];
			}
			else
			{
				#load item recipient
				$daMember = IPSMember::load( $memName, 'all', 'displayname' );				
			}
		
			#no one found?
			if ( !$returnMe['error'] && !$daMember['member_id'] )
			{	
				$returnMe['error'] = $this->lang->words['no_member_found_by_id'];			
			}

			#your own self, when not allowed?
			if ( !$returnMe['error'] && $daMember['member_id'] == $this->memberData['member_id'] )
			{
				$returnMe['error'] = $this->lang->words['item_cannot_be_done_on_self'];
			}			

			#member in protected group?
			if ( !$returnMe['error'] && in_array( $daMember['member_group_id'], explode(',', $theItem['si_protected_g']) ) )
			{
				$returnMe['error'] = $this->lang->words['member_in_protected_group'];
			}
			
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
		else		
		{
			$daMember = $this->memberData;
		}
                
		#use it
		if ( !$returnMe['error'] )
		{
			$this->maxPhotoSize	= $theItem['si_extra_settings_1'] ? $theItem['si_extra_settings_1'] : $this->memberData['photoMaxKb'];
			$returnMe['error'] 	= $this->doUseItem($daMember, $theItem);
		}
		
		#no errors, use it!
		if ( ! $returnMe['error'] )
		{
			#send PM
			if ( $msg2send != '' )
			{			
			    $title = sprintf( $this->lang->words['shop_item_used_on_you'], $theItem['si_title'] );
				
			    $this->registry->ecoclass->sendPM($daMember['member_id'] , '', 0, '', 'generic', $msg2send, $title, $sender );			
                        }
					
			#finish up
			$this->registry->ecoclass->finishUpItemUse($theItem,$myPortItem,$daMember['members_display_name'] != $this->memberData['members_display_name'] ? $daMember['members_display_name'] : '');
			
			#add to redirect text
			$returnMe['redirect_text'] = $this->lang->words['avatar_has_been_changed'];
		}
		
		return $returnMe;
	}

	/**
	 * Use Item EXECUTION
	 */
	public function doUseItem($member, $theItem)
	{
		/* Load lang file */
		$this->registry->class_localization->loadLanguageFile( array( 'public_profile' ), 'members' );
		
		/* Load library */
		$classToLoad    = IPSLib::loadLibrary( IPS_ROOT_PATH . 'sources/classes/member/photo.php', 'classes_member_photo' );
		$this->photo    = new $classToLoad( $this->registry );
        
		$photoType      = $this->request['photoType']+"SDFSDFSDF";
		$gravatar       = $this->request['gravatar'];
		
		/* Do it */
		try
		{
			#$this->registry->ecoclass->showVars($this->request);
			$photo = $this->save( $member, $photoType, $gravatar );
		}
		catch( Exception $error )
		{
		    $msg = $error->getMessage();
			
			switch ( $msg )
			{
				default:
					return $this->lang->words[ 'pp_' . $msg ];
				break;
				case 'PROFILE_DISABLED':
					return $this->lang->words['member_profile_disabled'];
				break;
			}
		}
	}
        
	/**
	 * Save photo
	 * @param	array	member
	 * @param	string	Phototype
	 */
	public function save( $member, $photoType, $gravatar='' )
	{
		#echo($member['member_id']);
		/* Fetch member data */
		$member = IPSMember::buildDisplayData( IPSMember::load( $member['member_id'], 'all' ) );
		
		$photo       = array();
		$bwOptions   = IPSBWOptions::thaw( $member['fb_bwoptions'], 'facebook' );
		$tcbwOptions = IPSBWOptions::thaw( $member['tc_bwoptions'], 'twitter' );
		
		/* Whadda-we-doing? */
		switch( $photoType )
		{
			default:
			case 'custom':
				$photo = $this->uploadPhoto($member['member_id']);
				$photo['pp_photo_type'] = 'custom';
			break;
		}

		if ( $photo['status'] == 'fail' )
		{
			throw new Exception( $photo['error'] );
		}
			
		$save = array( 'pp_main_photo'		=> $photo['final_location'],
  				   	   'pp_main_width'		=> intval($photo['final_width']),
					   'pp_main_height'		=> intval($photo['final_height']),
					   'pp_thumb_photo'		=> $photo['t_final_location'],
					   'pp_thumb_width'		=> intval($photo['t_final_width']),
					   'pp_thumb_height'	=> intval($photo['t_final_height']),
					   'pp_photo_type'		=> $photo['pp_photo_type'],
					   'pp_gravatar'		=> $photo['pp_gravatar'],
					   'fb_photo'			=> '',
					   'fb_photo_thumb'		=> '',
					   'fb_bwoptions'		=> IPSBWOptions::freeze( $bwOptions, 'facebook' ),
					   'tc_photo'			=> '',
					   'tc_bwoptions'		=> IPSBWOptions::freeze( $tcbwOptions, 'twitter' ) );
			
		IPSMember::save( $member['member_id'], array( 'extendedProfile' => $save  ) );
		
		return $save;
	}
        
	/**
	 * Upload personal photo function
	 * Assumes all security checks have been performed by this point
	 *
	 * @access	public
	 * @param	integer		[Optional] member id instead of current member
	 * @return 	array  		[ error (error message), status (status message [ok/fail] ) ]
	 */
	public function uploadPhoto( $member_id = 0 )
	{
		//-----------------------------------------
		// INIT
		//-----------------------------------------
		
		$return		      = array( 'error'            => '',
								   'status'           => '',
								   'final_location'   => '',
								   'final_width'      => '',
								   'final_height'     => '',
								   't_final_location' => '',
								   't_final_width'    => '',
								   't_final_height'   => ''  );
		$member_id        = $member_id ? intval($member_id) : intval( $this->memberData['member_id'] );
		$memberData		  = IPSMember::load( $member_id );
		$real_name        = '';
		$upload_dir       = '';
		$t_real_name      = '';
		$p_max    		  = $this->maxPhotoSize;//changed as of 2.0.8 $this->memberData['photoMaxKb'];
		
		if ( ! $member_id )
		{
			return array( 'status' => 'cannot_find_member' );
		}
				
		/* Fix up upload directory */
		$paths       = $this->_getProfileUploadPaths();
		$upload_path = $paths['path'];
		$upload_dir  = $paths['dir'];
		
		/* Check for an upload */
		if ( $_FILES['upload_photo']['name'] != "" and ($_FILES['upload_photo']['name'] != "none" ) )
		{
			#changed as of 2.0.8, now admin can set a max size
			if ( ! IPSMember::canUploadPhoto( $memberData ) && intval($this->maxPhotoSize) == 0)
			{
				$return['status'] = 'fail';
				$return['error']  = 'no_photo_upload_permission';
				
				return $return;
			}
			
			/* Remove any current photos */
			$this->removeUploadedPhotos( $member_id, $upload_path );
			
			$real_name = 'photo-'.$member_id;
			
			/* Fetch library */
			require_once( IPS_KERNEL_PATH.'classUpload.php' );/*noLibHook*/
			$upload    = new classUpload();

			/* Bit of set up */
			$upload->out_file_name     = 'photo-'.$member_id;
			$upload->out_file_dir      = $upload_path;
			$upload->max_file_size     = $p_max * 1024;
			$upload->upload_form_field = 'upload_photo';
			
			/* Set up our allowed types */
			$upload->allowed_file_ext  = array( 'gif', 'png', 'jpg', 'jpeg' );
			
			/* Upload */
			$upload->process();
			
			/* Oops, what happened? */
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
						
			/* We got this far.. */
			$real_name   = $upload->parsed_file_name;
			$t_real_name = $upload->parsed_file_name;
			
			/* Now build sized copies */
			$return = $this->buildSizedPhotos( $upload->parsed_file_name, $member_id );
		}
		
		return $return;
	}
        
	/**
	 * Fetch upload path and dir
	 * 
	 */
	protected function _getProfileUploadPaths()
	{
		/* Fix for bug 5075 */
		$this->settings['upload_dir'] = str_replace( '&#46;', '.', $this->settings['upload_dir'] );		

		$upload_path  = $this->settings['upload_dir'];
		
		/* Create a dir if need be */
		if ( ! file_exists( $upload_path . "/profile" ) )
		{
			if ( @mkdir( $upload_path . "/profile", IPS_FOLDER_PERMISSION ) )
			{
				@file_put_contents( $upload_path . '/profile/index.html', '' );
				@chmod( $upload_path . "/profile", IPS_FOLDER_PERMISSION );
				
				# Set path and dir correct
				$upload_path .= "/profile";
				$upload_dir   = "profile/";
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
			$upload_path .= "/profile";
			$upload_dir   = "profile/";
		}
		
		return array( 'path' => $upload_path, 'dir' => $upload_dir );
	}
        
	/**
	 * Remove member uploaded photos
	 *
	 * @access	public
	 * @param	integer		Member ID
	 * @param	string		[Optional] Directory to check
	 * @return 	array  		[ error (error message), status (status message [ok/fail] ) ]
	 */
	public function removeUploadedPhotos( $id, $upload_path='' )
	{
		//-----------------------------------------
		// INIT
		//-----------------------------------------
		
		$upload_path = $upload_path ? $upload_path : $this->settings['upload_dir'];
		
		foreach( array( 'swf', 'jpg', 'jpeg', 'gif', 'png' ) as $ext )
		{
			if ( @is_file( $upload_path."/photo-".$id.".".$ext ) )
			{
				@unlink( $upload_path."/photo-".$id.".".$ext );
			}
			
			if ( @is_file( $upload_path."/photo-thumb-".$id.".".$ext ) )
			{
				@unlink( $upload_path."/photo-thumb-".$id.".".$ext );
			}
		}
	}
        
	/**
	 * Takes a photo and builds it nicely.
	 * @param string $fileLocation
	 * @param int $memberId
	 */
	public function buildSizedPhotos( $fileLocation, $memberId )
	{
		$t_height		  = $this->settings['member_photo_crop'] ? $this->settings['member_photo_crop'] : 100;
		$t_width          = $this->settings['member_photo_crop'] ? $this->settings['member_photo_crop'] : 100;
		$p_max    		  = $this->maxPhotoSize;//changed as of 2.0.8 $this->memberData['photoMaxKb'];
		$p_width  		  = $this->memberData['photoMaxWidth'];
		$p_height 		  = $this->memberData['photoMaxHeight'];
		$ext              = IPSText::getFileExtension( $fileLocation );
		
		if ( ! $memberId )
		{
			return array( 'status' => 'cannot_find_member' );
		}
				
		/* Fix up upload directory */
		$paths       = $this->_getProfileUploadPaths();
		$upload_path = $paths['path'];
		$upload_dir  = $paths['dir'];
		
		/* Get kernel library */
		require_once( IPS_KERNEL_PATH . 'classImage.php' );/*noLibHook*/
				
		/* Fetch image dims */
		$imageDimensions = @getimagesize( $upload_path . '/' . $fileLocation );
		
		/** SQUARE THUMBS **/
		if ( ( $imageDimensions[0] > $t_width OR $imageDimensions[1] > $t_height ) OR ( $ext == 'gif' && $this->settings['member_photo_gif_no_animate'] ) )
		{
			$image = ips_kernel_image::bootstrap( 'gd' );
			
			$image->init( array( 'image_path'	  => $upload_path, 
								 'image_file'	  => $fileLocation ) );
			
			/* If we're uploading a GIF then resize to stop animations */
			if ( $ext == 'gif' && $this->settings['member_photo_gif_no_animate'] )
			{
				$image->force_resize = true;
			}
			
			$return = $image->croppedResize( $t_width, $t_height );
			
			$image->writeImage( $upload_path . '/' . 'photo-thumb-'.$memberId . '.' . $ext );
            
			$t_im['img_width']	  = $return['newWidth'];
			$t_im['img_height']	  = $return['newHeight'];
			$t_im['img_location'] = count($return) ? 'photo-thumb-'.$memberId . '.' . $ext : $fileLocation;
		} 
		else 
		{
			$_data = IPSLib::scaleImage( array(  'max_height' => $t_height,
												 'max_width'  => $t_width,
												 'cur_width'  => $imageDimensions['img_width'],
												 'cur_height' => $imageDimensions['img_height'] ) );
			
			$t_im['img_width']		= $_data['img_width'];
			$t_im['img_height']		= $_data['img_height'];
			$t_im['img_location']	= $fileLocation;
		}
		
		/** MAIN PHOTO **/
		if ( $imageDimensions[0] > $p_width OR $imageDimensions[1] > $p_height )
		{
			$image = ips_kernel_image::bootstrap( 'gd' );
			
			$image->init( array( 'image_path'	  => $upload_path, 
								 'image_file'	  => $fileLocation ) );
            
			$return = $image->resizeImage( $p_width, $p_height );
			$image->writeImage( $upload_path . '/' . 'photo-'.$memberId . '.' . $ext );
            
			$t_real_name = $return['thumb_location'] ? $return['thumb_location'] : $fileLocation;
            
			$im['img_width']  = $return['newWidth']  ? $return['newWidth']   : $image->cur_dimensions['width'];
			$im['img_height'] = $return['newHeight'] ? $return['newHeight'] : $image->cur_dimensions['height'];
		}
		else
		{
			$im['img_width']  = $imageDimensions[0];
			$im['img_height'] = $imageDimensions[1];
		}
	
		/* Main photo */
		$return['final_location'] = $upload_dir . $fileLocation;
		$return['final_width']    = $im['img_width'];
		$return['final_height']   = $im['img_height'];
		
		/* Thumb */
		$return['t_final_location'] = $upload_dir . $t_im['img_location'];
		$return['t_file_name']		= $t_im['img_location'];
		$return['t_final_width']    = $t_im['img_width'];
		$return['t_final_height']   = $t_im['img_height'];
		
		$return['status'] = 'ok';
		return $return;
	}        
}