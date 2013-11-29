<?php
namespace ch\timesplinter\template;

use ch\timesplinter\common\StringUtils;
use ch\timesplinter\core\FrameworkLoggerFactory;
use ch\timesplinter\logger\LoggerFactory;
use ch\timesplinter\htmlparser\HtmlDoc;
use ch\timesplinter\htmlparser\ElementNode;
use ch\timesplinter\htmlparser\TextNode;
use ArrayObject;
use \Exception;

/**
 * TemplateEngine
 *
 * @author Pascal Münst <entwicklung@metanet.ch>
 * @copyright Copyright (c) 2012, METANET AG, www.metanet.ch
 * @version 1.0
 */
class TemplateEngine {

	const CACHE_SUBFIX = '.cache';

	/** @var HtmlDoc */
	private $htmlDoc;
	private $tplNsPrefix;
	private $dataPool;
	private $dataTable;
	private $customTags;
//	private $callbackMethods;

	private $cached;

	/** @var TemplateCache */
	private $templateCache;
	private $currentTemplateFile;

	/** @var TemplateTag */
	private $lastTplTag;
	private $logger;

	private $getterMethodPrefixes;

	/**
	 * 
	 * @param TemplateCache $tplCache The template cache object
	 * @param string $tplNsPrefix The prefix for custom tags in the template file
	 * @param array $customTags Additional custom tags to be loaded
	 * @return TemplateEngine A template engine instance to render files
	 */
	public function __construct(TemplateCache $tplCache, $tplNsPrefix, array $customTags = array()) {
		$this->logger = FrameworkLoggerFactory::getLogger($this);

		$this->templateCache = $tplCache;
		$this->tplNsPrefix = $tplNsPrefix;
		$this->customTags = array_merge($this->getDefaultCustomTags(), $customTags);

		$this->dataPool = new ArrayObject();
		$this->dataTable = new ArrayObject();

		$this->getterMethodPrefixes = array('get', 'is', 'has');
	}

	private function getDefaultCustomTags() {
		return array(
			'text' => 'ch\timesplinter\customtags\TextTag',
			'checkboxOptions' => 'ch\timesplinter\customtags\CheckboxOptionsTag',
			'checkbox' => 'ch\timesplinter\customtags\CheckboxTag',
			'date' => 'ch\timesplinter\customtags\DateTag',
			'else' => 'ch\timesplinter\customtags\ElseTag',
			'forgroup' => 'ch\timesplinter\customtags\ForgroupTag',
			'for' => 'ch\timesplinter\customtags\ForTag',
			'if' => 'ch\timesplinter\customtags\IfTag',
			'inUsergroup' => 'ch\timesplinter\customtags\InUsergroupTag',
			'lang' => 'ch\timesplinter\customtags\LangTag',
			'loadSubTpl' => 'ch\timesplinter\customtags\LoadSubTplTag',
			'options' => 'ch\timesplinter\customtags\OptionsTag',
			'option' => 'ch\timesplinter\customtags\OptionTag',
			'radioOptions' => 'ch\timesplinter\customtags\RadioOptionsTag',
			'radio' => 'ch\timesplinter\customtags\RadioTag',
			'subNavi' => 'ch\timesplinter\customtags\SubNaviTag',
			'subSite' => 'ch\timesplinter\customtags\SubSiteTag'
		);
	}

	private function load() {
		$this->lastTplTag = null;

		$this->htmlDoc->parse();

		$nodeList = $this->htmlDoc->getNodeTree()->childNodes;

		if(count($nodeList) === 0)
			throw new TemplateEngineException('That\'s no valid template-file');

		try {
			$this->copyNodes($nodeList);
		} catch(DOMException $e) {
			throw new TemplateEngineException('Error while processing the template file: ' . $e->getMessage());
		}
	}
	
