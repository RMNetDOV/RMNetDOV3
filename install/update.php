<?php

/*
	RM-Net - DOV CP 3 posodobitev.

	-------------------------------------------------------------------------------------
	- Interaktivna posodobitev
	-------------------------------------------------------------------------------------
	run:

	php update.php

	-------------------------------------------------------------------------------------
	- Neinteraktivni način (samodejno posodabljanje).
	-------------------------------------------------------------------------------------

    Način samodejnega posodabljanja lahko prebere vprašanja programa za posodabljanje iz datoteke v slogu .ini ali iz
    konfiguracijsko datoteko php. Primeri za obe vrsti datotek so v mapi dokumenti.
	Glej autoinstall.ini.sample in autoinstall.conf_sample.php.

	run:

	php update.php --autoinstall=autoinstall.ini

	or

	php update.php --autoinstall=autoinstall.conf.php

*/

error_reporting(E_ALL|E_STRICT);

define('INSTALLER_RUN', true);
define('INSTALLER_UPDATE', true);

//** Pasica v ukazni vrstici
echo "\n\n".str_repeat('-', 80)."\n";
echo "
  ____  __  __       _   _      _             ____   _____     __   ____ ____
 |  _ \|  \/  |     | \ | | ___| |_          |  _ \ / _ \ \   / /  / ___|  _ \
 | |_) | |\/| |_____|  \| |/ _ \ __|  _____  | | | | | | \ \ / /  | |   | |_) |
 |  _ <| |  | |_____| |\  |  __/ |_  |_____| | |_| | |_| |\ V /   | |___|  __/
 |_| \_\_|  |_|     |_| \_|\___|\__|         |____/ \___/  \_/     \____|_|
";
echo "\n".str_repeat('-', 80)."\n";
echo "\n\n>> Nadgradnja  \n\n";

//** Vključite knjižnico z osnovnimi funkcijami namestitvenega programa
require_once 'lib/install.lib.php';

//** Vključite knjižnico z osnovnimi funkcijami posodobitve
require_once 'lib/update.lib.php';

//** Vključite osnovni razred razreda namestitvenega programa
require_once 'lib/installer_base.lib.php';

//** Prepričajte se, da je trenutni delovni imenik namestitveni imenik
$cur_dir = getcwd();
if(realpath(dirname(__FILE__)) != $cur_dir) die("Zaženite namestitev/posodobitev from _inside_ the install directory!\n");

//** Namesti dnevniško datoteko
define('RMNETDOV_LOG_FILE', '/var/log/rmnetdov_install.log');
define('RMNETDOV_INSTALL_ROOT', realpath(dirname(__FILE__).'/../'));

//** Vključite lib
require_once 'lib/classes/tpl.inc.php';

//** Preverite različice RM-Net - DOV CP 2.x
if(is_dir('/root/rmnetdov') || is_dir('/home/admrmnetdov')) {
	if(is_dir('/home/admrmnetdov')) {
		die('Te programske opreme ni mogoče namestiti na strežnik, ki poganja RM-Net - DOV CP 2.x.');
	} else {
		die('Te programske opreme ni mogoče namestiti na strežnik, ki poganja RM-Net - DOV CP 2.x; prisotnost /root/rmnetdov/ imenik lahko nakazuje namestitev RM-Net - DOV CP 2.x, sicer ga lahko odstranite ali preimenujete za nadaljevanje.');
	}
}

// Za popravilo najnovejših različic macosov je potreben popravek
if(is_installed('amavisd-new') && !is_installed('patch')) die('Manjka ukaz patch. Namestite ukaz za popravek in znova zaženite posodobitev.');

//** Pridobite identifikator distribucije
$dist = get_distname();

include_once "/usr/local/rmnetdov/server/lib/config.inc.php";
$conf_old = $conf;
unset($conf);

if($dist['id'] == '') die('Distribucija ali različica Linuxa ni prepoznana.');

//** Vključite konfiguracijo samodejnega namestitvenega programa (za neinteraktivne nastavitve)
error_reporting(E_ALL ^ E_NOTICE);

//** Pridobite možnosti ukazne vrstice
$cmd_opt = getopt('', array('autoinstall::'));

