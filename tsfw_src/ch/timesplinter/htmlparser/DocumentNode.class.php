<?php
namespace ch\timesplinter\htmlparser;

/**
 * DocumentNode
 *
 * @author Pascal Münst <dev@timesplinter.ch>
 * @copyright Copyright (c) 2012, TiMESPLiNTER Webdevelopment
 * @version 1.0.0
 */
class DocumentNode extends HtmlNode
{
	public $childNodes;

	public function __construct(HtmlDoc $htmlDocument)
	{
		parent::__construct(HtmlNode::DOCUMENT_NODE, $htmlDocument);

		$this->childNodes = array();
	}
}

/* EOF */