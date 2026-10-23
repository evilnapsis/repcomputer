<?php
namespace App\Controller;

use App\Service\AuthService;
use ViewEngine;

class AuthController {
	public function showLogin() {
		if (AuthService::check()) {
			$baseUrl = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/\\');
			header('Location: ' . ($baseUrl ?: '') . '/home');
			exit;
		}
		ViewEngine::render('auth/login.html.twig');
	}

	public function processLogin() {
		$username = $_POST['username'] ?? '';
		$password = $_POST['password'] ?? '';
		$baseUrl = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/\\');

		if (AuthService::login($username, $password)) {
			header('Location: ' . ($baseUrl ?: '') . '/home');
			exit;
		} else {
			$_SESSION['error'] = 'Usuario o contraseña incorrectos.';
			header('Location: ' . ($baseUrl ?: '') . '/login');
			exit;
		}
	}

	public function logout() {
		AuthService::logout();
		$baseUrl = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/\\');
		header('Location: ' . ($baseUrl ?: '') . '/login');
		exit;
	}
}
