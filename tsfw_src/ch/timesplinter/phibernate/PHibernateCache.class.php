<?php

/**
 * Handles the cache of the PHibernate framework
 *
 * @author Pascal Münst
 * @copyright Copyright (c) 2012, Metanet AG
 * @version 1.0
 */
class PHibernateCache {
	private $databaseName;
	private $mappedClasses;
	
	private $schemaFilepath;
	private $cacheDir;
	
	/**
	 *
	 * @param type $databaseName
	 * @param type $schemaFilepath
	 * @param type $cacheDir 
	 */
	public function __construct($databaseName, $schemaFilepath, $cacheDir) {
		$this->databaseName = $databaseName;
		$this->mappedClasses = null;
		
		$this->schemaFilepath = $schemaFilepath;
		$this->cacheDir = $cacheDir;
	}
	
	/**
	 * Parses the XML file and caches it as a PHP file
	 * @return
	 * @throws PHibernateException 
	 */
	public function loadCache() {
		$cacheFileName = $this->cacheDir . $this->databaseName . PHibernate::CACHE_SUFFIX;

		if(file_exists($this->schemaFilepath) === false) {
			throw new PHibernateException('XML-Schema "' . $this->schemaFilepath . '" does not exists!');
		
			return;
		}
		
		$xml = simplexml_load_file($this->schemaFilepath);

		$fp = @fopen($cacheFileName, 'w');

		if($fp === false)
			throw new PHibernateException('Cache file is not writable!');

		fwrite($fp, "<?php\n\n" . '$classes = array();' . "\n");

		$classes = array();
		$schemaFound = false;

		foreach($xml as $db) { 
			$attrsDb = $db->attributes();
			
			if((string)$attrsDb['name'] !== $this->databaseName)
				continue;
		
			$schemaFound = true;
			
			foreach($db as $class) {
				$attrs = $class->attributes();

				$mappingClassName = (string) $attrs['name'];

				fwrite($fp, "\n" . '// NEW CLASS: ' . $mappingClassName . "\n");
				fwrite($fp, '$tmpClass = new PHibernateClass(\'' . $mappingClassName . '\',\'' . (string) $attrs['table'] . '\');' . "\n");

				foreach($class->children() as $option) {
					$childTag = $option->getName();

					if($childTag === 'primarykey') {
						// PrimaryKey
						$attrs = $option->attributes();
						$pkType = 'null';

						switch((string)$attrs['type']) {
							case 'custom':
								$pkType = 'PHibernateClass::PK_TYPE_CUSTOM';
								break;
							case 'generated':
								$pkType = 'PHibernateClass::PK_TYPE_GENERATED';
								break;
							default:
								break;
						}

						fwrite($fp, '$tmpClass->setPrimaryKeyType('.$pkType.');' . "\n");

						foreach($option->children() as $prop) {
							fwrite($fp, self::parseProperty($prop));

							fwrite($fp, '$tmpClass->addProperty($tmpProp);' . "\n");
							fwrite($fp, '$tmpClass->addPrimaryKey($tmpProp);' . "\n");
						}
					} elseif($childTag === 'property') {
						// Property
						fwrite($fp, self::parseProperty($option));
						fwrite($fp, '$tmpClass->addProperty($tmpProp);' . "\n");
					} elseif($childTag === 'view') {
						// View
						fwrite($fp, self::parseView($option));
						fwrite($fp, '$tmpClass->addView($tmpView);' . "\n");
					}elseif($childTag === 'foreignkey') {
						// ForeignKey (one-to-one)
						$fkAttrs = $option->attributes();

						$fkProperty = $fkAttrs['property'];
						$fkName = $fkAttrs['name'];

						if($fkName === null)
							throw new PHibernateException('No foreignkey name specified');

						if($fkProperty === null)
							throw new PHibernateException('No property specified for foreignkey "' . (string) $fkName . '"');



						fwrite($fp, '$fk = new PHibernateForeignkey(\'' . (string) $fkName . '\', \'' . (string) $fkAttrs['class'] . '\', \'' . (string) $fkProperty . '\');' . "\n");

						if(isset($fkAttrs['ondelete']) === true) {
							$fkOnDelete = null;

							switch((string)$fkAttrs['ondelete']) {
								case 'cascade':
									$fkOnDelete = 'PHibernate::ACTION_CASCADE';
									break;
								case 'keep':
									$fkOnDelete = 'PHibernate::ACTION_KEEP';
									break;
								default:
									break;
							}

							if($fkOnDelete !== null)
								fwrite($fp,'$fk->setOnDelete('.$fkOnDelete.');' . "\n");
						}

						foreach($option->column as $col) {
							fwrite($fp, self::parseColumn($col));
							fwrite($fp, '$fk->addColumn($column);' . "\n");
						}

						fwrite($fp, '$tmpClass->addForeignKey($fk);' . "\n");
					} elseif($childTag === 'map') {
						// Map (one-to-many)
						$mapAttrs = $option->attributes();

						fwrite($fp, '$map = new PHibernateMap(\'' . (string) $mapAttrs['property'] . '\', \'' . (string) $mapAttrs['class'] . '\');' . "\n");

						if(isset($mapAttrs['ondelete']) === true) {
							$mapOnDelete = null;

							switch((string)$mapAttrs['ondelete']) {
								case 'cascade':
									$mapOnDelete = 'PHibernate::ACTION_CASCADE';
									break;
								case 'keep':
									$mapOnDelete = 'PHibernate::ACTION_KEEP';
									break;
								default:
									break;
							}

							if($mapOnDelete !== null)
								fwrite($fp,'$map->setOnDelete('.$mapOnDelete.');' . "\n");
						}

						foreach($option->column as $col) {
							fwrite($fp, self::parseColumn($col));
							fwrite($fp, '$map->addColumn($column);' . "\n");
						}

						fwrite($fp, '$tmpClass->addMap($map);' . "\n");
					}
				}

				fwrite($fp, '$classes[\'' . $mappingClassName . '\'] = $tmpClass;' . "\n");
			}
		}

		fwrite($fp, "\n?>");
		fclose($fp);
		
		if($schemaFound === false)
			throw new PHibernateException('No schema for the database name "' . $this->databaseName . '" found in XML-Schema: ' . $this->schemaFilepath);

		$this->mappedClasses = array();

		require_once $cacheFileName;

		$this->mappedClasses = $classes;

		// PREPARE SQL
		$fp = @fopen($cacheFileName, 'a');

		if($fp === false)
			throw new PharException('Cache file is not writable!');
		
		fwrite($fp, "<?php\n");
	
		/** @var $mappedClass PHibernateClass */
		foreach($classes as $key => $mappedClass) {
			$joins = self::joinTables($mappedClass, $fp);
			
			$sqlUpdateCond = $sqlDeleteCond = array();
			$sqlUpdateCols = $sqlInsertCols = array();
			
			$pk = $mappedClass->getPrimaryKey();
			
			foreach($pk as $pkProp) {
				$sqlUpdateCond[] = $sqlDeleteCond[] = $pkProp->getColumn() . ' = ?';
			}
			
			foreach($mappedClass->getProperties() as $prop) {
				if($mappedClass->getPrimaryKeyType() !== PHibernateClass::PK_TYPE_GENERATED || in_array($prop, $pk) === false)
					$sqlInsertCols[] = $prop->getColumn() . ' = ?';
				
				if(in_array($prop, $pk) === true)
					continue;
				
				$sqlUpdateCols[] = $prop->getColumn() . ' = ?';
			}
			
			$sqlDelete = 'DELETE FROM ' . $mappedClass->getTableName() . ' WHERE ' . implode(' AND ', $sqlDeleteCond);
			$sqlUpdate = 'UPDATE ' . $mappedClass->getTableName() . ' SET ' . implode(', ', $sqlUpdateCols) . ' WHERE ' . implode(' AND ', $sqlUpdateCond);
			$sqlInsert = 'INSERT INTO ' . $mappedClass->getTableName() . ' SET ' . implode(', ', $sqlInsertCols);

			// SELECT (bit special)
			$selCols = self::getPropertiesAsSqlSelectors($mappedClass,$mappedClass->getTableName());

			$sqlSelect = 'SELECT ' . implode("\n ,", $selCols) . "\nFROM " . $mappedClass->getTableName() . ((count($joins) > 0) ? "\n" . implode("\n", $joins) : '');

			fwrite($fp, '$classes[\'' . $key . '\']->setSqlUpdate($this->db->prepareStatement(\'' . $sqlUpdate . '\'));' . "\n");
			fwrite($fp, '$classes[\'' . $key . '\']->setSqlDelete($this->db->prepareStatement(\'' . $sqlDelete . '\'));' . "\n");
			fwrite($fp, '$classes[\'' . $key . '\']->setSqlInsert($this->db->prepareStatement(\'' . $sqlInsert . '\'));' . "\n");
			fwrite($fp, '$classes[\'' . $key . '\']->setSqlSelect("' . $sqlSelect . '");' . "\n");
		}

		fwrite($fp, "\n?>");
		fclose($fp);
	}

