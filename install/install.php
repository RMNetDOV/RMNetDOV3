<?php


/*
	RM-Net - DOV CP 3 namestitveni program.

	-------------------------------------------------------------------------------------
	- Interaktivna namestitev
	-------------------------------------------------------------------------------------
	run:

	php install.php

	-------------------------------------------------------------------------------------
	- Neinteraktivni način (samodejna namestitev).
	-------------------------------------------------------------------------------------

	Način samodejne namestitve lahko prebere vprašanja namestitvenega programa iz datoteke v slogu .ini ali iz
    konfiguracijsko datoteko php. Primeri za obe vrsti datotek so v mapi dokumenti.
	Glejte autoinstall.ini.sample in autoinstall.conf_sample.php.

	run:

	php install.php --autoinstall=autoinstall.ini

	or

	php install.php --autoinstall=autoinstall.conf.php

*/

error_reporting(E_ALL|E_STRICT);

define('INSTALLER_RUN', true);

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
echo "\n\n>> Začetna konfiguracija  \n\n";

//** Vključite knjižnico z osnovnimi funkcijami namestitvenega programa
require_once 'lib/install.lib.php';

//** Vključite osnovni razred razreda namestitvenega programa
require_once 'lib/installer_base.lib.php';

//** Prepričajte se, da je trenutni delovni imenik namestitveni imenik
$cur_dir = getcwd();
if(realpath(dirname(__FILE__)) != $cur_dir) {
	chdir( realpath(dirname(__FILE__)) );
}

//** Namesti dnevniško datoteko
define('RMNETDOV_LOG_FILE', '/var/log/rmnetdov_install.log');
define('RMNETDOV_INSTALL_ROOT', realpath(dirname(__FILE__).'/../'));

//** Vključite lib
require_once 'lib/classes/tpl.inc.php';

//** Preverite obstoječo namestitev
/*if(is_dir("/usr/local/rmnetdov")) {
    die('Tu se bomo ustavili. Namestitev RM-Net - DOV CP že obstaja, uporabite posodobitveni skript za posodobitev te namestitve.');
}*/

// Za popravilo najnovejših različic macosov je potreben popravek
if(is_installed('amavisd-new') && !is_installed('patch')) die('Manjka ukaz patch. Install patch ukaz in znova zaženite namestitev.');

//** Pridobite identifikator distribucije
$dist = get_distname();

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

//****************************************************************************************************
//** Installer Interface
//****************************************************************************************************
$inst = new installer();
$retval=shell_exec("which which");
if (empty($retval)) die ("RM-Net - DOV CP zahteva kateri \n");

$inst->check_prerequisites();

swriteln($inst->lng('    Sledi nekaj vprašanj za primarno konfiguracijo, zato bodite previdni.'));
swriteln($inst->lng('    Privzete vrednosti so v [oklepajih] in jih je mogoče sprejeti <ENTER>.'));
swriteln($inst->lng('    Tapnite "zapri" (brez narekovajev), da ustavite namestitveni program.'."\n\n"));

//** Preverite, ali je zapisljiva datoteka dnevnika (verjetno ni root ali sudo)
if(!is_writable(dirname(RMNETDOV_LOG_FILE))){
	die("NAPAKA: Ne morem pisati na ".dirname(RMNETDOV_LOG_FILE)." imenik. Ste root ali sudo ?\n\n");
}

//** Preverite različice RM-Net - DOV CP 2.x
if(is_dir('/root/rmnetdov') || is_dir('/home/admrmnetdov')) {
	if(is_dir('/home/admrmnetdov')) {
		die('Te programske opreme ni mogoče namestiti na strežnik, ki izvaja RM-Net - DOV CP 2.x.');
	} else {
		die('Te programske opreme ni mogoče namestiti na strežnik, ki izvaja RM-Net - DOV CP 2.x; prisotnost /root/rmnetdov/ imenik lahko nakazuje namestitev RM-Net - DOV CP 2.x, sicer ga lahko odstranite ali preimenujete za nadaljevanje.');
	}
}

