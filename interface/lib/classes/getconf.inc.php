<?php

class getconf {

	private $config;
	private $security_config;

	public function get_server_config($server_id, $section = '') {
		global $app;

		if(!isset($this->config[$server_id])) {
			$app->uses('ini_parser');
			$server_id = $app->functions->intval($server_id);
			$server = $app->db->queryOneRecord('SELECT config FROM server WHERE server_id = ?', $server_id);
			$this->config[$server_id] = $app->ini_parser->parse_ini_string(stripslashes($server['config']));
		}
		return ($section == '') ? $this->config[$server_id] : $this->config[$server_id][$section];
	}

	public function get_global_config($section = '') {
		global $app;

		if(!isset($this->config['global'])) {
			$app->uses('ini_parser');
			$tmp = $app->db->queryOneRecord('SELECT config FROM sys_ini WHERE sysini_id = 1');
			$this->config['global'] = $app->ini_parser->parse_ini_string(stripslashes($tmp['config']));
		}
		return ($section == '') ? $this->config['global'] : $this->config['global'][$section];
	}
	
	// Function has been moved to $app->get_security_config($section)
	public function get_security_config($section = '') {
		global $app;
		
		if(is_array($this->security_config)) {
			return ($section == '') ? $this->security_config : $this->security_config[$section];
		} else {
			$app->uses('ini_parser');
			$security_config_path = '/usr/local/rmnetdov/security/security_settings.ini';
			if(!is_readable($security_config_path)) $security_config_path = realpath(RMNETDOV_ROOT_PATH.'/../security/security_settings.ini');
			$this->security_config = $app->ini_parser->parse_ini_string(file_get_contents($security_config_path));

			return ($section == '') ? $this->security_config : $this->security_config[$section];
		}
	}

}

?>
