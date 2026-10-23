<?php
namespace App\Service;

use PaymentData;
use OperationData;

/**
 * Cobros de una orden de servicio: abonos, total cobrado y saldo pendiente.
 */
class PaymentService {

	/** Pagos de una orden, del mas antiguo al mas reciente. */
	public function getByOperation(int $opId): array {
		return PaymentData::getAllByOperationId($opId);
	}

	/** Total cobrado en una orden. */
	public function getTotalPaid(int $opId): float {
		return PaymentData::getTotalByOperationId($opId);
	}

	/**
	 * Resumen de cobro de una orden: total, cobrado, saldo y estado.
	 * El estado es 'unpaid' (sin pagos), 'partial' (abonada) o 'paid' (liquidada).
	 */
	public function getSummary(int $opId): array {
		$op = OperationData::getById($opId);
		$total = $op ? floatval($op->total) : 0.0;
		$paid = $this->getTotalPaid($opId);
		$balance = round($total - $paid, 2);

		if ($paid <= 0) {
			$status = 'unpaid';
		} elseif ($balance > 0.001) {
			$status = 'partial';
		} else {
			$status = 'paid';
		}

		return [
			'total' => $total,
			'paid' => $paid,
			'balance' => max(0, $balance),
			// Si se cobro de mas, se reporta aparte en vez de esconderlo.
			'overpaid' => $balance < -0.001 ? abs($balance) : 0.0,
			'status' => $status,
		];
	}

	/**
	 * Registra un abono. Devuelve null si se guardo bien, o el texto del
	 * error cuando el monto no es valido.
	 */
	public function create(int $opId, array $data): ?string {
		$op = OperationData::getById($opId);
		if (!$op) {
			return 'La orden de servicio no existe.';
		}

		$amount = isset($data['amount']) ? round(floatval($data['amount']), 2) : 0;
		if ($amount <= 0) {
			return 'El monto del pago debe ser mayor a cero.';
		}

		$summary = $this->getSummary($opId);
		if ($summary['total'] <= 0) {
			return 'La orden no tiene servicios cobrables todavia.';
		}
		if ($amount > $summary['balance'] + 0.001) {
			return 'El monto excede el saldo pendiente ($' . number_format($summary['balance'], 2) . ').';
		}

		$payment = new PaymentData();
		$payment->operation_id = $opId;
		$payment->amount = $amount;
		$payment->method = $this->normalizeMethod($data['method'] ?? '');
		$payment->reference = mb_substr(trim($data['reference'] ?? ''), 0, 100);
		$payment->notes = mb_substr(trim($data['notes'] ?? ''), 0, 255);
		$payment->user_id = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : null;

		$res = $payment->add();
		return ($res && $res[0]) ? null : 'No se pudo registrar el pago.';
	}

	public function delete(int $paymentId): bool {
		$payment = PaymentData::getById($paymentId);
		if (!$payment) return false;
		$payment->del();
		return true;
	}

	/** Solo se guardan formas de pago conocidas. */
	public function normalizeMethod($method): string {
		$method = trim((string) $method);
		foreach (PaymentData::$methods as $m) {
			if (strcasecmp($m, $method) === 0) {
				return $m;
			}
		}
		return 'Otro';
	}

	/** Cobrado por orden, en una sola consulta, para el listado de ordenes. */
	public function getPaidMap(): array {
		return PaymentData::getTotalsByOperation();
	}

	public function getMethods(): array {
		return PaymentData::$methods;
	}
}
