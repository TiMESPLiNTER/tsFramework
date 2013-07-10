<?php
namespace ch\timesplinter\customtags;

use ch\timesplinter\template\TemplateTag;
use ch\timesplinter\template\TagNode;
use ch\timesplinter\template\TemplateEngine;
use ch\timesplinter\htmlparser\ElementNode;
use ch\timesplinter\htmlparser\TextNode;

/**
 *
 *
 * @author Pascal Muenst <entwicklung@metanet.ch>
 * @copyright Copyright (c) 2012, METANET AG, www.metanet.ch
 * @version 1.0.0
 */
class OptionsTag extends TemplateTag implements TagNode {

	public function __construct() {
		parent::__construct('options', false);
	}

	public function replaceNode(TemplateEngine $tplEngine, ElementNode $node) {
		TemplateEngine::checkRequiredAttrs($node, array('options'));

		// DATA
		$compareValue = ($node->getAttribute('selected')->value !== null)?$tplEngine->getSelectorAsPHPStr($node->getAttribute('selected')->value):null;

		$dataKey = $tplEngine->getSelectorAsPHPStr($node->getAttribute('options')->value);

		$compareStr = '$selected = null;';

		if($compareValue !== null) {
			if(is_object($tplEngine->getSelectorValue($node->getAttribute('selected')->value))) {
				$compareStr = '$selected = in_array($key, (array)' . $compareValue . ')?\' selected\':null;';
			} else {
				$compareStr = '$selected = ($key == ' . $compareValue . ')?\' selected\':null;';
			}
		}

		$textContent = '<?php foreach(' . $dataKey . ' as $key => $val) {
			' . $compareStr . '
			echo \'<option value="\'.$key.\'"\'.$selected.\'>\'.$val.\'</option>\' . "\n";
		} ?>';

		$newNode = new TextNode($tplEngine->getDomReader());
		$newNode->content = $textContent;

		$node->parentNode->insertBefore($newNode, $node);
		$node->parentNode->removeNode($node);
	}
}

/* EOF */