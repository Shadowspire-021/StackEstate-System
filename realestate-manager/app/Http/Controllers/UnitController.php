<?php

namespace App\Http\Controllers;

use App\Models\Unit;
use App\Models\Property;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class UnitController extends Controller
{
    /**
     * Display unit listing with DataTable
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = Unit::with('property');

            // Apply status filter
            if ($request->filled('status') && $request->status !== 'all') {
                $query->where('status', $request->status);
            }

            return Datatables::of($query)
                ->addColumn('property_info', function ($unit) {
                    return $unit->property ? $unit->property->property_type . ' - ' . $unit->property->plot_number : 'N/A';
                })
                ->addColumn('status_badge', function ($unit) {
                    $colors = [
                        'available' => 'bg-emerald-50 text-emerald-700 border border-emerald-100',
                        'booked' => 'bg-amber-50 text-amber-700 border border-amber-100',
                        'sold' => 'bg-blue-50 text-blue-700 border border-blue-100',
                        'reserved' => 'bg-purple-50 text-purple-700 border border-purple-100',
                    ];
                    $color = $colors[$unit->status] ?? $colors['available'];
                    return '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase tracking-wider ' . $color . '">' . $unit->status . '</span>';
                })
                ->addColumn('price_formatted', function ($unit) {
                    return $unit->price ? 'Rs. ' . number_format($unit->price) : 'N/A';
                })
                ->addColumn('action', function ($unit) {
                    return '<a href="' . route('units.edit', $unit->id) . '" class="text-indigo-600 hover:text-indigo-500 font-semibold text-sm">Edit</a>';
                })
                ->rawColumns(['status_badge', 'action'])
                ->make(true);
        }

        $stats = [
            'total' => Unit::count(),
            'available' => Unit::where('status', 'available')->count(),
            'booked' => Unit::where('status', 'booked')->count(),
            'sold' => Unit::where('status', 'sold')->count(),
            'reserved' => Unit::where('status', 'reserved')->count(),
        ];

        return view('units.index', compact('stats'));
    }

    /**
     * Show form for creating new unit
     */
    public function create()
    {
        $properties = Property::orderBy('plot_number')->get();
        return view('units.create', compact('properties'));
    }

    /**
     * Store newly created unit
     */
    public function store(Request $request)
    {
        $request->validate([
            'property_id' => 'required|exists:properties,id',
            'unit_number' => 'required|string|max:50',
            'floor_number' => 'nullable|integer',
            'size' => 'nullable|numeric|min:0',
            'price' => 'nullable|integer|min:0',
            'status' => 'required|in:available,booked,sold,reserved',
        ]);

        // Check unique unit number within property
        $exists = Unit::where('property_id', $request->property_id)
            ->where('unit_number', $request->unit_number)
            ->exists();

        if ($exists) {
            return back()->withInput()->with('error', 'Unit number ' . $request->unit_number . ' already exists for this property.');
        }

        Unit::create($request->only([
            'property_id', 'unit_number', 'floor_number', 'size', 'price', 'status',
        ]));

        return redirect()->route('units.index')
            ->with('success', 'Unit created successfully.');
    }

    /**
     * Show form for editing unit
     */
    public function edit(Unit $unit)
    {
        $unit->load('property');
        $properties = Property::orderBy('plot_number')->get();
        return view('units.edit', compact('unit', 'properties'));
    }

    /**
     * Update unit
     */
    public function update(Request $request, Unit $unit)
    {
        $request->validate([
            'property_id' => 'required|exists:properties,id',
            'unit_number' => 'required|string|max:50',
            'floor_number' => 'nullable|integer',
            'size' => 'nullable|numeric|min:0',
            'price' => 'nullable|integer|min:0',
            'status' => 'required|in:available,booked,sold,reserved',
        ]);

        // Check unique unit number within property (excluding current unit)
        $exists = Unit::where('property_id', $request->property_id)
            ->where('unit_number', $request->unit_number)
            ->where('id', '!=', $unit->id)
            ->exists();

        if ($exists) {
            return back()->withInput()->with('error', 'Unit number ' . $request->unit_number . ' already exists for this property.');
        }

        $unit->update($request->only([
            'property_id', 'unit_number', 'floor_number', 'size', 'price', 'status',
        ]));

        return redirect()->route('units.index')
            ->with('success', 'Unit updated successfully.');
    }

    /**
     * Delete unit
     */
    public function destroy(Unit $unit)
    {
        // Check if unit is assigned to any property
        if ($unit->property && $unit->property->unit_id === $unit->id) {
            return back()->with('error', 'Cannot delete unit that is assigned to a client. Remove the assignment first.');
        }

        $unit->delete();

        return back()->with('success', 'Unit deleted successfully.');
    }
}
