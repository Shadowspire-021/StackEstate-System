<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Activity Log Detail') }}
            </h2>
            <a href="{{ route('activity-logs.index') }}"
               class="text-sm text-indigo-600 hover:text-indigo-800 font-semibold">
                &larr; Back to Logs
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            @php
                $logType = class_basename($log->loggable_type);

                $actionColors = [
                    'create' => 'bg-emerald-100 text-emerald-700 border border-emerald-200',
                    'update' => 'bg-amber-100 text-amber-700 border border-amber-200',
                    'delete' => 'bg-rose-100 text-rose-700 border border-rose-200',
                    'restore' => 'bg-teal-100 text-teal-700 border border-teal-200',
                ];
                $actionColor = $actionColors[$log->action] ?? 'bg-gray-100 text-gray-700 border border-gray-200';

                // Generate description
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
                        $description = implode(', ', array_map('ucfirst', $fields)) . " changed";
                    } else {
                        $description = "Details updated";
                    }
                }
            @endphp

            <!-- Log Header Card -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 border-b border-gray-100">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-extrabold uppercase tracking-wider {{ $actionColor }}">
                                {{ $log->action }}
                            </span>
                            <span class="text-sm font-semibold text-gray-800">{{ $description }}</span>
                        </div>
                        @if($log->old_values && $log->action !== 'create' && $log->action !== 'restore')
                            <form action="{{ route('activity-logs.rollback', $log->id) }}" method="POST" class="rollback-form">
                                @csrf
                                <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white font-semibold rounded-xl transition text-xs uppercase tracking-wider">
                                    Rollback to this version
                                </button>
                            </form>
                        @endif
                    </div>
                </div>

                <!-- Metadata -->
                <div class="p-6 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div>
                        <span class="text-xs font-bold text-gray-500 uppercase tracking-wider">Log ID</span>
                        <p class="text-sm font-semibold text-gray-800">#{{ $log->id }}</p>
                    </div>
                    <div>
                        <span class="text-xs font-bold text-gray-500 uppercase tracking-wider">Entity Type</span>
                        <p class="text-sm font-semibold text-gray-800">{{ $logType }}</p>
                    </div>
                    <div>
                        <span class="text-xs font-bold text-gray-500 uppercase tracking-wider">Entity ID</span>
                        <p class="text-sm font-semibold text-gray-800">#{{ $log->loggable_id }}</p>
                    </div>
                    <div>
                        <span class="text-xs font-bold text-gray-500 uppercase tracking-wider">Date & Time</span>
                        <p class="text-sm font-semibold text-gray-800">{{ $log->created_at->format('M d, Y h:i:s A') }}</p>
                    </div>
                    <div>
                        <span class="text-xs font-bold text-gray-500 uppercase tracking-wider">Performed By</span>
                        <p class="text-sm font-semibold text-gray-800">{{ $log->user ? $log->user->name : 'System' }}</p>
                    </div>
                    <div>
                        <span class="text-xs font-bold text-gray-500 uppercase tracking-wider">Client</span>
                        @if($log->client)
                            <a href="{{ route('clients.show', $log->client->id) }}" class="text-sm font-semibold text-indigo-600 hover:text-indigo-800">
                                {{ $log->client->full_name }} (ID: #{{ $log->client->id }})
                            </a>
                        @else
                            <p class="text-sm text-gray-400">N/A</p>
                        @endif
                    </div>
                    <div>
                        <span class="text-xs font-bold text-gray-500 uppercase tracking-wider">Related Entity</span>
                        @if($log->loggable)
                            @if($logType === 'Client')
                                <a href="{{ route('clients.show', $log->loggable->id) }}" class="text-sm font-semibold text-indigo-600 hover:text-indigo-800">
                                    {{ $log->loggable->full_name ?? 'View Client' }}
                                </a>
                            @else
                                <span class="text-sm font-semibold text-gray-800">{{ $logType }} #{{ $log->loggable_id }}</span>
                            @endif
                        @else
                            <p class="text-sm text-gray-400">Entity not found</p>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Values Comparison -->
            @if($log->old_values || $log->new_values)
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 border-b border-gray-100">
                        <h3 class="font-bold text-gray-800 text-md">
                            @if($log->action === 'update')
                                Changes (Old Values vs New Values)
                            @elseif($log->action === 'create')
                                Created Values
                            @elseif($log->action === 'delete')
                                Archived Values
                            @else
                                Values
                            @endif
                        </h3>
                    </div>
                    <div class="p-6">
                        @if($log->action === 'update')
                            @php
                                $changesList = [];
                                if ($log->old_values && $log->new_values) {
                                    foreach ($log->new_values as $key => $newVal) {
                                        if (array_key_exists($key, $log->old_values) && $log->old_values[$key] !== $newVal) {
                                            if ($key === 'updated_at') continue;
                                            $changesList[$key] = [
                                                'old' => is_array($log->old_values[$key]) ? json_encode($log->old_values[$key], JSON_PRETTY_PRINT) : ($log->old_values[$key] ?? 'NULL'),
                                                'new' => is_array($newVal) ? json_encode($newVal, JSON_PRETTY_PRINT) : ($newVal ?? 'NULL'),
                                            ];
                                        }
                                    }
                                }
                            @endphp

                            @if(!empty($changesList))
                                <div class="space-y-3">
                                    @foreach($changesList as $field => $diff)
                                        <div class="border border-gray-100 rounded-lg p-3">
                                            <span class="font-bold text-gray-500 text-xs uppercase tracking-wider block mb-2">{{ str_replace('_', ' ', $field) }}</span>
                                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                                <div class="bg-rose-50 border border-rose-100 rounded-lg p-2">
                                                    <span class="text-[10px] font-bold text-rose-500 uppercase tracking-wider block mb-1">Old Value</span>
                                                    <span class="text-sm font-mono text-rose-700 break-all">{{ $diff['old'] }}</span>
                                                </div>
                                                <div class="bg-emerald-50 border border-emerald-100 rounded-lg p-2">
                                                    <span class="text-[10px] font-bold text-emerald-500 uppercase tracking-wider block mb-1">New Value</span>
                                                    <span class="text-sm font-mono text-emerald-700 break-all">{{ $diff['new'] }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-sm text-gray-400 italic">No structural changes detected.</p>
                            @endif

                        @elseif($log->action === 'create')
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                @foreach($log->new_values as $key => $val)
                                    @if(!in_array($key, ['updated_at', 'created_at', 'id']))
                                        <div class="bg-emerald-50/30 border border-emerald-100/50 rounded-lg p-2">
                                            <span class="text-[10px] text-gray-400 capitalize">{{ str_replace('_', ' ', $key) }}</span>
                                            <p class="text-sm font-semibold text-gray-800 break-all">{{ is_array($val) ? json_encode($val) : ($val ?? 'N/A') }}</p>
                                        </div>
                                    @endif
                                @endforeach
                            </div>

                        @elseif($log->action === 'delete')
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                @foreach($log->old_values as $key => $val)
                                    @if(!in_array($key, ['updated_at', 'created_at', 'id']))
                                        <div class="bg-rose-50/30 border border-rose-100/50 rounded-lg p-2">
                                            <span class="text-[10px] text-gray-400 capitalize">{{ str_replace('_', ' ', $key) }}</span>
                                            <p class="text-sm font-semibold text-gray-700 line-through break-all">{{ is_array($val) ? json_encode($val) : ($val ?? 'N/A') }}</p>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        @else
                            <p class="text-sm text-gray-500">No value data available for this log entry.</p>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
