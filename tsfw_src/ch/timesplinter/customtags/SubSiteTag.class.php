<?php
namespace ch\timesplinter\customtags;

use 
 ch\timesplinter\template\TemplateEngine
,ch\timesplinter\template\TemplateTag
,ch\timesplinter\template\TagNode
,ch\timesplinter\htmlparser\ElementNode
,ch\timesplinter\htmlparser\TextNode
;

/**
 * SubSiteTag
 *
 * @author Pascal Münst <entwicklung@metanet.ch>
 * @copyright Copyright (c) 2012, METANET AG, www.metanet.ch
 * @version 1.0
 */
class SubSiteTag extends TemplateTag implements TagNode {

	public function __construct() {
		parent::__construct('subsite', true);
	}

	public function replaceNode(TemplateEngine $tplEngine, ElementNode $tagNode) {
		$sites = array_map('trim', explode(',', $tagNode->getAttribute('sites')->value));

		$phpCode = '<?php if(in_array(self::getData(\'_site\'),array(\'' . implode('\',\'', $sites) . '\'))) { ?>';
		$phpCode .= $tagNode->getInnerHtml();

		if($tplEngine->isFollowedBy($tagNode, 'else') === false)
			$phpCode .= '<?php } ?>';

		$textNode = new TextNode($tplEngine->getDomReader());
		$textNode->content = $phpCode;
		
		$tagNode->parentNode->replaceNode($tagNode, $textNode);
		$tagNode->parentNode->removeNode($tagNode);
	}

	public static function getCurrentSite() {
		$rh = RequestHandler::getInstance();

		return $rh->getFileTitle();
	}

}

?>