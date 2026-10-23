<?php
namespace App\Service;

use StatusData;

class StatusService {
	public function getAll(): array {
		return StatusData::getAll();
	}

	public function getById(int $id): ?StatusData {
		return StatusData::getById($id);
	}

	public function create(array $data): bool {
		$status = new StatusData();
		$status->name = $data['name'] ?? '';
		$status->color = $data['color'] ?? '#6c757d';

		$res = $status->add();
		return $res ? true : false;
	}

	public function update(int $id, array $data): bool {
		$status = StatusData::getById($id);
		if (!$status) return false;

		$status->name = $data['name'] ?? $status->name;
		$status->color = $data['color'] ?? $status->color;

		$status->update();
		return true;
	}

	public function delete(int $id): bool {
		$status = StatusData::getById($id);
		if ($status) {
			$status->del();
			return true;
		}
		return false;
	}
}
