<?php
namespace ch\timesplinter\customtags;

use ch\timesplinter\template\TemplateTag as TemplateTag;
use ch\timesplinter\template\TagNode as TagNode;
use ch\timesplinter\template\TagInline as TagInline;

class DateTag extends TemplateTag implements TagInline, TagNode {

	private $tagName = 'date';

	public function __construct() {

	}

	public function replaceNode(TemplateEngine $tplEngine, ElementNode $node) {
		//$attrs = $node->getAttributes();

		$format = $node->getAttribute('format')->value;
		$replNode = new TextNode($tplEngine->getDomReader());
		$replNode->content = '<?php echo date(\'' . $format . '\'); ?>';

		$node->getParentNode()->replaceNode($node, $replNode);
	}

	public function replaceInline(TemplateEngine $tplEngine, $nodeStr) {

	}

	public function getTagName() {
		return $this->tagName;
	}

}

?>