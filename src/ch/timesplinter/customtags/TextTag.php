<?php

namespace ch\timesplinter\customtags;

use ch\timesplinter\template\TemplateEngine;
use ch\timesplinter\template\TemplateTag;
use ch\timesplinter\template\TagInline;
use ch\timesplinter\template\TagNode;
use ch\timesplinter\htmlparser\ElementNode;
use ch\timesplinter\htmlparser\TextNode;

/**
 * @author Pascal Muenst <dev@timesplinter.ch>
 * @copyright Copyright (c) 2012, TiMESPLiNTER Webdevelopment
 */
class TextTag extends TemplateTag implements TagNode, TagInline
{
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
		return '<?php echo $this->getDataFromSelector(\'' . $params . '\'); ?>';
	}

	/**
	 * @return string
	 */
	public static function getName()
	{
		return 'text';
	}

	/**
	 * @return bool
	 */
	public static function isElseCompatible()
	{
		return false;
	}

	/**
	 * @return bool
	 */
	public static function isSelfClosing()
	{
		return true;
	}
}

/* EOF */