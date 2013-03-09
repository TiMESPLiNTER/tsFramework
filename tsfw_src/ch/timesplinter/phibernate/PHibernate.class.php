<?php

/**
 * PHibernate 2 - The magic database persistance framework. Set up an enjoy!
 * It's a child's play, breaking inovativ and hell fast!
 *
 * @author Pascal Muenst
 * @copyright Copyright (c) 2012, Actra AG
 * @version 1.0
 */
class PHibernate {
	const ORDER_ASC = 'ASC';
	const ORDER_DESC = 'DESC';
	
	const OPERATOR_EQUALS = '=';
	const OPERATOR_LIKE = 'LIKE';
	const OPERATOR_LT = '<';
	const OPERATOR_GT = '>';
	const OPERATOR_NOT = '!';
	
	const COND_AND = 'AND';
	const COND_OR = 'OR';
	
	const ACTION_CASCADE = 1;
	const ACTION_KEEP = 2;
	
	const CACHE_SUFFIX = '.cache';
	
	/** @var DB */
	private $db;
	private $databaseName;
	private $mappedClasses;
	private $stmntCache;

	private $saveCache;
	private $affectedRows;
	
	private $originalPopoCache;
	
	private $schmeaFilepath;
	private $cacheDir;
	
	/**
	 * 
	 * @param string $databaseName The name of the database on the schema XML
	 * @param DB $db The database object
	 * @param string $schemaFilepath The path to the XML schema file
	 * @param string $cacheDir The dir where the cache files are stored
	 * 
	 * @return PHibernate
	 */
	function __construct($databaseName, DB $db, $schemaFilepath, $cacheDir) {
		$this->db = $db;
		$this->databaseName = $databaseName;
		
		$this->schmeaFilepath = $schemaFilepath;
		$this->cacheDir = $cacheDir;
		
		$this->mappedClasses = self::loadCache($schemaFilepath, $cacheDir);
		$this->originalPopoCache = array();
		
		$this->stmntCache = array();
	}
	
	/**
	 * Loads the cache if it exists. Else it generates the cache and returns it
	 * @return array The classes loaded from the cache 
	 */
	private function loadCache() {
		$cacheFileName = $this->cacheDir . $this->databaseName . self::CACHE_SUFFIX;
		
		$classes = array();
		
		if(file_exists($cacheFileName) !== true || (filemtime($this->schmeaFilepath) > filemtime($cacheFileName))) {
			$cache = new PHibernateCache($this->databaseName, $this->schmeaFilepath, $this->cacheDir);
			$cache->loadCache();
			
			require $cacheFileName;
		} else {
			require_once $cacheFileName;
		}
		
		foreach($classes as $class) {
			$classes[$class->getTableName()] = $class;
		}
		
		return $classes;
	}

