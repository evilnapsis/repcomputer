<?php
namespace App\Service;

use OperationData;
use OperationDetailData;
use PaymentData;
use ServiceData;
use Session;

class OperationService {
	public function getAll(): array {
		return OperationData::getAll();
	}

	public function getById(int $id): ?OperationData {
		return OperationData::getById($id);
	}

	public function getDetails(int $operationId): array {
		return OperationDetailData::getAllByOperationId($operationId);
	}

	public function create(array $data): ?int {
		$op = new OperationData();
		$op->description = $data['description'] ?? '';
		$op->device_id = !empty($data['device_id']) ? intval($data['device_id']) : "NULL";
		$op->contact_id = !empty($data['contact_id']) ? intval($data['contact_id']) : "NULL";
		$op->user_id = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : 1;
		$op->status_id = !empty($data['status_id']) ? intval($data['status_id']) : 1;
		$op->start_date = !empty($data['start_date']) ? $data['start_date'] : "NULL";
		$op->end_date = !empty($data['end_date']) ? $data['end_date'] : "NULL";
		$op->total = !empty($data['total']) ? floatval($data['total']) : 0;

		$res = $op->add();
		if ($res && isset($res[1])) {
			return $res[1];
		}
		return null;
	}

	public function update(int $id, array $data): bool {
		$op = OperationData::getById($id);
		if (!$op) return false;

		$op->description = $data['description'] ?? $op->description;
		$op->device_id = !empty($data['device_id']) ? intval($data['device_id']) : "NULL";
		$op->contact_id = !empty($data['contact_id']) ? intval($data['contact_id']) : "NULL";
		$op->status_id = !empty($data['status_id']) ? intval($data['status_id']) : $op->status_id;
		$op->start_date = !empty($data['start_date']) ? $data['start_date'] : $op->start_date;
		$op->end_date = !empty($data['end_date']) ? $data['end_date'] : $op->end_date;

		$op->update();

		$this->recalculateTotal($id);
		return true;
	}

	public function updateStatus(int $id, int $statusId): bool {
		$op = OperationData::getById($id);
		if (!$op) return false;

		$op->status_id = $statusId;
		$op->update();
		return true;
	}

	public function addDetail(int $opId, array $data): bool {
		$detail = new OperationDetailData();
		$detail->operation_id = $opId;
		$detail->service_id = !empty($data['service_id']) ? intval($data['service_id']) : "NULL";
		$detail->quantity = !empty($data['quantity']) ? intval($data['quantity']) : 1;
		$detail->price = !empty($data['price']) ? floatval($data['price']) : 0;
		$detail->discount = !empty($data['discount']) ? floatval($data['discount']) : 0;

		$res = $detail->add();

		$this->recalculateTotal($opId);
		return $res ? true : false;
	}

	public function deleteDetail(int $detailId): bool {
		$detail = OperationDetailData::getById($detailId);
		if ($detail) {
			$opId = $detail->operation_id;
			$detail->del();
			$this->recalculateTotal($opId);
			return true;
		}
		return false;
	}

	public function recalculateTotal(int $opId): void {
		$op = OperationData::getById($opId);
		if (!$op) return;

		$details = OperationDetailData::getAllByOperationId($opId);

		$total = 0;
		foreach ($details as $d) {
			$linePrice = (floatval($d->price) * intval($d->quantity)) - floatval($d->discount);
			$total += max(0, $linePrice);
		}

		$op->total = $total;
		$op->update_total();
	}

	public function delete(int $id): bool {
		$op = OperationData::getById($id);
		if (!$op) return false;

		// Las llaves foraneas impiden borrar la orden mientras tenga pagos o
		// servicios colgando, asi que se limpian primero.
		PaymentData::delAllByOperationId($id);
		foreach (OperationDetailData::getAllByOperationId($id) as $d) {
			$d->del();
		}

		$op->del();
		return true;
	}
}
