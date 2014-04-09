<?php

namespace ch\timesplinter\core;

/**
 * Class Observer
 *
 * @author Pascal Münst
 * @copyright (c) 2012, Pascal Münst
 */
interface Observer {
	public function update(Observable $observable, $arg);
}

/* EOF */