<?php

/**
 * ElementNode
 *
 * @author				entwicklung@metanet.ch
 * @copyright	Copyright (c) 2012, METANET AG, www.metanet.ch
 * @version			1
 */
class ElementNode extends HtmlNode {

	const TAG_OPEN = 1;
	const TAG_CLOSE = 2;
	const TAG_SELFCLOSING = 3;

	public $childNodes;
	public $tagType;
	public $tagName;
	public $namespace;
	public $attributes;
	public $attributesNamed;

	public function __construct(HtmlDoc $htmlDocument) {
		parent::__construct(HtmlNode::ELEMENT_NODE, $htmlDocument);

		$this->namespace = null;
		$this->tagName = null;
		$this->tagType = null;

		$this->attributes = array();
		$this->attributesNamed = array();
	}

	public function getAttribute($key) {
		return $this->attributesNamed[$key];
	}

	public function addAttribute(HtmlAttribute $attr) {
		$this->attributes[] = $attr;
		$this->attributesNamed[$attr->key] = $attr;
	}

	public function getInnerHtml($entryNode = null) {
		$html = '';
		$nodeList = null;

		if($entryNode === null) {
			$nodeList = $this->childNodes;
		} else {
			$nodeList = $entryNode->childNodes;
		}

		foreach($nodeList as $node) {
			if($node instanceof ElementNode === false) {
				$html .= $node->content;
				continue;
			}

			$tagStr = (($node->namespace !== null) ? $node->namespace . ':' : '') . $node->tagName;

			$attrs = array();
			foreach($node->attributesNamed as $key => $val) {
				$attrs[] = $key . '="' . $val->value . '"';
			}
			$attrStr = (count($attrs) > 0) ? ' ' . implode(' ', $attrs) : '';

			if($node instanceof ElementNode === true) {
				$html .= '<' . $tagStr . $attrStr . (($node->tagType === ElementNode::TAG_SELFCLOSING) ? ' /' : '') . '>' . $node->content;
			} else {
				$html .= $node->content;
			}

			if($node->tagType === ElementNode::TAG_OPEN)
				$html .= self::getInnerHtml($node) . '</' . $tagStr . '>';
		}

		return $html;
	}

}

?>