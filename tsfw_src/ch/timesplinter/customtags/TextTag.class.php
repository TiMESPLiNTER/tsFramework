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
 * @author Pascal Muenst <dev@timesplinter.ch>
 * @copyright Copyright (c) 2012, TiMESPLiNTER Webdevelopment
 * @version 1.0.0
 */
class TextTag extends TemplateTag implements TagNode, TagInline {
	public function __construct() {
		parent::__construct('text', false, true);
	}

	public function replaceNode(TemplateEngine $tplEngine, ElementNode $node) {
		$replValue = $this->replace($tplEngine, $node->getAttribute('value')->value);

		$replNode = new TextNode($tplEngine->getDomReader());
		$replNode->content = $replValue;

		$node->parentNode->replaceNode($node, $replNode);
	}

	public function replaceInline(TemplateEngine $tplEngine, $params) {
		return $this->replace($tplEngine, $params['value']);
	}

	public function replace(TemplateEngine $tplEngine, $params) {
		/*$paramsArr = explode('.', $params);
		$firstParam = $paramsArr[0];
		array_shift($paramsArr);
		
		if(is_object($tplEngine->getData($firstParam))) {
			if($tplEngine->getData($firstParam) instanceof \stdClass) {
				$restParams = (count($paramsArr) > 0)?'->' . implode('->', $paramsArr):null;
			} else {
				$getters = array();

				foreach($paramsArr as $param) {
					$getters[] = 'get' . ucfirst($param) . '()';
				}

				$restParams = (count($paramsArr) > 0)?'->' . implode('->', $getters):null;

				return '<?php $textData = $this->getData(\'' . $firstParam . '\'); echo $textData' . $restParams . '; ?>';
			}
		} else {
			$restParams = (count($paramsArr) > 0)?'[\'' . implode('\'][\'', $paramsArr) . '\']':null;
		}

		return '<?php $textData = $this->getData(\'' . $firstParam . '\'); echo isset($textData' . $restParams . ')?$textData' . $restParams . ':null; ?>';*/
		return '<?php echo $this->getDataFromSelector(\'' . $params . '\'); ?>';
	}
}

/* EOF */