	/**
	 * Returns a XML attributes 'property' as a PHP cache statement
	 * @param SimpleXMLElement $propertyXml
	 * @return string The PHP code as string
	 */
	private function parseProperty(SimpleXMLElement $propertyXml) {
		$attrs = $propertyXml->attributes();

		$type = self::getPropertyType((string) $attrs['type']);

		$phpStr = '';

		$phpStr .= '$tmpProp = new PHibernateProperty(\'' . (string) $attrs['name'] . '\', \'' . (string) $attrs['column'] . '\', ' . $type . ');' . "\n";
		$phpStr .= '$tmpProp->setNullable(' . ((isset($attrs['nullable']) && (string) $attrs['nullable'] === 'yes') ? 'true' : 'false') . ');' . "\n";

		return $phpStr;
	}
	
	/**
	 * Returns a XML element 'view' as a PHP cache statement
	 * @param SimpleXMLElement $propertyXml
	 * @return string The PHP code as string
	 */
	private function parseView(SimpleXMLElement $propertyXml) {
		$attrs = $propertyXml->attributes();
		
		$type = self::getPropertyType((string) $attrs['type']);

		$phpStr = '';

		$phpStr .= '$tmpView = new PHibernateView(\'' . uniqid() . '\', "' . (string) $attrs['selector'] . '", \'' . (string) $attrs['property'] . '\', ' . $type . ');' . "\n";

		return $phpStr;
	}

