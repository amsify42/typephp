<?php

namespace TypePHP\Controller;

use TypePHP\Helpers\Config;
use TypePHP\Helpers\Request;
use TypePHP\Helpers\Response;
use TypePHP\Form\Validation;

class Controller
{
	protected $config, $request, $validation, $response;

	public function _setHelpers(Config $config, Request $request, Response $response, Validation $validation)
	{
		$this->config 		= $config;
		$this->request 		= $request;
		$this->response 	= $response;
		$this->validation 	= $validation;
	}
}