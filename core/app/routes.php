<?php
use App\Controller\HomeController;
use App\Controller\AuthController;
use App\Controller\DeviceController;
use App\Controller\ContactController;
use App\Controller\ServiceCatalogController;
use App\Controller\OperationController;
use App\Controller\UserController;
use App\Controller\SettingsController;
use App\Controller\ProfileController;
use App\Controller\ReportController;

return function(FastRoute\RouteCollector $r) {
	// Inicio / Dashboard
	$r->addRoute('GET', '/', [HomeController::class, 'index']);
	$r->addRoute('GET', '/home', [HomeController::class, 'index']);

	// Autenticación
	$r->addRoute('GET', '/login', [AuthController::class, 'showLogin']);
	$r->addRoute('POST', '/login', [AuthController::class, 'processLogin']);
	$r->addRoute('GET', '/logout', [AuthController::class, 'logout']);

	// Dispositivos / Equipos
	$r->addRoute('GET', '/devices', [DeviceController::class, 'index']);
	$r->addRoute('GET', '/device/new', [DeviceController::class, 'new']);
	$r->addRoute('POST', '/device/create', [DeviceController::class, 'create']);
	$r->addRoute('GET', '/device/{id:\d+}/edit', [DeviceController::class, 'edit']);
	$r->addRoute('POST', '/device/{id:\d+}/update', [DeviceController::class, 'update']);
	$r->addRoute('POST', '/device/{id:\d+}/delete', [DeviceController::class, 'delete']);

	// Clientes
	$r->addRoute('GET', '/contacts', [ContactController::class, 'index']);
	$r->addRoute('GET', '/contact/new', [ContactController::class, 'new']);
	$r->addRoute('POST', '/contact/create', [ContactController::class, 'create']);
	$r->addRoute('GET', '/contact/{id:\d+}/edit', [ContactController::class, 'edit']);
	$r->addRoute('POST', '/contact/{id:\d+}/update', [ContactController::class, 'update']);
	$r->addRoute('POST', '/contact/{id:\d+}/delete', [ContactController::class, 'delete']);

	// Catálogo de Servicios
	$r->addRoute('GET', '/services', [ServiceCatalogController::class, 'index']);
	$r->addRoute('GET', '/service/new', [ServiceCatalogController::class, 'new']);
	$r->addRoute('POST', '/service/create', [ServiceCatalogController::class, 'create']);
	$r->addRoute('GET', '/service/{id:\d+}/edit', [ServiceCatalogController::class, 'edit']);
	$r->addRoute('POST', '/service/{id:\d+}/update', [ServiceCatalogController::class, 'update']);
	$r->addRoute('POST', '/service/{id:\d+}/delete', [ServiceCatalogController::class, 'delete']);

	// Órdenes de Servicio
	$r->addRoute('GET', '/operations', [OperationController::class, 'index']);
	$r->addRoute('GET', '/operation/new', [OperationController::class, 'new']);
	$r->addRoute('POST', '/operation/create', [OperationController::class, 'create']);
	$r->addRoute('GET', '/operation/{id:\d+}', [OperationController::class, 'show']);
	$r->addRoute('GET', '/operation/{id:\d+}/edit', [OperationController::class, 'edit']);
	$r->addRoute('POST', '/operation/{id:\d+}/update', [OperationController::class, 'update']);
	$r->addRoute('POST', '/operation/{id:\d+}/status-update', [OperationController::class, 'updateStatus']);
	$r->addRoute('POST', '/operation/{id:\d+}/add-detail', [OperationController::class, 'addDetail']);
	$r->addRoute('POST', '/operation/{id:\d+}/delete-detail/{detailId:\d+}', [OperationController::class, 'deleteDetail']);
	$r->addRoute('POST', '/operation/{id:\d+}/add-payment', [OperationController::class, 'addPayment']);
	$r->addRoute('POST', '/operation/{id:\d+}/delete-payment/{paymentId:\d+}', [OperationController::class, 'deletePayment']);
	$r->addRoute('POST', '/operation/{id:\d+}/delete', [OperationController::class, 'delete']);

	// Usuarios
	$r->addRoute('GET', '/users', [UserController::class, 'index']);
	$r->addRoute('GET', '/user/new', [UserController::class, 'new']);
	$r->addRoute('POST', '/user/create', [UserController::class, 'create']);
	$r->addRoute('GET', '/user/{id:\d+}/edit', [UserController::class, 'edit']);
	$r->addRoute('POST', '/user/{id:\d+}/update', [UserController::class, 'update']);
	$r->addRoute('POST', '/user/{id:\d+}/delete', [UserController::class, 'delete']);

	// Ajustes y Perfil
	$r->addRoute('GET', '/settings', [SettingsController::class, 'index']);
	$r->addRoute('POST', '/settings/update', [SettingsController::class, 'update']);
	$r->addRoute('GET', '/profile', [ProfileController::class, 'index']);
	$r->addRoute('POST', '/profile/change-password', [ProfileController::class, 'changePassword']);

	// Reportes
	$r->addRoute('GET', '/reports', [ReportController::class, 'index']);
};
