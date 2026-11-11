<?php
namespace App\Service;

use DeviceTypeData;

class DeviceTypeService {
	public function getAll(): array {
		return DeviceTypeData::getAll();
	}

	public function getActive(): array {
		return DeviceTypeData::getActive();
	}

	public function getById(int $id): ?DeviceTypeData {
		return DeviceTypeData::getById($id);
	}

	public function create(array $data): bool {
		$name = trim($data['name'] ?? '');
		if (empty($name)) {
			return false;
		}

		$dt = new DeviceTypeData();
		$dt->name = $name;
		$dt->description = trim($data['description'] ?? '');
		$dt->is_active = isset($data['is_active']) ? intval($data['is_active']) : 1;

		$res = $dt->add();
		return $res ? true : false;
	}

	public function update(int $id, array $data): bool {
		$dt = DeviceTypeData::getById($id);
		if (!$dt) {
			return false;
		}

		$name = trim($data['name'] ?? '');
		if (empty($name)) {
			return false;
		}

		$dt->name = $name;
		$dt->description = trim($data['description'] ?? '');
		$dt->is_active = isset($data['is_active']) ? intval($data['is_active']) : 1;

		$dt->update();
		return true;
	}

	public function delete(int $id): array {
		$dt = DeviceTypeData::getById($id);
		if (!$dt) {
			return ['success' => false, 'message' => 'Tipo de dispositivo no encontrado.'];
		}

		$count = $dt->countDevices();
		if ($count > 0) {
			// Borrado lógico / Desactivación para proteger histórico
			$dt->is_active = 0;
			$dt->update();
			return [
				'success' => true,
				'mode' => 'deactivated',
				'message' => "El tipo '{$dt->name}' tiene {$count} equipo(s) asociado(s). Se ha desactivado para proteger el historial."
			];
		}

		$dt->del();
		return [
			'success' => true,
			'mode' => 'deleted',
			'message' => 'Tipo de dispositivo eliminado correctamente.'
		];
	}
}