if(is_dir('/usr/local/rmnetdov')) {
	die('Najdena namestitev RM-Net - DOV CP 3. Prosimo, uporabite update.php namesto tega, če install.php posodobite namestitev.');
}

//** Zaznajte nameščene aplikacije
$inst->find_installed_apps();

//* crontab, ki ga zahteva RM-Net - DOV CP
if(!$conf['cron']['installed']) {
	die("crontab ni najden; namestite združljiv demon cron pred RM-Net - DOV CP\n\n");
}

//** Izberite jezik in nastavite privzeti časovni pas
$conf['language'] = $inst->simple_query('Izberi jezik', array('sl', 'en', 'de'), 'sl','language');
$conf['timezone'] = get_system_timezone();

//* Nastavite privzeto temo
$conf['theme'] = 'default';
$conf['language_file_import_enabled'] = true;

//** Izberite način namestitve
$install_mode = $inst->simple_query('Način namestitve', array('standard', 'expert'), 'standard','install_mode');


//** Pridobite ime gostitelja
$tmp_out = array();
exec('hostname -f', $tmp_out);
$conf['hostname'] = @$tmp_out[0];
unset($tmp_out);
//** Prepreči prazno ime gostitelja
$check = false;
do {
	$conf['hostname'] = $inst->free_query('Polno kvalificirano ime gostitelja (FQDN) strežnika, npr server1.domain.tld ', $conf['hostname'], 'hostname');
	$conf['hostname']=trim($conf['hostname']);
	$check = @($conf['hostname'] !== '')?true:false;
	if(!$check) swriteln('Ime gostitelja ne sme biti prazno.');
} while (!$check);

// Check if the mysql functions are loaded in PHP
if(!function_exists('mysqli_connect')) die('Funkcije PHP MySQLi niso na voljo. Prepričajte se, da je modul PHP MySQL naložen.');

//** Pridobite poverilnice za koren MySQL
$finished = false;
do {
	$tmp_mysql_server_host = $inst->free_query('MySQL server hostname', $conf['mysql']['host'],'mysql_hostname');
	$tmp_mysql_server_port = $inst->free_query('MySQL server port', $conf['mysql']['port'],'mysql_port');
	$tmp_mysql_server_admin_user = $inst->free_query('MySQL root username', $conf['mysql']['admin_user'],'mysql_root_user');
	$tmp_mysql_server_admin_password = $inst->free_query('MySQL root password', $conf['mysql']['admin_password'],'mysql_root_password');
	$tmp_mysql_server_database = $inst->free_query('MySQL database to create', $conf['mysql']['database'],'mysql_database');
	$tmp_mysql_server_charset = $inst->free_query('MySQL charset', $conf['mysql']['charset'],'mysql_charset');

	if($install_mode == 'expert') {
		swriteln("Naslednji dve vprašanji se nanašata na uporabnika in geslo notranje baze podatkov RM-Net - DOV CP.\nPriporočljivo je, da kot uporabniško ime in naključno geslo sprejmete privzete nastavitve, ki so 'rmnetdov'.\nČe uporabljate drugo geslo, za geslo uporabite samo številke in znake.\n");

		$conf['mysql']['rmnetdov_user'] = $inst->free_query('RM-Net - uporabniško ime baze podatkov DOV CP mysql', $conf['mysql']['rmnetdov_user'],'mysql_rmnetdov_user');
		$conf['mysql']['rmnetdov_password'] = $inst->free_query('RM-Net - DOV CP geslo baze podatkov mysql', $conf['mysql']['rmnetdov_password'],'mysql_rmnetdov_password');
	}

	//* Initialize the MySQL server connection
	if(@mysqli_connect($tmp_mysql_server_host, $tmp_mysql_server_admin_user, $tmp_mysql_server_admin_password, '', (int)$tmp_mysql_server_port)) {
		$conf['mysql']['host'] = $tmp_mysql_server_host;
		$conf['mysql']['port'] = $tmp_mysql_server_port;
		$conf['mysql']['admin_user'] = $tmp_mysql_server_admin_user;
		$conf['mysql']['admin_password'] = $tmp_mysql_server_admin_password;
		$conf['mysql']['database'] = $tmp_mysql_server_database;
		$conf['mysql']['charset'] = $tmp_mysql_server_charset;
		$finished = true;
	} else {
		swriteln($inst->lng('Ni mogoče vzpostaviti povezave z navedenim strežnikom MySQL').' '.mysqli_connect_error());
	}
} while ($finished == false);
unset($finished);

