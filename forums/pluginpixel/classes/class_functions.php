<?php
/*  --------------------------------------------------------
    jvbPlugin multisite Edition
    (c) 2004-2008 BBpixel.com
	--------------------------------------------------------
    syncpixel: Functions class
    $revision: 080514100

    Written by Koudanshi
	--------------------------------------------------------
*/

class funcPixel {

	var $_input;

	function convertArrayToBits(&$arry, $_FIELDNAMES, $unset = 0) {

		$bits = 0;
		foreach ($_FIELDNAMES AS $fieldname => $bitvalue) {
			if ($arry["$fieldname"] == 1) {
				$bits += $bitvalue;
			}
			if ($unset) {
				unset($arry["$fieldname"]);
			}
		}
		return $bits;
	}

	function cleanIncomming() {

		$this->cleanGPC($_GET);
		$this->cleanGPC($_POST);
		$this->cleanGPC($_COOKIE);
		$this->cleanGPC($_REQUEST);
		$input = $this->cleanGPCrecursively($_GET, array());
		$input = $this->cleanGPCrecursively($_POST, $input);
		$this->_input = $input;
		unset($input);
	}


	function cleanGPC(&$data, $iteration=0) {

		if ($iteration >= 10) {
			return $data;
		}
		if (count($data)) {
			foreach($data as $k => $v) {
				if (is_array($v)) {
					$this->cleanGPC($data[$k], $iteration++);
				} else {
					# Null byte characters
					$v = preg_replace( '/\\\0/' , '&#92;&#48;', $v );
					$v = preg_replace( '/\\x00/', '&#92;x&#48;&#48;', $v );
					$v = str_replace( '%00'     , '%&#48;&#48;', $v );
					# File traversal
					$v = str_replace( '../'    , '&#46;&#46;/', $v );
					$data[ $k ] = $v;
				}
			}
		}
	}

	function cleanGPCrecursively(&$data, $input=array(), $iteration = 0) {

		if ($iteration >= 10) {
			return $input;
		}
		if (count($data)) {
			foreach ($data as $k => $v)	{
				if (is_array($v)) {
					$input[ $k ] = $this->cleanGPCrecursively( $data[ $k ], array(), $iteration++ );
				} else {
					$k = $this->cleanKey( $k );
					$v = $this->cleanValue( $v );
					$input[ $k ] = $v;
				}
			}
		}
		return $input;
	}

    function cleanKey($key) {

    	if ($key == "") {
    		return "";
    	}
    	$key = htmlspecialchars(urldecode($key));
    	$key = str_replace( ".."           		, ""  , $key );
    	$key = preg_replace( "/\_\_(.+?)\_\_/"  , ""  , $key );
    	$key = preg_replace( "/^([\w\.\-\_]+)$/", "$1", $key );
    	return $key;
    }

    function cleanValue($val) {

    	if ($val == "") {
    		return "";
    	}
    	$val = str_replace( "&#032;"		, " "			  , $val );
    	$val = str_replace( "&#8238;"		, ''			  , $val );
    	$val = str_replace( "&"				, "&amp;"         , $val );
    	$val = str_replace( "<!--"			, "&#60;&#33;--"  , $val );
    	$val = str_replace( "-->"			, "--&#62;"       , $val );
    	$val = preg_replace( "/<script/i"	, "&#60;script"   , $val );
    	$val = str_replace( ">"				, "&gt;"          , $val );
    	$val = str_replace( "<"				, "&lt;"          , $val );
    	$val = str_replace( '"'				, "&quot;"        , $val );
    	$val = str_replace( "\n"			, "<br />"        , $val ); // Convert literal newlines
    	$val = str_replace( "$"				, "&#036;"        , $val );
    	$val = str_replace( "\r"			, ""              , $val ); // Remove literal carriage returns
    	$val = str_replace( "!"				, "&#33;"         , $val );
    	$val = str_replace( "'"				, "&#39;"         , $val ); // IMPORTANT: It helps to increase sql query safety.
    	return $val;
    }
}
?>