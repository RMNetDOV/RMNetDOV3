<?php

class ids {

	public function start()
	{
		global $app, $conf;
		
		$security_config = $app->getconf->get_security_config('ids');
		
		set_include_path(
			get_include_path()
			. PATH_SEPARATOR
			. RMNETDOV_CLASS_PATH.'/'
		);
			
		require_once(RMNETDOV_CLASS_PATH.'/IDS/Init.php');
		require_once(RMNETDOV_CLASS_PATH.'/IDS/Monitor.php');
		require_once(RMNETDOV_CLASS_PATH.'/IDS/Filter.php');
		require_once(RMNETDOV_CLASS_PATH.'/IDS/Filter/Storage.php');
		require_once(RMNETDOV_CLASS_PATH.'/IDS/Report.php');
		require_once(RMNETDOV_CLASS_PATH.'/IDS/Event.php');
		require_once(RMNETDOV_CLASS_PATH.'/IDS/Converter.php');
		
		$ids_request = array(
			'GET' => $_GET,
			'POST' => $_POST,
			'COOKIE' => $_COOKIE
		);
		
		$ids_init = IDS\Init::init(RMNETDOV_CLASS_PATH.'/IDS/Config/Config.ini.php');
		
		$ids_init->config['General']['base_path'] = RMNETDOV_CLASS_PATH.'/IDS/';
		$ids_init->config['General']['tmp_path'] = '../../../temp';
		$ids_init->config['General']['use_base_path'] = true;
		$ids_init->config['Caching']['caching'] = 'none';
		$ids_init->config['Logging']['path'] = '../../../temp/ids.log';
		
		$current_script_name = trim($_SERVER['SCRIPT_NAME']);
		
		// Get whitelist
		$whitelist_path = '/usr/local/rmnetdov/security/ids.whitelist';
		if(is_readable('/usr/local/rmnetdov/security/ids.whitelist.custom')) $whitelist_path = '/usr/local/rmnetdov/security/ids.whitelist.custom';
		if(!is_file($whitelist_path)) $whitelist_path = realpath(RMNETDOV_ROOT_PATH.'/../security/ids.whitelist');
		
		$whitelist_lines = file($whitelist_path);
		if(is_array($whitelist_lines)) {
			foreach($whitelist_lines as $line) {
				$line = trim($line);
				if(substr($line,0,1) != '#') {
					list($user,$path,$varname) = explode(':',$line);
					if($current_script_name == $path) {
						if($user = 'any' 
							|| ($user == 'user' && ($_SESSION['s']['user']['typ'] == 'user' || $_SESSION['s']['user']['typ'] == 'admin')) 
							|| ($user == 'admin' && $_SESSION['s']['user']['typ'] == 'admin')) {
								$ids_init->config['General']['exceptions'][] = $varname;
								
						}
					}
				}
			}
		}
		
		// Get HTML fields
		$htmlfield_path = '/usr/local/rmnetdov/security/ids.htmlfield';
		if(is_readable('/usr/local/rmnetdov/security/ids.htmlfield.custom')) $htmlfield_path = '/usr/local/rmnetdov/security/ids.htmlfield.custom';
		if(!is_file($htmlfield_path)) $htmlfield_path = realpath(RMNETDOV_ROOT_PATH.'/../security/ids.htmlfield');
		
		$htmlfield_lines = file($htmlfield_path);
		if(is_array($htmlfield_lines)) {
			foreach($htmlfield_lines as $line) {
				$line = trim($line);
				if(substr($line,0,1) != '#') {
					list($user,$path,$varname) = explode(':',$line);
					if($current_script_name == $path) {
						if($user = 'any' 
							|| ($user == 'user' && ($_SESSION['s']['user']['typ'] == 'user' || $_SESSION['s']['user']['typ'] == 'admin')) 
							|| ($user == 'admin' && $_SESSION['s']['user']['typ'] == 'admin')) {
								$ids_init->config['General']['html'][] = $varname;
						}
					}
				}
			}
		}
		
		$ids = new IDS\Monitor($ids_init);
		$ids_result = $ids->run($ids_request);
		
		if (!$ids_result->isEmpty()) {
			
			$impact = $ids_result->getImpact();
			
			// Choose level from security config
			if($app->auth->is_admin()) {
				// User is admin
				$ids_log_level = $security_config['ids_admin_log_level'];
				$ids_warn_level = $security_config['ids_admin_warn_level'];
				$ids_block_level = $security_config['ids_admin_block_level'];
			} elseif(is_array($_SESSION['s']['user']) && $_SESSION['s']['user']['userid'] > 0) {
				// User is Client or Reseller
				$ids_log_level = $security_config['ids_user_log_level'];
				$ids_warn_level = $security_config['ids_user_warn_level'];
				$ids_block_level = $security_config['ids_user_block_level'];
			} else {
				// Not logged in
				$ids_log_level = $security_config['ids_anon_log_level'];
				$ids_warn_level = $security_config['ids_anon_warn_level'];
				$ids_block_level = $security_config['ids_anon_block_level'];
			}
			
			if($impact >= $ids_log_level) {
				$ids_log = RMNETDOV_ROOT_PATH.'/temp/ids.log';
				if(!is_file($ids_log)) touch($ids_log);
				
				$user = isset($_SESSION['s']['user']['typ'])?$_SESSION['s']['user']['typ']:'any';
				
				$log_lines = '';
				foreach ($ids_result->getEvents() as $event) {
					$log_lines .= $user.':'.$current_script_name.':'.$event->getName()."\n";
				}
				file_put_contents($ids_log,$log_lines,FILE_APPEND);
				
			}
			
			if($impact >= $ids_warn_level) {
				$app->log("PHP IDS Alert.".$ids_result, 2);
			}
			
			if($impact >= $ids_block_level) {
				$app->error("Possible attack detected. This action has been logged.",'', true, 2);
			}
			
		}
	}
	
}

?>