// Razreši naslov IP imena gostitelja MySQL.
$tmp = explode(':', $conf['mysql']['host']);
if(!$conf['mysql']['ip'] = gethostbyname($tmp[0])) die('Ni mogoče razrešiti imena gostitelja'.$tmp[0]);
unset($tmp);


//** Inicializacija povezave z bazo podatkov
include_once 'lib/mysql.lib.php';
$inst->db = new db();

//** Začnite s standardno ali strokovno namestitvijo

$conf['services']['mail'] = false;
$conf['services']['web'] = false;
$conf['services']['dns'] = false;
$conf['services']['file'] = false;
$conf['services']['db'] = true;
$conf['services']['vserver'] = false;
$conf['services']['firewall'] = false;
$conf['services']['proxy'] = false;
$conf['services']['xmpp'] = false;

//** Pridobite ID strežnika
// $conf['server_id'] = $inst->free_query('Enolični številski ID strežnika','1');
// ID strežnika je zdaj vrednost autoInc baze podatkov mysql
if($install_mode == 'expert' && strtolower($inst->simple_query('Ali naj se ta strežnik pridruži obstoječi nastavitvi več strežnikov RM-Net - DOV CP', array('y', 'n'), 'n','join_multiserver_setup')) == 'y') {
	$conf['mysql']['master_slave_setup'] = 'y';

	//** Pridobite poverilnice za koren MySQL
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

	// Inicializirati povezavo z glavno bazo podatkov
	$inst->dbmaster = new db();
	if($inst->dbmaster->linkId) $inst->dbmaster->closeConn();
	$inst->dbmaster->setDBData($conf['mysql']["master_host"], $conf['mysql']["master_admin_user"], $conf['mysql']["master_admin_password"], $conf['mysql']['master_port']);
	$inst->dbmaster->setDBName($conf['mysql']["master_database"]);

} else {
	// Glavni DB je enak podrejenemu DB
	$inst->dbmaster = $inst->db;
}

//* Ustvarite bazo podatkov mysql
$inst->configure_database();

//* Preverite spletni strežnik
if(!$conf['apache']['installed'] && !$conf['nginx']['installed']) {
	$conf['apache']['installed'] = $inst->force_configure_app('Apache', ($install_mode == 'expert'));
	$conf['nginx']['installed'] = $inst->force_configure_app('nginx', ($install_mode == 'expert'));
}

//* Konfigurirajte spletni strežnik - Apache ali nginx
if($conf['apache']['installed'] == true && $conf['nginx']['installed'] == true) {
	$http_server_to_use = $inst->simple_query('Zaznana sta Apache in nginx. Izberite strežnik za uporabo za RM-Net - DOV CP:', array('apache', 'nginx'), 'apache','http_server');
	if($http_server_to_use == 'apache'){
		$conf['nginx']['installed'] = false;
		$conf['services']['file'] = true;
	} else {
		$conf['apache']['installed'] = false;
	}
}

//* V bazo podatkov vstavite zapis strežnika
if($install_mode == 'expert') {
	swriteln('Dodajanje zapisa strežnika RM-Net - DOV CP v bazo podatkov.');
	swriteln('');
}
$inst->add_database_server_record();

