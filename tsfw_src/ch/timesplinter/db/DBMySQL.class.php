<?php

namespace ch\timesplinter\db;

use ch\timesplinter\db\DB;
use ch\timesplinter\db\DBConnect;
use \PDO;
use \PDOStatement;
use \PDOException;

/**
 * @author Pascal Münst <dev@timesplinter.ch>
 * @copyright Copyright (c) 2012, TiMESPLiNTER Webdevelopment
 */
class DBMySQL extends DB
{
	/**
	 * @param DBConnect $dbConnect
	 */
	public function __construct(DBConnect $dbConnect)
	{
		$this->dbConnect = $dbConnect;

		try {
			parent::__construct(
				'mysql:host=' . $dbConnect->getHost() . ';dbname=' . $dbConnect->getDatabase() . (($dbConnect->getCharset() !== null)?';charset=' . $dbConnect->getCharset():null)
				, $dbConnect->getUsername()
				, $dbConnect->getPassword()
			);

			$this->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

			if(($charset = $dbConnect->getCharset()) !== null) {
				$this->query("SET NAMES '" . $charset . "'");
				$this->query("SET CHARSET '" . $charset . "'");
			}

			$this->triggerListeners('onConnect', array($this, $this->dbConnect));
		} catch(PDOException $e) {
			$dbException = new DBException('PDO could not connect to the database ' . $dbConnect->getDatabase() . '@' . $dbConnect->getHost() . ': ' . $e->getMessage(), $e->getCode());
			$dbException->errorInfo = $e->errorInfo;
			
			throw $e;
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function prepare($sql, $driver_options = array())
	{
		try {
			$stmnt = parent::prepare($sql, $driver_options);

			$this->triggerListeners('onPrepare', array($this, $stmnt));

			return $stmnt;
		} catch(PDOException $e) {
			$dbException = new DBQueryException('PDO could not prepare query: ' . $e->getMessage(), $e->getCode(), $sql);
			$dbException->errorInfo = $e->errorInfo;
			
			throw $e;
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function select(PDOStatement $stmnt, array $params = array())
	{
		$paramCount = count($params);

		try {
			// Bind params to statement
			for($i = 0; $i < $paramCount; $i++) {
				$paramType = (is_int($params[$i])) ? PDO::PARAM_INT : PDO::PARAM_STR;
				$stmnt->bindParam(($i + 1), $params[$i], $paramType);
			}

			$this->execute($stmnt);

			$this->triggerListeners('onSelect', array($this, $stmnt, $params));

			return $stmnt->fetchAll(PDO::FETCH_OBJ);
		} catch(PDOException $e) {
			$dbException = new DBQueryException('PDO could not execute select query: ' . $e->getMessage(), $e->errorInfo[1], $stmnt->queryString, $params);
			$dbException->errorInfo = $e->errorInfo;

			throw $e;
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function selectAsObjects(PDOStatement $stmnt, $className, array $params = null)
	{
		$paramCount = count($params);

		try {
			// Bind params to statement
			for($i = 0; $i < $paramCount; $i++) {
				$paramType = (is_int($params[$i])) ? PDO::PARAM_INT : PDO::PARAM_STR;
				$stmnt->bindParam(($i + 1), $params[$i], $paramType);
			}

			$this->execute($stmnt);

			$this->triggerListeners('onSelect', array($this, $stmnt, $params));

			return $stmnt->fetchAll(PDO::FETCH_CLASS, $className);
		} catch(PDOException $e) {
			$dbException = new DBQueryException('PDO could not execute select query: ' . $e->getMessage(), $e->getCode(), $stmnt->queryString, $params);
			$dbException->errorInfo = $e->errorInfo;

			throw $e;
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function insert(PDOStatement $stmnt, array $params = array())
	{
		$paramCount = count($params);

		try {
			$this->triggerListeners('beforeMutation', array($this, $stmnt, $params, DBListener::QUERY_TYPE_INSERT));

			// Bind params to statement
			for($i = 0; $i < $paramCount; $i++) {
				$paramType = (is_int($params[$i])) ? PDO::PARAM_INT : PDO::PARAM_STR;
				$stmnt->bindParam(($i + 1), $params[$i], $paramType);
			}

			$this->execute($stmnt);

			$this->triggerListeners('afterMutation', array($this, $stmnt, $params, DBListener::QUERY_TYPE_INSERT));

			return $this->lastInsertId();
		} catch(PDOException $e) {
			$dbException = new DBQueryException('PDO could not execute insert query: ' . $e->getMessage(), $e->errorInfo[1], $stmnt->queryString, $params);
			$dbException->errorInfo = $e->errorInfo;
			
			throw $e;
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function update(PDOStatement $stmnt, array $params = array())
	{
		$paramCount = count($params);

		try {
			$this->triggerListeners('beforeMutation', array($this, $stmnt, $params, DBListener::QUERY_TYPE_UPDATE));

			// Bind params to statement
			for($i = 0; $i < $paramCount; $i++) {
				$paramType = (is_int($params[$i])) ? PDO::PARAM_INT : PDO::PARAM_STR;
				$stmnt->bindParam(($i + 1), $params[$i], $paramType);
			}
			
			$this->execute($stmnt);

			$this->triggerListeners('afterMutation', array($this, $stmnt, $params, DBListener::QUERY_TYPE_UPDATE));

			return $stmnt->rowCount();
		} catch(PDOException $e) {
			$dbException = new DBException('PDO could not execute update query: ' . $e->getMessage(), $e->errorInfo[1], $stmnt->queryString, $params);
			$dbException->errorInfo = $e->errorInfo;

			throw $e;
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function delete(PDOStatement $stmnt, array $params)
	{
		$paramCount = count($params);

		try {
			$this->triggerListeners('beforeMutation', array($this, $stmnt, $params, DBListener::QUERY_TYPE_DELETE));

			// Bind params to statement
			for($i = 0; $i < $paramCount; $i++) {
				$paramType = (is_int($params[$i])) ? PDO::PARAM_INT : PDO::PARAM_STR;
				$stmnt->bindParam(($i + 1), $params[$i], $paramType);
			}

			$this->execute($stmnt);

			$this->triggerListeners('afterMutation', array($this, $stmnt, $params, DBListener::QUERY_TYPE_DELETE));

			return $stmnt->rowCount();
		} catch(PDOException $e) {
			$dbException = new DBException('PDO could not execute delete query: ' . $e->getMessage(), $e->getCode(), $stmnt->queryString, $params);
			$dbException->errorInfo = $e->errorInfo;

			throw $e;
		}
	}

	/**
	 * @return DBConnect
	 */
	public function getDbConnect()
	{
		return $this->dbConnect;
	}
}

/* EOF */