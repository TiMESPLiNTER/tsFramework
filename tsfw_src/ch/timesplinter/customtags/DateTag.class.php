<?php
namespace ch\timesplinter\customtags;

use ch\timesplinter\template\TemplateEngine;
use ch\timesplinter\template\TemplateTag;
use ch\timesplinter\htmlparser\ElementNode;
use ch\timesplinter\htmlparser\TextNode;
use ch\timesplinter\template\TagNode as TagNode;
use ch\timesplinter\template\TagInline as TagInline;

class DateTag extends TemplateTag implements TagInline, TagNode {
	public function __construct() {
		parent::__construct('if', false);
	}

	public function replaceNode(TemplateEngine $tplEngine, ElementNode $tagNode) {
		//$attrs = $node->getAttributes();

		$format = $tagNode->getAttribute('format')->value;
		$replNode = new TextNode($tplEngine->getDomReader());
		$replNode->content = '<?php echo date(\'' . $format . '\'); ?>';

		$tagNode->parentNode->replaceNode($tagNode, $replNode);
	}

	public function replaceInline(TemplateEngine $tplEngine, $params) {

	}

	public function getTagName() {
		return $this->tagName;
	}

}

?>