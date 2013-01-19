<?php
/*  --------------------------------------------------------
    jvbPlugin multisite Edition
    (c) 2004-2008 BBpixel.com
	--------------------------------------------------------
    syncpixel: database class
    $revision: 080514100

    Written by Koudanshi
	--------------------------------------------------------
*/

class dbPixel {

     var $_db = array (
				     	'port' 		=> 3306,
				     	'pconnect' 	=> 0

     			);
     var $_connID;
     var $_sql;


    /**
     * Connect to the database
     *
     * @return unknown
     */
	function connect() {

		if ($this->_db['pconnect']) {
			$this->_connID = mysql_pconnect("{$this->_db['host']}:{$this->_db['port']}", $this->_db['user'], $this->_db['pwd']);
		} else {
			$this->_connID = mysql_connect("{$this->_db['host']}:{$this->_db['port']}", $this->_db['user'], $this->_db['pwd']);
		}

		if (!$this->_connID) {
			$this->error();
			return false;
		}

		if (!empty($this->_db['charset'])) {
			if (function_exists('mysql_set_charset')) {
				mysql_set_charset($this->_db['charset']);
			} else {
				$this->_sql = "SET NAMES {$this->_db['charset']}";
				$this->executeQuery(true);
			}
		}

        if (!mysql_select_db($this->_db['name'], $this->_connID)){
        	$this->error();
        	return false;
        }

        return true;
    }

    /**
     * Execute a query
     *
     * @param boolean Whether or not to run this query buffered
     */
    function executeQuery($buffer=true) {

    	if ($buffer) {
    		$result = mysql_query($this->_sql, $this->_connID);
    	} else {
    		$result = mysql_unbuffered_query($this->_sql, $this->_connID);
    	}

		if ($result) {
			$this->_sql = "";
			return $result;
		} else {
			//$this->_sql = "";
			$this->error();
		}
    }


    /**
     * Manual query
     *
     * @param string $sql
     * @param boolean $buffer
     * @return resource
     */
    function query($sql=null, $buffer=true) {

		$this->_sql = $sql;
		return $this->executeQuery($buffer);
    }


    /**
     * Escapes a string to make it safe to be inserted into an SQL query
     *
     * string	The string to be escaped
     */
    function escapeString($string=null) {
    	return @mysql_real_escape_string($string);
    }


    /**
     * Fetches a row from a query result and returns the values from that row as an array with numeric keys
     *
     * @param string $rows : query ID
     * @return resource
     */
    function fetchRow($rows=null) {
		return @mysql_fetch_row($rows);
    }



   /**
     * Fetches a row from a query result and returns the values from that row as an array
     *
     * @param string $rows : query ID
     * @return resource
     */
    function fetchArray($rows=null) {
		return @mysql_fetch_array($rows);
    }


    /**
     * get number rows of query result
     *
     * @param string $rows : query ID
     * @return numbers of result
     */
    function numRows($rows=null) {
        return @mysql_num_rows($rows);
    }


    /**
     * Get insert ID from previous query
     *
     * @return number of ID
     */
    function insertID() {
        return @mysql_insert_id($this->_connID);
    }


	/**
	 * Close an connection
	 *
	 * @return unknown
	 */
    function close() {
    	if ( $this->_connID ) {
        	return @mysql_close( $this->_connID );
        }
    }


 	/**
 	 * Return error
 	 *
 	 */
    function error()
    {
    	$error .= "\n\nmySQL error: ".mysql_error()."\n";
    	$error .= "Date: ".date("l dS of F Y h:i:s A");

    	$out = "<html><head><title>Database Error</title>
    		   <style>P,BODY{ font-family:arial,sans-serif; font-size:11px; }</style></head><body>
    		   &nbsp;<br><br><blockquote><b>There appears to be an error with the database.</b><br>
    		   You can try to refresh the page by clicking <a href=\"javascript:window.location=window.location;\">here</a>.
    		   <br><br><b>Error Returned</b><br>
    		   <form name='mysql'><textarea rows=\"15\" cols=\"60\">query: $this->_sql \n".htmlspecialchars($error)."</textarea></form><br>We apologise for any inconvenience</blockquote></body></html>";
        echo($out);
        exit;
    }

} // end class


?>