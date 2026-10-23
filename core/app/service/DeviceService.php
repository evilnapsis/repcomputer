<?php
namespace App\Service;

use DeviceData;

class DeviceService {
	public function getAll(): array {
		return DeviceData::getAll();
	}

	public function getById(int $id): ?DeviceData {
		return DeviceData::getById($id);
	}

	public function create(array $data): bool {
		$device = new DeviceData();
		$device->plate = $data['plate'] ?? '';
		$device->vin = $data['vin'] ?? '';
		$device->brand = $data['brand'] ?? '';
		$device->model = $data['model'] ?? '';
		$device->year = !empty($data['year']) ? intval($data['year']) : 0;
		$device->color = $data['color'] ?? '';
		$device->type = $data['type'] ?? 'Smartphone';
		$device->imei = $data['imei'] ?? '';
		$device->mac_address = $data['mac_address'] ?? '';
		$device->capacity = $data['capacity'] ?? '';
		$device->hours_meter = !empty($data['hours_meter']) ? intval($data['hours_meter']) : 0;
		$device->contact_id = !empty($data['contact_id']) ? intval($data['contact_id']) : "NULL";
		$device->description = $data['description'] ?? '';
		
		$res = $device->add();
		return $res ? true : false;
	}

	public function update(int $id, array $data): bool {
		$device = DeviceData::getById($id);
		if (!$device) return false;

		$device->plate = $data['plate'] ?? $device->plate;
		$device->vin = $data['vin'] ?? $device->vin;
		$device->brand = $data['brand'] ?? $device->brand;
		$device->model = $data['model'] ?? $device->model;
		$device->year = !empty($data['year']) ? intval($data['year']) : $device->year;
		$device->color = $data['color'] ?? $device->color;
		$device->type = $data['type'] ?? $device->type;
		$device->imei = $data['imei'] ?? $device->imei;
		$device->mac_address = $data['mac_address'] ?? $device->mac_address;
		$device->capacity = $data['capacity'] ?? $device->capacity;
		$device->hours_meter = isset($data['hours_meter']) ? intval($data['hours_meter']) : $device->hours_meter;
		$device->contact_id = !empty($data['contact_id']) ? intval($data['contact_id']) : $device->contact_id;
		$device->description = $data['description'] ?? $device->description;

		$device->update();
		return true;
	}

	public function delete(int $id): bool {
		$device = DeviceData::getById($id);
		if (!$device) return false;
		$device->del();
		return true;
	}
}
?>
