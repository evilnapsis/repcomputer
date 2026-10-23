<?php
namespace App\Controller;

use App\Service\UserService;
use ViewEngine;

class UserController {
	private $userService;

	public function __construct() {
		$this->userService = new UserService();
	}

	public function index() {
		$users = $this->userService->getAll();
		ViewEngine::render('users/index.html.twig', ['users' => $users]);
	}

	public function new() {
		ViewEngine::render('users/new.html.twig');
	}

	public function create() {
		$baseUrl = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/\\');
		if ($this->userService->create($_POST)) {
			$_SESSION['success'] = 'Usuario creado correctamente.';
			header('Location: ' . ($baseUrl ?: '') . '/users');
		} else {
			$_SESSION['error'] = 'Error al crear el usuario.';
			header('Location: ' . ($baseUrl ?: '') . '/user/new');
		}
		exit;
	}

	public function edit(array $vars) {
		$id = intval($vars['id']);
		$user = $this->userService->getById($id);
		if (!$user) {
			$_SESSION['error'] = 'Usuario no encontrado.';
			$baseUrl = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/\\');
			header('Location: ' . ($baseUrl ?: '') . '/users');
			exit;
		}
		ViewEngine::render('users/edit.html.twig', ['user' => $user]);
	}

	public function update(array $vars) {
		$id = intval($vars['id']);
		$baseUrl = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/\\');
		if ($this->userService->update($id, $_POST)) {
			$_SESSION['updated'] = 'Usuario actualizado correctamente.';
			header('Location: ' . ($baseUrl ?: '') . '/users');
		} else {
			$_SESSION['error'] = 'Error al actualizar el usuario.';
			header('Location: ' . ($baseUrl ?: '') . '/user/' . $id . '/edit');
		}
		exit;
	}

	public function delete(array $vars) {
		$id = intval($vars['id']);
		$baseUrl = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/\\');
		if ($this->userService->delete($id)) {
			$_SESSION['deleted'] = 'Usuario eliminado correctamente.';
		} else {
			$_SESSION['error'] = 'Error al eliminar el usuario.';
		}
		header('Location: ' . ($baseUrl ?: '') . '/users');
		exit;
	}
}
