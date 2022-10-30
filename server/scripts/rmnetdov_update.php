<?php

function sread() {
	$input = fgets(STDIN);
	return rtrim($input);
}

function swrite($text = '') {
	echo $text;
}

function swriteln($text = '') {
	echo $text."\n";
}

function simple_query($query, $answers, $default)
{
	$finished = false;
	do {
		$answers_str = implode(',', $answers);
		swrite($query.' ('.$answers_str.') ['.$default.']: ');
		$input = sread();

		//* Stop the installation
		if($input == 'quit') {
			swriteln("Installation terminated by user.\n");
			die();
		}

		//* Select the default
		if($input == '') {
			$answer = $default;
			$finished = true;
		}

		//* Set answer id valid
		if(in_array($input, $answers)) {
			$answer = $input;
			$finished = true;
		}

	} while ($finished == false);
	swriteln();
	return $answer;
}

require_once '/usr/local/rmnetdov/server/lib/config.inc.php';


echo "\n\n".str_repeat('-', 80)."\n";
echo " ____  __  __       _   _      _             ____   _____     __   ____ ____
      |  _ \|  \/  |     | \ | | ___| |_          |  _ \ / _ \ \   / /  / ___|  _ \
      | |_) | |\/| |_____|  \| |/ _ \ __|  _____  | | | | | | \ \ / /  | |   | |_) |
      |  _ <| |  | |_____| |\  |  __/ |_  |_____| | |_| | |_| |\ V /   | |___|  __/
      |_| \_\_|  |_|     |_| \_|\___|\__|         |____/ \___/  \_/     \____|_|

"
echo "\n".str_repeat('-', 80)."\n";
echo "\n\n>> Nadgradnja  \n\n";
echo "Izberite način posodobitve. Za proizvodne sisteme izberite 'stable'. \nOPOZORILO: Posodobitev iz GIT je samo za razvojne sisteme in lahko pokvari vašo trenutno nastavitev. Ne uporabljajte različice GIT na strežnikih, ki gostijo kakršna koli spletna mesta v živo!\npomba: V večstrežniških sistemih omogočite način vzdrževanja in najprej posodobite glavni strežnik. Nato posodobite vse podrejene strežnike in onemogočite način vzdrževanja, ko so vsi strežniki posodobljeni.\n\n";

$method = simple_query('Izberite način posodobitve', array('stable', 'nightly', 'git-develop'), 'stable');

if($method == 'stable') {
	$new_version = @file_get_contents('https://github.com/orgs/RM-Net-DOV-Control-Panel/dashboard/rmnetdov3_version.txt') or die('Ni mogoče pridobiti datoteke različice.');
	$new_version = trim($new_version);
	if(version_compare($new_version, RMNETDOV_APP_VERSION, '<=') && !in_array('--force', $argv, true)) {
		echo "Za RM-Net - DOV CP ni na voljo nobenih posodobitev ".RMNETDOV_APP_VERSION."\n";
		echo "Če ste prepričani, da želite vseeno posodobiti na stabilno, uporabite --force parameter\n";
		echo "PREHOD NA STRANJŠO RAZGRADNJO LAHKO POVZROČI TEŽAVE!\n";
		exit(1);
	}
}

passthru('/usr/local/rmnetdov/server/scripts/update_runner.sh ' . escapeshellarg($method));
exit;
