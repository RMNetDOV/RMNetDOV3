<?php
/*
TABLE STRUCTURE of the "named" database:

CREATE TABLE IF NOT EXISTS `records` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `zone` varchar(255) NOT NULL,
  `ttl` int(11) NOT NULL default '3600',
  `type` varchar(255) NOT NULL,
  `host` varchar(255) NOT NULL default '@',
  `mx_priority` int(11) default NULL,
  `data` text,
  `primary_ns` varchar(255) default NULL,
  `resp_contact` varchar(255) default NULL,
  `serial` bigint(20) default NULL,
  `refresh` int(11) default NULL,
  `retry` int(11) default NULL,
  `expire` int(11) default NULL,
  `minimum` int(11) default NULL,
  `rmnetdov_id` int(11) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `type` (`type`),
  KEY `host` (`host`),
  KEY `zone` (`zone`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `xfr` (
  `id` int(11) NOT NULL auto_increment,
  `zone` varchar(255) NOT NULL,
  `client` varchar(255) NOT NULL,
  `rmnetdov_id` int(11) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `zone` (`zone`),
  KEY `client` (`client`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

*/

class bind_dlz_plugin {

	var $plugin_name = 'bind_dlz_plugin';
	var $class_name  = 'bind_dlz_plugin';

	//* This function is called during rmnetdov installation to determine
	//  if a symlink shall be created for this plugin.
	function onInstall()
	{
		global $conf;

		if(isset($conf['bind']['installed']) && $conf['bind']['installed'] == true) {
			// Temporarily disabled until the installer supports the automatic creation of the necessary
			// database or at least to select between filebased nd db based bind, as not all bind versions
			// support dlz out of the box. To enable this plugin manually, create a symlink from the plugins-enabled
			// directory to this file in the plugins-available directory.
			return false;
			//return true;
		} else {
			return false;
		}

	}

	/*
	 	This function is called when the plugin is loaded
	*/

	function onLoad()
	{
		global $app;

		/*
		Register for the events
		*/

		//* SOA
		$app->plugins->registerEvent('dns_soa_insert', $this->plugin_name, 'soa_insert');
		$app->plugins->registerEvent('dns_soa_update', $this->plugin_name, 'soa_update');
		$app->plugins->registerEvent('dns_soa_delete', $this->plugin_name, 'soa_delete');

		//* RR
		$app->plugins->registerEvent('dns_rr_insert', $this->plugin_name, 'rr_insert');
		$app->plugins->registerEvent('dns_rr_update', $this->plugin_name, 'rr_update');
		$app->plugins->registerEvent('dns_rr_delete', $this->plugin_name, 'rr_delete');
	}


	function soa_insert($event_name, $data)
	{
		global $app, $conf;

		if($data["new"]["active"] != 'Y') return;

		$origin = substr($data["new"]["origin"], 0, -1);
		$rmnetdov_id = $data["new"]["id"];
		$serial = $app->db->queryOneRecord("SELECT * FROM dns_soa WHERE id = ?", $rmnetdov_id);

		$ttl = $data["new"]["ttl"];

		//$_db = clone $app->db;
		//$_db->dbName = 'named';

		$app->db->query("INSERT INTO named.records (zone, ttl, type, primary_ns, resp_contact, serial, refresh, retry, expire, minimum, rmnetdov_id) VALUES ".
			"(?, ?, 'SOA', ?, ?, ?, ?, ?, ?, ?, ?)", $origin, $ttl, $data["new"]["ns"], $data["new"]["mbox"], $serial["serial"], $serial["refresh"], $serial["retry"], $serial["expire"], $serial["minimum"], $rmnetdov_id);
		//unset($_db);
	}

