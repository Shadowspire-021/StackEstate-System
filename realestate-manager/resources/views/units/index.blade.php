<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <h2 class="font-extrabold text-2xl bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-600 bg-clip-text text-transparent leading-tight">
                {{ __('Unit Inventory') }}
            </h2>
            <a href="{{ route('units.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white font-semibold rounded-xl shadow-sm transition-all duration-200">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                Add New Unit
            </a>
        </div>
    </x-slot>

    <div class="py-12 bg-gray-50/50 min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <!-- Stats -->
            <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
                <div class="bg-white p-4 rounded-2xl shadow-sm border border-gray-100">
                    <div class="text-2xl font-black text-gray-800">{{ $stats['total'] }}</div>
                    <div class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Total Units</div>
                </div>
                <div class="bg-white p-4 rounded-2xl shadow-sm border border-gray-100">
                    <div class="text-2xl font-black text-emerald-600">{{ $stats['available'] }}</div>
                    <div class="text-[10px] font-bold text-emerald-600 uppercase tracking-wider">Available</div>
                </div>
                <div class="bg-white p-4 rounded-2xl shadow-sm border border-gray-100">
                    <div class="text-2xl font-black text-amber-600">{{ $stats['booked'] }}</div>
                    <div class="text-[10px] font-bold text-amber-600 uppercase tracking-wider">Booked</div>
                </div>
                <div class="bg-white p-4 rounded-2xl shadow-sm border border-gray-100">
                    <div class="text-2xl font-black text-blue-600">{{ $stats['sold'] }}</div>
                    <div class="text-[10px] font-bold text-blue-600 uppercase tracking-wider">Sold</div>
                </div>
                <div class="bg-white p-4 rounded-2xl shadow-sm border border-gray-100">
                    <div class="text-2xl font-black text-purple-600">{{ $stats['reserved'] }}</div>
                    <div class="text-[10px] font-bold text-purple-600 uppercase tracking-wider">Reserved</div>
                </div>
            </div>

            <!-- Filters -->
            <div class="bg-white p-4 rounded-2xl shadow-sm border border-gray-100">
                <div class="flex flex-wrap items-center gap-3">
                    <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Filter by Status:</span>
                    <button onclick="filterUnits('all')" class="filter-btn px-4 py-2 rounded-xl text-sm font-semibold transition-all duration-200 bg-indigo-100 text-indigo-700" data-status="all">All</button>
                    <button onclick="filterUnits('available')" class="filter-btn px-4 py-2 rounded-xl text-sm font-semibold transition-all duration-200 bg-gray-100 text-gray-600 hover:bg-emerald-50 hover:text-emerald-700" data-status="available">Available</button>
                    <button onclick="filterUnits('booked')" class="filter-btn px-4 py-2 rounded-xl text-sm font-semibold transition-all duration-200 bg-gray-100 text-gray-600 hover:bg-amber-50 hover:text-amber-700" data-status="booked">Booked</button>
                    <button onclick="filterUnits('sold')" class="filter-btn px-4 py-2 rounded-xl text-sm font-semibold transition-all duration-200 bg-gray-100 text-gray-600 hover:bg-blue-50 hover:text-blue-700" data-status="sold">Sold</button>
                    <button onclick="filterUnits('reserved')" class="filter-btn px-4 py-2 rounded-xl text-sm font-semibold transition-all duration-200 bg-gray-100 text-gray-600 hover:bg-purple-50 hover:text-purple-700" data-status="reserved">Reserved</button>
                </div>
            </div>

            <!-- DataTable -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="p-6 border-b border-gray-100">
                    <h3 class="text-lg font-bold text-gray-800">All Units</h3>
                </div>
                <div class="overflow-x-auto">
                    <table id="units-table" class="w-full text-left">
                        <thead>
                            <tr class="border-b border-gray-100 text-gray-400 text-xs font-semibold uppercase tracking-wider">
                                <th class="px-6 py-3">Unit #</th>
                                <th class="px-6 py-3">Property</th>
                                <th class="px-6 py-3">Floor</th>
                                <th class="px-6 py-3">Size</th>
                                <th class="px-6 py-3">Price</th>
                                <th class="px-6 py-3">Status</th>
                                <th class="px-6 py-3">Created</th>
                                <th class="px-6 py-3">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        let table;

        document.addEventListener('DOMContentLoaded', function() {
            table = $('#units-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ route("units.index") }}',
                    data: function(d) {
                        d.status = currentStatus;
                    }
                },
                columns: [
                    { data: 'unit_number', name: 'unit_number', className: 'px-6 py-4 font-bold text-gray-800' },
                    { data: 'property_info', name: 'property_info', className: 'px-6 py-4 text-gray-600' },
                    { data: 'floor_number', name: 'floor_number', className: 'px-6 py-4 text-gray-600', defaultContent: 'N/A' },
                    { data: 'size', name: 'size', className: 'px-6 py-4 text-gray-600', render: function(data) { return data ? data + ' sq.yd' : 'N/A'; } },
                    { data: 'price_formatted', name: 'price', className: 'px-6 py-4 font-bold text-indigo-600' },
                    { data: 'status_badge', name: 'status', orderable: false, searchable: false, className: 'px-6 py-4' },
                    { data: 'created_at', name: 'created_at', className: 'px-6 py-4 text-gray-500', render: function(data) { return new Date(data).toLocaleDateString(); } },
                    { data: 'action', name: 'action', orderable: false, searchable: false, className: 'px-6 py-4' }
                ],
                order: [[0, 'desc']],
                pageLength: 15,
                lengthMenu: [10, 15, 25, 50]
            });
        });

        let currentStatus = 'all';

        function filterUnits(status) {
            currentStatus = status;
            table.ajax.reload();

            // Update active button style
            document.querySelectorAll('.filter-btn').forEach(btn => {
                if (btn.dataset.status === status) {
                    btn.className = 'filter-btn px-4 py-2 rounded-xl text-sm font-semibold transition-all duration-200 bg-indigo-100 text-indigo-700';
                } else {
                    btn.className = 'filter-btn px-4 py-2 rounded-xl text-sm font-semibold transition-all duration-200 bg-gray-100 text-gray-600 hover:bg-emerald-50 hover:text-emerald-700';
                }
            });
        }
    </script>
    @endpush
</x-app-layout>
