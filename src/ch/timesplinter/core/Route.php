<?php

namespace ch\timesplinter\core;

/**
 * @author Pascal Muenst <dev@timesplinter.ch>
 * @copyright Copyright (c) 2014 by TiMESPLiNTER Webdevelopment
 */
class Route
{
	const METHOD_UNKNOWN = 'UNKNOWN';
	const METHOD_GET = 'GET';
	const METHOD_POST = 'POST';
	const METHOD_HEAD = 'HEAD';
	const METHOD_PUT = 'PUT';
	const METHOD_TRACE = 'TRACE';
	const METHOD_DELETE = 'DELETE';
	const METHOD_CONNECT = 'CONNECT';
	const METHOD_OPTIONS = 'OPTIONS';
	
	public $id;
	public $pattern;
	public $sslRequired;
	public $sslForbidden;
	public $methods;
	
	protected $params;
	
	public function __construct($params = array())
	{
		$this->params = $params;
	}
	
	public function getParams()
	{
		return $this->params;
	}
	
	public function getParam($key, $cleaned = true)
	{
		if(isset($this->params[$key]) === true)
			return ($cleaned === true)?strip_tags($this->params[$key]):$this->params[$key];
		return null;
	}
	
	public function setParams($params)
	{
		$this->params = $params;
	}
}
/* EOF */