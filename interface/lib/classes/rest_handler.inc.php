<?php

class RMNetDOVRESTHandler extends RMNetDOVRemotingHandlerBase {
	private $api_version = 1;

	private function _return_error($code, $codename, $message) {
		header('HTTP/1.1 ' . $code . ' ' . $codename);
		print '<!DOCTYPE html>
		<html lang="sl">
		<head>
		<title>
		ERROR ' . $code . ': ' . $codename . '
		</title>
		</head>
		<body>
		<h1>' . $code . ': ' . $codename . '</h1>
		<p>' . htmlentities($message, ENT_QUOTES, 'UTF-8') . '</p>
		</body>
		</html>';
		exit;
	}

	private function _return_json($code, $data = '') {

		header('HTTP/1.1 ' . $code . ' OK');
		if(!is_array($data) && !is_object($data)) {
			header('Content-Type: text/plain; charset="utf-8"');
			print $data;
		} else {
			header('Content-Type: application/json; charset="utf-8"');
			print json_encode($data);
		}
		exit;
	}

	public function run() {
		// check called http method
		
		$method = '';
		$return_code = 0;
		$http_method = (isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : '');
		if($http_method == 'POST') {
			$method = 'add';
			$return_code = 201;
		} elseif($http_method == 'GET') {
			$method = 'get';
			$return_code = 200;
		} elseif($http_method == 'PUT') {
			$method = 'update';
			$return_code = 204;
		} elseif($http_method == 'DELETE') {
			$method = 'delete';
			$return_code = 204;
		} else {
			$this->_return_error(400, 'INVALID REQUEST', 'Invalid request');
		}
		
		$params = array();
		if($http_method == 'POST' || $http_method == 'PUT') { 
			$raw = file_get_contents("php://input");
			$json = json_decode($raw, true);
			if(!is_array($json)) $this->_return_error(400, 'INVALID REQUEST', 'The JSON data sent to the api is invalid');
		}
		
		// get URL
		$url_path = (isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '');
		if(!preg_match('^\/?remote\/api\/v(\d+)\/(\w+)(?:\/(\d+)|\/)?(?:\?.*)$/', $url_path, $parts)) {
			$this->_return_error(400, 'INVALID REQUEST', 'The url you called is not a valid REST url.');
		}
		$this->api_version = $parts[1];
		if($this->api_version != 1) {
			$this->_return_error(400, 'INVALID REQUEST', 'Invalid API version called.');
		}
		$section = $parts[2];
		$primary_id = (isset($parts[3]) ? $parts[3] : 0);
		$qry = (isset($_SERVER['QUERY_STRING']) ? $_SERVER['QUERY_STRING'] : '');
		$get = array();
		parse_str($qry, $get);
		
		$method = $section . '_' . $method;
		
		
		if(array_key_exists($method, $this->methods) == false) {
			$this->_return_error(400, 'INVALID REQUEST', 'Method ' . $method . ' does not exist');
		}

		$class_name = $this->methods[$method];
		if(array_key_exists($class_name, $this->classes) == false) {
			$this->_return_error(400, 'INVALID REQUEST', 'Class ' . $class_name . ' does not exist');
		}

		if(method_exists($this->classes[$class_name], $method) == false) {
			$this->_return_error(400, 'INVALID REQUEST', 'Method ' . $method . ' does not exist in the class it was expected (' . $class_name . ')');
		}
		
		$methObj = new ReflectionMethod($this->classes[$class_name], $method);
		foreach($methObj->getParameters() as $param) {
			$pname = $param->name;
			if($pname == 'session_id') $params[] = (isset($get['session_id']) ? $get['session_id'] : '');
			elseif($pname == 'primary_id' && $primary_id) $params[] = $primary_id;
			elseif($pname == 'params' && is_array($json)) $params[] = $json;
			elseif(isset($json[$pname])) $params[] = $json[$pname];
			else $params[] = null;
		}
		
		try {
			$this->_return_json($return_code, call_user_func_array(array($this->classes[$class_name], $method), $params));
		} catch(SoapFault $e) {
			$this->_return_error(500, 'REQUEST ERROR', $e->getMessage());
		}
	}

}

?>
