<?php

/**
 * Description of RequestHandler
 *
 * @author pascal91
 */
class RequestHandler {
    const PROTOCOL_HTTP = 'http';
    const PROTOCOL_HTTPS = 'https';
    
    private static $instance = null;
    
    private $requestArray;
    private $requestUri;
    private $requestProtocol;
    private $requestMethod;
    private $requestReferer;
    
    private function __construct() {
        $this->requestUri = $_SERVER['REQUEST_URI'];
        $this->requestArray = self::parseRequestArray($this->requestUri);
        $this->requestProtocol = (array_key_exists('HTTPS', $_SERVER) === true && $_SERVER['HTTPS'] === 'on')?self::PROTOCOL_HTTPS:self::PROTOCOL_HTTP;
        $this->requestMethod = $_SERVER['REQUEST_METHOD'];
        $this->requestReferer = (array_key_exists('HTTP_REFERER', $_SERVER))?$_SERVER['HTTP_REFERER']:null;
        
        var_dump( $this->requestProtocol, $this->requestMethod , $this->requestReferer );
    }
    
    /**
     * 
     * @return RequestHandler
     */
    public static function getInstance() {
        if(self::$instance === null) {
            $c = __CLASS__;
            self::$instance = new $c;
        }
        
        return self::$instance;
    }
    
    private static function parseRequestArray($reqUri) {
        $reqArray = array();
        
        $pathArr = explode('/',substr($reqUri,1));
        $reqFile = array_pop($pathArr);
        
        $reqArray['path'] = $pathArr;
        
        $reqFileParams = StringUtils::before($reqFile, '.');
        $reqArray['fileExt'] = StringUtils::after($reqFile, '.');
        
        $reqFileParamsArr = explode('-', $reqFileParams);
        $reqArray['fileName'] = array_shift($reqFileParamsArr);
                
        $reqArray['params'] = $reqFileParamsArr;
        
        echo'<pre>'; var_dump($reqArray); echo'</pre>';
        
        return $reqArray;
    }
    
    public function getRequestArray() {
        return $this->requestArray;
    }
    
    public function getRequestURI() {
        return $this->requestUri;
    }
}

?>