<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\ActivityLog;
use App\Models\Unit;
use App\Http\Traits\ClientFilterTrait;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use App\Services\GoogleDriveService;
use App\Jobs\SyncToGoogleSheetJob;

class ClientController extends Controller
{
    use ClientFilterTrait;

    /**
     * Get available units for a property (AJAX endpoint)
     */
    public function getUnitsByProperty(Request $request)
    {
        $request->validate([
            'property_id' => 'required|exists:properties,id',
        ]);

        $units = Unit::where('property_id', $request->property_id)
            ->where('status', 'available')
            ->select('id', 'unit_number', 'floor_number', 'size', 'price', 'status')
            ->orderBy('unit_number')
            ->get();

        return response()->json($units);
    }

    /**
     * Check unit availability (AJAX endpoint)
     */
    public function checkUnitAvailability(Request $request)
    {
        $request->validate([
            'unit_id' => 'required|exists:units,id',
        ]);

        $unit = Unit::find($request->unit_id);

        return response()->json([
            'available' => $unit->status === 'available',
            'unit' => [
                'id' => $unit->id,
                'unit_number' => e($unit->unit_number),
                'floor_number' => $unit->floor_number,
                'size' => $unit->size,
                'price' => $unit->price,
                'status' => $unit->status,
                'property_id' => $unit->property_id,
            ],
        ]);
    }

    public function profiles(Request $request)
    {
        if ($request->ajax()) {
            // Only list active, non-trashed clients for the viewing directory
            $data = Client::with(['property.unit', 'payments'])->select('clients.*');

            $this->applyClientFilters($data, $request);

            return DataTables::of($data)
                ->addColumn('action', function($row){
                    return '<a href="'.route('clients.show', $row->id).'" class="inline-flex items-center px-3 py-1 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 font-extrabold rounded-lg text-[10px] uppercase tracking-wider transition border border-indigo-100/30">View Profile</a>';
                })
                ->addColumn('property_type', function($row){
                    return $row->property ? $row->property->property_type : 'N/A';
                })
                ->addColumn('plot_number', function($row){
                    if (!$row->property) return 'N/A';
                    $block = $row->property->block_name;
                    return $row->property->plot_number . ($block ? ' - ' . $block : '');
                })
                ->addColumn('unit_number', function($row){
                    if (!$row->property || !$row->property->unit_id) return '<span class="text-gray-400 font-semibold">N/A</span>';
                    $unit = $row->property->unit;
                    if (!$unit) return '<span class="text-gray-400 font-semibold">N/A</span>';
                    $statusColors = [
                        'available' => 'bg-emerald-100 text-emerald-700',
                        'booked' => 'bg-amber-100 text-amber-700',
                        'sold' => 'bg-blue-100 text-blue-700',
                        'reserved' => 'bg-purple-100 text-purple-700',
                    ];
                    $colorClass = $statusColors[$unit->status] ?? 'bg-gray-100 text-gray-700';
                    return '<span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold ' . $colorClass . '">' . e($unit->unit_number) . '</span>';
                })
                ->addColumn('total_deal_value', function($row){
                    return $row->property ? 'Rs. ' . number_format($row->property->total_deal_value) : 'N/A';
                })
                ->addColumn('remaining_balance', function($row){
                    if (!$row->property) return '<span class="text-gray-400 font-semibold">N/A</span>';
                    $totalDeal = $row->property->total_deal_value;
                    $totalPaid = (float) $row->payments->sum('amount');
                    $rem = $totalDeal - $totalPaid;
                    
                    $color = $rem <= 0 ? 'text-emerald-600' : 'text-amber-600';
                    return '<span class="font-bold ' . $color . '">Rs. ' . number_format($rem) . '</span>';
                })
                ->addColumn('status_badge', function($row){
                    if ($row->trashed()) {
                        return '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-red-50 text-red-700 border border-red-100 uppercase tracking-wider">Deleted</span>';
                    }
                    $colors = [
                        'active' => 'bg-green-50 text-green-700 border border-green-100',
                        'inactive' => 'bg-yellow-50 text-yellow-700 border border-yellow-100',
                        'completed' => 'bg-indigo-50 text-indigo-700 border border-indigo-100',
                    ];
                    $color = $colors[$row->status] ?? 'bg-gray-50 text-gray-700 border border-gray-100';
                    return '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold uppercase tracking-wider ' . $color . '">' . e($row->status) . '</span>';
                })
                ->rawColumns(['action', 'status_badge', 'remaining_balance', 'unit_number'])
                ->make(true);
        }

        return view('clients.profiles');
    }

