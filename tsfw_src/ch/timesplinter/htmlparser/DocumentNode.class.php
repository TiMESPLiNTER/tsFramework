<?php
namespace ch\timesplinter\htmlparser;

/**
 * DocumentNode
 *
 * @author				entwicklung@metanet.ch
 * @copyright	Copyright (c) 2012, METANET AG, www.metanet.ch
 * @version			1
 */
class DocumentNode extends HtmlNode {

	public $childNodes;

	public function __construct(HtmlDoc $htmlDocument) {
		parent::__construct(HtmlNode::DOCUMENT_NODE, $htmlDocument);

		$this->childNodes = array();
	}

}

?>