if($install_mode == 'standard' || strtolower($inst->simple_query('Konfigurirajte pošto', array('y', 'n') , 'y','configure_mail') ) == 'y') {

	//* Konfiguriranje Postgrey
	$force = @($conf['postgrey']['installed']) ? true : $inst->force_configure_app('Postgrey', ($install_mode == 'expert'));
	if($force) swriteln('Konfiguriranje Postgrey');

	//* Konfiguriranje Postfix
	$force = @($conf['postfix']['installed']) ? true : $inst->force_configure_app('Postfix', ($install_mode == 'expert'));
	if($force) {
		swriteln('Konfiguriranje Postfixa');
		$conf['services']['mail'] = true;
		$inst->configure_postfix();
	}

	if($conf['services']['mail']) {
		//* Konfiguriranje Mailman
		$force = @($conf['mailman']['installed']) ? true : $inst->force_configure_app('Mailman', ($install_mode == 'expert'));
		if($force) {
			swriteln('Konfiguriranje Mailmana');
			$inst->configure_mailman();
		}

		//* Preverite Dovecot in Courier
		if(!$conf['dovecot']['installed'] && !$conf['courier']['installed']) {
			$conf['dovecot']['installed'] = $inst->force_configure_app('Dovecot', ($install_mode == 'expert'));
			$conf['courier']['installed'] = $inst->force_configure_app('Courier', ($install_mode == 'expert'));
		}
		//* Configure Mailserver - Dovecot or Courier
		if($conf['dovecot']['installed'] && $conf['courier']['installed']) {
			$mail_server_to_use = $inst->simple_query('Zaznana Dovecot in Courier. Izberite strežnik za uporabo z RM-Net - DOV CP:', array('dovecot', 'courier'), 'dovecot','mail_server');
			if($mail_server_to_use == 'dovecot'){
				$conf['courier']['installed'] = false;
			} else {
				$conf['dovecot']['installed'] = false;
			}
		}
		//* Konfiguriranje Dovecot
		if($conf['dovecot']['installed']) {
			swriteln('Konfiguriranje Dovecota');
			$inst->configure_dovecot();
		}
		//* Konfiguriranje Courier
		if($conf['courier']['installed']) {
			swriteln('Konfiguriranje Courier');
			$inst->configure_courier();
			swriteln('Konfiguriranje SASL');
			$inst->configure_saslauthd();
			swriteln('Konfiguriranje PAM');
			$inst->configure_pam();
		}

		//* Konfiguriranje Spamasassin
		$force = @($conf['spamassassin']['installed']) ? true : $inst->force_configure_app('Spamassassin', ($install_mode == 'expert'));
		if($force) {
			swriteln('Konfiguriranje Spamassassin');
			$inst->configure_spamassassin();
		}

		//* Konfiguriranje Amavis
		$force = @($conf['amavis']['installed']) ? true : $inst->force_configure_app('Amavisd', ($install_mode == 'expert'));
		if($force) {
			swriteln('Konfiguriranje Amavisd');
			$inst->configure_amavis();
		}

		//* Konfiguriranje Rspamd
		$force = @($conf['rspamd']['installed']) ? true : $inst->force_configure_app('Rspamd', ($install_mode == 'expert'));
		if($force) {
			swriteln('Konfiguriranje Rspamd');
			$inst->configure_rspamd();
		}

//* Konfiguriranje Getmail
		$force = @($conf['getmail']['installed']) ? true : $inst->force_configure_app('Getmail', ($install_mode == 'expert'));
		if($force) {
			swriteln('Konfiguriranje Getmail');
			$inst->configure_getmail();
		}
	} else {
		swriteln('[NAPAKA] Postfix ni nameščen - preskakuje pošto');
	}
}

//* Konfiguriranje Jailkit
$force = @($conf['jailkit']['installed']) ? true : $inst->force_configure_app('Jailkit', ($install_mode == 'expert'));
if($force) {
	swriteln('Konfiguriranje Jailkit');
	$inst->configure_jailkit();
}

//* Konfiguriranje Pureftpd
$force = @($conf['pureftpd']['installed']) ? true : $inst->force_configure_app('pureftpd', ($install_mode == 'expert'));
if($force) {
	swriteln('Konfiguriranje Pureftpd');
	$inst->configure_pureftpd();
}