//** Naloži datoteko za samodejno namestitev
if(isset($cmd_opt['autoinstall']) && is_file($cmd_opt['autoinstall'])) {
	$path_parts = pathinfo($cmd_opt['autoinstall']);
	if($path_parts['extension'] == 'php') {
		include_once $cmd_opt['autoinstall'];
	} elseif($path_parts['extension'] == 'ini') {
		if(is_file('autoinstall.ini')) {
			$tmp = ini_to_array(file_get_contents('autoinstall.ini'));
		} else {
			$tmp = ini_to_array(file_get_contents($cmd_opt['autoinstall']));
		}
		if(!is_array($tmp['install'])) $tmp['install'] = array();
		if(!is_array($tmp['ssl_cert'])) $tmp['ssl_cert'] = array();
		if(!is_array($tmp['expert'])) $tmp['expert'] = array();
		if(!is_array($tmp['update'])) $tmp['update'] = array();
		$autoinstall = $tmp['install'] + $tmp['ssl_cert'] + $tmp['expert'] + $tmp['update'];
		unset($tmp);
	}
	unset($path_parts);
	define('AUTOINSTALL', true);
} else {
	$autoinstall = array();
	define('AUTOINSTALL', false);
}

//** Vključite knjižnico in konfiguracijo namestitvenega programa, specifično za distribucijo
if(is_file('dist/lib/'.$dist['baseid'].'.lib.php')) include_once 'dist/lib/'.$dist['baseid'].'.lib.php';
include_once 'dist/lib/'.$dist['id'].'.lib.php';
include_once 'dist/conf/'.$dist['confid'].'.conf.php';

//** Pridobite ime gostitelja
exec('hostname -f', $tmp_out);
$conf['hostname'] = $tmp_out[0];
unset($tmp_out);

//** Nastavite podatke za prijavo v mysql
$conf["mysql"]["host"] = $conf_old["db_host"];
$conf["mysql"]["database"] = $conf_old["db_database"];
$conf['mysql']['charset'] = 'utf8';
$conf["mysql"]["rmnetdov_user"] = $conf_old["db_user"];
$conf["mysql"]["rmnetdov_password"] = $conf_old["db_password"];
$conf['mysql']['port'] = $conf_old["db_port"];
if($conf['mysql']['port'] == 0 || $conf['mysql']['port'] == '') $conf['mysql']['port'] = 3306;
$conf['language'] = $conf_old['language'];
$conf['theme'] = $conf_old['theme'];
if($conf['language'] == '{language}') $conf['language'] = 'sl';
$conf['timezone'] = (isset($conf_old['timezone']))?$conf_old['timezone']:'UTC';
if($conf['timezone'] == '{timezone}' or trim($conf['timezone']) == '') $conf['timezone'] = 'UTC';
$conf['language_file_import_enabled'] = (isset($conf_old['language_file_import_enabled']))?$conf_old['language_file_import_enabled']:true;

if(isset($conf_old["dbmaster_host"])) $conf["mysql"]["master_host"] = $conf_old["dbmaster_host"];
if(isset($conf_old["dbmaster_port"])) $conf["mysql"]["master_port"] = $conf_old["dbmaster_port"];
if(isset($conf_old["dbmaster_database"])) $conf["mysql"]["master_database"] = $conf_old["dbmaster_database"];
if(isset($conf_old["dbmaster_user"])) $conf["mysql"]["master_rmnetdov_user"] = $conf_old["dbmaster_user"];
if(isset($conf_old["dbmaster_password"])) $conf["mysql"]["master_rmnetdov_password"] = $conf_old["dbmaster_password"];

//* Preverite, ali je to nastavitev glavni/podrejeni
if($conf["mysql"]["master_host"] != '' && $conf["mysql"]["host"] != $conf["mysql"]["master_host"]) {
	$conf['mysql']['master_slave_setup'] = 'y';
}

// Razreši naslov IP imena gostitelja mysql.
if(!$conf['mysql']['ip'] = gethostbyname($conf['mysql']['host'])) die('Ni mogoče razrešiti imena gostitelja'.$conf['mysql']['host']);

$conf['server_id'] = intval($conf_old["server_id"]);
$conf['rmnetdov_log_priority'] = $conf_old["log_priority"];

$inst = new installer();
$inst->is_update = true;

$inst->check_prerequisites();

echo "Ta aplikacija bo posodobila RM-Net - DOV CP 3 na vašem strežniku.\n\n";

//* Naredite varnostno kopijo, preden začnemo s posodobitvijo
$do_backup = $inst->simple_query('Ali naj skript ustvari varnostno kopijo RM-Net - DOV CP /var/backup/ zdaj?', array('yes', 'no'), 'yes','do_backup');

