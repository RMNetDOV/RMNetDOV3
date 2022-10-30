<?php


class RMNetDOVSoapHandler extends RMNetDOVRemotingHandlerBase {
	public function __call($method, $params) {
		if(array_key_exists($method, $this->methods) == false) {
			throw new SoapFault('invalid_method', 'Method ' . $method . ' does not exist');
		}

		$class_name = $this->methods[$method];
		if(array_key_exists($class_name, $this->classes) == false) {
			throw new SoapFault('invalid_class', 'Class ' . $class_name . ' does not exist');
		}

		if(method_exists($this->classes[$class_name], $method) == false) {
			throw new SoapFault('invalid_method', 'Method ' . $method . ' does not exist in the class it was expected (' . $class_name . ')');
		}

		try {
			return call_user_func_array(array($this->classes[$class_name], $method), $params);
		} catch(SoapFault $e) {
			throw $e;
		}
	}

}

?>
