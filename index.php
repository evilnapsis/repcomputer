<?php
/**
* RepComputer - Sistema de Gestion de Taller y Servicios Tecnicos
* @author evilnapsis
**/

define("ROOT", dirname(__FILE__));

// Ponlo en true solo mientras desarrolles: muestra los errores de PHP en pantalla.
$debug = false;
if($debug){
	ini_set('display_errors', 1);
	ini_set('display_startup_errors', 1);
	error_reporting(E_ALL);
}else{
	ini_set('display_errors', 0);
	error_reporting(0);
}

include "core/autoload.php";
include_once "core/app/autoload.php";

ob_start();
session_start();
Core::$root="";

// si quieres que se muestren las consultas SQL debes descomentar la siguiente linea
// Core::$debug_sql = true;

use App\Service\AuthService;

$routes = require __DIR__ . '/core/app/routes.php';
$dispatcher = FastRoute\simpleDispatcher($routes);

$httpMethod = $_SERVER['REQUEST_METHOD'];
$uri = $_SERVER['REQUEST_URI'];
if (false !== $pos = strpos($uri, '?')) {
	$uri = substr($uri, 0, $pos);
}
$uri = rawurldecode($uri);

$baseFolder = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
if (!empty($baseFolder) && strpos($uri, $baseFolder) === 0) {
	$uri = substr($uri, strlen($baseFolder));
}
if (empty($uri)) {
	$uri = '/';
}

$routeInfo = $dispatcher->dispatch($httpMethod, $uri);

if ($routeInfo[0] === FastRoute\Dispatcher::FOUND) {
	if ($httpMethod === 'POST') {
		$token = $_POST['csrf_token'] ?? null;
		if (!Session::validateCsrf($token)) {
			http_response_code(403);
			echo "<h1>403 - Token CSRF invalido</h1><p>Recarga el formulario e intenta de nuevo.</p>";
			exit;
		}
	}

	// Controladores que requieren sesion iniciada
	$protectedControllers = [
		App\Controller\HomeController::class,
		App\Controller\DeviceController::class,
		App\Controller\ContactController::class,
		App\Controller\ServiceCatalogController::class,
		App\Controller\OperationController::class,
		App\Controller\UserController::class,
		App\Controller\SettingsController::class,
		App\Controller\ProfileController::class,
		App\Controller\ReportController::class,
	];
	$handler = $routeInfo[1];
	if (in_array($handler[0], $protectedControllers, true) && !AuthService::check()) {
		header('Location: ' . ($baseFolder ?: '') . '/login');
		exit;
	}

	[$controllerClass, $method] = $handler;
	$controller = new $controllerClass();
	$controller->$method($routeInfo[2]);
	exit;
}

// Ruta no encontrada (404)
http_response_code(404);
echo "<h1>404 - Pagina no encontrada</h1>";
exit;
