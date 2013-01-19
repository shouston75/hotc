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

$ip_allowed = "";   // ip addresses delimited by comma, example: $ip_allowed='127.0.0.1, 192.168.0.1";
if (!empty($ip_allowed))
{
    $ip = isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR'];
    $ip_allowed_array = array_map('trim', explode(',', $ip_allowed));
    if (!in_array($ip,$ip_allowed_array))
    {
        exit();
    }
}

// 123 Flash Chat  Login Return Value
define('FC_LOGIN_SUCCESS',	0);
define('FC_LOGIN_ERROR_PASSWD', 1);
define('FC_LOGIN_ERROR', 3);
define('FC_LOGIN_ERROR_NOUSERID', 4);
define('FC_LOGIN_SUCCESS_ADMIN', 5);

class public_123flashchat_123flashchat_login extends ipsCommand
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
		if($this->request['username'] && $this->request['password'])
		{
			if($member = $this->DB->buildAndFetch(array( 'select' => 'member_group_id, members_pass_hash, members_pass_salt', 'from' => 'members', 'where' => "name='{$this->request['username']}'" )))
			{
				if ($this->request['password'] == $member['members_pass_hash']  or  IPSMember::generateCompiledPasshash( $member['members_pass_salt'], md5($this->request['password']) ) == $member['members_pass_hash'])
				{
					if($member['member_group_id'] == 4)
					{
						echo FC_LOGIN_SUCCESS_ADMIN;
					}
					else
					{
						echo FC_LOGIN_SUCCESS;
					}
				}
				else
				{
					echo FC_LOGIN_ERROR_PASSWD;
				}
			}
			else
			{
				echo FC_LOGIN_ERROR_NOUSERID;
			}
		}
		else
		{
			echo FC_LOGIN_ERROR;
		}
	
	}

}
