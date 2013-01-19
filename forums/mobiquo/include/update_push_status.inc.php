<?php

defined('IN_MOBIQUO') or exit;

function update_push_status_func($xmlrpc_params)
{
    global $member;
    
    $decode_params = php_xmlrpc_decode($xmlrpc_params);
    
    $userid = $member['member_id'];
    $status = false;
    
    if (ipsRegistry::DB()->checkForTable( 'tapatalk_users' ))
    {
        if (empty($userid) && isset($decode_params[1]) && isset($decode_params[2]))
        {
            $username = to_local($decode_params[1]);
            $password = to_local($decode_params[2]);
            
            $member = IPSMember::load( IPSText::parseCleanValue( $username ), 'all', 'username' );
            
            if ( $member['member_id'] )
            {
                $result = IPSMember::authenticateMember( $member['member_id'], md5( IPSText::parseCleanValue( $password ) ) );
                
                if ( $result !== false )
                {
                    $userid = $member['member_id'];
                }
            }
        }
        
        if ($userid)
        {
            $pushItems = array('ann', 'conv', 'sub', 'like', 'quote', 'newtopic', 'tag');
            
            $update_params = array();
            if (isset($decode_params[0]['all']))
            {
                foreach($pushItems as $pushItem)
                    $update_params['`'.$pushItem.'`'] = intval($decode_params[0]['all']);
            }
            else
            {
                foreach($pushItems as $pushItem)
                {
                    if (isset($decode_params[0][$pushItem]))
                        $update_params['`'.$pushItem.'`'] = intval($decode_params[0][$pushItem]);
                }
            }
            
            if ($update_params)
            {
                ipsRegistry::DB()->update( 'tapatalk_users', $update_params, 'userid=' . $userid );
            }
            
            $status = true;
        }
    }
    
    return new xmlrpcresp(new xmlrpcval(array(
        'result' => new xmlrpcval($status, 'boolean'),
    ), 'struct'));
}