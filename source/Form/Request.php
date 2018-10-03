<?php

namespace TypePHP\Form;

use TypePHP\Form\Validation;
use TypePHP\Helpers\Request;
use TypePHP\Helpers\Response;

class Request
{
	public $validation;

	public function _setValidation(Request $request, Response $response)
	{
		$this->validation = new Validation($request, $response);
	}

	public function __call($method, $args)
	{
		// Making this class instance call methods from request class
		if($this->validation) {
			if(method_exists($this->validation->request, $method) && is_callable(array($this->validation->request, $method))) {
				return call_user_func_array(array($this->validation->request, $method), $args);
			}
		}
	}

	protected function rules()
	{
		return [];
	}

	public function validated()
	{
		return $this->validation->process($this->rules());
	}
}