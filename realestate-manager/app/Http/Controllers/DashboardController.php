<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Payment;
use App\Models\Property;
use App\Models\Unit;
use App\Models\Installment;
use Illuminate\Http\Request;
use App\Services\CacheService;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->get('status', 'active');

        // Determine client query depending on selected status scope
        if ($status === 'deleted') {
            $clientsQuery = Client::onlyTrashed();
        } elseif ($status === 'hold') {
            $clientsQuery = Client::where('status', 'inactive');
        } elseif ($status === 'completed') {
            $clientsQuery = Client::where('status', 'completed');
        } else {
            $status = 'active';
            $clientsQuery = Client::where('status', 'active');
        }

        $clientIds = $clientsQuery->pluck('id');
        $clientIdsHash = md5($clientIds->implode(','));

        $dashboardGen = CacheService::getGeneration(CacheService::PREFIX_DASHBOARD);

        // Core financial metrics (cached per status scope)
        $cacheKey = CacheService::key(CacheService::PREFIX_DASHBOARD, 'metrics', $status, $clientIdsHash, (string) $dashboardGen);

        $metrics = CacheService::remember($cacheKey, CacheService::TTL_MEDIUM, function () use ($clientsQuery, $clientIds, $status) {
            $totalClients = $clientsQuery->count();
            $totalDealValue = Property::whereIn('client_id', $clientIds)->sum('total_deal_value');
            $totalReceived = Payment::whereIn('client_id', $clientIds)->sum('amount');
            $remainingBalance = $totalDealValue - $totalReceived;
            $collectionRate = $totalDealValue > 0 ? round(($totalReceived / $totalDealValue) * 100, 1) : 0;

            // Installment status counts
            $installmentStats = Installment::whereIn('client_id', $clientIds)
                ->selectRaw('COUNT(*) as total')
                ->selectRaw('SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as paid', ['paid'])
                ->selectRaw('SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as pending', ['pending'])
                ->selectRaw('SUM(CASE WHEN status = ? AND due_date < ? THEN 1 ELSE 0 END) as overdue', ['pending', now()])
                ->selectRaw('SUM(CASE WHEN status = ? AND due_date < ? THEN amount ELSE 0 END) as overdue_amount', ['pending', now()])
                ->first();

            // Client payment status breakdown
            $fullyPaidClients = 0;
            $partialClients = 0;
            $overdueClients = 0;
            $unpaidClients = 0;

            if ($status !== 'deleted') {
                $activeClients = Client::whereIn('id', $clientIds)->with(['property', 'payments', 'installments'])->get();
                $now = now();
                foreach ($activeClients as $client) {
                    $dealValue = $client->property ? (float) $client->property->total_deal_value : 0;
                    $totalPaid = (float) $client->payments->sum('amount');
                    $remaining = $dealValue - $totalPaid;

                    if ($remaining <= 0) {
                        $fullyPaidClients++;
                    } elseif ($client->installments->contains(function ($inst) use ($now) {
                        return $inst->status === 'pending' && $inst->due_date->isPast();
                    })) {
                        $overdueClients++;
                    } elseif ($totalPaid > 0) {
                        $partialClients++;
                    } else {
                        $unpaidClients++;
                    }
                }
            }

            return [
                'totalClients' => $totalClients,
                'totalDealValue' => $totalDealValue,
                'totalReceived' => $totalReceived,
                'remainingBalance' => $remainingBalance,
                'collectionRate' => $collectionRate,
                'totalInstallments' => (int) ($installmentStats->total ?? 0),
                'paidInstallments' => (int) ($installmentStats->paid ?? 0),
                'pendingInstallments' => (int) ($installmentStats->pending ?? 0),
                'overdueInstallments' => (int) ($installmentStats->overdue ?? 0),
                'overdueAmount' => (float) ($installmentStats->overdue_amount ?? 0),
                'fullyPaidClients' => $fullyPaidClients,
                'partialClients' => $partialClients,
                'overdueClients' => $overdueClients,
                'unpaidClients' => $unpaidClients,
            ];
        });

        // Inventory status (cached globally — plain array, no Eloquent models)
        $unitGen = CacheService::getGeneration(CacheService::PREFIX_UNIT_STATS);
        $unitStats = CacheService::remember(
            CacheService::key(CacheService::PREFIX_UNIT_STATS, (string) $unitGen),
            CacheService::TTL_MEDIUM,
            fn () => ($row = Unit::selectRaw('COUNT(*) as total')
                ->selectRaw('SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as available', ['available'])
                ->selectRaw('SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as booked', ['booked'])
                ->selectRaw('SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as sold', ['sold'])
                ->selectRaw('SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as reserved', ['reserved'])
                ->first()) ? $row->toArray() : ['total' => 0, 'available' => 0, 'booked' => 0, 'sold' => 0, 'reserved' => 0]
        );

        // Monthly revenue data for charts (last 12 months, cached)
        $monthlyKey = CacheService::key(CacheService::PREFIX_DASHBOARD, 'monthly_revenue', $status, $clientIdsHash, (string) $dashboardGen);
        $monthlyRevenue = CacheService::remember($monthlyKey, CacheService::TTL_MEDIUM, function () use ($clientIds) {
            return Payment::whereIn('client_id', $clientIds)
                ->selectRaw('DATE_FORMAT(payment_date, "%Y-%m") as month')
                ->selectRaw('SUM(amount) as total')
                ->where('payment_date', '>=', now()->subMonths(12)->startOfMonth())
                ->groupBy('month')
                ->orderBy('month')
                ->pluck('total', 'month');
        });

        // Recent payments feed scoped to the selected category (short TTL, not cached to ensure freshness)
        $recentPayments = Payment::whereIn('client_id', $clientIds)
            ->with(['client', 'property'])
            ->latest('payment_date')
            ->latest('id')
            ->limit(8)
            ->get();

        return view('dashboard', array_merge($metrics, [
            'totalUnits' => (int) ($unitStats['total'] ?? 0),
            'availableUnits' => (int) ($unitStats['available'] ?? 0),
            'bookedUnits' => (int) ($unitStats['booked'] ?? 0),
            'soldUnits' => (int) ($unitStats['sold'] ?? 0),
            'reservedUnits' => (int) ($unitStats['reserved'] ?? 0),
            'recentPayments' => $recentPayments,
            'monthlyRevenue' => $monthlyRevenue,
            'status' => $status,
        ]));
    }
}
