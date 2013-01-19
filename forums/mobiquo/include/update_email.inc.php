<?php
require_once 'class/mobi_usercp_class.php';
$usercp = new mobi_usercp($registry);
$result = $usercp->doExecute($registry);
$result_text = 'Email have been changed sucess , but you need to activate your account again , plase go to the '.$board_url.' to activate your account.';