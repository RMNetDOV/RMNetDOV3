<?php

class RMNetDOVJSONHandler extends RMNetDOVRemotingHandlerBase {
	private function _return_json($code, $message, $data = false) {
		$ret = new stdClass;
		$ret->code = $code;
		$ret->message = $message;
		$ret->response = $data;

		header('Content-Type: application/json; charset="utf-8"');
		print json_encode($ret);
		exit;
	}

	public function run() {

		if(!isset($_GET) || !is_array($_GET) || count($_GET) < 1) {
			$this->_return_json('invalid_method', 'Method not provided in json call');
		}
		$keys = array_keys($_GET);
		$method = reset($keys);
		$params = array();
		
		$raw = file_get_contents("php://input");
		$json = json_decode($raw, true);
		if(!is_array($json)) $this->_return_json('invalid_data', 'The JSON data sent to the api is invalid');
		
		if(array_key_exists($method, $this->methods) == false) {
			$this->_return_json('invalid_method', 'Method ' . $method . ' does not exist');
		}

		$class_name = $this->methods[$method];
		if(array_key_exists($class_name, $this->classes) == false) {
			$this->_return_json('invalid_class', 'Class ' . $class_name . ' does not exist');
		}

		if(method_exists($this->classes[$class_name], $method) == false) {
			$this->_return_json('invalid_method', 'Method ' . $method . ' does not exist in the class it was expected (' . $class_name . ')');
		}
		
		$methObj = new ReflectionMethod($this->classes[$class_name], $method);
		foreach($methObj->getParameters() as $param) {
			$pname = $param->name;
			if(isset($json[$pname])) $params[] = $json[$pname];
			else $params[] = null;
		}
		
		try {
			$this->_return_json('ok', '', call_user_func_array(array($this->classes[$class_name], $method), $params));
		} catch(SoapFault $e) {
			$this->_return_json('remote_fault', $e->getMessage());
		}
	}

}

?>