    public function index(Request $request)
    {
        if ($request->ajax()) {
            // Include soft-deleted clients on listing
            $data = Client::withTrashed()->with(['property.unit', 'payments'])->select('clients.*');

            $this->applyClientFilters($data, $request);

            return DataTables::of($data)
                ->addColumn('action', function($row){
                    // Ellipsis 3-dots Toggle Button with onclick trigger
                    $dropdown = '<div class="relative inline-block text-left">';
                    $dropdown .= '<button type="button" onclick="toggleDropdown(event, this)" class="inline-flex justify-center items-center h-8 w-8 rounded-full bg-gray-50 hover:bg-gray-100 border border-gray-200 text-gray-500 hover:text-gray-700 transition cursor-pointer shadow-sm focus:outline-none">';
                    $dropdown .= '<svg class="w-5 h-5 pointer-events-none" fill="currentColor" viewBox="0 0 20 20"><path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z"></path></svg>';
                    $dropdown .= '</button>';
                    
                    $dropdown .= '<div class="dropdown-menu absolute right-0 mt-1 w-44 rounded-xl bg-white border border-gray-100 shadow-xl hidden z-[9999] p-1.5 space-y-1">';
                    
                    if ($row->trashed()) {
                        // Restore Option - only for users who can delete clients
                        if (auth()->user()->can('delete clients')) {
                            $dropdown .= '<form action="'.route('clients.restore', $row->id).'" method="POST" class="restore-form w-full">';
                            $dropdown .= csrf_field();
                            $dropdown .= '<button type="submit" class="flex w-full items-center px-3 py-2 text-left text-xs font-extrabold text-emerald-700 bg-emerald-50 hover:bg-emerald-100 rounded-lg transition uppercase tracking-wider cursor-pointer">';
                            $dropdown .= '<svg class="w-3.5 h-3.5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 1121.21 8H18.2M9 11l3-3 3 3m-3-3v12"></path></svg>';
                            $dropdown .= 'Restore';
                            $dropdown .= '</button>';
                            $dropdown .= '</form>';
                        }
                    } else {
                        // Log Payment Option (Quick launch)
                        $dropdown .= '<a href="'.route('payments.create', ['client_id' => $row->id]).'" class="flex items-center px-3 py-2 text-xs font-extrabold text-emerald-700 bg-emerald-50 hover:bg-emerald-100 rounded-lg transition uppercase tracking-wider">';
                        $dropdown .= '<svg class="w-3.5 h-3.5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>';
                        $dropdown .= 'Log Payment';
                        $dropdown .= '</a>';

                        // View Profile Option
                        $dropdown .= '<a href="'.route('clients.show', $row->id).'" class="flex items-center px-3 py-2 text-xs font-extrabold text-indigo-700 bg-indigo-50 hover:bg-indigo-100 rounded-lg transition uppercase tracking-wider">';
                        $dropdown .= '<svg class="w-3.5 h-3.5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>';
                        $dropdown .= 'View Profile';
                        $dropdown .= '</a>';
                        
                        // Edit Client Option
                        $dropdown .= '<a href="'.route('clients.edit', $row->id).'" class="flex items-center px-3 py-2 text-xs font-extrabold text-gray-700 bg-gray-50 hover:bg-gray-100 rounded-lg transition uppercase tracking-wider">';
                        $dropdown .= '<svg class="w-3.5 h-3.5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>';
                        $dropdown .= 'Edit Client';
                        $dropdown .= '</a>';
                        
                        // Delete Option - only for users who can delete clients
                        if (auth()->user()->can('delete clients')) {
                            $dropdown .= '<form action="'.route('clients.destroy', $row->id).'" method="POST" class="delete-form w-full">';
                            $dropdown .= csrf_field();
                            $dropdown .= method_field('DELETE');
                            $dropdown .= '<button type="submit" class="flex w-full items-center px-3 py-2 text-left text-xs font-extrabold text-rose-700 bg-rose-50 hover:bg-rose-100 rounded-lg transition uppercase tracking-wider cursor-pointer">';
                            $dropdown .= '<svg class="w-3.5 h-3.5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>';
                            $dropdown .= 'Delete';
                            $dropdown .= '</button>';
                            $dropdown .= '</form>';
                        }
                    }
                    
                    $dropdown .= '</div></div>';
                    return $dropdown;
                })
                ->addColumn('property_type', function($row){
                    return $row->property ? $row->property->property_type : 'N/A';
                })
                ->addColumn('plot_number', function($row){
                    if (!$row->property) return 'N/A';
                    $block = $row->property->block_name;
                    return $row->property->plot_number . ($block ? ' - ' . $block : '');
                })
                ->addColumn('unit_number', function($row){
                    if (!$row->property || !$row->property->unit_id) return '<span class="text-gray-400 font-semibold">N/A</span>';
                    $unit = $row->property->unit;
                    if (!$unit) return '<span class="text-gray-400 font-semibold">N/A</span>';
                    $statusColors = [
                        'available' => 'bg-emerald-100 text-emerald-700',
                        'booked' => 'bg-amber-100 text-amber-700',
                        'sold' => 'bg-blue-100 text-blue-700',
                        'reserved' => 'bg-purple-100 text-purple-700',
                    ];
                    $colorClass = $statusColors[$unit->status] ?? 'bg-gray-100 text-gray-700';
                    return '<span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold ' . $colorClass . '">' . e($unit->unit_number) . '</span>';
                })
                ->addColumn('total_deal_value', function($row){
                    return $row->property ? 'Rs. ' . number_format($row->property->total_deal_value) : 'N/A';
                })
                ->addColumn('remaining_balance', function($row){
                    if (!$row->property) return '<span class="text-gray-400 font-semibold">N/A</span>';
                    $totalDeal = $row->property->total_deal_value;
                    $totalPaid = (float) $row->payments->sum('amount');
                    $rem = $totalDeal - $totalPaid;
                    
                    $color = $rem <= 0 ? 'text-emerald-600' : 'text-amber-600';
                    return '<span class="font-bold ' . $color . '">Rs. ' . number_format($rem) . '</span>';
                })
                ->addColumn('status_badge', function($row){
                    if ($row->trashed()) {
                        return '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-red-50 text-red-700 border border-red-100 uppercase tracking-wider">Deleted</span>';
                    }
                    $colors = [
                        'active' => 'bg-green-50 text-green-700 border border-green-100',
                        'inactive' => 'bg-yellow-50 text-yellow-700 border border-yellow-100',
                        'completed' => 'bg-indigo-50 text-indigo-700 border border-indigo-100',
                    ];
                    $color = $colors[$row->status] ?? 'bg-gray-50 text-gray-700 border border-gray-100';
                    return '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold uppercase tracking-wider ' . $color . '">' . e($row->status) . '</span>';
                })
                ->rawColumns(['action', 'status_badge', 'remaining_balance', 'unit_number'])
                ->make(true);
        }

        return view('clients.index');
    }

