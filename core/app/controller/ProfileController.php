<?php
namespace App\Controller;

use App\Service\UserService;
use App\Service\AuthService;
use ViewEngine;

class ProfileController {
	private $userService;

	public function __construct() {
		$this->userService = new UserService();
	}

	public function index() {
		$user = AuthService::user();
		ViewEngine::render('profile/index.html.twig', ['user' => $user]);
	}

	public function changePassword() {
		$baseUrl = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/\\');
		$user = AuthService::user();
		if (!$user) {
			header('Location: ' . ($baseUrl ?: '') . '/login');
			exit;
		}

		$currentPass = $_POST['current_password'] ?? '';
		$newPass = $_POST['new_password'] ?? '';
		$confirmPass = $_POST['confirm_password'] ?? '';

		$hash = sha1(md5($currentPass));
		if ($user->password !== $hash && $user->password !== md5($currentPass) && $user->password !== $currentPass) {
			$_SESSION['error'] = 'La contraseña actual no es correcta.';
			header('Location: ' . ($baseUrl ?: '') . '/profile');
			exit;
		}

		if ($newPass !== $confirmPass) {
			$_SESSION['error'] = 'La confirmación de la contraseña no coincide.';
			header('Location: ' . ($baseUrl ?: '') . '/profile');
			exit;
		}

		if (strlen($newPass) < 4) {
			$_SESSION['error'] = 'La nueva contraseña debe tener al menos 4 caracteres.';
			header('Location: ' . ($baseUrl ?: '') . '/profile');
			exit;
		}

		$this->userService->changePassword($user->id, $newPass);
		$_SESSION['success'] = 'Contraseña actualizada con éxito.';
		header('Location: ' . ($baseUrl ?: '') . '/profile');
		exit;
	}
}