	/**
	 * Returns a XML attributes 'property' as a PHP cache statement
	 * @param SimpleXMLElement $columnXmlObj
	 * @return string The PHP code as string
	 */
	private function parseColumn(SimpleXMLElement $columnXmlObj) {
		$colAttrs = $columnXmlObj->attributes();
		$type = self::getPropertyType((string) $colAttrs['type']);
		$nullable = (isset($colAttrs['nullable']) && (string) $colAttrs['nullable'] === 'yes') ? 'true' : 'false';
		return '$column = new PHibernateColumn(\'' . (string) $colAttrs['name'] . '\', ' . $nullable . ',\'' . (string) $colAttrs['references'] . '\', ' . $type . ');' . "\n";
	}

	/**
	 * Returns a XML attributes 'type' as a PHP cache statement
	 * @param type $columnXmlObj
	 * @return type 
	 */
	private function getPropertyType($type) {
		$propType = PHibernateProperty::TYPE_UNKNOWN;

		switch($type) {
			case 'datetime':
			case 'date':
				$propType = PHibernateProperty::TYPE_DATETIME;
				break;
			case 'int':
				$propType = PHibernateProperty::TYPE_INT;
				break;
			case 'float':
			case 'double':
				$propType = PHibernateProperty::TYPE_FLOAT;
				break;
			case 'string':
				$propType = PHibernateProperty::TYPE_STRING;
				break;
			default:
				break;
		}

		return $propType;
	}
	
