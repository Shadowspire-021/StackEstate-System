<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <h2 class="font-extrabold text-2xl bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-600 bg-clip-text text-transparent leading-tight">
                {{ __('Dashboard') }}
            </h2>
            
            <div class="flex items-center space-x-2 bg-white px-3 py-1.5 border border-gray-200 rounded-xl shadow-sm">
                <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Dashboard Scope:</span>
                <select id="dashboard-scope-filter" onchange="window.location.href = '?status=' + this.value" class="border-0 focus:ring-0 text-sm font-bold text-gray-700 bg-transparent py-0 pl-1 pr-8 cursor-pointer focus:outline-none">
                    <option value="active" {{ $status === 'active' ? 'selected' : '' }}>🟢 Active Clients Dashboard</option>
                    <option value="hold" {{ $status === 'hold' ? 'selected' : '' }}>🟡 Hold Clients Dashboard</option>
                    <option value="completed" {{ $status === 'completed' ? 'selected' : '' }}>🔵 Completed Clients Dashboard</option>
                    <option value="deleted" {{ $status === 'deleted' ? 'selected' : '' }}>🔴 Deleted Clients Dashboard</option>
                </select>
            </div>
        </div>
    </x-slot>

    <div class="py-12 bg-gray-50/50 min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">
            <!-- Stats Grid -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <!-- Total Clients -->
                <div class="relative overflow-hidden bg-white p-6 rounded-2xl shadow-sm border border-gray-100 hover:shadow-lg transition-all duration-300 group">
                    <div class="absolute -right-4 -bottom-4 text-gray-100 group-hover:text-indigo-50 transition-colors duration-300">
                        <svg class="w-32 h-32" fill="currentColor" viewBox="0 0 20 20"><path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a7 7 0 00-7 7v1h12v-1a7 7 0 00-7-7z"></path></svg>
                    </div>
                    <div class="relative z-10 space-y-4">
                        <span class="text-sm font-semibold text-gray-500 uppercase tracking-wider">Total Clients</span>
                        <div class="flex items-baseline space-x-2">
                            <span class="text-4xl font-extrabold text-indigo-600">{{ $totalClients }}</span>
                        </div>
                    </div>
                </div>

                <!-- Total Deal Value -->
                <div class="relative overflow-hidden bg-white p-6 rounded-2xl shadow-sm border border-gray-100 hover:shadow-lg transition-all duration-300 group">
                    <div class="absolute -right-4 -bottom-4 text-gray-100 group-hover:text-emerald-50 transition-colors duration-300">
                        <svg class="w-32 h-32" fill="currentColor" viewBox="0 0 20 20"><path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"></path></svg>
                    </div>
                    <div class="relative z-10 space-y-4">
                        <span class="text-sm font-semibold text-gray-500 uppercase tracking-wider">Total Deal Value</span>
                        <div class="flex items-baseline space-x-2">
                            <span class="text-2xl font-extrabold text-emerald-600">Rs. {{ number_format($totalDealValue) }}</span>
                        </div>
                    </div>
                </div>

                <!-- Total Received -->
                <div class="relative overflow-hidden bg-white p-6 rounded-2xl shadow-sm border border-gray-100 hover:shadow-lg transition-all duration-300 group">
                    <div class="absolute -right-4 -bottom-4 text-gray-100 group-hover:text-blue-50 transition-colors duration-300">
                        <svg class="w-32 h-32" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4 4a2 2 0 00-2 2v4a2 2 0 002 2V6h10a2 2 0 00-2-2H4zm2 6a2 2 0 012-2h8a2 2 0 012 2v4a2 2 0 01-2 2H8a2 2 0 01-2-2v-4zm6 4a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"></path></svg>
                    </div>
                    <div class="relative z-10 space-y-4">
                        <span class="text-sm font-semibold text-gray-500 uppercase tracking-wider">Total Received</span>
                        <div class="flex items-baseline space-x-2">
                            <span class="text-2xl font-extrabold text-blue-600">Rs. {{ number_format($totalReceived) }}</span>
                        </div>
                    </div>
                </div>

                <!-- Remaining Balance -->
                <div class="relative overflow-hidden bg-white p-6 rounded-2xl shadow-sm border border-gray-100 hover:shadow-lg transition-all duration-300 group">
                    <div class="absolute -right-4 -bottom-4 text-gray-100 group-hover:text-amber-50 transition-colors duration-300">
                        <svg class="w-32 h-32" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path></svg>
                    </div>
                    <div class="relative z-10 space-y-4">
                        <span class="text-sm font-semibold text-gray-500 uppercase tracking-wider">Remaining Balance</span>
                        <div class="flex items-baseline space-x-2">
                            <span class="text-2xl font-extrabold text-amber-600">Rs. {{ number_format($remainingBalance) }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                <h3 class="text-lg font-bold text-gray-800 mb-4">Quick Actions</h3>
                <div class="flex flex-wrap gap-4">
                    <a href="{{ route('clients.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white font-semibold rounded-xl shadow-sm transition-all duration-200">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path></svg>
                        Onboard New Client
                    </a>
                    <a href="{{ route('payments.create') }}" class="inline-flex items-center px-4 py-2 bg-emerald-600 hover:bg-emerald-500 text-white font-semibold rounded-xl shadow-sm transition-all duration-200">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        Log Payment
                    </a>
                    @if(Auth::user()->hasRole('super_admin'))
                        <a href="{{ route('settings.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 hover:bg-gray-700 text-white font-semibold rounded-xl shadow-sm transition-all duration-200">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                            Settings
                        </a>
                    @endif
                </div>
            </div>

            <!-- Recent Activity Table -->
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-lg font-bold text-gray-800">Recent Payments</h3>
                    <a href="{{ route('clients.index') }}" class="text-indigo-600 hover:text-indigo-500 font-semibold text-sm">View All Clients &rarr;</a>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-gray-100 text-gray-400 text-xs font-semibold uppercase tracking-wider">
                                <th class="pb-3">Client</th>
                                <th class="pb-3">Property</th>
                                <th class="pb-3">Amount</th>
                                <th class="pb-3">Method</th>
                                <th class="pb-3">Date</th>
                                <th class="pb-3">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 text-sm">
                            @forelse($recentPayments as $payment)
                                <tr>
                                    <td class="py-4">
                                        <div class="font-semibold text-gray-800">
                                            {{ $payment->client?->full_name ?? 'Deleted Client' }}
                                            @if($payment->client?->trashed())
                                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xxs font-bold bg-red-50 text-red-700 border border-red-100 uppercase ml-1">Deleted</span>
                                            @endif
                                        </div>
                                        <span class="text-xs text-gray-400 uppercase tracking-wider">{{ $payment->client?->client_id ?? 'N/A' }}</span>
                                    </td>
                                    <td class="py-4 text-gray-600">
                                        {{ $payment->property?->property_type ?? 'N/A' }} - Plot {{ $payment->property?->plot_number ?? 'N/A' }}
                                    </td>
                                    <td class="py-4 font-bold text-indigo-600">
                                        Rs. {{ number_format($payment->amount) }}
                                    </td>
                                    <td class="py-4">
                                        <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-800 uppercase tracking-wider">
                                            {{ $payment->payment_method }}
                                        </span>
                                    </td>
                                    <td class="py-4 text-gray-500">
                                        {{ $payment->payment_date }}
                                    </td>
                                    <td class="py-4">
                                        <a href="{{ route('clients.show', $payment->client_id) }}" class="text-indigo-600 hover:text-indigo-500 font-semibold">View History</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="py-8 text-center text-gray-400">
                                        No recent payments logged.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
