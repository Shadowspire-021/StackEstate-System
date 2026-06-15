<?php

namespace App\Http\Controllers;

use App\Services\ExportService;
use Illuminate\Http\Request;

class ExportController extends Controller
{
    public function __construct(
        private ExportService $exportService
    ) {}

    public function clientsCsv()
    {
        return $this->exportService->clientsCsv();
    }

    public function paymentsCsv(Request $request)
    {
        return $this->exportService->paymentsCsv($request->integer('client_id') ?: null);
    }

    public function installmentsCsv(Request $request)
    {
        return $this->exportService->installmentsCsv($request->integer('client_id') ?: null);
    }
}
