<?php

namespace TypePHP\Helpers;

class Config
{
	private $data = [];

	public function set($path, $value)
	{
		$pathArray = explode('.', $path);
		if(sizeof($pathArray)> 0) {
			if(isset($this->data[$pathArray[0]])) {
				return $this->setValue($pathArray, $value);
			} else if(file_exists($this->confiPath($pathArray[0]))) {
				$this->extract($pathArray[0]);
				return $this->setValue($pathArray, $value);
			}
		}
	}

	public function get($path)
	{
		$pathArray = explode('.', $path);
		if(sizeof($pathArray)> 0) {
			if(isset($this->data[$pathArray[0]])) {
				return $this->getValue($pathArray);
			} else if(file_exists($this->confiPath($pathArray[0]))) {
				$this->extract($pathArray[0]);
				return $this->getValue($pathArray);
			}
		}
		return null;
	}

	private function extract($parentKey)
	{
		$this->data[$parentKey] = include $this->confiPath($parentKey);
	}

	private function setValue($name, $value)
	{
		for($i = &$this->data; $key = array_shift($name); $i = &$i[$key]) {
	      if(!isset($i[$key])) $i[$key] = array();
	    }
	    $i = $value;
	}

	private function getValue($name)
	{
		for($i = &$this->data; $key = array_shift($name); $i = $i[$key]) {
	      if(!isset($i[$key])) return null;
	    }
	    return $i;
	}

	private function confiPath($file)
	{
		return ROOT_PATH.DS.'config'.DS.$file.'.php';
	}
}