    public function create()
    {
        $templates = \App\Models\InstallmentPlanTemplate::orderBy('name')->get();
        return view('clients.create', compact('templates'));
    }

    public function store(Request $request, GoogleDriveService $driveService)
    {
        $request->validate([
            'salutation' => 'required|in:Mr.,Mrs.,Ms.,Dr.,Eng.',
            'full_name' => 'required|string|max:150',
            'father_husband_salutation' => 'required|in:S/O,D/O,W/O',
            'father_husband_name' => 'required|string|max:150',
            'cnic' => 'required|string|max:15|unique:clients,cnic',
            'phone' => 'required|string|max:20',
            'residential_address' => 'required|string',
            'vendor_type' => 'required|in:default,custom',
            'vendor_name' => 'required_if:vendor_type,custom|nullable|string|max:150',
            'vendor_cnic' => 'required_if:vendor_type,custom|nullable|string|max:20',
            'property_type' => 'required|in:Residential Plot,Commercial Plot,House,Flat,Shop',
            'plot_number' => 'required|string|max:50',
            'block_name' => 'required|string|max:50',
            'location' => 'required|string|max:100',
            'size_sqyards' => 'required|numeric|min:0.1',
            'total_deal_value' => 'required|numeric|min:1',
            'agreement_date' => 'required|date',
            'notes' => 'nullable|string',

            // Unit selection (optional for backward compatibility)
            'unit_id' => 'nullable|exists:units,id',

            // Template selection
            'template_id' => 'nullable|exists:installment_plan_templates,id',

            // Installment Calculator additions
            'apply_installment_plan' => 'nullable|boolean',
            'advance_amount' => 'nullable|numeric|min:0',
            'advance_payment_method' => 'required_if:apply_installment_plan,1|nullable|in:CASH,CHEQUE,BANK_TRANSFER,ONLINE',
            'advance_bank_name' => 'nullable|string|max:100',
            'advance_cheque_number' => 'nullable|string|max:100',
            'installment_count' => 'required_if:apply_installment_plan,1|nullable|integer|min:1',
            'installment_interval' => 'required_if:apply_installment_plan,1|nullable|in:monthly,quarterly',
            'installment_start_date' => 'required_if:apply_installment_plan,1|nullable|date',
            'generate_advance_receipt' => 'nullable|boolean',
        ]);

        $clientId = \App\Helpers\ClientIdHelper::generate();

        $folderId = null;
        try {
            $folderName = date('Y') . '_' . str_replace(' ', '_', $request->full_name) . '_' . str_replace('-', '', $request->cnic);
            $folderId = $driveService->createFolder($folderName);
            if ($folderId) {
                $driveService->createClientFolderStructure($folderId);
            }
        } catch (\Exception $e) {
            \Log::error('Client Onboarding - Google Drive folder creation failed: ' . $e->getMessage());
        }

        \DB::beginTransaction();
        try {
            $client = Client::create([
                'client_id' => $clientId,
                'salutation' => $request->salutation,
                'full_name' => $request->full_name,
                'father_husband_salutation' => $request->father_husband_salutation,
                'father_husband_name' => $request->father_husband_name,
                'cnic' => $request->cnic,
                'phone' => $request->phone,
                'residential_address' => $request->residential_address,
                'vendor_type' => $request->vendor_type,
                'vendor_name' => $request->vendor_type === 'custom' ? $request->vendor_name : null,
                'vendor_cnic' => $request->vendor_type === 'custom' ? $request->vendor_cnic : null,
                'google_drive_folder_id' => $folderId,
                'status' => 'active',
                'created_by' => auth()->id(),
            ]);

            $property = $client->property()->create([
                'property_type' => $request->property_type,
                'plot_number' => $request->plot_number,
                'block_name' => $request->block_name,
                'location' => $request->location,
                'size_sqyards' => $request->size_sqyards,
                'total_deal_value' => $request->total_deal_value,
                'agreement_date' => $request->agreement_date,
                'notes' => $request->notes,
                'template_id' => $request->template_id,
            ]);

            // Handle unit assignment if provided (with row-level lock to prevent double-booking)
            if ($request->filled('unit_id')) {
                $unit = \App\Models\Unit::where('id', $request->unit_id)
                    ->where('property_id', $property->id) // Ensure unit belongs to property
                    ->lockForUpdate()
                    ->first();
                
                if ($unit && $unit->status === 'available') {
                    $property->update(['unit_id' => $unit->id]);
                    $unit->update(['status' => 'booked']);
                } elseif ($unit) {
                    \DB::rollBack();
                    return back()->withInput()->with('error', 'Unit ' . e($unit->unit_number) . ' is no longer available. It has been assigned to another client. Please select a different unit.');
                } else {
                    \DB::rollBack();
                    return back()->withInput()->with('error', 'Selected unit is not available or does not belong to this property.');
                }
            }

            \App\Services\ActivityLogger::logCreate($client);

            $receipt = null;
            if ($request->filled('apply_installment_plan') && $request->apply_installment_plan == 1) {
                $advanceAmount = floatval($request->advance_amount ?? 0);
                $totalDeal = floatval($request->total_deal_value);

                if ($advanceAmount > 0) {
                    $advancePayment = \App\Models\Payment::create([
                        'client_id' => $client->id,
                        'property_id' => $property->id,
                        'payment_number' => 1,
                        'amount' => $advanceAmount,
                        'payment_method' => $request->advance_payment_method,
                        'particulars' => 'Token / Advance Payment',
                        'bank_name' => $request->advance_bank_name,
                        'cheque_number' => $request->advance_cheque_number,
                        'payment_date' => $request->agreement_date,
                        'created_by' => auth()->id()
                    ]);
                    \App\Services\ActivityLogger::logCreate($advancePayment);

                    if ($request->filled('generate_advance_receipt') && $request->generate_advance_receipt == 1) {
                        $receiptNumber = 'RCP-' . str_replace('-', '', $client->client_id) . '-001';
                        $docxFilename = $receiptNumber . '_' . date('Ymd') . '.docx';

                        $receipt = \App\Models\Receipt::create([
                            'receipt_number' => $receiptNumber,
                            'client_id' => $client->id,
                            'property_id' => $property->id,
                            'total_amount_this_receipt' => $advanceAmount,
                            'total_received_to_date' => $advanceAmount,
                            'remaining_balance' => $totalDeal - $advanceAmount,
                            'receipt_date' => $request->agreement_date,
                            'docx_filename' => $docxFilename,
                            'generated_by' => auth()->id()
                        ]);

                        $advancePayment->receipt_id = $receipt->id;
                        $advancePayment->save();

                        $receiptService = new \App\Services\ReceiptService();
                        $receiptService->generate($receipt);
                    }
                }

                $remainingBalance = $totalDeal - $advanceAmount;

                if ($remainingBalance > 0) {
                    $startDate = new \DateTime($request->installment_start_date);

                    // Use template-based generation if template is linked to property
                    if ($property->template_id) {
                        $property->load('template');
                        $template = $property->template;
                        if ($template) {
                            $installments = $template->generateInstallments($remainingBalance);
                            foreach ($installments as $i => $inst) {
                                $dueDate = clone $startDate;
                                $dueDate->modify("+{$inst['due_months']} month");

                                \App\Models\Installment::create([
                                    'client_id' => $client->id,
                                    'property_id' => $property->id,
                                    'installment_number' => $i + 1,
                                    'amount' => $inst['amount'],
                                    'original_amount' => $inst['amount'],
                                    'due_date' => $dueDate->format('Y-m-d'),
                                    'status' => 'pending'
                                ]);
                            }
                        }
                    } else {
                        // Manual generation (existing logic)
                        $installmentCount = intval($request->installment_count);
                        if ($installmentCount > 0) {
                            $baseInstallmentAmount = floor($remainingBalance / $installmentCount);
                            $remainder = $remainingBalance % $installmentCount;

                            for ($i = 0; $i < $installmentCount; $i++) {
                                $instAmount = $baseInstallmentAmount;
                                if ($i === $installmentCount - 1) {
                                    $instAmount += $remainder;
                                }

                                $dueDate = clone $startDate;
                                if ($request->installment_interval === 'monthly') {
                                    $dueDate->modify("+{$i} month");
                                } else {
                                    $intervalMonths = $i * 3;
                                    $dueDate->modify("+{$intervalMonths} month");
                                }

                                \App\Models\Installment::create([
                                    'client_id' => $client->id,
                                    'property_id' => $property->id,
                                    'installment_number' => $i + 1,
                                    'amount' => $instAmount,
                                    'original_amount' => $instAmount,
                                    'due_date' => $dueDate->format('Y-m-d'),
                                    'status' => 'pending'
                                ]);
                            }
                        }
                    }
                }
            }

            \DB::commit();

            // Dispatch notification event
            \App\Events\ClientCreated::dispatch($client);

            if ($receipt) {
                if ($client->google_drive_folder_id) {
                    \App\Jobs\UploadToDriveJob::dispatch($receipt);
                } else {
                    \App\Services\SyncManager::trigger($client);
                }
            } else {
                \App\Services\SyncManager::trigger($client);
            }

            return redirect()->route('clients.show', $client->id)
                ->with('success', 'Client onboarded successfully. Folder created in Google Drive.');

        } catch (\Exception $e) {
            \DB::rollBack();
            \Log::error('Client Onboarding failed: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Failed to onboard client. Please try again.');
        }
    }

