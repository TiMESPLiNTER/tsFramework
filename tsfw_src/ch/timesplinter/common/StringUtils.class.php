<?php
namespace ch\timesplinter\common;

/**
 * Some useful functions for string operations
 * @author Pascal Muenst <dev@timesplinter.ch>
 * @copyright Copyright (c) 2013 by TiMESPLiNTER Webdevelopment
 * @version 1.0.0
 */
class StringUtils {
	/**
	 * @param string $str
	 * @param string $start
	 * @param string $end
	 * @return null|string
	 */
	public static function between($str, $start, $end) {
        $posStart = strpos($str, $start) + strlen($start);
        $posEnd = strrpos($str, $end, $posStart);
        
		if($posEnd === false)
			return null;
		
        return substr($str, $posStart, $posEnd-$posStart);   
    }

	/**
	 * @param string $str
	 * @param string $before
	 * @return string
	 */
	public static function beforeFirst($str, $before) {
        $posUntil = strpos($str, $before);
        
        if($posUntil === false)
            return $str;
        
        return substr($str, 0, $posUntil);
    }

	/**
	 * @param string $str
	 * @param string $before
	 * @return string
	 */
	public static function beforeLast($str, $before) {
        $posUntil = strrpos($str, $before);
        
        if($posUntil === false)
            return $str;
        
        return substr($str, 0, $posUntil);
    }

	/**
	 * @param string $str
	 * @param string $after
	 * @return null|string
	 */
	public static function afterLast($str, $after) {
        $posFrom = strrpos($str, $after);
        
        if($posFrom === false)
            return null;
        
        return substr($str, $posFrom+strlen($after));
    }

	/**
	 * @param string $str
	 * @param string $after
	 * @return null|string
	 */
	public static function afterFirst($str, $after) {
        $posFrom = strpos($str, $after);
        
        if($posFrom === false)
            return '';

		$afterStr = substr($str, $posFrom+strlen($after));

        return ($afterStr !== false)?$afterStr:'';
    }

	/**
	 * @param string $str
	 * @param string $startStr
	 * @return bool
	 */
	public static function startsWith($str, $startStr) {
		return (strpos($str, $startStr) === 0);
	}

	/**
	 * @param string $str
	 * @param string $endStr
	 * @return bool
	 */
	public static function endsWith($str, $endStr) {
		$endStrlen = strlen($endStr);
		
		return (strrpos($str, $endStr)+$endStrlen === strlen($str));
	}

	/**
	 * @param string $str The string to split
	 * @param string $token The tokens to split the string
	 * @return array The splitted parts
	 */
	public static function tokenize($str, $token) {
		$tokenArr = array();
		$tokStr = strtok($str, $token);

		while($tokStr !== false) {
			$tokenArr[] = $tokStr;

			$tokStr = strtok($token);
		}

		return $tokenArr;
	}

	/**
	 * @param string|array $tokens
	 * @param string $str
	 * @return array
	 */
	public static function explode($tokens, $str) {
		$strToExplode = $str;
		$explodeStr = $tokens;

		if(is_array($tokens) === true) {
			$explodeStr = chr(31);
			$strToExplode = str_replace($tokens, $explodeStr, $str);
		}

		return explode($explodeStr, $strToExplode);
	}

	/**
	 * @param string $str The string to urlify
	 * @param int $maxLength The max length of the urlified string. 0 is no length limit.
	 * @return string The urlified string
	 */
	public static function urlify($str, $maxLength = 0) {
		$charMap = array(
			' ' => '-', '.' => '', ':' => '', ',' => '', '?' => '', '!' => '', '´' => '', '"' => '',
			'(' => '', ')' => '', '[' => '', ']' => '', '{' => '', '}' => '', '\'' => '',

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