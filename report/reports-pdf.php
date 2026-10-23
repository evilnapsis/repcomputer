<?php
/**
 * RepComputer - Reporte General PDF con FPDF
 * @author @evilnapsis
 */

chdir("..");
include "core/autoload.php";
include "core/app/autoload.php";
session_start();

require('fpdf/fpdf.php');

if(!isset($_SESSION["user_id"])){
    die("Acceso denegado. Inicie sesión.");
}

use App\Service\ReportService;
use App\Service\PaymentService;

// Solo los tipos de reporte disponibles en esta edicion.
$opt = $_GET["opt"] ?? "service_orders";
if(!in_array($opt, ReportService::TIPOS, true)){
    $opt = "service_orders";
}

// Las fechas se validan como Y-m-d antes de tocar la consulta.
$sd = ReportService::fecha($_GET["sd"] ?? "", date("Y-m-01"));
$ed = ReportService::fecha($_GET["ed"] ?? "", date("Y-m-t"));

$contact_id = isset($_GET["contact_id"]) && $_GET["contact_id"] != "" ? intval($_GET["contact_id"]) : 0;
$status_id  = isset($_GET["status_id"])  && $_GET["status_id"]  != "" ? intval($_GET["status_id"])  : 0;

$paymentService = new PaymentService();
$method = isset($_GET["method"]) && $_GET["method"] != "" ? $paymentService->normalizeMethod($_GET["method"]) : "";

$operations = array();
$payments = array();
$paid_map = PaymentData::getTotalsByOperation();

if($opt == "payments"){
    $payments = PaymentData::getAllByQ(ReportService::buildPaymentQuery($sd, $ed, $contact_id, $method));
    $report_title = "Reporte de Ingresos / Pagos";
}else{
    $operations = OperationData::getAllByQ(ReportService::buildOperationQuery($sd, $ed, $contact_id, $status_id));
    $report_title = "Reporte de Ordenes de Servicio";
}

$w_name = ConfigurationData::getByShort("workshop_name")->val;
$w_address = ConfigurationData::getByShort("workshop_address")->val;
$w_phone = ConfigurationData::getByShort("workshop_phone")->val;

class PDF extends FPDF {
    public $w_name;
    public $w_address;
    public $w_phone;
    public $report_title;
    public $sd;
    public $ed;
    public $opt;

    function Header() {
        $this->SetFont('Arial', 'B', 16);
        $this->SetTextColor(30, 30, 50);
        $this->Cell(0, 10, mb_convert_encoding($this->w_name, 'ISO-8859-1', 'UTF-8'), 0, 1, 'L');
        $this->SetFont('Arial', '', 9);
        $this->SetTextColor(100, 100, 100);
        $this->Cell(0, 4, mb_convert_encoding($this->w_address . " | Tel: " . $this->w_phone, 'ISO-8859-1', 'UTF-8'), 0, 1, 'L');
        $this->Ln(10);

        $this->SetFont('Arial', 'B', 14);
        $this->SetFillColor(240, 240, 240);
        $this->SetTextColor(0, 0, 0);
        $this->Cell(0, 10, mb_convert_encoding(strtoupper($this->report_title), 'ISO-8859-1', 'UTF-8'), 0, 1, 'C', true);
        $this->SetFont('Arial', '', 9);
        $this->Cell(0, 6, "Rango de fechas: " . date("d/m/Y", strtotime($this->sd)) . " al " . date("d/m/Y", strtotime($this->ed)), 0, 1, 'C');
        $this->Ln(5);

        $this->SetFont('Arial', 'B', 10);
        $this->SetFillColor(41, 128, 185);
        $this->SetTextColor(255, 255, 255);

        if($this->opt == "service_orders") {
            $this->Cell(25, 8, mb_convert_encoding('Folio', 'ISO-8859-1', 'UTF-8'), 1, 0, 'L', true);
            $this->Cell(70, 8, mb_convert_encoding('Equipo', 'ISO-8859-1', 'UTF-8'), 1, 0, 'L', true);
            $this->Cell(55, 8, mb_convert_encoding('Cliente', 'ISO-8859-1', 'UTF-8'), 1, 0, 'L', true);
            $this->Cell(35, 8, mb_convert_encoding('Estado', 'ISO-8859-1', 'UTF-8'), 1, 0, 'C', true);
            $this->Cell(22, 8, mb_convert_encoding('Fecha', 'ISO-8859-1', 'UTF-8'), 1, 0, 'C', true);
            $this->Cell(25, 8, mb_convert_encoding('Total', 'ISO-8859-1', 'UTF-8'), 1, 0, 'R', true);
            $this->Cell(25, 8, mb_convert_encoding('Cobrado', 'ISO-8859-1', 'UTF-8'), 1, 0, 'R', true);
            $this->Cell(20, 8, mb_convert_encoding('Saldo', 'ISO-8859-1', 'UTF-8'), 1, 1, 'R', true);
        } else {
            $this->Cell(32, 8, mb_convert_encoding('Fecha', 'ISO-8859-1', 'UTF-8'), 1, 0, 'C', true);
            $this->Cell(25, 8, mb_convert_encoding('Orden', 'ISO-8859-1', 'UTF-8'), 1, 0, 'L', true);
            $this->Cell(85, 8, mb_convert_encoding('Cliente', 'ISO-8859-1', 'UTF-8'), 1, 0, 'L', true);
            $this->Cell(35, 8, mb_convert_encoding('Forma de Pago', 'ISO-8859-1', 'UTF-8'), 1, 0, 'C', true);
            $this->Cell(60, 8, mb_convert_encoding('Referencia', 'ISO-8859-1', 'UTF-8'), 1, 0, 'L', true);
            $this->Cell(40, 8, mb_convert_encoding('Monto', 'ISO-8859-1', 'UTF-8'), 1, 1, 'R', true);
        }
    }

    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 10, mb_convert_encoding('Página ', 'ISO-8859-1', 'UTF-8') . $this->PageNo() . '/{nb}', 0, 0, 'C');
    }
}

