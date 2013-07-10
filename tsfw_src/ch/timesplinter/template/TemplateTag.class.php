<?php
namespace ch\timesplinter\template;

/**
 * TemplateTag
 *
 * @author Pascal Münst <entwicklung@metanet.ch>
 * @copyright (c) 2012, METANET AG, www.metanet.ch
 * @version 1.0
 */
abstract class TemplateTag {
	protected $tagName;
	protected $elseable;
	protected $selfClosing;

	/**
	 *
	 * @param boolean $elseable Can Tag be followed by a (@see ElseTag) or not
	 */
	public function __construct($tagName, $elseable = false, $selfClosing = false) {
		$this->tagName = $tagName;
		$this->elseable = $elseable;
		$this->selfClosing = $selfClosing;
	}

	public function isElseable() {
		return $this->elseable;
	}
	
	public function isSelfClosing() {
		return $this->selfClosing;
	}

	public function setElseable($elseable) {
		$this->elseable = $elseable;
	}

	public function getTagName() {
		return $this->tagName;
	}
}

/* EOF */