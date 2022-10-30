<?php

class squid_plugin {

	var $plugin_name = 'squid_plugin';
	var $class_name = 'squid_plugin';

	// private variables
	var $action = '';

	//* This function is called during rmnetdov installation to determine
	//  if a symlink shall be created for this plugin.
	function onInstall() {
		global $conf;

		if(isset($conf['services']['proxy']) &&$conf['services']['proxy'] == true && isset($conf['squid']['installed']) && $conf['squid']['installed'] == true) {
			return true;
		} else {
			return false;
		}

	}


	/*
	 	This function is called when the plugin is loaded
	*/

	function onLoad() {
		global $app;

		/*
		Register for the events
		*/


		$app->plugins->registerEvent('proxy_reverse_insert', $this->plugin_name, 'insert');
		$app->plugins->registerEvent('proxy_reverse_update', $this->plugin_name, 'update');
		$app->plugins->registerEvent('proxy_reverse_delete', $this->plugin_name, 'delete');

		$app->plugins->registerEvent('web_domain_insert', $this->plugin_name, 'insert');
		$app->plugins->registerEvent('web_domain_update', $this->plugin_name, 'update');
		$app->plugins->registerEvent('web_domain_delete', $this->plugin_name, 'delete');



	}




	function insert($event_name, $data) {
		global $app, $conf;


		// just run the update function
		$this->update($event_name, $data);


	}


	function update($event_name, $data) {
		global $app, $conf;

		$domains = $this->_getSquidDomains($app);
		$rules = $this->_getSquidRewriteRules($app);

		$app->load('tpl');
		$tpl = new tpl();
		$tpl->newTemplate("squidRewriteRules.py.master");
		if (!empty($rules))$tpl->setLoop('squid_rewrite_rules', $rules);
		file_put_contents('/etc/squid/squidRewriteRules.py', $tpl->grab());
		unset($tpl);
		$app->log('Pisanje konfiguracije prepisovanja squid v /etc/squid/squidRewriteRules.py', LOGLEVEL_DEBUG);


		$tpl = new tpl();
		$tpl->newTemplate("domains.txt.master");
		$tpl->setLoop('squid_domains', $domains);
		file_put_contents('/etc/squid/domains.txt', $tpl->grab());
		unset($tpl);
		$app->log('Pisanje konfiguracije domene squid v /etc/squid/domains.txt', LOGLEVEL_DEBUG);


		// request a httpd reload when all records have been processed
		$app->services->restartServiceDelayed('squid', 'restart');

	}

	function delete($event_name, $data) {
		global $app, $conf;

		// load the server configuration options

		// just run the update function
		$this->update($event_name, $data);

	}

	function _getSquidDomains($app)
	{
		$records = $app->dbmaster->queryAllRecords("SELECT ds.origin, dr.name, IF(origin=name,true,false) AS isRoot FROM dns_soa ds inner join dns_rr dr ON ds.id=dr.zone WHERE ds.active='Y' AND dr.type IN ('A','CNAME') AND dr.name NOT IN ('mail','ns1')");
		$domains = array();
		foreach ($records as $record) {

			$origin = substr($record["origin"], 0, -1);
			if ($record["isRoot"])
			{
				array_push($domains, array("domain" => $origin));
			} else {
				array_push($domains, array("domain" => $record["name"].".".$origin));
			}

		}

		return $domains;

	}

	function _getSquidRewriteRules($app)
	{
		$rules = array();

		$rules = $app->db->queryAllRecords("SELECT rewrite_url_src, rewrite_url_dest FROM squid_reverse ORDER BY rewrite_id ASC");
		$web_domains = $app->db->queryAllRecords("SELECT wd.subdomain, wd.domain, si.ip_address  FROM web_domain wd INNER JOIN server s USING(server_id) INNER JOIN server_ip si USING(server_id)");

		foreach ($web_domains as $domain) {
			if ($domain["subdomain"] == "www") {
				array_push($rules, array("rewrite_url_src"=>"^http://www.".$domain["domain"]."/(.*)", "rewrite_url_dest"=>"http://".$domain["ip_address"].":80/"));
				array_push($rules, array("rewrite_url_src"=>"^http://".$domain["domain"]."/(.*)", "rewrite_url_dest"=>"http://".$domain["ip_address"].":80/"));
			}  else {
				array_push($rules, array("rewrite_url_src"=>"^http://www.".$domain["domain"]."/(.*)", "rewrite_url_dest"=>"http://".$domain["ip_address"].":80/"));
			}
		}
		return $rules;
	}



} // end class

?>
