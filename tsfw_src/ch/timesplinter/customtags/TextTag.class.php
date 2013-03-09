<?php
namespace ch\timesplinter\customtags;

use 
 ch\timesplinter\template\TemplateEngine
,ch\timesplinter\template\TemplateTag
,ch\timesplinter\template\TagInline
,ch\timesplinter\template\TagNode
,ch\timesplinter\htmlparser\ElementNode
,ch\timesplinter\htmlparser\TextNode
;

/**
 * TextTag
 *
 * @author Pascal Münst <dev@timesplinter.ch>
 * @copyright Copyright (c) 2012, METANET AG, www.metanet.ch
 * @version 1.0
 */
class TextTag extends TemplateTag implements TagNode, TagInline {
	public function __construct() {
		parent::__construct('text', false, true);
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
		$paramsArr = explode('.', $params);
		$firstParam = $paramsArr[0];
		array_shift($paramsArr);
		
		if(is_object($tplEngine->getData($firstParam))) {
			$restParams = (count($paramsArr) > 0)?'->' . implode('->', $paramsArr):null;
		} else {
			$restParams = (count($paramsArr) > 0)?'[\'' . implode('\'][\'', $paramsArr) . '\']':null;
		}
		
		return '<?php $textData = self::getData(\'' . $firstParam . '\'); echo isset($textData' . $restParams . ')?$textData' . $restParams . ':null; ?>';
	}
}

?>