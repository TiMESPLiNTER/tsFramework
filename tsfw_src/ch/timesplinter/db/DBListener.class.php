<?php

namespace ch\timesplinter\db;

use \PDOStatement;

/**
 * A listener class with which you can extend your own listener to react on some DB events like select, update, insert
 * delete, transaction start, commit, etc.
 * @author Pascal Muenst <dev@timesplinter.ch>
 * @copyright Copyright (c) 2013, TiMESPLiNTER
 * @version 1.0.0
 */
abstract class DBListener {
	const QUERY_TYPE_DELETE = 'delete';
	const QUERY_TYPE_UPDATE = 'update';
	const QUERY_TYPE_INSERT = 'insert';

	/**
	 * Called on SELECT
	 * @param DB $db
	 * @param PDOStatement $stmnt
	 * @param array $params
	 */
	public function onSelect(DB $db, PDOStatement $stmnt, array $params) {

	}

	/**
	 * Called on execute a statement
	 * @param DB $db
	 * @param PDOStatement $stmnt
	 */
	public function onExecute(DB $db, PDOStatement $stmnt) {

	}

	/**
	 * Called on preparing a statement
	 * @param DB $db
	 * @param PDOStatement $stmnt
	 */
	public function onPrepare(DB $db, PDOStatement $stmnt) {

	}

	/**
	 * Called on connect to db
	 * @param DB $db
	 * @param DBConnect $dbConnect
	 */
	public function onConnect(DB $db, DBConnect $dbConnect) {

	}

	/**
	 * Called before UPDATE, INSERT or DELETE
	 * @param DB $db
	 * @param PDOStatement $stmnt
	 */
	public function beforeMutation(DB $db, PDOStatement $stmnt, array $params, $queryType) {

	}

	/**
	 * Called after UPDATE, INSERT or DELETE
	 * @param DB $db
	 * @param PDOStatement $stmnt
	 */
	public function afterMutation(DB $db, PDOStatement $stmnt, array $params, $queryType) {

	}

	/**
	 * Called before a transaction starts
	 * @param DB $db
	 */
	public function beforeBeginTransaction(DB $db) {

	}

	/**
	 * Called after a transaction is commited
	 * @param DB $db
	 */
	public function afterCommit(DB $db) {

	}
}

/* EOF */