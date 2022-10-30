<?php

//* Remote functions of the monitor module
class remoting_monitor extends remoting {

	//* get the number of pending jobs from jobqueue
	public function monitor_jobqueue_count($session_id, $server_id = 0)
	{
		global $app;

		if(!$this->checkPerm($session_id, 'monitor_jobqueue_count')) {
			throw new SoapFault('permission_denied', 'You do not have the permissions to access this function.');
		}
		
		$server_id = intval($server_id);
		
		if($server_id == 0) {
			$servers = $app->db->queryAllRecords("SELECT server_id, updated FROM server");
			$sql = 'SELECT count(datalog_id) as jobqueue_count FROM sys_datalog WHERE ';
			foreach($servers as $sv) {
				$sql .= " (datalog_id > ".$sv['updated']." AND server_id IN (0,".$sv['server_id'].")) OR ";
			}
			$sql = substr($sql, 0, -4);
			$tmp = $app->db->queryOneRecord($sql);
			return $tmp['jobqueue_count'];
			
		} else {
			$server = $app->db->queryOneRecord("SELECT updated FROM server WHERE server_id = ?",$server_id);
			$tmp = $app->db->queryOneRecord('SELECT count(datalog_id) as jobqueue_count FROM sys_datalog WHERE datalog_id > ? AND server_id IN ?',$server['updated'], array(0, $server_id));
			return $tmp['jobqueue_count'];
		}
	}

}

?>
