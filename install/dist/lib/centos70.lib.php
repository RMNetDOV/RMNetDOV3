<?php

require_once realpath(dirname(__FILE__)) . '/centos_base.lib.php';

class installer extends installer_centos {

	protected $clamav_socket = '/var/run/clamav/clamd.sock';
	
	// everything else is inherited from installer_centos class
}

?>
