<?php

/**
 *
 * @author Pascal Münst
 * @copyright Copyright (c) 2012, METANET AG
 * @version	1.0
 */
interface DB {
	const TYPE_MYSQL = 1;
	const TYPE_POSTGRESQL = 2;
	const TYPE_MSSQL = 3;
	
	/**
	 * Returns the SQL as a prepared statement
	 * @param String $sql The SQL for the prepared statement
	 * @return PDOStatement The prepared statement
	 */
	public function prepareStatement($sql);

	/**
	 * Returns the result as an array of $className objects
	 * @param PDOStatement $stmnt The prepared statement
	 * @param Array $params The parameters for the prepared statement
	 * @param String $className The mapped class name
	 * @return Array
	 */
	public function selectAsObjects($stmnt, $className, $params = array());

	/**
	 * Returns the result as an array of anonymous objects
	 * @param PDOStatement $stmnt The prepared statement
	 * @param ArrayObject $params The parameters for the prepared statement
	 * @return Array
	 */
	public function select($stmnt, $params = array());

	/**
	 * Inserts a prepared statement with the given parameters
	 * @param PDOStatement $stmnt The prepared statement
	 * @param ArrayObject $params The paremeters for the prepared statement
	 * @return int ID of inserted row
	 */
	public function insert($stmnt, $params = array());

	/**
	 * @param PDOStatement
	 * @param ArrayObject
	 * @return Integer Affected rows
	 */
	public function update($stmnt, $params);

	/**
	 * @param PDOStatement
	 * @param ArrayObject
	 * @return Integer Affected rows
	 */
	public function delete($stmnt, $params);

	/**
	 * Returns the DBConnect object with the current used connection
	 * @return DBConnect
	 */
	public function getDbConnect();
}

?>
