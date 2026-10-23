<?php
namespace App\Controller;

use App\Service\DeviceService;
use App\Service\ContactService;
use ViewEngine;

class DeviceController {
	private $deviceService;
	private $contactService;

	public function __construct() {
		$this->deviceService = new DeviceService();
		$this->contactService = new ContactService();
	}

	public function index() {
		$devices = $this->deviceService->getAll();
		ViewEngine::render('devices/index.html.twig', ['devices' => $devices]);
	}

	public function new() {
		$clients = $this->contactService->getClients();
		ViewEngine::render('devices/new.html.twig', ['clients' => $clients]);
	}

	public function create() {
		$baseUrl = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/\\');
		if ($this->deviceService->create($_POST)) {
			$_SESSION['success'] = 'Dispositivo registrado correctamente.';
			header('Location: ' . ($baseUrl ?: '') . '/devices');
		} else {
			$_SESSION['error'] = 'Error al registrar el dispositivo.';
			header('Location: ' . ($baseUrl ?: '') . '/device/new');
		}
		exit;
	}

	public function edit(array $vars) {
		$id = intval($vars['id']);
		$device = $this->deviceService->getById($id);
		if (!$device) {
			$_SESSION['error'] = 'Dispositivo no encontrado.';
			$baseUrl = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/\\');
			header('Location: ' . ($baseUrl ?: '') . '/devices');
			exit;
		}
		$clients = $this->contactService->getClients();
		ViewEngine::render('devices/edit.html.twig', ['device' => $device, 'clients' => $clients]);
	}

	public function update(array $vars) {
		$id = intval($vars['id']);
		$baseUrl = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/\\');
		if ($this->deviceService->update($id, $_POST)) {
			$_SESSION['updated'] = 'Dispositivo actualizado correctamente.';
			header('Location: ' . ($baseUrl ?: '') . '/devices');
		} else {
			$_SESSION['error'] = 'Error al actualizar el dispositivo.';
			header('Location: ' . ($baseUrl ?: '') . '/device/' . $id . '/edit');
		}
		exit;
	}

	public function delete(array $vars) {
		$id = intval($vars['id']);
		$baseUrl = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/\\');
		if ($this->deviceService->delete($id)) {
			$_SESSION['deleted'] = 'Dispositivo eliminado correctamente.';
		} else {
			$_SESSION['error'] = 'Error al eliminar el dispositivo.';
		}
		header('Location: ' . ($baseUrl ?: '') . '/devices');
		exit;
	}
}
?>
