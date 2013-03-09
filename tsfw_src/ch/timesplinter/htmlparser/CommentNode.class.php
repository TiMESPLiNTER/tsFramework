<?php
namespace ch\timesplinter\htmlparser;

/**
 * CommentNode
 *
 * @author				entwicklung@metanet.ch
 * @copyright	Copyright (c) 2012, METANET AG, www.metanet.ch
 * @version			1
 */
class CommentNode extends HtmlNode {

	public function __construct(HtmlDoc $htmlDocument) {
		parent::__construct(HtmlNode::COMMENT_NODE, $htmlDocument);
	}

}

?>