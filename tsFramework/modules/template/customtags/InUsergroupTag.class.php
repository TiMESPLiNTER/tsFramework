<?php

/**
 * InUsergroup
 *
 * @author				entwicklung@metanet.ch
 * @copyright	Copyright (c) 2012, METANET AG, www.metanet.ch
 * @version			1
 */
class InUsergroupTag extends TemplateTag implements TagNode {

	public function __construct() {
		parent::__construct(true);
	}

	public function replaceNode(TemplateEngine $tplEngine, ElementNode $tagNode) {
		$requiredUG = array_map('trim', explode(',', $tagNode->getAttribute('groups')->value));

		$phpCode = '<?php if(' . __CLASS__ . '::checkUGs(array(\'' . implode('\',\'', $requiredUG) . '\')) === true) { ?>';
		$phpCode .= $tagNode->getInnerHtml();

		if($tplEngine->isFollowedBy($tagNode, 'else') === false)
			$phpCode .= '<?php } ?>';

		$textNode = new TextNode($tplEngine->getDomReader());
		$textNode->content = $phpCode;

		$tagNode->parentNode->replaceNode($tagNode, $textNode);
	}

	public static function checkUGs($usergroups) {
		$auth = AuthHandlerFactory::getEnvAuth();

		foreach($usergroups as $ug) {
			if($auth->checkUG($ug) === true)
				return true;
		}

		return false;
	}

}

?>