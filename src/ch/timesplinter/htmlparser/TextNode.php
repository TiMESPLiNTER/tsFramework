<?php
namespace ch\timesplinter\htmlparser;

/**
 * TextNode
 *
 * @author Pascal Muenst <dev@timesplinter.ch>
 * @copyright Copyright (c) 2012, TiMESPLiNTER Webedevlopment
 * @version 1.0.0
 */
class TextNode extends HtmlNode {
	public function __construct(HtmlDoc $htmlDocument) {
		parent::__construct(HtmlNode::TEXT_NODE, $htmlDocument);
	}
}

/* EOF */