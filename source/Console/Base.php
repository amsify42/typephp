<?php

namespace TypePHP\Console;

use TypePHP\Helpers\Request;
use TypePHP\Helpers\Response;

class Base
{
	private $request, $response;
	private $actions 	= ['create'];
	private $types 		= ['controller', 'model', 'request', 'middleware'];

	function __construct(Request $request, Response $response)
	{
		$this->request 	= $request;
		$this->response = $response;
	}

	public function init()
	{
		$type 	= $this->request->get('type');
		$action = $this->request->get('action');
		$name 	= $this->request->get('name');
		if($type && $action && $name) {
			if(in_array($type, $this->types) && in_array($action, $this->actions)) {
				$method 	= $action.ucfirst($type);
				$message 	= $this->{$method}($name);
				return $this->response->output($message);
			}
		}
		return $this->response->output("No action executed!");
	}

	private function createController($name)
	{
		$file 	= APP_PATH.DS.'Controllers'.DS.$name.'.php';

		if(!file_exists($file)) {
			$fp = fopen($file,'w');

$content = "<?php

namespace App\Controllers;

use App\Base\Controller;

class {$name} extends Controller
{

}";

			fwrite($fp, $content);
			fclose($fp);
			return 'Controller Created Successfully';
		} else {
			return 'Controller already exist';
		}
	}

	private function createModel($name)
	{
		$file 	= APP_PATH.DS.'Models'.DS.$name.'.php';

		if(!file_exists($file)) {
			$fp = fopen($file,'w');

$content = "<?php

namespace App\Models;

use App\Base\Model;

class {$name} extends Model
{
";
		
		$table = $this->request->get('table');
		if($table) {
$content .= "	protected \$table = '{$table}';
";
		}

		$primaryKey = $this->request->get('primary_key');
		if($primaryKey) {
$content .= "	protected \$primaryKey = '{$primaryKey}';
";
		}		

		$timestamps = $this->request->get('timestamps');
		if($timestamps) {
$content .= "	protected \$timestamps = {$timestamps};
";
		}
$content .= "	
}";

			fwrite($fp, $content);
			fclose($fp);
			return 'Model Created Successfully';
		} else {
			return 'Model already exist';
		}
	}

	private function createRequest($name)
	{
		$file 	= APP_PATH.DS.'Request'.DS.$name.'.php';

		if(!file_exists($file)) {
			$fp = fopen($file,'w');

$content = "<?php

namespace App\Request;

use App\Base\FormRequest;

class {$name} extends FormRequest
{
	protected function rules()
	{
		return [
			
		];
	}

}";

			fwrite($fp, $content);
			fclose($fp);
			return 'Request Created Successfully';
		} else {
			return 'Request already exist';
		}
	}

	private function createMiddleware($name)
	{
		$file 	= APP_PATH.DS.'Middlewares'.DS.$name.'.php';

		if(!file_exists($file)) {
			$fp = fopen($file,'w');

$content = "<?php

namespace App\Middlewares;

use App\Base\Middleware;
use App\Helpers\Request;

class {$name} extends Middleware
{
	public function process(Request \$request)
	{
		return false;
	}

}";

			fwrite($fp, $content);
			fclose($fp);
			return 'Middleware Created Successfully';
		} else {
			return 'Middleware already exist';
		}
	}
}