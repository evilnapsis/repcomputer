<?php
namespace App\Controller;

use App\Service\ReportService;
use ViewEngine;

class ReportController {
	private $reportService;

	public function __construct() {
		$this->reportService = new ReportService();
	}

	public function index() {
		$data = $this->reportService->getReportData($_GET);
		ViewEngine::render('reports/index.html.twig', $data);
	}
}
