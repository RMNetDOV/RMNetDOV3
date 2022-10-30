<?php

/******************************************
* Begin Form configuration
******************************************/

$list_def_file = "list/directive_snippets.list.php";
$tform_def_file = "form/directive_snippets.tform.php";

/******************************************
* End Form configuration
******************************************/

require_once '../../lib/config.inc.php';
require_once '../../lib/app.inc.php';

//* Check permissions for module
$app->auth->check_module_permissions('admin');

$app->load("tform_actions");

class page_action extends tform_actions {
	function onBeforeDelete() {
		global $app;

		if($this->dataRecord['type'] === 'php') {
			$rlike = $this->dataRecord['directive_snippets_id'].'|,'.$this->dataRecord['directive_snippets_id'].'|'.$this->dataRecord['directive_snippets_id'].',';
			$affected_snippets = $app->db->queryAllRecords('SELECT directive_snippets_id FROM directive_snippets WHERE required_php_snippets REGEXP ?', $rlike);
			if(is_array($affected_snippets) && !empty($affected_snippets)) {
				foreach($affected_snippets as $snippet) {
					$sql_in[] = $snippet['directive_snippets_id'];
				}
				$affected_sites = $app->db->queryAllRecords('SELECT domain_id FROM web_domain WHERE directive_snippets_id IN ?', $sql_in);
			}
		} elseif($this->dataRecord['type'] === 'apache' || $this->dataRecord['type'] === 'nginx') {
			$affected_sites = $app->db->queryAllRecords('SELECT domain_id FROM web_domain WHERE directive_snippets_id = ?', $this->dataRecord['directive_snippets_id']);
		}

		if(!empty($affected_sites)) {
			$app->error($app->tform->lng('error_delete_snippet_active_sites'));
		}
	}
}

$page = new page_action();
$page->onDelete();

