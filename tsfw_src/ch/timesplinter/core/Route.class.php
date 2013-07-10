<?php
namespace ch\timesplinter\core;

/**
 * Description of Route
 *
 * @author pascal91
 */
class Route {
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
	public $method;
	public $controllerClass;
	public $controllerMethod;
	public $params;
}

/* EOF */