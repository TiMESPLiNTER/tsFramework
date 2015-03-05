<?php

namespace ch\timesplinter\core;

/**
 * @author Pascal Muenst <dev@timesplinter.ch>
 * @copyright Copyright (c) 2014 by TiMESPLiNTER Webdevelopment
 */
class FrameworkUtils
{
	const OS_WINDOWS = 'win';
	const OS_LINUX = 'lin';
	const OS_DARWIN = 'dar';
	const OS_FREEBSD = 'fre';
	const OS_NETBSD = 'net';
	const OS_UNIX = 'uni';
	const OS_OPENBSD = 'ope';
	const OS_HPUX = 'hp-';
	const OS_SUNOS = 'sun';

	/**
	 * @param string $os
	 *
	 * @return bool
	 */
	public static function isOS($os)
	{
		$currOS = null;
		
		if(defined('PHP_OS') === true)
			$currOS = strtolower(substr(PHP_OS,0,3));
		elseif(function_exists('php_uname') === true)
			$currOS = strtolower(substr(php_uname('s'),0,3));
		
		if($currOS === null)
			return false;
		
		return (is_array($os) === true)?in_array($currOS, $os):($currOS === $os);
	}

	/**
	 * @param string $str The full qualified namespace
	 * @param bool $methodIncluded
	 * @param string $separator
	 * 
	 * @return \stdClass Contains two properties: $className - The class name, $methodName - The method name
	 */
	public static function stringToClassName($str, $methodIncluded = true, $separator = ':')
	{
        $classParts = explode($separator, $str);

        $returnValue = new \stdClass();

		if($methodIncluded === true)
            $returnValue->methodName = array_pop($classParts);

        $returnValue->className = implode('\\', $classParts);

        return $returnValue;
    }

	/**
	 * @param string $pathStr
	 *
	 * @return string
	 */
	public static function preparePath($pathStr)
	{
		return str_replace(array('/','\\'), array(DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR), $pathStr);
	}
}

/* EOF */