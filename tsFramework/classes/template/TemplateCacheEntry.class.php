<?php

/**
 * TemplateCacheEntry
 *
 * @author				entwicklung@metanet.ch
 * @copyright	Copyright (c) 2012, METANET AG, www.metanet.ch
 * @version			1
 */
class TemplateCacheEntry {

	private $fileName;
	private $id;
	private $size;
	private $changeTime;

	public function __construct($fileName, $id, $size, $changeTime) {
		$this->fileName = $fileName;
		$this->id = $id;
		$this->size = $size;
		$this->changeTime = $changeTime;
	}

	public function getFileName() {
		return $this->fileName;
	}

	public function setFileName($fileName) {
		$this->fileName = $fileName;
	}

	public function getId() {
		return $this->id;
	}

	public function setId($id) {
		$this->id = $id;
	}

	public function getSize() {
		return $this->size;
	}

	public function setSize($size) {
		$this->size = $size;
	}

	public function __toString() {
		return $this->fileName . '=' . (int) $this->size . '=' . $this->id . '=' . (int) $this->changeTime;
	}

	public function getChangeTime() {
		return $this->changeTime;
	}

	public function setChangeTime($changeTime) {
		$this->changeTime = $changeTime;
	}

}

?>