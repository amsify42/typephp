<?php

namespace TypePHP\Application;

use TypePHP\Helpers\Request;

class Router
{
	private $routes = [];
	private $isAuto = true;

	public function setAutoRoute($flag = true)
	{
		$this->isAuto = $flag;
	}

	public function get($pattern, $action)
	{
		$this->setRoute('GET', $pattern, $action);
	}

	public function post($pattern, $action)
	{
		$this->setRoute('POST', $pattern, $action);
	}

	public function put($pattern, $action)
	{
		$this->setRoute('PUT', $pattern, $action);
	}

	public function delete($pattern, $action)
	{
		$this->setRoute('DELETE', $pattern, $action);
	}

	public function match($types = [], $pattern, $action)
	{
		$this->setRoute($types, $pattern, $action);
	}

	private function setRoute($type, $pattern, $action)
	{
		$class 			= null;
		$method 		= null;
		$callback 		= null;
		if(is_callable($action)) {
			$callback = $action;
		} else {
			$actionArray 	= explode('@', $action);
			$class 			= $actionArray[0];
			$method 		= isset($actionArray[1])? $actionArray[1]: 'index';
		}
		$this->routes[] = [
						'type' 		=> $type,
						'pattern' 	=> $pattern,
						'class' 	=> $class,
						'action' 	=> $method,
						'callback' 	=> $callback,
						'params' 	=> []
					];
	}

	public function isRegistered(Request $request, $uri)
	{
		$target = NULL;
		if(sizeof($this->routes)> 0) {
			$uriArray 	= explode('/', $uri);
			foreach($this->routes as $rKey => $route) {
				if($route['pattern'] == $uri || $route['pattern'] == $uri.'/') {
					$target = $route; break;
				} else {
					$routeArray = explode('/', $route['pattern']);
					$result 	= $this->matchPattern($uriArray, $routeArray);
					if($result['matched']) {
						$this->routes[$rKey]['params'] 	= $result['params'];
						$target 						= $this->routes[$rKey];
						break;
					}
				}
			}
		}
		if($target) {
			$target['is_type'] = ($request->isRequest($target['type']))? true: false;
		}
		return $target;
	}

	private function matchPattern($uriArray, $routeArray)
	{
		$uriArray 	= array_values(array_filter($uriArray));
		$routeArray = array_values(array_filter($routeArray));
		$result 	= ['matched' => false, 'params' => []];
		if(sizeof($uriArray) == sizeof($routeArray)) {
			$result['matched'] = true;
			foreach($uriArray as $uKey => $uri) {
				preg_match('/{(.*?)}/', $routeArray[$uKey], $matches);
				$matchCount = sizeof($matches);
				if($matchCount > 1) {
					$result['params'][] = $uri;
				}
				if($uri != $routeArray[$uKey] && !$matchCount) {
					$result['matched'] = false; break;
				}
			}
		}
		return $result;
	}

	public function autoRouteInfo($route)
	{
		if(!$this->isAuto) return NULL;
		$result 	= ['class' => '', 'action' => '', 'params' => []];
		$routeArray = explode('/', $route);
		$strings 	= [];
		foreach($routeArray as $rKey => $routeEl) {
			if(is_numeric($routeEl)) {
				$result['params'][] = $routeEl;
			} else {
				$strings[] = $routeEl;
			}
		}
		$result['action'] = dashesToCamelCase(end($strings));
		array_pop($strings);
		foreach($strings as $sKey => $string) {
			$result['class'] .= '\\'.dashesToCamelCase($string, true);
		}
		$result['class'] = trim($result['class'], '\\');
		return $result;
	}
}