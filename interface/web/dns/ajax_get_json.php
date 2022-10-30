<?php

require_once '../../lib/config.inc.php';
require_once '../../lib/app.inc.php';

//* Check permissions for module
$app->auth->check_module_permissions('dns');

$type = $_GET["type"];
$ca_id = $app->functions->intval($_GET['ca_id']);

if($type == 'get_ipv4'){
	$result = array();

	// ipv4
	$result[] = $app->functions->suggest_ips('IPv4');

	$json = $app->functions->json_encode($result);
}

if($type == 'get_ipv6'){
	$result = array();

	// ipv6
	$result[] = $app->functions->suggest_ips('IPv6');

	$json = $app->functions->json_encode($result);
}

if($type == 'ca_wildcard'){
	$json = '{';
	$json .= '"ca_wildcard":"';
	$tmp = $app->db->queryOneRecord("SELECT ca_wildcard, ca_issue, ca_critical FROM dns_ssl_ca WHERE id = ?", $ca_id);
	$json .= $tmp['ca_wildcard'].'"';
	$json .= ',"ca_issue":"'.$tmp['ca_issue'].'"';
	$json .= ',"ca_critical":"'.$tmp['ca_critical'].'"';
	unset($tmp);
	$json .= '}';
}

header('Content-type: application/json');
echo $json;
?>
