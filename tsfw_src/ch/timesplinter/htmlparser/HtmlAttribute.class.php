<?php
namespace ch\timesplinter\htmlparser;

/**
 * ElementNode
 *
 * @author Pascal Münst <entwicklung@metanet.ch>
 * @copyright	Copyright (c) 2012, METANET AG, www.metanet.ch
 * @version 1.0
 */
class HtmlAttribute {

	public $key;
	public $value;

	public function __construct($key, $value) {
		$this->key = $key;
		$this->value = $value;
	}

}

?>