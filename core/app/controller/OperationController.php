<?php
namespace App\Controller;

use App\Service\OperationService;
use App\Service\DeviceService;
use App\Service\ContactService;
use App\Service\StatusService;
use App\Service\ServiceCatalogService;
use App\Service\PaymentService;
use ViewEngine;

class OperationController {
	private $operationService;
	private $deviceService;
	private $contactService;
	private $statusService;
	private $serviceCatalogService;
	private $paymentService;

	public function __construct() {
		$this->operationService = new OperationService();
		$this->deviceService = new DeviceService();
		$this->contactService = new ContactService();
		$this->statusService = new StatusService();
		$this->serviceCatalogService = new ServiceCatalogService();
		$this->paymentService = new PaymentService();
	}

	public function index() {
		$operations = $this->operationService->getAll();
		ViewEngine::render('operations/index.html.twig', [
			'operations' => $operations,
			'paid_map' => $this->paymentService->getPaidMap(),
		]);
	}

	public function new() {
		$devices = $this->deviceService->getAll();
		$clients = $this->contactService->getClients();
		$statuses = $this->statusService->getAll();

		ViewEngine::render('operations/new.html.twig', [
			'devices' => $devices,
			'clients' => $clients,
			'statuses' => $statuses,
		]);
	}

	public function create() {
		$baseUrl = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/\\');
		$opId = $this->operationService->create($_POST);
		if ($opId) {
			$_SESSION['success'] = 'Orden de servicio registrada correctamente.';
			header('Location: ' . ($baseUrl ?: '') . '/operation/' . $opId);
		} else {
			$_SESSION['error'] = 'Error al registrar la orden de servicio.';
			header('Location: ' . ($baseUrl ?: '') . '/operation/new');
		}
		exit;
	}

	public function show(array $vars) {
		$id = intval($vars['id']);
		$operation = $this->operationService->getById($id);
		if (!$operation) {
			$_SESSION['error'] = 'Orden de servicio no encontrada.';
			$baseUrl = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/\\');
			header('Location: ' . ($baseUrl ?: '') . '/operations');
			exit;
		}

		$details = $this->operationService->getDetails($id);
		$services = $this->serviceCatalogService->getAll();
		$statuses = $this->statusService->getAll();

		ViewEngine::render('operations/show.html.twig', [
			'operation' => $operation,
			'details' => $details,
			'services' => $services,
			'statuses' => $statuses,
			'payments' => $this->paymentService->getByOperation($id),
			'payment_summary' => $this->paymentService->getSummary($id),
			'payment_methods' => $this->paymentService->getMethods(),
		]);
	}

	public function edit(array $vars) {
		$id = intval($vars['id']);
		$operation = $this->operationService->getById($id);
		if (!$operation) {
			$_SESSION['error'] = 'Orden de servicio no encontrada.';
			$baseUrl = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/\\');
			header('Location: ' . ($baseUrl ?: '') . '/operations');
			exit;
		}

		$devices = $this->deviceService->getAll();
		$clients = $this->contactService->getClients();
		$statuses = $this->statusService->getAll();

		ViewEngine::render('operations/edit.html.twig', [
			'operation' => $operation,
			'devices' => $devices,
			'clients' => $clients,
			'statuses' => $statuses,
		]);
	}

	public function update(array $vars) {
		$id = intval($vars['id']);
		$baseUrl = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/\\');
		if ($this->operationService->update($id, $_POST)) {
			$_SESSION['updated'] = 'Orden de servicio actualizada correctamente.';
			header('Location: ' . ($baseUrl ?: '') . '/operation/' . $id);
		} else {
			$_SESSION['error'] = 'Error al actualizar la orden de servicio.';
			header('Location: ' . ($baseUrl ?: '') . '/operation/' . $id . '/edit');
		}
		exit;
	}

	public function updateStatus(array $vars) {
		$id = intval($vars['id']);
		$statusId = !empty($_POST['status_id']) ? intval($_POST['status_id']) : 1;
		$baseUrl = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/\\');

		if ($this->operationService->updateStatus($id, $statusId)) {
			$_SESSION['updated'] = 'Estado de la reparación actualizado.';
		} else {
			$_SESSION['error'] = 'Error al actualizar el estado.';
		}
		header('Location: ' . ($baseUrl ?: '') . '/operation/' . $id);
		exit;
	}

	public function addDetail(array $vars) {
		$id = intval($vars['id']);
		$baseUrl = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/\\');
		if ($this->operationService->addDetail($id, $_POST)) {
			$_SESSION['success'] = 'Servicio agregado a la orden.';
		} else {
			$_SESSION['error'] = 'Error al agregar el servicio.';
		}
		header('Location: ' . ($baseUrl ?: '') . '/operation/' . $id);
		exit;
	}

	public function deleteDetail(array $vars) {
		$opId = intval($vars['id']);
		$detailId = intval($vars['detailId']);
		$baseUrl = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/\\');
		if ($this->operationService->deleteDetail($detailId)) {
			$_SESSION['deleted'] = 'Detalle eliminado.';
		} else {
			$_SESSION['error'] = 'Error al eliminar el detalle.';
		}
		header('Location: ' . ($baseUrl ?: '') . '/operation/' . $opId);
		exit;
	}

	public function addPayment(array $vars) {
		$id = intval($vars['id']);
		$baseUrl = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/\\');
		$error = $this->paymentService->create($id, $_POST);
		if ($error === null) {
			$_SESSION['success'] = 'Pago registrado correctamente.';
		} else {
			$_SESSION['error'] = $error;
		}
		header('Location: ' . ($baseUrl ?: '') . '/operation/' . $id);
		exit;
	}

	public function deletePayment(array $vars) {
		$opId = intval($vars['id']);
		$paymentId = intval($vars['paymentId']);
		$baseUrl = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/\\');
		if ($this->paymentService->delete($paymentId)) {
			$_SESSION['deleted'] = 'Pago eliminado de la orden.';
		} else {
			$_SESSION['error'] = 'Error al eliminar el pago.';
		}
		header('Location: ' . ($baseUrl ?: '') . '/operation/' . $opId);
		exit;
	}

	public function delete(array $vars) {
		$id = intval($vars['id']);
		$baseUrl = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/\\');
		if ($this->operationService->delete($id)) {
			$_SESSION['deleted'] = 'Orden de servicio eliminada.';
		} else {
			$_SESSION['error'] = 'Error al eliminar la orden de servicio.';
		}
		header('Location: ' . ($baseUrl ?: '') . '/operations');
		exit;
	}
}
