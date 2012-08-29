<?php

/**
 * Description of DBMySQL
 *
 *
 * @author				entwicklung@metanet.ch
 * @copyright	Copyright (c) 2012, METANET AG, www.metanet.ch
 * @version			1
 */
class DBMySQL extends PDO implements DB {

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
		try {
			return $this->prepare($sql);
		} catch(PDOException $e) {
			throw new DBException('PDO could not prepare query: ' . $e->getMessage(), 203, $sql);
		}
	}

	public function select($stmnt, $params = array()) {
		$paramCount = count($params);

		try {
			// Bind params to statement
			for($i = 0; $i < $paramCount; $i++) {
				$paramType = (is_int($params[$i])) ? PDO::PARAM_INT : PDO::PARAM_STR;
				$stmnt->bindParam(($i + 1), $params[$i], $paramType);
			}

			$stmnt->execute();

			return $stmnt->fetchAll(PDO::FETCH_OBJ);
		} catch(PDOException $e) {
			throw new DBException('PDO could not execute select query: ' . $e->getMessage(), 202, $stmnt->queryString, $params);
		}
	}

// selectAsObjects($stmnt,$params,$className)
	public function selectAsObjects($stmnt, $className, $params = array()) {
		$paramCount = count($params);

		try {
			// Bind params to statement
			for($i = 0; $i < $paramCount; $i++) {
				$paramType = (is_int($params[$i])) ? PDO::PARAM_INT : PDO::PARAM_STR;
				$stmnt->bindParam(($i + 1), $params[$i], $paramType);
			}

			$stmnt->execute();

			return $stmnt->fetchAll(PDO::FETCH_CLASS, $className);
		} catch(PDOException $e) {
			throw new DBException('PDO could not execute select query: ' . $e->getMessage(), 202, $stmnt->queryString, $params);
		}
	}

	public function insert($stmnt, $params = array()) {
		$paramCount = count($params);

		try {
			// Bind params to statement
			for($i = 0; $i < $paramCount; $i++) {
				$paramType = (is_int($params[$i])) ? PDO::PARAM_INT : PDO::PARAM_STR;
				$stmnt->bindParam(($i + 1), $params[$i], $paramType);
			}

			$stmnt->execute();

			return $this->lastInsertId();
		} catch(PDOException $e) {
			throw new DBException('PDO could not execute insert query: ' . $e->getMessage(), 204, $stmnt->queryString, $params);
		}
	}

	public function update($stmnt, $params) {
		$paramCount = count($params);

		try {
			// Bind params to statement
			for($i = 0; $i < $paramCount; $i++) {
				$paramType = (is_int($params[$i])) ? PDO::PARAM_INT : PDO::PARAM_STR;
				$stmnt->bindParam(($i + 1), $params[$i], $paramType);
			}

			$stmnt->execute();

			return $stmnt->rowCount();
		} catch(PDOException $e) {
			throw new DBException('PDO could not execute update query: ' . $e->getMessage(), 205, $stmnt->queryString, $params);
		}
	}

	public function delete($stmnt, $params) {
		$paramCount = count($params);

		try {
			// Bind params to statement
			for($i = 0; $i < $paramCount; $i++) {
				$paramType = (is_int($params[$i])) ? PDO::PARAM_INT : PDO::PARAM_STR;
				$stmnt->bindParam(($i + 1), $params[$i], $paramType);
			}

			$stmnt->execute();

			return $stmnt->rowCount();
		} catch(PDOException $e) {
			throw new DBException('PDO could not execute delete query: ' . $e->getMessage(), 205, $stmnt->queryString, $params);
		}
	}

	public function getDbConnect() {
		return $this->dbConnect;
	}
}

?>
