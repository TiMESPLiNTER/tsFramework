<?php

namespace ch\timesplinter\common;

use ch\timesplinter\core\FrameworkException;
use ch\timesplinter\core\JSONException;
use \Exception;

/**
 * Description of JsonUtils
 *
 * @author Pascal Muenst <dev@timesplinter.ch>
 * @copyright Copyright (c) 2013 by TiMESPLiNTER Webdevelopment
 * @version 1.0.0
 */
class JsonUtils {

	public static function decode($json, $toAssoc = false, $minified = true)  {
		if($minified === false) {
			$json = self::minify($json);
		}

		$result = json_decode($json, $toAssoc);

		$error = null;
		
        switch(json_last_error()) {
            case JSON_ERROR_DEPTH:
                $error =  'Maximum stack depth exceeded';
                break;
            case JSON_ERROR_CTRL_CHAR:
                $error = 'Unexpected control character found';
                break;
            case JSON_ERROR_SYNTAX:
                $error = 'Syntax error, malformed JSON markup';
                break;
            case JSON_ERROR_NONE:
	        case 0:
		        break;
            default:
                $error = 'Unknown error in JSON: ' . json_last_error();
        }

        if($error !== null)
            throw new JSONException('Invalid JSON code: ' . $error);
       
        return $result;
    }

	public static function decodeFile($filePath, $toAssoc = false, $minified = true) {
		if(file_exists($filePath) === false)
			throw new JSONException('JSON-File does not exist: ' . $filePath);

		try {
			return self::decode(file_get_contents($filePath), $toAssoc, $minified);
		} catch(\Exception $e) {

			throw new JSONException($e->getMessage() . ', File: ' . $filePath);
		}
	}

	/**
	 * JSON.minify()
	 * v0.1 (c) Kyle Simpson
	 * MIT License
	 */
	public static function minify($json) {
		$tokenizer = "/\"|(\/\*)|(\*\/)|(\/\/)|\n|\r/";
		$in_string = false;
		$in_multiline_comment = false;
		$in_singleline_comment = false;
		$tmp = null;
		$tmp2 = null;
		$new_str = array();
		$from = 0;
		$lc = null;
		$rc = null;
		$lastIndex = 0;

		while (preg_match($tokenizer, $json, $tmp, PREG_OFFSET_CAPTURE, $lastIndex)) {
			$tmp = $tmp[0];
			$lastIndex = $tmp[1] + strlen($tmp[0]);
			$lc = substr($json, 0, $lastIndex - strlen($tmp[0]));
			$rc = substr($json, $lastIndex);
			
			if (!$in_multiline_comment && !$in_singleline_comment) {
				$tmp2 = substr($lc, $from);
				if (!$in_string)
					$tmp2 = preg_replace("/(\n|\r|\s)*/", null, $tmp2);
				
				$new_str[] = $tmp2;
			}
			
			$from = $lastIndex;

			if ($tmp[0] == '"' && !$in_multiline_comment && !$in_singleline_comment) {
				preg_match("/(\\\\)*$/", $lc, $tmp2);
				
				if (!$in_string || !$tmp2 || (strlen($tmp2[0]) % 2) == 0) // start of string with ", or unescaped " character found to end string
					$in_string = !$in_string;
				
				$from--; // include " character in next catch
				$rc = substr($json, $from);
			} elseif ($tmp[0] == "/*" && !$in_string && !$in_multiline_comment && !$in_singleline_comment) {
				$in_multiline_comment = true;
			} elseif ($tmp[0] == "*/" && !$in_string && $in_multiline_comment && !$in_singleline_comment) {
				$in_multiline_comment = false;
			} elseif ($tmp[0] == "//" && !$in_string && !$in_multiline_comment && !$in_singleline_comment) {
				$in_singleline_comment = true;
			} elseif (($tmp[0] == "\n" || $tmp[0] == "\r") && !$in_string && !$in_multiline_comment && $in_singleline_comment) {
				$in_singleline_comment = false;
			} elseif (!$in_multiline_comment && !$in_singleline_comment && !(preg_match("/\n|\r|\s/", $tmp[0]))) {
				$new_str[] = $tmp[0];
			}
		}
		$new_str[] = $rc;
		return implode(null, $new_str);
	}

}

/* EOF */
