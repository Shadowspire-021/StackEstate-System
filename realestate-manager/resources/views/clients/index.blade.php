<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center py-2">
            <h2 class="font-extrabold text-2xl bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-600 bg-clip-text text-transparent leading-tight">
                {{ __('Client Management') }}
            </h2>
            <div class="flex items-center space-x-3" style="width: max-content;">
                <!-- Filters Toggle Button -->
                <button type="button" id="toggle-filter-btn" style="width: max-content; padding: 8px 16px; border-radius: 12px; display: inline-flex; align-items: center; justify-content: center; font-weight: 700; font-size: 12px;" class="bg-white hover:bg-gray-50 text-gray-700 hover:text-gray-900 border border-gray-200 hover:border-gray-300 shadow-sm transition cursor-pointer">
                    <!-- Inline SVG styled with explicit width/height for complete safety -->
                    <svg width="14" height="14" style="width: 14px; height: 14px; margin-right: 6px; display: inline-block;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
                    </svg>
                    Filters
                </button>
                <a href="{{ route('clients.create') }}" style="width: max-content; padding: 8px 16px; border-radius: 12px; display: inline-flex; align-items: center; justify-content: center; font-weight: 700; font-size: 12px;" class="bg-indigo-600 hover:bg-indigo-500 text-white shadow-sm transition-all duration-200">
                    <svg width="14" height="14" style="width: 14px; height: 14px; margin-right: 6px; display: inline-block;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Onboard New Client
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-10 bg-gray-50/50 min-h-screen" style="padding-top: 32px;">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl flex items-center shadow-sm">
                    <svg class="w-5 h-5 mr-2 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    {{ session('success') }}
                </div>
            @endif

            <!-- Premium Filtration Panel -->
            <div id="filtration-panel" class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 mb-6 space-y-4" style="display: none;">
                <div class="flex items-center justify-between border-b border-gray-50 pb-3">
                    <div class="flex items-center space-x-2">
                        <span class="h-8 w-8 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center border border-indigo-100">
                            <svg width="14" height="14" style="width:14px; height:14px; display:inline-block;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
                            </svg>
                        </span>
                        <h3 class="font-bold text-gray-800 text-sm">Advanced Search & Filtration</h3>
                    </div>
                    <button id="reset-filters" class="text-xs font-bold text-indigo-600 hover:text-indigo-700 transition cursor-pointer bg-indigo-50 px-3 py-1.5 rounded-lg border border-indigo-100">
                        Reset All Filters
                    </button>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-6 gap-4">
                    <!-- Name Search -->
                    <div>
                        <label class="block text-[9px] font-bold text-gray-400 uppercase tracking-wider mb-1.5">Client Name</label>
                        <input type="text" id="filter_name" placeholder="e.g. Ahmed Khan" class="w-full rounded-xl border-gray-200 focus:border-indigo-500 focus:ring-indigo-500 text-xs font-semibold text-gray-700 py-2">
                    </div>

                    <!-- Phone Search -->
                    <div>
                        <label class="block text-[9px] font-bold text-gray-400 uppercase tracking-wider mb-1.5">Phone Number</label>
                        <input type="text" id="filter_phone" placeholder="e.g. 0300-xxxx" class="w-full rounded-xl border-gray-200 focus:border-indigo-500 focus:ring-indigo-500 text-xs font-semibold text-gray-700 py-2">
                    </div>

                    <!-- CNIC Filter -->
                    <div>
                        <label class="block text-[9px] font-bold text-gray-400 uppercase tracking-wider mb-1.5">CNIC</label>
                        <input type="text" id="filter_cnic" placeholder="e.g. 42101-xxxx" class="w-full rounded-xl border-gray-200 focus:border-indigo-500 focus:ring-indigo-500 text-xs font-semibold text-gray-700 py-2">
                    </div>
                    
                    <!-- Plot Number -->
                    <div>
                        <label class="block text-[9px] font-bold text-gray-400 uppercase tracking-wider mb-1.5">Plot Number</label>
                        <input type="text" id="filter_plot" placeholder="e.g. 24356" class="w-full rounded-xl border-gray-200 focus:border-indigo-500 focus:ring-indigo-500 text-xs font-semibold text-gray-700 py-2">
                    </div>
                    
                    <!-- Block Name -->
                    <div>
                        <label class="block text-[9px] font-bold text-gray-400 uppercase tracking-wider mb-1.5">Block / Phase</label>
                        <input type="text" id="filter_block" placeholder="e.g. Block A" class="w-full rounded-xl border-gray-200 focus:border-indigo-500 focus:ring-indigo-500 text-xs font-semibold text-gray-700 py-2">
                    </div>
                    
                    <!-- Unit Number -->
                    <div>
                        <label class="block text-[9px] font-bold text-gray-400 uppercase tracking-wider mb-1.5">Unit Number</label>
                        <input type="text" id="filter_unit" placeholder="e.g. Flat 101" class="w-full rounded-xl border-gray-200 focus:border-indigo-500 focus:ring-indigo-500 text-xs font-semibold text-gray-700 py-2">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 pt-2">
                    <!-- Date Range Start -->
                    <div>
                        <label class="block text-[9px] font-bold text-gray-400 uppercase tracking-wider mb-1.5">Start Date</label>
                        <input type="date" id="start_date" class="w-full rounded-xl border-gray-200 focus:border-indigo-500 focus:ring-indigo-500 text-xs font-semibold text-gray-700 py-2">
                    </div>

                    <!-- Date Range End -->
                    <div>
                        <label class="block text-[9px] font-bold text-gray-400 uppercase tracking-wider mb-1.5">End Date</label>
                        <input type="date" id="end_date" class="w-full rounded-xl border-gray-200 focus:border-indigo-500 focus:ring-indigo-500 text-xs font-semibold text-gray-700 py-2">
                    </div>

                    <!-- Dues Percentage -->
                    <div>
                        <label class="block text-[9px] font-bold text-gray-400 uppercase tracking-wider mb-1.5">Dues Level</label>
                        <select id="filter_dues" class="w-full rounded-xl border-gray-200 focus:border-indigo-500 focus:ring-indigo-500 text-xs font-semibold text-gray-750 py-2">
                            <option value="">All Clients</option>
                            <option value="fully_paid">Fully Paid (0% Dues)</option>
                            <option value="low">Low Dues (1% - 30%)</option>
                            <option value="medium">Medium Dues (31% - 70%)</option>
                            <option value="high">High Dues (71% - 99%)</option>
                            <option value="no_payment">No Payments Made (100% Dues)</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Custom Separator-Highlighted Table View -->
            <div class="table-responsive-custom">
                <table id="clients-table" class="min-w-full">
                    <thead>
                        <tr>
                            <th>Client ID</th>
                            <th>Full Name</th>
                            <th>CNIC</th>
                            <th>Phone</th>
                            <th>Prop Type</th>
                            <th>Plot / Block</th>
                            <th>Unit</th>
                            <th>Deal Value</th>
                            <th>Balance</th>
                            <th>Status</th>
                            <th class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Populated dynamically via DataTables -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Styles & DataTables JS -->
    @push('styles')
        <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">
        <style>
            /* Custom Table Container Wrapper spacing */
            .table-responsive-custom {
                background-color: #ffffff;
                border-radius: 16px;
                padding: 12px;
                border: 1px solid #f3f4f6;
                box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02);
            }
            
            /* Custom Tabular layout specs */
            #clients-table {
                width: 100% !important;
                border-collapse: separate !important;
                border-spacing: 0 4px !important; /* Minimal vertical spacing between list items */
            }
            
            /* Modernized Custom Header th */
            #clients-table thead th {
                text-transform: uppercase !important;
                font-size: 10px !important;
                font-weight: 800 !important;
                letter-spacing: 0.1em !important;
                color: #9ca3af !important;
                border-bottom: 2px solid #f3f4f6 !important;
                padding: 10px 14px !important;
            }
            
            /* Stylized Highlighted Separator Row spacing */
            #clients-table tbody tr {
                background-color: #ffffff !important;
                border: 1px solid #e5e7eb !important; /* Bold, highly highlighted separator borders */
                transition: all 150ms ease-in-out;
            }
            
            /* Highlighted separation borders for list items */
            #clients-table tbody td {
                padding: 8px 14px !important; /* Minimal vertical space inside list rows */
                font-size: 12px !important;
                font-weight: 600 !important;
                color: #4b5563 !important;
                border-top: 1.5px solid #e5e7eb !important; /* Prominent horizontal separator */
                border-bottom: 1.5px solid #e5e7eb !important; /* Prominent horizontal separator */
                vertical-align: middle !important;
            }
            
            /* Highlighted outer separation border contours */
            #clients-table tbody td:first-child {
                border-left: 1.5px solid #e5e7eb !important;
                border-top-left-radius: 8px !important;
                border-bottom-left-radius: 8px !important;
            }
            
            #clients-table tbody td:last-child {
                border-right: 1.5px solid #e5e7eb !important;
                border-top-right-radius: 8px !important;
                border-bottom-right-radius: 8px !important;
            }
            
            /* Hover item highlighting */
            #clients-table tbody tr:hover {
                background-color: #f9fafb !important;
                transform: translateY(-0.5px);
            }
            
            #clients-table tbody tr:hover td {
                border-color: #d1d5db !important; /* Darker border highlighting on hover */
                color: #1f2937 !important;
            }

            /* Custom DataTables Component styling overrides */
            .dataTables_wrapper .dataTables_paginate .paginate_button.current {
                background: #4f46e5 !important;
                color: #fff !important;
                border: 1px solid #4f46e5 !important;
                border-radius: 8px !important;
            }
            .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
                background: #818cf8 !important;
                color: #fff !important;
                border: 1px solid #818cf8 !important;
                border-radius: 8px !important;
            }
            .dataTables_wrapper .dataTables_filter input {
                border: 1px solid #e5e7eb !important;
                border-radius: 8px !important;
                padding: 6px 12px !important;
                outline: none !important;
                margin-bottom: 15px !important;
            }
            .dataTables_wrapper .dataTables_length select {
                border: 1px solid #e5e7eb !important;
                border-radius: 8px !important;
                padding: 4px 8px !important;
            }
            table.dataTable tbody tr {
                background-color: transparent !important;
            }
            table.dataTable.no-footer {
                border-bottom: none !important;
            }
        </style>
    @endpush

    @push('scripts')
        <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
        <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
        <script>
            $(document).ready(function() {
                const table = $('#clients-table').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: {
                        url: "{{ route('clients.index') }}",
                        data: function (d) {
                            d.start_date = $('#start_date').val();
                            d.end_date = $('#end_date').val();
                            d.filter_name = $('#filter_name').val();
                            d.filter_phone = $('#filter_phone').val();
                            d.filter_cnic = $('#filter_cnic').val();
                            d.filter_plot = $('#filter_plot').val();
                            d.filter_block = $('#filter_block').val();
                            d.filter_unit = $('#filter_unit').val();
                            d.filter_dues = $('#filter_dues').val();
                        }
                    },
                    columns: [
                        { data: 'client_id', name: 'client_id', className: 'font-bold text-gray-800' },
                        { data: 'full_name', name: 'full_name', className: 'font-semibold text-gray-700' },
                        { data: 'cnic', name: 'cnic', className: 'text-gray-500' },
                        { data: 'phone', name: 'phone', className: 'text-gray-500' },
                        { data: 'property_type', name: 'property_type', className: 'text-gray-500' },
                        { data: 'plot_number', name: 'plot_number', className: 'text-gray-500' },
                        { data: 'unit_number', name: 'unit_number', orderable: false, searchable: false },
                        { data: 'total_deal_value', name: 'total_deal_value', className: 'font-semibold text-indigo-600' },
                        { data: 'remaining_balance', name: 'remaining_balance' },
                        { data: 'status_badge', name: 'status_badge' },
                        { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-right overflow-visible' }
                    ],
                    dom: '<"flex justify-between items-center mb-4"<"text-xs font-bold text-gray-400 uppercase tracking-wider"l><"relative"f>>t<"flex justify-between items-center mt-4"<"text-xs font-bold text-gray-400"i><"text-xs font-bold"p>>',
                    language: {
                        searchPlaceholder: "Quick filter columns...",
                        search: "",
                        lengthMenu: "Show _MENU_ records",
                        paginate: {
                            previous: "Prev",
                            next: "Next"
                        }
                    }
                });

                // Trigger reload on filter inputs change
                $('#filter_name, #filter_phone, #filter_cnic, #filter_plot, #filter_block, #filter_unit').on('keyup input', function () {
                    table.draw();
                });

                $('#start_date, #end_date, #filter_dues').on('change', function () {
                    table.draw();
                });

                // Reset all filters button logic
                $('#reset-filters').on('click', function () {
                    $('#start_date, #end_date, #filter_name, #filter_phone, #filter_cnic, #filter_plot, #filter_block, #filter_unit, #filter_dues').val('');
                    table.draw();
                });
                // Toggle filtration panel slide
                $('#toggle-filter-btn').on('click', function () {
                    $('#filtration-panel').slideToggle(250);
                });
            });

            // Global dropdown toggle helper
            window.toggleDropdown = function(event, btn) {
                event.stopPropagation();
                
                const menu = btn.nextElementSibling;
                const allMenus = document.querySelectorAll('.dropdown-menu');
                
                // Hide all other dropdowns
                allMenus.forEach(el => {
                    if (el !== menu) {
                        el.classList.add('hidden');
                    }
                });
                
                // Toggle current dropdown
                if (menu) {
                    menu.classList.toggle('hidden');
                }
            };
            
            // Close dropdowns when clicking anywhere outside
            document.addEventListener('click', function() {
                document.querySelectorAll('.dropdown-menu').forEach(el => {
                    el.classList.add('hidden');
                });
            });
        </script>
    @endpush
</x-app-layout>
