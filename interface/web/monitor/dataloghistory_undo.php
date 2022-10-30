<?php

require_once '../../lib/config.inc.php';
require_once '../../lib/app.inc.php';

//* Check permissions for module
$app->auth->check_module_permissions('monitor');

// Loading the template
$app->uses('tpl');
$app->tpl->newTemplate("form.tpl.htm");
$app->tpl->setInclude('content_tpl', 'templates/dataloghistory_undo.htm');

require('lib/lang/'.$_SESSION['s']['language'].'_dataloghistory_undo.lng');
$app->tpl->setvar($wb);

$id = intval($_GET['id']);

$record = $app->db->queryOneRecord('SELECT * FROM sys_datalog WHERE datalog_id = ?', $id);

$dbidx = explode(':', $record['dbidx']);

$old_record = $app->db->queryOneRecord('SELECT * FROM ?? WHERE ??=?', $record['dbtable'], $dbidx[0], $dbidx[1]);

if($record['action'] === 'u') {
	if (is_array($old_record)) {
		if(!$data = unserialize(stripslashes($record['data']))) {
			$data = unserialize($record['data']);
		}

		$new_record = $data['old'];

		$app->db->datalogUpdate($record['dbtable'], $new_record, $dbidx[0], $dbidx[1]);

		$app->tpl->setVar('success', true);
	} else {
		$app->tpl->setVar('success', false);
	}
} elseif($record['action'] === 'd') {
	if(is_array($old_record)) {
		$app->tpl->setVar('success', false);
		$app->tpl->setVar('error_txt', $wb['error_undelete_txt']);
	} else {
		if(!$data = unserialize(stripslashes($record['data']))) {
			$data = unserialize($record['data']);
		}

		$new_record = $data['old'];
		/* TODO: maybe check some data, e. g. server_id -> server still there?, sys_groupid -> sys_group/sys_user still there? */

		$app->db->datalogInsert($record['dbtable'], $new_record, $dbidx[0]);

		$app->tpl->setVar('success', true);
	}
}

$app->tpl_defaults();
$app->tpl->pparse();

?>
