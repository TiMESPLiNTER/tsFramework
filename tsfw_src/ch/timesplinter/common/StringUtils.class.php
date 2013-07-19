<?php
namespace ch\timesplinter\common;

/**
 * Some useful functions for string operations
 * @author Pascal Muenst <dev@timesplinter.ch>
 * @copyright Copyright (c) 2013 by TiMESPLiNTER Webdevelopment
 * @version 1.0.0
 */
class StringUtils {
    public static function between($str, $start, $end) {
        $posStart = strpos($str, $start) + strlen($start);
        $posEnd = strrpos($str, $end, $posStart);
        
		if($posEnd === false)
			return null;
		
        return substr($str, $posStart, $posEnd-$posStart);   
    }
    
	public static function beforeFirst($str, $before) {
        $posUntil = strpos($str, $before);
        
        if($posUntil === false)
            return $str;
        
        return substr($str, 0, $posUntil);
    }
	
    public static function beforeLast($str, $before) {
        $posUntil = strrpos($str, $before);
        
        if($posUntil === false)
            return $str;
        
        return substr($str, 0, $posUntil);
    }
    
    public static function afterLast($str, $after) {
        $posFrom = strrpos($str, $after);
        
        if($posFrom === false)
            return null;
        
        return substr($str, $posFrom+strlen($after));
    }
	
	public static function afterFirst($str, $after) {
        $posFrom = strpos($str, $after);
        
        if($posFrom === false)
            return null;
        
        return substr($str, $posFrom+strlen($after));
    }
	
	public static function startsWith($str, $startStr) {
		return (strpos($str, $startStr) === 0);
	}
	
	public static function endsWith($str, $endStr) {
		$endStrlen = strlen($endStr);
		
		return (strrpos($str, $endStr)+$endStrlen === strlen($str));
	}

	public static function urlify($str, $maxLength = 0) {
		$charMap = array(
			' ' => '-', '.' => '', ':' => '', ',' => '', '?' => '', '!' => '', '´' => '', '"' => '',
			'(' => '', ')' => '', '[' => '', ']' => '', '{' => '', '}' => '',

			// German
			'ä' => 'ae', 'ö' => 'oe', 'ü' => 'ue',

			// Francais
			'é' => 'e', 'è' => 'e', 'ê' => 'e', 'à' => 'a', 'â' => 'a', 'ç' => 'c', 'ï' => '', 'î' => '',

			// Espanol
			'ñ' => 'n', 'ó' => 'o', 'ú' => 'u', '¿' => '', '¡' => ''
		);

		$urlifiedStr = str_replace(array_keys($charMap), $charMap, strtolower(trim($str)));

		// Replace multiple dashes
		$urlifiedStr = preg_replace('/[-]{2,}/', '-', $urlifiedStr);

		if($maxLength === 0)
			return $urlifiedStr;

		return substr($urlifiedStr, 0, $maxLength);
	}
}

/* EOF */