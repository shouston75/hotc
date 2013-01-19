<?php
/**
 *
 * @package jipbPlugin
 * @version $Id: jipb_fconfig.php, v 1.0 2008/08/12 15:37:17 koudanshi Exp $
 * @copyright (c) 2003-2008 BBpixel
 *
 * Minimum Requirement: PHP 4.3.3 | MySQL 4.1
 */


class corePixel {

	var $_limit = QUERY_LIMIT;
	var $_debug;


	function corePixel(){

	}


	/**
	 * Assign connection info for Joomla
	 *
	 */
	function jConnID() {

		global $dbPixel, $jipbConfigs;

		$dbPixel->_db['host'] 	= $jipbConfigs['dbhost'];
		$dbPixel->_db['name'] 	= $jipbConfigs['dbname'];
		$dbPixel->_db['user'] 	= $jipbConfigs['dbuser'];
		$dbPixel->_db['pwd'] 	= $jipbConfigs['dbpass'];
		$dbPixel->_db['prefix'] = $jipbConfigs['dbprefix'];
	}


	/**
	 * Assign connection info for IPB
	 *
	 */
	function bbConnID() {

		global $dbPixel, $INFO;

		$dbPixel->_db['host'] 	= $INFO['sql_host'];
		$dbPixel->_db['name'] 	= $INFO['sql_database'];
		$dbPixel->_db['user'] 	= $INFO['sql_user'];
		$dbPixel->_db['pwd'] 	= $INFO['sql_pass'];
		$dbPixel->_db['prefix'] = $INFO['sql_tbl_prefix'];
	}


	/**
	 * Increase Joomla userID
	 *
	 */
	function doIncrease() {

		global $dbPixel, $tplPixel;

		// Find the max id from ipb
		$this->bbConnID();
		$dbPixel->connect();
		$sql 		= "SELECT max(id) as maxid FROM {$dbPixel->_db['prefix']}members LIMIT 1";
		$result		= $dbPixel->query($sql);
		$rows		= $dbPixel->fetchArray($result);
		$bbMaxID 	= intval($rows['maxid']);
		$maxID 		= $bbMaxID;

		// start to update Joomla info
		$this->jConnID();
		$dbPixel->connect();

		// Update mos_users->id
		$query = "UPDATE {$dbPixel->_db['prefix']}users SET id=id+{$maxID} ORDER BY id DESC";
		$dbPixel->query($query);
		#..mos_content->created_by
		$query = "UPDATE {$dbPixel->_db['prefix']}content SET created_by=created_by+{$maxID} ORDER BY created_by DESC";
		$dbPixel->query($query);
		#..mos_content->modified_by
		$query = "UPDATE {$dbPixel->_db['prefix']}content SET modified_by=modified_by+{$maxID} WHERE modified_by>0 ORDER BY modified_by DESC";
		$dbPixel->query($query);
		#..mos_contact_details->user_id
		$query = "UPDATE {$dbPixel->_db['prefix']}contact_details SET user_id=user_id+{$maxID} WHERE user_id>0 ORDER BY user_id DESC";
		$dbPixel->query($query);
		#..mos_messages->user_id_from
		$query = "UPDATE {$dbPixel->_db['prefix']}messages SET user_id_from=user_id_from+{$maxID} ORDER BY user_id_from DESC";
		$dbPixel->query($query);
		#..mos_messages->user_id_to
		$query = "UPDATE {$dbPixel->_db['prefix']}messages SET user_id_to=user_id_to+{$maxID} ORDER BY user_id_to DESC";
		$dbPixel->query($query);
		#..mos_messages_cfg->user_id
		$query = "UPDATE {$dbPixel->_db['prefix']}messages_cfg SET user_id=user_id+{$maxID} ORDER BY user_id DESC";
		$dbPixel->query($query);


/* Turn on if any
        #Extra
		$query = "UPDATE {$dbPixel->_db['prefix']}zoomfiles SET uid=uid+{$maxID} ";
		$dbPixel->query($query);
		$query = "UPDATE {$dbPixel->_db['prefix']}zoom SET uid=uid+{$maxID} ";
		$dbPixel->query($query);
		$query = "UPDATE {$dbPixel->_db['prefix']}sb_users SET userid=userid+{$maxID} ";
		$dbPixel->query($query);
		$query = "UPDATE {$dbPixel->_db['prefix']}sb_subscriptions SET userid=userid+{$maxID} ";
		$dbPixel->query($query);
		$query = "UPDATE {$dbPixel->_db['prefix']}sb_messages SET userid=userid+{$maxID} ";
		$dbPixel->query($query);
		$query = "UPDATE {$dbPixel->_db['prefix']}pshop_shopper_vendor_xref SET user_id=user_id+{$maxID} ";
		$dbPixel->query($query);
		$query = "UPDATE {$dbPixel->_db['prefix']}events SET created_by=created_by+{$maxID} ";
		$dbPixel->query($query);
*/

		$url		= "index.php?action=syncBB";
		$title		= "Increased ".CMS_NAME." userID completed";
		$message 	= "Turn to next step..";
		$tplPixel->redirect($url, $title, $message);
	}



