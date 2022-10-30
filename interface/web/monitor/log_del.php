<?php

require_once '../../lib/config.inc.php';
require_once '../../lib/app.inc.php';

//* Check permissions for module
$app->auth->check_module_permissions('monitor');

$type = $app->functions->intval($_GET['type']);
if ($type == "batch") {
  $loglevel = $app->functions->intval($_GET['loglevel']);
  $app->db->query("UPDATE sys_log SET loglevel = 0 WHERE loglevel = ?", $loglevel);
} else {
  $syslog_id = $app->functions->intval($_GET['id']);
  $app->db->query("UPDATE sys_log SET loglevel = 0 WHERE syslog_id = ?", $syslog_id);
}

header('Location: log_list.php');
exit;


?>
