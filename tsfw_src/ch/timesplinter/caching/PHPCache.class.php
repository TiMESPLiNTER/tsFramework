<?php
namespace ch\timesplinter\caching;

/**
 * Description of PHPCache
 *
 * @author pascal91
 */
class PHPCache extends Cache {
	private $logger;
	
	private $store;
	
	public function __construct() {
		parent::__construct();
		
		$this->store = array();
		$this->logger = LoggerFactory::getLoggerByName('dev', $this);
	}
	
	public function init(){
	}

	public function getValue($key){
	}
	
	public function getAllValues() {
		return $this->store;
	}
	
	public function storeValue($key, $value){
		$this->store[$key] = $value;
		$this->cacheChanged = true;
	}
	
	public function load() {
		$cacheFile = FW_DIR . 'cache/sample.cache';
		
		if(file_exists($cacheFile) === false)
			return;
		
		$cacheArr = array();
		
		require $cacheFile;
		
		$this->store = $cacheArr;
	}
	
	public function save() {
		$cacheContent = '<?php' . "\n\n" . '// Created with PHPCache 1.0' . "\n";
		
		foreach($this->store as $key => $entry)
			$cacheContent .= self::getCacheContent($key, $entry);
	
		
		$cacheContent .= "\n" . '?>';
		
		file_put_contents(FW_DIR . 'cache/sample.cache', $cacheContent);
		
		$this->logger->debug('cache content:' , array($cacheContent));
	}
	
	private function getCacheContent($key, $var) {
		$cacheContent = '';
		
		if(is_array($key)) {
			$keyEscaped = '';
			foreach($key as $subKey)
				$keyEscaped .= '[' . ((is_string($subKey) === true)?'\'' . $subKey . '\'':$subKey) . ']';
		} else {
			$keyEscaped = '[' . ((is_string($key) === true)?'\'' . $key . '\'':$key) . ']';
		}
		
		if(is_array($var)) {
			// go deeper
			$cacheContent .= '$cacheArr' . $keyEscaped . ' = array();' . "\n";
			foreach($var as $sunKey => $re)
				$cacheContent .= self::getCacheContent(array_merge((is_array($key) === true )?$key: array($key), array($sunKey)), $re);
		} elseif(is_object($var)) {
			// create object in php
			$cacheContent .= self::getObjectAsPHPStr($keyEscaped, $var);
		} elseif(is_bool($var)) {
			$cacheContent .= '$cacheArr' . $keyEscaped . ' = ' . (($var === true)?'true':'false') . ';' . "\n";
		} else{
			// simpli add to an array
			$cacheContent .= '$cacheArr' . $keyEscaped . ' = ' . ((is_string($var) === true)?'\'' . $var . '\'':$var) . ';' . "\n";
		}
		
		return $cacheContent;
	}
	
	private function getObjectAsPHPStr($keyEscaped, $obj) {
		/*$objClass = get_class($obj);
		
		$reflection = new ReflectionClass($obj);
		$constructor =  $reflection->getConstructor(); // NULL if no constructor
		$this->logger->debug('reflection: ' , array($constructor));
		
		return '$cacheArr' . $keyEscaped . ' = new ' . $objClass . '();' . "\n";*/
		return '$cacheArr' . $keyEscaped . ' = unserialize(\'' . serialize($obj) . '\');' . "\n";
	}
	
	public function close() {
		if($this->cacheChanged === false)
			return;
		
		self::save();
	}
	
	public function __destruct() {
		self::close();
	}
}

?>
