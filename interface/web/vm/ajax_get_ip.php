<?php

require_once '../../lib/config.inc.php';
require_once '../../lib/app.inc.php';

//* Check permissions for module
$app->auth->check_module_permissions('vm');

$server_id = $app->functions->intval($_GET["server_id"]);

if($_SESSION["s"]["user"]["typ"] == 'admin' or $app->auth->has_clients($_SESSION['s']['user']['userid'])) {

	$sql = "SELECT ip_address FROM openvz_ip WHERE reserved = 'n' AND server_id = ?";
	$ips = $app->db->queryAllRecords($sql, $server_id);
	$ip_select = "";
	if(is_array($ips)) {
		foreach( $ips as $ip) {
			//$selected = ($ip["ip_address"] == $this->dataRecord["ip_address"])?'SELECTED':'';
			$ip_select .= "$ip[ip_address]#";
		}
	}
	unset($tmp);
	unset($ips);
}
$ip_select = substr($ip_select, 0, -1);
echo $ip_select;
?>
