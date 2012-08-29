<?php

/**
 *
 *
 * @author				entwicklung@metanet.ch
 * @copyright	Copyright (c) 2012, METANET AG, www.metanet.ch
 * @version			1
 */
class ElseTag extends TemplateTag implements TagNode {

	public function __construct() {
		parent::__construct(false);
	}

	public function replaceNode(TemplateEngine $tplEngine, ElementNode $tagNode) {
		$lastTplTag = $tplEngine->getLastTplTag();

		if($lastTplTag === null)
			throw new TemplateEngineException('There is no custom tag that can be followed by an ElseTag');

		if($lastTplTag->isElseable() === false)
			throw new TemplateEngineException('The custom tag "' . get_class($lastTplTag) . '" can not be followed by an ElseTag');

		$phpCode = '<?php } else { ?>';
		$phpCode .= $tagNode->getInnerHtml();
		$phpCode .= '<?php } ?>';

		$textNode = new TextNode($tplEngine->getDomReader());
		$textNode->content = $phpCode;

		$tagNode->parentNode->replaceNode($tagNode, $textNode);

		$tagNode->parentNode->removeNode($tagNode);
	}

}

?>