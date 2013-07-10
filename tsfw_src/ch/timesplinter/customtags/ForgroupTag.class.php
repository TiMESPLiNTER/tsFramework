<?php

namespace ch\timesplinter\customtags;

use ch\timesplinter\core\FrameworkLoggerFactory;
use ch\timesplinter\template\TemplateTag;
use ch\timesplinter\template\TagNode;
use ch\timesplinter\template\TemplateEngine;
use ch\timesplinter\htmlparser\ElementNode;
use ch\timesplinter\htmlparser\TextNode;

class ForgroupTag extends TemplateTag implements TagNode {
	private $logger;
	
	private $var;
	private $no;
	
	public function __construct() {
		$this->logger = FrameworkLoggerFactory::getLogger($this);
		parent::__construct('forgroup', false);
	}
	
	public function replaceNode(TemplateEngine $tplEngine, ElementNode $node) {
		$var = $node->getAttribute('var')->value;
		
		$entryNoArr = explode(':', $var);
		$this->no = $entryNoArr[0];
		$this->var = $entryNoArr[1];
		
		TemplateEngine::checkRequiredAttrs($node, array('var'));
		
		$replNode = new TextNode($tplEngine->getDomReader());
		
		$replNode->content = "<?php if(isset(\${$this->var}{$this->no}) === true) { ?>";
		$replNode->content .= self::prepareHtml($node->getInnerHtml());
		$replNode->content .= "<?php } ?>";
		
		$node->getParentNode()->replaceNode($node, $replNode);
	}
	
	private function prepareHtml($html) {
		$newHtml = preg_replace_callback('/\{' . $this->var . '\.(.*?)\}/', array($this,'replace'), $html);
		$newHtmlRepl = preg_replace_callback('/\{(\w+?)(?:\.([\w|\.]+))?\}/', array($this,'replaceForeign'), $newHtml);
		
		return $newHtmlRepl;	
	}
	
	private function replaceForeign($matches) {
		return '<?php echo $' . $matches[1] . '->' . str_replace('.', '->', $matches[2]) . '; ?>';
	}
	
	private function replace($matches) {
		return '<?php echo $' . $this->var . $this->no . '->' . str_replace('.', '->', $matches[1]) . '; ?>';
	}
}

/* EOF */