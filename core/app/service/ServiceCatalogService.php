<?php
namespace App\Service;

use ServiceData;

class ServiceCatalogService {
	public function getAll(): array {
		return ServiceData::getAll();
	}

	public function getById(int $id): ?ServiceData {
		return ServiceData::getById($id);
	}

	public function create(array $data): bool {
		$service = new ServiceData();
		$service->name = $data['name'] ?? '';
		$service->description = $data['description'] ?? '';
		$service->price = !empty($data['price']) ? floatval($data['price']) : 0;
		$service->is_active = 1;

		$res = $service->add();
		return $res ? true : false;
	}

	public function update(int $id, array $data): bool {
		$service = ServiceData::getById($id);
		if (!$service) return false;

		$service->name = $data['name'] ?? $service->name;
		$service->description = $data['description'] ?? $service->description;
		$service->price = isset($data['price']) ? floatval($data['price']) : $service->price;
		$service->is_active = isset($data['is_active']) ? intval($data['is_active']) : $service->is_active;

		$service->update();
		return true;
	}

	public function delete(int $id): bool {
		$service = ServiceData::getById($id);
		if ($service) {
			$service->del();
			return true;
		}
		return false;
	}
}
