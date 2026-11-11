<?php
namespace App\Controller;

use App\Service\DeviceTypeService;
use ViewEngine;

class DeviceTypeController {
	private $deviceTypeService;

	public function __construct() {
		$this->deviceTypeService = new DeviceTypeService();
	}

	public function index() {
		$deviceTypes = $this->deviceTypeService->getAll();
		ViewEngine::render('device_types/index.html.twig', [
			'device_types' => $deviceTypes
		]);
	}

	public function new() {
		ViewEngine::render('device_types/new.html.twig');
	}

	public function create() {
		$baseUrl = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/\\');
		if ($this->deviceTypeService->create($_POST)) {
			$_SESSION['success'] = 'Tipo de dispositivo registrado correctamente.';
			header('Location: ' . ($baseUrl ?: '') . '/device-types');
		} else {
			$_SESSION['error'] = 'Error al registrar el tipo de dispositivo. Verifique los datos ingresados.';
			header('Location: ' . ($baseUrl ?: '') . '/device-type/new');
		}
		exit;
	}

	public function edit(array $vars) {
		$id = intval($vars['id']);
		$deviceType = $this->deviceTypeService->getById($id);
		if (!$deviceType) {
			$_SESSION['error'] = 'Tipo de dispositivo no encontrado.';
			$baseUrl = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/\\');
			header('Location: ' . ($baseUrl ?: '') . '/device-types');
			exit;
		}
		ViewEngine::render('device_types/edit.html.twig', [
			'device_type' => $deviceType
		]);
	}

	public function update(array $vars) {
		$id = intval($vars['id']);
		$baseUrl = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/\\');
		if ($this->deviceTypeService->update($id, $_POST)) {
			$_SESSION['updated'] = 'Tipo de dispositivo actualizado correctamente.';
			header('Location: ' . ($baseUrl ?: '') . '/device-types');
		} else {
			$_SESSION['error'] = 'Error al actualizar el tipo de dispositivo.';
			header('Location: ' . ($baseUrl ?: '') . '/device-type/' . $id . '/edit');
		}
		exit;
	}

	public function delete(array $vars) {
		$id = intval($vars['id']);
		$baseUrl = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/\\');
		$result = $this->deviceTypeService->delete($id);

		if ($result['success']) {
			if (($result['mode'] ?? '') === 'deactivated') {
				$_SESSION['updated'] = $result['message'];
			} else {
				$_SESSION['deleted'] = $result['message'];
			}
		} else {
			$_SESSION['error'] = $result['message'];
		}

		header('Location: ' . ($baseUrl ?: '') . '/device-types');
		exit;
	}
}
?>