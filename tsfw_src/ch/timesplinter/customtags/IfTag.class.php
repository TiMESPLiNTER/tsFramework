<?php
namespace ch\timesplinter\customtags;

use 
 ch\timesplinter\template\TemplateEngine
,ch\timesplinter\template\TemplateTag
,ch\timesplinter\template\TagNode
,ch\timesplinter\htmlparser\ElementNode
,ch\timesplinter\htmlparser\TextNode
,ch\timesplinter\htmlparser\HtmlNode
,  ch\timesplinter\common\StringUtils
,  ch\timesplinter\logger\LoggerFactory
;
/**
 *
 *
 * @author Pascal Münst <entwicklung@metanet.ch>
 * @copyright Copyright (c) 2012, METANET AG, www.metanet.ch
 * @version 1.0.0
 */
class IfTag extends TemplateTag implements TagNode {
	private $logger;
	
	public function __construct() {
		//$this->logger = LoggerFactory::getEnvLogger($this);
		parent::__construct('if', true);
	}

	public function replaceNode(TemplateEngine $tplEngine, ElementNode $tagNode) {
		$compareAttr = $tagNode->getAttribute('compare')->value;
		$operatorAttr = $tagNode->getAttribute('operator')->value;
		$againstAttr = $tagNode->getAttribute('against')->value;
		//$this->logger->debug(print_r($tagNode->parentNode->nodeType, true));
		
		// Check required attrs
		TemplateEngine::checkRequiredAttrs($tagNode, array('compare', 'operator', 'against'));
		
		$varTags = array('tst:for');
		$parentTagName = ($tagNode->parentNode->nodeType === HtmlNode::ELEMENT_NODE)?$tplEngine->getTplNsPrefix() . ':' . $tagNode->parentNode->tagName:null;
		
		$compareValParts = explode('.', $compareAttr);
		
		if(in_array($parentTagName, $varTags) === true)
			$compareVal = '$' . $compareValParts[0];
		else
			$compareVal = 'self::getData(\'' . $compareValParts[0] . '\')';
			
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
				$againstAttr = ($againstAttr === 'true')?'true':'false';
			} elseif(StringUtils::startsWith($againstAttr, '{') && StringUtils::endsWith($againstAttr, '}')) {
				$arr = substr(explode(',', $againstAttr), 1, -1);
				$againstAttr = array();
				
				foreach($arr as $a) {
					$againstAttr[] = trim($a);
				}
			}
		}
		
		$phpCode = '<?php if(' . $compareVal . ' ' . $operatorAttr . ' ' . $againstAttr . ') { ?>';
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