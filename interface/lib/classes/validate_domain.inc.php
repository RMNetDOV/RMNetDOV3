<?php

class validate_domain {

	function get_error($errmsg) {
		global $app;

		if(isset($app->tform->wordbook[$errmsg])) {
			return $app->tform->wordbook[$errmsg]."<br>\r\n";
		} else {
			return $errmsg."<br>\r\n";
		}
	}

	/* Validator function for domain (website) */
	function web_domain($field_name, $field_value, $validator) {
		if(empty($field_value)) return $this->get_error('domain_error_empty');

		// do not allow wildcards on website domains
		$result = $this->_regex_validate($field_value);
		if(!$result) return $this->get_error('domain_error_regex');

		$result = $this->_check_unique($field_value);
		if(!$result) return $this->get_error('domain_error_unique');

		$pattern = '/\.acme\.invalid$/';
		if(preg_match($pattern, $field_value)) return $this->get_error('domain_error_acme_invalid');
	}

	/* Validator function for sub domain */
	function sub_domain($field_name, $field_value, $validator) {
		if(empty($field_value)) return $this->get_error('domain_error_empty');

		$allow_wildcard = $this->_wildcard_limit();
		if($allow_wildcard == false && substr($field_value, 0, 2) === '*.') return $this->get_error('domain_error_wildcard');

		$result = $this->_regex_validate($field_value, $allow_wildcard);
		if(!$result) return $this->get_error('domain_error_regex');

		$result = $this->_check_unique($field_value);
		if(!$result) return $this->get_error('domain_error_unique');

		$pattern = '/\.acme\.invalid$/';
		if(preg_match($pattern, $field_value)) return $this->get_error('domain_error_acme_invalid');
	}

	/* Validator function for alias domain */
	function alias_domain($field_name, $field_value, $validator) {
		if(empty($field_value)) return $this->get_error('domain_error_empty');

		// do not allow wildcards on alias domains
		$result = $this->_regex_validate($field_value);
		if(!$result) return $this->get_error('domain_error_regex');

		$result = $this->_check_unique($field_value);
		if(!$result) return $this->get_error('domain_error_unique');

		$pattern = '/\.acme\.invalid$/';
		if(preg_match($pattern, $field_value)) return $this->get_error('domain_error_acme_invalid');
	}

	/* Validator function for checking the auto subdomain of a web/aliasdomain */
	function web_domain_autosub($field_name, $field_value, $validator) {
		global $app;
		if(empty($field_value) || $field_name != 'subdomain') return; // none set

		if(isset($app->remoting_lib->primary_id)) {
			$check_domain = $app->remoting_lib->dataRecord['domain'];
		} else {
			$check_domain = $_POST['domain'];
		}

		$app->uses('ini_parser,getconf');
		$settings = $app->getconf->get_global_config('domains');
		if ($settings['use_domain_module'] == 'y') {
			$sql = "SELECT domain_id, domain FROM domain WHERE domain_id = ?";
			$domain_check = $app->db->queryOneRecord($sql, $check_domain);
			if(!$domain_check) return;
			$check_domain = $domain_check['domain'];
		}

		$result = $this->_check_unique($field_value . '.' . $check_domain, true);
		if(!$result) return $this->get_error('domain_error_autosub');
	}

	/* Check apache directives */
	function web_apache_directives($field_name, $field_value, $validator) {
		global $app;

		if(trim($field_value) != '') {
			$security_config = $app->getconf->get_security_config('ids');

			if($security_config['apache_directives_scan_enabled'] == 'yes') {

				// Get blacklist
				$blacklist_path = '/usr/local/rmnetdov/security/apache_directives.blacklist';
				if(is_file('/usr/local/rmnetdov/security/apache_directives.blacklist.custom')) $blacklist_path = '/usr/local/rmnetdov/security/apache_directives.blacklist.custom';
				if(!is_file($blacklist_path)) $blacklist_path = realpath(RMNETDOV_ROOT_PATH.'/../security/apache_directives.blacklist');

				$directives = explode("\n",$field_value);
				$regex = explode("\n",file_get_contents($blacklist_path));
				$blocked = false;
				$blocked_line = '';

				if(is_array($directives) && is_array($regex)) {
					foreach($directives as $directive) {
						$directive = trim($directive);
						foreach($regex as $r) {
							if(trim($r) == '') {
								continue;
							}
							if(preg_match(trim($r),$directive)) {
								$blocked = true;
								$blocked_line .= $directive.'<br />';
							};
						}
					}
				}
			}
		}

		if($blocked === true) {
			return $this->get_error('apache_directive_blocked_error').' '.$blocked_line;
		}
	}

	/* Check nginx directives */
	function web_nginx_directives($field_name, $field_value, $validator) {
		global $app;

		if(trim($field_value) != '') {
			$security_config = $app->getconf->get_security_config('ids');

			if($security_config['nginx_directives_scan_enabled'] == 'yes') {

				// Get blacklist
				$blacklist_path = '/usr/local/rmnetdov/security/nginx_directives.blacklist';
				if(is_file('/usr/local/rmnetdov/security/nginx_directives.blacklist.custom')) $blacklist_path = '/usr/local/rmnetdov/security/nginx_directives.blacklist.custom';
				if(!is_file($blacklist_path)) $blacklist_path = realpath(RMNETDOV_ROOT_PATH.'/../security/nginx_directives.blacklist');

				$directives = explode("\n",$field_value);
				$regex = explode("\n",file_get_contents($blacklist_path));
				$blocked = false;
				$blocked_line = '';

				if(is_array($directives) && is_array($regex)) {
					foreach($directives as $directive) {
						$directive = trim($directive);
						foreach($regex as $r) {
							if(preg_match(trim($r),$directive)) {
								$blocked = true;
								$blocked_line .= $directive.'<br />';
							};
						}
					}
				}
			}
		}

		if($blocked === true) {
			return $this->get_error('nginx_directive_blocked_error').' '.$blocked_line;
		}
	}


