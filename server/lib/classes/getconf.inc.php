<?php

class getconf {

	function get_server_config($server_id, $section = '') {
		global $app;

		$app->uses('ini_parser');
		$server_id = intval($server_id);
		$server = $app->db->queryOneRecord('SELECT config FROM server WHERE server_id = ?', $server_id);
		$config = $app->ini_parser->parse_ini_string(stripslashes($server['config']));

		if($section == '') {
			return $config;
		} else {
			return $config[$section];
		}
	}

	public function get_global_config($section = '') {
		global $app;

		$app->uses('ini_parser');
		$tmp = $app->db->queryOneRecord('SELECT config FROM sys_ini WHERE sysini_id = 1');
		$config = $app->ini_parser->parse_ini_string(stripslashes($tmp['config']));
		return ($section == '') ? $config : $config[$section];
	}
	
	public function get_security_config($section = '') {
		global $app;

		$app->uses('ini_parser');
		$security_config = $app->ini_parser->parse_ini_string(file_get_contents('/usr/local/rmnetdov/security/security_settings.ini'));

		return ($section == '') ? $security_config : $security_config[$section];
	}

}

?>
