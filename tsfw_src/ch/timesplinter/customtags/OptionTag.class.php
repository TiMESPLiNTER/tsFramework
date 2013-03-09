<?php
namespace ch\timesplinter\customtags;

use ch\timesplinter\template\TemplateEngine;
use ch\timesplinter\template\TemplateTag;
use ch\timesplinter\template\TagNode;
use ch\timesplinter\htmlparser\ElementNode;
use ch\timesplinter\htmlparser\HtmlAttribute;

/**
 * @author Pascal Münst <entwicklung@metanet.ch>
 * @copyright (c) 2012, METANET AG
 * @version 1.0
 */
class OptionTag extends TemplateTag implements TagNode {
	private $logger;
	
	public function __construct() {
		//$this->logger = LoggerFactory::getEnvLogger($this);
		parent::__construct('option', false, true);
	}

	public function replaceNode(TemplateEngine $tplEngine, ElementNode $node) {
		// DATA
		$sels = $node->getAttribute('selection')->value;
		$valueAttr = $node->getAttribute('value')->value;
		$value = is_numeric($valueAttr)?$valueAttr:"'" . $valueAttr . "'";
		$type = $node->getAttribute('type')->value;
		$node->removeAttribute('selection');
		
		$node->namespace = null;
		$node->tagName = 'input';
		if($sels !== null)
			$node->tagExtension = " <?php echo in_array({$value}, \$this->getData('{$sels}'))?' checked=\"checked\"':null; ?>";
		$node->addAttribute(new HtmlAttribute('type', $type));
		
		/*$node->parentNode->insertBefore($newNode, $node);
		$node->parentNode->removeNode($node);*/
	}
}

?>