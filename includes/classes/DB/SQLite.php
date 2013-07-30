<?php
/** ************************************************************************************************
* Class  : Sql			- SQLite
* Author : Binny V A(binnyva@gmail.com | http://www.bin-co.com/)
* Version: 0.00.A Beta
* Date   : 9 April, 2007
***************************************************************************************************
* Creates a Database abstration layer - using the most commonly used functions.
* 										For SQLite
* get* functions require a query as the argument
* fetch* functions requrire a sql resource as the argument.
***************************************************************************************************/
class Sql {
	//All Variables - Public
	var $mode = 'd'; ///Mode - p = Production, d = Development and t = Testing
	
	//Private Variables
	var $_row  = "";
	var $_list = array();
	var $_db_connection;
	var $_resource;

	/**
	 * Constructor
	 * Connects to the database with the details given as the argument. Exits with error if there are problems.
	 * Arguments :	$db_host - The database host server - eg. localhost
	 *				$db_user - Database user - eg. root
	 *				$db_password - The password for the given user - eg. ''
	 *				$db_name - The database that must be used.
	 */
	function Sql($db) {
		if (!$this->_db_connection = sqlite_open($db, 0666, $sqlite_error)) {
			die($sqliteerror);
		}
	}
	/**
	 * Disconnects from the currently open database connection
	 */
	function disconnect() {
		sqlite_close($this->_db_connection);
	}

	/**
	 * Executes the given query and returns the resource. If an error has occured, passes the error data to $Sql->_error()
	 * Argument : $query - SQL query
	 * Return   : The SQL Resource of the given query
	 */
	function getSql($query) {
		$this->_resource = sqlite_query($this->_db_connection,$query);
		if(!$this->_resource) {
			$this->_error($query);
			return false;
		}

		return $this->_resource;
	}

	/**
	 * Returns the first row of the result as an associative array - after stripslashing it
	 * Argument : $query - SQL query
	 * Return   : First row in the query result - as an associative array.
	 */ 
	function getAssoc($query) {
		$result = $this->getSql($query);
		$row = sqlite_fetch_array($result,SQLITE_ASSOC);
		return $this->_stripSlashes($row);
	}

	/**
	 * Returns the first row of the result as a numeric array - or list
	 * Argument : $query - SQL Query
	 * Return   : First row in the query result - as an numeric array.
	 */
	function getList($query) {
		$result = $this->getSql($query);
		$row = sqlite_fetch_array($result,SQLITE_NUM);
		return $this->_stripSlashes($row);
	}
	
	/**
	 * Runs a query and returns the first column of the first row
	 * Argument : $query - SQL Query
	 * Return   : The first column of the first row in the query result
	 */
	function getOne($query) {
		$result = $this->getSql($query);
		$res = sqlite_fetch_array($result,SQLITE_NUM);
		if(!$res) return '';
		return stripslashes($res[0]);
	}

	/**
	 * Runs a query and returns all the data as an array
 	 * Argument : $query - SQL Query
 	 * Return : All the data is the result
	 */
	function getAll($query) {
		$result = $this -> getSql($query);

		$arr = sqlite_fetch_all($result,SQLITE_ASSOC);
		return $this->_stripSlashes($arr);
	}

	/**
	 * Runs a query and returns the data from a single column
 	 * Argument : $query - SQL Query
 	 * Return : All the values in the given column - as a list
	 */
	function getCol($query) {
		$result = $this -> getSql($query);

		$arr = array();
		while ($row = sqlite_fetch_array($result,SQLITE_NUM)) {
			array_push($arr,stripslashes($row[0]));
		}
		return $arr;
	}
	
	/**
	 * Runs a query and returns all the data as an array - with the first field as the key and the second as the value
 	 * Argument : $query - SQL Query
 	 * Return : All the data is the result - with the first field as the key and the second as the value
	 */
	function getById($query) {
		$result = $this -> getSql($query);

		$arr = array();
		while ($row = sqlite_fetch_array($result,SQLITE_NUM)) {
			if(count($row) == 2)
				$arr[$row[0]] = stripslashes($row[1]);
			else 
				$arr[] = $row;
		}
		return $arr;
	}

	/**
	 * Function : runQuery()
	 * Arguments: $query - MySQL query
	 * Runs a query in the currently open MySQL connection and gets the number of affected rows. Use
	 *	this while running update, insert etc. query that don't need to 'fetch' the results.
	 */ 
	function execQuery($query) {
		$this -> getSql($query);
	}	
	
	/////////////////////////////////// Editing Functions /////////////////////////////////
	/**
	 * Builds and executes an INSERT command based on the given data.
	 * Arguments :	$table - The name of the table
	 *				$fields- The names all the fields that should be inserted - as an array. Just give $_REQUEST as the data for this argument. This will not be stripslashed.
	 *				$values- All the values that must be inserted as an associative array. The key of the array must be the field name to which it is inserted.
	 * Example : insertFields('user',array('name','job','phone','email'),$_REQUEST);
	 */
	function insertFields($table,$fields,$values = array()) {
		if(!$values) {
			$values = ($GLOBALS['QUERY']) ? $GLOBALS['QUERY'] : $_REQUEST; //The $QUERY is Binny Specific
		}
		$insert_query = "INSERT INTO $table(".join(',',$fields).") VALUES('";
		foreach($fields as $fld) {
			$insert_query .= $values[$fld] . "','";
		}
		$insert_query = substr($insert_query,0,-3); //Remove the last three chars - ie. "','"
		$insert_query .= "')";

		$this->getSql($insert_query);
		
		return $this->fetchInsertId();
	}

