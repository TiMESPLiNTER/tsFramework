<?php

namespace ch\timesplinter\customtags;

use ch\timesplinter\template\TemplateEngine;
use ch\timesplinter\template\TagNode;
use ch\timesplinter\template\TemplateTag;
use ch\timesplinter\htmlparser\ElementNode;
use ch\timesplinter\htmlparser\HtmlAttribute;

/**
 * @author Pascal Münst <entwicklung@metanet.ch>
 * @copyright (c) 2012, METANET AG
 * @version 1.0.0
 */
class CheckboxTag extends TemplateTag implements TagNode {
	public function __construct() {
		parent::__construct('checkbox', false);
	}

	public function replaceNode(TemplateEngine $tplEngine, ElementNode $node) {
		// DATA
		$sels = $node->getAttribute('selection')->value;
		$selsStr = $tplEngine->getSelectorAsPHPStr($sels);
		$value = $node->getAttribute('value')->value;
		$node->removeAttribute('selection');
		
		$node->namespace = null;
		$node->tagName = 'input';
		if($sels !== null)
			$node->tagExtension = " <?php echo ((is_array({$selsStr}) && in_array({$value}, {$selsStr})) || ({$selsStr} == {$value}))?' checked':null; ?>";
		$node->addAttribute(new HtmlAttribute('type', 'checkbox'));
		
		/*$node->parentNode->insertBefore($newNode, $node);
		$node->parentNode->removeNode($node);*/
	}
}

/* EOF */