$pdf = new PDF('L', 'mm', 'A4');
$pdf->w_name = $w_name;
$pdf->w_address = $w_address;
$pdf->w_phone = $w_phone;
$pdf->report_title = $report_title;
$pdf->sd = $sd;
$pdf->ed = $ed;
$pdf->opt = $opt;

$pdf->AliasNbPages();
$pdf->AddPage();
$pdf->SetTextColor(50, 50, 50);
$pdf->SetFont('Arial', '', 9);

if($opt == "service_orders") {

    $total_sum = 0;
    $paid_sum = 0;
    $balance_sum = 0;

    foreach($operations as $op) {
        $paid = isset($paid_map[intval($op->id)]) ? $paid_map[intval($op->id)] : 0;
        $balance = max(0, $op->total - $paid);

        $total_sum += $op->total;
        $paid_sum += $paid;
        $balance_sum += $balance;

        $device = $op->getDevice();
        $device_label = $device ? ($device->plate . " - " . $device->brand . " " . $device->model) : 'Sin dispositivo';
        $contact = $op->getContact();
        $client_name = $contact ? ($contact->name . " " . $contact->lastname) : 'Sin cliente';
        $status = $op->getStatus();
        $status_name = $status ? $status->name : 'Pendiente';

        $pdf->Cell(25, 7, mb_convert_encoding("#".str_pad($op->id, 5, "0", STR_PAD_LEFT), 'ISO-8859-1', 'UTF-8'), 1, 0, 'L');
        $pdf->Cell(70, 7, mb_convert_encoding($device_label, 'ISO-8859-1', 'UTF-8'), 1, 0, 'L');
        $pdf->Cell(55, 7, mb_convert_encoding($client_name, 'ISO-8859-1', 'UTF-8'), 1, 0, 'L');
        $pdf->Cell(35, 7, mb_convert_encoding($status_name, 'ISO-8859-1', 'UTF-8'), 1, 0, 'C');
        $pdf->Cell(22, 7, date("d/m/Y", strtotime($op->created_at)), 1, 0, 'C');
        $pdf->Cell(25, 7, "$ " . number_format($op->total, 2), 1, 0, 'R');
        $pdf->Cell(25, 7, "$ " . number_format($paid, 2), 1, 0, 'R');
        $pdf->Cell(20, 7, "$ " . number_format($balance, 2), 1, 1, 'R');
    }

    $pdf->SetFont('Arial', 'B', 10);
    $pdf->SetFillColor(240, 240, 240);
    $pdf->Cell(207, 8, "TOTALES:", 1, 0, 'R', true);
    $pdf->Cell(25, 8, "$ " . number_format($total_sum, 2), 1, 0, 'R', true);
    $pdf->Cell(25, 8, "$ " . number_format($paid_sum, 2), 1, 0, 'R', true);
    $pdf->Cell(20, 8, "$ " . number_format($balance_sum, 2), 1, 1, 'R', true);

} else {

    $total_sum = 0;

    foreach($payments as $pay) {
        $total_sum += $pay->amount;

        $contact = $pay->getContact();
        $client_name = $contact ? ($contact->name . " " . $contact->lastname) : 'Sin cliente';

        $pdf->Cell(32, 7, date("d/m/Y H:i", strtotime($pay->created_at)), 1, 0, 'C');
        $pdf->Cell(25, 7, mb_convert_encoding("#".str_pad($pay->operation_id, 5, "0", STR_PAD_LEFT), 'ISO-8859-1', 'UTF-8'), 1, 0, 'L');
        $pdf->Cell(85, 7, mb_convert_encoding($client_name, 'ISO-8859-1', 'UTF-8'), 1, 0, 'L');
        $pdf->Cell(35, 7, mb_convert_encoding($pay->method, 'ISO-8859-1', 'UTF-8'), 1, 0, 'C');
        $pdf->Cell(60, 7, mb_convert_encoding($pay->reference, 'ISO-8859-1', 'UTF-8'), 1, 0, 'L');
        $pdf->Cell(40, 7, "$ " . number_format($pay->amount, 2), 1, 1, 'R');
    }

    $pdf->SetFont('Arial', 'B', 10);
    $pdf->SetFillColor(240, 240, 240);
    $pdf->Cell(237, 8, "TOTAL COBRADO:", 1, 0, 'R', true);
    $pdf->Cell(40, 8, "$ " . number_format($total_sum, 2), 1, 1, 'R', true);
}

$pdf->Output('I', strtolower(str_replace(' ', '_', $report_title)) . '_' . date('Ymd') . '.pdf');
