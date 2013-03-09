<?php

/**
 * Description of PHibernateCommon
 *
 * @author Pascal Münst
 * @copyright Copyright (c) 2012, Actra AG
 * @version 1.0
 */
class PHibernateCommon {
	/**
	 * Increases an entry in a counter ArrayObject
	 * @param ArrayObject $counter
	 * @param mixed $index The index to increase
	 * @return mixed The new value on the index position 
	 */
	public static function getIncreasedCounter($counter, $index) {
		$newVal = 0;
		
		if($counter->offsetExists($index) === true) {
			$oldVal = $counter->offsetGet($index);
			$newVal = $oldVal + 1;
		}

		$counter->offsetSet($index, $newVal);

		return $newVal;
	}
}

?>
