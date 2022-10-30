<?php

/******************************************
* Begin Form configuration
******************************************/

$tform_def_file = "form/client_template.tform.php";

/******************************************
* End Form configuration
******************************************/

require_once '../../lib/config.inc.php';
require_once '../../lib/app.inc.php';

//* Check permissions for module
$app->auth->check_module_permissions('client');
if($_SESSION["s"]["user"]["typ"] != 'admin' && !$app->auth->has_clients($_SESSION['s']['user']['userid'])) die('Client-Templates are for Admins and Resellers only.');

// Loading classes
$app->uses('tpl,tform,tform_actions');
$app->load('tform_actions');

class page_action extends tform_actions {


	function onSubmit() {
		global $app;

		//* Resellers shall not be able to create another reseller or set reseller specific settings
		if($_SESSION["s"]["user"]["typ"] == 'user') {
			$this->dataRecord['limit_client'] = 0;
			$this->dataRecord['limit_domainmodule'] = 0;
		}

		parent::onSubmit();
	}

	function onShowEnd() {
		global $app;
		// Check wether per domain relaying is enabled or not
		$global_config = $app->getconf->get_global_config('mail');
		if($global_config['show_per_domain_relay_options'] == 'y') {
			$app->tpl->setVar("show_per_domain_relay_options", 1);
		} else {
			$app->tpl->setVar("show_per_domain_relay_options", 0);
		}
		// APS is enabled or not
		$global_config = $app->getconf->get_global_config('sites');
		if($global_config['show_aps_menu'] == 'y') {
			$app->tpl->setVar("show_aps_menu", 1);
		} else {
			$app->tpl->setVar("show_aps_menu", 0);
		}
		parent::onShowEnd();
	}

	function onBeforeUpdate() {
		global $app;

		if(isset($this->dataRecord['template_type'])) {
			//* Check if the template_type has been changed
			$rec = $app->db->queryOneRecord("SELECT template_type from client_template WHERE template_id = ?", $this->id);
			if($rec['template_type'] != $this->dataRecord['template_type']) {
				//* Add a error message and switch back to old server
				$app->tform->errorMessage .= $app->lng('The template type can not be changed.');
				$this->dataRecord['template_type'] = $rec['template_type'];
			}
			unset($rec);
		}
	}


	/*
	 This function is called automatically right after
	 the data was successful updated in the database.
	*/
	function onAfterUpdate() {
		global $app;

		$app->uses('client_templates');
		if (isset($this->dataRecord["template_type"])) {
			$template_type = $this->dataRecord["template_type"];
		} else {
			$tmp = $app->tform->getDataRecord($this->id);
			$template_type = $tmp['template_type'];
		}

		/*
		 * the template has changed. apply the new data to all clients
		 */
		if ($template_type == 'm'){
			$sql = "SELECT client_id FROM client WHERE template_master = ?";
			$clients = $app->db->queryAllRecords($sql, $this->id);
		} else {
			$sql = "SELECT client_id FROM client WHERE template_additional LIKE ? OR template_additional LIKE ? OR template_additional LIKE ? UNION SELECT client_id FROM client_template_assigned WHERE client_template_id = ?";
			$clients = $app->db->queryAllRecords($sql, '%/' . $this->id . '/%', $this->id . '/%', '%/' . $this->id, $this->id);
		}
		if (is_array($clients)){
			foreach ($clients as $client){
				$app->client_templates->apply_client_templates($client['client_id']);
			}
		}
	}

}

$page = new page_action;
$page->onLoad();
?>
