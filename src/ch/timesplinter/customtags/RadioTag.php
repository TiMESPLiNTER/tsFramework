<?php

namespace ch\timesplinter\customtags;

use ch\timesplinter\template\TemplateEngine;
use ch\timesplinter\template\TagNode;
use ch\timesplinter\template\TemplateTag;
use ch\timesplinter\htmlparser\ElementNode;
use ch\timesplinter\htmlparser\HtmlAttribute;

/**
 * @author Pascal Münst <dev@timesplinter.ch>
 * @copyright (c) 2012, TiMESPLiNTER Webdevelopment
 */
class RadioTag extends TemplateTag implements TagNode
{
	public function replaceNode(TemplateEngine $tplEngine, ElementNode $node)
	{
		// DATA
		$sels = $node->getAttribute('selection')->value;
		$selsStr = '$this->getDataFromSelector(\'' . $sels . '\')';
		$value = $node->getAttribute('value')->value;
		$node->removeAttribute('selection');
		
		$node->namespace = null;
		$node->tagName = 'input';
		if($sels !== null)
			$node->tagExtension = " <?php echo ((is_array({$selsStr}) && in_array({$value}, {$selsStr})) || ({$selsStr} == '{$value}'))?' checked':null; ?>";

		$node->addAttribute(new HtmlAttribute('type', 'radio'));
	}

	/**
	 * @return string
	 */
	public static function getName()
	{
		return 'radio';
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