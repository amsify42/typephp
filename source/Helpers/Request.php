<?php

namespace TypePHP\Helpers;

class Request
{
	private $headers 	= [];
	private $data 		= [];
	
	function __construct($onlyHeaders = false, $loadData = false)
	{
		if($onlyHeaders) {
			$this->headers 	= $this->getRequestHeaders();
		}
		if($loadData || !$onlyHeaders) {
			$this->_loadBodyData();
		}
	}

	public function _loadCLIData()
	{
		if($this->isCLI()) {
			if(sizeof($_SERVER['argv'])> 0) {
				foreach($_SERVER['argv'] as $aKey => $argv) {
					if($aKey == 1) {
						$this->data['action'] = $argv;
					} else if($aKey == 2) {
						$this->data['type'] = $argv;
					} else if($aKey == 3) {
						$this->data['name'] = $argv;
					} else {
						$vArray = explode('=', $argv);
						if(sizeof($vArray)> 1) {
							$this->data[$vArray[0]] = $vArray[1];
						}
					}
				}
			}
		}
	}

	public function _loadBodyData()
	{
		$this->data = json_decode(file_get_contents("php://input"), true);
	}

	private function getRequestHeaders()
	{
	    $headers = array();
	    foreach($_SERVER as $key => $value) {
	        if(substr($key, 0, 5) <> 'HTTP_') continue;
	        $header 			= str_replace(' ', '-', ucwords(str_replace('_', ' ', strtolower(substr($key, 5)))));
	        $headers[$header] 	= $value;
	    }
	    return $headers;
	}

	public function headers()
	{
		return $this->headers;
	}

	public function header($key)
	{
		return isset($this->headers[$key])? $this->headers[$key]: null;
	}

	public function isCLI()
	{
		return (PHP_SAPI === 'cli');
	}

	public function isGet()
	{
		return ($_SERVER['REQUEST_METHOD'] === 'GET');
	}

	public function isPost()
	{
		return ($_SERVER['REQUEST_METHOD'] === 'POST');
	}

	public function isPut()
	{
		return ($_SERVER['REQUEST_METHOD'] === 'PUT');
	}

	public function isDelete()
	{
		return ($_SERVER['REQUEST_METHOD'] === 'DELETE');
	}

	public function isRequest($type)
	{
		if(is_array($type)) {
			return (in_array($_SERVER['REQUEST_METHOD'], $type));
		} else {
			return ($_SERVER['REQUEST_METHOD'] === $type);
		}
	}

	public function get($key)
	{
		if(!empty($this->data)) {
			return isset($this->data[$key])? $this->data[$key]: null;
		} else if($this->isPost()) {
			return isset($_POST[$key])? $_POST[$key]: null;
		} else {
			return isset($_GET[$key])? $_GET[$key]: null;
		}
	}

	public function all()
	{
		if(!empty($this->data)) {
			return $this->data;
		} else if($this->isPost()) {
			return $_POST;
		} else {
			return $_GET;
		}
	}	
}