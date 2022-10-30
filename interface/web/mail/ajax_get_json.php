<?php

require_once '../../lib/config.inc.php';
require_once '../../lib/app.inc.php';

//* Check permissions for module
$app->auth->check_module_permissions('mail');

$type = $_GET['type'];
$domain_id = $_GET['domain_id'];

if($type == 'create_dkim' && $domain_id != ''){
	$dkim_public = $_GET['dkim_public'];
	$dkim_selector = $_GET['dkim_selector'];
	$domain = $domain_id;
	if(is_numeric($domain_id)) {
		$temp = $app->db->queryOneRecord("SELECT domain FROM domain WHERE domain_id = ? AND ".$app->tform->getAuthSQL('r'), $domain_id);
		$domain = $temp['domain'];
	}
	$rec = $app->db->queryOneRecord("SELECT server_id, domain FROM mail_domain WHERE domain = ?", $domain);
	$server_id = $rec['server_id'];
	$maildomain = $rec['domain'];
	unset($rec);
	$mail_config = $app->getconf->get_server_config($server_id, 'mail');
	$dkim_strength = $app->functions->intval($mail_config['dkim_strength']);
	if ($dkim_strength=='') $dkim_strength = 2048;

	$rnd_val = $dkim_strength * 10;
	$app->system->exec_safe('openssl rand -out ../../temp/random-data.bin '.$rnd_val.' 2> /dev/null');
	$app->system->exec_safe('openssl genrsa -rand ../../temp/random-data.bin '.$dkim_strength.' 2> /dev/null');
	$privkey = $app->system->last_exec_out();
	unlink("../../temp/random-data.bin");
	$dkim_private='';
	foreach($privkey as $values) $dkim_private=$dkim_private.$values."\n";

	if ($dkim_public != '' && $maildomain != '') {
		if (validate_domain($domain) && validate_selector($dkim_selector) ) {
			//* get active selectors from dns
			$soa_rec = $app->db->queryOneRecord("SELECT origin FROM dns_soa WHERE active = 'Y' AND origin = ?", $domain.'.');
			if (isset($soa_rec) && !empty($soa_rec)) {
				//* check for a dkim-record in the dns?
				$dns_data = $app->db->queryOneRecord("SELECT name FROM dns_rr WHERE name = ? AND active = 'Y'", $dkim_selector.'._domainkey.'.$domain.'.');
				if (!empty($dns_data)){
					$selector = str_replace( '._domainkey.'.$domain.'.', '', $dns_data['name']);
				} else {
				}
			}
			if ($dkim_selector == $selector || !isset($selector)) {
				$selector = substr($old_selector,0,53).time(); //* add unix-timestamp to delimiter to allow old and new key in the dns
			}
		} else {
			$selector = 'invalid domain or selector';
		}
		unset($dkim_public);
		$app->system->exec_safe('echo ?|openssl rsa -pubout -outform PEM 2> /dev/null', $dkim_private);
		$pubkey = $app->system->last_exec_out();
		foreach($pubkey as $values) $dkim_public=$dkim_public.$values."\n";
		$selector = $dkim_selector;
	} else {
		unset($dkim_public);
		$app->system->exec_safe('echo ?|openssl rsa -pubout -outform PEM 2> /dev/null', $dkim_private);
		$pubkey = $app->system->last_exec_out();
		foreach($pubkey as $values) $dkim_public=$dkim_public.$values."\n";
		$selector = $dkim_selector;
	}

	$dns_record=str_replace(array('-----BEGIN PUBLIC KEY-----','-----END PUBLIC KEY-----',"\r","\n"),'',$dkim_public);
	$dns_record = str_replace(array("\r\n", "\n", "\r"),'',$dns_record);

	$dkim_private=json_encode($dkim_private);
	$dkim_private=substr($dkim_private, 1, -1);

	$dkim_public=json_encode($dkim_public);
	$dkim_public=substr($dkim_public, 1, -1);

	$json = '{';
	$json .= '"dkim_private":"'.$dkim_private.'"';
	$json .= ',"dkim_public":"'.$dkim_public.'"';
	$json .= ',"dkim_selector":"'.$selector.'"';
	$json .= ',"dns_record":"'.$dns_record.'"';
	$json .= ',"domain":"'.$domain.'"';
	$json .= '}';
}
header('Content-type: application/json');
echo $json;

function validate_domain($domain) {
	$regex = '/^[\w\.\-]{1,255}\.[a-zA-Z0-9\-]{2,63}$/';
	if ( preg_match($regex, $domain) === 1 ) return true; else return false;
}

function validate_selector($selector) {
	$regex = '/^[a-z0-9]{0,63}$/';
	if ( preg_match($regex, $selector) === 1 ) return true; else return false;
}

?>