    public function show($id)
    {
        // Support loading soft-deleted clients to check system history
        $client = Client::withTrashed()->with(['property.unit', 'payments.creator', 'payments.receipt', 'receipts' => function($q) {
            $q->latest('receipt_date')->latest('id');
        }, 'documents' => function($q) {
            $q->latest('created_at');
        }, 'installments' => function($q) {
            $q->orderBy('installment_number');
        }])->findOrFail($id);

        // Fetch activity logs for this client
        $activityLogs = ActivityLog::with('user')
            ->where('client_id', $client->id)
            ->latest()
            ->get();

        return view('clients.show', compact('client', 'activityLogs'));
    }

    public function edit($id)
    {
        $client = Client::withTrashed()->with('property')->findOrFail($id);
        $availableUnits = \App\Models\Unit::with('property')->where('status', 'available')->get();
        return view('clients.edit', compact('client', 'availableUnits'));
    }

    public function update(Request $request, $id)
    {
        $client = Client::withTrashed()->findOrFail($id);

        $request->validate([
            'salutation' => 'required|in:Mr.,Mrs.,Ms.,Dr.,Eng.',
            'full_name' => 'required|string|max:150',
            'father_husband_salutation' => 'required|in:S/O,D/O,W/O',
            'father_husband_name' => 'required|string|max:150',
            'cnic' => 'required|string|max:15|unique:clients,cnic,' . $client->id,
            'phone' => 'required|string|max:20',
            'residential_address' => 'required|string',
            'vendor_type' => 'required|in:default,custom',
            'vendor_name' => 'required_if:vendor_type,custom|nullable|string|max:150',
            'vendor_cnic' => 'required_if:vendor_type,custom|nullable|string|max:20',
            'status' => 'required|in:active,inactive,completed',
            'property_type' => 'required|in:Residential Plot,Commercial Plot,House,Flat,Shop',
            'plot_number' => 'required|string|max:50',
            'block_name' => 'required|string|max:50',
            'location' => 'required|string|max:100',
            'size_sqyards' => 'required|numeric|min:0.1',
            'total_deal_value' => 'required|numeric|min:1',
            'agreement_date' => 'required|date',
            'notes' => 'nullable|string',
            'unit_id' => 'nullable|exists:units,id',
        ]);

        $oldClientValues = $client->toArray();
        $oldPropertyValues = $client->property ? $client->property->toArray() : [];
        $oldUnitId = $client->property ? $client->property->unit_id : null;

        \DB::beginTransaction();
        try {
            $client->update([
                'salutation' => $request->salutation,
                'full_name' => $request->full_name,
                'father_husband_salutation' => $request->father_husband_salutation,
                'father_husband_name' => $request->father_husband_name,
                'cnic' => $request->cnic,
                'phone' => $request->phone,
                'residential_address' => $request->residential_address,
                'vendor_type' => $request->vendor_type,
                'vendor_name' => $request->vendor_type === 'custom' ? $request->vendor_name : null,
                'vendor_cnic' => $request->vendor_type === 'custom' ? $request->vendor_cnic : null,
                'status' => $request->status,
            ]);

            $client->property()->update([
                'property_type' => $request->property_type,
                'plot_number' => $request->plot_number,
                'block_name' => $request->block_name,
                'location' => $request->location,
                'size_sqyards' => $request->size_sqyards,
                'total_deal_value' => $request->total_deal_value,
                'agreement_date' => $request->agreement_date,
                'notes' => $request->notes,
                'unit_id' => $request->unit_id,
            ]);

            $newUnitId = $request->unit_id;

            if ($oldUnitId && $oldUnitId != $newUnitId) {
                $oldUnit = \App\Models\Unit::where('id', $oldUnitId)->lockForUpdate()->orderBy('id')->first();
                if ($oldUnit) {
                    $oldUnit->update(['status' => 'available']);
                }
            }

            if ($newUnitId && $newUnitId != $oldUnitId) {
                $newUnit = \App\Models\Unit::where('id', $newUnitId)
                    ->where('property_id', $client->property->id) // Ensure unit belongs to property
                    ->lockForUpdate()
                    ->orderBy('id')
                    ->first();
                
                if ($newUnit && $newUnit->status === 'available') {
                    $newUnit->update(['status' => 'booked']);
                } elseif ($newUnit) {
                    \DB::rollBack();
                    return back()->withInput()->with('error', 'Unit ' . $newUnit->unit_number . ' is no longer available. It has been assigned to another client. Please select a different unit.');
                } else {
                    \DB::rollBack();
                    return back()->withInput()->with('error', 'Selected unit is not available or does not belong to this property.');
                }
            }

            $client->refresh();
            \App\Services\ActivityLogger::logUpdate($client, $oldClientValues);
            if ($client->property) {
                \App\Services\ActivityLogger::logUpdate($client->property, $oldPropertyValues, $client->id);
            }

            \DB::commit();

            \App\Services\SyncManager::trigger($client);

            return redirect()->route('clients.show', $client->id)
                ->with('success', 'Client details updated successfully.');

        } catch (\Exception $e) {
            \DB::rollBack();
            \Log::error('Failed to update client: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Failed to update client. Please try again.');
        }
    }