	/**
	 * Loads data from a table in the database
	 * @param string $tableName The table name to start loading from
	 * @param array|null $criterias No, one or more criterium objects
	 * (@see PHibernateCriteria)
	 * @param array|null $methodPath The current method path (DO NOT TOUCH)
	 * @return PHibernateResultset The results
	 * @throws PHibernateException 
	 */
	public function load($tableName, $criterias = null, $methodPath = null) {
		if(array_key_exists($tableName, $this->mappedClasses) === false)
			throw new PHibernateException('Table "' . $tableName . '" not in schema');
		
		$mappingClass = $this->mappedClasses[$tableName];
		
		if($methodPath === null)
			$methodPath = array($tableName);

		$orderSql = array();
		$condition = null;
		$limit = null;
		$params = array();
		
		$methodPathStr = implode('->',$methodPath);
		$counterOrder = new ArrayObject();
		
		// PHibernateConditions
		if($criterias !== null) {
			if(is_array($criterias) === false)
				$criterias = array($criterias);
			
			foreach($criterias as $c) {
				if($c === null)
					continue;
				
				/** @var $c PHibernateCriteria */
				$cMethodPath = $c->getMethodPath();
				
				if($cMethodPath === $methodPathStr) {
					// ORDERS
					$orders = $c->getOrders();
					
					foreach($orders as $col => $order) {
						$orderSql[] = $mappingClass->getTableName() . '.' . $col . ' ' . $order;
					}
					
					// WHERES
					$condGrps = $c->getConditionGroups();
					
					if(count($condGrps) > 0) {
						$params = new ArrayObject(array());
						
						foreach($condGrps as $condGrp) {
							$condition .= self::generateQueryConds($condGrp,$params,$mappingClass,true);
						}
					}
					
					// LIMIT
					$maxResults = $c->getResultRange();
					
					if($maxResults !== null) {
						$limit = 'LIMIT ' . $maxResults[0] . ',' . $maxResults[1];
					}
					
					continue;
				}

				// FKs durch
				$fks = $mappingClass->getForeignKeys();
				
				foreach($fks as $fk) {
					if($cMethodPath !== $methodPathStr . '->' . $fk->getProperty())
						continue;

					$idPos = PHibernateCommon::getIncreasedCounter($counterOrder, $fk->getName());
					$tableAlias = $fk->getJoinId($idPos);
					
					// ORDERS
					$orders = $c->getOrders();
					
					foreach($orders as $col => $order) {
						$orderSql[] = $tableAlias . '.' . $col . ' ' . $order;
					}
					
					continue;
				}
			}
		}
		
		$sql = $mappingClass->getSqlSelect() . (($condition !== null) ? "\nWHERE " . $condition : '') . (count($orderSql) > 0?"\nORDER BY " . implode(', ',$orderSql):'') . (($limit !== null)?"\n" . $limit:'');
		//echo'<pre style="color:#00c;">';var_dump($sql,(array)$params); echo'</pre>';
		
		$stmnt = self::getPreparedStmnt($sql);
		$res = $this->db->select($stmnt, (array)$params);
		$result = new PHibernateResultset();
		
		foreach($res as $r)
			$result->append(self::loadMapping($mappingClass->getClassName(), $r, $mappingClass->getTableName(), $methodPath, $criterias,null));
		
		return $result;
	}
	
	/**
	 * Generates the WHERE condition for a SELECT SQL query
	 * @param PHibernateConditionGroup $phCondGrp The condition group 
	 * @param ArrayObject $params The paramters to add to the select query
	 * @param PHibernateClass $mappingClass
	 * @param boolean $firstGrp
	 * @return string
	 * @throws PHibernateException 
	 */
	private function generateQueryConds(PHibernateConditionGroup $phCondGrp, ArrayObject $params, PHibernateClass $mappingClass, $firstGrp = false) {
		$condGrpSqlStr = '';

		foreach($phCondGrp->getConditions() as $cond) {
			if($cond instanceof PHibernateConditionGroup) {
				$condGrpSqlStr .= ' ' . $cond->getJoinCondition() . ' '. self::generateQueryConds($cond, $params, $mappingClass, true);

				continue;
			}

			$values = $cond->getValues();
			$operator = $cond->getOperator();

			$multiOperator = null;
			$nullOperator = null;

			switch($operator) {
				case PHibernate::OPERATOR_EQUALS:
					$multiOperator = 'IN';
					$nullOperator = 'IS NULL';
					break;
				case PHibernate::OPERATOR_NOT . PHibernate::OPERATOR_EQUALS:
					$multiOperator = 'NOT IN';
					$nullOperator = 'IS NOT NULL';
					break;
				default:
					if((is_array($values) === true/* && in_array(null,$values) === true*/) || (is_array($values) === false && $values === null))
						throw new PHibernateException('You can only add a condition with multiple values if the operator is "equals" (=) and "not equals" (!=) your operator is "'.$operator.'"');
					break;
			}

			// One value
			if(is_array($values) === false) {
				$colQualifier = '';

				if($values === null) {
					$colQualifier = $mappingClass->getTableName() . '.' . $cond->getColumn() . ' ' . $nullOperator;
					$condGrpSqlStr .= ((strlen($condGrpSqlStr) > 0)?' ' . $cond->getCond() . ' ':'') . $colQualifier;
				} else {
					$colQualifier = $mappingClass->getTableName() . '.' . $cond->getColumn() . ' ' . $operator . ' ?';
					$params[] = $values;
				}

				$condGrpSqlStr .= ((strlen($condGrpSqlStr) > 0)?' ' . $cond->getCond() . ' ':'') . $colQualifier;

				continue;
			} 

			// Multiple values
			$anzParams = array();
			$paramsCount = count($values);

			$nullValue = false;

			for($i = 0; $i < $paramsCount; $i++) {
				if($values[$i] === null) {
					$nullValue = true;
					continue;
				}

				$anzParams[] = '?';
				$params[] = $values[$i];
			}

			$colQualifier = $mappingClass->getTableName() . '.' . $cond->getColumn();

			$condGrpSqlStr .= ((strlen($condGrpSqlStr) > 0)?' ' . $cond->getCond() . ' ':'') . $colQualifier . ' ' . $multiOperator . '(' . implode(',',$anzParams) . ')' . (($nullValue === true)?' OR ' . $colQualifier . ' ' . $nullOperator:'');
		}

		$finalCond = (($firstGrp === false)?$phCondGrp->getJoinCondition() . ' ':'') . '(' . $condGrpSqlStr . ')';
		
		return $finalCond;
	}
	