	function soa_update($event_name, $data)
	{
		global $app, $conf;

		if($data["new"]["active"] != 'Y')
		{
			if($data["old"]["active"] != 'Y') return;
			$this->soa_delete($event_name, $data);
		}
		else
		{
			if($data["old"]["active"] == 'Y')
			{
				$origin = substr($data["new"]["origin"], 0, -1);
				$rmnetdov_id = $data["new"]["id"];
				$serial = $app->db->queryOneRecord("SELECT * FROM dns_soa WHERE id = ?", $rmnetdov_id);

				$ttl = $data["new"]["ttl"];

				//$_db = clone $app->db;
				//$_db->dbName = 'named';

				$app->db->query("UPDATE named.records SET zone = ?, ttl = ?, primary_ns = ?, resp_contact = ?, serial = ?, refresh = ?, retry = ?, expire = ?, minimum = ? WHERE rmnetdov_id = ? AND type = 'SOA'", $origin, $ttl, $data["new"]["ns"], $data["new"]["mbox"], $serial["serial"], $serial["refresh"], $serial["retry"], $serial["expire"], $serial["minimum"], $data["new"]["id"]);
				//unset($_db);
			}
			else
			{
				$this->soa_insert($event_name, $data);
				$rmnetdov_id = $data["new"]["id"];

				if ($records = $app->db->queryAllRecords("SELECT * FROM dns_rr WHERE zone = ? AND active = 'Y'", $rmnetdov_id))
				{
					foreach($records as $record)
					{
						foreach ($record as $key => $val) {
							$data["new"][$key] = $val;
						}
						$this->rr_insert("dns_rr_insert", $data);
					}
				}
			}
		}

	}

	function soa_delete($event_name, $data)
	{
		global $app, $conf;

		//$_db = clone $app->db;
		//$_db->dbName = 'named';

		$app->db->query( "DELETE FROM named.dns_records WHERE zone = ?", substr($data['old']['origin'], 0, -1));
		//unset($_db);
	}

	function rr_insert($event_name, $data)
	{
		global $app, $conf;
		if($data["new"]["active"] != 'Y') return;

		$zone = $app->db->queryOneRecord("SELECT * FROM dns_soa WHERE id = ?", $data["new"]["zone"]);
		$origin = substr($zone["origin"], 0, -1);
		$rmnetdov_id = $data["new"]["id"];

		$type = $data["new"]["type"];

		if (substr($data["new"]["name"], -1) == '.') {
			$name = substr($data["new"]["name"], 0, -1);
		} else {
			$name = ($data["new"]["name"] == "") ? $name = '@' : $data["new"]["name"];
		}

		if ($name == $origin || $name == '') {
			$name = '@';
		}

		switch ($type)
		{
		case "CNAME":
		case "MX":
		case "NS":
		case "ALIAS":
		case "PTR":
		case "SRV":
			if(substr($data["new"]["data"], -1) != '.'){
				$content = $data["new"]["data"] . '.';
			} else {
				$content = $data["new"]["data"];
			}
			break;
		case "HINFO":
			$content = $data["new"]["data"];
			$quote1 = strpos($content, '"');

			if($quote1 !== FALSE) {
				$quote2 = strpos(substr($content, ($quote1 + 1)), '"');
			}

			if ($quote1 !== FALSE && $quote2 !== FALSE) {
				$text_between_quotes = str_replace(' ', '_', substr($content, ($quote1 + 1), (($quote2 - $quote1))));
				$content = $text_between_quotes.substr($content, ($quote2 + 2));
			}
			break;
		default:
			$content = $data["new"]["data"];
		}

		$ttl = $data["new"]["ttl"];

		//$_db = clone $app->db;
		//$_db->dbName = 'named';

		if ($type == 'MX') {
			$app->db->query("INSERT INTO named.records (zone, ttl, type, host, mx_priority, data, rmnetdov_id)".
				" VALUES (?, ?, ?, ?, ?, ?, ?)", $origin, $ttl, $type, $name, $data["new"]["aux"], $content, $rmnetdov_id);
		} elseif ($type == 'SRV') {
			$app->db->query("INSERT INTO named.records (zone, ttl, type, data, rmnetdov_id)".
				" VALUES (?, ?, ?, ?, ?)", $origin, $ttl, $type, $data["new"]["aux"] . ' ' . $content, $rmnetdov_id);
		} else {
			$app->db->query("INSERT INTO named.records (zone, ttl, type, host, data, rmnetdov_id)".
				" VALUES (?, ?, ?, ?, ?, ?)", $origin, $ttl, $type, $name, $content, $rmnetdov_id);
		}

		//unset($_db);
	}

