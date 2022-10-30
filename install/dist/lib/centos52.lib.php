<?php

require_once realpath(dirname(__FILE__)) . '/centos_base.lib.php';

class installer extends installer_centos {

	protected $clamav_socket = '/tmp/clamd.socket';

	// everything else is inherited from installer_centos class
}

?>
