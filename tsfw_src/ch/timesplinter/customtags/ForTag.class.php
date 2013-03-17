<?php
namespace ch\timesplinter\customtags;

use ch\timesplinter\logger\TSLogger;
use
 ch\timesplinter\template\TemplateEngine
,ch\timesplinter\template\TemplateTag
,ch\timesplinter\template\TagNode
,ch\timesplinter\htmlparser\HtmlNode
,ch\timesplinter\htmlparser\ElementNode
,ch\timesplinter\htmlparser\TextNode
,ch\timesplinter\logger\LoggerFactory
;

/**
 * @author Pascal Münst <entwicklung@metanet.ch>
 * @copyright Copyright (c) 2012, METANET AG, www.metanet.ch
 * @version 1.0.1
 * 
 * @change 2012-10-03 Changed the way the for-tag accesses template data during runtime (pam)
 */
class ForTag extends TemplateTag implements TagNode {
	private $logger;
	
	public function __construct() {
		$this->logger = TSLogger::getLoggerByName('dev',$this);
		
		parent::__construct('for', false);
	}

	public function replaceNode(TemplateEngine $tplEngine, ElementNode $node) {
		// DATA
		TemplateEngine::checkRequiredAttrs($node, array('value','var'));
		
		$dataKeyAttr = $node->getAttribute('value')->value;
		$asVarAttr = $node->getAttribute('var')->value;
		$stepAttr = $node->getAttribute('groups');
		
		$dataKey = $dataKeyAttr;
		$asVar = $asVarAttr;
		$step = ($stepAttr->value === null)?1:intval($stepAttr->value);
		
		
		$firstClassAttr = $node->getAttribute('classfirst');
		$firstClass = ($firstClassAttr !== null)?$firstClassAttr->value:null;
		
		$lastClassAttr = $node->getAttribute('classlast');
		$lastClass = ($lastClassAttr !== null)?$lastClassAttr->value:null;
		$forUID = uniqid();
		
		$phpVar = '$' . str_replace('.', '->', $dataKey);
		
		self::str_replace_node($node->childNodes);

		$nodeForStart = new TextNode($tplEngine->getDomReader());
		$nodeForStart->content = "<?php\n";
		$nodeForStart->content .= "\$arr_{$forUID} = (isset({$phpVar}) === false)?\$this->getData('" . $dataKey . "'):{$phpVar};\n";
		$nodeForStart->content .= "\$arrCount_{$forUID} = count(\$arr_{$forUID});\n";
		$nodeForStart->content .= "\$i_{$forUID} = 0;\n";
		
		$nodeForStart->content .= "for(\$i_{$forUID} = 0; \$i_{$forUID} < \$arrCount_{$forUID}; \$i_{$forUID} = \$i_{$forUID}+{$step}) {\n";
		
		if($step === 1) {
			$nodeForStart->content .= "\t\${$asVar} = \$arr_{$forUID}[\$i_{$forUID}];\n";
		} else {
			for($i = 0; $i < $step; $i++) {
				$nodeForStart->content .= "\t\${$asVar}" . ($i+1) . " = (isset(\$arr_{$forUID}[\$i_{$forUID}+{$i}]) === true)?\$arr_{$forUID}[\$i_{$forUID}+{$i}]:null;\n";
			}
		}
		
		$nodeForStart->content .= "?>";
		
		$nodeForEnd = new TextNode($tplEngine->getDomReader());
		$nodeForEnd->content =  '<?php } ?>';

		$node->parentNode->insertBefore($nodeForStart, $node);
			
		// No fist/last class magic
		if($firstClass === null && $lastClass === null) {
			$txtForNode = new TextNode($tplEngine->getDomReader());
			$txtForNode->content = $node->getInnerHtml();
			$node->parentNode->insertBefore($txtForNode, $node);

			$node->parentNode->insertBefore($nodeForEnd, $node);
			$node->parentNode->removeNode($node);

			return;
		}

		$forDOM = new HtmlDoc($node->getInnerHtml(), null);
		$forDOM->parse();
		
		foreach($forDOM->getNodeTree()->childNodes as $forNode) {
			if(($forNode instanceof ElementNode) === false)
				continue;

			$classAttr = $forNode->getAttribute('class');
			$classVal = $classAttr->value;

			if($classVal === null) {
				$firstClassStr = ($firstClass !== null)?' class="' . $firstClass . '"':null;
				$lastClassStr = ($lastClass !== null)?' class="' . $lastClass . '"':null;
				$firstLastClassStr = ' class="' . (($firstClass !== null && $lastClass !== null)?$firstClass . ' ' . $lastClass:(($firstClass !== null)?$firstClass:$lastClass)) . '"';
				
				$firstLast = "<?php echo ((\$arrCount_{$forUID} === 1)?'{$firstLastClassStr}':(\$i_{$forUID} === 0)?'{$firstClassStr}':((\$arrCount_{$forUID} === \$i_{$forUID}+1)?'{$lastClassStr}':null)); ?>";
			} else {
				$space = ($classVal !== '')?' ':null;
				
				$firstClassStr = ($firstClass !== null)?$space . $firstClass:null;
				$lastClassStr = ($lastClass !== null)?$space . $lastClass:null;
				$firstLastClassStr = (($firstClass !== null && $lastClass !== null)?$space . $firstClass . ' ' . $lastClass:(($firstClass !== null)?$space . $firstClass:$space . $lastClass));
				
				$firstLast = ' class="' . $classVal . '<?php echo (($arrCount_' . $forUID . ' === 1)?\'' . $firstLastClassStr . '\':(($i_' . $forUID . ' === 0)?\'' . $firstClassStr . '\':(($arrCount_' . $forUID . ' === $i_' . $forUID . '+1)?\'' . $lastClassStr . '\':null))); ?>"';
			}

			$forNode->tagExtension = $firstLast;
			$forNode->removeAttribute('class');
		}

		$txtForNode = new TextNode($tplEngine->getDomReader());
		$txtForNode->content = $forDOM->getHtml();
		$node->parentNode->insertBefore($txtForNode, $node);
		
		$node->parentNode->insertBefore($nodeForEnd, $node);
		$node->parentNode->removeNode($node);
	}

