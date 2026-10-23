<?php
/**
 * RepComputer - Reporte PDF
 * Reubicado en carpeta report/
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

if(!isset($_GET["id"])){
    die("ID no especificado.");
}

$op = OperationData::getById($_GET["id"]);
if(!$op){
    die("Operación no encontrada.");
}

$device = $op->device_id ? $op->getDevice() : ($op->vehicle_id ? $op->getVehicle() : null);
$contact = $op->getContact();
$status = $op->getStatus();
$details = OperationDetailData::getAllByOperationId($op->id);

// Config
$w_name = ConfigurationData::getByShort("workshop_name")->val;
$w_address = ConfigurationData::getByShort("workshop_address")->val;
$w_phone = ConfigurationData::getByShort("workshop_phone")->val;
$w_manager = ConfigurationData::getByShort("workshop_manager")->val;

class PDF extends FPDF {
    public $w_name;
    public $w_address;
    public $w_phone;

    function Header() {
        $this->SetFont('Arial', 'B', 16);
        $this->SetTextColor(30, 30, 50);
        $this->Cell(0, 10, mb_convert_encoding($this->w_name, 'ISO-8859-1', 'UTF-8'), 0, 1, 'L');
        $this->SetX(10);
        $this->SetFont('Arial', '', 9);
        $this->SetTextColor(100, 100, 100);
        $this->MultiCell(0, 4, mb_convert_encoding($this->w_address . "\nTel: " . $this->w_phone, 'ISO-8859-1', 'UTF-8'), 0, 'L');
        $this->Ln(10);
    }

    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 10, mb_convert_encoding('Página ', 'ISO-8859-1', 'UTF-8') . $this->PageNo() . '/{nb}', 0, 0, 'C');
    }
}

$pdf = new PDF();
$pdf->w_name = $w_name;
$pdf->w_address = $w_address;
$pdf->w_phone = $w_phone;
$pdf->w_manager = $w_manager;

$pdf->AliasNbPages();
$pdf->AddPage();
$pdf->SetFont('Arial', '', 10);

// Order Title
$pdf->SetFont('Arial', 'B', 14);
$kind_title = "ORDEN DE SERVICIO";
$pdf->Cell(0, 8, mb_convert_encoding($kind_title . " #" . str_pad($op->id, 5, "0", STR_PAD_LEFT), 'ISO-8859-1', 'UTF-8'), 0, 1, 'R');
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(0, 5, mb_convert_encoding("Fecha de Emisión: ", 'ISO-8859-1', 'UTF-8') . date("d/m/Y", strtotime($op->created_at)), 0, 1, 'R');
if($status){
    $pdf->Cell(0, 5, "Estado: " . mb_convert_encoding($status->name, 'ISO-8859-1', 'UTF-8'), 0, 1, 'R');
}
$pdf->Ln(5);

// Client & Device Block
$contact_label = "DATOS DEL CLIENTE";

$x = $pdf->GetX();
$y = $pdf->GetY();

// Barras de seccion: gris claro con texto oscuro.
$pdf->SetFillColor(235, 235, 240);
$pdf->SetTextColor(30, 30, 50);

$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell($device ? 95 : 190, 7, mb_convert_encoding($contact_label, 'ISO-8859-1', 'UTF-8'), 0, 1, 'L', true);
$pdf->SetFont('Arial', '', 10);
$pdf->Cell($device ? 95 : 190, 6, 'Nombre: ' . mb_convert_encoding($contact ? $contact->name . " " . $contact->lastname : "Publico General", 'ISO-8859-1', 'UTF-8'), 0, 1, 'L');
$pdf->Cell($device ? 95 : 190, 6, mb_convert_encoding('Teléfono: ', 'ISO-8859-1', 'UTF-8') . ($contact ? $contact->phone : "-"), 0, 1, 'L');
$pdf->Cell($device ? 95 : 190, 6, 'Email: ' . ($contact ? $contact->email : "-"), 0, 1, 'L');

if($device){
    $pdf->SetXY($x + 100, $y);
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(90, 7, 'DATOS DEL DISPOSITIVO / EQUIPO', 0, 1, 'L', true);
    $pdf->SetFont('Arial', '', 10);
    $pdf->SetX($x + 100);
    $pdf->Cell(90, 6, 'Folio: ' . $device->plate . ' (' . $device->type . ')', 0, 1, 'L');
    $pdf->SetX($x + 100);
    $pdf->Cell(90, 6, 'Marca/Modelo: ' . mb_convert_encoding($device->brand . " " . $device->model, 'ISO-8859-1', 'UTF-8'), 0, 1, 'L');
    $pdf->SetX($x + 100);
    $sn_text = !empty($device->vin) ? $device->vin : (!empty($device->imei) ? "IMEI: " . $device->imei : "N/A");
    $pdf->Cell(90, 6, mb_convert_encoding('Serie/IMEI: ', 'ISO-8859-1', 'UTF-8') . $sn_text, 0, 1, 'L');
}

$pdf->Ln(10);

// Description
if($op->description != ""){
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(0, 7, mb_convert_encoding('NOTAS / DESCRIPCIÓN', 'ISO-8859-1', 'UTF-8'), 0, 1, 'L', true);
    $pdf->SetFont('Arial', '', 10);
    $pdf->MultiCell(0, 6, mb_convert_encoding($op->description, 'ISO-8859-1', 'UTF-8'), 0, 'L');
    $pdf->Ln(5);
}

// Details Table
if(count($details)>0){
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(0, 7, 'DETALLE DE ITEMS', 0, 1, 'L', true);
    $pdf->SetFont('Arial', 'B', 9);
    $pdf->Cell(20, 7, 'Cant.', 1, 0, 'C');
    $pdf->Cell(120, 7, 'Concepto', 1, 0, 'C');
    $pdf->Cell(25, 7, 'Precio', 1, 0, 'C');
    $pdf->Cell(25, 7, 'Subtotal', 1, 1, 'C');
    $pdf->SetFont('Arial', '', 9);
    foreach($details as $d){
        $name = $d->service_id ? $d->getService()->name : "";
        
        $pdf->Cell(20, 6, $d->quantity, 1, 0, 'C');
        $pdf->Cell(120, 6, mb_convert_encoding($name, 'ISO-8859-1', 'UTF-8'), 1, 0, 'L');
        $pdf->Cell(25, 6, '$' . number_format($d->price, 2), 1, 0, 'R');
        $pdf->Cell(25, 6, '$' . number_format($d->price * $d->quantity, 2), 1, 1, 'R');
    }
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(165, 8, 'TOTAL:', 0, 0, 'R');
    $pdf->Cell(25, 8, '$' . number_format($op->total, 2), 0, 1, 'R');
    $pdf->Ln(10);
}

// Pagos aplicados y saldo pendiente
$payments = PaymentData::getAllByOperationId($op->id);
$paid = PaymentData::getTotalByOperationId($op->id);
$balance = max(0, $op->total - $paid);

if($op->total > 0){
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->SetFillColor(235, 235, 240);
    $pdf->SetTextColor(30, 30, 50);
    $pdf->Cell(0, 7, 'PAGOS Y SALDO', 0, 1, 'L', true);
    $pdf->SetTextColor(50, 50, 50);

    if(count($payments) > 0){
        $pdf->SetFont('Arial', 'B', 9);
        $pdf->Cell(35, 7, 'Fecha', 1, 0, 'C');
        $pdf->Cell(45, 7, mb_convert_encoding('Forma de Pago', 'ISO-8859-1', 'UTF-8'), 1, 0, 'C');
        $pdf->Cell(75, 7, 'Referencia', 1, 0, 'L');
        $pdf->Cell(35, 7, 'Monto', 1, 1, 'R');
        $pdf->SetFont('Arial', '', 9);
        foreach($payments as $pay){
            $pdf->Cell(35, 6, date("d/m/Y", strtotime($pay->created_at)), 1, 0, 'C');
            $pdf->Cell(45, 6, mb_convert_encoding($pay->method, 'ISO-8859-1', 'UTF-8'), 1, 0, 'C');
            $pdf->Cell(75, 6, mb_convert_encoding($pay->reference, 'ISO-8859-1', 'UTF-8'), 1, 0, 'L');
            $pdf->Cell(35, 6, '$' . number_format($pay->amount, 2), 1, 1, 'R');
        }
    }else{
        $pdf->SetFont('Arial', 'I', 9);
        $pdf->Cell(0, 6, mb_convert_encoding('Sin pagos registrados.', 'ISO-8859-1', 'UTF-8'), 0, 1, 'L');
    }

    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(155, 7, 'TOTAL COBRADO:', 0, 0, 'R');
    $pdf->Cell(35, 7, '$' . number_format($paid, 2), 0, 1, 'R');
    $pdf->Cell(155, 7, mb_convert_encoding('SALDO PENDIENTE:', 'ISO-8859-1', 'UTF-8'), 0, 0, 'R');
    $pdf->Cell(35, 7, '$' . number_format($balance, 2), 0, 1, 'R');
    $pdf->Ln(6);
}

// Signatures
$pdf->Ln(20);
$pdf->SetFont('Arial', '', 9);
$pdf->Cell(95, 5, '__________________________', 0, 0, 'C');
$pdf->Cell(95, 5, '__________________________', 0, 1, 'C');
$pdf->Cell(95, 5, mb_convert_encoding('Firma del Cliente', 'ISO-8859-1', 'UTF-8'), 0, 0, 'C');
$pdf->Cell(95, 5, mb_convert_encoding($w_manager, 'ISO-8859-1', 'UTF-8'), 0, 1, 'C');
$pdf->SetFont('Arial', '', 8);
$pdf->SetTextColor(120, 120, 120);
$pdf->Cell(95, 4, '', 0, 0, 'C');
$pdf->Cell(95, 4, 'Responsable del Taller', 0, 1, 'C');

$pdf->Output();
?>
