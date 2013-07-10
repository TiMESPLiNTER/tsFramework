<?php

/**
 * @author Pascal Muenst <entwicklung@metanet.ch>
 * @copyright Copyright (c) 2013, METANET AG
 * @version 1.0.0
 */

namespace ch\timesplinter\common;


class ArrayUtils {
	public static function getLevelFromArray($array, $levelFrom, $levelCount = 1) {
		$newArray = array();

		foreach($array as $k => $v) {

			if(!is_array($v)) {
				var_dump($v);
				continue;
			} else {
				var_dump($levelCount, $levelFrom);
				if($levelCount >= $levelFrom) {
					$newArray[$k] = $v;
				} else {
					echo 'called!';
					$newArray += self::getLevelFromArray($v, $levelFrom, ($levelCount + 1));
				}
			}
		}

		var_dump($newArray); echo '<hr>';

		return $newArray;
	}
}

/* EOF */