<?php

/**
 *
 *
 * @author				entwicklung@metanet.ch
 * @copyright	Copyright (c) 2012, METANET AG, www.metanet.ch
 * @version			1
 */
class OptionsTag extends TemplateTag implements TagNode {

	private $tagName = 'options';

	public function __construct() {
		parent::__construct(false);
	}

	public function replaceNode(TemplateEngine $tplEngine, ElementNode $node) {
		// DATA
		$compareValue = $node->getAttribute('selected')->value;

		$dataKey = $node->getAttribute('options')->value;

		$textContent = '<?php foreach($this->getData(\'' . $dataKey . '\') as $key => $val) {
			$selected = ($key == $this->getData(\'' . $compareValue . '\'))?\' selected="selected"\':\'\';
			echo \'<option value="\'.$key.\'"\'.$selected.\'>\'.$val.\'</option>\' . "\n";
		} ?>';

		$newNode = new TextNode($tplEngine->getDomReader());
		$newNode->content = $textContent;

		$node->parentNode->insertBefore($newNode, $node);
		$node->parentNode->removeNode($node);
	}

	public function getTagName() {
		return $this->tagName;
	}

}

?>