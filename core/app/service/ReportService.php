<?php
namespace App\Service;

use OperationData;
use PaymentData;
use StatusData;
use ContactData;

class ReportService {
	/** Tipos de reporte disponibles en esta edicion. */
	const TIPOS = ['service_orders', 'payments'];

	public function getReportData(array $filters): array {
		$opt = $filters['opt'] ?? 'service_orders';
		if (!in_array($opt, self::TIPOS, true)) {
			$opt = 'service_orders';
		}

		// Las fechas se normalizan a Y-m-d antes de tocar la consulta.
		$sd = self::fecha($filters['sd'] ?? '', date('Y-m-01'));
		$ed = self::fecha($filters['ed'] ?? '', date('Y-m-t'));

		$contactId = !empty($filters['contact_id']) ? intval($filters['contact_id']) : 0;
		$statusId = !empty($filters['status_id']) ? intval($filters['status_id']) : 0;

		$paymentService = new PaymentService();
		$method = !empty($filters['method']) ? $paymentService->normalizeMethod($filters['method']) : '';

		$operations = [];
		$payments = [];

		if ($opt === 'payments') {
			// El reporte de ingresos lee los cobros reales, no el valor de las ordenes.
			$payments = PaymentData::getAllByQ(self::buildPaymentQuery($sd, $ed, $contactId, $method));
		} else {
			$operations = OperationData::getAllByQ(self::buildOperationQuery($sd, $ed, $contactId, $statusId));
		}

		return [
			'opt' => $opt,
			'sd' => $sd,
			'ed' => $ed,
			'status_id' => $filters['status_id'] ?? '',
			'contact_id' => $filters['contact_id'] ?? '',
			'method' => $method,
			'operations' => $operations,
			'payments' => $payments,
			'paid_map' => $paymentService->getPaidMap(),
			'statuses' => StatusData::getAll(),
			'clients' => ContactData::getAllClients(),
			'methods' => $paymentService->getMethods(),
		];
	}

	/** Filtro del reporte de ordenes de servicio. */
	public static function buildOperationQuery(string $sd, string $ed, int $contactId, int $statusId): string {
		$q = "where 1=1";
		if ($statusId > 0) {
			$q .= " and status_id=$statusId";
		}
		$q .= " and date(created_at) >= '$sd'";
		$q .= " and date(created_at) <= '$ed'";
		if ($contactId > 0) {
			$q .= " and contact_id=$contactId";
		}
		return $q . " order by created_at desc";
	}

	/**
	 * Filtro del reporte de ingresos. El cliente se filtra contra la orden a
	 * la que pertenece el pago.
	 */
	public static function buildPaymentQuery(string $sd, string $ed, int $contactId, string $method = ''): string {
		$q = "where 1=1";
		$q .= " and date(created_at) >= '$sd'";
		$q .= " and date(created_at) <= '$ed'";
		if ($contactId > 0) {
			$q .= " and operation_id in (select id from operation where contact_id=$contactId)";
		}
		if ($method !== '') {
			$q .= " and method='" . \Database::getCon()->real_escape_string($method) . "'";
		}
		return $q . " order by created_at desc, id desc";
	}

	/**
	 * Valida que el texto recibido sea una fecha Y-m-d real. Cualquier otra
	 * cosa se descarta y se usa el valor por defecto, de modo que nunca entra
	 * texto arbitrario a la consulta.
	 */
	public static function fecha($valor, string $porDefecto): string {
		if (!is_string($valor) || $valor === '') {
			return $porDefecto;
		}
		$d = \DateTime::createFromFormat('Y-m-d', $valor);
		if ($d && $d->format('Y-m-d') === $valor) {
			return $valor;
		}
		return $porDefecto;
	}
}