	private function copyNodes($nodeList) {
		
		
		foreach($nodeList as $node) {
			// Parse inline tags if activated
			if($node instanceof ElementNode === true) {

				$attrs = $node->attributes;
				$countAttrs = count($attrs);

				if($countAttrs > 0) {
					for($i = 0; $i < $countAttrs; $i++)
						$attrs[$i]->value = $this->replInlineTag($attrs[$i]->value);
				}
			} else {
				if($node instanceof TextNode || /*$node instanceof CommentNode ||*/ $node instanceof CDataSectionNode)
					$node->content = $this->replInlineTag($node->content);
				
				continue;
			}
			
			if(count($node->childNodes) > 0)
				$this->copyNodes($node->childNodes);

			if($node->namespace !== $this->tplNsPrefix)
				continue;

			if(isset($this->customTags[$node->tagName]) === false)
				throw new TemplateEngineException('The custom tag "' . $node->tagName . '" is not registered in this template engine instance');

			$tagClassName = $this->customTags[$node->tagName];

			if(class_exists($tagClassName) === false)
				throw new TemplateEngineException('The Tag "' . $tagClassName . '" does not exist');

			// Initiate Tag-Class and call replace()-Method
			$tagInstance = new $tagClassName;

			if($tagInstance instanceof TemplateTag === false) {
				$this->templateCache->setSaveOnDestruct(false);
				throw new TemplateEngineException('The class "' . $tagClassName . '" does not implement the abstract class "TemplateTag" and is so not recognized as an illegal class for a custom tag."');
			}

			try {
				$tagInstance->replaceNode($this, $node);
			} catch(TemplateEngineException $e) {
				$this->templateCache->setSaveOnDestruct(false);
				throw $e;
			}

			$this->lastTplTag = $tagInstance;
		}
		//exit;
	}

	private function replInlineTag($value) {
		$inlineTags = null;
		
		preg_match_all('@\{' . $this->tplNsPrefix . ':(.+?)(?:\\s+(\\w+=\'.+?\'))?\\s*\}@', $value, $inlineTags, PREG_SET_ORDER);
		
		if(count($inlineTags) <= 0)
			return $value;

		for($j = 0; $j < count($inlineTags); $j++) {
			$tagName = $inlineTags[$j][1];

			if(isset($this->customTags[$tagName]) === false)
				throw new TemplateEngineException('The custom tag "' . $tagName . '" is not registered in this template engine instance');

			$tagClassName = $this->customTags[$tagName];

			$tagInstance = new $tagClassName;

			if($tagInstance instanceof TemplateTag === false) {
				$this->templateCache->setSaveOnDestruct(false);
				throw new TemplateEngineException('The class "' . $tagClassName . '" does not implement the abstract class "TemplateTag" and is so not recognized as an illegal class for a custom tag."');
			}

			if($tagInstance instanceof TagInline === false)
				throw new TemplateEngineException('CustomTag "' . $tagClassName . '" is not allowed to use inline.');

			// Params
			$params = $parsedParams = array();

			if(array_key_exists(2, $inlineTags[$j])) {
				preg_match_all('@(?:(\\w+)=\'(.+?)\')@', $inlineTags[$j][2], $parsedParams, PREG_SET_ORDER);

				$countParams = count($parsedParams);
				for($p = 0; $p < $countParams; $p++)
					$params[$parsedParams[$p][1]] = $parsedParams[$p][2];
			}

			try {
				$repl = $tagInstance->replaceInline($this, $params);
				$value = str_replace($inlineTags[$j][0], $repl, $value);
			} catch(TemplateEngineException $e) {
				$this->templateCache->setSaveOnDestruct(false);
				throw $e;
			}
		}

		return $value;
	}

	/**
	 * This method parses the given template file
	 * @param string $tplFile The path to the template file to parse
	 * @return string The parsed template
	 */
	public function parse($tplFile) {
		$this->cached = $this->isTplFileCached($tplFile);
		
		// PARSE IT NEW: No NodeList given? Okay! I'll load defaults for you
		if($this->cached !== null)
			return $this->cached;
		
		return $this->cache($tplFile);
	}

	/**
	 * Returns if file is cached or not
	 * @param string $filePath Path to the templace file that should be checked
	 * @throws TemplateEngineException
	 * @return boolean Is file cached or not
	 */
	private function isTplFileCached($filePath) {
		if(stream_resolve_include_path($filePath) === false)
			throw new TemplateEngineException('Could not find template file: ' . $filePath);

		/** @var TemplateCacheEntry */
		$tplCacheEntry = $this->templateCache->getCachedTplFile($filePath);

		if($tplCacheEntry === null)
			return null;

		$changeTime = @filemtime($filePath);
		$changeTimeReal = ($changeTime !== false) ? $changeTime : @filectime($filePath);

		if($tplCacheEntry->size !== @filesize($filePath) || $tplCacheEntry->changeTime !== $changeTimeReal) {
			$this->templateCache->getCachedTplFile($filePath)->size = -1;
			return null;
		}

		return $tplCacheEntry->ID;
	}

