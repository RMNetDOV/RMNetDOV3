<?php

require_once '../../lib/config.inc.php';
require_once '../../lib/app.inc.php';

//* Check permissions for module
$app->auth->check_module_permissions('sites');
$app->uses('getconf');

$server_id = $app->functions->intval($_GET["server_id"]);
$client_group_id = $app->functions->intval($_GET["client_group_id"]);
$ip_type = $_GET['ip_type'];

//if($_SESSION["s"]["user"]["typ"] == 'admin' or $app->auth->has_clients($_SESSION['s']['user']['userid'])) {

	//* Get global web config
	$web_config = $app->getconf->get_server_config($server_id, 'web');
	
	$tmp = $app->db->queryOneRecord("SELECT client_id FROM sys_group WHERE groupid = ?", $client_group_id);
	$sql = "SELECT ip_address FROM server_ip WHERE ip_type = ? AND server_id = ? AND virtualhost = 'y' AND (client_id = 0 OR client_id=?)";

	$ips = $app->db->queryAllRecords($sql, $ip_type, $server_id, $tmp['client_id']);
	// $ip_select = "<option value=''></option>";
	if($ip_type == 'IPv4'){
		$ip_select = ($web_config['enable_ip_wildcard'] == 'y')?"*#":"";
	} else {
		$ip_select = "#";
	}
	if(is_array($ips)) {
		foreach( $ips as $ip) {
			//$selected = ($ip["ip_address"] == $this->dataRecord["ip_address"])?'SELECTED':'';
			$ip_select .= "$ip[ip_address]#";
		}
	}
	unset($tmp);
	unset($ips);
//}

echo substr($ip_select, 0, -1);
?>
