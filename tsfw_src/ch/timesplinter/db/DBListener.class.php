<?php

/**
 * @author Pascal Muenst <entwicklung@metanet.ch>
 * @copyright Copyright (c) 2013, METANET AG
 * @version 1.0.0
 */

namespace ch\timesplinter\db;

use \PDOStatement;

abstract class DBListener {
	/**
	 * @param DB $db
	 * @param PDOStatement $stmnt
	 * @param array $params
	 */
	public function onSelect(DB $db, PDOStatement $stmnt, array $params) {

	}

	/**
	 * @param DB $db
	 * @param PDOStatement $stmnt
	 * @param array $params
	 */
	public function onUpdate(DB $db, PDOStatement $stmnt, array $params) {

	}

	/**
	 * @param DB $db
	 * @param PDOStatement $stmnt
	 * @param array $params
	 */
	public function onInsert(DB $db, PDOStatement $stmnt, array $params) {

	}

	/**
	 * @param DB $db
	 * @param PDOStatement $stmnt
	 * @param array $params
	 */
	public function onDelete(DB $db, PDOStatement $stmnt, array $params) {

	}

	/**
	 * @param DB $db
	 * @param PDOStatement $stmnt
	 */
	public function onExecute(DB $db, PDOStatement $stmnt) {

	}

	/**
	 * @param DB $db
	 * @param PDOStatement $stmnt
	 */
	public function onPrepare(DB $db, PDOStatement $stmnt) {

	}
}

/* EOF */