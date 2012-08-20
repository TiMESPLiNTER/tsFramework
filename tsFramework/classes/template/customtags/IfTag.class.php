<?php

/**
 *
 *
 * @author				entwicklung@metanet.ch
 * @copyright	Copyright (c) 2012, METANET AG, www.metanet.ch
 * @version			1
 */
class IfTag extends TemplateTag implements TagNode {

	public function __construct() {
		parent::__construct(true);
	}

	public function replaceNode(TemplateEngine $tplEngine, ElementNode $tagNode) {
		$comapreValue = $tagNode->getAttribute('compare')->value;
		$values = array_map('trim', explode(',', $tagNode->getAttribute('against')->value));
		$equalsTo = $tagNode->getAttribute('result')->value;

		$phpCode = '<?php if(in_array((string)$this->getData(\'' . $comapreValue . '\'),array(\'' . implode('\',\'', $values) . '\')) === ' . (($equalsTo === true) ? 'true' : 'false') . ') { ?>';
		$phpCode .= $tagNode->getInnerHtml();

		if($tplEngine->isFollowedBy($tagNode, 'else') === false)
			$phpCode .= '<?php } ?>';

		$textNode = new TextNode($tplEngine->getDomReader());
		$textNode->content = $phpCode;

		$tagNode->parentNode->replaceNode($tagNode, $textNode);
		$tagNode->parentNode->removeNode($tagNode);
	}

}

?>