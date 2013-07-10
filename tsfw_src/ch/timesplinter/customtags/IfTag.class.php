<?php

namespace ch\timesplinter\customtags;

use ch\timesplinter\core\FrameworkLoggerFactory;
use ch\timesplinter\logger\TSLogger;
use ch\timesplinter\template\TemplateEngine;
use ch\timesplinter\template\TemplateTag;
use ch\timesplinter\template\TagNode;
use ch\timesplinter\htmlparser\ElementNode;
use ch\timesplinter\htmlparser\TextNode;
use ch\timesplinter\htmlparser\HtmlNode;
use ch\timesplinter\common\StringUtils;

/**
 *
 *
 * @author Pascal Münst <entwicklung@metanet.ch>
 * @copyright Copyright (c) 2012, METANET AG, www.metanet.ch
 * @version 1.0.0
 * @package CustomTags
 */
class IfTag extends TemplateTag implements TagNode {
	private $logger;

	public function __construct() {
		$this->logger = FrameworkLoggerFactory::getLogger($this);
		parent::__construct('if', true);
	}

	public function replaceNode(TemplateEngine $tplEngine, ElementNode $tagNode) {
		$compareAttr = $tagNode->getAttribute('compare')->value;
		$operatorAttr = $tagNode->getAttribute('operator')->value;
		$againstAttr = $tagNode->getAttribute('against')->value;
		$this->logger->debug(print_r($tagNode->parentNode->nodeType, true));

		// Check required attrs
		TemplateEngine::checkRequiredAttrs($tagNode, array('compare', 'operator', 'against'));

		$varTags = array('tst:for');
		$parentTagName = ($tagNode->parentNode->nodeType === HtmlNode::ELEMENT_NODE)?$tplEngine->getTplNsPrefix() . ':' . $tagNode->parentNode->tagName:null;

		$compareValParts = explode('.', $compareAttr);

		if(in_array($parentTagName, $varTags) === true)
			$compareVal = "(isset(\$" . $compareValParts[0] . ")?\$" . $compareValParts[0] . ":\$this->getData('" . $compareValParts[0] . "'))";
		else
			$compareVal = '$this->getData(\'' . $compareValParts[0] . '\')';

		array_shift($compareValParts);
		$compareVal .= (count($compareValParts) > 0)?'->' . implode('->', $compareValParts):null;

		if(is_int($againstAttr) === true) {
			$againstAttr = intval($againstAttr);
		} elseif(is_float($againstAttr) === true) {
			$againstAttr = floatval($againstAttr);
		} elseif(is_string($againstAttr) === true) {
			if(strtolower($againstAttr) === 'null') {
				//$againstAttr = 'null';
			} elseif(strtolower($againstAttr) === 'true' || strtolower($againstAttr) === 'false') {
				//$againstAttr = ($againstAttr === 'true')?true:false;
			} elseif(StringUtils::startsWith($againstAttr, '{') && StringUtils::endsWith($againstAttr, '}')) {
				$arr = substr(explode(',', $againstAttr), 1, -1);
				$againstAttr = array();

				foreach($arr as $a) {
					$againstAttr[] = trim($a);
				}
			}
		}

		$operatorStr = '==';

		switch(strtolower($operatorAttr)) {
			case 'gt': $operatorStr = '>';
				break;
			case 'ge': $operatorStr = '>=';
				break;
			case 'lt': $operatorStr = '<';
				break;
			case 'le': $operatorStr = '<=';
				break;
			case 'lt': $operatorStr = '<';
				break;
			case 'eq': $operatorStr = '==';
				break;
			case 'ne': $operatorStr = '!=';
				break;
		}

		$phpCode = '<?php if(' . $compareVal . ' ' . $operatorStr . ' ' . $againstAttr . ') { ?>';
		$phpCode .= $tagNode->getInnerHtml();

		if($tplEngine->isFollowedBy($tagNode, 'else') === false)
			$phpCode .= '<?php } ?>';

		$textNode = new TextNode($tplEngine->getDomReader());
		$textNode->content = $phpCode;

		$tagNode->parentNode->replaceNode($tagNode, $textNode);
		$tagNode->parentNode->removeNode($tagNode);
	}
}

/* EOF */