<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Global Search') }}
            </h2>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Search Form -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <form action="{{ route('search') }}" method="GET" class="flex gap-3">
                        <input type="text" name="q" value="{{ $term }}"
                               placeholder="Search by name, CNIC, phone, client ID, plot number, unit number..."
                               class="flex-1 rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                        <button type="submit"
                                class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm font-semibold hover:bg-indigo-500 transition">
                            Search
                        </button>
                    </form>
                </div>
            </div>

            @if(!empty($term))
                <!-- Clients Results -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6 border-b border-gray-100">
                        <h3 class="text-lg font-bold text-gray-800">
                            Clients
                            <span class="ml-2 text-xs font-semibold text-gray-500">({{ $results['clients']->count() }} results)</span>
                        </h3>
                    </div>
                    <div class="p-6">
                        @if($results['clients']->isEmpty())
                            <p class="text-sm text-gray-500">No clients found matching "{{ $term }}".</p>
                        @else
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Client</th>
                                            <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">CNIC</th>
                                            <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Phone</th>
                                            <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Property</th>
                                            <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-100">
                                        @foreach($results['clients'] as $client)
                                            <tr class="hover:bg-gray-50">
                                                <td class="px-4 py-3 text-sm font-semibold text-gray-800">{{ $client->full_name }}</td>
                                                <td class="px-4 py-3 text-sm text-gray-600">{{ $client->cnic }}</td>
                                                <td class="px-4 py-3 text-sm text-gray-600">{{ $client->phone }}</td>
                                                <td class="px-4 py-3 text-sm text-gray-600">
                                                    {{ $client->property ? $client->property->plot_number : 'N/A' }}
                                                </td>
                                                <td class="px-4 py-3 text-sm">
                                                    <a href="{{ route('clients.show', $client->id) }}"
                                                       class="inline-flex items-center px-3 py-1 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 font-extrabold rounded-lg text-[10px] uppercase tracking-wider transition border border-indigo-100/30">
                                                        View
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Properties Results -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6 border-b border-gray-100">
                        <h3 class="text-lg font-bold text-gray-800">
                            Properties
                            <span class="ml-2 text-xs font-semibold text-gray-500">({{ $results['properties']->count() }} results)</span>
                        </h3>
                    </div>
                    <div class="p-6">
                        @if($results['properties']->isEmpty())
                            <p class="text-sm text-gray-500">No properties found matching "{{ $term }}".</p>
                        @else
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Plot</th>
                                            <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Block</th>
                                            <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Type</th>
                                            <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Client</th>
                                            <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Deal Value</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-100">
                                        @foreach($results['properties'] as $property)
                                            <tr class="hover:bg-gray-50">
                                                <td class="px-4 py-3 text-sm font-semibold text-gray-800">{{ $property->plot_number }}</td>
                                                <td class="px-4 py-3 text-sm text-gray-600">{{ $property->block_name ?: 'N/A' }}</td>
                                                <td class="px-4 py-3 text-sm text-gray-600">{{ $property->property_type }}</td>
                                                <td class="px-4 py-3 text-sm text-gray-600">
                                                    {{ $property->client ? $property->client->full_name : 'N/A' }}
                                                </td>
                                                <td class="px-4 py-3 text-sm text-gray-600">
                                                    Rs. {{ number_format($property->total_deal_value) }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Units Results -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 border-b border-gray-100">
                        <h3 class="text-lg font-bold text-gray-800">
                            Units
                            <span class="ml-2 text-xs font-semibold text-gray-500">({{ $results['units']->count() }} results)</span>
                        </h3>
                    </div>
                    <div class="p-6">
                        @if($results['units']->isEmpty())
                            <p class="text-sm text-gray-500">No units found matching "{{ $term }}".</p>
                        @else
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Unit #</th>
                                            <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Floor</th>
                                            <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Size</th>
                                            <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Price</th>
                                            <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Status</th>
                                            <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Property</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-100">
                                        @foreach($results['units'] as $unit)
                                            <tr class="hover:bg-gray-50">
                                                <td class="px-4 py-3 text-sm font-semibold text-gray-800">{{ $unit->unit_number }}</td>
                                                <td class="px-4 py-3 text-sm text-gray-600">{{ $unit->floor_number ?: 'N/A' }}</td>
                                                <td class="px-4 py-3 text-sm text-gray-600">{{ $unit->size ?: 'N/A' }}</td>
                                                <td class="px-4 py-3 text-sm text-gray-600">Rs. {{ number_format($unit->price) }}</td>
                                                <td class="px-4 py-3 text-sm">
                                                    @php
                                                        $statusColors = [
                                                            'available' => 'bg-emerald-100 text-emerald-700',
                                                            'booked' => 'bg-amber-100 text-amber-700',
                                                            'sold' => 'bg-blue-100 text-blue-700',
                                                            'reserved' => 'bg-purple-100 text-purple-700',
                                                        ];
                                                        $colorClass = $statusColors[$unit->status] ?? 'bg-gray-100 text-gray-700';
                                                    @endphp
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold {{ $colorClass }}">
                                                        {{ ucfirst($unit->status) }}
                                                    </span>
                                                </td>
                                                <td class="px-4 py-3 text-sm text-gray-600">
                                                    {{ $unit->property ? $unit->property->plot_number : 'N/A' }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
            @else
                <!-- Empty State -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-12 text-center">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                        <h3 class="mt-2 text-sm font-semibold text-gray-900">No search term</h3>
                        <p class="mt-1 text-sm text-gray-500">Enter a search term to find clients, properties, and units.</p>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
