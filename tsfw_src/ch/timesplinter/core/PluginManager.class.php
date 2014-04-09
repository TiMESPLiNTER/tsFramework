<?php

namespace ch\timesplinter\core;

/**
 * @author Pascal Muenst <dev@timesplinter.ch>
 * @copyright Copyright (c) 2013, Pascal Muenst
 * @version 1.0.0
 */
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

	/**
	 * Invokes a specific plugin hook
	 * @param string $hookName The name of the hook to invoke
	 */
	public function invokePluginHook($hookName) {
		$args = func_get_args();
		array_shift($args);

		foreach($this->plugins as $plugin) {
			if(method_exists($plugin, $hookName) === false)
				continue;

			call_user_func_array(array($plugin, $hookName), $args);
		}
	}
}

/* EOF */