if($do_backup == 'yes') {

	//* Ustvarite rezervni imenik
	$backup_path = '/var/backup/rmnetdov_'.$conf['hostname'].'_'.@date('Y-m-d_H-i');
	$conf['backup_path'] = $backup_path;
	exec("mkdir -p $backup_path");
	exec("chown root:root $backup_path");
	exec("chmod 700 $backup_path");

	//* Naredi varnostno kopijo
	swriteln('Ustvarjanje varnostne kopije imenika "/usr/local/rmnetdov"...');
	exec("tar pcfz $backup_path/rmnetdov_software.tar.gz /usr/local/rmnetdov 2> /dev/null", $out, $returnvar);
	if($returnvar != 0) die("Varnostno kopiranje ni uspelo. Tu se ustavimo...\n");

	swriteln('Ustvarjanje varnostne kopije imenika "/etc"....');
	exec("tar pcfz $backup_path/etc.tar.gz /etc 2> /dev/null", $out, $returnvar);
	if($returnvar != 0) die("Varnostno kopiranje ni uspelo. Tu se ustavimo...\n");

  if (is_dir('/root/.acme.sh')) {
    swriteln('Ustvarjanje varnostne kopije imenika "/root/.acme.sh"....');
    exec("tar pcfz $backup_path/acme.sh.tar.gz /root/.acme.sh 2> /dev/null", $out, $returnvar);
    if($returnvar != 0) die("Varnostno kopiranje ni uspelo. Tu se ustavimo...\n");
  }

  if (is_dir('/etc/letsencrypt')) {
    swriteln('Ustvarjanje varnostne kopije imenika "/etc/letsencrypt"....');
    exec("tar pcfz $backup_path/certbot.tar.gz /etc/letsencrypt 2> /dev/null", $out, $returnvar);
    if($returnvar != 0) die("Varnostno kopiranje ni uspelo. Tu se ustavimo...\n");
  }


	exec("chown root:root $backup_path/*.tar.gz");
	exec("chmod 700 $backup_path/*.tar.gz");
}


//** Inicializirajte povezavo s strežnikom MySQL
include_once 'lib/mysql.lib.php';

//** Posodobitev baze podatkov je nekoliko groba in jo je treba pozneje obnoviti ;)

/*
 * Poskusite prebrati nastavitve DB-admin
 */
$clientdb_host   = '';
$clientdb_user   = '';
$clientdb_password  = '';
include_once "/usr/local/rmnetdov/server/lib/mysql_clientdb.conf";
$conf["mysql"]["admin_user"] = $clientdb_user;
$conf["mysql"]["admin_password"] = $clientdb_password;
$clientdb_host   = '';
$clientdb_user   = '';
$clientdb_password  = '';

//** Preizkusite root povezavo mysql
$finished = false;
do {
	if(@mysqli_connect($conf["mysql"]["host"], $conf["mysql"]["admin_user"], $conf["mysql"]["admin_password"])) {
		$finished = true;
	} else {
		swriteln($inst->lng('Ni mogoče vzpostaviti povezave s strežnikom mysql').' '.mysqli_connect_error());
		$conf["mysql"]["admin_password"] = $inst->free_query('MySQL root geslo', $conf['mysql']['admin_password'],'mysql_root_password');
	}
} while ($finished == false);
unset($finished);

/*
 *  Pripravite izpis baze podatkov
 */
prepareDBDump();

//* inicializirati bazo podatkov
$inst->db = new db();
$inst->db->setDBData($conf['mysql']["host"], $conf['mysql']["rmnetdov_user"], $conf['mysql']["rmnetdov_password"], $conf['mysql']["port"]);
$inst->db->setDBName($conf['mysql']['database']);

