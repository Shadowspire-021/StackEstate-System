<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Payment;
use App\Models\Property;
use Illuminate\Http\Request;

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

        // Metrics calculated strictly within the scoped clients list
        $totalClients = $clientsQuery->count();
        $totalDealValue = Property::whereIn('client_id', $clientIds)->sum('total_deal_value');
        $totalReceived = Payment::whereIn('client_id', $clientIds)->sum('amount');
        $remainingBalance = $totalDealValue - $totalReceived;

        // Recent payments feed scoped to the selected category
        $recentPayments = Payment::whereIn('client_id', $clientIds)
            ->with(['client', 'property'])
            ->latest('payment_date')
            ->latest('id')
            ->limit(5)
            ->get();

        return view('dashboard', compact(
            'totalClients',
            'totalDealValue',
            'totalReceived',
            'remainingBalance',
            'recentPayments',
            'status'
        ));
    }
}
