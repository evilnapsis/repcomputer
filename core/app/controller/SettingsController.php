<?php
namespace App\Controller;

use App\Service\SettingsService;
use ViewEngine;

class SettingsController {
	private $settingsService;

	public function __construct() {
		$this->settingsService = new SettingsService();
	}

	public function index() {
		$configs = $this->settingsService->getAll();
		$workshopName = $this->settingsService->getByShort('workshop_name');
		$workshopManager = $this->settingsService->getByShort('workshop_manager');
		$workshopAddress = $this->settingsService->getByShort('workshop_address');
		$workshopPhone = $this->settingsService->getByShort('workshop_phone');

		ViewEngine::render('settings/index.html.twig', [
			'configs' => $configs,
			'workshop_name' => $workshopName ? $workshopName->val : '',
			'workshop_manager' => $workshopManager ? $workshopManager->val : '',
			'workshop_address' => $workshopAddress ? $workshopAddress->val : '',
			'workshop_phone' => $workshopPhone ? $workshopPhone->val : '',
		]);
	}

	public function update() {
		$baseUrl = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/\\');
		if ($this->settingsService->update($_POST)) {
			$_SESSION['updated'] = 'Configuración del taller actualizada correctamente.';
		} else {
			$_SESSION['error'] = 'Error al actualizar la configuración.';
		}
		header('Location: ' . ($baseUrl ?: '') . '/settings');
		exit;
	}
}
