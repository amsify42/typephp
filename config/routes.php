<?php

/**
 * Create instance for registering routes
 * @var \TypePHP\Application\Router
 */
$router = new \TypePHP\Application\Router;

/**
 * Set Auto Route
 */
$router->setAutoRoute(false);

/**
 * Example Routes
 */
// $router->get('/', function(){ return $this->response->output('Welcome') });
// $router->get('contact', 'Contact@index');
// $router->post('contact/action', 'Contact@action');

/*********************************************************/

$router->get('/', function() {
	return $this->response->output('Welcome to TypePHP');
});