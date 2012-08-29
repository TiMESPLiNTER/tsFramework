<?php

/**
 * LoadSubTplTag
 *
 * @author				entwicklung@metanet.ch
 * @copyright	Copyright (c) 2012, METANET AG, www.metanet.ch
 * @version			1
 */
class LoadSubTplTag extends TemplateTag implements TagNode {

	private $tagName = 'loadSubTpl';

	public function __construct() {
		parent::__construct(false);
	}

	public function replaceNode(TemplateEngine $tplEngine, ElementNode $node) {
		$dataKey = $node->getAttribute('tplfile')->value;

		$dataKeyOld = $dataKey;

		if($dataKey === '{this}')
			$dataKey = $tplEngine->getData('pagesDir');

		/* $newTpl = new TemplateEngine($dataKey,$tplEngine->getTplNsPrefix());
		  $newTpl->setCacheDir($tplEngine->getCacheDir());
		  $newTpl->setAllData($tplEngine->getAllData());

		  $newTpl->parse();
		  $newTpl->cache(); */

		/** @var TextNode */
		$newNode = new TextNode($tplEngine->getDomReader());
		$newNode->content = '<?php LoadSubTplTag::requireFile(\'' . $dataKeyOld . '\',$this); ?>'; //$newTpl->getResultAsHtml();

		$node->parentNode->replaceNode($node, $newNode);
	}

	public function replaceInline(TemplateEngine $tplEngine, $nodeStr) {
		throw new TemplateEngineException("Don't use this tag (LoadSubTpl) inline!");
	}

	public function getTagName() {
		return $this->tagName;
	}

	/**
	 * A special method that belongs to the LoadSubTplTag class but needs none
	 * static properties from this class and is called from the cached template
	 * files.
	 * @param string $file The full filepath to include (OR magic {this})
	 */
	public static function requireFile($file, TemplateEngine $tplEngine) {
		if($file === '{this}')
			$file = $tplEngine->getData('pagesDir');

		$tplEngineNew = new TemplateEngine($tplEngine->getTemplateCache(), $file, $tplEngine->getTplNsPrefix());

		$tplEngineNew->setAllData($tplEngine->getAllData());
		$tplEngineNew->parse();

		$tplEngineNew->getResultAsHtml();
	}

}

?>