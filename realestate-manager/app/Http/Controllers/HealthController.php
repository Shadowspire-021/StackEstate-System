<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;

class HealthController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $startTime = microtime(true);
        $dbStatus = 'ok';
        $dbMessage = null;

        try {
            DB::select('SELECT 1');
        } catch (\Exception $e) {
            $dbStatus = 'error';
            $dbMessage = $e->getMessage();
        }

        $responseTime = round((microtime(true) - $startTime) * 1000);

        $statusCode = $dbStatus === 'ok' ? 200 : 500;

        return response()->json([
            'status' => $dbStatus === 'ok' ? 'healthy' : 'degraded',
            'timestamp' => now()->toIso8601String(),
            'services' => [
                'database' => $dbStatus,
                'app' => 'ok',
            ],
            'response_time_ms' => $responseTime,
        ], $statusCode);
    }
}