    public function destroy($id)
    {
        $client = Client::findOrFail($id);

        \DB::beginTransaction();
        try {
            if ($client->property && $client->property->unit_id) {
                $unit = \App\Models\Unit::where('id', $client->property->unit_id)->lockForUpdate()->first();
                if ($unit && in_array($unit->status, ['booked', 'reserved'])) {
                    $unit->update(['status' => 'available']);
                }
            }

            \App\Services\ActivityLogger::logDelete($client);
            $client->delete();

            \DB::commit();

            $client->status = 'deleted';
            \App\Services\SyncManager::trigger($client);

            return redirect()->route('clients.index')
                ->with('success', 'Client deleted successfully (Soft Deleted).');
        } catch (\Exception $e) {
            \DB::rollBack();
            \Log::error('Failed to delete client: ' . $e->getMessage());
            return back()->with('error', 'Failed to delete client. Please try again.');
        }
    }

    public function restore($id)
    {
        $client = Client::withTrashed()->findOrFail($id);

        \DB::beginTransaction();
        try {
            $restoreMessage = 'Client successfully restored!';
            $unitConflict = false;

            if ($client->property && $client->property->unit_id) {
                $unit = \App\Models\Unit::where('id', $client->property->unit_id)->lockForUpdate()->first();
                if ($unit && $unit->status === 'available') {
                    $unit->update(['status' => 'booked']);
                    $restoreMessage = 'Client successfully restored. Unit ' . e($unit->unit_number) . ' has been re-assigned.';
                } elseif ($unit) {
                    $unitConflict = true;
                    $client->property->update(['unit_id' => null]);
                    $restoreMessage = 'Client restored. Unit ' . e($unit->unit_number) . ' was assigned to another client and has been unlinked. Please assign a new unit.';
                } else {
                    $client->property->update(['unit_id' => null]);
                    $restoreMessage = 'Client restored. Previously assigned unit no longer exists. Please assign a new unit.';
                }
            }

            $client->restore();
            $client->status = 'active';
            $client->save();
            \App\Services\ActivityLogger::logRestore($client);

            \DB::commit();

            \App\Services\SyncManager::trigger($client);

            return redirect()->route('clients.show', $client->id)
                ->with($unitConflict ? 'warning' : 'success', $restoreMessage);
        } catch (\Exception $e) {
            \DB::rollBack();
            \Log::error('Failed to restore client: ' . $e->getMessage());
            return back()->with('error', 'Failed to restore client. Please try again.');
        }
    }

