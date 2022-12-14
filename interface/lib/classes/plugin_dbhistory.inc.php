<?php

class plugin_dbhistory extends plugin_base {

	var $module;
	var $form;
	var $tab;
	var $record_id;
	var $formdef;
	var $options;

	function onShow() {

		global $app, $conf;

		$content = '';

		$db_table = $app->tform->formDef["db_table"];
		$db_table_idx = $app->tform->formDef["db_table_idx"];
		$primary_id = $this->form->id;
		if($_SESSION["s"]["user"]["typ"] == 'admin') {
			$sql = "SELECT action, tstamp, user, data FROM sys_datalog WHERE dbtable = ? AND dbidx = ?";
			$records = $app->db->queryAllRecords($sql, $db_table, $db_table_idx.":".$primary_id);
		} else {
			$sql = "SELECT action, tstamp, user, data FROM sys_datalog WHERE user = ? AND dbtable = ? AND dbidx = ?";
			$records = $app->db->queryAllRecords($sql, $_SESSION["s"]["user"]["username"], $db_table, $db_table_idx.":".$primary_id);
		}

		if(is_array($records)) {
			$content .= '<table>';
			foreach($records as $rec) {
				$content .= "<tr><td>".date("d.m.Y", $rec["tstamp"])."</td><td>".$rec["user"]."</td></tr>";
			}
			$content .= '</table>';
		}

		return $content;

	}

}

?>
