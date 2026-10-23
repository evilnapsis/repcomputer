<?php
namespace App\Service;

use DeviceData;
use OperationData;
use ContactData;
use ServiceData;
use UserData;
use PaymentData;
use Executor;

class HomeService {
	public function getDashboardData(): array {
		$allDevices = DeviceData::getAll();
		$allOperations = OperationData::getAll();
		$allClients = ContactData::getAllBy("type", 1);
		$allServices = ServiceData::getAll();
		$allUsers = UserData::getAll();

		$activeOps = 0;
		$completedOps = 0;
		$totalRevenue = 0;

		// Conteo de ordenes pendientes vs en reparacion para la grafica de pie
		$pendingStatusOps = 0;
		$inRepairOps = 0;

		foreach ($allOperations as $op) {
			if ($op->status_id == 5 || $op->status_id == 6) { // Terminado o Entregado
				$completedOps++;
				$totalRevenue += floatval($op->total);
			} else {
				$activeOps++;
				if ($op->status_id == 3) {
					$inRepairOps++;
				} else {
					$pendingStatusOps++;
				}
			}
		}

		// Cobros de los ultimos 30 dias (por dia para la grafica)
		$dailyPayments = [];
		$labels30Days = [];
		for ($i = 29; $i >= 0; $i--) {
			$dateKey = date('Y-m-d', strtotime("-$i days"));
			$labels30Days[] = date('d M', strtotime($dateKey));
			$dailyPayments[$dateKey] = 0.0;
		}

		$startDate = date('Y-m-d', strtotime("-29 days"));
		$endDate = date('Y-m-d');
		$sql = "SELECT DATE(created_at) as p_date, SUM(amount) as day_total 
				FROM payment 
				WHERE DATE(created_at) >= '$startDate' AND DATE(created_at) <= '$endDate' 
				GROUP BY DATE(created_at)";
		$query = Executor::doit($sql);
		if ($query && $query[0]) {
			while ($r = $query[0]->fetch_assoc()) {
				$d = $r['p_date'];
				if (isset($dailyPayments[$d])) {
					$dailyPayments[$d] = round(floatval($r['day_total']), 2);
				}
			}
		}
		$values30Days = array_values($dailyPayments);
		$totalPayments30Days = array_sum($values30Days);

		// Calculo de saldo pendiente de todas las ordenes con servicios realizados
		$paidMap = PaymentData::getTotalsByOperation();
		$totalPendingBalance = 0.0;
		$pendingOrdersWithBalanceCount = 0;

		foreach ($allOperations as $op) {
			$total = floatval($op->total);
			$paid = floatval($paidMap[$op->id] ?? 0);
			$balance = max(0, round($total - $paid, 2));
			if ($balance > 0.01) {
				$totalPendingBalance += $balance;
				$pendingOrdersWithBalanceCount++;
			}
		}

		$recentOperations = array_slice($allOperations, 0, 8);

		return [
			'total_devices' => count($allDevices),
			'total_vehicles' => count($allDevices), // Compatibilidad
			'active_operations' => $activeOps,
			'completed_operations' => $completedOps,
			'total_clients' => count($allClients),
			'total_services' => count($allServices),
			'total_users' => count($allUsers),
			'total_revenue' => $totalRevenue,
			'recent_operations' => $recentOperations,

			// Datos grafica pagos ultimos 30 dias
			'chart_30days_labels' => $labels30Days,
			'chart_30days_values' => $values30Days,
			'total_payments_30_days' => $totalPayments30Days,
			'total_pending_balance' => $totalPendingBalance,
			'pending_orders_count' => $pendingOrdersWithBalanceCount,

			// Datos grafica de pie ordenes
			'orders_pending_count' => $pendingStatusOps,
			'orders_in_repair_count' => $inRepairOps,
			'pie_chart_labels' => ['Pendientes', 'En Reparación'],
			'pie_chart_data' => [$pendingStatusOps, $inRepairOps],
		];
	}
}

