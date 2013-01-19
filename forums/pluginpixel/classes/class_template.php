<?php
/*  --------------------------------------------------------
    jvbPlugin multisite Edition
    (c) 2004-2008 BBpixel.com
	--------------------------------------------------------
    syncpixel: Template class
    $revision: 080514100

    Written by Koudanshi
	--------------------------------------------------------
*/


class tplPixel {

	var $_meta;
	var $_title;
	var $_content;

	/**
	 * Out put the content of a page
	 *
	 */
	function output()
	{
		//Header
		$head = <<<HTML
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html dir="ltr" lang="en">
<head>
	<!-- no cache headers -->
	<meta http-equiv="Pragma" content="no-cache" />
	<meta http-equiv="Expires" content="-1" />
	<meta http-equiv="Cache-Control" content="no-cache" />
	<!-- end no cache headers -->
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	{$this->_meta}
	<link href="../style.css" rel="stylesheet" type="text/css" />
	<title>BBPixel Synchronization :: {$this->_title} </title>
</head>
HTML;

		//Footer
		$curYear = date("Y", time());
		$footer = "
	<div id='footer'>
		".PRODUCT_SHORT_NAME." &copy; 2003-$curYear <a href='http://bbpixel.com/' target='_blank'>BBPixel.com</a><br/>For ".CMS_NAME." ".CMS_VERSION." and ".BB_NAME." ".BB_VERSION." Only
	</div>
		";
		$html = <<<HTML
$head
<body>
<div id="wrapper">
	<div id="header">
		<div id="logo"></div>
		<div id="fade"></div>
	</div>
{$this->_content}
$footer
</div>
</body>
</html>
HTML;

		echo $html;
		exit();
	}


	/**
	 * Output a messsage content
	 *
	 * @param string $text
	 * @param string $title
	 */
	function printMsg($title=null, $msg=null) {

		$this->_content = "
	<div class='centerbox'>
		<div class='boxhead'></div>
			<div class='maintitle'>{$title}</div>
			<div class='maincontent'>{$msg}</div>

	</div>";
		$this->output();
	}


	/**
	 * Output a warning message content
	 *
	 * @param string $text
	 * @param string $title
	 */
	function printWarning($title=null, $msg=null) {

		$this->_content = "
	<div class='centerbox warning'>
		<div class='boxhead'></div>
			<div class='maintitle'>{$title}</div>
			<div class='maincontent'>{$msg}</div>

	</div>";
		$this->output();
	}


	function redirect($url=null, $title=null, $msg=null, $time=2) {

		$this->_title = $title;
		$this->_meta = "<meta http-equiv='Refresh' content='$time; url=$url' />";
		$this->printMsg($title, $msg);
	}

}

?>