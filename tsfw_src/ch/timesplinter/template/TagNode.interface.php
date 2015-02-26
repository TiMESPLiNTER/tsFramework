<?php

namespace ch\timesplinter\template;

use ch\timesplinter\htmlparser\ElementNode;

/**
 * @author Pascal Münst <dev@timesplinter.ch>
 * @copyright Copyright (c) 2012, TiMESPLiNTER Webdevelopment
 */
interface TagNode
{
	/**
	 * Replaces the custom tag as node
	 * @param TemplateEngine $tplEngine
	 * @param ElementNode $tagNode
	 */
	public function replaceNode(TemplateEngine $tplEngine, ElementNode $tagNode);

	/**
	 * @return string
	 */
	public static function getName();

	/**
	 * @return bool
	 */
	public static function isElseCompatible();

	/**
	 * @return bool
	 */
	public static function isSelfClosing();
}

/* EOF */