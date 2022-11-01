<?php

/*
	RM-Net - DOV CP 3 Nastavite pravice. Poskuša popraviti pravice strank, če se motijo
*/

error_reporting(E_ALL|E_STRICT);

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
echo "\n\n>>Ta skript poskuša popraviti pravice odjemalca  \n\n";

//** Vključite knjižnico z osnovnimi funkcijami namestitvenega programa
require_once 'lib/install.lib.php';

//** Vključite knjižnico z osnovnimi funkcijami posodobitve
require_once 'lib/update.lib.php';

//** Vključite osnovni razred razreda namestitvenega programa
require_once 'lib/installer_base.lib.php';

//** Prepričajte se, da je trenutni delovni imenik namestitveni imenik
$cur_dir = getcwd();
if(realpath(dirname(__FILE__)) != $cur_dir) die("Please run installation/update from _inside_ the install directory!\n");

//** Get distribution identifier
$dist = get_distname();

include_once "/usr/local/rmnetdov/server/lib/config.inc.php";
$conf_old = $conf;
unset($conf);

if($dist['id'] == '') die('Distribucija ali različica Linuxa ni prepoznana.');

//** Vključite knjižnico in konfiguracijo namestitvenega programa, specifično za distribucijo
if(is_file('dist/lib/'.$dist['baseid'].'.lib.php')) include_once 'dist/lib/'.$dist['baseid'].'.lib.php';
include_once 'dist/lib/'.$dist['id'].'.lib.php';
include_once 'dist/conf/'.$dist['id'].'.conf.php';

//** Pridobite ime gostitelja
exec('hostname -f', $tmp_out);
$conf['hostname'] = $tmp_out[0];
unset($tmp_out);

//* Preverite, ali je to nastavitev glavni/podrejeni
$conf['mysql']['master_slave_setup'] = 'n';
if($conf["mysql"]["master_host"] != '' && $conf["mysql"]["host"] != $conf["mysql"]["master_host"]) {
	$conf['mysql']['master_slave_setup'] = 'y';
}

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

//** Prišlo je do napake, če je uporabnik za mysql admin_password, če je prazno
if( empty($conf["mysql"]["admin_password"]) ) {
	die("notranja napaka - MYSQL-Root geslo ni znano");
}

$inst = new installer();

//** Inicializirajte povezavo s strežnikom MySQL
include_once 'lib/mysql.lib.php';

//* initialize the database
$inst->db = new db();

/*
 * Naslednja vrstica je nekoliko zapletena!
 * Pri samodejni posodobitvi nimamo povezave z master-db (ne potrebujemo je, ker
 * tam sta samo DVE točki, kjer je to potrebno)
 * 1) posodobi pravice --> program za samodejno posodabljanje nastavi pravice vseh odjemalcev, ko je strežnik
 *    samodejno posodobljeno)
 * 2) posodobite nastavitve strežnika (ali je nameščen splet, ali je nameščena pošta) --> samodejne posodobitve
 *    ne spremeni nobene od teh nastavitev, zato tega ni treba posodobiti.
 * To pomeni, da program za samodejno posodabljanje ni potreboval nobene povezave z master-db (samo z lokalnim bd
 * glavnega strežnika). Da bi se izognili težavam, smo master-db nastavili na lokalno.
 */
$inst->dbmaster = $inst->db;

/*
 * Če NI master-slave - Setup, potem smo pri Master-DB. Torej nastavite vse pravice
*/
if($conf['mysql']['master_slave_setup'] != 'y') {
	$inst->grant_master_database_rights(true);
}

echo "Dokončano.\n";

?>
