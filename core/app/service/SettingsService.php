<?php
namespace App\Service;

use ConfigurationData;

class SettingsService {
	public function getAll(): array {
		return ConfigurationData::getAll();
	}

	public function getByShort(string $short): ?ConfigurationData {
		return ConfigurationData::getByShort($short);
	}

	public function update(array $postData): bool {
		$campos = ['workshop_name', 'workshop_manager', 'workshop_address', 'workshop_phone'];

		foreach ($campos as $campo) {
			if (isset($postData[$campo])) {
				ConfigurationData::updateValByShort($campo, $postData[$campo]);
			}
		}

		return true;
	}
}
