<?php

/**
 * TemplateTag
 *
 * @author				entwicklung@metanet.ch
 * @copyright	Copyright (c) 2012, METANET AG, www.metanet.ch
 * @version			1
 */
abstract class TemplateTag {

	private $elseable = false;

	/**
	 *
	 * @param boolean $elseable Can Tag be followed by a (@see ElseTag) or not
	 */
	public function __construct($elseable) {
		$this->elseable = $elseable;
	}

	public function isElseable() {
		return $this->elseable;
	}

	public function setElseable($elseable) {
		$this->elseable = $elseable;
	}

}

?>