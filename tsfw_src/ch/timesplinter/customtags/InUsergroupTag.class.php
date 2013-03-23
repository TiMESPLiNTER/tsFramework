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
 * InUsergroup
 *
 * @author				entwicklung@metanet.ch
 * @copyright	Copyright (c) 2012, METANET AG, www.metanet.ch
 * @version			1
 */
class InUsergroupTag extends TemplateTag implements TagNode {
	public function __construct() {
		parent::__construct('InUsergroupTag', true);
	}

	public function replaceNode(TemplateEngine $tplEngine, ElementNode $tagNode) {
		TemplateEngine::checkRequiredAttrs($tagNode, array('groups'));
		
		$requiredUG = array_map('trim', explode(',', $tagNode->getAttribute('groups')->value));

		$phpCode = '<?php if(' . __CLASS__ . '::checkUGs(array(\'' . implode('\',\'', $requiredUG) . '\'), $this) === true) { ?>';
		$phpCode .= $tagNode->getInnerHtml();

		if($tplEngine->isFollowedBy($tagNode, 'else') === false)
			$phpCode .= '<?php } ?>';

		$textNode = new TextNode($tplEngine->getDomReader());
		$textNode->content = $phpCode;

		$tagNode->parentNode->replaceNode($tagNode, $textNode);
	}

	public static function checkUGs($usergroups, TemplateEngine $tplEngine) {
		/** @var $auth AuthHandlerDB */
		$auth = $tplEngine->getData('_auth');
		
		if($auth === null)
			throw new TemplateEngineException('No auth object accessable');
		
		foreach($usergroups as $ug) {
			if($auth->hasRightgroup($ug) === true)
				return true;
		}

		return false;
	}
}

?>