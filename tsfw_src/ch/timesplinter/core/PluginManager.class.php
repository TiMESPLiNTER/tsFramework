<?php
/**
 * @author pascal91
 * @copyright Copyright (c) 2013, Pascal Muenst
 * @version 1.0.0
 */

namespace ch\timesplinter\core;


class PluginManager {
	private $core;
	private $plugins;

	public function __construct(Core $core) {
		$this->core = $core;
		$this->plugins = array();
	}

	public function loadPlugins($plugins) {
		foreach($plugins as $plugin) {
			$pluginClass = str_replace(':','\\',$plugin);

			$this->plugins[] = new $pluginClass($this->core);
		}
	}


	public function invokePluginHook($hookname) {
		foreach($this->plugins as $plugin) {
			if(method_exists($plugin, $hookname) === false)
				continue;

			$plugin->$hookname();
		}
	}
}