<?php
/**
 *
 * @package jipbPlugin
 * @version $Id: jipb_fcore.php, v 1.0 2008/08/12 15:37:17 koudanshi Exp $
 * @copyright (c) 2003-2008 BBpixel
 *
 * Minimum Requirement: PHP 4.3.3 | MySQL 4.1
 */

class jipb_fcore {

	var $_jDB;
	var $_jRootPath;
	var $_regGroupID = 18;
	var $_modsGroupsLimit = ",24,25";//Mods can not see Admin groups
	var $_product 	= "jipbPlugin";
	var $_version 	= "R15.23.1";
	var $_debug 	= "Start - debugging";
	
	function jipb_fcore () {	
	}

	
	/**
	 * Create Joomla account
	 *
	 */
	function doSaveUsers($data=array()) {
		
		global $ipsclass, $regGroupID;
		
		$password	= !empty($data['password']) ? $data['password'] : trim ($ipsclass->input['password']);
		$block		= $data['groupid'] == 8 ? 1 : 0; // banned group

		$jUser = array();
		$jUser['id']       		= intval($data['id']);
		$jUser['name'] 			= $data['name'];
		$jUser['username'] 		= $data['name'];
		$jUser['password'] 		= $password; //must be in raw
		$jUser['email'] 		= $data['email'];
		$jUser['gid'] 			= $data['mgroup'];
		$jUser['usertype'] 		= $data['mgroup'];
		$jUser['block'] 		= $block;
		$jUser['sendEmail'] 	= $data['allow_admin_mails'];
		$jUser['registerDate'] 	= $data['joined'];
		$data['lastvisitDate']	= $data['joined'];
		$data['activation']		= '';
		$data['params']			= '';

		//save to db
		$this->saveUser($jUser,1);
		$this->saveUserGroup($jUser,1);
	}
	
	
	/**
	 * Get Joomla mainsite user info only
	 *
	 * @param Portal user name $userID
	 * @return User info array
	 */
	function getUser($userID=0)	{

	    $this->_debug .= "<p><b>Get Joomla user info:</b><p>";

		global $vbulletin, $jipbConfigs;

		if (!$userID) return;

		//make a connection to Joomla db
		$this->jConnID($jipbConfigs);

		//start to deal with
		$sql = "SELECT * FROM {$this->_jDB->_db['prefix']}users WHERE id={$userID} LIMIT 1";
		$this->_debug .= "</br><div> $sql </div>";
		$results = $this->_jDB->query($sql);
		$member = array();

		if ($this->_jDB->num_rows($results)){
			$member = $this->_jDB->fetchArray($results);
			//switch back to VB db
			$this->_jDB->select_db($vbulletin->config['Database']['dbname']);
			return  $member;
		}

		$this->_debug .= "</br><div>Portal: no member with username=<b> $userID </b> </div>";
		//switch back to VB db
		$this->_jDB->select_db($vbulletin->config['Database']['dbname']);
		return false;
	}


	/**
	 * Get groups list from Joomla mainsite only
	 *
	 * @return array of group name
	 */
	function getGroups() {

		$this->_debug .= "<p><b>Get Portal group:</b><p>";

		global $vbulletin, $jipbConfigs;

		//make a connection to Joomla db
		$this->jConnID($jipbConfigs);

		//Get group
		if (VB_AREA != "AdminCP") {
			$gIDlimit = $this->_modsGroupsLimit;
		}

		$sql = "SELECT group_id as id, name FROM {$this->_jDB->_db['prefix']}core_acl_aro_groups WHERE group_id NOT IN (17,28,29,30 $gID_limit)";
		$this->_debug .= "</br><div> $sql </div>";
		$results 	= $this->_jDB->query($sql);
		$groupsList = array();

		while($row = $this->_jDB->fetchArray($results)){
			$groupsList[$row['id']] .= $row['name'];
		}

		//switch back to VB db
		$this->_jDB->select_db($vbulletin->config['Database']['dbname']);
		return $groupsList;
	}


	/**
	 * Rebuid passwords for ALL subsites
	 * Guarantee Joomla can run standalone in the future
	 * Apply when user password was updated/created from IPB side.
	 *
	 */
	function doRebuildPasswords($userID=0) {

		global $ipsclass;
		
		$fakePwd 	= md5('bbpixel');
		$newPwd 	= $ipsclass->input['PassWord'];
		$userID 	= intval($userID);

		$jUser 				= array();
		$jUser['id'] 		= $userID;
		$jUser['password'] 	= $newPwd;

		$sql = "SELECT password FROM {$this->_jDB->_db['prefix']}users WHERE id=$userID LIMIT 1";
		$results 	= $this->_jDB->query($sql);
		$row 		= $this->_jDB->fetchArray($results);
		$oldPwd 	= $row['password'];

		if ($oldPwd == $fakePwd) {
			$this->saveUser($jUser);
		}
	}


