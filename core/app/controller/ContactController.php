<?php
namespace App\Controller;

use App\Service\ContactService;
use ViewEngine;

class ContactController {
	private $contactService;

	public function __construct() {
		$this->contactService = new ContactService();
	}

	public function index() {
		$contacts = $this->contactService->getClients();

		ViewEngine::render('contacts/index.html.twig', [
			'contacts' => $contacts,
		]);
	}

	public function new() {
		ViewEngine::render('contacts/new.html.twig');
	}

	public function create() {
		$baseUrl = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/\\');
		if ($this->contactService->create($_POST)) {
			$_SESSION['success'] = 'Contacto registrado correctamente.';
			header('Location: ' . ($baseUrl ?: '') . '/contacts');
		} else {
			$_SESSION['error'] = 'Error al registrar el contacto.';
			header('Location: ' . ($baseUrl ?: '') . '/contact/new');
		}
		exit;
	}

	public function edit(array $vars) {
		$id = intval($vars['id']);
		$contact = $this->contactService->getById($id);
		if (!$contact) {
			$_SESSION['error'] = 'Contacto no encontrado.';
			$baseUrl = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/\\');
			header('Location: ' . ($baseUrl ?: '') . '/contacts');
			exit;
		}
		ViewEngine::render('contacts/edit.html.twig', ['contact' => $contact]);
	}

	public function update(array $vars) {
		$id = intval($vars['id']);
		$baseUrl = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/\\');
		if ($this->contactService->update($id, $_POST)) {
			$_SESSION['updated'] = 'Contacto actualizado correctamente.';
			header('Location: ' . ($baseUrl ?: '') . '/contacts');
		} else {
			$_SESSION['error'] = 'Error al actualizar el contacto.';
			header('Location: ' . ($baseUrl ?: '') . '/contact/' . $id . '/edit');
		}
		exit;
	}

	public function delete(array $vars) {
		$id = intval($vars['id']);
		$baseUrl = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/\\');
		if ($this->contactService->delete($id)) {
			$_SESSION['deleted'] = 'Contacto eliminado correctamente.';
		} else {
			$_SESSION['error'] = 'Error al eliminar el contacto.';
		}
		header('Location: ' . ($baseUrl ?: '') . '/contacts');
		exit;
	}
}