//* inicializirati glavno DB, če imamo nastavitev z več strežniki
if($conf['mysql']['master_slave_setup'] == 'y') {
	//** Pridobite poverilnice za root MySQL
	$finished = false;
	do {
		$tmp_mysql_server_host = $inst->free_query('MySQL master server hostname', $conf['mysql']['master_host'],'mysql_master_hostname');
		$tmp_mysql_server_port = $inst->free_query('MySQL master server port', $conf['mysql']['master_port'],'mysql_master_port');
		$tmp_mysql_server_admin_user = $inst->free_query('MySQL master server root username', $conf['mysql']['master_admin_user'],'mysql_master_root_user');
		$tmp_mysql_server_admin_password = $inst->free_query('MySQL master server root password', $conf['mysql']['master_admin_password'],'mysql_master_root_password');
		$tmp_mysql_server_database = $inst->free_query('MySQL master server database name', $conf['mysql']['master_database'],'mysql_master_database');

		//* Inicializirajte povezavo s strežnikom MySQL
		if(@mysqli_connect($tmp_mysql_server_host, $tmp_mysql_server_admin_user, $tmp_mysql_server_admin_password, $tmp_mysql_server_database, (int)$tmp_mysql_server_port)) {
			$conf['mysql']['master_host'] = $tmp_mysql_server_host;
			$conf['mysql']['master_port'] = $tmp_mysql_server_port;
			$conf['mysql']['master_admin_user'] = $tmp_mysql_server_admin_user;
			$conf['mysql']['master_admin_password'] = $tmp_mysql_server_admin_password;
			$conf['mysql']['master_database'] = $tmp_mysql_server_database;
			$finished = true;
		} else {
			swriteln($inst->lng('Ni mogoče vzpostaviti povezave s strežnikom mysql').' '.mysqli_connect_error());
		}
	} while ($finished == false);
	unset($finished);

	// inicializirati povezavo z glavno bazo podatkov
	$inst->dbmaster = new db();
	if($inst->dbmaster->linkId) $inst->dbmaster->closeConn();
	$inst->dbmaster->setDBData($conf['mysql']["master_host"], $conf['mysql']["master_admin_user"], $conf['mysql']["master_admin_password"], $conf['mysql']["master_port"]);
	$inst->dbmaster->setDBName($conf['mysql']["master_database"]);
} else {
	$inst->dbmaster = $inst->db;
}

/*
 *  Preverite vse tabele
*/
checkDbHealth();


/*
 * Preverite prijavo v ukazno vrstico mysql
 */
if( !empty($conf["mysql"]["admin_password"]) ) {
	$cmd = "mysql --default-character-set=".escapeshellarg($conf['mysql']['charset'])." --force -h ".escapeshellarg($conf['mysql']['host'])." -u ".escapeshellarg($conf['mysql']['admin_user'])." -p".escapeshellarg($conf['mysql']['admin_password'])." -P ".escapeshellarg($conf['mysql']['port'])." -D ".escapeshellarg($conf['mysql']['database'])." -e ". escapeshellarg('SHOW DATABASES');
} else {
	$cmd = "mysql --default-character-set=".escapeshellarg($conf['mysql']['charset'])." --force -h ".escapeshellarg($conf['mysql']['host'])." -u ".escapeshellarg($conf['mysql']['admin_user'])." -P ".escapeshellarg($conf['mysql']['port'])." -D ".escapeshellarg($conf['mysql']['database'])." -e ". escapeshellarg('SHOW DATABASES');
}
$retval = 0;
$retout = array();
exec($cmd, $retout, $retval);
if($retval != 0) {
	die("Ni mogoče poklicati ukazne vrstice mysql s poverilnicami iz mysql_clientdb.conf\n");
}

/*
 *  izpis nove zbirke podatkov in ponovno konfigurirajte server.ini
 */
updateDbAndIni();

//** preberi konfiguracijo strežnika iz db v $conf['server_config']
$tmp = $inst->db->queryOneRecord("SELECT config FROM ?? WHERE server_id = ?", $conf["mysql"]["database"] . '.server', $conf['server_id']);
$conf['server_config'] = ini_to_array(stripslashes($tmp['config']));
unset($tmp);

/*
 * Po potrebi znova konfigurirajte dovoljenje
 * (če se to naredi na strani odjemalca, se posodobi samo ta odjemalec.
 * Če se to izvede na strani strežnika, se posodobijo vsi odjemalci.
 */
//if($conf_old['dbmaster_user'] != '' or $conf_old['dbmaster_host'] != '') {
//** Posodobite pravice glavne baze podatkov
$reconfigure_master_database_rights_answer = $inst->simple_query('Ponovno konfigurirajte dovoljenja v glavni zbirki podatkov?', array('yes', 'no'), 'no','reconfigure_permissions_in_master_database');

if($reconfigure_master_database_rights_answer == 'yes') {
	$inst->grant_master_database_rights();
}
//}

//** Zaznajte nameščene aplikacije
$inst->find_installed_apps();

