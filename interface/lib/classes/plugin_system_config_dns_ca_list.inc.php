<?php

class plugin_system_config_dns_ca_list extends plugin_base {

	var $module;
	var $form;
	var $tab;
	var $record_id;
	var $formdef;
	var $options;

	function onShow() {
		global $app;

		$listTpl = new tpl;
		$listTpl->newTemplate('templates/system_config_dns_ca_list.htm');

		//* Loading language file
		$lng_file = 'lib/lang/'.$app->functions->check_language($_SESSION['s']['language']).'_system_config.lng';
		include $lng_file;
		$listTpl->setVar($wb);
		if($_SESSION['s']['user']['typ'] == 'admin') {
			if(isset($_GET['action'])) { 
				$ca_id = $app->functions->intval($_GET['id']);
				if($_GET['action'] == 'delete' && $ca_id > 0) {
					$app->db->query("DELETE FROM dns_ssl_ca WHERE id = ?",  $ca_id);
				}
			}
		}

		if(isset($_GET['action']) && $_GET['action'] == 'edit' && $_GET['id'] > 0) $listTpl->setVar('edit_record', 1);

		// Getting Datasets from DB
		$ca_records = $app->db->queryAllRecords("SELECT * FROM dns_ssl_ca ORDER BY ca_name ASC");
		$records=array();
		if(is_array($ca_records) && count($ca_records) > 0) {
			foreach($ca_records as $ca) {
				$rec['ca_id'] = $ca['id'];
				$rec['name'] = $ca['ca_name'];
				$rec['active'] = $ca['active'];
				$records[] = $rec;
				unset($rec);
			}
			$listTpl->setLoop('ca_records', @$records);
		} 
		$listTpl->setVar('parent_id', $this->form->id);

		return $listTpl->grab();
	}

}

?>
