<?php
namespace ch\timesplinter\customtags;

use ch\timesplinter\template\TemplateTag;
use ch\timesplinter\template\TagNode;
use ch\timesplinter\template\TemplateEngine;
use ch\timesplinter\htmlparser\ElementNode;
use ch\timesplinter\htmlparser\TextNode;

/**
 * @author Pascal Münst
 * @copyright Copyright (c) 2012, Pascal Münst
 * @version 1.0
 */
class CheckboxOptionsTag extends TemplateTag implements TagNode {

	public function __construct() {
		parent::__construct('checkboxOptions', false);
	}

	public function replaceNode(TemplateEngine $tplEngine, ElementNode $node) {
		// DATA
		$compareArr = $tplEngine->getSelectorAsPHPStr($node->getAttribute('checked')->value);
		$dataKey = $tplEngine->getSelectorAsPHPStr($node->getAttribute('options')->value);
		$fldName = $node->getAttribute('name')->value . '[]';

		$textContent = '<?php echo "<ul>";  foreach(' . $dataKey . ' as $key => $val) {
			$checked = in_array($key, ((array)' . $compareArr . '))?\' checked\':null;
			echo \'<li><label><input type="checkbox" value="\'.$key.\'" name="' . $fldName . '"\'.$checked.\'>\'.$val.\'</label></li>\' . "\n";
		} echo "</ul>"; ?>';

		$newNode = new TextNode($tplEngine->getDomReader());
		$newNode->content = $textContent;

		$node->parentNode->insertBefore($newNode, $node);
		$node->parentNode->removeNode($node);
	}

	public function getTagName() {
		return $this->tagName;
	}

}

/* EOF */