//** Preverite trenutno stanje konfiguracije storitve in primerjajte z našimi rezultati
if ($conf['mysql']['master_slave_setup'] == 'y') $current_svc_config = $inst->dbmaster->queryOneRecord("SELECT mail_server,web_server,dns_server,xmpp_server,firewall_server,vserver_server,db_server FROM ?? WHERE server_id=?", $conf['mysql']['master_database'] . '.server', $conf['server_id']);
else $current_svc_config = $inst->db->queryOneRecord("SELECT mail_server,web_server,dns_server,xmpp_server,firewall_server,vserver_server,db_server FROM ?? WHERE server_id=?", $conf["mysql"]["database"] . '.server', $conf['server_id']);
$conf['services']['mail'] = check_service_config_state('mail_server', $conf['postfix']['installed']);
$conf['services']['dns'] = check_service_config_state('dns_server', ($conf['powerdns']['installed'] || $conf['bind']['installed'] || $conf['mydns']['installed']));
$conf['services']['web'] = check_service_config_state('web_server', ($conf['apache']['installed'] || $conf['nginx']['installed']));
$conf['services']['xmpp'] = check_service_config_state('xmpp_server', $conf['xmpp']['installed']);
$conf['services']['firewall'] = check_service_config_state('firewall_server', ($conf['ufw']['installed'] || $conf['firewall']['installed']));
$conf['services']['vserver'] = check_service_config_state('vserver_server', $conf['services']['vserver']);
$conf['services']['db'] = check_service_config_state('db_server', true); /* Will always offer as MySQL is of course installed on this host as it's a requirement for RMNETDO to work... */
unset($current_svc_config);

//** Zapišite nove odločitve v DB
$sql = "UPDATE ?? SET mail_server = '{$conf['services']['mail']}', web_server = '{$conf['services']['web']}', dns_server = '{$conf['services']['dns']}', file_server = '{$conf['services']['file']}', db_server = '{$conf['services']['db']}', vserver_server = '{$conf['services']['vserver']}', proxy_server = '{$conf['services']['proxy']}', firewall_server = '$firewall_server_enabled', xmpp_server = '$xmpp_server_enabled' WHERE server_id = ?";
$inst->db->query($sql, $conf['mysql']['database'].'.server', $conf['server_id']);
if($conf['mysql']['master_slave_setup'] == 'y') {
	$inst->dbmaster->query($sql, $conf['mysql']['master_database'].'.server', $conf['server_id']);
}

//** Ali je plošča RM-Net - DOV CP nameščena na tem gostitelju? To lahko kasneje delno preglasi uporabniške nastavitve.
if($conf['apache']['installed'] == true){
	if(!is_file($conf['apache']['vhost_conf_dir'].'/rmnetdov.vhost')) $inst->install_rmnetdov_interface = false;
}
elseif($conf['nginx']['installed'] == true){
	if(!is_file($conf['nginx']['vhost_conf_dir'].'/rmnetdov.vhost')) $inst->install_rmnetdov_interface = false;
}
else {
	// Če noben spletni strežnik ni nameščen, potem to ne more biti strežnik, ki gosti vmesnik rmnetdov.
	$inst->install_rmnetdov_interface = false;
}

//** Ali naj bodo storitve med posodobitvijo znova konfigurirane
$reconfigure_services_answer = $inst->simple_query('Ponovno konfigurirajte storitve?', array('yes', 'no', 'selected'), 'yes','reconfigure_services');