    public function rollback($id)
    {
        $log = ActivityLog::findOrFail($id);

        if (!$log->old_values) {
            return back()->with('error', 'No historical values available for this log entry.');
        }

        $modelClass = $log->loggable_type;
        $modelId = $log->loggable_id;

        \DB::beginTransaction();
        try {
            $model = $modelClass::withTrashed()->find($modelId);
            if (!$model) {
                $model = new $modelClass();
            }

            $oldValues = $model->toArray();
            
            // Revert changes but protect sensitive fields
            $protectedFields = ['id', 'created_at', 'updated_at', 'created_by', 'client_id', 'google_sheet_row', 'google_drive_folder_id'];
            $allowedValues = array_filter($log->old_values, function($key) use ($protectedFields) {
                return !in_array($key, $protectedFields);
            }, ARRAY_FILTER_USE_KEY);
            $model->fill($allowedValues);
            
            if ($model instanceof Client && $model->trashed()) {
                $model->restore();
            }
            
            $model->save();

            \App\Services\ActivityLogger::log('restore', $model, $oldValues, $model->toArray(), $log->client_id);

            \DB::commit();

            // Refresh client for sync
            $client = Client::withTrashed()->find($log->client_id);
            if ($client) {
                \App\Services\SyncManager::trigger($client);
            }

            return back()->with('success', 'Successfully rolled back to the selected historical version!');
        } catch (\Exception $e) {
            \DB::rollBack();
            \Log::error('Rollback failed: ' . $e->getMessage());
            return back()->with('error', 'Failed to roll back. Please try again.');
        }
    }

