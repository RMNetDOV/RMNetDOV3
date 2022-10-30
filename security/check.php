<?php

require "/usr/local/rmnetdov/server/lib/config.inc.php";
require "/usr/local/rmnetdov/server/lib/app.inc.php";

set_time_limit(0);
ini_set('error_reporting', E_ALL & ~E_NOTICE);

// make sure server_id is always an int
$conf['server_id'] = intval($conf['server_id']);


// Load required base-classes
$app->uses('ini_parser,file,services,getconf,system');

// get security config
$security_config = $app->getconf->get_security_config('systemcheck');

$alert = '';
$data_dir = '/usr/local/rmnetdov/security/data';


// Check if a new rmnetdov user has been added
if($security_config['warn_new_admin'] == 'yes') {
	$data_file = $data_dir.'/admincount';
	//get number of admins
	$tmp = $app->db->queryOneRecord("SELECT count(userid) AS number FROM sys_user WHERE typ = 'admin'");
	if($tmp) {
		$admin_user_count_new = intval($tmp['number']);
		
		if(is_file($data_file)) {
			$admin_user_count_old = intval(file_get_contents($data_file));
			if($admin_user_count_new != $admin_user_count_old) {
				$alert .= "The number of RM-Net - DOV CP administrator users has changed. Old: $admin_user_count_old New: $admin_user_count_new \n";
				file_put_contents($data_file,$admin_user_count_new);
			}
		} else {
			// first run, so we save the current count
			file_put_contents($data_file,$admin_user_count_new);
			chmod($data_file,0700);
		}
	}
}

// Check if /etc/passwd file has been changed
if($security_config['warn_passwd_change'] == 'yes') {
	$data_file = $data_dir.'/passwd.md5';
	$md5sum_new = md5_file('/etc/passwd');
	
	if(is_file($data_file)) {
		$md5sum_old = trim(file_get_contents($data_file));
		if($md5sum_new != $md5sum_old) {
			$alert .= "The file /etc/passwd has been changed.\n";
			file_put_contents($data_file,$md5sum_new);
		}
	} else {
		file_put_contents($data_file,$md5sum_new);
		chmod($data_file,0700);
	}
}

// Check if /etc/shadow file has been changed
if($security_config['warn_shadow_change'] == 'yes') {
	$data_file = $data_dir.'/shadow.md5';
	$md5sum_new = md5_file('/etc/shadow');
	
	if(is_file($data_file)) {
		$md5sum_old = trim(file_get_contents($data_file));
		if($md5sum_new != $md5sum_old) {
			$alert .= "The file /etc/shadow has been changed.\n";
			file_put_contents($data_file,$md5sum_new);
		}
	} else {
		file_put_contents($data_file,$md5sum_new);
		chmod($data_file,0700);
	}
}

// Check if /etc/group file has been changed
if($security_config['warn_group_change'] == 'yes') {
	$data_file = $data_dir.'/group.md5';
	$md5sum_new = md5_file('/etc/group');
	
	if(is_file($data_file)) {
		$md5sum_old = trim(file_get_contents($data_file));
		if($md5sum_new != $md5sum_old) {
			$alert .= "The file /etc/group has been changed.\n";
			file_put_contents($data_file,$md5sum_new);
		}
	} else {
		file_put_contents($data_file,$md5sum_new);
		chmod($data_file,0700);
	}
}


if($alert != '') {
	$admin_email = $security_config['security_admin_email'];
	$admin_email_subject = $security_config['security_admin_email_subject'];
	mail($admin_email, $admin_email_subject, $alert);
	//$app->log(str_replace("\n"," -- ",$alert),1);
	echo str_replace("\n"," -- ",$alert)."\n";
}
























?>