//** Konfiguriranje DNS
if($install_mode == 'standard' || strtolower($inst->simple_query('Konfiguriranje DNS Server', array('y', 'n'), 'y','configure_dns')) == 'y') {
	//* Check for DNS
	if(!$conf['powerdns']['installed'] && !$conf['bind']['installed'] && !$conf['mydns']['installed']) {
		if($install_mode == 'expert') $conf['powerdns']['installed'] = $inst->force_configure_app('PowerDNS', ($install_mode == 'expert'));
		$conf['bind']['installed'] = $inst->force_configure_app('BIND', ($install_mode == 'expert'));
		$conf['mydns']['installed'] = $inst->force_configure_app('MyDNS', ($install_mode == 'expert'));
	}
	//* Konfiguriranje PowerDNS
	if($install_mode == 'expert' && $conf['powerdns']['installed']) {
		swriteln('Konfiguriranje PowerDNS');
		$inst->configure_powerdns();
		$conf['services']['dns'] = true;
	}
	//* Konfiguriranje Bind
	if($conf['bind']['installed']) {
		swriteln('Konfiguriranje BIND');
		$inst->configure_bind();
		$conf['services']['dns'] = true;
		if(!is_installed('haveged')) {
			swriteln("[INFO] haveged ni zaznan - DNSSEC lahko odpove");
		}
	}
	//* Konfiguriranje MyDNS
	if($conf['mydns']['installed']) {
		swriteln('Konfiguriranje MyDNS');
		$inst->configure_mydns();
		$conf['services']['dns'] = true;
	}

}

if($install_mode == 'expert') swriteln('Možnost spletnega strežnika mora biti omogočena, ko želite zagnati spletni strežnik ali ko bo to vozlišče gostilo vmesnik RM-Net - DOV CP.');
if($install_mode == 'standard' || strtolower($inst->simple_query('Konfiguriranje Web Server', array('y', 'n'), 'y','configure_webserver')) == 'y') {
	//* Configure Apache
	if($conf['apache']['installed']){
		swriteln('Konfiguriranje Apache');
		$inst->configure_apache();
		$conf['services']['web'] = true;
		$conf['services']['file'] = true;
		//* Configure Vlogger
		$force = @($conf['vlogger']['installed']) ? true : $inst->force_configure_app('vlogger', ($install_mode == 'expert'));
		if($force) {
			swriteln('Konfiguriranje vlogger');
			$inst->configure_vlogger();
		}
		//* Konfiguriranje squid
/*
		$force = @($conf['squid']['installed']) ? true : $inst->force_configure_app('squid', ($install_mode == 'expert'));
		if($force) {
			swriteln('Konfiguriranje Squid');
			$inst->configure_squid();
			$conf['services']['proxy'] = true;
			if($conf['squid']['init_script'] != '' && is_executable($conf['init_scripts'].'/'.$conf['squid']['init_script']))system($conf['init_scripts'].'/'.$conf['squid']['init_script'].' restart &> /dev/null');
		}
*/
	}
	//* Konfiguriranje nginx
	if($conf['nginx']['installed']){
		swriteln('Konfiguriranje nginx');
		$inst->configure_nginx();
		$conf['services']['web'] = true;
	}
}

//* Konfiguriranje OpenVZ
$force = @($conf['openvz']['installed']) ? true : $inst->force_configure_app('OpenVZ', ($install_mode == 'expert'));
if($force) {
	$conf['services']['vserver'] = true;
	swriteln('Konfiguriranje OpenVZ');
}

// Konfiguriranje AppArmor
if($conf['apparmor']['installed']){
  swriteln('Konfiguriranje AppArmor');
  $inst->configure_apparmor();
}

