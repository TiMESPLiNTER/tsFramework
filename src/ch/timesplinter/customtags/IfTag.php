<?php

namespace ch\timesplinter\customtags;

use ch\timesplinter\template\TemplateEngine;
use ch\timesplinter\template\TemplateEngineException;
use ch\timesplinter\template\TemplateTag;
use ch\timesplinter\template\TagNode;
use ch\timesplinter\htmlparser\ElementNode;
use ch\timesplinter\htmlparser\TextNode;
use ch\timesplinter\htmlparser\HtmlNode;
use ch\timesplinter\common\StringUtils;

/**
 * @author Pascal Münst <dev@timesplinter.ch>
 * @copyright Copyright (c) 2012, TiMESPLiNTER Webdevelopment
 */
class IfTag extends TemplateTag implements TagNode
{
	public function replaceNode(TemplateEngine $tplEngine, ElementNode $tagNode)
	{
		$compareAttr = $tagNode->getAttribute('compare')->value;
		$operatorAttr = $tagNode->getAttribute('operator')->value;
		$againstAttr = $tagNode->getAttribute('against')->value;
		$condAttr = $tagNode->getAttribute('cond')->value;

		if($condAttr === null) {
			// Check required attrs
			$tplEngine->checkRequiredAttrs($tagNode, array('compare', 'operator', 'against'));

			if(strlen($againstAttr) === 0) {
				$againstAttr = "''";
			} elseif(is_int($againstAttr) === true) {
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
				} else {
					$againstAttr = "'" . $againstAttr . "'";
				}
			}

			$operatorStr = '==';

			switch(strtolower($operatorAttr)) {
				case 'gt':
					$operatorStr = '>';
					break;
				case 'ge':
					$operatorStr = '>=';
					break;
				case 'lt':
					$operatorStr = '<';
					break;
				case 'le':
					$operatorStr = '<=';
					break;
				case 'eq':
					$operatorStr = '==';
					break;
				case 'ne':
					$operatorStr = '!=';
					break;
			}

			$phpCode = '<?php ';

			$phpCode .= 'if($this->getDataFromSelector(\'' . $compareAttr . '\') ' . $operatorStr . ' ' . $againstAttr . '): ?>';
			$phpCode .= $tagNode->getInnerHtml();

			if($tplEngine->isFollowedBy($tagNode, array('else', 'elseif')) === false) {
				$phpCode .= '<?php endif; ?>';
			}

			$textNode = new TextNode($tplEngine->getDomReader());
			$textNode->content = $phpCode;

			$tagNode->parentNode->replaceNode($tagNode, $textNode);
			$tagNode->parentNode->removeNode($tagNode);
		} else {
			$phpCode = '<?php ';

			$phpCode .= 'if(' . preg_replace_callback('/\${(.*?)}/i', function($m) {
				if(strlen($m[1]) === 0)
					throw new TemplateEngineException('Empty template data reference');
					
				return '$this->getDataFromSelector(\'' . $m[1] . '\')';
			}, $condAttr) . '): ?>';
			
			$phpCode .= $tagNode->getInnerHtml();

			if($tplEngine->isFollowedBy($tagNode, array('else', 'elseif')) === false) {
				$phpCode .= '<?php endif; ?>';
			}

			$textNode = new TextNode($tplEngine->getDomReader());
			$textNode->content = $phpCode;

			$tagNode->parentNode->replaceNode($tagNode, $textNode);
			$tagNode->parentNode->removeNode($tagNode);
		}
	}

	/**
	 * @return string
	 */
	public static function getName()
	{
		return 'if';
	}

	/**
	 * @return bool
	 */
	public static function isElseCompatible()
	{
		return true;
	}

	/**
	 * @return bool
	 */
	public static function isSelfClosing()
	{
		return false;
	}
}

/* EOF */