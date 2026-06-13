<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <div class="flex items-center space-x-3">
                    <h2 class="font-extrabold text-2xl bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-600 bg-clip-text text-transparent leading-tight">
                        {{ $client->salutation }} {{ $client->full_name }}
                    </h2>
                    @php
                        $badgeColor = 'bg-gray-100 text-gray-800';
                        if ($client->status === 'active') $badgeColor = 'bg-emerald-50 text-emerald-700 border border-emerald-100';
                        elseif ($client->status === 'completed') $badgeColor = 'bg-indigo-50 text-indigo-700 border border-indigo-100';
                        elseif ($client->status === 'inactive') $badgeColor = 'bg-rose-50 text-rose-700 border border-rose-100';
                    @endphp
                    <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-semibold uppercase tracking-wider {{ $badgeColor }}">
                        {{ $client->status }}
                    </span>
                </div>
                <p class="text-xs text-gray-400 font-semibold uppercase tracking-widest mt-1">Client ID: {{ $client->client_id }}</p>
            </div>
            
            <div class="flex flex-wrap gap-3">
                <a href="{{ route('clients.edit', $client->id) }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-xl text-sm font-semibold text-gray-700 bg-white hover:bg-gray-50 focus:outline-none transition duration-150">
                    <svg class="w-4 h-4 mr-2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                    Edit Client
                </a>
                <a href="{{ route('payments.create', ['client_id' => $client->id]) }}" class="inline-flex items-center px-4 py-2 bg-emerald-600 hover:bg-emerald-500 text-white font-semibold rounded-xl shadow-md transition duration-150 text-sm">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    Log Payment
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12 bg-gray-50/50 min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">
            @if(session('success'))
                <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl flex items-center shadow-sm">
                    <svg class="w-5 h-5 mr-2 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-xl flex items-center shadow-sm">
                    <svg class="w-5 h-5 mr-2 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                    {{ session('error') }}
                </div>
            @endif

            <!-- Deal Totals Cards -->
            @php
                $dealValue = $client->property ? $client->property->total_deal_value : 0;
                $totalPaid = $client->payments->sum('amount');
                $remainingBalance = $dealValue - $totalPaid;
                $pctPaid = $dealValue > 0 ? round(($totalPaid / $dealValue) * 100) : 0;
            @endphp
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Deal Value -->
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 space-y-2">
                    <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Total Deal Value</span>
                    <div class="text-2xl font-black text-gray-800">Rs. {{ number_format($dealValue) }}</div>
                </div>
                <!-- Total Received -->
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 space-y-2">
                    <div class="flex justify-between items-center">
                        <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Total Paid</span>
                        <span class="text-xs font-bold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-md">{{ $pctPaid }}%</span>
                    </div>
                    <div class="text-2xl font-black text-emerald-600">Rs. {{ number_format($totalPaid) }}</div>
                </div>
                <!-- Remaining Balance -->
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 space-y-2">
                    <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Remaining Balance</span>
                    <div class="text-2xl font-black text-rose-600">Rs. {{ number_format($remainingBalance) }}</div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Client Details Column -->
                <div class="lg:col-span-1 space-y-8">
                    <!-- Personal Info Card -->
                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 space-y-6">
                        <h3 class="font-bold text-gray-800 text-md border-b border-gray-100 pb-3">Buyer Profile</h3>
                        <div class="space-y-4 text-sm">
                            <div>
                                <span class="block text-xs font-semibold text-gray-400 uppercase tracking-wider">Full Name</span>
                                <span class="font-semibold text-gray-800">{{ $client->salutation }} {{ $client->full_name }}</span>
                            </div>
                            <div>
                                <span class="block text-xs font-semibold text-gray-400 uppercase tracking-wider">Relation</span>
                                <span class="font-semibold text-gray-800">{{ $client->father_husband_salutation }} {{ $client->father_husband_name }}</span>
                            </div>
                            <div>
                                <span class="block text-xs font-semibold text-gray-400 uppercase tracking-wider">CNIC Number</span>
                                <span class="font-semibold text-gray-800">{{ $client->cnic }}</span>
                            </div>
                            <div>
                                <span class="block text-xs font-semibold text-gray-400 uppercase tracking-wider">Phone</span>
                                <span class="font-semibold text-gray-800">{{ $client->phone }}</span>
                            </div>
                            <div>
                                <span class="block text-xs font-semibold text-gray-400 uppercase tracking-wider">Residential Address</span>
                                <span class="text-gray-600 block leading-relaxed">{{ $client->residential_address }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Vendor Info Card -->
                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 space-y-6">
                        <h3 class="font-bold text-gray-800 text-md border-b border-gray-100 pb-3">Vendor Details</h3>
                        <div class="space-y-4 text-sm">
                            <div>
                                <span class="block text-xs font-semibold text-gray-400 uppercase tracking-wider">Vendor Profile Type</span>
                                <span class="font-semibold text-gray-800">
                                    {{ $client->vendor_type === 'custom' ? 'Custom Vendor Profile' : 'Default Company Vendor' }}
                                </span>
                            </div>
                            @if($client->vendor_type === 'custom')
                                <div>
                                    <span class="block text-xs font-semibold text-gray-400 uppercase tracking-wider">Custom Vendor Name</span>
                                    <span class="font-semibold text-gray-800">{{ $client->vendor_name ?: 'N/A' }}</span>
                                </div>
                                <div>
                                    <span class="block text-xs font-semibold text-gray-400 uppercase tracking-wider">Custom Vendor CNIC</span>
                                    <span class="font-semibold text-gray-800">{{ $client->vendor_cnic ?: 'N/A' }}</span>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Property Info Card -->
                    @if($client->property)
                        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 space-y-6">
                            <h3 class="font-bold text-gray-800 text-md border-b border-gray-100 pb-3">Property Details</h3>
                            <div class="space-y-4 text-sm">
                                <div>
                                    <span class="block text-xs font-semibold text-gray-400 uppercase tracking-wider">Property Type</span>
                                    <span class="font-semibold text-gray-800">{{ $client->property->property_type }}</span>
                                </div>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <span class="block text-xs font-semibold text-gray-400 uppercase tracking-wider">Unit / Plot #</span>
                                        <span class="font-semibold text-gray-800">{{ $client->property->plot_number }}</span>
                                    </div>
                                    <div>
                                        <span class="block text-xs font-semibold text-gray-400 uppercase tracking-wider">Block / Phase</span>
                                        <span class="font-semibold text-gray-800">{{ $client->property->block_name }}</span>
                                    </div>
                                </div>
                                <div>
                                    <span class="block text-xs font-semibold text-gray-400 uppercase tracking-wider">Location</span>
                                    <span class="font-semibold text-gray-800">{{ $client->property->location }}</span>
                                </div>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <span class="block text-xs font-semibold text-gray-400 uppercase tracking-wider">Size</span>
                                        <span class="font-semibold text-gray-800">{{ $client->property->size_sqyards }} Sq. Yards</span>
                                    </div>
                                    <div>
                                        <span class="block text-xs font-semibold text-gray-400 uppercase tracking-wider">Agreement Date</span>
                                        <span class="font-semibold text-gray-800">{{ $client->property->agreement_date }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Google Drive Folder Link -->
                    @if($client->google_drive_folder_id)
                        <a href="https://drive.google.com/drive/folders/{{ $client->google_drive_folder_id }}" target="_blank" class="w-full flex items-center justify-center p-4 border border-transparent rounded-2xl font-semibold text-sm text-white hover:bg-indigo-700 shadow-md transition-all duration-200 bg-indigo-600">
                            <svg class="w-5 h-5 mr-2" viewBox="0 0 24 24" fill="currentColor"><path d="M19.347 14.925l-3.957-6.857c-.244-.424-.693-.687-1.183-.687h-7.915c-.48 0-.923.255-1.17.67l-3.948 6.643c-.255.428-.26 1.01-.01 1.442l3.96 6.84c.243.424.693.687 1.183.687h7.915c.48 0 .923-.255 1.17-.67l3.948-6.643c.255-.428.26-1.01.01-1.442z" fill="#00E676"/><path d="M12.002 2l-7.915 13.712c-.243.424-.237.952.016 1.376l3.96 6.642c.246.415.69.67 1.17.67h15.828c.48 0 .923-.255 1.17-.67l3.96-6.642c.253-.424.26-.952.016-1.376l-7.915-13.712c-.246-.415-.69-.67-1.17-.67h-7.915c-.48 0-.923.255-1.17.67z" fill="#FFC107" opacity=".8"/><path d="M12.002 2v10.3h7.915c.48 0 .923-.255 1.17-.67l3.96-6.642c.253-.424.26-.952.016-1.376l-7.915-13.712c-.246-.415-.69-.67-1.17-.67h-7.915c-.48 0-.923.255-1.17.67z" fill="#2196F3" opacity=".9"/></svg>
                            View Client Google Drive Folder
                        </a>
                    @endif
                </div>

                <!-- Ledger & Upload Column -->
                <div class="lg:col-span-2 space-y-8">
                    <!-- Installment Schedule -->
                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 space-y-6">
                        <h3 class="font-bold text-gray-800 text-md border-b border-gray-100 pb-3 flex items-center justify-between">
                            <span>Installment Schedule</span>
                            <div class="flex items-center gap-2">
                                @if($client->installments->where('status', 'pending')->count() > 0)
                                    <span class="text-xs bg-orange-50 text-orange-700 px-2 py-0.5 rounded-md font-semibold">{{ $client->installments->where('status', 'pending')->count() }} Pending</span>
                                    @can('delete installments')
                                        <form action="{{ route('clients.installments.clear', $client->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to clear ALL pending installments? This cannot be undone.')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="inline-flex items-center gap-1 text-[10px] font-bold text-rose-600 bg-rose-50 hover:bg-rose-100 border border-rose-100 px-2 py-0.5 rounded-md uppercase tracking-wider transition cursor-pointer">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                Clear All
                                            </button>
                                        </form>
                                    @endcan
                                @endif
                            </div>
                        </h3>

                        @if($client->installments->count() > 0)
                            <div class="overflow-x-auto">
                                <table class="w-full text-left border-collapse text-sm">
                                    <thead>
                                        <tr class="border-b border-gray-100 text-gray-400 text-xs font-semibold uppercase tracking-wider">
                                            <th class="pb-3">No.</th>
                                            <th class="pb-3">Due Date</th>
                                            <th class="pb-3 text-right">Amount</th>
                                            <th class="pb-3 text-right">Status</th>
                                            <th class="pb-3 text-right">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100">
                                        @foreach($client->installments as $installment)
                                            <tr class="hover:bg-gray-50/50 transition">
                                                <td class="py-4 font-semibold text-gray-800">
                                                    #{{ $installment->installment_number }}
                                                </td>
                                                <td class="py-4 text-gray-600 font-medium">
                                                    {{ $installment->due_date->format('M d, Y') }}
                                                    @if($installment->status === 'pending' && $installment->due_date->isPast())
                                                        <span class="ml-2 text-[10px] text-rose-600 font-bold bg-rose-50 px-1.5 py-0.5 rounded-md uppercase border border-rose-100">Overdue</span>
                                                    @endif
                                                </td>
                                                <td class="py-4 text-right">
                                                    <div class="font-bold {{ $installment->status === 'paid' ? 'text-emerald-600 line-through' : 'text-gray-800' }}">Rs. {{ number_format($installment->amount) }}</div>
                                                </td>
                                                <td class="py-4 text-right">
                                                    @if($installment->status === 'paid')
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-extrabold bg-emerald-50 text-emerald-600 uppercase tracking-wider border border-emerald-100">Paid</span>
                                                    @else
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-extrabold bg-orange-50 text-orange-600 uppercase tracking-wider border border-orange-100">Pending</span>
                                                    @endif
                                                </td>
                                                <td class="py-4 text-right">
                                                    @if($installment->status === 'pending')
                                                        @can('delete installments')
                                                            <form action="{{ route('clients.installments.destroy', [$client->id, $installment->id]) }}" method="POST" onsubmit="return confirm('Delete installment #{{ $installment->installment_number }}?')">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit" title="Delete Installment" class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-rose-50 hover:bg-rose-100 text-rose-500 hover:text-rose-700 border border-rose-100 transition cursor-pointer">
                                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                                </button>
                                                            </form>
                                                        @endcan
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-center py-6 space-y-3">
                                <p class="text-sm text-gray-500">No active installment plan configured.</p>
                            </div>
                        @endif
                        
                        @can('manage installments')
                        <!-- Collapsible Form for Setup / Re-plan -->
                        <div x-data="{ showPlanner: false }" class="mt-4 pt-4 border-t border-gray-100">
                            <button @click="showPlanner = !showPlanner" type="button" class="w-full text-center text-xs font-bold text-indigo-600 hover:text-indigo-800 py-2 bg-indigo-50 hover:bg-indigo-100 rounded-xl transition cursor-pointer border border-indigo-100">
                                <span x-text="showPlanner ? 'Close Planner' : 'Setup / Restructure Installment Plan'"></span>
                            </button>
                            
                            <div x-show="showPlanner" x-collapse class="mt-4" style="display: none;">
                                <form action="{{ route('clients.installments.store', $client->id) }}" method="POST" class="bg-gray-50 p-6 rounded-2xl border border-gray-200 space-y-4">
                                    @csrf
                                    <h4 class="text-sm font-bold text-gray-800 mb-2">Restructure / Create New Plan</h4>
                                    <p class="text-xs text-gray-500 mb-4">This will delete all current <strong class="text-orange-600">Pending</strong> installments and generate a new schedule for the remaining balance (<strong class="text-gray-800">Rs. {{ number_format($remainingBalance) }}</strong>).</p>

                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Number of Installments</label>
                                            <input type="number" name="installment_count" min="1" required class="w-full rounded-xl border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Interval</label>
                                            <select name="installment_interval" required class="w-full rounded-xl border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                                <option value="monthly">Monthly</option>
                                                <option value="quarterly">Quarterly</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">First Installment Date</label>
                                        <input type="date" name="installment_start_date" required class="w-full rounded-xl border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                    </div>
                                    
                                    <div class="pt-2">
                                        <button type="submit" class="w-full inline-flex justify-center items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white font-semibold rounded-xl shadow-md transition text-sm cursor-pointer">
                                            Generate Plan
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                        @endcan
                    </div>

                    <!-- Payment Ledger -->
                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 space-y-6">
                        <h3 class="font-bold text-gray-800 text-md border-b border-gray-100 pb-3 flex items-center justify-between">
                            <span>Payment History Ledger</span>
                            <span class="text-xs bg-indigo-50 text-indigo-700 px-2 py-0.5 rounded-md font-semibold">{{ $client->payments->count() }} Payments</span>
                        </h3>
                        <div class="overflow-x-auto">
                            <table class="w-full text-left border-collapse text-sm">
                                <thead>
                                    <tr class="border-b border-gray-100 text-gray-400 text-xs font-semibold uppercase tracking-wider">
                                        <th class="pb-3">No.</th>
                                        <th class="pb-3">Amount</th>
                                        <th class="pb-3">Particulars</th>
                                        <th class="pb-3">Method</th>
                                        <th class="pb-3">Date</th>
                                        <th class="pb-3 text-right">Receipt</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    @forelse($client->payments as $payment)
                                        <tr>
                                            <td class="py-4 font-semibold text-gray-800">
                                                #{{ $payment->payment_number }}
                                            </td>
                                            <td class="py-4 font-bold text-indigo-600">
                                                Rs. {{ number_format($payment->amount) }}
                                            </td>
                                            <td class="py-4 text-gray-600">
                                                {{ $payment->particulars }}
                                            </td>
                                            <td class="py-4">
                                                <div class="text-gray-800 font-semibold">{{ $payment->payment_method }}</div>
                                                @if($payment->bank_name)
                                                    <span class="text-xxs text-gray-400 uppercase tracking-widest block">{{ $payment->bank_name }} {{ $payment->cheque_number }}</span>
                                                @endif
                                            </td>
                                            <td class="py-4 text-gray-500">
                                                {{ $payment->payment_date }}
                                            </td>
                                            <td class="py-4 text-right">
                                                @if($payment->receipt)
                                                    <div class="flex justify-end gap-2 items-center">
                                                        <a href="{{ route('receipts.download', $payment->receipt->id) }}" class="inline-flex items-center px-2 py-1 bg-indigo-50 text-indigo-600 hover:bg-indigo-100 rounded-lg text-xs font-bold transition">
                                                            DOCX
                                                        </a>
                                                        @if($payment->receipt->google_drive_file_url)
                                                            <a href="{{ $payment->receipt->google_drive_file_url }}" target="_blank" class="inline-flex items-center px-2 py-1 bg-emerald-50 text-emerald-600 hover:bg-emerald-100 rounded-lg text-xs font-bold transition">
                                                                Drive
                                                            </a>
                                                        @endif
                                                        @can('delete payments')
                                                            <button type="button" onclick="reversePayment('{{ $payment->id }}')" class="inline-flex items-center px-2 py-1 bg-rose-50 text-rose-600 hover:bg-rose-100 rounded-lg text-xs font-bold transition m-0">
                                                                Reverse
                                                            </button>
                                                        @endcan
                                                    </div>
                                                @else
                                                    <div class="flex justify-end gap-2 items-center">
                                                        <span class="text-xs text-gray-400">None</span>
                                                        @can('delete payments')
                                                            <button type="button" onclick="reversePayment('{{ $payment->id }}')" class="inline-flex items-center px-2 py-1 bg-rose-50 text-rose-600 hover:bg-rose-100 rounded-lg text-xs font-bold transition m-0">
                                                                Reverse
                                                            </button>
                                                        @endcan
                                                    </div>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="py-8 text-center text-gray-400">
                                                No payments logged for this client yet.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Upload Documents & Listing -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <!-- Documents List -->
                        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 space-y-6">
                            <h3 class="font-bold text-gray-800 text-md border-b border-gray-100 pb-3">Legal Documents</h3>
                            <ul class="divide-y divide-gray-100 text-sm">
                                @forelse($client->documents as $document)
                                    <li class="py-3 flex justify-between items-center gap-3">
                                        <div class="min-w-0">
                                            <div class="font-semibold text-gray-800 truncate" title="{{ $document->original_filename }}">{{ $document->original_filename }}</div>
                                            <span class="text-xxs uppercase tracking-wider text-indigo-600 font-bold bg-indigo-50 px-1.5 py-0.5 rounded">{{ $document->document_type }}</span>
                                        </div>
                                        <a href="{{ $document->google_drive_file_url }}" target="_blank" class="inline-flex items-center text-indigo-600 hover:text-indigo-500 font-semibold flex-shrink-0">
                                            View
                                            <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                                        </a>
                                    </li>
                                @empty
                                    <li class="py-6 text-center text-gray-400">
                                        No supplementary files uploaded.
                                    </li>
                                @endforelse
                            </ul>
                        </div>

                        <!-- Inline Upload Form -->
                        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 space-y-4">
                            <h3 class="font-bold text-gray-800 text-md border-b border-gray-100 pb-3">Upload Legal File</h3>
                            <form action="{{ route('documents.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                                @csrf
                                <input type="hidden" name="client_id" value="{{ $client->id }}">

                                <div>
                                    <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Document Type</label>
                                    <select name="document_type" class="w-full rounded-xl border-gray-200 focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                        <option value="agreement">Agreement Copy</option>
                                        <option value="cnic">Scanned CNIC Copy</option>
                                        <option value="other">Other / Miscellaneous</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Select File</label>
                                    <input type="file" name="document_file" class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 border border-gray-200 rounded-xl p-2">
                                    <p class="text-xxs text-gray-400 mt-1">Maximum size: 10MB (PDF, PNG, JPG, DOCX)</p>
                                </div>

                                <button type="submit" class="w-full inline-flex items-center justify-center px-4 py-2 bg-gray-800 hover:bg-gray-700 text-white font-semibold rounded-xl shadow-md transition duration-150 text-sm">
                                    Upload Document to Drive
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- System Audit & History Logs -->
                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 space-y-6 mt-8">
                        <h3 class="font-bold text-gray-800 text-md border-b border-gray-100 pb-3 flex items-center justify-between">
                            <span class="flex items-center space-x-2">
                                <span>System Activity & Audit Logs</span>
                                <span class="text-xs bg-gray-50 text-gray-500 px-2 py-0.5 rounded-md font-semibold">{{ $activityLogs->count() }} Entries</span>
                            </span>
                            
                            <!-- Toggle Button -->
                            <button 
                                id="toggle-logs-btn"
                                type="button"
                                class="inline-flex items-center px-3 py-1.5 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 rounded-xl text-xs font-bold transition cursor-pointer select-none border border-indigo-100"
                            >
                                <span id="toggle-logs-text">View Logs</span>
                                <svg 
                                    id="toggle-logs-icon"
                                    class="w-3.5 h-3.5 ml-1.5 transition-transform duration-200" 
                                    fill="none" 
                                    stroke="currentColor" 
                                    viewBox="0 0 24 24"
                                >
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </button>
                        </h3>
                        
                        <div id="logs-list-wrapper" class="flow-root" style="max-height: 280px; overflow-y: auto; display: none; padding-right: 4px;">
                            <ul role="list" class="divide-y divide-gray-100">
                                @forelse($activityLogs as $log)
                                    @php
                                        // Plain English Audit Log Description Helper
                                        $description = 'Activity recorded';
                                        $iconBg = 'bg-gray-50 text-gray-500 border border-gray-200';
                                        $svgPath = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>';
                                        
                                        $logType = class_basename($log->loggable_type);
                                        
                                        if ($log->action === 'create') {
                                            $iconBg = 'bg-emerald-50 text-emerald-600 border border-emerald-100';
                                            $svgPath = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>';
                                            
                                            if ($logType === 'Payment') {
                                                $amount = isset($log->new_values['amount']) ? 'Rs. ' . number_format($log->new_values['amount']) : '';
                                                $description = "Payment of {$amount} added";
                                            } elseif ($logType === 'Property') {
                                                $description = "Property details registered";
                                            } else {
                                                $description = "Client profile onboarded";
                                            }
                                        } elseif ($log->action === 'delete') {
                                            $iconBg = 'bg-rose-50 text-rose-600 border border-rose-100';
                                            $svgPath = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>';
                                            
                                            $description = $logType === 'Payment' ? "Payment record deleted" : "Client profile deleted";
                                        } elseif ($log->action === 'restore') {
                                            $iconBg = 'bg-teal-50 text-teal-600 border border-teal-100';
                                            $svgPath = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 1121.21 8H18.2M9 11l3-3 3 3m-3-3v12"></path>';
                                            
                                            $description = $logType === 'Payment' ? "Payment record restored" : "Client profile restored";
                                        } elseif ($log->action === 'update') {
                                            $iconBg = 'bg-amber-50 text-amber-600 border border-amber-100';
                                            $svgPath = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path>';
                                            
                                            $fields = [];
                                            if ($log->old_values && $log->new_values) {
                                                foreach ($log->new_values as $key => $newVal) {
                                                    if (array_key_exists($key, $log->old_values) && $log->old_values[$key] !== $newVal) {
                                                        if ($key === 'updated_at') continue;
                                                        $fields[] = str_replace('_', ' ', $key);
                                                    }
                                                }
                                            }
                                            $changesCount = count($fields);
                                            
                                            if ($changesCount === 1) {
                                                $description = ucfirst($fields[0]) . " changed";
                                            } elseif ($changesCount > 1) {
                                                $description = implode(', ', array_map('ucfirst', $fields)) . " changed";
                                            } else {
                                                $description = "Details updated";
                                            }
                                        }
                                    @endphp
                                    
                                    <li class="py-2.5">
                                        <div class="flex items-center justify-between hover:bg-gray-50/50 px-2.5 py-1.5 rounded-xl transition duration-150">
                                            <div class="flex items-center space-x-3 min-w-0">
                                                <!-- Action status icon -->
                                                <span class="h-6 w-6 rounded-full flex items-center justify-center shrink-0 {{ $iconBg }}">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        {!! $svgPath !!}
                                                    </svg>
                                                </span>
                                                
                                                <!-- Simple plain-English log summary -->
                                                <div class="truncate">
                                                    <span class="text-xs font-bold text-gray-800">{{ $description }}</span>
                                                    <span class="text-[10px] text-gray-400 font-semibold ml-2">by {{ $log->user ? $log->user->name : 'System' }}</span>
                                                </div>
                                            </div>
                                            
                                            <div class="flex items-center space-x-3 shrink-0 ml-4">
                                                <!-- Date & time -->
                                                <span class="text-[10px] text-gray-400 font-semibold">{{ $log->created_at->format('M d, h:i A') }}</span>
                                                
                                                <!-- Rollback action -->
                                                @if($log->old_values && $log->action !== 'create' && $log->action !== 'restore')
                                                    <form action="{{ route('activity-logs.rollback', $log->id) }}" method="POST" class="rollback-form inline-block">
                                                        @csrf
                                                        <button type="submit" class="px-2 py-0.5 bg-indigo-50 hover:bg-indigo-100 border border-indigo-100 text-indigo-600 rounded-md text-[9px] font-extrabold transition uppercase tracking-wider cursor-pointer">
                                                            Rollback
                                                        </button>
                                                    </form>
                                                @endif
                                                
                                                <!-- Expand details caret button -->
                                                @if($log->old_values || $log->new_values)
                                                    <button onclick="document.getElementById('log-details-{{ $log->id }}').classList.toggle('hidden'); this.querySelector('.arrow-icon').classList.toggle('rotate-180')" class="h-6 w-6 rounded-lg flex items-center justify-center text-gray-400 hover:text-gray-700 hover:bg-gray-100 cursor-pointer transition select-none">
                                                        <svg class="w-3.5 h-3.5 transition-transform duration-200 arrow-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                                    </button>
                                                @endif
                                            </div>
                                        </div>
                                        
                                        <!-- Collapsible Drawer details -->
                                        @if($log->old_values || $log->new_values)
                                            <div id="log-details-{{ $log->id }}" class="hidden mt-1.5 mx-8 bg-gray-50 border border-gray-100 rounded-xl p-3 space-y-2 transition-all">
                                                @if($log->action === 'update')
                                                    @php
                                                        $changesList = [];
                                                        if ($log->old_values && $log->new_values) {
                                                            foreach ($log->new_values as $key => $newVal) {
                                                                if (array_key_exists($key, $log->old_values) && $log->old_values[$key] !== $newVal) {
                                                                    if ($key === 'updated_at') continue;
                                                                    $changesList[$key] = [
                                                                        'old' => is_array($log->old_values[$key]) ? json_encode($log->old_values[$key]) : ($log->old_values[$key] ?? 'NULL'),
                                                                        'new' => is_array($newVal) ? json_encode($newVal) : ($newVal ?? 'NULL')
                                                                    ];
                                                                }
                                                            }
                                                        }
                                                    @endphp
                                                    
                                                    @if(!empty($changesList))
                                                        <div class="space-y-1.5">
                                                            @foreach($changesList as $field => $diff)
                                                                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between border-b border-gray-100 pb-1.5 last:border-0 last:pb-0 gap-2">
                                                                    <span class="font-bold text-gray-500 capitalize text-[10px] sm:w-1/4">{{ str_replace('_', ' ', $field) }}</span>
                                                                    <div class="flex flex-1 items-center gap-1.5 flex-wrap">
                                                                        <span class="text-[10px] font-mono text-rose-700 bg-rose-50 border border-rose-100 px-2 py-0.5 rounded-lg line-through break-all">
                                                                            {{ $diff['old'] }}
                                                                        </span>
                                                                        <svg class="w-3 h-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5l7 7-7 7"></path></svg>
                                                                        <span class="text-[10px] font-mono text-emerald-700 bg-emerald-50 border border-emerald-100 px-2 py-0.5 rounded-lg font-bold break-all">
                                                                            {{ $diff['new'] }}
                                                                        </span>
                                                                    </div>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    @else
                                                        <p class="text-[10px] text-gray-400 font-medium italic">No structural changes detected.</p>
                                                    @endif
                                                    
                                                @elseif($log->action === 'create')
                                                    <div class="space-y-1.5">
                                                        <span class="font-bold text-gray-400 text-[10px] block">Created Values:</span>
                                                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 bg-emerald-50/20 p-2 border border-emerald-100/50 rounded-lg">
                                                            @foreach($log->new_values as $key => $val)
                                                                @if($key !== 'updated_at' && $key !== 'created_at' && $key !== 'id')
                                                                    <div class="flex flex-col">
                                                                        <span class="text-[10px] text-gray-400 capitalize">{{ str_replace('_', ' ', $key) }}</span>
                                                                        <span class="font-bold text-gray-700 text-[10px] break-all">{{ is_array($val) ? json_encode($val) : $val }}</span>
                                                                    </div>
                                                                @endif
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                    
                                                @elseif($log->action === 'delete')
                                                    <div class="space-y-1.5">
                                                        <span class="font-bold text-gray-400 text-[10px] block">Archived Attributes:</span>
                                                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 bg-rose-50/20 p-2 border border-rose-100/50 rounded-lg">
                                                            @foreach($log->old_values as $key => $val)
                                                                @if($key !== 'updated_at' && $key !== 'created_at' && $key !== 'id')
                                                                    <div class="flex flex-col">
                                                                        <span class="text-[10px] text-gray-400 capitalize">{{ str_replace('_', ' ', $key) }}</span>
                                                                        <span class="font-bold text-gray-700 text-[10px] line-through break-all">{{ is_array($val) ? json_encode($val) : $val }}</span>
                                                                    </div>
                                                                @endif
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>
                                        @endif
                                    </li>
                                @empty
                                    <li class="py-6 text-center text-gray-400 text-sm">
                                        No activity logs recorded for this client yet.
                                    </li>
                                @endforelse
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
    <form id="globalReversePaymentForm" method="POST" class="hidden" style="display: none;">
        @csrf
        @method('DELETE')
        <input type="hidden" name="reason" id="globalReverseReasonInput">
    </form>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const toggleLogsBtn = document.getElementById('toggle-logs-btn');
            const toggleLogsText = document.getElementById('toggle-logs-text');
            const toggleLogsIcon = document.getElementById('toggle-logs-icon');
            const logsListWrapper = document.getElementById('logs-list-wrapper');
            
            if (toggleLogsBtn && logsListWrapper) {
                toggleLogsBtn.addEventListener('click', function () {
                    const isHidden = logsListWrapper.style.display === 'none';
                    if (isHidden) {
                        logsListWrapper.style.display = 'block';
                        toggleLogsText.textContent = 'Hide Logs';
                        toggleLogsIcon.classList.add('rotate-180');
                    } else {
                        logsListWrapper.style.display = 'none';
                        toggleLogsText.textContent = 'View Logs';
                        toggleLogsIcon.classList.remove('rotate-180');
                    }
                });
            }
        });

        function reversePayment(paymentId) {
            let reason = prompt('Please enter the reason for deleting/reversing this payment:');
            if (reason) {
                let form = document.getElementById('globalReversePaymentForm');
                form.action = '/payments/' + paymentId;
                document.getElementById('globalReverseReasonInput').value = reason;
                form.submit();
            }
        }
    </script>
</x-app-layout>
