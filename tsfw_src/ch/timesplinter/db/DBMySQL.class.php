<?php

namespace ch\timesplinter\db;

use ch\timesplinter\db\DB;
use ch\timesplinter\db\DBConnect;
use \PDO;
use \PDOStatement;

/**
 * Description of DBMySQL
 * 
 * Changes:
 * - 2012-10-01 pam Typesafe select, update, insert methods -> version 1.1
 *
 * @author Pascal Münst <dev@timesplinter.ch>
 * @copyright Copyright (c) 2012, TiMESPLiNTER
 * @version 1.1.2
 * 
 * @change 2012-10-10 Uses now the new abstract class 'DB' instead of the interface and makes use of the execute()-method in it (pam)
 * @change 2012-10-23 Method prepare() from PDO class overridden. Throws now a DBException. (pam)
 */
class DBMySQL extends DB {

	private $dbConnect;

	public function __construct(DBConnect $dbConnect) {
		$this->dbConnect = $dbConnect;

		try {
			parent::__construct(
				'mysql:host=' . $dbConnect->getHost() . ';dbname=' . $dbConnect->getDatabase() . ';charset=' . $dbConnect->getCharset()
				, $dbConnect->getUsername()
				, $dbConnect->getPassword()
			);

			$this->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

			$this->query("SET NAMES '" . $dbConnect->getCharset() . "'");
			$this->query("SET CHARSET '" . $dbConnect->getCharset() . "'");
		} catch(PDOException $e) {
			throw new DBException('PDO could not connect to the database ' . $dbConnect->getDatabase() . '@' . $dbConnect->getHost(), 201);
		}
	}

	public function prepareStatement($sql) {
		return self::prepare($sql);
	}
	
	public function prepare($sql, $driver_options = array()) {
		try {
			return parent::prepare($sql, $driver_options);
		} catch(PDOException $e) {
			throw new DBException('PDO could not prepare query: ' . $e->getMessage(), 203, $sql);
		}
	}

	public function select(PDOStatement $stmnt, array $params = array()) {
		$paramCount = count($params);

		try {
			// Bind params to statement
			for($i = 0; $i < $paramCount; $i++) {
				$paramType = (is_int($params[$i])) ? PDO::PARAM_INT : PDO::PARAM_STR;
				$stmnt->bindParam(($i + 1), $params[$i], $paramType);
			}

			parent::execute($stmnt);

			return $stmnt->fetchAll(PDO::FETCH_OBJ);
		} catch(PDOException $e) {
			throw new DBException('PDO could not execute select query: ' . $e->getMessage(), 202, $stmnt->queryString, $params);
		}
	}

// selectAsObjects($stmnt,$params,$className)
	public function selectAsObjects(PDOStatement $stmnt, $className, array $params = array()) {
		$paramCount = count($params);

		try {
			// Bind params to statement
			for($i = 0; $i < $paramCount; $i++) {
				$paramType = (is_int($params[$i])) ? PDO::PARAM_INT : PDO::PARAM_STR;
				$stmnt->bindParam(($i + 1), $params[$i], $paramType);
			}

			parent::execute($stmnt);

			return $stmnt->fetchAll(PDO::FETCH_CLASS, $className);
		} catch(PDOException $e) {
			throw new DBException('PDO could not execute select query: ' . $e->getMessage(), 202, $stmnt->queryString, $params);
		}
	}

	public function insert(PDOStatement $stmnt, array $params = array()) {
		$paramCount = count($params);

		try {
			// Bind params to statement
			for($i = 0; $i < $paramCount; $i++) {
				$paramType = (is_int($params[$i])) ? PDO::PARAM_INT : PDO::PARAM_STR;
				$stmnt->bindParam(($i + 1), $params[$i], $paramType);
			}

			parent::execute($stmnt);

			return $this->lastInsertId();
		} catch(PDOException $e) {
			throw new DBException('PDO could not execute insert query: ' . $e->getMessage(), 204, $stmnt->queryString, $params);
		}
	}

	public function update(PDOStatement $stmnt, array $params = array()) {
		$paramCount = count($params);

		try {
			// Bind params to statement
			for($i = 0; $i < $paramCount; $i++) {
				$paramType = (is_int($params[$i])) ? PDO::PARAM_INT : PDO::PARAM_STR;
				$stmnt->bindParam(($i + 1), $params[$i], $paramType);
			}
			
			parent::execute($stmnt);

			return $stmnt->rowCount();
		} catch(PDOException $e) {
			throw new DBException('PDO could not execute update query: ' . $e->getMessage(), 205, $stmnt->queryString, $params);
		}
	}

	public function delete(PDOStatement $stmnt, array $params) {
		$paramCount = count($params);

		try {
			// Bind params to statement
			for($i = 0; $i < $paramCount; $i++) {
				$paramType = (is_int($params[$i])) ? PDO::PARAM_INT : PDO::PARAM_STR;
				$stmnt->bindParam(($i + 1), $params[$i], $paramType);
			}

			parent::execute($stmnt);

			return $stmnt->rowCount();
		} catch(PDOException $e) {
			throw new DBException('PDO could not execute delete query: ' . $e->getMessage(), 205, $stmnt->queryString, $params);
		}
	}

	public function getDbConnect() {
		return $this->dbConnect;
	}
}

/* EOF */