	private function str_replace_node($nodeList) {
		$pattern1 = '/\$\{(?:(\d+?)\:)?(\w+?)(?:\.([\w|\.]+?))?\}/';
		$pattern2 = '/\{(?:(\d+?)\:)?(\w+?)(?:\.([\w|\.]+?))?\}/';
		
		foreach($nodeList as $node) {
			$t1 = preg_replace_callback($pattern1, array($this,'replaceVar'), $node->content);
			$node->content = preg_replace_callback($pattern2, array($this,'replaceEcho'), $t1);
			
			if($node->nodeType !== HtmlNode::ELEMENT_NODE)
				continue;
			
			foreach($node->attributes as $attr) {
				$attr->value = preg_replace_callback($pattern2, array($this,'replaceEcho'), $attr->value);
			}
			
			if($node->tagExtension !== null) {
				$node->tagExtension = preg_replace_callback($pattern1, array($this,'replaceVar'), $node->tagExtension);
			}
			
			if(count($node->childNodes) > 0) {
				self::str_replace_node($node->childNodes);
			}
		}
		
		return $nodeList;
	}
	
	public function replaceEcho($m) {
		return '<?php echo $' . $m[2] . ((is_numeric($m[1]) === true)?$m[1]:null) . '->' . str_replace('.','->',$m[3]) . '; ?>';
	}
	
	public function replaceVar($m) {
		return '$' . $m[2] . ((is_numeric($m[1]) === true)?$m[1]:null) . '->' . str_replace('.','->',$m[3]);
	}
}

?>