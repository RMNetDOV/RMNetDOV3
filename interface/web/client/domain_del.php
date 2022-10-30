<?php

/******************************************
* Begin Form configuration
******************************************/

$list_def_file = "list/domain.list.php";
$tform_def_file = "form/domain.tform.php";

/******************************************
* End Form configuration
******************************************/

require_once '../../lib/config.inc.php';
require_once '../../lib/app.inc.php';

//* Check permissions for module
$app->auth->check_module_permissions('client');

// Loading classes
$app->uses('tpl,tform,tform_actions');
$app->load('tform_actions');

class page_action extends tform_actions {

	function onBeforeDelete() {
		global $app; $conf;

		//* load language file
		$lng_file = 'lib/lang/'.$app->functions->check_language($_SESSION['s']['language']).'.lng';
		include $lng_file;

		/*
		 * We can only delete domains if they are NOT in use
		 */
		$domain = $this->dataRecord['domain'];

		$sql = "SELECT id FROM dns_soa WHERE origin = ?";
		$res = $app->db->queryOneRecord($sql, $domain.".");
		if (is_array($res)){
			$app->error($wb['error_domain_in dnsuse']);
		}

		$sql = "SELECT id FROM dns_slave WHERE origin = ?";
		$res = $app->db->queryOneRecord($sql, $domain.".");
		if (is_array($res)){
			$app->error($wb['error_domain_in dnsslaveuse']);
		}

		$sql = "SELECT domain_id FROM mail_domain WHERE domain = ?";
		$res = $app->db->queryOneRecord($sql, $domain);
		if (is_array($res)){
			$app->error($wb['error_domain_in mailuse']);
		}

		$sql = "SELECT domain_id FROM web_domain WHERE (domain = ? AND type IN ('alias', 'vhost', 'vhostalias')) OR (domain LIKE ? AND type IN ('subdomain', 'vhostsubdomain'))";
		$res = $app->db->queryOneRecord($sql, $domain, '%.' . $domain);
		if (is_array($res)){
			$app->error($wb['error_domain_in webuse']);
		}
	}

}

$page = new page_action;
$page->onDelete();

?>