if($reconfigure_services_answer == 'yes' || $reconfigure_services_answer == 'selected') {

	checkAndRenameCustomTemplates();

	if($conf['services']['mail']) {

		//** Konfiguriranje postfix
		if($inst->reconfigure_app('Postfix and IMAP/POP3', $reconfigure_services_answer)) {
			swriteln('Konfiguriranje Postfix');
			$inst->configure_postfix('dont-create-certs');

			if($conf['dovecot']['installed'] == true) {
				//* Konfiguriranje dovecot
				swriteln('Konfiguriranje Dovecot');
				$inst->configure_dovecot();
			} elseif ($conf['courier']['installed'] == true) {
				//** Konfiguriranje saslauthd
				swriteln('Konfiguriranje SASL');
				$inst->configure_saslauthd();

				//** Konfiguriranje PAM
				swriteln('Konfiguriranje PAM');
				$inst->configure_pam();

				//* Konfiguriranje courier
				swriteln('Konfiguriranje Courier');
				$inst->configure_courier();
			}

		}

		//** Konfiguriranje mailman
		if($conf['mailman']['installed'] == true && $inst->reconfigure_app('Mailman', $reconfigure_services_answer)) {
			swriteln('Konfiguriranje Mailman');
			$inst->configure_mailman('update');
		}

		//** Konfiguriranje Spamasassin
		if($inst->reconfigure_app('Spamassassin', $reconfigure_services_answer)) {
			swriteln('Konfiguriranje Spamassassin');
			$inst->configure_spamassassin();
		}

		//** Konfiguriranje Amavis
		if($conf['amavis']['installed'] == true && $inst->reconfigure_app('Amavisd', $reconfigure_services_answer)) {
			swriteln('Konfiguriranje Amavisd');
			$inst->configure_amavis();
		}

		//** Konfiguriranje Rspamd
		if($conf['rspamd']['installed'] == true && $inst->reconfigure_app('Rspamd', $reconfigure_services_answer)) {
			swriteln('Konfiguriranje Rspamd');
			$inst->configure_rspamd();
		}

		//** Konfiguriranje Getmail
		if ($inst->reconfigure_app('Getmail', $reconfigure_services_answer)) {
			swriteln('Konfiguriranje Getmail');
			$inst->configure_getmail();
		}
	}

	if($conf['services']['dns'] && $inst->reconfigure_app('DNS', $reconfigure_services_answer)) {
		//* Konfiguriranje DNS
		if($conf['powerdns']['installed'] == true) {
			swriteln('Konfiguriranje PowerDNS');
			$inst->configure_powerdns();
		} elseif($conf['bind']['installed'] == true) {
			swriteln('Konfiguriranje BIND');
			$inst->configure_bind();
			if(!is_installed('haveged')) {
				swriteln("[INFO] haveged ni zaznan - DNSSEC lahko odpove");
			}
		} else {
			swriteln('Konfiguriranje MyDNS');
			$inst->configure_mydns();
		}
	}

	if($conf['services']['web'] || $inst->install_rmnetdov_interface) {

		if($conf['pureftpd']['installed'] == true && $inst->reconfigure_app('Pureftpd', $reconfigure_services_answer)) {
			//** Configure Pureftpd
			swriteln('Konfiguriranje Pureftpd');
			$inst->configure_pureftpd();
		}

		if($inst->reconfigure_app('Web-Server', $reconfigure_services_answer)) {
			if($conf['webserver']['server_type'] == 'apache'){
				//** Konfiguriranje Apache
				swriteln('Konfiguriranje Apache');
				$inst->configure_apache();

				//** Konfiguriranje vlogger
				swriteln('Konfiguriranje vlogger');
				$inst->configure_vlogger();
			} else {
				//** Konfiguriranje nginx
				swriteln('Konfiguriranje nginx');
				$inst->configure_nginx();
			}

			if ($conf['server_config']['web']['apps_vhost_enabled'] == 'y') {
				//** Konfiguriranje apps vhost
				swriteln('Konfiguriranje Apps vhost');
				$inst->configure_apps_vhost();
			} else swriteln('Preskok konfiguracije Apps vhost');
		}

		//* Konfiguriranje Jailkit
		if($inst->reconfigure_app('Jailkit', $reconfigure_services_answer)) {
			swriteln('Konfiguriranje Jailkit');
			$inst->configure_jailkit();
		}

	}

	if($conf['services']['xmpp'] && $inst->reconfigure_app('XMPP', $reconfigure_services_answer)) {
		//** Konfiguriranje Metronome XMPP
	$inst->configure_xmpp('dont-create-certs');
	}

  // Konfiguriranje AppArmor
  if($conf['apparmor']['installed']){
    swriteln('Konfiguriranje AppArmor');
    $inst->configure_apparmor();
  }

	if($conf['services']['firewall'] && $inst->reconfigure_app('Firewall', $reconfigure_services_answer)) {
		if($conf['ufw']['installed'] == true) {
			//* Konfiguriranje Ubuntu Firewall
			$conf['services']['firewall'] = true;
			swriteln('Konfiguriranje Ubuntu Firewall');
			$inst->configure_ufw_firewall();
		} else {
			//* Konfiguriranje Bastille Firewall
			swriteln('Konfiguriranje Bastille Firewall');
			$inst->configure_bastille_firewall();
		}
	}

	//* Konfiguriranje DBServer
	swriteln('Konfiguriranje Database');
	$inst->configure_dbserver();

	/*
	if($conf['squid']['installed'] == true) {
		swriteln('Konfiguriranje Squid');
		$inst->configure_squid();
	} else if($conf['nginx']['installed'] == true) {
		swriteln('Konfiguriranje Nginx');
		$inst->configure_nginx();
	}
	*/
}

//** Konfiguriranje RM-Net - DOV CP
swriteln('Posodobitev RM-Net - DOV CP');