	/** 
	 * Builds and executes an UPDATE command based on the argument
	 * Arguments :	$table	- Table Name
	 *				$fields	- The names of all the fields that should be updated - as an array.
	 *				$values	- All the values that should be inserted - must be given as an associative array. Just give $_REQUEST as the data for this argument. This will not be stripslashed.
	 *				$where 	- The where condition that will decide where to do the update.
	 * Example : updateFields('user',array('name','job','phone','email'),$_REQUEST,"WHERE user_id=12");
	 */
	function updateFields($table,$fields,$values,$where) {
		$update_query = "UPDATE $table SET ";
		foreach($fields as $fld) {
			$update_query .= $fld . "='" . $values[$fld] . "',";
		}
		$update_query = substr($update_query,0,-1);
		
		if($where) {
			if(strpos($where,"where ") !== false)
				$update_query .= " $where";
			else
				$update_query .= " WHERE $where";
		}

		$this->getSql($update_query);
		
		return $this->fetchAffectedRows();
	}
	
	/**
	 * Builds and executes an INSERT command - by taking a table name and an array holding all the data in an associative array - the key being the field name and the value being the data.
	 * Arugments :	$table	- Name of the table
	 * 				$data	- An array holding all the data in an associative array - the key being the field name and the value being the data.
	 * Example : <pre>insert("Data",array(
	 *				'name' => 'Binny',
	 *				'age' => 12,
	 *				'year' => 2007,
	 *				'something' => 'Xrats'
	 *			));</pre>
	 */
	function insert($table,$data) {
		if(!$data or !$table) return;
	
		$fields = array_keys($data);
		$values = array_values($data);
		$insert_query = "INSERT INTO $table(".join(',',$fields).") VALUES('";
		$insert_query .= implode("','",$values);
		$insert_query .= "')";
	
		$this->getSql($insert_query);
		
		return $this->fetchInsertId();
	}

	/**
	 * Builds and executes an UPDATE command - by taking a table name and an array holding all the data in an associative array - the key being the field name and the value being the data.
	 * Arugments :	$table	- Name of the table
	 * 				$data	- An array holding all the data in an associative array - the key being the field name and the value being the data.
	 *				$where	- The WHERE clause should be given here.
	 * Example : <pre>update("Data",array(
	 *				'name' => 'Binny',
	 *				'age' => 12,
	 *				'year' => 2007,
	 *				'something' => 'Xrats'
	 *			),'id=14');</pre>
	 */
	function update($table,$data,$where) {
		if(!$data or !$table) return;
	
		$update_query = "UPDATE $table SET ";
		$update_fields = array();
		foreach($data as $field=>$value) {
			$update_fields[] = "$field='$value'";
		}
		$update_query .= implode(',',$update_fields);
	
		if($where) {
			if(strpos(strtolower($where),"where ") !== false)
				$update_query .= " $where";
			else
				$update_query .= " WHERE $where";
		}

		$this->getSql($update_query);
	}

	/**
	 * To emulate the functioning of prepare and execute command - if we are on a PHP 5/MySQL 5 system, we should NOT use this
	 * Arguments :	$query - The SQL Query to be executed.
	 *				Data that should be used in the query
	 * Example : prepExec("INSERT INTO rats(name,text) VALUES(?,?)","Name",23);
	 */
	function prepExec() {
		$args = func_get_args();
		$qry = $args[0];
		$datas = array_slice($args,1);
		
		//If there is only one argument and it is an array, set it as the data provider.
		if(count($datas) == 1 and is_array($datas)) {
			$datas = $datas[0];
		}
		
		//Go thru each available value and insert it at the position of the '?'
		foreach($datas as $value) {
			$pos = strpos($qry,'?');
			if($pos === false) break;
			$value = sqlite_escape_string($value);

			if(is_string($value)) {
				$value = "'".$value."'";
			}
			$qry = substr($qry,0,$pos) . $value . substr($qry,$pos+1);
		}
		
		$this->getSql($qry);
	}

	///////////////////////////////////// Other Functions /////////////////////////////////////
	/**
	 * Handles the SQL errors depending on what mode we are in.
	 * Argument : $query - The SQL Query in which the error occured.
	 */
	function _error($query) {
		$error_message = "SQLite Error : <code>" . sqlite_error_string(sqlite_last_error($this->_db_connection)) . "<code><br /><u>Query...</u><code>" . $query . "</code>";
		if($this->mode == 'd') {
			die($error_message);
		} elseif($this->mode == 't') {
			print($error_message);
		}
	}
	
	/**
	 * Do a stripslash on every element of the array and return it.
	 * Arguments: $arr - The array that should be stripslashed
	 * Return	: The array given in the argument - stripslashed
	 */
	function _stripSlashes($arr) {
		if(is_array($arr)) {
			foreach($arr as $key=>$value) {
				$arr[$key] = $this->_stripSlashes($value);// :RECURSION:
			}
		} else {
			$arr = stripslashes($arr);
		}
		return $arr;
	}
	
	/*****************************************************************************/
	function fetchAssoc($resource = false) {
		if(!$resource) $resource = $this->_resource;
		return sqlite_fetch_array($resource,SQLITE_ASSOC);
	}
	function fetchRow($resource = false) {
		if(!$resource) $resource = $this->_resource;
		return sqlite_fetch_array($resource,SQLITE_NUM);
	}
	function fetchNumRows($resource = false) {
		if(!$resource) $resource = $this->_resource;
		return sqlite_num_rows($resource);
	}
	function fetchInsertId() {
		return sqlite_last_insert_rowid();
	}
	function fetchAffectedRows() {
		return 1;//sqlite_affected_rows(); - Is there something similar in SQLite
	}
}
