<?php
namespace ch\timesplinter\customtags;

use 
	ch\timesplinter\template\TemplateTag,
	ch\timesplinter\htmlparser\TextNode,
	ch\timesplinter\htmlparser\ElementNode,
	ch\timesplinter\template\TagNode,
	ch\timesplinter\template\TemplateEngine
;

/**
 * LoadSubTplTag
 *
 * @author Pascal Münst <dev@timesplinter.ch>
 * @copyright Copyright (c) 2012, METANET AG, www.metanet.ch
 * @version 1.0
 */
class LoadSubTplTag extends TemplateTag implements TagNode {
	public function __construct() {
		parent::__construct('LoadSubTpl',false);
	}

	public function replaceNode(TemplateEngine $tplEngine, ElementNode $node) {
		$dataKey = $node->getAttribute('tplfile')->value;

		$tplFile = null;
		
		if(preg_match('/^\{(.+)\}$/', $dataKey, $res) !== false)
			$tplFile = 'self::getData(\'' . $res[1] . '\')';
		else
			$tplFile = '\'' . $dataKey . '\'';

		/* $newTpl = new TemplateEngine($dataKey,$tplEngine->getTplNsPrefix());
		  $newTpl->setCacheDir($tplEngine->getCacheDir());
		  $newTpl->setAllData($tplEngine->getAllData());

		  $newTpl->parse();
		  $newTpl->cache(); */

		/** @var TextNode */
		$newNode = new TextNode($tplEngine->getDomReader());
		$newNode->content = '<?php ' . __NAMESPACE__ . '\\LoadSubTplTag::requireFile(' . $tplFile . ',$this); ?>'; //$newTpl->getResultAsHtml();

		$node->parentNode->replaceNode($node, $newNode);
	}

	public function replaceInline(TemplateEngine $tplEngine, $nodeStr) {
		throw new TemplateEngineException('Don\'t use this tag (LoadSubTpl) inline!');
	}

	/**
	 * A special method that belongs to the LoadSubTplTag class but needs none
	 * static properties from this class and is called from the cached template
	 * files.
	 * @param string $file The full filepath to include (OR magic {this})
	 */
	public static function requireFile($file, TemplateEngine $tplEngine) {
		$tplEngineNew = new TemplateEngine($tplEngine->getTemplateCache(), $file, $tplEngine->getTplNsPrefix());

		$tplEngineNew->setAllData($tplEngine->getAllData());
		$tplEngineNew->parse();

		echo $tplEngineNew->getResultAsHtml();
	}

}

?>