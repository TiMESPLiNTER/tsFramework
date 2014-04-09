<?php

namespace ch\timesplinter\customtags;

use ch\timesplinter\htmlparser\ElementNode;
use ch\timesplinter\htmlparser\TextNode;
use ch\timesplinter\template\TagNode;
use ch\timesplinter\template\TemplateEngine;
use ch\timesplinter\template\TemplateTag;

/**
 * Class RadioOptionsTag
 * @package ch\timesplinter\customtags
 */
class RadioOptionsTag extends TemplateTag implements TagNode {
	public function __construct() {
		parent::__construct('radioOptions', false);
	}

	public function replaceNode(TemplateEngine $tplEngine, ElementNode $node) {
		// DATA
		TemplateEngine::checkRequiredAttrs($node, array('options', 'selected'));

		//$compareArr = $tplEngine->getDataFromSelector($node->getAttribute('checked')->value);
		$dataKey = $node->getAttribute('options')->value;
		$fldName = $node->getAttribute('name')->value;

		$textContent = '<?php echo "<ul>";  foreach($this->getDataFromSelector(\'' . $dataKey . '\') as $key => $val) {
			$checked = ($key == $this->getDataFromSelector(\'' . $node->getAttribute('selected')->value . '\'))?\' checked\':null;
			echo \'<li><label><input type="radio" value="\'.$key.\'" name="' . $fldName . '"\'.$checked.\'> \'.$val.\'</label></li>\' . "\n";
		} echo "</ul>"; ?>';

		$newNode = new TextNode($tplEngine->getDomReader());
		$newNode->content = $textContent;

		$node->parentNode->insertBefore($newNode, $node);
		$node->parentNode->removeNode($node);
	}
}

/* EOF */