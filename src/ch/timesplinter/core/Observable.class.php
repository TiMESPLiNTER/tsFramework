<?php
namespace ch\timesplinter\core;

/**
 * @author Pascal Münst
 * @copyright (c) 2012, Pascal Münst
 * @version 1.0
 */
abstract class Observable {
    protected $observers;
    protected $changed;
 
    /**
    * Constructs the Observerable object
    */
    protected function Observable() {
        $this->observers = array();
		$this->changed = false;
    }
 
    /**
    * Calls the update() function using the reference to each
    * registered observer - used by children of Observable
    * @return void
    */ 
    public function notifyObservers($arg) {
        if($this->changed === false)
			return;
		
		$observers = count($this->observers);
		
        for ($i=0; $i<$observers; ++$i) {
            $this->observers[$i]->update($this, $arg);
        }
    }
 
    /**
    * Register the reference to an object object
    * @return void
    */ 
    public function addObserver(&$observer) {
        $this->observers[] = &$observer;
    }
	
	public function countObservers() {
		return count($this->observers);
	}
	
	public function deleteObservers() {
		$this->observers = array();
	}
	
	public function setChanged() {
		$this->changed = true;
	}
	
	public function clearChanged() {
		$this->changed = false;
	}
	
	public function hasChanged() {
		return $this->changed;
	}
}

/* EOF */