<?php
require_once 'class/mobi_lostpass_class.php';
$lostpass = new mobi_lostpss($registry);
$result = $lostpass->doExecute($registry);
if($result === 'verified')
{
	$result = true;
	$verified = true;
	$result_text = '';
}
else
{
	$result_text = 'An email containing further activation instructions has been sent. This email should be received within the next 10 minutes (usually instantly).  ';
}