$issue_asked = false;
$issue_tried = false;
// ustvarite acme vhost
if($conf['nginx']['installed'] == true) {
	$inst->make_acme_vhost('nginx'); // potrebujemo to konfiguracijsko datoteko, vendar ne želimo, da se nginx na tej točki znova zažene
}
if($conf['apache']['installed'] == true) {
	$inst->make_acme_vhost('apache'); //potrebujemo to konfiguracijsko datoteko, vendar ne želimo, da se apache na tej točki znova zažene
}

if ($inst->install_rmnetdov_interface) {
	//** Prilagodite vrata RM-Net - DOV CP deluje
	$rmnetdov_port_number = get_rmnetdov_port_number();
	if($autoupdate['rmnetdov_port'] == 'default') $autoupdate['rmnetdov_port'] = $rmnetdov_port_number;
	if($conf['webserver']['server_type'] == 'nginx'){
		$conf['nginx']['vhost_port'] = $inst->free_query('RM-Net - DOV CP Port', $rmnetdov_port_number,'rmnetdov_port');
	} else {
		$conf['apache']['vhost_port'] = $inst->free_query('RM-Net - DOV CP Port', $rmnetdov_port_number,'rmnetdov_port');
	}


	// $rmnetdov_ssl_default = (is_rmnetdov_ssl_enabled() == true)?'y':'n';
	if(strtolower($inst->simple_query('Ustvari novo RM-Net - DOV CP SSL potrdilo', array('yes', 'no'), 'no','create_new_rmnetdov_ssl_cert')) == 'yes') {
		$inst->make_rmnetdov_ssl_cert();
		$issue_tried = true;
	}
	$issue_asked = true;
}

// Želite ustvariti potrdila SSL za ne-spletne strežnike?
if(!$issue_asked) {
    if(!file_exists('/usr/local/rmnetdov/interface/ssl/rmnetdovserver.crt')) {
        if(!$issue_tried && strtolower($inst->simple_query('Ali želite ustvariti potrdila SSL za vaš strežnik?', array('y', 'n'), 'y','create_ssl_server_certs')) == 'y') {
            $inst->make_rmnetdov_ssl_cert();
	    }
    } else {
        swriteln('Certifikat obstaja. Ne ustvarjanje novega.');
    }
}

// posodobite acme.sh, če je nameščen
$inst->update_acme();

$inst->install_rmnetdov();

// Pospravi
$inst->cleanup_rmnetdov();

//** Konfigurirajte Crontab
$update_crontab_answer = $inst->simple_query('Znova konfigurirati Crontab?', array('yes', 'no'), 'yes','reconfigure_crontab');
if($update_crontab_answer == 'yes') {
	swriteln('Posodabljanje Crontaba');
	$inst->install_crontab();
}

