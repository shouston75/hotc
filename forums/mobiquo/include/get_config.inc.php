<?php
/*======================================================================*\
|| #################################################################### ||
|| # Copyright &copy;2009 Quoord Systems Ltd. All Rights Reserved.    # ||
|| # This file may not be redistributed in whole or significant part. # ||
|| # This file is part of the Tapatalk package and should not be used # ||
|| # and distributed for any other purpose that is not approved by    # ||
|| # Quoord Systems Ltd.                                              # ||
|| # http://www.tapatalk.com | http://www.tapatalk.com/license.html   # ||
|| #################################################################### ||
\*======================================================================*/
defined('IN_MOBIQUO') or exit;

if ($settings['force_login'] == 1) {
    $mobiquo_config['guest_okay'] = 0;
}

if ($settings['board_offline'] == 1) {
    $mobiquo_config['is_open'] = 0;
}

if (version_compare($app_version, '3.2.0', '<'))
{
    unset($mobiquo_config['conversation']);
    unset($mobiquo_config['subscribe_topic_mode']);
    unset($mobiquo_config['subscribe_forum_mode']);
    unset($mobiquo_config['mod_approve']);
    unset($mobiquo_config['mod_delete']);
    unset($mobiquo_config['advanced_delete']);
    unset($mobiquo_config['advanced_search']);
    unset($mobiquo_config['register']);
}