<?php

namespace ch\timesplinter\customtags;

use ch\timesplinter\htmlparser\ElementNode;
use ch\timesplinter\htmlparser\TextNode;
use ch\timesplinter\template\TagNode;
use ch\timesplinter\template\TemplateEngine;
use ch\timesplinter\template\TemplateTag;

/**
 *
 *
 * @author Pascal Muenst <dev@timesplinter.ch>
 * @copyright Copyright (c) 2012, TiMESPLiNTER Webdevelopment
 */
class RadioOptionsTag extends TemplateTag implements TagNode
{
	public function replaceNode(TemplateEngine $tplEngine, ElementNode $node)
	{
		// DATA
		$tplEngine->checkRequiredAttrs($node, array('options', 'checked'));

		$compareArr = $tplEngine->getSelectorAsPHPStr($node->getAttribute('checked')->value);
		$dataKey = $node->getAttribute('options')->value;
		$fldName = $node->getAttribute('name')->value;

		$textContent = '<?php echo "<ul>";  foreach($this->getDataFromSelector(\'' . $dataKey . '\') as $key => $val) {
			$checked = in_array($key, ((array)' . $compareArr . '))?\' checked\':null;
			echo \'<li><label><input type="radio" value="\'.$key.\'" name="' . $fldName . '"\'.$checked.\'> \'.$val.\'</label></li>\' . "\n";
		} echo "</ul>"; ?>';

		$newNode = new TextNode($tplEngine->getDomReader());
		$newNode->content = $textContent;

		$node->parentNode->insertBefore($newNode, $node);
		$node->parentNode->removeNode($node);
	}

	/**
	 * @return string
	 */
	public static function getName()
	{
		return 'radioOptions';
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