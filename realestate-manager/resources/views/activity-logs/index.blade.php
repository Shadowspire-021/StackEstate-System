<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Activity Logs') }}
            </h2>
            <span class="text-xs bg-gray-50 text-gray-500 px-2 py-0.5 rounded-md font-semibold">{{ $logs->total() }} Total Entries</span>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Filters -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <form action="{{ route('activity-logs.index') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
                        <!-- Action Filter -->
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Action</label>
                            <select name="action" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                <option value="">All Actions</option>
                                <option value="create" {{ ($filters['action'] ?? '') === 'create' ? 'selected' : '' }}>Create</option>
                                <option value="update" {{ ($filters['action'] ?? '') === 'update' ? 'selected' : '' }}>Update</option>
                                <option value="delete" {{ ($filters['action'] ?? '') === 'delete' ? 'selected' : '' }}>Delete</option>
                                <option value="restore" {{ ($filters['action'] ?? '') === 'restore' ? 'selected' : '' }}>Restore</option>
                            </select>
                        </div>

                        <!-- Entity Type Filter -->
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Entity Type</label>
                            <select name="loggable_type" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                <option value="">All Entities</option>
                                <option value="App\Models\Client" {{ ($filters['loggable_type'] ?? '') === 'App\Models\Client' ? 'selected' : '' }}>Client</option>
                                <option value="App\Models\Payment" {{ ($filters['loggable_type'] ?? '') === 'App\Models\Payment' ? 'selected' : '' }}>Payment</option>
                                <option value="App\Models\Property" {{ ($filters['loggable_type'] ?? '') === 'App\Models\Property' ? 'selected' : '' }}>Property</option>
                                <option value="App\Models\Installment" {{ ($filters['loggable_type'] ?? '') === 'App\Models\Installment' ? 'selected' : '' }}>Installment</option>
                            </select>
                        </div>

                        <!-- From Date -->
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">From</label>
                            <input type="date" name="from" value="{{ $filters['from'] ?? '' }}"
                                   class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                        </div>

                        <!-- To Date -->
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">To</label>
                            <input type="date" name="to" value="{{ $filters['to'] ?? '' }}"
                                   class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                        </div>

                        <!-- Buttons -->
                        <div class="flex items-end gap-2">
                            <button type="submit"
                                    class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm font-semibold hover:bg-indigo-500 transition">
                                Filter
                            </button>
                            <a href="{{ route('activity-logs.index') }}"
                               class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg text-sm font-semibold hover:bg-gray-300 transition">
                                Reset
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Activity Logs Table -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Action</th>
                                <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Entity</th>
                                <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Description</th>
                                <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Client</th>
                                <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">User</th>
                                <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Date</th>
                                <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Action</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-100">
                            @forelse($logs as $log)
                                @php
                                    $logType = class_basename($log->loggable_type);

                                    // Action badge colors
                                    $actionColors = [
                                        'create' => 'bg-emerald-100 text-emerald-700 border border-emerald-200',
                                        'update' => 'bg-amber-100 text-amber-700 border border-amber-200',
                                        'delete' => 'bg-rose-100 text-rose-700 border border-rose-200',
                                        'restore' => 'bg-teal-100 text-teal-700 border border-teal-200',
                                    ];
                                    $actionColor = $actionColors[$log->action] ?? 'bg-gray-100 text-gray-700 border border-gray-200';

                                    // Generate human-readable description
                                    $description = 'Activity recorded';
                                    if ($log->action === 'create') {
                                        if ($logType === 'Payment') {
                                            $amount = isset($log->new_values['amount']) ? 'Rs. ' . number_format($log->new_values['amount']) : '';
                                            $description = "Payment of {$amount} added";
                                        } elseif ($logType === 'Property') {
                                            $description = "Property details registered";
                                        } else {
                                            $description = "Client profile onboarded";
                                        }
                                    } elseif ($log->action === 'delete') {
                                        $description = $logType === 'Payment' ? "Payment record deleted" : "Client profile deleted";
                                    } elseif ($log->action === 'restore') {
                                        $description = $logType === 'Payment' ? "Payment record restored" : "Client profile restored";
                                    } elseif ($log->action === 'update') {
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
                                            $description = implode(', ', array_map('ucfirst', array_slice($fields, 0, 3))) . ($changesCount > 3 ? '...' : '') . " changed";
                                        } else {
                                            $description = "Details updated";
                                        }
                                    }
                                @endphp
                                <tr class="hover:bg-gray-50">
                                    <!-- Action Badge -->
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-extrabold uppercase tracking-wider {{ $actionColor }}">
                                            {{ $log->action }}
                                        </span>
                                    </td>

                                    <!-- Entity Type -->
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-600">
                                        {{ $logType }}
                                    </td>

                                    <!-- Description -->
                                    <td class="px-4 py-3 text-sm text-gray-800 font-medium">
                                        {{ $description }}
                                    </td>

                                    <!-- Client -->
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-600">
                                        @if($log->client)
                                            <a href="{{ route('clients.show', $log->client->id) }}" class="text-indigo-600 hover:text-indigo-800 font-semibold">
                                                {{ $log->client->full_name }}
                                            </a>
                                        @else
                                            <span class="text-gray-400">N/A</span>
                                        @endif
                                    </td>

                                    <!-- User -->
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-600">
                                        {{ $log->user ? $log->user->name : 'System' }}
                                    </td>

                                    <!-- Date -->
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">
                                        {{ $log->created_at->format('M d, Y h:i A') }}
                                    </td>

                                    <!-- View Link -->
                                    <td class="px-4 py-3 whitespace-nowrap text-sm">
                                        <a href="{{ route('activity-logs.show', $log->id) }}"
                                           class="inline-flex items-center px-3 py-1 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 font-extrabold rounded-lg text-[10px] uppercase tracking-wider transition border border-indigo-100/30">
                                            View
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-4 py-12 text-center text-gray-500 text-sm">
                                        No activity logs found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                @if($logs->hasPages())
                    <div class="px-6 py-4 border-t border-gray-100">
                        {{ $logs->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
