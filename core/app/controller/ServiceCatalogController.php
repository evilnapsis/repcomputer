<?php
namespace App\Controller;

use App\Service\ServiceCatalogService;
use ViewEngine;

class ServiceCatalogController {
	private $serviceCatalogService;

	public function __construct() {
		$this->serviceCatalogService = new ServiceCatalogService();
	}

	public function index() {
		$services = $this->serviceCatalogService->getAll();
		ViewEngine::render('services/index.html.twig', ['services' => $services]);
	}

	public function new() {
		ViewEngine::render('services/new.html.twig');
	}

	public function create() {
		$baseUrl = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/\\');
		if ($this->serviceCatalogService->create($_POST)) {
			$_SESSION['success'] = 'Servicio creado correctamente.';
			header('Location: ' . ($baseUrl ?: '') . '/services');
		} else {
			$_SESSION['error'] = 'Error al crear el servicio.';
			header('Location: ' . ($baseUrl ?: '') . '/service/new');
		}
		exit;
	}

	public function edit(array $vars) {
		$id = intval($vars['id']);
		$service = $this->serviceCatalogService->getById($id);
		if (!$service) {
			$_SESSION['error'] = 'Servicio no encontrado.';
			$baseUrl = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/\\');
			header('Location: ' . ($baseUrl ?: '') . '/services');
			exit;
		}
		ViewEngine::render('services/edit.html.twig', ['service' => $service]);
	}

	public function update(array $vars) {
		$id = intval($vars['id']);
		$baseUrl = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/\\');
		if ($this->serviceCatalogService->update($id, $_POST)) {
			$_SESSION['updated'] = 'Servicio actualizado correctamente.';
			header('Location: ' . ($baseUrl ?: '') . '/services');
		} else {
			$_SESSION['error'] = 'Error al actualizar el servicio.';
			header('Location: ' . ($baseUrl ?: '') . '/service/' . $id . '/edit');
		}
		exit;
	}

	public function delete(array $vars) {
		$id = intval($vars['id']);
		$baseUrl = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/\\');
		if ($this->serviceCatalogService->delete($id)) {
			$_SESSION['deleted'] = 'Servicio eliminado correctamente.';
		} else {
			$_SESSION['error'] = 'Error al eliminar el servicio.';
		}
		header('Location: ' . ($baseUrl ?: '') . '/services');
		exit;
	}
}
