<?php

/**
 * Description of Observer
 *
 * @author pascal91
 */
interface Observer {
	public function update(Observable $observable, $arg);
}

?>