if($install_mode == 'standard' || strtolower($inst->simple_query('Konfiguriranje Firewall Server', array('y', 'n'), 'y','configure_firewall')) == 'y') {
	//* Preverite za Firewall
	if(!$conf['ufw']['installed'] && !$conf['firewall']['installed']) {
		$conf['ufw']['installed'] = $inst->force_configure_app('Ubuntu Firewall', ($install_mode == 'expert'));
		$conf['firewall']['installed'] = $inst->force_configure_app('Bastille Firewall', ($install_mode == 'expert'));
	}
	//* Konfiguriranje Firewall - Ubuntu or Bastille
	if($conf['ufw']['installed'] && $conf['firewall']['installed']) {
		$firewall_to_use = $inst->simple_query('Zaznan Ubuntu and Bastille Firewall. Izberite firewall za uporabo z RM-Net - DOV CP:', array('bastille', 'ubuntu'), 'bastille','firewall_server');
		if($firewall_to_use == 'bastille'){
			$conf['ufw']['installed'] = false;
		} else {
			$conf['firewall']['installed'] = false;
		}
	}
	//* Konfiguriranje Ubuntu Firewall
	if($conf['ufw']['installed']){
		swriteln('Konfiguriranje Ubuntu Firewall');
		$inst->configure_ufw_firewall();
		$conf['services']['firewall'] = true;
	}
	//* Konfiguriranje Bastille Firewall
	if($conf['firewall']['installed']){
		swriteln('Konfiguriranje Bastille Firewall');
		$inst->configure_bastille_firewall();
		$conf['services']['firewall'] = true;
		$conf['bastille']['installed'] = true;
	}
}

//* Konfiguriranje XMPP
$force = @($conf['xmpp']['installed']) ? true : $inst->force_configure_app('Konfiguriranje XMPP Server', ($install_mode == 'expert'));
if($force) {
	swriteln('Konfiguriranje Metronome XMPP Server');
	$inst->configure_xmpp();
	$conf['services']['xmpp'] = true;
}

//* Konfiguriranje Fail2ban
$force = @($conf['fail2ban']['installed']) ? true : $inst->force_configure_app('Fail2ban', ($install_mode == 'expert'));
if($force) {
	swriteln('Konfiguriranje Fail2ban');
	$inst->configure_fail2ban();
}

// ustvarite acme vhost
if($conf['nginx']['installed'] == true) {
	$inst->make_acme_vhost('nginx'); // potrebujemo to konfiguracijsko datoteko, vendar ne želimo, da se nginx na tej točki znova zažene
}
if($conf['apache']['installed'] == true) {
	$inst->make_acme_vhost('apache'); // potrebujemo to konfiguracijsko datoteko, vendar ne želimo, da se apache na tej točki znova zažene
}

