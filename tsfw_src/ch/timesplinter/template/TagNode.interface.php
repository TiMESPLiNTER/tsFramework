<?php
namespace ch\timesplinter\template;

use ch\timesplinter\htmlparser\ElementNode;

/**
 * TagNode
 *
 * @author Pascal Münst <entwicklung@metanet.ch>
 * @copyright Copyright (c) 2012, METANET AG, www.metanet.ch
 * @version	 1.0
 */
interface TagNode {
	/**
	 * Replaces the custom tag as node
	 * @param \ch\timesplinter\template\TemplateEngine $tplEngine
	 * @param \ch\timesplinter\template\ElementNode $tagNode
	 */
	public function replaceNode(TemplateEngine $tplEngine, ElementNode $tagNode);
}

/* EOF */