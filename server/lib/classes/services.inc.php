<?php

class services {

	var $registered_services = array();
	var $delayed_restarts = array();
	var $debug = false;

	// This function adds a request for restarting
	// a service at the end of the configuration run.
	function restartServiceDelayed($service_name, $action = 'restart') {
		global $app;
		if(is_array($this->registered_services[$service_name])) {
			$this->delayed_restarts[$service_name] = $action;
		} else {
			$app->log("Unable to add a delayed restart for '$service_name'. Service not registered.", LOGLEVEL_WARN);
		}

	}

	// This function restarts a service when the function is called
	function restartService($service_name, $action = 'restart') {
		global $app;

		if(is_array($this->registered_services[$service_name])) {
			$module_name = $this->registered_services[$service_name]['module'];
			$function_name = $this->registered_services[$service_name]['function'];
			$app->log("Calling function '$function_name' from module '$module_name'.", LOGLEVEL_DEBUG);
			// call_user_method($function_name,$app->loaded_modules[$module_name],$action);
			return call_user_func(array($app->loaded_modules[$module_name], $function_name), $action);
		} else {
			$app->log("Unable to restart $service_name. Service not registered.", LOGLEVEL_WARNING);
			return array('output' => '', 'retval' => 0);
		}

	}

	// This function is used to register callback functions for services that can be restarted
	function registerService($service_name, $module_name, $function_name) {
		global $app;
		$this->registered_services[$service_name] = array('module' => $module_name, 'function' => $function_name);
		if($this->debug) $app->log("Registered Service '$service_name' in module '$module_name' for processing function '$function_name'", LOGLEVEL_DEBUG);
	}

	// This function is called at the end of the server script to restart services.
	function processDelayedActions() {
		global $app;
		foreach($this->delayed_restarts as $service_name => $action) {
			$this->restartService($service_name, $action);
		}
	}

}

?>
