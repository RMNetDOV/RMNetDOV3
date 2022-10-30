<?php

class sites_database_plugin {

	public function processDatabaseInsert($form_page) {
		global $app;

		$this->processDatabaseUpdate($form_page);
	}

	public function processDatabaseUpdate($form_page) {
		global $app;

		if($form_page->dataRecord["parent_domain_id"] > 0) {
			$web = $app->db->queryOneRecord("SELECT * FROM web_domain WHERE domain_id = ?", $form_page->dataRecord["parent_domain_id"]);

			//* The Database user shall be owned by the same group then the website
			$sys_groupid = $app->functions->intval($web['sys_groupid']);
			$backup_interval = $web['backup_interval'];
			$backup_format_web = $web['backup_format_web'];
			$backup_format_db = $web['backup_format_db'];
			$backup_copies = $app->functions->intval($web['backup_copies']);

			$sql = "UPDATE web_database SET sys_groupid = ?, backup_interval = ?, backup_copies = ? WHERE database_id = ?";
			$app->db->query($sql, $sys_groupid, $backup_interval, $backup_copies, $form_page->id);
		}
	}

	public function processDatabaseDelete($primary_id) {
		global $app;

	}

}

?>
