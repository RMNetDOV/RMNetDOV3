<?php

class cronjob_awstats extends cronjob {

	// job schedule
	protected $_schedule = '0 0 * * *';

	/* this function is optional if it contains no custom code */
	public function onPrepare() {
		global $app;

		parent::onPrepare();
	}

	/* this function is optional if it contains no custom code */
	public function onBeforeRun() {
		global $app;

		return parent::onBeforeRun();
	}

	public function onRunJob() {
		global $app, $conf;

		//######################################################################################################
		// Create awstats statistics
		//######################################################################################################

		$sql = "SELECT domain_id, domain, document_root, web_folder, type, system_user, system_group, parent_domain_id FROM web_domain WHERE (type = 'vhost' or type = 'vhostsubdomain' or type = 'vhostalias') and stats_type = 'awstats' AND server_id = ?";
		$records = $app->db->queryAllRecords($sql, $conf['server_id']);

		$web_config = $app->getconf->get_server_config($conf['server_id'], 'web');

		foreach($records as $rec) {
			$yesterday = date('Ymd', strtotime("-1 day", time()));

			$log_folder = 'log';
			if($rec['type'] == 'vhostsubdomain' || $rec['type'] == 'vhostalias') {
				$tmp = $app->db->queryOneRecord('SELECT `domain` FROM web_domain WHERE domain_id = ?', $rec['parent_domain_id']);
				$subdomain_host = preg_replace('/^(.*)\.' . preg_quote($tmp['domain'], '/') . '$/', '$1', $rec['domain']);
				if($subdomain_host == '') $subdomain_host = 'web'.$rec['domain_id'];
				$log_folder .= '/' . $subdomain_host;
				unset($tmp);
			}
			$logfile = $rec['document_root'].'/' . $log_folder . '/'.$yesterday.'-access.log';
			if(!@is_file($logfile)) {
				$logfile = $rec['document_root'].'/' . $log_folder . '/'.$yesterday.'-access.log.gz';
				if(!@is_file($logfile)) {
					continue;
				}
			}
			$web_folder = (($rec['type'] == 'vhostsubdomain' || $rec['type'] == 'vhostalias') ? $rec['web_folder'] : 'web');
			$domain = $rec['domain'];
			$statsdir = $rec['document_root'].'/'.$web_folder.'/stats';
			$awstats_pl = $web_config['awstats_pl'];
			$awstats_buildstaticpages_pl = $web_config['awstats_buildstaticpages_pl'];

			$awstats_conf_dir = $web_config['awstats_conf_dir'];
			$awstats_website_conf_file = $web_config['awstats_conf_dir'].'/awstats.'.$domain.'.conf';

			$existing_awstats_conf_array = array();
			if(is_file($awstats_website_conf_file)) {
				$existing_awstats_conf = file($awstats_website_conf_file);
				foreach ($existing_awstats_conf as $line) {
					if(preg_match("/Lang=/",$line)) $existing_awstats_conf_array['Lang'] = implode('',parse_ini_string($line));
				}
				unlink($awstats_website_conf_file);
			}

			$sql = "SELECT domain FROM web_domain WHERE (type = 'alias' OR type = 'subdomain') AND parent_domain_id = ?";
			$aliases = $app->db->queryAllRecords($sql, $rec['domain_id']);
			$aliasdomain = '';

			if(is_array($aliases)) {
				foreach ($aliases as $alias) {
					$aliasdomain.= ' '.$alias['domain']. ' www.'.$alias['domain'];
				}
			}

			if(!is_file($awstats_website_conf_file)) {
				if (is_file($awstats_conf_dir."/awstats.conf")) {
                                	$include_file = $awstats_conf_dir."/awstats.conf";
				} elseif (is_file($awstats_conf_dir."/awstats.model.conf")) {
					$include_file = $awstats_conf_dir."/awstats.model.conf";
				}
				$awstats_conf_file_content = 'Include "'.$include_file.'"
        LogFile="/var/log/rmnetdov/httpd/'.$domain.'/yesterday-access.log"
        SiteDomain="'.$domain.'"
        HostAliases="www.'.$domain.' localhost 127.0.0.1'.$aliasdomain.'"';
				if (array_key_exists('Lang',$existing_awstats_conf_array)) $awstats_conf_file_content .='
		Lang="'.$existing_awstats_conf_array['Lang'].'"';
				if (isset($include_file)) {
					file_put_contents($awstats_website_conf_file, $awstats_conf_file_content);
				} else {
					$app->log("No awstats base config found. Either awstats.conf or awstats.model.conf must exist in ".$awstats_conf_dir.".", LOGLEVEL_WARN);
				}
			}

			if(!@is_dir($statsdir)) mkdir($statsdir);
			$username = $rec['system_user'];
			$groupname = $rec['system_group'];
			chown($statsdir, $username);
			chgrp($statsdir, $groupname);
			if(is_link('/var/log/rmnetdov/httpd/'.$domain.'/yesterday-access.log')) unlink('/var/log/rmnetdov/httpd/'.$domain.'/yesterday-access.log');
			symlink($logfile, '/var/log/rmnetdov/httpd/'.$domain.'/yesterday-access.log');

			$awmonth = date("n");
			$awyear = date("Y");

			if (date("d") == 1) {
				$awmonth = date("m")-1;
				if (date("m") == 1) {
					$awyear = date("Y")-1;
					$awmonth = "12";
				}
			}

			$command = escapeshellcmd($awstats_buildstaticpages_pl) . ' -month=' . escapeshellarg($awmonth) . ' -year=' . escapeshellarg($awyear) . ' -update -config=' . escapeshellarg($domain) . ' -dir=' . escapeshellarg($statsdir) . ' -awstatsprog=' . escapeshellarg($awstats_pl);

			if (date("d") == 2) {
				$awmonth = date("m")-1;
				if (date("m") == 1) {
					$awyear = date("Y")-1;
					$awmonth = "12";
				}

				$statsdirold = $statsdir."/".$awyear."-".$awmonth."/";
				if(!is_dir($statsdirold)) {
					mkdir($statsdirold);
				}
				$files = scandir($statsdir);

				if (($key = array_search('index.php', $files)) !== false) {
					unset($files[$key]);
				}

				foreach ($files as $file) {
					if (substr($file, 0, 1) != "." && !is_dir("$statsdir"."/"."$file") && substr($file, 0, 1) != "w" && substr($file, 0, 1) != "i") $app->system->move("$statsdir"."/"."$file", "$statsdirold"."$file");
				}
			}


			if($awstats_pl != '' && $awstats_buildstaticpages_pl != '' && fileowner($awstats_pl) == 0 && fileowner($awstats_buildstaticpages_pl) == 0) {
				exec($command);
				if(is_file($rec['document_root'].'/'.$web_folder.'/stats/index.html')) unlink($rec['document_root'].'/'.$web_folder.'/stats/index.html');
				rename($rec['document_root'].'/'.$web_folder.'/stats/awstats.'.$domain.'.html', $rec['document_root'].'/'.$web_folder.'/stats/awsindex.html');
				if(!is_file($rec['document_root']."/".$web_folder."/stats/index.php")) {
					if(file_exists("/usr/local/rmnetdov/server/conf-custom/awstats_index.php.master")) {
						copy("/usr/local/rmnetdov/server/conf-custom/awstats_index.php.master", $rec['document_root']."/".$web_folder."/stats/index.php");
					} else {
						copy("/usr/local/rmnetdov/server/conf/awstats_index.php.master", $rec['document_root']."/".$web_folder."/stats/index.php");
					}
				}

				$app->log('Created awstats statistics with command: '.$command, LOGLEVEL_DEBUG);
			} else {
				$app->log("No awstats statistics created. Either $awstats_pl or $awstats_buildstaticpages_pl is not owned by root user.", LOGLEVEL_WARN);
			}

			if(is_file($rec['document_root']."/".$web_folder."/stats/index.php")) {
				chown($rec['document_root']."/".$web_folder."/stats/index.php", $rec['system_user']);
				chgrp($rec['document_root']."/".$web_folder."/stats/index.php", $rec['system_group']);
			}

			$app->system->exec_safe('chown -R ?:? ?', $username, $groupname, $statsdir);
		}


		parent::onRunJob();
	}

	/* this function is optional if it contains no custom code */
	public function onAfterRun() {
		global $app;

		parent::onAfterRun();
	}

}

?>