	/**
	 * Create a temporary table to cache synchronized joomla ID
	 *
	 */
	function createTmpSyncTable() {

		global $dbPixel;

		$this->bbConnID();
		$dbPixel->connect();
		$sql 	= array();
		$sql[] 	= "DROP TABLE IF EXISTS bbpixel_jsync_users";
		$sql[] 	= "CREATE TABLE bbpixel_jsync_users (
						id int(11) NOT NULL auto_increment,
						PRIMARY KEY  (id)
					)";
		foreach ($sql AS $v) {
			$dbPixel->query($v);
		}
	}


	/**
	 * Synchronized users from Joomla to IPB
	 *
	 * @return unknown
	 */
	function doSyncBB() {

		global $dbPixel, $tplPixel, $ipsclass;

		$start 	= intval($ipsclass->input['st']);

		//Get joomla user
		$this->jConnID();
		$dbPixel->connect();

		$jSql 		= "SELECT id, username, password, email, gid, UNIX_TIMESTAMP(registerDate) AS joined, block FROM {$dbPixel->_db['prefix']}users WHERE 1 ORDER BY id ASC LIMIT $start, {$this->_limit}";
		$jResults 	= $dbPixel->query($jSql);
		$count 		= intval($dbPixel->numRows($jResults));

		$this->_debug .= "dosync::jSql=$jSql <br />";
		$this->_debug .= "dosync::jResult=$jResults <br />";
		$this->_debug .= "dosync::count=$count<br />";

		if ($count > 0) {
			$bbNums = 0;
			while ($jRow = $dbPixel->fetchArray($jResults)) {

				//Get IPB user info base on Joomla email condition
				$this->bbConnID();
				$dbPixel->connect();

				$email 		= $dbPixel->escapeString($jRow['email']);
				$bbSql 		= "SELECT id, name FROM {$dbPixel->_db['prefix']}members WHERE email = '$email' LIMIT 1";
				$bbResults 	= $dbPixel->query($bbSql);
				$bbRow 		= $dbPixel->fetchArray($bbResults);

				$this->_debug .= "dosync::jrowID={$jRow['id']} <br />";
				$this->_debug .= "dosync::bbSql=$bbSql <br />";
				$this->_debug .= "dosync::bbResult=$bbResults <br />";
				$this->_debug .= "dosync::bbrow=$bbRow<br />";

				//echo $this->_debug;
				//exit;

				//Found IPB record, let update Joomla user with IPB info
				if (!empty($bbRow['id'])) {
					// cache J userID to IPB database
					$sql = "REPLACE INTO bbpixel_jsync_users VALUES ({$bbRow['id']})";
					$dbPixel->query($sql);

					// update joomla info
					$this->updateJinfo($jRow['id'], $bbRow);

					//Update IPB to admin group
					if ($jRow['gid'] == 25) {
						$this->bbConnID();
						$dbPixel->connect();
						$sql = "UPDATE {$dbPixel->_db['prefix']}members SET mgroup=4 WHERE id='{$bbRow['id']}' ";
						$dbPixel->query($sql);
					}
				} else {
					// Create IPB account
					$newID = $this->createBB($jRow);

					// cache J userID to IPB database
					$sql = "REPLACE INTO bbpixel_jsync_users VALUES ({$newID})";
					$dbPixel->query($sql);

					// Update Joomla user info with new insert ID
					$newMem = array();
					$newMem['id'] = $newID;
					$newMem['name'] = $jRow['username'];
					$this->updateJinfo($jRow['id'], $newMem);
					$bbNums++;
				}
			}

			// Continue to sync
			$end 		= $start + $count;
			$url		= "index.php?action=doSyncBB&st=$end";
			$title		= 'Synchronizing users from '.CMS_NAME.' to '.BB_NAME.'...';
			$message 	= "Data analyzing up to [$end] records <br /> Transfered [$bbNums] records";
			$tplPixel->redirect($url, $title, $message);
		} else {
			// go to next step
			$url		= "index.php?action=syncJ";
			$title		= 'Synchronized users from '.CMS_NAME.' to '.BB_NAME.' completely';
			$message 	= "Redirecting to next step...";
			$tplPixel->redirect($url, $title, $message);

		}
	}



	/**
	 * Update Joomla userID
	 *
	 * @param int $curID current user ID
	 * @param array $newMem new user info
	 */
	function updateJinfo($curID=0, $newMem=array()) {

		global $dbPixel;

		$this->jConnID();
		$dbPixel->connect();

		$username = $dbPixel->escapeString($newMem['name']);
		
		//Update mos_users->id
		$query = "UPDATE {$dbPixel->_db['prefix']}users SET id='{$newMem['id']}', username='$username' WHERE id='{$curID}'";
		$dbPixel->query($query);
		#..mos_content->created_by
		$query = "UPDATE {$dbPixel->_db['prefix']}content SET created_by='{$newMem['id']}' WHERE created_by='{$curID}' ";
		$dbPixel->query($query);
		#..mos_content->modified_by
		$query = "UPDATE {$dbPixel->_db['prefix']}content SET modified_by='{$newMem['id']}' WHERE modified_by='{$curID}' ";
		$dbPixel->query($query);
		#..mos_contact_details->user_id
		$query = "UPDATE {$dbPixel->_db['prefix']}contact_details SET user_id='{$newMem['id']}' WHERE user_id='{$curID}' ";
		$dbPixel->query($query);
		#..mos_messages->user_id_from
		$query = "UPDATE {$dbPixel->_db['prefix']}messages SET user_id_from='{$newMem['id']}' WHERE user_id_from='{$curID}' ";
		$dbPixel->query($query);
		#..mos_messages->user_id_to
		$query = "UPDATE {$dbPixel->_db['prefix']}messages SET user_id_to='{$newMem['id']}' WHERE user_id_to='{$curID}' ";
		$dbPixel->query($query);
		#..mos_messages_cfg->user_id
		$query = "UPDATE {$dbPixel->_db['prefix']}messages_cfg SET user_id='{$newMem['id']}' WHERE user_id='{$curID}' ";
		$dbPixel->query($query);
		/* Turn on if any
        #Extra
		$query = "UPDATE {$dbPixel->_db['prefix']}zoomfiles SET uid='{$newMem['id']}' WHERE uid='{$newMem['id']}' ";
		$dbPixel->query($query);
		$query = "UPDATE {$dbPixel->_db['prefix']}zoom SET uid='{$newMem['id']}' WHERE uid='{$newMem['id']}' ";
		$dbPixel->query($query);
		$query = "UPDATE {$dbPixel->_db['prefix']}sb_users SET userid='{$newMem['id']}' WHERE userid='{$newMem['id']}' ";
		$dbPixel->query($query);
		$query = "UPDATE {$dbPixel->_db['prefix']}sb_subscriptions SET userid='{$newMem['id']}' WHERE userid='{$newMem['id']}' ";
		$dbPixel->query($query);
		$query = "UPDATE {$dbPixel->_db['prefix']}sb_messages SET userid='{$newMem['id']}' WHERE userid='{$newMem['id']}' ";
		$dbPixel->query($query);
		$query = "UPDATE {$dbPixel->_db['prefix']}pshop_shopper_vendor_xref SET user_id='{$newMem['id']}' WHERE user_id='{$newMem['id']}' ";
		$dbPixel->query($query);
		$query = "UPDATE {$dbPixel->_db['prefix']}events SET created_by='{$newMem['id']}' WHERE created_by='{$newMem['id']}' ";
		$dbPixel->query($query);
		*/
	}



	/**
	 * Create IPB account
	 *
	 * @param array $data input member info
	 */
	function createBB($data=null) {

		global $dbPixel, $INFO;

		$username = $dbPixel->escapeString($data['username']);
		$email    = $dbPixel->escapeString($data['email']);
		$mgroup   = $data['block'] ? 5 : $INFO['member_group']; // 5 = banned group
		if ($data['gid']==25) {
			//Force to be admin group
			$mgroup = 4;
		}
		$joined	  = $dbPixel->escapeString($data['joined']);
		$password = md5("bbpixel"); //temp password used for recovery later

		//Member converge
		$sql = "INSERT INTO {$dbPixel->_db['prefix']}members_converge (`converge_id`, `converge_email`, `converge_joined`, `converge_pass_hash`)
					VALUES ('', '$email', '$joined', '$password')";
		$dbPixel->query($sql);

		$userID = intval($dbPixel->insertID());

		// Member
		$sql = "INSERT INTO {$dbPixel->_db['prefix']}members (`id`, `name`, `email`, `members_l_display_name`, `members_l_username`, `members_display_name`, `mgroup`, `joined`, `ip_address`)
					VALUES ($userID, '$username', '$email', '$username', '$username', '$username', $mgroup, '$joined', '127.0.0.1')";
		$dbPixel->query($sql);

		// Member Extra...
		$sql = "INSERT INTO {$dbPixel->_db['prefix']}member_extra (`id`) VALUES ($userID)";
		$dbPixel->query($sql);

		return $userID;
	}



	/**
	 * Synchronize users from IPB to Joomla
	 *
	 * @return unknown
	 */
	function doSyncJ() {

		global $dbPixel, $tplPixel, $ipsclass;
		$start 	= intval($ipsclass->input['st']);

		//Get data from ipb
		$this->bbConnID();
		$dbPixel->connect();

		$bbSql 		= "SELECT m.id, m.name, m.email, m.mgroup, m.joined FROM {$dbPixel->_db['prefix']}members m LEFT JOIN bbpixel_jsync_users ju USING (id)
							WHERE ju.id is NULL
								ORDER BY m.id ASC
									LIMIT {$this->_limit}";
		$bbResults 	= $dbPixel->query($bbSql);
		$count 		= intval($dbPixel->numRows($bbResults));
		$this->_debug .= "dosyncJ:bbSql=$bbSql <br />";
		$this->_debug .= "dosyncJ:count=$count <br />";
		//echo $this->_debug;
		//exit;
		if ($count > 0) {
			$bbNums = 0;
			while ($bbRow = $dbPixel->fetchArray($bbResults)) {
				$newID = $this->createJ($bbRow);

				// cache Joomla userID
				$this->bbConnID();
				$dbPixel->connect();
				$sql = "REPLACE INTO bbpixel_jsync_users VALUES ({$newID})";
				$dbPixel->query($sql);
				$bbNums ++;
			}
			// Continue to sync
			$end 		= $start + $count;
			$url		= "index.php?action=doSyncJ&st=$end";
			$title		= "Synchronizing users from ".BB_NAME." to ".CMS_NAME."...";
			$message 	= "Data analyzing up to [$end] records <br /> Transfered [$bbNums] records";
			$tplPixel->redirect($url, $title, $message);
		} else {
			// go to rebuild Joomla acl group
			$url		= "index.php?action=doRebuild";
			$title		= "Synchronized users from ".BB_NAME." to ".CMS_NAME." completely";
			$message 	= "Redirecting to next step...";
			$tplPixel->redirect($url, $title, $message);
		}
	}


	/**
	 * Create Joomla account
	 *
	 * @param unknown_type $acount
	 */
	function createJ($data) {

		global $dbPixel;

		$userID 	= intval($data['id']);
		$username 	= $dbPixel->escapeString($data['name']);
		$password	= md5("bbpixel"); // temp password used for recovery later
		$email 		= $dbPixel->escapeString($data['email']);
		$joined 	= date("Y-m-d\TH:i:s", $data['joined']);
		$usertype	= $data['mgroup'] == 4 ? "Super Administrator" : "Registered";
		$block		= $data['mgroup'] == 5 ? 1 : 0; // banned group
		$gid		= $data['mgroup'] == 4 ? 25 : 18;

		// insert to Joomla DB
		$this->jConnID();
		$dbPixel->connect();

		$sql 		= "INSERT INTO {$dbPixel->_db['prefix']}users (`id`, `name`, `username`, `email`, `password`, `usertype`, `block`, `sendEmail`, `gid`, `registerDate`, `lastvisitDate`, `params`)
							VALUES ($userID, '$username', '$username', '$email', '$password', '$usertype', $block, 1, $gid, '$joined', '', '') ";
		$dbPixel->query($sql);

		return $userID;
	}



	/**
	 * Rebuild Joomla ACL groups permission
	 *
	 */
	function doRebuild() {

		global $dbPixel, $tplPixel;

		$this->jConnID();
		$dbPixel->connect();

		$sql = array();
		// reset auto increase ID
		$sql[] = ("ALTER TABLE {$dbPixel->_db['prefix']}users auto_increment = 0;");
		$sql[] = ("TRUNCATE TABLE {$dbPixel->_db['prefix']}core_acl_aro;");
		$sql[] = ("TRUNCATE TABLE {$dbPixel->_db['prefix']}core_acl_groups_aro_map;");
		$sql[] = ("INSERT INTO {$dbPixel->_db['prefix']}core_acl_aro (id, section_value, value, order_value, name, hidden)
					SELECT id, 'users', id, '0', username, '0'
						FROM {$dbPixel->_db['prefix']}users
					");
		$sql[] = ("INSERT INTO {$dbPixel->_db['prefix']}core_acl_groups_aro_map (group_id, section_value, aro_id)
					SELECT gid, '', id
						FROM {$dbPixel->_db['prefix']}users
					");
		foreach ($sql AS $v) {
			$dbPixel->query($v);
		}
		// go to final step
		$url		= "index.php?action=final";
		$title		= 'Rebuild '.CMS_NAME.' ACL groups permission completely';
		$message 	= "Redirecting to next step...";
		$tplPixel->redirect($url, $title, $message);
	}



	/**
	 * Display welcome screen
	 *
	 */
	function viewIntro() {

		global $tplPixel;

		$tplPixel->_title = "Welcome...";
		$tplPixel->_content = "
	<span class='titlehead'>".PRODUCT_FULL_NAME." Synchronizing engine</span>
	<div class='content'>
		<b>".PRODUCT_SHORT_NAME."</b> will: <br />
		1. Increase ID of ".CMS_NAME." users in order to avoid conflicting with ".BB_NAME." users <br />
		2. Synchronize data of two user tables <br />
		3. Copy unique users from ".CMS_NAME." to ".BB_NAME." <br />
		4. Copy unique users from ".BB_NAME." to ".CMS_NAME." <br />
		<br/>
		<b>Note: Before you start, we recommend you BACKUP your database first</b><br /><br />
		<div class='process'><a href='index.php?action=increase'><img src='../images/btn_proceed.gif' border='0' alt='proceed'></a></div>
	</div>
			";
		$tplPixel->output();
	}



	/**
	 * Display increase Joomla user screen
	 *
	 */
	function viewIncrease() {

		global $tplPixel;

		$tplPixel->_title = "Increase ".CMS_NAME." userID...";
		$tplPixel->_content = "
	<span class='titlehead'>Step 1: Increase ".CMS_NAME." userID</span>
	<div class='content'>
		In order to invoid confliction, we will increase ID of ".CMS_NAME." users<br/><br/>
		Click <b>Proceed >></b> button to start<br /><br />
		<div class='process'><a href='index.php?action=doIncrease'><img src='../images/btn_proceed.gif' border='0' alt='proceed'></a></div>
	</div>
		";

		$tplPixel->output();
	}



	/**
	 * Display Synchronize users from Joomla to IPB screen
	 *
	 */
	function viewSyncBB() {

		global $tplPixel;

		// Create temp sync table for Joomla cache ID
		$this->createTmpSyncTable();

		$tplPixel->_title = "Synchronize users...";
		$tplPixel->_content = "
	<span class='titlehead'>Step 2: Synchronize users from ".CMS_NAME." to ".BB_NAME."...</span>
	<div class='content'>
		We will synchronize accounts (username, email, password...) <br />
		Make sure that ".CMS_NAME." and ".BB_NAME." must have unique email address of its own or this step will be hang on<br />
		This process will take a little longer or shorter depends on your database size <br /><br />
		Click <b>Proceed >></b> button to start <br /><br />
		<div class='process'><a href='index.php?action=doSyncBB'><img src='../images/btn_proceed.gif' border='0' alt='proceed'></a></div>
	</div>
		";
		$tplPixel->output();
	}



	/**
	 * Display Synchronize users from IPB to Joomla screen
	 *
	 */
	function viewSyncJ() {

		global $tplPixel;

		$tplPixel->_title = "Synchronize users...";

		$tplPixel->_content = "
	<span class='titlehead'>Step 3: Synchronize users from ".BB_NAME." to ".CMS_NAME."...</span>
	<div class='content'>
		We will synchronize accounts (username, email, password...) <br />
		Make sure that ".CMS_NAME." and ".BB_NAME." must have unique email address of its own or this step will be hang on<br />
		This process will take a little longer or shorter depends on your database size <br /><br />
		Click <b>Proceed >></b> button to start <br /><br />
		<div class='process'><a href='index.php?action=doSyncJ'><img src='../images/btn_proceed.gif' border='0' alt='proceed'></a></div>
	</div>
		";
		$tplPixel->output();
	}



	/**
	 * Display final screen
	 *
	 */
	function viewFinal() {

		global $tplPixel;

		$tplPixel->_title = "Finished...";

		$tplPixel->_content = "
	<span class='titlehead'>Synchronization progress completed successfully</span>
	<div class='content'>
		You should <b>DELETE</b> this sync directory for security
		<br/><br/>
		<b>Note: You must login to ".BB_NAME." Admin Control Panel to reset password and set the 'Supper Administrator' permission for
		the ".CMS_NAME." Administrator account </b> <br/><br/>
		For more info or support, please visit our homepage <a href='http://www.bbpixel.com' targer='_balnk'>BBPixel.com</a> <br /><br />
	</div>
		";
		$tplPixel->output();
	}
}
?>