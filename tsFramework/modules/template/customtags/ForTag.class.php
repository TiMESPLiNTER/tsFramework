<?php

/**
 *
 *
 * @author				entwicklung@metanet.ch
 * @copyright	Copyright (c) 2012, METANET AG, www.metanet.ch
 * @version			1
 */
class ForTag extends TemplateTag implements TagNode {

	private $tagName = 'for';

	public function __construct() {
		parent::__construct(false);
	}

	public function replaceNode(TemplateEngine $tplEngine, ElementNode $node) {
		// DATA
		$dataKey = $node->getAttribute('value')->value;
		$asVar = $node->getAttribute('var')->value;
		$dataArr = $tplEngine->getData($dataKey);

		$cachedHtml = ltrim($node->getInnerHtml());


		$entryVals = self::getEntryVals(array_shift($dataArr), $asVar);

		$cachedHtml = str_replace(array_keys($entryVals), $entryVals, $cachedHtml);

		$textContent = '<?php foreach($this->getData(\'' . $dataKey . '\') as $' . $asVar . ') { ?>' . $cachedHtml . '<?php } ?>';

		$newNode = new TextNode($tplEngine->getDomReader());
		$newNode->content = $textContent;

		$node->parentNode->insertBefore($newNode, $node);
		$node->parentNode->removeNode($node);
	}

	private static function getEntryVals($entry, $asVar) {
		$entryVals = array();

		if(is_array($entry) === true) {
			foreach($entry as $keySubEntry => $subEntry)
				$entryVals = $entryVals + self::getEntryVals($subEntry, $asVar . '.' . $keySubEntry);
		} else {
			$values = explode('.', $asVar);

			if(count($values) === 1) {
				$entryVals['{' . $asVar . '}'] = '<?php echo $' . $values[0] . '; ?>';
			} else {
				$value = '<?php echo $' . $values[0] . '[\'';
				unset($values[0]);

				$entryVals['{' . $asVar . '}'] = $value . implode('\'][\'', $values) . '\']; ?>';
			}
		}

		return $entryVals;
	}

	public function getTagName() {
		return $this->tagName;
	}

}

?>