	/**
	 * Create/Update user with buildable list
	 *
	 * @param array $data :: containt which field nanme, field value to do update
	 */
	function saveUser($data=array(), $isnew=0) {

		//build query
		$fields = "";
		$values = "";
		if ($data != null) {
			foreach ($data as $field => $value) {
				//convert GroupID
				if ($field == "gid") {
					$value = $this->convGroupID($value);
				}
				//buld register date
				if ($field == "registerDate") {
					$value = date("Y-m-d H:i:s", $value);
				}
				//build usertype
				if ($field == "usertype") {
					$value = $this->buildUsertype($value);
				}
				//convert to Joomla password type
				if ($field == "password") {
					$value = $this->buildPassword(trim($value));
				}
				//Clean value
				$value 	= $this->_jDB->escapeString($value);
				//make sure we don't stuck with ID
				if (!$isnew ) {
					if ($field != "id")
						$fields .= "`$field`='$value',";
				} else {
					//create fields list
					$fields .= "`$field`,";
					$values .= "'$value',";
				}
			}
			$fields = rtrim($fields, ",");
			$values = rtrim($values, ",");
		}
		$userID = intval($data['id']);

		if (!$isnew) {
			$sql = "UPDATE {$this->_jDB->_db['prefix']}users SET $fields WHERE id=$userID";
		} else {
			$sql = "INSERT INTO {$this->_jDB->_db['prefix']}users ($fields) VALUES ($values)";
		}
		$this->_jDB->query($sql);
		$this->_debug .= "update sql = $sql <br>";
	}



	/**
	 * build usertype from groupID
	 *
	 * @param integer $groupID
	 * @return string
	 */
	function buildUsertype($groupID=0) {

		if (!$groupID) return;

		$groupID = $this->convGroupID($groupID);
		
		$sql = "SELECT name FROM {$this->_jDB->_db['prefix']}core_acl_aro_groups WHERE id=$groupID LIMIT 1";
		$results 	= $this->_jDB->query($sql);
		$row		= $this->_jDB->fetchArray($results);
		$usertype 	= $row['name'];

		return $usertype;
	}



	/**
	 * Buid password
	 *
	 * @param string raw $password 
	 * @return hash password 
	 */
	function buildPassword($password=null) {

		//don't build for md5 pass
		if (strlen($password) == 32) return $password;

		$salt = $this->genSalt(16);
		$password = md5($password.$salt).":".$salt;

		return $password;
	}



	/**
	 * generate Joomla Salt
	 *
	 * @param interger $length
	 * @return string salt
	 */
	function genSalt($length=8) {

		$salt = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
		$len = strlen($salt);
		$makesalt = '';
		mt_srand(10000000 * (double) microtime());

		for ($i = 0; $i < $length; $i ++) {
			$makesalt .= $salt[mt_rand(0, $len -1)];
		}
		return $makesalt;
	}


	/**
	 * Update usergroup permission for 1 account
	 *
	 * @param Integer $userID
	 * @param Integer $groupID
	 */
	function saveUserGroup($data=array(), $isnew=0) {

		if ($data == null) return;

		//convert to Joomla groupID
		$data['gid'] = $this->convGroupID($data['gid']);
		
		if (!$isnew) {
			//get aro id
		    $sql 		= "SELECT id FROM {$this->_jDB->_db['prefix']}core_acl_aro WHERE value={$data['id']} LIMIT 1";
			$results 	= $this->_jDB->query($sql);
			$row 		= $this->_jDB->fetchArray($results);
			$aroID    	= intval($row['aro_id']);
			//do update 
			$sql = "UPDATE {$this->_jDB->_db['prefix']}core_acl_groups_aro_map SET group_id={$data['gid']} WHERE aro_id=$aroID";
			$this->_jDB->query($sql);
		} else {
			$username = $this->_jDB->escapeString(trim($data['username']));
			//acl_aro
			$sql 	= "INSERT INTO {$this->_jDB->_db['prefix']}core_acl_aro (`id`, `section_value`, `value`, `order_value`, `name`, `hidden`) VALUES (NULL, 'users', {$data['id']}, '', '{$username}', '0') ";
			$this->_jDB->query($sql);
			$aroID 	= $this->_jDB->insertID();
			//acl_groups_aro_map
			$sql 	= "INSERT INTO {$this->_jDB->_db['prefix']}core_acl_groups_aro_map (`group_id`, `section_value`, `aro_id`) VALUES ({$data['gid']}, '', $aroID) ";
			$this->_jDB->query($sql);
		}
	}



