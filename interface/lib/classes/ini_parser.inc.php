<?php

class ini_parser{

	private $config;

	//* Converts a ini string to array
	public function parse_ini_string($ini) {
		$this->config = array();
		$ini = str_replace("\r\n", "\n", $ini);
		$lines = explode("\n", $ini);
		foreach($lines as $line) {
			$line = trim($line);
			if($line != '') {
				if(preg_match("/^\[([\w\d_]+)\]$/", $line, $matches)) {
					$section = strtolower($matches[1]);
				} elseif(preg_match("/^([\w\d_]+)=(.*)$/", $line, $matches) && $section != null) {
					$item = trim($matches[1]);
					$this->config[$section][$item] = trim($matches[2]);
				}
			}
		}
		return $this->config;
	}


	//* Converts a config array to a string
	public function get_ini_string($config_array = '') {
		if($config_array == '') $config_array = $this->config;
		$content = '';
		foreach($config_array as $section => $data) {
			$content .= "[$section]\n";
			foreach($data as $item => $value) {
				if($item != ''){
					$value  = trim($value);
					$item  = trim($item);
					$content .= "$item=$value\n";
				}
			}
			$content .= "\n";
		}
		return $content;
	}

}

?>