    public function storeInstallments(Request $request, $id)
    {
        $client = Client::findOrFail($id);
        $property = $client->property;

        if (!$property) {
            return back()->with('error', 'The client must have an active property record to setup installments.');
        }

        $request->validate([
            'installment_count' => 'required|integer|min:1',
            'installment_interval' => 'required|in:monthly,quarterly',
            'installment_start_date' => 'required|date',
        ]);

        $totalDeal = floatval($property->total_deal_value);
        $totalPaid = floatval($client->payments()->sum('amount'));
        $remainingBalance = $totalDeal - $totalPaid;

        if ($remainingBalance <= 0) {
            return back()->with('error', 'The remaining balance is 0 or less. No installments needed.');
        }

        $installmentCount = intval($request->installment_count);

        \DB::beginTransaction();
        try {
            // Delete all existing pending installments for restructuring
            $client->installments()->where('status', 'pending')->delete();

            // Find the next installment number (start after any existing paid installments)
            $paidCount = $client->installments()->where('status', 'paid')->count();
            
            $baseInstallmentAmount = floor($remainingBalance / $installmentCount);
            $remainder = $remainingBalance % $installmentCount;

            $startDate = new \DateTime($request->installment_start_date);

            for ($i = 0; $i < $installmentCount; $i++) {
                $instAmount = $baseInstallmentAmount;
                if ($i === $installmentCount - 1) {
                    $instAmount += $remainder;
                }

                $dueDate = clone $startDate;
                if ($request->installment_interval === 'monthly') {
                    $dueDate->modify("+{$i} month");
                } else {
                    $intervalMonths = $i * 3;
                    $dueDate->modify("+{$intervalMonths} month");
                }

                \App\Models\Installment::create([
                    'client_id' => $client->id,
                    'property_id' => $property->id,
                    'installment_number' => $paidCount + $i + 1,
                    'amount' => $instAmount,
                    'original_amount' => $instAmount,
                    'due_date' => $dueDate->format('Y-m-d'),
                    'status' => 'pending'
                ]);
            }

            \DB::commit();

            return redirect()->route('clients.show', $client->id)
                ->with('success', 'Installment plan generated successfully.');

        } catch (\Exception $e) {
            \DB::rollBack();
            \Log::error('Restructuring installments failed: ' . $e->getMessage());
            return back()->with('error', 'Failed to generate installment plan. Please try again.');
        }
    }

