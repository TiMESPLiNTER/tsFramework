<?php

namespace ch\timesplinter\db;

use \PDO;
use \PDOStatement;
use \ArrayObject;

/**
 *
 * @author Pascal Münst <dev@timesplinter.ch>
 * @copyright Copyright (c) 2013, TiMESPLiNTER
 * @version	2.0.1
 * 
 * @change 2012-10-10 Changed from interface to abstract class, new method 'execute' (pam)
 * @change 2012-10-23 Method prepareStatement() is deprecated now (pam)
 */
abstract class DB extends PDO {
	const TYPE_MYSQL = 1;
	const TYPE_POSTGRESQL = 2;
	const TYPE_MSSQL = 3;
	
	/**
	 * Returns the SQL as a prepared statement
	 * @deprecated since version 2.0.1
	 * @param String $sql The SQL for the prepared statement
	 * @return PDOStatement The prepared statement
	 */
	abstract public function prepareStatement($sql);
	
	/**
	 * Returns the result as an array of $className objects
	 * @param PDOStatement $stmnt The prepared statement
	 * @param Array $params The parameters for the prepared statement
	 * @param String $className The mapped class name
	 * @return array
	 */
	abstract public function selectAsObjects(PDOStatement $stmnt, $className, $params = array());

	/**
	 * Returns the result as an array of anonymous objects
	 * @param PDOStatement $stmnt The prepared statement
	 * @param ArrayObject $params The parameters for the prepared statement
	 * @return array
	 */
	abstract public function select(PDOStatement $stmnt, ArrayObject $params = array());

	/**
	 * Inserts a prepared statement with the given parameters
	 * @param PDOStatement $stmnt The prepared statement
	 * @param ArrayObject $params The paremeters for the prepared statement
	 * @return int ID of inserted row
	 */
	abstract public function insert(PDOStatement $stmnt, ArrayObject $params = array());

	/**
	 * @param PDOStatement $stmnt
	 * @param ArrayObject $params
	 * @return int Affected rows
	 */
	abstract public function update(PDOStatement $stmnt, ArrayObject $params);

	/**
	 * @param PDOStatement $stmnt
	 * @param ArrayObject $params
	 * @return int Affected rows
	 */
	abstract public function delete(PDOStatement $stmnt, $params);

	/**
	 * Returns the DBConnect object with the current used connection
	 * @return DBConnect
	 */
	abstract public function getDbConnect();
	
	/**
	 * This method does the same as execute() of a PDOStatement but it fixes a 
	 * known issue of php that e.x. floats in some locale-settings contains a 
	 * comma instead of a point as decimal seperator. It sets LC_NUMERIC to 
	 * us_US, executes the query and sets the LC_NUMERIC back to the old locale.
	 * @param PDOStatement $stmnt The statement to execute
	 */
	public function execute(PDOStatement $stmnt) {
		$old = setlocale(LC_NUMERIC, NULL);
		setlocale(LC_NUMERIC, 'us_US');

		$stmnt->execute();
		
		setlocale(LC_NUMERIC, $old);
	}
}

/* EOF */