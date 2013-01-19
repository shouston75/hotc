<?php
/**
 * Invision Power Services
 * IP.Board v3.0.1
 * 123flashchat Public
 *
 * @author 		$Author: TopCMM $
 * @copyright	(c) 2001 - 2010 TopCMM, Inc.
 * @license		http://www.invisionpower.com/community/board/license.html
 * @package		Invision Power Board
 * @subpackage	123flashchat
 * @link		http://www.123flashchat.com
 */

if ( ! defined( 'IN_IPB' ) )
{
	print "<h1>Incorrect access</h1>You cannot access this file directly. If you have recently upgraded, make sure you upgraded all the relevant files.";
	exit();
}

class public_123flashchat_123flashchat_fchat extends ipsCommand
{
	/**
	 * Class entry point
	 *
	 * @access	public
	 * @param	object		Registry reference
	 * @return	void		[Outputs to screen/redirects]
	 */
	public function doExecute( ipsRegistry $registry )
	{
	
		/* Load language  */
		$this->registry->class_localization->loadLanguageFile( array( 'public_123flashchat' ) );


		switch($this->request['t'])
		{
			case 'status':
			default:
				$fc_status = $this->fc_get_status();
				$fc_status_str = $fc_status['ln'] . " logins";
				if (array_key_exists('rn', $fc_status))
				{
					$fc_status_str = $fc_status['rn'] . " rooms, " .  $fc_status['cn'] . " connections, " . $fc_status_str;
				}
		            echo "document.write(\"" . $fc_status_str . "\");";
		            break;
		        case 'room_list':
		            $fc_rooms = $this->fc_get_room_list();
		            foreach ($fc_rooms as $fc_room)
		            {   
		                $fc_client_url = 'index.php?app=123flashchat&room=' . $fc_room['id'];
		                $fc_room_list .= (($fc_room_list != '') ? ', ' : '') . ($this->settings['fc_client_present'] ? '<a href="'. $fc_client_url . '" target="_blank" >' : '<a href="http://www.123flashchat.com/" onClick="openFchat(' . "'" . $fc_client_url . "'" . ');return false;" >') . htmlspecialchars($fc_room['name']) . "</a>(" . $fc_room['count'] . ")"; 
		            }
		            echo "document.write(\"" . str_replace('"','\"',($fc_room_list ? $fc_room_list : 'None')) . "\");";
		            break;
		        case 'user_list':
		            $fc_users = $this->fc_get_user_list();
		            foreach ($fc_users as $fc_user)
		            {
		                $fc_user_list .= (($fc_user_list != '') ? ', ' : '') . str_replace('"','\"',$fc_user['name']);
		            }
		            echo "document.write(\"" . ($fc_user_list ? $fc_user_list : 'None') . "\");";
		            break;
			}
	
		/* Output */
		//$this->registry->output->addContent( $this->output );
		//$this->registry->output->sendOutput();
		//echo $this->output;
	}
	function fc_get_status()
	{   
	    $status_json = @file_get_contents("cache/status.js");
	    if ((time() > $this->settings['fc_status_update_time']) || !$status_json)
	    {
	        $server =  $this->settings['fc_server'];
	        switch ($server)
	        {   
	            case 0:
	        	    $status_js = $this->settings['fc_api_url'] . "online.js";
	        	    if($rs = @file_get_contents($status_js))
	        	    {
	        	        $status_json = substr($rs,11,-1);
	        	    }
	        	    break;
	        	case 1:
	        	    $status_js = $this->settings['fc_api_url'] . "online.js?group=" . $this->settings['fc_group'];
	        	    if($rs = @file_get_contents($status_js))
	        	    {
	        	        $status_json = substr($rs,11,-1);
	        	    }
	        	    break;
	        	case 2:
	        	    $status_js = "http://free.123flashchat.com/freeroomnum.php?roomname=" . $this->settings['fc_room'];
	        	    if($rs = @file_get_contents($status_js))
	        	    {
                        preg_match("/document.write\('(.*)'\);/",$rs,$matches);
	        	        $status['ln'] = $matches[1];
	        	        $status_json = json_encode($status);
	        	    }
	        	    break;
	        }
			@file_put_contents("cache/status.js",$status_json);
	        $status['fc_status_update_time'] = time() + 150;
	        $this->DB->update( 'core_sys_conf_settings', array('conf_value' => $status['fc_status_update_time']), "conf_key ='fc_status_update_time'");
			$this->settingsRebuildCache();
	    }
		return json_decode($status_json,true);
	}
	
	
	function fc_get_room_list()
	{
		$rooms_json =  @file_get_contents("cache/rooms.js");
	    if ((time() > $this->settings['fc_rooms_update_time']) || !$rooms_json)
	    {
	    	$server =  $this->settings['fc_server'];
	        switch ($server)
	        {
	            case 0:
	        	    $room_js = $this->settings['fc_api_url'] . "rooms.js";
	        	    break;
	            case 1:
	        	    $room_js = $this->settings['fc_api_url'] . "rooms.js?group=" . $this->settings['fc_group'];
	        	    break;
	        }
	        if($rs = @file_get_contents($room_js))
	        {
	        	$rooms_json = substr($rs,10,-1);
	        }
	        @file_put_contents("cache/rooms.js",$rooms_json);
	        $rooms['fc_rooms_update_time'] = time() + 150;
	        $this->DB->update( 'core_sys_conf_settings', array('conf_value' => $rooms['fc_rooms_update_time']), "conf_key ='fc_rooms_update_time'");
			$this->settingsRebuildCache();
	    }
	
	    return json_decode($rooms_json, true);
	}
	
	
	function fc_get_user_list()
	{
		$users_json =  @file_get_contents("cache/users.js");
	    if ((time() > $this->settings['fc_users_update_time']) || !$users_json)
	    {
	    	$server =  $this->settings['fc_server'];
	        switch ($server)
	        {
	            case 0:
	                $rooms = $this->fc_get_room_list();
	                $users = array();
	                foreach ($rooms as $room)
	                {
	                    $user_js = $this->settings['fc_api_url'] . "roomonlineusers.js?roomid=" . $room['id'];
	                    if($rs = @file_get_contents($user_js))
	                    {
	                        $users = array_merge($users, json_decode(substr($rs,20,-1),true));
	                    }
	                }
	                $users_json = json_encode($users);
	        	    break;
	            case 1:
	        	    $user_js = $this->settings['fc_api_url'] . "roomonlineusers.js?group=" . $this->settings['fc_group'];
	        	    if($rs = @file_get_contents($user_js))
	        	    {
	        	        $users_json = substr($rs,20,-1);
	        	    }
	        	    break;
	        	case 2:
	        	    $user_js = "http://free.123flashchat.com/freeroomuser.php?roomname=" . $this->settings['fc_room'];
	        	    if($rs = @file_get_contents($user_js))
	    	        {
                        preg_match("/document.write\('(.*)'\);/",$rs,$matches);
	    	            foreach (explode(',', $matches[1]) as $user)
	    	            {
	    	                $users[] = array('name' => $user);
	    	            }
	    	        }
	    	        $users_json = json_encode($users);
	    	        break;
	        }
	        @file_put_contents("cache/users.js",$users_json);
	        $users['fc_users_update_time'] = time() + 150;
	        $this->DB->update( 'core_sys_conf_settings', array('conf_value' => $users['fc_users_update_time']), "conf_key ='fc_users_update_time'");
			$this->settingsRebuildCache();
	    }
	
	    return json_decode($users_json, true);
	}
	/**
	 * Rebuild settings cache
	 *
	 * @access	public
	 * @return	void
	 */
	function settingsRebuildCache()
	{
		$settings = array();
		
		$this->DB->build( array( 'select' => '*', 'from' => 'core_sys_conf_settings', 'where' => 'conf_add_cache=1' ) );
		$info = $this->DB->execute();
	
		while ( $r = $this->DB->fetch($info) )
		{	
			$value = $r['conf_value'] != "" ?  $r['conf_value'] : $r['conf_default'];
			
			if ( $value == '{blank}' )
			{
				$value = '';
			}

			$settings[ $r['conf_key'] ] = $value;
		}
		
		$this->cache->setCache( 'settings', $settings, array( 'array' => 1, 'deletefirst' => 1 ) );
	}

}
