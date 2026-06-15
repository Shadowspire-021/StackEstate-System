<?php

namespace App\Http\Controllers;

use App\Services\ExportService;

class ReportController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        return view('reports.index');
    }

    public function clientsCsv(ExportService $export)
    {
        return $export->clientsCsv();
    }

    public function paymentsCsv(ExportService $export)
    {
        return $export->paymentsCsv(request('client_id'));
    }

    public function installmentsCsv(ExportService $export)
    {
        return $export->installmentsCsv(request('client_id'));
    }
}
