<?php

require_once '../../lib/config.inc.php';
require_once '../../lib/app.inc.php';

//* Check permissions for module
$app->auth->check_module_permissions('monitor');

$app->load('finediff');

// Loading the template
$app->uses('tpl');
$app->tpl->newTemplate("form.tpl.htm");
$app->tpl->setInclude('content_tpl', 'templates/dataloghistory_view.htm');

$app->load_language_file('web/monitor/lib/lang/'.$_SESSION['s']['language'].'_dataloghistory_view.lng');
require('lib/lang/'.$_SESSION['s']['language'].'_dataloghistory_view.lng');
$app->tpl->setvar($wb);

$id = intval($_GET['id']);

$record = $app->db->queryOneRecord('SELECT * FROM sys_datalog WHERE datalog_id = ?', $id);

$out['id'] = $id;
$out['username'] = $record['user'];

if(!$data = unserialize(stripslashes($record['data']))) {
	$data = unserialize($record['data']);
}

$out['timestamp'] = date($app->lng('conf_format_datetime'), $record['tstamp']);
$out['table'] = $record['dbtable'];
list($key, $value) = explode(':', $record['dbidx']);
if (!empty($value)) {
	if ($record['action'] == 'd') {
		// No link for deleted content.
		$out['table_id'] = $record['dbidx'];
	} else {
		switch ($out['table']) {
			case 'mail_forwarding':
				switch ($data['new']['type']) {
					case 'alias':
						$file = 'mail/mail_alias_edit.php';
						break;
					case 'aliasdomain':
						$file = 'mail/mail_aliasdomain_edit.php';
						break;
					case 'forward':
						$file = 'mail/mail_forward_edit.php';
						break;
					case 'catchall':
						$file = 'mail/mail_domain_catchall_edit.php';
						break;
				}
			break;
			case 'mail_user':
				$file = 'mail/mail_user_edit.php';
			break;
			case 'mail_domain':
				$file = 'mail/mail_domain_edit.php';
			break;
			case 'web_domain':
				$file = 'sites/web_vhost_domain_edit.php';
			break;
			case 'web_database':
				$file = 'sites/database_edit.php';
			break;
			case 'web_database_user':
				$file = 'sites/database_user_edit.php';
			break;
                       case 'ftp_user':
                               $file = 'sites/ftp_user_edit.php';
                       break;
                       case 'shell_user':
                               $file = 'sites/shell_user_edit.php';
                       break;
                       case 'dns_soa':
                               $file = 'dns/dns_soa_edit.php';
                       break;

			// TODO Add a link per content type
			default:
				$file = '';
		}

		if (!empty($file)) {
			$out['table_id'] = '<a href="#" data-load-content="' . $file . '?id=' . $value
						. '" data-toggle="tooltip" data-placement="bottom" title="" data-original-title="link">'
						. $record['dbidx'] . '</a>';
		}
	}
}

$out['action_char'] = $record['action'];
$out['action_name'] = $app->lng($record['action']);

$out['session_id'] = $record['session_id'];

switch ($record['action']) {
	case 'i':
		$inserts = array();
		foreach ($data['new'] as $key=>$value) {
			$inserts[] = array(
				'key' => $key,
				'value' => nl2br($value),
			);
		}
		$app->tpl->setLoop('inserts', $inserts);
		break;
	case 'u':
		$updates = array();
		foreach ($data['new'] as $key=>$value) {
			if ($value != $data['old'][$key]) {
				$old = $data['old'][$key];
				$new = $value;
				$changes = show_diff_if_needed($old, $new);
				$updates[] = array(
					'key' => $key,
					'is_diff' => $changes['is_diff'],
					'old' => nl2br($changes['old']),
					'new' => nl2br($changes['new']),
					'diff' => nl2br($changes['diff']),
				);
			}
		}
		if (count($updates) > 0) {
			$app->tpl->setLoop('updates', $updates);
		} else {
			$out['no_changes'] = true;
		}
		break;
	case 'd':
		$deletes = array();
		foreach ($data['old'] as $key=>$value) {
			$deletes[] = array(
				'key' => $key,
				'value' => nl2br($value),
			);
		}
		$app->tpl->setLoop('deletes', $deletes);
		break;
}

$app->tpl->setVar($out);
$app->tpl->setVar('can_undo', ($out['action_char'] === 'u' || $out['action_char'] === 'd'));

$app->tpl_defaults();
$app->tpl->pparse();

function show_diff_if_needed($old, $new) {
	global $app;

	$diff_min_lines = 6;

	if (substr_count($old, "\n") >= $diff_min_lines || substr_count($new, "\n") >= $diff_min_lines) {
		$opcodes = FineDiff::getDiffOpcodes($old, $new);
		$html = FineDiff::renderUTF8DiffToHTMLFromOpcodes($old, $opcodes);
		return array('is_diff'=>true, 'old'=>'', 'new'=>'', 'diff'=>$html);
	} else {
		return array('is_diff'=>false, 'old'=>$old, 'new'=>$new, 'diff'=>'');
	}
}

?>
