<?php
namespace ch\timesplinter\customtags;

/**
 *
 *
 * @author				entwicklung@metanet.ch
 * @copyright	Copyright (c) 2012, METANET AG, www.metanet.ch
 * @version			1
 */
class RadioOptionsTag extends TemplateTag implements TagNode {

	private $tagName = 'radioOptions';

	public function __construct() {
		parent::__construct(false);
	}

	public function replaceNode(TemplateEngine $tplEngine, ElementNode $node) {
		// DATA
		$dataKey = $node->getAttribute('options')->value;
		$asVar = $node->getAttribute('selvar')->value;
		//$dataArr = $tplEngine->getData($dataKey);

		$cachedHtml = ltrim($node->getInnerHtml());


		// place selected
		$cachedHtml = preg_replace('/(.*<input.*type="radio".*?)(>.*)/', '$1<?php echo $selected; ?>$2', $cachedHtml);

		$entryVals = array('{key}' => '<?php echo $key; ?>', '{label}' => '<?php echo $label; ?>');

		$cachedHtml = str_replace(array_keys($entryVals), $entryVals, $cachedHtml);

		$textContent = '<?php foreach($this->getData(\'' . $dataKey . '\') as $key => $label) { $selected = ($this->getData(\'' . $asVar . '\') == $key)?\' checked="checked"\':null; ?>' . $cachedHtml . '<?php } ?>';

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