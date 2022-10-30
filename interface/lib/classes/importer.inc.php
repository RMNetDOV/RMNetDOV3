<?php

require_once realpath(dirname(__FILE__)) . '/remoting.inc.php';

class fakeserver {
	private $faultMessage;
	private $faultText;
	public function fault($message = '', $text = '') {
		$this->faultMessage = $message;
		$this->faultText = $text;
	}

	public function getFault() {
		$ret = $this->faultMessage . ' (' . $this->faultText . ')';
		$this->faultMessage = null;
		$this->faultText = null;
		return $ret;
	}

}

class importer extends remoting {
	public function __construct()
	{
		$this->server = new fakeserver();
	}

	//* remote login function - overridden just to make sure it cannot be called from importer scripts
	public function login($username, $password)
	{

	}

	//* remote logout function - overridden just to make sure it cannot be called from importer scripts
	public function logout($session_id)
	{

	}

	public function getFault() {
		return $this->server->getFault();
	}

	protected function checkPerm($session_id, $function_name)
	{
		// always return true as this is used from inside the application not through remote calls
		return true;
	}


	protected function getSession($session_id)
	{
		return array(); // we have no sessions here
	}

}

?>
