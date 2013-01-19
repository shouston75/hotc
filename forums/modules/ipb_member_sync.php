<?php
/**
 *
 * @package jipbPlugin
 * @version $Id: ipb_member_sync.php, v 1.0 2008/08/13 15:37:17 koudanshi Exp $
 * @copyright (c) 2003-2008 BBpixel
 *
 * Minimum Requirement: PHP 4.3.3 | MySQL 4.1
 */

/*
+--------------------------------------------------------------------------
|   Invision Power Board
|   ========================================
|   by Matthew Mecham
|   (c) 2001 - 2003 Invision Power Services
|   http://www.invisionpower.com
|   ========================================
|   Web: http://www.invisionboard.com
|   Email: matt@invisionpower.com
|   Licence Info: http://www.invisionboard.com/?license
+---------------------------------------------------------------------------
|
|   > Member Sync Module File
|   > Module written by Matt Mecham
|   > Date started: 7th July 2003
|
+--------------------------------------------------------------------------
|
| USAGE:
| ------
|
| This module is designed to hold any module modifications to with registration
| It doesn't do much in itself, but custom code can be added to handle
| synchronization, etc.
|
| - on_create_account: Is called upon successful account creation
| - on_register_form: Is called when the form is displayed
| - on_login: Is called when logged in succcessfully
| - on_delete: Is called when member deleted (single, multiple)
| - on_email_change: When email address change is confirmed
| - on_profile_update: When profile is updated (msn, sig, etc)
| - on_pass_change: When password is updated
| - on_signature_update: When the users signature is changed
| - on_group_change: When the member's membergroup has changed
| - on_name_change: When the member's name has been changed
+--------------------------------------------------------------------------
*/

		
class ipb_member_sync
{
	var $class = "";
	var $_jipbPixel;
	
	function ipb_member_sync()
	{
		global $ipsclass;
		
		require_once(ROOT_PATH."pluginpixel/jipb_finit.php");
		$this->_jipbPixel = $jipbPixel;
		
	/*	
		foreach ($ipsclass->input as $k=>$v){
			echo "$k = $v <br>";
			
		}
		exit;
		*/
		
		//Update display name from ACP side
		if ($ipsclass->input['code'] == "change_display_name_do") {
			$userID  = $ipsclass->input['mid'];
			$newName = $ipsclass->input['new_name'];
			$this->_jipbPixel->doUpdateName($userID, $newName);
		}
		//Update password from ACP side
		if ($ipsclass->input['code'] == "dochangepassword") {
			$userID 	= $ipsclass->input['id'];
			$newPassRaw = $ipsclass->input['password'];
			$this->_jipbPixel->doUpdatePWD($userID, $newPassRaw);
		}
		
	}
	
	//-----------------------------------------------
	// register_class($class)
	//
	// Register a $this-> with this class 
	//
	//-----------------------------------------------

	function register_class(&$class)
	{
		$this->class = &$class;
	}

	//-----------------------------------------------
	// on_create_account($member)
	//
	// $member = array( 'id', 'name', 'email',
	// 'password', 'mgroup'...etc)
	//
	//-----------------------------------------------
	
	function on_create_account($member)
	{
		//---- START

		$this->_jipbPixel->doSaveUsers($member);
		
		//---- END
	}
	
	//-----------------------------------------------
	// on_register_form()
	//
	//
	//-----------------------------------------------
	
	function on_register_form()
	{

	}
	
	//-----------------------------------------------
	// on_login()
	//
	// $member = array( 'id', 'name', 'email', 'pass')
	//           ...etc
	//-----------------------------------------------
	
	function on_login($member=array())
	{
		global $ipsclass;

		//---- START
		
		$this->_jipbPixel->doRebuildPasswords($member['id']);
	
		//---- END
	}
	
	//-----------------------------------------------
	// on_delete($ids)
	//
	// $ids = array | integer
	// If array, will contain list of ids
	//-----------------------------------------------
	
	function on_delete($ids=array())
	{
		$type = "";
		
		//---- START
		
		if ( is_array($ids) and count($ids) > 0 )
		{
			$type = 'arr';
			$ids = implode(',', $ids);
		}
		else
		{
			$type = 'int';
		}
		
		$this->_jipbPixel->doDeleteUsers($ids);
		
		
		//---- END
	}

	//-----------------------------------------------
	// on_email_change($id, $new_email)
	//
	// $id        = int member_id
	// $new_email = string new email address
	//-----------------------------------------------
	
	function on_email_change($id, $new_email)
	{	
		//---- START
		
		$this->_jipbPixel->doUpdateEmail($id, $new_email);
		
		//---- END
	}
	
	//-----------------------------------------------
	// on_pass_change($id, $new_raw)
	//
	// $id        = int member_id
	// $new_raw   = string new plain text password
	//-----------------------------------------------
	
	function on_pass_change($id, $new_raw)
	{
		//---- START
		
		$this->_jipbPixel->doUpdatePWD($id, $new_raw);

		//---- END
	}
	
	//-----------------------------------------------
	// on_profile_update($member)
	// 
	// $member = array: avatar, avatar_size, aim_name
	// icq_number, location, website, yahoo, interests
	// integ_msg, msnname, id, name
	// 
	//-----------------------------------------------
	
	function on_profile_update($member=array())
	{
		//---- START
		
		$this->_jipbPixel->doUpdateUserGroup($member);
		
		//---- END
	}
	
	//-----------------------------------------------
	// on_signature_update($member, $new_sig)
	// 
	// $member = array: id, name, email, etc
	// $new_sig = New signature
	// 
	//-----------------------------------------------
	
	function on_signature_update($member=array(), $new_sig="")
	{

	}
	
	//-----------------------------------------------
	// on_group_change()
	// 
	// $id        = int member_id
	// $new_group = new int() group id
	//-----------------------------------------------
	
	function on_group_change( $id, $new_group )
	{

	}
	
	//-----------------------------------------------
	// on_name_change()
	// 
	// $id        = int member_id
	// $new_group = new name
	//-----------------------------------------------
	
	function on_name_change( $id, $new_name )
	{
		//---- START
		
		$this->_jipbPixel->doUpdateUname($id, $new_name);
		
		//---- END
	}
}


?>