<?php

/**
 * TagNode
 *
 * @author				entwicklung@metanet.ch
 * @copyright	Copyright (c) 2012, METANET AG, www.metanet.ch
 * @version			1
 */
interface TagNode {

	public function replaceNode(TemplateEngine $tplEngine, ElementNode $tagNode);
}

?>