//** Znova zaženite storitve:
if($reconfigure_services_answer == 'yes') {
	swriteln('Ponovni zagon storitev ...');
	if($conf['mysql']['installed'] == true && $conf['mysql']['init_script'] != '') system($inst->getinitcommand($conf['mysql']['init_script'], 'restart').' >/dev/null 2>&1');
	if($conf['services']['mail']) {
		if($conf['postfix']['installed'] == true && $conf['postfix']['init_script'] != '') system($inst->getinitcommand($conf['postfix']['init_script'], 'restart'));
		if($conf['saslauthd']['installed'] == true && $conf['saslauthd']['init_script'] != '') system($inst->getinitcommand($conf['saslauthd']['init_script'], 'restart'));
		if($conf['amavis']['installed'] == true && $conf['amavis']['init_script'] != '') system($inst->getinitcommand($conf['amavis']['init_script'], 'restart'));
		if($conf['rspamd']['installed'] == true && $conf['rspamd']['init_script'] != '') system($inst->getinitcommand($conf['rspamd']['init_script'], 'restart'));
		if($conf['clamav']['installed'] == true && $conf['clamav']['init_script'] != '' && $conf['amavis']['installed'] == true) system($inst->getinitcommand($conf['clamav']['init_script'], 'restart'));
		if($conf['courier']['installed'] == true){
			if($conf['courier']['courier-authdaemon'] != '') system($inst->getinitcommand($conf['courier']['courier-authdaemon'], 'restart'));
			if($conf['courier']['courier-imap'] != '') system($inst->getinitcommand($conf['courier']['courier-imap'], 'restart'));
			if($conf['courier']['courier-imap-ssl'] != '') system($inst->getinitcommand($conf['courier']['courier-imap-ssl'], 'restart'));
			if($conf['courier']['courier-pop'] != '') system($inst->getinitcommand($conf['courier']['courier-pop'], 'restart'));
			if($conf['courier']['courier-pop-ssl'] != '') system($inst->getinitcommand($conf['courier']['courier-pop-ssl'], 'restart'));
		}
		if($conf['dovecot']['installed'] == true && $conf['dovecot']['init_script'] != '') system($inst->getinitcommand($conf['dovecot']['init_script'], 'restart'));
		if($conf['mailman']['installed'] == true && $conf['mailman']['init_script'] != '') system('nohup '.$inst->getinitcommand($conf['mailman']['init_script'], 'restart').' >/dev/null 2>&1 &');
	}
	if($conf['services']['web'] || $inst->install_rmnetdov_interface) {
		if($conf['webserver']['server_type'] == 'apache') {
			// Če je uporabnik konfiguriral zagonski skript Apache po meri, ga uporabite. V nasprotnem primeru uporabite privzeti samodejno zaznani zagonski skript
			if(!empty($conf['server_config']['web']['apache_init_script'])) {
				system($inst->getinitcommand($conf['server_config']['web']['apache_init_script'], 'restart'));
			} elseif(!empty($conf['apache']['init_script'])) {
				system($inst->getinitcommand($conf['apache']['init_script'], 'restart'));
			}
		}
		//* Ponovno nalaganje je dovolj za nginx
		if($conf['webserver']['server_type'] == 'nginx'){
			if($conf['nginx']['php_fpm_init_script'] != '') system($inst->getinitcommand($conf['nginx']['php_fpm_init_script'], 'reload'));
			if($conf['nginx']['init_script'] != '') system($inst->getinitcommand($conf['nginx']['init_script'], 'reload'));
		}
		if($conf['pureftpd']['installed'] == true && $conf['pureftpd']['init_script'] != '') system($inst->getinitcommand($conf['pureftpd']['init_script'], 'restart'));
	}
	if($conf['services']['dns']) {
		if($conf['mydns']['installed'] == true && $conf['mydns']['init_script'] != '') system($inst->getinitcommand($conf['mydns']['init_script'], 'restart').' &> /dev/null');
		if($conf['powerdns']['installed'] == true && $conf['powerdns']['init_script'] != '') system($inst->getinitcommand($conf['powerdns']['init_script'], 'restart').' &> /dev/null');
		if($conf['bind']['installed'] == true && $conf['bind']['init_script'] != '') system($inst->getinitcommand($conf['bind']['init_script'], 'restart').' &> /dev/null');
	}

    if($conf['services']['xmpp']) {
        if($conf['xmpp']['installed'] == true && $conf['xmpp']['init_script'] != '') system($inst->getinitcommand($conf['xmpp']['init_script'], 'restart').' &> /dev/null');
    }

	if($conf['services']['proxy']) {
		// if($conf['squid']['installed'] == true && $conf['squid']['init_script'] != '' && is_executable($conf['init_scripts'].'/'.$conf['squid']['init_script']))     system($conf['init_scripts'].'/'.$conf['squid']['init_script'].' restart &> /dev/null');
		if($conf['nginx']['installed'] == true && $conf['nginx']['init_script'] != '') system($inst->getinitcommand($conf['nginx']['init_script'], 'restart').' &> /dev/null');
	}

	if($conf['services']['firewall']) {
		if($conf['ufw']['installed'] == true && $conf['ufw']['init_script'] != '' && is_executable($conf['init_scripts'].'/'.$conf['ufw']['init_script']))     system($conf['init_scripts'].'/'.$conf['ufw']['init_script'].' restart &> /dev/null');
	}
}

//* Nastavite privzete strežnike
setDefaultServers();

$inst->create_mount_script();

//* Ustvari seznam datotek md5
$md5_filename = '/usr/local/rmnetdov/security/data/file_checksums_'.date('Y-m-d_h-i').'.md5';
exec('find /usr/local/rmnetdov -type f -print0 | xargs -0 md5sum > '.$md5_filename . ' 2>/dev/null');
chmod($md5_filename,0700);

// OPRAVILO: Pri prihodnji posodobitvi ustavite skript posodobitve, ko izvajate kurir
if ($conf['courier']['installed'] == true) {
	swriteln('OPOZORILO: Uporabljate Courier. Odstranjujemo podporo za Courier iz RM-Net - DOV CP. Čim prej preselite svoj sistem na Dovecot. Glej https://www.howtoforge.com/community/threads/migrate-from-courier-to-dovecot-on-your-rmnetdov-managed-mailserver.88523/ za več informacij.');
}

echo "Posodobitev končana.\n";

?>
