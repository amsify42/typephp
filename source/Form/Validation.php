<?php

namespace TypePHP\Form;

use TypePHP\Helpers\Request;
use TypePHP\Helpers\Response;

class Validation
{
	public 	$message 	= 'Validation errors occured';
	public 	$request;
	public 	$response;
	private $tmpKeys 	= [];
	private $errors 	= [];
	
	function __construct(Request $request, Response $response)
	{
		$this->request 	= $request;
		$this->response = $response;
	}

	public function process($rules)
	{
		$this->doValidation($rules);
		return (sizeof($this->errors)> 0)? false: true;
	}

	public function responseErrors($errors = [])
	{
		$errors = (sizeof($errors)> 0)? $errors: $this->errors;
		return $this->response->setCode(400)->json($this->message, false, [], [], $errors);
	}

	private function doValidation($rules)
	{
		$this->errors = [];
		foreach($rules as $rKey => $ruleNames) {
			$ruleNamesArray = explode('|', $ruleNames);
			if(sizeof($ruleNamesArray)> 0) {
				foreach($ruleNamesArray as $rule) {
					$ruleArray 	= explode(':', $rule);
					$ruleName 	= $ruleArray[0];
					$inputValue = $this->request->get($rKey);
					switch($ruleName) {
						case 'required':
							if(isEmpty($inputValue))
								$this->errors[$rKey] = 'Field is required';
							break;
						case 'array':
							if(!is_array($inputValue))
								$this->errors[$rKey] = 'Field must be an array';
							break;	
						case 'keys':
							if(isset($ruleArray[1]) && !$this->areKeysPresent($inputValue, $ruleArray[1]))
								$this->errors[$rKey] = $this->keysToArray();
							break;
						case 'childkeys':
							if(isset($ruleArray[1]) && !$this->areChildKeysPresent($inputValue, $ruleArray[1]))
								$this->errors[$rKey] = $this->keysToArray(true);
							break;	
						default:
							# code...
							break;
					}
					if(isset($this->errors[$rKey])) break; 
				}
			}
		}
	}

	private function areChildKeysPresent($items, $keys)
	{
		$isPresent 	= false;
		if(is_array($items) && sizeof($items)> 0) {
			$isPresent = true;
			foreach($items as $item) {
				if(!$this->areKeysPresent($item, $keys)) {
					return false; break;
				}
			}
		}
		return $isPresent;
	}

	private function areKeysPresent($value, $keys)
	{
		$isPresent 	= false;
		$keysArray 	= explode(',', $keys);
		if(is_array($value) && sizeof($keysArray)> 0) {
			$isPresent 	= true;
			foreach($keysArray as $key) {
				$tKey = trim($key);
				if(!isset($value[$tKey]) || isEmpty($value[$tKey])) {
					$this->tmpKeys[] = $tKey; 
					$isPresent = false;
				}
			}
		}
		return $isPresent;
	}

	private function keysToArray($isChild = false)
	{
		$messages 	= [];
		foreach($this->tmpKeys as $tmpKey) {
			$text = $tmpKey.' is mandatory';
			if($isChild) $text .= ' for child element';
			$messages[] = $text;
		}
		$this->tmpKeys = [];
		return $messages;
	}
}