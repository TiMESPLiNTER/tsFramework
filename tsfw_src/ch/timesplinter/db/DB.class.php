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
 * @change 2013-05-28 Possibility to use listeners to react on events like select, update, insert, execute and prepare
 */
abstract class DB extends PDO {
	const TYPE_MYSQL = 1;
	const TYPE_POSTGRESQL = 2;
	const TYPE_MSSQL = 3;

	protected $listeners;

	public function __construct ($dsn, $username = null, $passwd = null, $options = null) {
		parent::__construct($dsn, $username, $passwd, $options);

		$this->listeners = new ArrayObject();
	}

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
	abstract public function selectAsObjects(PDOStatement $stmnt, $className, array $params = array());

	/**
	 * Returns the result as an array of anonymous objects
	 * @param PDOStatement $stmnt The prepared statement
	 * @param array $params The parameters for the prepared statement
	 * @return array
	 */
	abstract public function select(PDOStatement $stmnt, array $params = array());

	/**
	 * Inserts a prepared statement with the given parameters
	 * @param PDOStatement $stmnt The prepared statement
	 * @param array $params The paremeters for the prepared statement
	 * @return int ID of inserted row
	 */
	abstract public function insert(PDOStatement $stmnt, array $params = array());

	/**
	 * @param PDOStatement $stmnt
	 * @param array $params
	 * @return int Affected rows
	 */
	abstract public function update(PDOStatement $stmnt, array $params);

	/**
	 * @param PDOStatement $stmnt
	 * @param array $params
	 * @return int Affected rows
	 */
	abstract public function delete(PDOStatement $stmnt, array $params);

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
		//try {
		$old = setlocale(LC_NUMERIC, NULL);
		setlocale(LC_NUMERIC, 'us_US');

		$stmnt->execute();

		setlocale(LC_NUMERIC, $old);

		foreach($this->listeners as $l) {
			/** @var DBListener $l */
			$l->onExecute($this, $stmnt);
		}
		/*} catch(\PDOException $e) {
			throw new DBException($e->getMessage(), $e->getCode(), $stmnt->queryString, array());
		}*/
	}

	/**
	 * @param DBListener $listener The listener object to register
	 * @param string $name The name of the listener [optional]
	 */
	public function addListener(DBListener $listener, $name = null) {
		if($name !== null)
			$this->listeners->offsetSet($name, $listener);
		else
			$this->listeners->append($listener);
	}

	/**
	 * Removes the listener
	 * @param $name The name of the listener which should be removed
	 */
	public function removeListener($name) {
		$this->listeners->offsetUnset($name);
	}

	/**
	 * Removes all registered listeners at once
	 */
	public function removeAllListeners() {
		$this->listeners = new ArrayObject();
	}

	/**
	 * Creates a string like "?,?,?,..." for the number of array entries given
	 * @param $paramArr
	 * @return string
	 */
	public static function createInQuery($paramArr) {
		return implode(',', array_fill(0, count($paramArr), '?'));
	}
}

/* EOF */