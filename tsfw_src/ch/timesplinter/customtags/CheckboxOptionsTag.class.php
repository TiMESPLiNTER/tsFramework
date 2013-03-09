<?php
namespace ch\timesplinter\customtags;

use ch\timesplinter\template\TemplateTag as TemplateTag;
use ch\timesplinter\template\TagNode as TagNode;

/**
 * @author Pascal Münst
 * @copyright Copyright (c) 2012, Pascal Münst
 * @version 1.0
 */
class CheckboxOptionsTag extends TemplateTag implements TagNode {

	private $tagName = 'options';

	public function __construct() {
		echo 'CheckboxOptionsTag.class.php TO BE DONE!!!!';
		exit;
		parent::__construct(false);
	}

	public function replaceNode(TemplateEngine $tplEngine, ElementNode $node) {
		// DATA
		$compareArr = explode('|', $node->getAttribute('checked')->value);
		$dataKey = $node->getAttribute('options')->value;

		$textContent = '<?php foreach($this->getData(\'' . $dataKey . '\') as $key => $val) {
			$checked = (in_array($key, $this->getData(\'' . $compareValue . '\')))?\' checked="checked"\':\'\';
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