<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    /**
     * Display a paginated listing of activity logs with optional filters.
     *
     * Filters: action (create/update/delete/restore), loggable_type, from, to
     */
    public function index(Request $request)
    {
        $query = ActivityLog::with(['user', 'client', 'loggable']);

        if ($request->filled('action')) {
            $query->where('action', $request->input('action'));
        }

        if ($request->filled('loggable_type')) {
            $query->where('loggable_type', $request->input('loggable_type'));
        }

        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->input('from'));
        }

        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->input('to'));
        }

        $logs = $query->orderBy('created_at', 'desc')
            ->paginate(25)
            ->withQueryString();

        return view('activity-logs.index', [
            'logs' => $logs,
            'filters' => $request->only(['action', 'loggable_type', 'from', 'to']),
        ]);
    }

    /**
     * Display a single activity log detail.
     */
    public function show($id)
    {
        $log = ActivityLog::with(['user', 'client', 'loggable'])
            ->findOrFail($id);

        return view('activity-logs.show', [
            'log' => $log,
        ]);
    }
}
