<?php

/**
 * TextNode
 *
 * @author				entwicklung@metanet.ch
 * @copyright	Copyright (c) 2012, METANET AG, www.metanet.ch
 * @version			1
 */
class TextNode extends HtmlNode {

	public function __construct(HtmlDoc $htmlDocument) {
		parent::__construct(HtmlNode::TEXT_NODE, $htmlDocument);
	}

}

?>