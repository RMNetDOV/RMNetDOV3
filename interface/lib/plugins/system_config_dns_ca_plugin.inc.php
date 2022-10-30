<?php

class system_config_dns_ca_plugin {

	var $plugin_name = 'system_config_dns_ca_plugin';
	var $class_name = 'system_config_dns_ca_plugin';

	function onLoad() {
		global $app;

		$app->plugin->registerEvent('dns:dns_caa:on_after_update', 'system_config_dns_ca_plugin', 'caa_update');
		$app->plugin->registerEvent('dns:dns_caa:on_after_insert', 'system_config_dns_ca_plugin', 'caa_update');

		$app->plugin->registerEvent('sites:web_vhost_domain:on_after_insert', 'system_config_dns_ca_plugin', 'web_vhost_domain_edit');
		$app->plugin->registerEvent('sites:web_vhost_domain:on_after_update', 'system_config_dns_ca_plugin', 'web_vhost_domain_edit');
	}

	function caa_update($event_name, $page_form) {
		global $app;

		if(trim($page_form->dataRecord['additional'] != '')) {
			$rec = $app->db->queryOneRecord("SELECT * FROM dns_rr WHERE id = ?", $page_form->id);
			unset($rec['id']);
			$zone = $app->db->queryOneRecord("SELECT origin FROM dns_soa WHERE id = ?", $rec['zone']);
			$host=str_replace($zone['origin'], '', $page_form->dataRecord['name']);
			$host=rtrim($host,'.');
			$page_form->dataRecord['additional']=str_replace($host, '', $page_form->dataRecord['additional']);
			$additional=explode(',', $page_form->dataRecord['additional']);
			foreach($additional as $new) {
				if($new != '') {
					$insert_data = $rec;
					$insert_data['name'] = $new.'.'.$zone['origin'];
					$app->db->datalogInsert('dns_rr', $insert_data, 'id');
				}
			}
		}
	} 

	function web_vhost_domain_edit($event_name, $page_form) {
		global $app;

		if($page_form->dataRecord['ssl_letsencrypt'] == 'y') {
			$domain = $page_form->dataRecord['domain'];
			$subdomain = $page_form->dataRecord['subdomain'];
			$temp=$app->db->queryAllRecords("SELECT * FROM dns_rr WHERE type = 'CAA' AND (name = ? OR name = ?) AND data like ?", $domain.'.', $subdomain.'.'.$domain.'.', '%letsencrypt%');
			if(count($temp) == 0) {
				$caa = $app->db->queryOneRecord("SELECT * FROM dns_ssl_ca WHERE ca_issue = 'letsencrypt.org' AND active = 'Y'");
				$soa = $app->db->queryOneRecord("SELECT * FROM dns_soa WHERE origin = ?", $domain.'.');
				if(is_array($caa) && is_array($soa)) {
					$records = array();
					$records[] = $domain.'.';;
					if($subdomain != '' && $subdomain != 'www') $records[] = $subdomain.'.'.$domain;
					foreach($records as $record) {
						$new_rr = $app->db->queryOneRecord("SELECT * FROM dns_rr WHERE name = ?", $soa['origin']);
						unset($new_rr['id']);
						$new_rr['type'] = 'CAA';
						$new_rr['name'] = $record;
						$new_rr['data'] = "0 issue \"$caa[ca_issue]\"";
						$new_rr['ttl'] = $soa['ttl'];
						$new_rr['active'] = 'Y';
				        $new_rr['stamp'] = date('Y-m-d H:i:s');
		        		$new_rr['serial'] = $app->validate_dns->increase_serial($new_rr['serial']);
				        $app->db->datalogInsert('dns_rr', $new_rr, 'id', $new_rr['zone']);
						$zone = $app->db->queryOneRecord("SELECT id, serial FROM dns_soa WHERE active = 'Y' AND id = ?", $new_rr['zone']);
						$new_serial = $app->validate_dns->increase_serial($zone['serial']);
						$app->db->datalogUpdate('dns_soa', array("serial" => $new_serial), 'id', $zone['id']);
					}
				}
			}
		}
	}

} // End class

?>
