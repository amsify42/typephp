<?php

namespace TypePHP\Application;

class Middleware
{
	protected $responseCode 	= 401;
	protected $responseMessage 	= 'Access denied!';

	public function getCode()
	{
		return $this->responseCode;
	}

	public function getMessage()
	{
		return $this->responseMessage;
	}
}