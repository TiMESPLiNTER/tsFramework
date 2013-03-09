<?php

/**
 * Description of PHibernateMap
 *
 * @author Pascal Münst
 * @copyright Copyright (c) 2012, Metanet AG
 * @version 1.0
 */
class PHibernateClass {

	const PK_TYPE_GENERATED = 1;
	const PK_TYPE_CUSTOM = 2;

	private $className;
	private $tableName;
	private $aliasName;
	
	private $properties;
	private $primaryKey;
	private $primaryKeyType;
	private $foreignKeys;
	private $maps;
	private $views;
	
	private $sqlSelect;
	private $sqlUpdate;
	private $sqlDelete;
	private $sqlInsert;
	private $objectCache;

	public function __construct($className, $tableName) {
		$this->className = $className;
		$this->tableName = $tableName;

		$this->properties = array();
		$this->views = array();
		$this->primaryKey = array();
		$this->foreignKeys = array();
		$this->maps = array();

		$this->objectCache = array();
		$this->primaryKeyType = null;
		$this->aliasName = null;
	}

	public function addProperty(PHibernateProperty $property) {
		$this->properties[] = $property;
	}
	
	public function addView(PHibernateView $view) {
		$this->views[] = $view;
	}
	
	public function getViews() {
		return $this->views;
	}

	public function getClassName() {
		return $this->className;
	}

	public function getTableName() {
		return $this->tableName;
	}

	public function getProperties() {
		return $this->properties;
	}

	public function getPrimaryKey() {
		return $this->primaryKey;
	}

	public function addPrimaryKey(PHibernateProperty $primaryKeyProperty) {
		$this->primaryKey[] = $primaryKeyProperty;
	}

	public function getSqlSelect() {
		return $this->sqlSelect;
	}

	public function setSqlSelect($sqlSelect) {
		$this->sqlSelect = $sqlSelect;
	}

	public function getSqlUpdate() {
		return $this->sqlUpdate;
	}

	public function setSqlUpdate(PDOStatement $sqlUpdate) {
		$this->sqlUpdate = $sqlUpdate;
	}

	public function getSqlDelete() {
		return $this->sqlDelete;
	}

	public function setSqlDelete(PDOStatement $sqlDelete) {
		$this->sqlDelete = $sqlDelete;
	}

	public function getSqlInsert() {
		return $this->sqlInsert;
	}

	public function setSqlInsert(PDOStatement $sqlInsert) {
		$this->sqlInsert = $sqlInsert;
	}

	public function getForeignKeys() {
		return $this->foreignKeys;
	}

	public function getForeignKey($keyName) {
		if(array_key_exists($keyName, $this->foreignKeys) === false)
			throw new OutOfBoundsException('Foreignkey ' . $keyName . ' does not exist in class ' . $this->className);

		return $this->foreignKeys[$keyName];
	}

	public function addForeignKey(PHibernateForeignkey $foreignKey) {
		$this->foreignKeys[$foreignKey->getName()] = $foreignKey;
	}

	public function getMaps() {
		return $this->maps;
	}

	public function getMap($mapName) {
		if(array_key_exists($mapName, $this->maps) === false)
			throw new OutOfBoundsException('Map ' . $mapName . ' does not exist in class ' . $this->className);

		return $this->maps[$mapName];
	}

	public function addMap(PHibernateMap $map) {
		$this->maps[$map->getProperty()] = $map;
	}

	public function getCachedObject(PHibernatePopo $popo) {
		$arrKey = array();
		foreach($this->primaryKey as $pk) {
			$propName = $pk->getName();
			$arrKey[] = (string) $popo->$propName;
		}
		
		$key = implode('.', $arrKey);

		//echo 'read with key: <strong>' , $key , '</strong> on <strong>',$this->className,'</strong> cache<br>';

		
		if(array_key_exists($key, $this->objectCache) === true)
			return $this->objectCache[$key];

		return null;
	}

	public function cacheObject(PHibernatePopo $popo) {
		$arrKey = array();
		foreach($this->primaryKey as $pk) {
			$propName = $pk->getName();
			$arrKey[] = (string) $popo->$propName;
		}
		
		$key = implode('.', $arrKey);

		$this->objectCache[$key] = $popo;
		
		/*if($this->className === 'LoginPopo'  && $popo->email == 'pascal.muenst@actra.ch')
			echo'<pre style="color:#c00;"><strong>NEW CACHED:</strong>'; var_dump($popo); echo '</pre>';*/
	}

	public function getObjectCache() {
		return $this->objectCache;
	}

	public function getPrimaryKeyType() {
		return $this->primaryKeyType;
	}

	public function setPrimaryKeyType($primaryKeyType) {
		$this->primaryKeyType = $primaryKeyType;
	}

}

?>
