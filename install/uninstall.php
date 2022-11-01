<?php

/*
	RM-Net - DOV CP 3 odstranje.
*/

error_reporting(E_ALL|E_STRICT);

require_once "/usr/local/rmnetdov/server/lib/config.inc.php";
require_once "/usr/local/rmnetdov/server/lib/app.inc.php";
require "/usr/local/rmnetdov/server/lib/mysql_clientdb.conf";

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
echo "\n\n>> Odstrani  \n\n";

echo "Ali ste prepričani, da želite odstraniti RM-Net - DOV CP? [no]";
$input = fgets(STDIN);
$do_uninstall = rtrim($input);


if($do_uninstall == 'yes') {

	echo "\n\n>> Odstranjevanje RM-Net - DOV CP 3 ... \n\n";

	$link = mysqli_connect($clientdb_host, $clientdb_user, $clientdb_password);
	if (!$link) {
		echo "Ni mogoče vzpostaviti povezave z bazo podatkov. mysql_error($link)";
	} else {
		$result=mysqli_query($link,"SPUSTI BAZO PODATKOV ".$conf['db_database'].";");
		if (!$result) echo "Ni mogoče odstraniti rmnetdov-database ".$conf['db_database']." ".mysqli_error($link)."\n";
		$result=mysqli_query($link,"IZBRIŠITE UPORABNIKA '".$conf['db_user']."'@'".$conf['db_host']."';");
	        if (!$result) echo "Ni mogoče odstraniti rmnetdov-database-user ".$conf['db_user']." ".mysqli_error($link)."\n";
	}
	mysqli_close($link);

	// Brisanje simbolne povezave v /var/www
	// Apache
	@unlink("/etc/apache2/sites-enabled/000-rmnetdov.vhost");
	@unlink("/etc/apache2/sites-available/rmnetdov.vhost");
	@unlink("/etc/apache2/sites-enabled/000-apps.vhost");
	@unlink("/etc/apache2/sites-available/apps.vhost");

	// nginx
	@unlink("/etc/nginx/sites-enabled/000-rmnetdov.vhost");
	@unlink("/etc/nginx/sites-available/rmnetdov.vhost");
	@unlink("/etc/nginx/sites-enabled/000-apps.vhost");
	@unlink("/etc/nginx/sites-available/apps.vhost");

	// Izbrišite datoteke rmnetdov
	exec('rm -rf /usr/local/rmnetdov');

	// Izbrišite različne druge datoteke
	@unlink("/usr/local/bin/letsencrypt_post_hook.sh");
	@unlink("/usr/local/bin/letsencrypt_pre_hook.sh");
	@unlink("/usr/local/bin/letsencrypt_renew_hook.sh");
	@unlink("/usr/local/bin/rmnetdov_update.sh");
	@unlink("/usr/local/bin/rmnetdov_update_from_svn.sh");
	@unlink("/var/spool/mail/rmnetdov");
	@unlink("/var/www/rmnetdov");
	@exec('chattr -i /var/www/php-fcgi-scripts/rmnetdov/.php-fcgi-starter');
	@unlink("/var/www/php-fcgi-scripts/rmnetdov/.php-fcgi-starter");
	@unlink("/var/www/php-fcgi-scripts/rmnetdov");

	echo "Varnostne kopije v /var/backup/ in dnevniške datoteke v /var/log/rmnetdov se ne izbrišejo.";
	echo "Odstranitev je končana.\n";

} else {
	echo "\n\n>> Odstranitev preklicana. \n\n";
}

?>
