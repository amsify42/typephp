<?php

namespace TypePHP;

use TypePHP\Helpers\Request;
use TypePHP\Helpers\Response;

class TypePHP
{
	public function request()
	{
		return new Request(true, false);
	}

	public function render(Response $response)
	{
		http_response_code($response->getCode());
		if($response->getType()) {
			header('Content-Type: '.$response->getType());
		}
		echo $response->getContent();
	}
}