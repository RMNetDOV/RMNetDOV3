<?php

class remoting_domains extends remoting {
	// -----------------------------------------------------------------------------------------------

	//* Get record details
	public function domains_domain_get($session_id, $primary_id)
	{
		global $app;

		if(!$this->checkPerm($session_id, 'domains_domain_get')) {
			throw new SoapFault('permission_denied', 'You do not have the permissions to access this function.');
			return false;
		}
		$app->uses('remoting_lib');
		$app->remoting_lib->loadFormDef('../client/form/domain.tform.php');
		return $app->remoting_lib->getDataRecord($primary_id);
	}

	//* Add a record
	public function domains_domain_add($session_id, $client_id, $params)
	{
		if(!$this->checkPerm($session_id, 'domains_domain_add')) {
			throw new SoapFault('permission_denied', 'You do not have the permissions to access this function.');
			return false;
		}
		return $this->insertQuery('../client/form/domain.tform.php', $client_id, $params);
	}

	//* Update a record
	public function domains_domain_update($session_id, $client_id, $primary_id, $params)
	{
		if(!$this->checkPerm($session_id, 'domains_domain_update')) {
			throw new SoapFault('permission_denied', 'You do not have the permissions to access this function.');
			return false;
		}
		return $this->updateQuery('../client/form/domain.tform.php', $client_id, $primary_id, $params);
	}

	//* Delete a record
	public function domains_domain_delete($session_id, $primary_id)
	{
		if(!$this->checkPerm($session_id, 'domains_domain_delete')) {
			throw new SoapFault('permission_denied', 'You do not have the permissions to access this function.');
			return false;
		}
		$affected_rows = $this->deleteQuery('../client/form/domain.tform.php', $primary_id);
		return $affected_rows;
	}

	// -----------------------------------------------------------------------------------------------

	public function domains_get_all_by_user($session_id, $group_id)
	{
		global $app;
		if(!$this->checkPerm($session_id, 'domains_get_all_by_user')) {
			throw new SoapFault('permission_denied', 'You do not have the permissions to access this function.');
			return false;
		}
		$group_id = $app->functions->intval($group_id);
		$sql = "SELECT domain_id, domain FROM domain WHERE sys_groupid  = ?";
		$all = $app->db->queryAllRecords($sql, $group_id);
		return $all;
	}

}

?>
