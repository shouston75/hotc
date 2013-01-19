<?php
require_once 'class/mobi_register_class.php';
$register = new mobi_register($registry);
$result = $register->doExecute($registry);
if($result == false)
{
	$result_text = "Regsiter fail , try again";
}

