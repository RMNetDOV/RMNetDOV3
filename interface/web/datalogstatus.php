<?php

error_reporting(E_ALL|E_STRICT);

require_once '../lib/config.inc.php';
require_once '../lib/app.inc.php';

// Check if we have an active users ession and redirect to login if thats not the case.
if($_SESSION['s']['user']['active'] != 1) {
	header('Location: /login/');
	die();
}
$datalogstatus = json_encode($app->db->datalogStatus());
echo ($datalogstatus);
?>
