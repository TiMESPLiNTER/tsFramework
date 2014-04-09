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
	const TYPE_MYSQL = 'mysql';
	const TYPE_POSTGRESQL = 2;
	const TYPE_MSSQL = 3;

	protected $listeners;
	protected $muteListeners;
	protected $transactionName;
	protected $dbConnect;

	public function __construct ($dsn, $username = null, $passwd = null, $options = null) {
		parent::__construct($dsn, $username, $passwd, $options);

		$this->listeners = new ArrayObject();
		$this->muteListeners = false;
		$this->transactionName = null;
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
	 * @throws DBException
	 */
	public function execute(PDOStatement $stmnt) {
		try {
			$old = setlocale(LC_NUMERIC, NULL);
			setlocale(LC_NUMERIC, 'us_US');

			$stmnt->execute();

			setlocale(LC_NUMERIC, $old);

			$this->triggerListeners('onExecute', array($this, $stmnt));
		} catch(\PDOException $e) {
			throw new DBException($e->errorInfo[2], $e->errorInfo[1], $stmnt->queryString);
		}
	}

	public function beginTransaction($transactionName = null) {
		$this->transactionName = $transactionName;

		try {
			$this->triggerListeners('beforeBeginTransaction', array($this));

			parent::beginTransaction();
		} catch(PDOException $e) {
			throw new DBException('PDO could not begin transaction: ' . $e->getMessage(), $e->getCode());
		}
	}

	/**
	 * (PHP 5 &gt;= 5.1.0, PECL pdo &gt;= 0.1.0)<br/>
	 * Commits a transaction
	 * @link http://php.net/manual/en/pdo.commit.php
	 * @return bool <b>TRUE</b> on success or <b>FALSE</b> on failure.
	 */
	public function commit() {
		try {
			parent::commit();

			$this->triggerListeners('afterCommit', array($this));

			$this->transactionName = null;
		} catch(PDOException $e) {
			throw new DBException('PDO could not commit transaction: ' . $e->getMessage(), $e->getCode());
		}
	}

	/**
	 * Adds a DBListener to listen on some events of the DB class
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
	 * @param string $name The name of the listener which should be removed
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
	 * Returns the name of the current transaction or null if none given
	 * @return string|null
	 */
	public function getTransactionName() {
		return $this->transactionName;
	}

	/**
	 * Sets the listeners to mute so they'll be not triggered until mute is set to false again
	 * @param boolean $mute Mute = true, unmute = false
	 */
	public function setListenersMute($mute) {
		$this->muteListeners = $mute;
	}

	/**
	 * Returns the state of the listeners if they're mute or not
	 * @return bool The mute state of the listeners
	 */
	public function areListenersMute() {
		return $this->muteListeners;
	}

	/**
	 * Returns all the current registered listeners
	 * @return ArrayObject List of registered listeners
	 */
	public function getListeners() {
		return $this->listeners;
	}

	/**
	 * Triggers a call of a specific method from all registered listener classes if the listeners are not set to mute
	 * @param $method The listener method that should be called
	 * @param array $params The parameters for the listener method
	 */
	protected function triggerListeners($method, array $params = array()) {
		if($this->muteListeners === true)
			return;

		// Mute all the listeners cause we don't want listeners called in listeners
		// If we do so: unmute the listeners in the listener method itself
		$this->muteListeners = true;

		foreach($this->listeners as $l) {
			/** @var DBListener $l */
			call_user_func_array(array($l, $method), $params);
		}

		// Unmute listeners cause from now on we're not in a listener method anymore
		$this->muteListeners = false;
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