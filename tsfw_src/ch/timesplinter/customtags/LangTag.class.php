<?php
namespace ch\timesplinter\customtags;

/**
 * LangTag
 *
 * @author				entwicklung@metanet.ch
 * @copyright	Copyright (c) 2012, METANET AG, www.metanet.ch
 * @version			1
 */
class LangTag extends TemplateTag implements TagNode, TagInline {
	public function __construct() {
		parent::__construct('lang', false, true);
	}

	public function replaceNode(TemplateEngine $tplEngine, ElementNode $node) {
		$replValue = self::replace($tplEngine, $node->getAttribute('key')->value, $node->getAttribute('vars')->value);

		$replNode = new TextNode($tplEngine->getDomReader());
		$replNode->content = $replValue;

		$node->parentNode->replaceNode($node, $replNode);
	}

	public function replaceInline(TemplateEngine $tplEngine, $params) {
		$vars = (array_key_exists('vars', $params)) ? $params['vars'] : null;
		return self::replace($tplEngine, $params['key'], $vars);
	}

	public function replace(TemplateEngine $tplEngine, $key, $vars = null) {
		$phpVars = '';
		if($vars !== null) {
			$varsEx = explode(',', $vars);
			$varsFull = array();

			foreach($varsEx as $v) {
				$varsFull[] = '\'' . $v . '\' => self::getData(\'' . $v . '\')';
			}

			$phpVars = ',array(' . implode(', ', $varsFull) . ')';
		}

		return '<?php echo LocaleHandler::getInstance()->getText(\'' . $key . '\'' . $phpVars . '); ?>';
	}
}

?>