//** Konfiguriranje RM-Net - DOV CP :-)
$issue_asked = false;
$issue_tried = false;
$install_rmnetdov_interface_default = ($conf['mysql']['master_slave_setup'] == 'y')?'n':'y';
if($install_mode == 'standard' || strtolower($inst->simple_query('Namestite RM-Net - DOV CP Spletnega vmestnika', array('y', 'n'), $install_rmnetdov_interface_default,'install_rmnetdov_web_interface')) == 'y') {
	swriteln('Namestitev RM-Net - DOV CP');

	//** Prilagodite vrata RM-Net - DOV CP deluje
	$rmnetdov_vhost_port = $inst->free_query('RM-Net - DOV CP Port', '8090','rmnetdov_port');
	$temp_admin_password = str_shuffle(bin2hex(openssl_random_pseudo_bytes(4)));
	$conf['interface_password'] = $inst->free_query('Admin geslo', $temp_admin_password, 'rmnetdov_admin_password');
	if($conf['interface_password'] != $temp_admin_password) {
		$check = false;
		do {
			unset($temp_password);
			$temp_password = $inst->free_query('Znova vnesite skrbniško geslo', '','rmnetdov_admin_password');
			$check = @($temp_password == $conf['interface_password'])?true:false;
			if(!$check) swriteln('Geslo se ne ujema.');
		} while (!$check);
	}
	unset($check);
	unset($temp_password);
	unset($temp_admin_password);
	if($conf['apache']['installed'] == true) $conf['apache']['vhost_port']  = $rmnetdov_vhost_port;
	if($conf['nginx']['installed'] == true) $conf['nginx']['vhost_port']  = $rmnetdov_vhost_port;
	unset($rmnetdov_vhost_port);

	if(strtolower($inst->simple_query('Ali želite varno (SSL) povezavo s spletnim vmesnikom RM-Net - DOV CP', array('y', 'n'), 'y','rmnetdov_use_ssl')) == 'y') {
		$inst->make_rmnetdov_ssl_cert();
		$issue_tried = true;
	}
	$issue_asked = true;
	$inst->install_rmnetdov_interface = true;

} else {
	$inst->install_rmnetdov_interface = false;
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

if($conf['services']['web'] == true) {
	//** Configure apps vhost
	swriteln('Konfiguriranje Apps vhost');
	$inst->configure_apps_vhost();
}

$inst->install_rmnetdov();

//* Konfiguriranje DBServer
swriteln('Konfiguriranje DBServer');
$inst->configure_dbserver();

//* Konfiguriranje RM-Net - DOV CP
swriteln('Namestitev RM-Net - DOV CP crontab');
$inst->install_crontab();

swriteln('Zaznavanje naslovov IP');
$inst->detect_ips();

swriteln('Ponovni zagon storitev ...');
if($conf['mysql']['installed'] == true && $conf['mysql']['init_script'] != '') system($inst->getinitcommand($conf['mysql']['init_script'], 'restart').' >/dev/null 2>&1');
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
if($conf['apache']['installed'] == true && $conf['apache']['init_script'] != '') system($inst->getinitcommand($conf['apache']['init_script'], 'restart'));
//* Reload is enough for nginx
if($conf['nginx']['installed'] == true){
	if($conf['nginx']['php_fpm_init_script'] != '') system($inst->getinitcommand($conf['nginx']['php_fpm_init_script'], 'reload'));
	if($conf['nginx']['init_script'] != '') system($inst->getinitcommand($conf['nginx']['init_script'], 'reload'));
}
if($conf['pureftpd']['installed'] == true && $conf['pureftpd']['init_script'] != '') system($inst->getinitcommand($conf['pureftpd']['init_script'], 'restart'));
if($conf['mydns']['installed'] == true && $conf['mydns']['init_script'] != '') system($inst->getinitcommand($conf['mydns']['init_script'], 'restart').' &> /dev/null');
if($conf['powerdns']['installed'] == true && $conf['powerdns']['init_script'] != '') system($inst->getinitcommand($conf['powerdns']['init_script'], 'restart').' &> /dev/null');
if($conf['bind']['installed'] == true && $conf['bind']['init_script'] != '') system($inst->getinitcommand($conf['bind']['init_script'], 'restart').' &> /dev/null');
//if($conf['squid']['installed'] == true && $conf['squid']['init_script'] != '' && is_file($conf['init_scripts'].'/'.$conf['squid']['init_script']))     system($conf['init_scripts'].'/'.$conf['squid']['init_script'].' restart &> /dev/null');
if($conf['nginx']['installed'] == true && $conf['nginx']['init_script'] != '') system($inst->getinitcommand($conf['nginx']['init_script'], 'restart').' &> /dev/null');
if($conf['ufw']['installed'] == true && $conf['ufw']['init_script'] != '') system($inst->getinitcommand($conf['ufw']['init_script'], 'restart').' &> /dev/null');
if($conf['xmpp']['installed'] == true && $conf['xmpp']['init_script'] != '') system($inst->getinitcommand($conf['xmpp']['init_script'], 'restart').' &> /dev/null');


$inst->create_mount_script();

//* Create md5 filelist
$md5_filename = '/usr/local/rmnetdov/security/data/file_checksums_'.date('Y-m-d_h-i').'.md5';
exec('find /usr/local/rmnetdov -type f -print0 | xargs -0 md5sum > '.$md5_filename);
chmod($md5_filename,0700);


echo "Namestitev končana.\n";


?>
