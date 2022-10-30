<?php

class RMNetDOVRemotingHandlerBase
{
	protected $methods = array();
	protected $classes = array();

	public function __construct()
	{
		global $app;

		// load main remoting file
		$app->load('remoting');

		// load all remoting classes and get their methods
		$this->load_remoting_classes(realpath(__DIR__) . '/remote.d/*.inc.php');

		// load all remoting classes from modules
		$this->load_remoting_classes(realpath(__DIR__) . '/../../web/*/lib/classes/remote.d/*.inc.php');

		// add main methods
		$this->methods['login'] = 'remoting';
		$this->methods['logout'] = 'remoting';
		$this->methods['get_function_list'] = 'remoting';

		// create main class
		$this->classes['remoting'] = new remoting(array_keys($this->methods));
	}

	private function load_remoting_classes($glob_pattern)
	{
		$files = glob($glob_pattern);

		foreach ($files as $file) {
			$name = str_replace('.inc.php', '', basename($file));
			$class_name = 'remoting_' . $name;

			include_once $file;
			if(class_exists($class_name, false)) {
				$this->classes[$class_name] = new $class_name();
				foreach(get_class_methods($this->classes[$class_name]) as $method) {
					$this->methods[$method] = $class_name;
				}
			}
		}
	}
}