	/**
	 * Maps a single record from the database into the PHibernatePopo objects
	 * @param string $mappingClassName The class name of the object to load
	 * @param stdClass $r The data to load in this object
	 * @param string $prefix The prefix for the columns which are holding the data
	 * @param array $methodPath The current method path
	 * @param array $criterias An array with criteria objects 
	 * (@see PHibernateCriteria)
	 * @param ArrayObject $counter Counter for foreignkey occurance (DONT TOUCH)
	 * @return null|PHibernatePopo The filled in PHibernatePopo or null
	 */
	private function loadMapping($mappingClassName, $r, $prefix, $methodPath, $criterias, $counter = null) {
		$mappingClass = $this->mappedClasses[$mappingClassName];
		
		if(array_key_exists($mappingClassName, $this->originalPopoCache) === false) {
			$this->originalPopoCache[$mappingClassName] = new $mappingClassName;
		}
		
		/** @var PHibernatePopo */
		$popoInstance = clone $this->originalPopoCache[$mappingClassName]; 

		if($counter === null)
			$counter = new ArrayObject(array());

		// <property ...>
		foreach($mappingClass->getProperties() as $prop) {
			$propName = $prop->getName();
			$colName = $prefix . '_' . $prop->getColumn();
			
			$popoInstance->$propName = self::castType($r->$colName, $prop->getType());
		}
		
		// Now we have the primary key... let's see
		$cachedObj = $mappingClass->getCachedObject($popoInstance);
		if($cachedObj !== null) {
			// Just increase to reach the right level at the right time
			foreach($mappingClass->getForeignkeys() as $fk) 
				PHibernateCommon::getIncreasedCounter($counter, $fk->getName());
			
			return $cachedObj;
		}

		$mappingClass->cacheObject($popoInstance);

		// <foreignkey ...>
		foreach($mappingClass->getForeignkeys() as $fk) {
			$joinIdOffset = PHibernateCommon::getIncreasedCounter($counter, $fk->getName());

			$skip = false;
			$fkCols = $fk->getColumns();
			
			foreach($fkCols as $col) {
				$propName = $col->getName();
				
				if($popoInstance->$propName !== null)
					continue;
				
				$skip = true;
				break;
			}
			
			if($skip === true)
				continue;
			
			$propName = $fk->getProperty();
			$popoInstance->$propName = self::loadMapping($fk->getClass(), $r, $fk->getJoinId($joinIdOffset), array_merge($methodPath,array($propName)), $criterias, $counter);
		}
		
		// <map ...>
		foreach($mappingClass->getMaps() as $map) {
			$mappingClassName = $map->getClass();
			$mapClass = $this->mappedClasses[$mappingClassName];
			$mapTableName = $mapClass->getTableName();

			$propNameMap = $map->getProperty();
			
			$primCriteria = new PHibernateCriteria(implode('->',$methodPath) . '->' . $propNameMap);

			$criteriaArray = array();
			
			foreach($map->getColumns() as $col) {
				$propName = $col->getName();
				
				$criteriaArray[] = new PHibernateCondition($col->getReferences(), PHibernate::OPERATOR_EQUALS, $popoInstance->$propName);
			}

			$primCriteria->addConditionGroup(new PHibernateConditionGroup($criteriaArray));
			
			$criterias[] = $primCriteria;
			
			$popoInstance->$propNameMap = self::load($mapTableName, $criterias, array_merge($methodPath,array($propNameMap)));
		}
		
		// <view ...>
		foreach($mappingClass->getViews() as $view) {
			$viewPropName = $view->getProperty();
			$viewColumnName = $prefix . '_' . $view->getIdentifier();
			
			$popoInstance->$viewPropName = self::castType($r->$viewColumnName, $view->getType());
		}
		
		if($popoInstance->isNull() === true)
			return null;
		
		$popoInstance->resetChanged();
		
		return $popoInstance;
	}

