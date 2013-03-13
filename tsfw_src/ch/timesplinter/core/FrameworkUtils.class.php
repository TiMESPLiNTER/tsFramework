<?php
namespace ch\timesplinter\core;

/**
 * Description of FrameworkUtils
 *
 * @author pascal91
 */
class FrameworkUtils {
	const OS_WINDOWS = 'win';
	const OS_LINUX = 'lin';
	const OS_DARWIN = 'dar';
	const OS_FREEBSD = 'fre';
	const OS_NETBSD = 'net';
	const OS_UNIX = 'uni';
	const OS_OPENBSD = 'ope';
	const OS_HPUX = 'hp-';
	const OS_SUNOS = 'sun';
	
	public static function isOS($os) {
		$currOS = null;
		
		if(defined('PHP_OS') === true)
			$currOS = strtolower(substr(PHP_OS,0,3));
		elseif(function_exists('php_uname') === true)
			$currOS = strtolower(substr(php_uname('s'),0,3));
		
		if($currOS === null)
			return false;
		
		return (is_array($os) === true)?in_array($currOS, $os):($currOS === $os);
	}

    public static function stringToClassName($str, $seperator = ':') {
        $classParts = explode($seperator, $str);

        $returnValue = new \stdClass();

        $returnValue->methodName = array_pop($classParts);
        $returnValue->className = implode('/', $classParts);

        return $returnValue;
    }
	
	public static function preparePath($pathStr) {
		return str_replace(array('/','\\'), array(DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR), $pathStr);
	}
}

/* EOF */