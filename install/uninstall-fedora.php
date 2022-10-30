<?php

/*
	RM-Net - DOV CP 3 odstranjevalec za fedora core.
*/

error_reporting(E_ALL|E_STRICT);

require "/usr/local/rmnetdov/server/lib/config.inc.php";
require "/usr/local/rmnetdov/server/lib/app.inc.php";
require "/usr/local/rmnetdov/server/mysql_clientdb.conf";

//** Pasica v ukazni vrstici
echo "\n\n".str_repeat('-', 80)."\n";
echo " ____  __  __       _   _      _             ____   _____     __   ____ ____
      |  _ \|  \/  |     | \ | | ___| |_          |  _ \ / _ \ \   / /  / ___|  _ \
      | |_) | |\/| |_____|  \| |/ _ \ __|  _____  | | | | | | \ \ / /  | |   | |_) |
      |  _ <| |  | |_____| |\  |  __/ |_  |_____| | |_| | |_| |\ V /   | |___|  __/
      |_| \_\_|  |_|     |_| \_|\___|\__|         |____/ \___/  \_/     \____|_|

";
echo "\n".str_repeat('-', 80)."\n";
echo "\n\n>> Odstrani  \n\n";

echo "Ali ste prepričani, da želite odstraniti RM-Net - DOV CP? [no]";
$input = fgets(STDIN);
$do_uninstall = rtrim($input);


if($do_uninstall == 'yes') {

	echo "\n\n>> Odstranitev RM-Net - DOV CP 3... \n\n";

	
	// Izbrišite bazo podatkov RM-Net - DOV CP
	//exec("/etc/init.d/mysqld stop");
	//exec("rm -rf /var/lib/mysql/".$conf["db_database"]);
	//exec("/etc/init.d/mysqld start");
	$link = mysqli_connect($clientdb_host, $clientdb_user, $clientdb_password);
	if (!$link) {
		echo "Ni mogoče vzpostaviti povezave z bazo podatkov'.mysql_error($link)";
	} else {
		$result=mysqli_query($link,"SPUSTI BAZO PODATKOV ".$conf['db_database']."';");
		if (!$result) echo "Ni mogoče odstraniti rmnetdov-database ".$conf['db_database']." ".mysqli_error($link)."\n";
		$result=mysqli_query($link, "IZBRIŠITE UPORABNIKA '".$conf['db_user'] ."';");
		if (!$result) echo "Ni mogoče odstraniti rmnetdov-database-user ".$conf['db_user']." ".mysqli_error($link)."\n";
	}
	mysqli_close($link);

	// Brisanje simbolne povezave v /var/www
	// Apache
	@unlink("/etc/httpd/conf/sites-enabled/000-rmnetdov.vhost");
	@unlink("/etc/httpd/conf/sites-available/rmnetdov.vhost");
	@unlink("/etc/httpd/conf/sites-enabled/000-apps.vhost");
	@unlink("/etc/httpd/conf/sites-available/apps.vhost");

	// nginx
	@unlink("/etc/nginx/sites-enabled/000-rmnetdov.vhost");
	@unlink("/etc/nginx/sites-available/rmnetdov.vhost");
	@unlink("/etc/nginx/sites-enabled/000-apps.vhost");
	@unlink("/etc/nginx/sites-available/apps.vhost");

	// Izbrišite datoteke rmnetdov
	exec('rm -rf /usr/local/rmnetdov');

//	echo "Ne pozabite izbrisati uporabnika rmnetdov v tabeli mysql.user.\n\n";

	echo "Odstranitev je končana.\n";
} else {
	echo "\n\n>> Odstranitev preklicana. \n\n";
}

?>
