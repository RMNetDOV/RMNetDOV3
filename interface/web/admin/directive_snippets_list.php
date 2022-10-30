<?php

require_once '../../lib/config.inc.php';
require_once '../../lib/app.inc.php';

/******************************************
* Begin Form configuration
******************************************/

$list_def_file = "list/directive_snippets.list.php";

/******************************************
* End Form configuration
******************************************/

//* Check permissions for module
$app->auth->check_module_permissions('admin');

$app->uses('listform_actions');

class list_action extends listform_actions {

	public function prepareDataRow($rec)
	{
		global $app;

		$rec = $app->listform->decode($rec);

		//* Alternating datarow colors
		$this->DataRowColor = ($this->DataRowColor == '#FFFFFF') ? '#EEEEEE' : '#FFFFFF';
		$rec['bgcolor'] = $this->DataRowColor;

		//* substitute value for select fields
		if(is_array($app->listform->listDef['item']) && count($app->listform->listDef['item']) > 0) {
			foreach($app->listform->listDef['item'] as $field) {
				$key = $field['field'];
				if(isset($field['formtype']) && $field['formtype'] == 'SELECT') {
					if(strtolower($rec[$key]) == 'y' or strtolower($rec[$key]) == 'n') {
						// Set a additional image variable for bolean fields
						$rec['_'.$key.'_'] = (strtolower($rec[$key]) == 'y')?'x16/tick_circle.png':'x16/cross_circle.png';
					}
					//* substitute value for select field
					$rec[$key] = @$field['value'][$rec[$key]];
				}
			}
		}

		//* The variable "id" contains always the index variable
		$rec['id'] = $rec[$this->idx_key];
		return $rec;
	}

}
$list = new list_action;
$list->SQLOrderBy = 'ORDER BY directive_snippets.name';
$list->onLoad();