    public function clearInstallments($id)
    {
        $client = Client::findOrFail($id);
        
        \DB::beginTransaction();
        try {
            $client->installments()->where('status', 'pending')->delete();
            \DB::commit();
            return redirect()->route('clients.show', $client->id)
                ->with('success', 'All pending installments cleared successfully.');
        } catch (\Exception $e) {
            \DB::rollBack();
            \Log::error('Failed to clear installments: ' . $e->getMessage());
            return back()->with('error', 'Failed to clear installments. Please try again.');
        }
    }

    public function destroyInstallment($clientId, $installmentId)
    {
        $client = Client::findOrFail($clientId);
        $installment = $client->installments()->findOrFail($installmentId);
        
        \DB::beginTransaction();
        try {
            $installment->delete();
            \DB::commit();
            return redirect()->route('clients.show', $client->id)
                ->with('success', 'Installment deleted successfully.');
        } catch (\Exception $e) {
            \DB::rollBack();
            \Log::error('Failed to delete installment: ' . $e->getMessage());
            return back()->with('error', 'Failed to delete installment. Please try again.');
        }
    }

    public function updateLateFee(Request $request, $clientId, $installmentId)
    {
        $request->validate([
            'late_fee_amount' => 'required|numeric|min:0',
        ]);

        $client = Client::findOrFail($clientId);
        $installment = $client->installments()->findOrFail($installmentId);

        $installment->update(['late_fee_amount' => $request->late_fee_amount]);

        return redirect()->route('clients.show', $client->id)
            ->with('success', 'Late fee updated successfully.');
    }

    public function lookupByCnic($cnic)
    {
        // RATE LIMITING: Add rate limiting to prevent enumeration abuse
        // In production, implement Redis-based rate limiting
        $maxAttempts = 5;
        $throttleKey = 'cnic-lookup-' . $cnic;
        $attempts = cache()->get($throttleKey, 0);
        
        if ($attempts >= $maxAttempts) {
            \Log::warning('CNIC lookup rate limit exceeded for IP: ' . request()->ip() . ', CNIC: ' . $cnic);
            return response()->json([
                'error' => 'Too many requests. Please try again later.'
            ], 429);
        }
        
        $client = Client::where('cnic', $cnic)->latest()->first();
        if ($client) {
            // Increment attempt counter
            cache()->put($throttleKey, $attempts + 1, now()->addMinutes(15));
            
            return response()->json([
                'found' => true,
                'salutation' => $client->salutation,
                'full_name' => $client->full_name,
                'father_husband_salutation' => $client->father_husband_salutation,
                'father_husband_name' => $client->father_husband_name,
                'phone' => $client->phone,
                'residential_address' => $client->residential_address,
                'vendor_type' => $client->vendor_type,
                'vendor_name' => $client->vendor_name,
                'vendor_cnic' => $client->vendor_cnic,
            ]);
        }
        
        // Increment attempt counter even for not found (to prevent enumeration)
        cache()->put($throttleKey, $attempts + 1, now()->addMinutes(15));
        return response()->json(['found' => false]);
    }

    public function bulkDestroy(Request $request)
    {
        $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'exists:clients,id',
        ]);

        $count = 0;
        foreach ($request->ids as $id) {
            $client = Client::withTrashed()->find($id);
            if ($client && !$client->trashed()) {
                $this->destroy($id);
                $count++;
            }
        }

        return redirect()->route('clients.index')
            ->with('success', "{$count} client(s) deleted successfully.");
    }
}