	function rr_update($event_name, $data)
	{
		global $app, $conf;

		if ($data["new"]["active"] != 'Y')
		{
			if($data["old"]["active"] != 'Y') return;
			$this->rr_delete($event_name, $data);
		}
		else
		{
			if ($data["old"]["active"] == 'Y')
			{
				$zone = $app->db->queryOneRecord("SELECT * FROM dns_soa WHERE id = ?", $data["new"]["zone"]);
				$origin = substr($zone["origin"], 0, -1);
				$rmnetdov_id = $data["new"]["id"];

				$type = $data["new"]["type"];

				if (substr($data["new"]["name"], -1) == '.') {
					$name = substr($data["new"]["name"], 0, -1);
				} else {
					$name = ($data["new"]["name"] == "") ? $name = '@' : $data["new"]["name"];
				}

				if ($name == $origin || $name == '') {
					$name = '@';
				}

				switch ($type)
				{
				case "CNAME":
				case "MX":
				case "NS":
				case "ALIAS":
				case "PTR":
				case "SRV":
					if(substr($data["new"]["data"], -1) != '.'){
						$content = $data["new"]["data"] . '.';
					} else {
						$content = $data["new"]["data"];
					}
					break;
				case "HINFO":
					$content = $data["new"]["data"];
					$quote1 = strpos($content, '"');
					if($quote1 !== FALSE){
						$quote2 = strpos(substr($content, ($quote1 + 1)), '"');
					}
					if($quote1 !== FALSE && $quote2 !== FALSE){
						$text_between_quotes = str_replace(' ', '_', substr($content, ($quote1 + 1), (($quote2 - $quote1))));
						$content = $text_between_quotes.substr($content, ($quote2 + 2));
					}
					break;
				default:
					$content = $data["new"]["data"];
				}

				$ttl = $data["new"]["ttl"];
				$prio = (int)$data["new"]["aux"];

				//$_db = clone $app->db;
				//$_db->dbName = 'named';

				if ($type == 'MX') {
					$app->db->query("UPDATE named.records SET zone = ?, ttl = ?, type = ?, host = ?, mx_priority = ?, data = ? WHERE rmnetdov_id = ? AND type != 'SOA'", $origin, $ttl, $type, $name, $prio, $content, $rmnetdov_id);
				} elseif ($type == 'SRV') {
					$app->db->query("UPDATE named.records SET zone = ?, ttl = ?, type = ?, data = ? WHERE rmnetdov_id = ? AND type != 'SOA'", $origin, $ttl, $type, $prio . ' ' . $content, $rmnetdov_id);
				} else {
					$app->db->query("UPDATE named.records SET zone = ?, ttl = ?, type = ?, host = ?, data = ? WHERE rmnetdov_id = ? AND type != 'SOA'", $origin, $ttl, $type, $name, $content, $rmnetdov_id);
				}

				//unset($_db);
			} else {
				$this->rr_insert($event_name, $data);
			}
		}
	}

	function rr_delete($event_name, $data) {
		global $app, $conf;

		//$_db = clone $app->db;
		//$_db->dbName = 'named';

		$app->db->query( "DELETE FROM named.dns_records WHERE type != 'SOA' AND zone = ?", substr($data['old']['origin'], 0, -1));
		//unset($_db);
	}

} // end class
?>
