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
class ElseifTag extends TemplateTag implements TagNode
{
	public function replaceNode(TemplateEngine $tplEngine, ElementNode $tagNode)
	{
		$tplEngine->checkRequiredAttrs($tagNode, 'cond');
		
		$condAttr = $tagNode->getAttribute('cond')->value;

		$phpCode = '<?php ';

		$phpCode .= 'elseif(' . preg_replace_callback('/\${(.*?)}/i', function($m) {
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

	/**
	 * @return string
	 */
	public static function getName()
	{
		return 'elseif';
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