	/**
	 * Returns the final HTML-code or includes the cached file (if caching is
	 * enabled)
	 * @param $tplFile
	 * @param array $tplVars
	 * @throws \Exception
	 * @return type
	 */
	public function getResultAsHtml($tplFile, $tplVars = array()) {
		$this->currentTemplateFile = $tplFile;
		$this->dataPool = new ArrayObject($tplVars);
		$this->dataTable = new ArrayObject();
		$cacheID = $this->parse($tplFile);

		try {
			ob_start();
			require $this->templateCache->getCachePath() . $cacheID . '.cache';
			return ob_get_clean();
		} catch(Exception $e) {
			// Throw away the whole template code till now
			ob_clean();

			// Throw the exception again
			throw $e;
		}
	}

	private function cache($tplFile) {
		$cacheFileName = null;
		
		if(stream_resolve_include_path($tplFile) === false)
			throw new TemplateEngineException('Template file \'' . $tplFile . '\' does not exists');
		
		/** @var TemplateCacheEntry */
		$cacheEntry = $this->templateCache->getCachedTplFile($tplFile);
		$fileSize = @filesize($tplFile);

		$changeTime = @filemtime($tplFile);
		$changeTimeReal = ($changeTime !== false) ? $changeTime : @filectime($tplFile);
		
		// Render tpl
		$content = file_get_contents($tplFile);
		$this->htmlDoc = new HtmlDoc($content, $this->tplNsPrefix);
		$this->htmlDoc->addSelfClosingTag('tst:text');
		$this->htmlDoc->addSelfClosingTag('tst:lang');
		$this->htmlDoc->addSelfClosingTag('tst:loadSubTpl');
		$this->htmlDoc->addSelfClosingTag('tst:checkbox');

		$this->load();

		$htmlToReturn = $this->htmlDoc->getHtml();
		$this->templateCache->setSaveOnDestruct(false);
		
		$cacheId = null;

		if($cacheEntry === null) {
			$cacheId = uniqid();
			$this->templateCache->addCachedTplFile($tplFile, $cacheId, $fileSize, $changeTimeReal);
		} else {
			$cacheId = $cacheEntry->ID;
			$this->templateCache->addCachedTplFile($tplFile, $cacheId, $fileSize, $changeTimeReal);
		}

		$cacheFileName = $this->templateCache->getCachePath() . $cacheId . self::CACHE_SUBFIX;
		
		if(stream_resolve_include_path($cacheFileName) === true && is_writable($cacheFileName) === false)
			throw new TemplateEngineException('Cache file is not writeable: ' . $cacheFileName);

		$fp = @fopen($cacheFileName, 'w');

		if($fp !== false) {
			fwrite($fp, $htmlToReturn);
			fclose($fp);

			$this->templateCache->setSaveOnDestruct(true);
		} else {
			$this->logger->error('Could not cache template-file: ' . $cacheFileName);
		}

		$this->logger->debug('Tpl-File (re-)cached: ' . $tplFile . ' -> ' . $cacheId);
		
		return $cacheId;
	}

	/**
	 *
	 * @return HtmlDoc
	 */
	public function getDomReader() {
		return $this->htmlDoc;
	}

	/**
	 * Checks if a template node is followed by another template tag with a
	 * specific tagname.
	 * @param type $tagNode The template tag
	 * @param type $tagName The tagname of the following template tag
	 * @return type
	 */
	public function isFollowedBy($tagNode, $tagName) {
		$nextSibling = $tagNode->getNextSibling();

		if($nextSibling !== null && $nextSibling->namespace === $this->getTplNsPrefix() && $nextSibling->tagName === $tagName)
			return true;

		return false;
	}

	/**
	 * Register a value to make it accessable for the engine
	 * @param string $key
	 * @param mixed $value
	 * @param boolean $overwrite
	 * @throws TemplateEngineException
	 */
	public function addData($key, $value, $overwrite = false) {
		if($this->dataPool->offsetExists($key) === true && $overwrite === false) {
			$this->logger->debug('current data print', array($this->dataPool));
			throw new TemplateEngineException("Data with the key '" . $key . "' is already registered");
		}

		$this->dataPool->offsetSet($key, $value);
	}