	/**
	 * Saves a PHibernateResultset
	 * @param PHibernateResultset $resultset The resultset to save
	 * @param array $clearSaveChache (DO NOT TOUCH THIS)
	 */
	public function save(PHibernateResultset $resultset, $clearSaveChache = true) {
		if($clearSaveChache === true) {
			$this->saveCache = array();
			$this->affectedRows = 0;
		}
		
		try {
			foreach($resultset as $res) {
				$this->db->beginTransaction();

				self::saveMapping($res);

				$this->db->commit();
			}
		} catch(Exception $e) {
			$this->db->rollBack();
			$this->affectedRows = 0;
			throw $e;
		}
		
		return $this->affectedRows;
	}

	/**
	 * Saves a PHibernatePopo to the database
	 * @param PHibernatePopo $mappedObj The PHibernatePopo to save
	 * @return array The primary key of the saved object 
	 */
	private function saveMapping(PHibernatePopo $mappedObj) {
		$splObjHash = spl_object_hash($mappedObj);
		$mappedClass = $this->mappedClasses[get_class($mappedObj)];
		
		if(in_array($splObjHash, $this->saveCache) === true) {
			$pParams = array();
			
			foreach($mappedClass->getPrimaryKey() as $pk) {
				$propName = $pk->getName();
				$pParams[] = $mappedObj->$propName;
			}
			
			return $pParams;
		}
		
		$this->saveCache[] = $splObjHash;
		
		$params = array();
				
		// Foreinkey properties
		foreach($mappedClass->getForeignKeys() as $fk) {
			$propName = $fk->getProperty();
			
			if($mappedObj->$propName === null)
				continue;
			
			if($mappedObj->getActionOnSave() === PHibernatePopo::ACTION_DELETE && $fk->getOnDelete() === PHibernate::ACTION_CASCADE)
				$mappedObj->$propName->delete();
			
			$colsToUpdate = self::saveMapping($mappedObj->$propName);
			
			if($colsToUpdate === null)
				continue;
			
			$i = 0;
			foreach($fk->getColumns() as $col) {
				$colName = $col->getName();
				
				$mappedObj->$colName = $colsToUpdate[$i];
				$i++;
			}
		}
		
		if($mappedObj->hasChanged() === false) {
			// NO CHANGES
			$pkArr = array();
			
			foreach($mappedClass->getPrimaryKey() as $pk) {
				$pkProp = $pk->getName();
				$pkArr[] = $mappedObj->$pkProp;
			}
			
			$returnParams = $pkArr;
		} else {
			// HAS CHANGES
			// Normal properties
			foreach($mappedClass->getProperties() as $prop) {
				$propName = $prop->getName();

				if($prop->getForeignKey() === null) {
					$params[] = self::castTypeToString($mappedObj->$propName);
					continue;
				}
			}

			$returnParams = null;
			switch($mappedObj->getActionOnSave()) {
				case PHibernatePopo::ACTION_INSERT:
					$returnParams = self::insertPopo($mappedObj);
					break;
				case PHibernatePopo::ACTION_UPDATE:
					$returnParams = self::updatePopo($mappedObj);
					break;
				case PHibernatePopo::ACTION_DELETE:
					self::deletePopo($mappedObj);
					break;
				default:

					break;
			}
		}
		
		// Map properties
		foreach($mappedClass->getMaps() as $map) {
			$propName = $map->getProperty();
			
			$resSet = $mappedObj->$propName;
			
			if($resSet === null)
				continue;

			if($mappedObj->getActionOnSave() === PHibernatePopo::ACTION_DELETE && $map->getOnDelete() === PHibernate::ACTION_CASCADE) {
				foreach($resSet as $r) {
					$r->delete();
				}
			}
			
			$mapCols = $map->getColumns();
			
			foreach($resSet as $r) {
				$i = 0;
				foreach($mapCols as $mc) {
					$ref = $mc->getReferences();
					
					$r->$ref = $returnParams[$i];
					$i++;
				}
				
				self::saveMapping($r);
			}
		}
		
		return $returnParams;
	}

