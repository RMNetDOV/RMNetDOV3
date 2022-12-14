<?php

// Constants describing instances
define('INSTANCE_PENDING', 0);
define('INSTANCE_INSTALL', 1);
define('INSTANCE_ERROR', 2);
define('INSTANCE_SUCCESS', 3);
define('INSTANCE_REMOVE', 4);

// Constants describing packages
define('PACKAGE_LOCKED', 1);
define('PACKAGE_ENABLED', 2);
define('PACKAGE_OUTDATED', 3);
define('PACKAGE_ERROR_NOMETA', 4);

class ApsBase
{
	protected $log_prefix = '';
	protected $fetch_url = '';
	protected $aps_version = '';
	protected $packages_dir = '';
	protected $temp_pkg_dir = '';
	protected $interface_pkg_dir = '';
	protected $interface_mode = false; // server mode by default

	/**
	 * Constructor
	 *
	 * @param $app the application instance (db handle + log method)
	 * @param $interface_mode act in interface (true) or server mode (false)
	 * @param $log_prefix a prefix to set before all log entries
	 */


	public function __construct($app, $log_prefix = 'APS: ', $interface_mode = false)
	{
		$this->log_prefix = $log_prefix;
		$this->interface_mode = $interface_mode;
		$this->fetch_url = 'apscatalog.com';
		$this->aps_version = '1.2';
		$this->packages_dir = RMNETDOV_ROOT_PATH.'/aps_packages';
		$this->interface_pkg_dir = RMNETDOV_ROOT_PATH.'/web/sites/aps_meta_packages';
	}



	/**
	 * Converts a given value to it's native representation in 1024 units
	 *
	 * @param $value the size to convert
	 * @return integer and string
	 */
	public function convertSize($value)
	{
		$unit = array('Bytes', 'KB', 'MB', 'GB', 'TB');
		return @round($value/pow(1024, ($i = floor(log($value, 1024)))), 2).' '.$unit[$i];
	}



	/**
	 * Determine a specific xpath from a given SimpleXMLElement handle. If the
	 * element is found, it's string representation is returned. If not,
	 * the return value will stay empty
	 *
	 * @param $xml_handle the SimpleXMLElement handle
	 * @param $query the XPath query
	 * @param $array define whether to return an array or a string
	 * @return $ret the return string
	 */
	protected function getXPathValue($xml_handle, $query, $array = false)
	{
		$ret = '';

		$xp_result = @($xml_handle->xpath($query)) ? $xml_handle->xpath($query) : false;
		if($xp_result !== false) $ret = (($array === false) ? (string)$xp_result[0] : $xp_result);

		return $ret;
	}

}

?>