	private function getPropertiesAsSqlSelectors($mappedClass, $tableAlias, $joinCounter = null, $srch = null, $repl = null, $fkProperty = 'this') {
		var_dump($fkProperty);
		
		$properties = $mappedClass->getProperties();
		
		if($joinCounter === null)
			$joinCounter = new ArrayObject(array());
		
		$columns = array();
		
		if($srch === null)
			$srch = new ArrayObject(array());
		
		if($repl === null)
			$repl = new ArrayObject(array());

		foreach($properties as $prop) {
			$columns[] = $tableAlias . '.' . $prop->getColumn() . ' ' . $tableAlias . '_' . $prop->getColumn();

			$srch[] = '{' . $fkProperty . '.' . $prop->getName() . '}';
			$repl[] = $tableAlias . '.' . $prop->getColumn();
		}

		// <foreignkey ...>
		foreach($mappedClass->getForeignkeys() as $fk) {
			/** @var $fk PHibernateForeignkey */
			$mappedClassToJoin = $this->mappedClasses[$fk->getClass()];

			$joinId = $fk->getJoinId(PHibernateCommon::getIncreasedCounter($joinCounter, $fk->getName()));
			
			$columns = array_merge($columns,self::getPropertiesAsSqlSelectors($mappedClassToJoin, $joinId, $joinCounter, $srch, $repl, $fk->getProperty()));
		}
		
		foreach($mappedClass->getViews() as $view) {
			// Replace {this.xyz} through the current property
			$selector = str_replace('{this','{' . $fkProperty, $view->getSelector());
			
			// Replace the properties {fkProperty.propertyName}
			$selector = str_replace((array)$srch, (array)$repl, $selector);

			$columns[] = $selector . ' ' . $tableAlias . '_' . $view->getIdentifier();
		}
		
		return $columns;
	}

	/**
	 * Joins the tables to a select from the foreign keys
	 * @param type $mappedClass
	 * @param type $fp
	 * @param type $alias
	 * @param ArrayObject $counter
	 * @return type 
	 */
	private function joinTables($mappedClass, $fp, $alias = null, $counter = null) {
		$joins = array();

		if($counter === null)
			$counter = new ArrayObject(array());

		if($alias === null)
			$alias = $mappedClass->getTableName();

		foreach($mappedClass->getForeignkeys() as $fk) {
			$mappedClassToJoin = $this->mappedClasses[$fk->getClass()];

			$pos = PHibernateCommon::getIncreasedCounter($counter, $fk->getName());

			$id = $fk->getJoinId($pos);

			if($id === null) {
				$id = uniqid();
				$fk->addJoinId($pos, $id);
				$phpCode = '$classes[\'' . $mappedClass->getClassName() . '\']->getForeignkey(\'' . $fk->getName() . '\')->addJoinId(' . $pos . ', \'' . $id . '\');';
				fwrite($fp, $phpCode . "\n");
			}

			$joinSql = 'LEFT JOIN ' . $mappedClassToJoin->getTableName() . ' ' . $id . ' ON ';
			$joinCols = array();
			foreach($fk->getColumns() as $col)
				$joinCols[] = $alias . '.' . $col->getName() . ' = ' . $id . '.' . $col->getReferences();

			$joinSql .= implode(' AND ', $joinCols);

			$joins[] = $joinSql;

			if(count($mappedClassToJoin->getForeignKeys()) > 0) {
				$subJoins = self::joinTables($mappedClassToJoin, $fp, $id, $counter);
				$joins = array_merge($joins, $subJoins);
			}
		}

		return $joins;
	}
}

?>