	/**
	 * Updates a PHibernatePopo
	 * @param PHibernatePopo $popoObj The PHibernatePopo to update
	 * @return array The primary key the updated PHibernatePopo 
	 */
	private function updatePopo($popoObj) {
		$mappedClass = $this->mappedClasses[get_class($popoObj)];
	
		$stmnt = $mappedClass->getSqlUpdate();
		
		$forbidden = array();
		$params = array();
		$pParams = array();
		
		foreach($mappedClass->getPrimaryKey() as $pk) {
			$propName = $pk->getColumn();
			$forbidden[] = $propName;
			$pParams[] = self::castTypeToString($popoObj->$propName);
		}
	
		foreach($mappedClass->getProperties() as $prop) {
			$propName = $prop->getName();
			
			if(in_array($propName,$forbidden) === true)
				continue;
			
			$params[] = self::castTypeToString($popoObj->$propName);
		}
		
		$affRows = $this->db->update($stmnt, array_merge($params,$pParams));
		
		$this->affectedRows += $affRows;
		
		return $pParams;
	}
	
	/**
	 * Inserts a new PHibernatePopo into the database
	 * @param PHibernatePopo $popoObj The PHibernatePopo to insert
	 * @return array The primary key the updated PHibernatePopo 
	 */
	private function insertPopo($popoObj) {
		$mappedClass = $this->mappedClasses[get_class($popoObj)];
	
		$stmnt = $mappedClass->getSqlInsert();
		
		$params = array();
		$pParams = array();
		
		foreach($mappedClass->getProperties() as $prop) {
			$propName = $prop->getName();
			
			if(in_array($prop, $mappedClass->getPrimaryKey()) === true) {
				$pParams[] = self::castTypeToString($popoObj->$propName);
				
				if($mappedClass->getPrimaryKeyType() === PHibernateClass::PK_TYPE_GENERATED)
					continue;
			}
						
			$params[] = self::castTypeToString($popoObj->$propName);
		}
		
		$res = $this->db->insert($stmnt,$params);
		
		$this->affectedRows++;
		
		if($mappedClass->getPrimaryKeyType() === PHibernateClass::PK_TYPE_GENERATED) {
			return array($res);
		}
		
		return $pParams;
	}
	
	/**
	 * Simply deletes a PHibernatePopo from the database
	 * @param PHibernatePopo $popoObj The PHibernatePopo to delete
	 */
	private function deletePopo($popoObj) {
		if($popoObj === null)
			return;
		
		$mappedClass = $this->mappedClasses[get_class($popoObj)];
	
		$stmnt = $mappedClass->getSqlDelete();
		
		$pParams = array();
		
		foreach($mappedClass->getPrimaryKey() as $pk) {
			$propName = $pk->getName();
			$pParams[] = self::castTypeToString($popoObj->$propName);
		}
		
		$affRows = $this->db->delete($stmnt, $pParams);
		
		$this->affectedRows += $affRows;
	}
	
	/**
	 * Casts any object to a string (for the database)
	 * @param StdObject $value The object/value to be represented as string
	 * @return mixed The string representation of the object or
	 * the original value 
	 */
	private function castTypeToString($value) {
		if($value === null)
			return null;

		if(is_object($value) === true) {
			if($value instanceof DateTime)
				return $value->format('Y-m-d H:i:s');
			else
				return (string) $value;
		}

		return $value;
	}

	/**
	 * Casts a database value into a useful php object
	 * @param mixed $value
	 * @param int $type
	 * @return mixed 
	 */
	private function castType($value, $type) {
		if($value === null)
			return $value;

		switch($type) {
			case PHibernateProperty::TYPE_INT:
				return (int) $value;
				break;
			case PHibernateProperty::TYPE_STRING:
				return (string) $value;
				break;
			case PHibernateProperty::TYPE_FLOAT:
			case PHibernateProperty::TYPE_DOUBLE:
				return (float) $value;
				break;
			case PHibernateProperty::TYPE_DATETIME:
				return new DateTime($value);
				break;
			default:
				return $value;
				break;
		}

		return $value;
	}

	/**
	 * Caches a prepared statement for later use
	 * @param string $sql the SQL string for the prepared statement
	 * @return PDOStatement The prepared statement 
	 */
	private function getPreparedStmnt($sql) {
		if(array_key_exists($sql, $this->stmntCache) === true)
			return $this->stmntCache[$sql];

		$tmpStmnt = $this->db->prepareStatement($sql);
		$this->stmntCache[$sql] = $tmpStmnt;

		return $tmpStmnt;
	}
}

?>