<?php

require_once '../../lib/config.inc.php';
require_once '../../lib/app.inc.php';

//* Check permissions for module
$app->auth->check_module_permissions('dns');

// Loading classes
$app->uses('tpl,tform,tform_actions,validate_dns');
$app->load('tform_actions');

class dns_page_action extends tform_actions {

	protected function checkDuplicate() {
		return false;
	}

	protected function zoneFileEscape( $str ) {
		// escape backslash and double quotes
		$ret = str_replace( '\\', '\\\\', $str );
		$ret = str_replace( '"', '\\"', $ret );
		return $ret;
	}

	protected function zoneFileUnescape( $str ) {
		// escape sequence can be rfc 1035 '\DDD' (backslash, 3 digits) or '\X' (backslash, non-digit char)
		return preg_replace_callback(  '/\\\\(\d\d\d|\D)/',
			function( $Matches ) {
				if (preg_match( '/\d{3}/', $Matches[1] )) {
					return chr( $Matches[1] );
				} elseif (preg_match( '/\D/', $Matches[1])) {
					return $Matches[1];
				} else {
					return $Matches[0];
				}
			},
			$str
		);
	}

	function onShowNew() {
		global $app, $conf;

		// we will check only users, not admins
		if($_SESSION["s"]["user"]["typ"] == 'user') {

			// Get the limits of the client
			$client_group_id = intval($_SESSION["s"]["user"]["default_group"]);
			$client = $app->db->queryOneRecord("SELECT limit_dns_record FROM sys_group, client WHERE sys_group.client_id = client.client_id and sys_group.groupid = ?", $client_group_id);

			// Check if the user may add another mailbox.
			if($client["limit_dns_record"] >= 0) {
				$tmp = $app->db->queryOneRecord("SELECT count(id) as number FROM dns_rr WHERE sys_groupid = ?", $client_group_id);
				if($tmp["number"] >= $client["limit_dns_record"]) {
					$app->error($app->tform->wordbook["limit_dns_record_txt"]);
				}
			}
		}

		parent::onShowNew();
	}

	function onSubmit() {
		global $app, $conf;

		// Get the parent soa record of the domain
		$soa = $app->db->queryOneRecord("SELECT * FROM dns_soa WHERE id = ? AND " . $app->tform->getAuthSQL('r'), $_POST["zone"]);

		// Check if Domain belongs to user
		if($soa["id"] != $_POST["zone"]) $app->tform->errorMessage .= $app->tform->wordbook["no_zone_perm"];

		// Check the client limits, if user is not the admin
		if($_SESSION["s"]["user"]["typ"] != 'admin') { // if user is not admin
			// Get the limits of the client
			$client_group_id = intval($_SESSION["s"]["user"]["default_group"]);
			$client = $app->db->queryOneRecord("SELECT limit_dns_record FROM sys_group, client WHERE sys_group.client_id = client.client_id and sys_group.groupid = ?", $client_group_id);

			// Check if the user may add another record.
			if($this->id == 0 && $client["limit_dns_record"] >= 0) {
				$tmp = $app->db->queryOneRecord("SELECT count(id) as number FROM dns_rr WHERE sys_groupid = ?", $client_group_id);
				if($tmp["number"] >= $client["limit_dns_record"]) {
					$app->error($app->tform->wordbook["limit_dns_record_txt"]);
				}
			}
		} // end if user is not admin

		// Replace @ to example.com.
		if($this->dataRecord["name"] === '@') {
			$this->dataRecord["name"] = $soa['origin'];
		}

		// Replace * to *.example.com.
		if($this->dataRecord["name"] === '*') {
			$this->dataRecord["name"] = '*.' . $soa['origin'];
		}

		if($this->checkDuplicate()) $app->tform->errorMessage .= $app->tform->lng("data_error_duplicate")."<br>";

		// Set the server ID of the rr record to the same server ID as the parent record.
		$this->dataRecord["server_id"] = $soa["server_id"];

		// Update the serial number  and timestamp of the RR record
		$soa = $app->db->queryOneRecord("SELECT serial FROM dns_rr WHERE id = ?", $this->id);
		$this->dataRecord["serial"] = $app->validate_dns->increase_serial($soa["serial"]);
		$this->dataRecord["stamp"] = date('Y-m-d H:i:s');

		parent::onSubmit();
	}

	function onAfterInsert() {
		global $app, $conf;

		//* Set the sys_groupid of the rr record to be the same then the sys_groupid of the soa record
		$soa = $app->db->queryOneRecord("SELECT * FROM dns_soa WHERE id = ? AND " . $app->tform->getAuthSQL('r'), $this->dataRecord["zone"]);
		$app->db->datalogUpdate('dns_rr', array("sys_groupid" => $soa['sys_groupid']), 'id', $this->id);

		//* Update the serial number of the SOA record
		$soa_id = $app->functions->intval($_POST["zone"]);
		$serial = $app->validate_dns->increase_serial($soa["serial"]);
		$app->db->datalogUpdate('dns_soa', array("serial" => $serial), 'id', $soa_id);
	}

	function onAfterUpdate() {
		global $app, $conf;

		//* Update the serial number of the SOA record
		$soa = $app->db->queryOneRecord("SELECT * FROM dns_soa WHERE id = ? AND " . $app->tform->getAuthSQL('r'), $this->dataRecord["zone"]);
		$soa_id = $app->functions->intval($_POST["zone"]);
		$serial = $app->validate_dns->increase_serial($soa["serial"]);
		$app->db->datalogUpdate('dns_soa', array("serial" => $serial), 'id', $soa_id);
	}

}

?>
