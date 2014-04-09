<?php
namespace ch\timesplinter\common;

/**
 * Some useful functions for string operations
 * @package ch\timesplinter\common
 *
 * @author Pascal Muenst <dev@timesplinter.ch>
 * @copyright Copyright (c) 2013 by TiMESPLiNTER Webdevelopment
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

	public static function insertBeforeLast($str, $beforeLast, $newStr) {
		return self::beforeLast($str, $beforeLast) . $newStr . $beforeLast . self::afterLast($str, $beforeLast);
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
	 * @param string $printableCharReplacement Replacement char for incompatible printable chars
	 * @return string The urlified string
	 */
	public static function urlify($str, $maxLength = 0, $printableCharReplacement = '-') {
		$charMap = array(
			'!' => null, '"' => null,
			'#' => $printableCharReplacement . 'no' . $printableCharReplacement,
			'$' => $printableCharReplacement . 'dollar' . $printableCharReplacement,
			'%' => $printableCharReplacement . 'percentage' . $printableCharReplacement,
			'&' => $printableCharReplacement . 'and'. $printableCharReplacement,
			'\'' => null,
			'(' => $printableCharReplacement,
			')' => $printableCharReplacement,
			'*' => null,
			'+' => $printableCharReplacement . 'plus' . $printableCharReplacement,
			',' => null, '/' => $printableCharReplacement,
			':' => $printableCharReplacement,
			';' => $printableCharReplacement,
			'<' => null,
			'=' => $printableCharReplacement . 'equals' . $printableCharReplacement,
			'>' => null, '?' => null,
			'@' => $printableCharReplacement . 'at' . $printableCharReplacement,
			'[' => $printableCharReplacement,
			']' => $printableCharReplacement,
			'\\' => null, '^' => null, '`' => null,
			'{' => $printableCharReplacement,
			'}' => $printableCharReplacement,
			'|' => $printableCharReplacement,
			'~' => $printableCharReplacement,
			"\t" => null, "\n" => null, "\r" => null
		);

		$asciiStr = iconv('ASCII', 'UTF-8', iconv('UTF-8', 'ASCII//TRANSLIT', trim($str)));

		$urlifiedStr = str_replace(array_keys($charMap), $charMap, $asciiStr);

		// Replace multiple dashes
		$urlifiedStr = preg_replace('/[' . $printableCharReplacement . ']{2,}/', $printableCharReplacement, $urlifiedStr);

		if($maxLength === 0)
			return $urlifiedStr;

		return substr($urlifiedStr, 0, $maxLength);
	}
}

/* EOF */