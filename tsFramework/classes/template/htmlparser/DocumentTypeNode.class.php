<?php

/**
 * CommentNode
 *
 * @author				entwicklung@metanet.ch
 * @copyright	Copyright (c) 2012, METANET AG, www.metanet.ch
 * @version			1
 */
class DocumentTypeNode extends HtmlNode {

	public function __construct(HtmlDoc $htmlDocument) {
		parent::__construct(HtmlNode::DOCUMENT_TYPE_NODE, $htmlDocument);
	}

}

?>