	/* internal validator function to match regexp */
	function _regex_validate($domain_name, $allow_wildcard = false) {
		$pattern = '/^' . ($allow_wildcard == true ? '(\*\.)?' : '') . '[\w\.\-]{1,255}\.[a-zA-Z0-9\-]{2,63}$/';
		return preg_match($pattern, $domain_name);
	}

	/* check if the domain hostname is unique (keep in mind the auto subdomains!) */
	function _check_unique($domain_name, $only_domain = false) {
		global $app, $page;

		if(isset($app->remoting_lib->primary_id)) {
			$primary_id = $app->remoting_lib->primary_id;
			$domain = $app->remoting_lib->dataRecord;
		} else {
			$primary_id = $app->tform->primary_id;
			$domain = $page->dataRecord;
		}

		if($domain['ip_address'] == '' || $domain['ipv6_address'] == ''){
			if($domain['parent_domain_id'] > 0){
				$parent_domain = $app->db->queryOneRecord("SELECT * FROM web_domain WHERE domain_id = ?", $domain['parent_domain_id']);
				if(is_array($parent_domain) && !empty($parent_domain)){
					$domain['ip_address'] = $parent_domain['ip_address'];
					$domain['ipv6_address'] = $parent_domain['ipv6_address'];
				}
			}
		}

		// check if domain has alias/subdomains - if we move a web to another IP, make sure alias/subdomains are checked as well
		$aliassubdomains = $app->db->queryAllRecords("SELECT * FROM web_domain WHERE parent_domain_id = ? AND (type = 'alias' OR type = 'subdomain' OR type = 'vhostsubdomain')", $primary_id);
		$additional_sql1 = '';
		$additional_sql2 = '';
		$domain_params = array();
		if(is_array($aliassubdomains) && !empty($aliassubdomains)){
			foreach($aliassubdomains as $aliassubdomain){
				$additional_sql1 .= " OR d.domain = ?";
				$additional_sql2 .= " OR CONCAT(d.subdomain, '.', d.domain) = ?";
				$domain_params[] = $aliassubdomain['domain'];
			}
		}


		$qrystr = "SELECT d.domain_id, IF(d.parent_domain_id != 0 AND p.domain_id IS NOT NULL, p.ip_address, d.ip_address) as `ip_address`, IF(d.parent_domain_id != 0 AND p.domain_id IS NOT NULL, p.ipv6_address, d.ipv6_address) as `ipv6_address` FROM `web_domain` as d LEFT JOIN `web_domain` as p ON (p.domain_id = d.parent_domain_id) WHERE (d.domain = ?" . $additional_sql1 . ") AND d.server_id = ? AND d.domain_id != ?" . ($primary_id ? " AND d.parent_domain_id != ?" : "");
		$params = array_merge(array($domain_name), $domain_params, array($domain['server_id'], $primary_id, $primary_id));
		$checks = $app->db->queryAllRecords($qrystr, true, $params);
		if(is_array($checks) && !empty($checks)){
			foreach($checks as $check){
				if($domain['ip_address'] == '*') return false;
				if($check['ip_address'] == '*') return false;
				if($domain['ip_address'] != '' && $check['ip_address'] == $domain['ip_address']) return false;
				if($domain['ipv6_address'] != '' && $check['ipv6_address'] == $domain['ipv6_address']) return false;
			}
		}

		if($only_domain == false) {
			$qrystr = "SELECT d.domain_id, IF(d.parent_domain_id != 0 AND p.domain_id IS NOT NULL, p.ip_address, d.ip_address) as `ip_address`, IF(d.parent_domain_id != 0 AND p.domain_id IS NOT NULL, p.ipv6_address, d.ipv6_address) as `ipv6_address` FROM `web_domain` as d LEFT JOIN `web_domain` as p ON (p.domain_id = d.parent_domain_id) WHERE (CONCAT(d.subdomain, '.', d.domain) = ?" . $additional_sql2 . ") AND d.server_id = ? AND d.domain_id != ?" . ($primary_id ? " AND d.parent_domain_id != ?" : "");
			$params = array_merge(array($domain_name), $domain_params, array($domain['server_id'], $primary_id, $primary_id));
			$checks = $app->db->queryAllRecords($qrystr, true, $params);
			if(is_array($checks) && !empty($checks)){
				foreach($checks as $check){
					if($domain['ip_address'] == '*') return false;
					if($check['ip_address'] == '*') return false;
					if($domain['ip_address'] != '' && $check['ip_address'] == $domain['ip_address']) return false;
					if($domain['ipv6_address'] != '' && $check['ipv6_address'] == $domain['ipv6_address']) return false;
				}
			}
		}

		return true;
	}

	/* check if the client may add wildcard domains */
	function _wildcard_limit() {
		global $app;

		if($_SESSION["s"]["user"]["typ"] != 'admin') {
			// Get the limits of the client
			$client_group_id = $app->functions->intval($_SESSION["s"]["user"]["default_group"]);
			$client = $app->db->queryOneRecord("SELECT limit_wildcard FROM sys_group, client WHERE sys_group.client_id = client.client_id and sys_group.groupid = ?", $client_group_id);

			if($client["limit_wildcard"] == 'y') return true;
			else return false;
		}
		return true; // admin may always add wildcard domain
	}


}
