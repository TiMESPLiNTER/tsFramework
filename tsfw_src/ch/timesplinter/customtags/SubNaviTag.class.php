<?php

/**
 * InUsergroup
 *
 * @author				entwicklung@metanet.ch
 * @copyright	Copyright (c) 2012, METANET AG, www.metanet.ch
 * @version			1
 */
class SubNaviTag extends TemplateTag implements TagNode {

	private static $navigationLevels = null;

	public function __construct() {
		parent::__construct('subnavi', true);
	}

	public function replaceNode(TemplateEngine $tplEngine, ElementNode $tagNode) {
		$sites = array_map('trim', explode(',', $tagNode->getAttribute('sites')->value));

		$phpCode = '<?php if(' . __CLASS__ . '::getCurrentNavi(array(\'' . implode('\',\'', $sites) . '\'))) { ?>';
		$phpCode .= $tagNode->getInnerHtml();

		if($tplEngine->isFollowedBy($tagNode, 'else') === false)
			$phpCode .= '<?php } ?>';

		$textNode = new TextNode($tplEngine->getDomReader());
		$textNode->content = $phpCode;

		$tagNode->parentNode->replaceNode($tagNode, $textNode);
		$tagNode->parentNode->removeNode($tagNode);
	}

	public static function getCurrentNavi($navigationLevelsTpl) {

		$rh = RequestHandler::getInstance();
		$fileTitle = $rh->getFileTitle();

		if(self::$navigationLevels === null) {
			$pageHandler = PageHandler::getInstance();
			$navigationLevels = $pageHandler->getNavigationLevels();
			/*
			  $navistufe = array();

			  include fwRoot . '/backend/config/' . $fileTitle . '.php';
			 */
			self::$navigationLevels = $navigationLevels;
		}

		foreach(self::$navigationLevels as $stufe) {
			foreach($navigationLevelsTpl as $navTpl) {
				if($navTpl === $stufe)
					return true;
			}
		}

		return false;
	}

}

?>