	public function unsetData($key) {
		if($this->dataPool->offsetExists($key) === false)
			return;

		$this->dataPool->offsetUnset($key);
	}

	/**
	 * Returns a registered data entry with the given key
	 * @param string $key The key of the data element
	 * @return mixed The value for that key or the key itselfs
	 */
	public function getData($key) {
		if($this->dataPool->offsetExists($key) === false)
			return null;

		return $this->dataPool->offsetGet($key);
	}

	public function getDataFromSelector($selector) {
		return $this->getSelectorValue($selector);
	}

	public function setAllData($dataPool) {
		foreach($dataPool as $key => $val)
			$this->dataPool->offsetSet($key, $val);
	}

	public function getAllData() {
		return $this->dataPool;
	}

	public function getTplNsPrefix() {
		return $this->tplNsPrefix;
	}

	public function getTemplateCache() {
		return $this->templateCache;
	}

	/**
	 * Returns the latest template tag found by the engine
	 * @return TemplateTag
	 */
	public function getLastTplTag() {
		return $this->lastTplTag;
	}

	/**
	 * @return string The template file path which gets parsed at the moment
	 */
	public function getCurrentTemplateFile() {
		return $this->currentTemplateFile;
	}
	
	public static function checkRequiredAttrs($contextTag, $attrs) {
		foreach($attrs as $a) {
			$val = $contextTag->getAttribute($a)->value;
			
			if($val !== null)
				continue;
			
			throw new TemplateEngineException('Could not parse the template: Missing attribute \'' . $a .'\' for custom tag \'' . $contextTag->tagName . '\' on line ' . $contextTag->line);
		}

		return true;
	}

	/**
	 * @param $selectorStr
	 * @param bool $echo
	 * @return string
	 * @throws TemplateEngineException
	 */
	public function getSelectorAsPHPStr($selectorStr, $echo = false) {
		if(StringUtils::startsWith($selectorStr, '${') === true)
			return $selectorStr;

		$selParts = explode('.', $selectorStr);
		$firstPart = array_shift($selParts);
		$currentSel = $firstPart;

		if($this->dataPool->offsetExists($firstPart) === false)
			throw new TemplateEngineException('The data with offset "' . $currentSel . '" does not exist.');

		$varData = $this->dataPool->offsetGet($firstPart);
		$varSelector = '$this->getData(\'' . $firstPart . '\')';

		foreach($selParts as $part) {
			if(is_object($varData) === true && ($varData instanceof ArrayObject) === false) {

				if(property_exists($varData, $part) === true) {
					$getProperty = new \ReflectionProperty($varData, $part);

					$varData = ($getProperty->isPrivate() || $getProperty->isProtected())
						?call_user_func(array($varData, 'get' . ucfirst($part)))
						:$varData->$part;

					$varSelector .= '->' . (($getProperty->isPrivate() || $getProperty->isProtected())
						?'get' . ucfirst($part) . '()'
						:$part);

					continue;
				}

				$varData = call_user_func(array($varData, 'get' . ucfirst($part)));
				$varSelector .= '->get' . ucfirst($part) . '()';

			} elseif(is_array($varData) === true) {
				if(array_key_exists($part, $varData) === false)
					throw new TemplateEngineException('Array key "' . $part . '" does not exist in array "' . $currentSel . '"');

				$varData = $varData[$part];
				$varSelector .= '[' . $part . ']';
			} elseif(($varData instanceof ArrayObject) === true) {
				/** @var ArrayObject $varData */
				if($varData->offsetExists($part) === false)
					throw new TemplateEngineException('Array key "' . $part . '" does not exist in ArrayObject "' . $currentSel . '"');

				$varData = $varData->offsetGet($part);
				$varSelector .= '->offsetGet(' . $part . ')';
			} else {
				throw new TemplateEngineException('The data with offset "' . $currentSel . '" is not an object nor an array.');
			}

			$currentSel .= '.' . $part;
		}

		return ($echo)?'<?php echo ' . $varSelector . '; ?>':$varSelector;
	}