	/**
	 * Delete user accounts of ALL subsites
	 *
	 * @param string $userIDs
	 */
	function doDeleteUsers($userIDs=null) {

		$this->deleteUser($userIDs);
	}



	/**
	 * Delete a user account
	 *
	 * @param unknown_type $userIDs
	 */
	function deleteUser($userIDs=null) {

		if ($userIDs == null) return ;

		//build acro_id list
		$sql = "SELECT id FROM {$this->_jDB->_db['prefix']}core_acl_aro WHERE value IN ('$userIDs')";
		$results = $this->_jDB->query($sql);
		$aroList = "";
		$this->_debug .= "delete user sql = $sql <br>";
		while ($row = $this->_jDB->fetchArray($results)) {
			$aroList .= $row['id'].",";
		}
		$aroIDs = rtrim($aroList, ",");

		$sqls = array();
		$sqls[] = "DELETE FROM {$this->_jDB->_db['prefix']}users WHERE id IN ('$userIDs')";
		$sqls[] = "DELETE FROM {$this->_jDB->_db['prefix']}core_acl_aro WHERE value IN ('$userIDs')";
		$sqls[] = "DELETE FROM {$this->_jDB->_db['prefix']}core_acl_groups_aro_map WHERE aro_id IN ('$aroIDs')";
		foreach ($sqls as $sql) {
			$this->_jDB->query($sql);
		}
		$this->_debug .= "aroID list = $aroIDs <br>";
	}


	/**
	 * Update Joomla email
	 *
	 * @param integer $userID
	 * @param string $email
	 */
	function doUpdateEmail($userID=0, $email=null) {
		
		if (!$userID) return ;
		
		$jUser = array();
		$jUser['id'] 	= $userID;
		$jUser['email'] = $email;
		$this->saveUser($jUser);
	}
	
	
	/**
	 * Update Joomla Password
	 *
	 * @param integer $userID
	 * @param string $email
	 */
	function doUpdatePWD($userID=0, $rawPWD=null) {
		
		global $ipsclass;
		
		//fixed IPB bug on parse $id at user ACP
		if (!$userID and !empty($ipsclass->input['id'])) {
			$userID = $ipsclass->input['id'];
		}
		
		if (!$userID) return ;
		
		$jUser = array();
		$jUser['id'] 		= $userID;
		$jUser['password'] 	= $rawPWD;
		$this->saveUser($jUser);
	}	
	
	
	/**
	 * Enter description here...
	 *
	 * @param array $data
	 */
	function doUpdateUserGroup($data=array()) {
		
		global $ipsclass;
		
		$groupID = $ipsclass->input['mgroup'];
				
		if ( $groupID ) {
			$jUser = array();
			$jUser['id'] 		= $data['id'];
			$jUser['gid'] 		= $groupID;
			$jUser['usertype'] 	= $groupID;
			$this->saveUser($jUser);
			$this->saveUserGroup($jUser);
		}
	}

	
	/**
	 * Convert IPB groupID to Joomla Group
	 *
	 * @param integer $groupID
	 * @return integer
	 */
	function convGroupID($groupID=0) {
		
		if ( $groupID == 4 or $group == 6 ) {
			$jGroupID = 25;
		} else {
			$jGroupID = $this->_regGroupID;
		}
		return $jGroupID;
	}
	
	
	/**
	 * Update Joomla username
	 *
	 * @param integer $userID
	 * @param string $newName
	 */
	function doUpdateUname($userID=0, $newName=null) {
		
		if (!$userID) return ;
		
		$jUser = array();
		$jUser['id'] 		= $userID;
		$jUser['username'] 	= $newName;
		$this->saveUser($jUser);
	}
	
	/**
	 * Update Joomla display name
	 *
	 * @param integer $userID
	 * @param string $newname
	 */
	function doUpdateName($userID=0, $newName=null) {
		
		if (!$userID) return ;
		
		$jUser = array();
		$jUser['id'] 	= $userID;
		$jUser['name'] 	= $newName;
		$this->saveUser($jUser);
	}	
}





?>