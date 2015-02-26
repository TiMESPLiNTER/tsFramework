<?php

namespace ch\timesplinter\customtags;

use ch\timesplinter\auth\AuthHandlerDB;
use
 ch\timesplinter\template\TemplateEngine
,ch\timesplinter\template\TemplateTag
,ch\timesplinter\template\TagNode
,ch\timesplinter\htmlparser\ElementNode
,ch\timesplinter\htmlparser\TextNode
,ch\timesplinter\template\TemplateEngineException
;

/**
 * @author Pascal Muenst <entwicklung@metanet.ch>
 * @copyright Copyright (c) 2012, METANET AG, www.metanet.ch
 */
class InUsergroupTag extends TemplateTag implements TagNode
{
	public function replaceNode(TemplateEngine $tplEngine, ElementNode $tagNode)
	{
		$tplEngine->checkRequiredAttrs($tagNode, array('groups'));
		
		$requiredUG = array_map('trim', explode(',', $tagNode->getAttribute('groups')->value));

		$phpCode = '<?php if(' . __CLASS__ . '::checkUGs(array(\'' . implode('\',\'', $requiredUG) . '\'), $this) === true) { ?>';
		$phpCode .= $tagNode->getInnerHtml();

		if($tplEngine->isFollowedBy($tagNode, 'else') === false)
			$phpCode .= '<?php } ?>';

		$textNode = new TextNode($tplEngine->getDomReader());
		$textNode->content = $phpCode;

		$tagNode->parentNode->replaceNode($tagNode, $textNode);
	}

	public static function checkUGs($userGroups, TemplateEngine $tplEngine)
	{
		/** @var $auth AuthHandlerDB */
		$auth = $tplEngine->getData('_auth');
		
		if($auth === null)
			throw new TemplateEngineException('No auth object accessable');
		
		foreach($userGroups as $ug) {
			if($auth->hasRightGroup($ug) === true)
				return true;
		}

		return false;
	}

	/**
	 * @return string
	 */
	public static function getName()
	{
		return 'InUsergroup';
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