	/**
	 * @param $selectorStr
	 * @return mixed
	 * @throws TemplateEngineException
	 */
	/*private function getSelectorValue($selectorStr) {
		$selParts = explode('.', $selectorStr);
		$firstPart = array_shift($selParts);
		$currentSel = $firstPart;

		if($this->dataPool->offsetExists($firstPart) === false)
			throw new TemplateEngineException('The data with offset "' . $currentSel . '" does not exist.');

		$varData = $this->dataPool->offsetGet($firstPart);

		foreach($selParts as $part) {
			$newSelector = $currentSel . '.' . $part;

			// Try to find value in hashmap, thats faster then parse again
			if($this->dataTable->offsetExists($newSelector)) {
				$varData = $this->dataTable->offsetGet($newSelector);
				$currentSel = $newSelector;

				continue;
			}

			if(is_object($varData) === true) {
				$getProperty = new \ReflectionProperty($varData, $part);

				$varData = ($getProperty->isPrivate() || $getProperty->isProtected())
					?call_user_func(array($varData, 'get' . ucfirst($part)))
					:$varData->$part;

			} elseif(is_array($varData) === true) {
				if(array_key_exists($part, $varData))
					throw new TemplateEngineException('Array key "' . $part . '" does not exist in array "' . $currentSel . '"');

				$varData = $varData[$part];
			} else {
				throw new TemplateEngineException('The data with offset "' . $currentSel . '" is not an object nor an array.');
			}

			$currentSel = $newSelector;

			$this->dataTable->offsetSet($currentSel, $varData);
		}

		return $varData;
	}*/

	private function getSelectorValue($selectorStr, $returnNull = false) {
		$selParts = explode('.', $selectorStr);
		$firstPart = array_shift($selParts);
		$currentSel = $firstPart;

		if($this->dataPool->offsetExists($firstPart) === false) {
			if($returnNull === false)
				throw new TemplateEngineException('The data with offset "' . $currentSel . '" does not exist.');

			return null;
		}

		$varData = $this->dataPool->offsetGet($firstPart);
		//$tableKey =

		foreach($selParts as $part) {
			$nextSel = $currentSel . '.' . $part;

			// Try to find value in hashmap, thats faster then parse again
			/*if($this->dataTable->offsetExists($nextSel)) {
				$varData = $this->dataTable->offsetGet($nextSel);

				continue;
			}*/

			if(is_object($varData) === true && ($varData instanceof ArrayObject) === false) {
				if(property_exists($varData, $part) === true) {
					$getProperty = new \ReflectionProperty($varData, $part);

					if($getProperty->isPublic() === true) {
						$varData = $varData->$part;
					} else {
						$getterMethodName = null;

						foreach($this->getterMethodPrefixes as $mp) {
							$getterMethodName = $mp . ucfirst($part);

							if(method_exists($varData, $getterMethodName) === true)
								break;

							$getterMethodName = null;
						}

						if($getterMethodName === null)
							throw new TemplateEngineException('Could not access private property "' . $part . '". Please provide a getter method');

						$varData = call_user_func(array($varData, $getterMethodName));
					}

					/* OLD ONE (more performant) $varData = ($getProperty->isPrivate() || $getProperty->isProtected())
						?call_user_func(array($varData, 'get' . ucfirst($part)))
						:$varData->$part;*/
				} elseif(method_exists($varData, $part) === true) {
					$varData = call_user_func(array($varData, ucfirst($part)));
				} else {
					throw new TemplateEngineException('Don\'t know how to handle selector part "' . $part . '"');
				}

			} elseif(($varData instanceof ArrayObject) === true) {
				/** @var ArrayObject $varData */
				if($varData->offsetExists($part) === false)
					throw new TemplateEngineException('Array key "' . $part . '" does not exist in ArrayObject "' . $currentSel . '"');

				$varData = $varData->offsetGet($part);
			} elseif(is_array($varData) === true) {
				if(array_key_exists($part, $varData) === false)
					throw new TemplateEngineException('Array key "' . $part . '" does not exist in array "' . $currentSel . '"');

				$varData = $varData[$part];
			} else {
				throw new TemplateEngineException('The data with offset "' . $currentSel . '" is not an object nor an array.');
			}

			$currentSel = $nextSel;
			$this->dataTable->offsetSet($currentSel, $varData);
		}

		return $varData;
	}
}

/* EOF */