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
//	private $callbackMethods;

	private $cached;

	/** @var TemplateCache */
	private $templateCache;

	/** @var TemplateTag */
	private $lastTplTag;
	private $logger;

	/**
	 * 
	 * @param TemplateCache $tplCache
	 * @param string $tplFile
	 * @param string $tplNsPrefix
	 * @return TemplateEngine
	 */
	public function __construct(TemplateCache $tplCache, $tplNsPrefix) {
		$this->logger = FrameworkLoggerFactory::getLogger($this);

		$this->templateCache = $tplCache;
		$this->tplNsPrefix = $tplNsPrefix;

		$this->dataPool = new ArrayObject();
	}

	private function load() {
		$this->lastTplTag = null;

		$this->htmlDoc->parse();

		$nodeList = $this->htmlDoc->getNodeTree()->childNodes;

		if(count($nodeList) === 0)
			throw new TemplateEngineException('That\'s no valid template-file');

		try {
			self::copyNodes($nodeList);
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
						$attrs[$i]->value = self::replInlineTag($attrs[$i]->value);
				}
			} else {
				
				if($node instanceof TextNode || $node instanceof CommentNode || $node instanceof CDataSectionNode)
					$node->content = self::replInlineTag($node->content);
				
				continue;
			}
			
			if(count($node->childNodes) > 0)
				self::copyNodes($node->childNodes);

			if($node->namespace !== $this->tplNsPrefix)
				continue;

			$tagClassName = 'ch\\timesplinter\\customtags\\' . ucfirst($node->tagName) . 'Tag';
			
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
			$tagClassName = 'ch\\timesplinter\\customtags\\' . ucfirst($inlineTags[$j][1]) . 'Tag';
			$tag = new $tagClassName;

			if($tag instanceof TemplateTag === false) {
				$this->templateCache->setSaveOnDestruct(false);
				throw new TemplateEngineException('The class "' . $tagClassName . '" does not implement the abstract class "TemplateTag" and is so not recognized as an illegal class for a custom tag."');
			}

			if($tag instanceof TagInline === false)
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
				$repl = $tag->replaceInline($this, $params);
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
	 * @return boolean Is file cached or not
	 */
	private function isTplFileCached($filePath) {
		if(file_exists($filePath) === false)
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
	 * @return type
	 */
	public function getResultAsHtml($tplFile, $tplVars = array()) {
		$this->dataPool = new ArrayObject($tplVars);
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
		
		if(file_exists($tplFile) === false)
			throw new TemplateEngineException('Template file \'' . $tplFile . '\' does not exists');
		
		/** @var TemplateCacheEntry */
		$cacheEntry = $this->templateCache->getCachedTplFile($tplFile);
		$fileSize = @filesize($tplFile);

		$changeTime = @filemtime($tplFile);
		$changeTimeReal = ($changeTime !== false) ? $changeTime : @filectime($tplFile);
		
		// Render tpl
		$content = file_exists($tplFile) ? file_get_contents($tplFile):null;
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
		
		if(file_exists($cacheFileName) === true && is_writable($cacheFileName) === false)
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
	 */
	public function addData($key, $value, $overwrite = false) {
		if($this->dataPool->offsetExists($key) === true && $overwrite === false) {
			$this->logger->debug('current data print', array($this->dataPool));
			throw new TemplateEngineException("Data with the key '" . $key . "' is already registered");
		}

		$this->dataPool->offsetSet($key, $value);
	}

	/**
	 * Returns a registered data entry with the given key
	 * @param string $key The key of the data element
	 * @return mixed The value for that key or the key itselfs
	 */
	public function getData($key) {
		if($this->dataPool->offsetExists($key) === false) {
			$this->logger->debug('Key not found in datapool',array($key));
			return null;
		}

		return $this->dataPool->offsetGet($key);
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

	/*
	  public function registerCallback($callbackEntry) {
	  $this->callbackMethods[] = $callbackEntry;
	  }
	 */

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
	
	public static function checkRequiredAttrs($contextTag, $attrs) {
		foreach($attrs as $a) {
			$val = $contextTag->getAttribute($a)->value;
			
			if($val !== null)
				continue;
			
			throw new TemplateEngineException('Could not parse the template: Missing attribute \'' . $a .'\' for custom tag \'' . $contextTag->tagName . '\' on line ' . $contextTag->line);
		}

		return true;
	}


	public function getSelectorAsPHPStr($selectorStr, $echo = false) {
		if(StringUtils::startsWith($selectorStr, '${') === true)
			return $selectorStr;

		$selParts = explode('.', $selectorStr);
		$firstPart = array_shift($selParts);

		if($this->dataPool->offsetExists($firstPart) === false) {
			throw new TemplateEngineException('The data with offset "' . $firstPart . '" does not exist.');
		}

		$varData = $this->dataPool->offsetGet($firstPart);

		$selPHPStr = "\$this->getData('" . $selectorStr . "')";

		if(is_array($varData) === true) {
			$selPHPStr = "((object)\$this->getData('" . $firstPart . "'))" . ((count($selParts) > 0)?'->' . implode('->', $selParts):null);
		} elseif(is_object($varData) === true) {
			// TODO: Make this recursive through all $selParts array entries
			$getProperty = new \ReflectionProperty($varData, $selParts[0]);

			if($getProperty->isPrivate()) {
				$selParts[0] = 'get' . ucfirst($selParts[0]) . '()';
			}

			$selPHPStr = "\$this->getData('" . $firstPart . "')" . ((count($selParts) > 0)?'->' . implode('->', $selParts):null);
		} else {
			$selPHPStr = "\$this->getData('" . $selectorStr . "')";
		}

		$returnVal = ($echo)?'<?php echo ' . $selPHPStr . '; ?>':$selPHPStr;

		return $returnVal;
	}

	public function getSelectorValue($selectorStr) {
		$selParts = explode('.', $selectorStr);
		$firstPart = array_shift($selParts);
		$properties = null;

		if($this->dataPool->offsetExists($firstPart) === false) {
			throw new TemplateEngineException('The data with offset "' . $firstPart . '" does not exist.');
		}

		$varData = $this->dataPool->offsetGet($firstPart);

		if(is_array($varData) === true) {
			$varData = ((object)$firstPart);


			if(count($selParts) > 0) {
				$selectorOther = implode('->', $selParts);
				return $varData->{$selectorOther};
			}

			return $varData;
		}

		if(is_object($varData) === true) {
			$getProperty = new \ReflectionProperty($varData, $selParts[0]);

			if($getProperty->isPrivate()) {
				$selParts[0] = 'get' . ucfirst($selParts[0]) . '()';
			}

			$selPHPStr = $firstPart;
			$properties = ((count($selParts) > 0)?implode('->', $selParts):null);
		} else {
			$selPHPStr = $selectorStr;
		}

		$returnVal = call_user_func(array($this, 'getData'), $selPHPStr);

		if($properties !== null) {
			return $returnVal->{$properties};
		}

		return $returnVal;
	}

	public static function getVar($selector) {

	}
}

/* EOF */