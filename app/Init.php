<?php

namespace App;

use ReflectionClass;
use TypePHP\Helpers\Config;
use TypePHP\Helpers\Request;
use TypePHP\Helpers\Response;
use TypePHP\Form\Validation;

class Init
{
	/**
	 * Route of the App
	 * @var string
	 */
	private $route 				= '';

	/**
	 * Controllers to escape from middleware
	 * @var array
	 */
	private $escapeControllers 	= [];

	/**
	 * Middleware names needs to be processed before hitting Controller
	 * @var Array
	 */
	private $middlewares 		= [];

	/**
	 * Helpers to instantiate in the beginning
	 * @var TypePHP\Helpers\Config;
	 * @var TypePHP\Helpers\Request;
	 * @var TypePHP\Helpers\Response;
	 */
	private $config, $request, $response;

	/**
	 * Default response code
	 * @var integer
	 */
	private $responseCode 		= 404;

	/**
	 * Default response message
	 * @var string
	 */
	private $responseMessage 	= 'Invalid route';
	
	public function __construct(Request $request)
	{
		// Define global APP_PATH
		define('APP_PATH', __DIR__);
		// Initiate request for headers only
		$this->request 	= $request;
		// Initiate Config
		$this->config 	= new Config();
		// Get Route URI
		$this->route 	= $this->getURI();
		// Initiate Response
		$this->response = new Response();
	}

	public function response()
	{
		if($this->request->isCLI()) {
			// Load CLI data
			$this->request->_loadCLIData();
			$console 	= new \TypePHP\Console\Base($this->request, $this->response);
			return $console->init();
		} else {
			include_once ROOT_PATH.DS.'config'.DS.'routes.php';
			$target = $router->isRegistered($this->request, $this->route);
			// If route is manually registered
			if($target && $target['is_type']) {
				if(is_callable($target['callback'])) {
					return call_user_func_array($target['callback'], $target['params']);
				} else {
					return $this->validate($target['class'], $target['action'], $target['params']);
				}
			} else if(!$target) {
				$target = $router->autoRouteInfo($this->route);
				if($target) {
					return $this->validate($target['class'], $target['action'], $target['params']);
				}
			}
			return $this->doResponse();
		}
	}

	private function getURI()
	{
		if(!$this->request->isCLI()) {
			$url 		= strtok($_SERVER['REQUEST_URI'],'?');
			$pathArray 	= explode($_SERVER['SERVER_NAME'], $url);
			return isset($pathArray[1])? trim($pathArray[1], '/') : '';
		}
	}

	private function validate($class, $method, $parameters = [])
	{
		if($method && $method != '_setHelpers') {
			// Is Auth Action or Authorized
			if(in_array($class, $this->escapeControllers) || $this->processMiddlewares()) {
				$controller = "\\App\\Controllers\\".$class;
				$obj 		= new $controller();
				if(method_exists($obj, $method)) {
					$rc 		= new ReflectionClass($controller);
					$rcm 		= $rc->getMethod($method);
					// Load form data if already instantiated
					$this->request->_loadBodyData();
					if($rcm->getNumberOfParameters()> 0) {
						$request = NULL;
						foreach($rcm->getParameters() as $param) {
							if($param->getClass()) {
								$class 		= "\\".$param->getClass()->name;
								$request 	= new $class();
								$request->_setValidation($this->request, $this->response);
								if(!$request->validated()) {
									return $request->validation->responseErrors();
								}
							}
						}
						$validation 	= ($request)? $request->validation: new Validation($this->request, $this->response);
						$obj->_setHelpers($this->config, $this->request, $this->response, $validation);
						if($request) {
							$parameters[] = $request;
						}
						if($rcm->getNumberOfParameters() == sizeof($parameters)) {
							return call_user_func_array([$obj, $method], $parameters);
						}
					} else if($rcm->getNumberOfParameters() == 0 && sizeof($parameters) == 0) {
						$obj->_setHelpers($this->config, $this->request, $this->response, new Validation($this->request, $this->response));
						return call_user_func_array([$obj, $method], $parameters);
					}
				}
			}
		}
		return $this->doResponse();
	}

	private function doResponse()
	{
		$response = $this->response->setResponseCode($this->responseCode);
		if($this->config->get('app.response_type') == 'json') {
			return $response->json($this->responseMessage);
		} else {
			return $response->view('errors.404');
		}
	}

	private function processMiddlewares()
	{
		if(sizeof($this->middlewares)> 0) {
			foreach($this->middlewares as $middleware) {
				$class 		= "\\App\\Middlewares\\".$middleware;
				$instance 	= new $class();
				if(!$instance->process($this->request)) {
					$this->responseCode 	= $instance->getCode();
					$this->responseMessage 	= $instance->getMessage();
					return false;
				}
			}
		}
		return true;
	}
}