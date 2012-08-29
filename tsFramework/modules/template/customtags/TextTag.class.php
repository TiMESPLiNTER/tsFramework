<?php

/**
 * TextTag
 *
 * @author				entwicklung@metanet.ch
 * @copyright	Copyright (c) 2012, METANET AG, www.metanet.ch
 * @version			1
 */
class TextTag extends TemplateTag implements TagNode, TagInline {

	private $tagName = 'text';

	public function __construct() {

	}

	public function replaceNode(TemplateEngine $tplEngine, ElementNode $node) {
		$replValue = self::replace($tplEngine, $node->getAttribute('value')->value);

		$replNode = new TextNode($tplEngine->getDomReader());
		$replNode->content = $replValue;

		$node->parentNode->replaceNode($node, $replNode);
	}

	public function replaceInline(TemplateEngine $tplEngine, $params) {
		return self::replace($tplEngine, $params['value']);
	}

	public function replace(TemplateEngine $tplEngine, $params) {
		return '<?php echo self::getData(\'' . $params . '\'); ?>';
	}

	public function getTagName() {
		return $this->tagName;
	}

}

?>