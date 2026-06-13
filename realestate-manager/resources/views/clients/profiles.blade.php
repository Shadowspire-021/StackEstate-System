<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <h2 class="font-extrabold text-2xl bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-600 bg-clip-text text-transparent leading-tight animate-fade-in">
                    {{ __('Client Profiles Directory') }}
                </h2>
            </div>
            
            <div class="flex items-center" style="width: max-content;">
                <!-- Advanced Filters Toggle -->
                <button type="button" id="toggle-filter-btn" style="width: max-content; padding: 8px 16px; border-radius: 12px; display: inline-flex; align-items: center; justify-content: center; font-weight: 700; font-size: 12px;" class="bg-white hover:bg-gray-50 text-gray-700 hover:text-gray-900 border border-gray-200 hover:border-gray-300 shadow-sm transition cursor-pointer">
                    <!-- Inline SVG styled with explicit width/height for complete safety -->
                    <svg width="14" height="14" style="width: 14px; height: 14px; margin-right: 6px; display: inline-block;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
                    </svg>
                    Advanced Filters
                </button>
            </div>
        </div>
    </x-slot>

    <!-- Custom highly highlighted separator styling for Datatables rows with minimal spacing -->
    <style>
        /* Table outer wrapper spacing */
        .table-responsive-custom {
            background-color: #ffffff;
            border-radius: 16px;
            padding: 12px;
            border: 1px solid #f3f4f6;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02);
        }
        
        /* Modern Datatable Structure styling */
        #profiles-table {
            width: 100% !important;
            border-collapse: separate !important;
            border-spacing: 0 4px !important; /* Minimal vertical spacing between list items */
        }
        
        /* Stylized Header Row */
        #profiles-table thead th {
            text-transform: uppercase !important;
            font-size: 10px !important;
            font-weight: 800 !important;
            letter-spacing: 0.1em !important;
            color: #9ca3af !important;
            border-bottom: 2px solid #f3f4f6 !important;
            padding: 10px 14px !important;
        }
        
        /* Stylized Highlighted Separator Row spacing */
        #profiles-table tbody tr {
            background-color: #ffffff !important;
            border: 1px solid #e5e7eb !important; /* Bold, highly highlighted separator borders */
            transition: all 150ms ease-in-out;
        }
        
        /* Highlighted separation borders for list items */
        #profiles-table tbody td {
            padding: 8px 14px !important; /* Minimal vertical space inside list rows */
            font-size: 12px !important;
            font-weight: 600 !important;
            color: #4b5563 !important;
            border-top: 1.5px solid #e5e7eb !important; /* Prominent horizontal separator */
            border-bottom: 1.5px solid #e5e7eb !important; /* Prominent horizontal separator */
            vertical-align: middle !important;
        }
        
        /* Highlighted outer separation border contours */
        #profiles-table tbody td:first-child {
            border-left: 1.5px solid #e5e7eb !important;
            border-top-left-radius: 8px !important;
            border-bottom-left-radius: 8px !important;
        }
        
        #profiles-table tbody td:last-child {
            border-right: 1.5px solid #e5e7eb !important;
            border-top-right-radius: 8px !important;
            border-bottom-right-radius: 8px !important;
        }
        
        /* Hover item highlighting */
        #profiles-table tbody tr:hover {
            background-color: #f9fafb !important;
            transform: translateY(-0.5px);
        }
        
        #profiles-table tbody tr:hover td {
            border-color: #d1d5db !important; /* Darker border highlighting on hover */
            color: #1f2937 !important;
        }

        /* Clean styling for search filters toggle wrapper */
        #filtration-panel label {
            font-size: 9px !important;
            font-weight: 800 !important;
            letter-spacing: 0.05em !important;
            text-transform: uppercase !important;
            color: #9ca3af !important;
            margin-bottom: 4px !important;
            display: block !important;
        }
    </style>

    <div class="py-10 bg-gray-50/50 min-h-screen" style="padding-top: 32px;">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            <!-- Premium Filtration Panel (Advanced) -->
            <div id="filtration-panel" class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 space-y-4" style="display: none;">
                <div class="flex items-center justify-between border-b border-gray-50 pb-3">
                    <div class="flex items-center space-x-2">
                        <span class="h-8 w-8 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center border border-indigo-100">
                            <!-- SVG width/height hardcoded for full reliability -->
                            <svg width="14" height="14" style="width:14px; height:14px; display:inline-block;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
                            </svg>
                        </span>
                        <h3 class="font-bold text-gray-800 text-sm">Advanced Search Options</h3>
                    </div>
                    <button id="reset-filters" class="text-xs font-bold text-indigo-600 hover:text-indigo-700 transition cursor-pointer bg-indigo-50 px-3 py-1.5 rounded-lg border border-indigo-100">
                        Clear All Filters
                    </button>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                    <!-- Date Range Start -->
                    <div>
                        <label>Start Date (Onboarded)</label>
                        <input type="date" id="start_date" class="w-full rounded-xl border-gray-200 focus:border-indigo-500 focus:ring-indigo-500 text-xs font-semibold text-gray-700 py-2">
                    </div>
                    
                    <!-- Date Range End -->
                    <div>
                        <label>End Date (Onboarded)</label>
                        <input type="date" id="end_date" class="w-full rounded-xl border-gray-200 focus:border-indigo-500 focus:ring-indigo-500 text-xs font-semibold text-gray-700 py-2">
                    </div>
                    
                    <!-- CNIC Filter -->
                    <div>
                        <label>CNIC Number</label>
                        <input type="text" id="filter_cnic" placeholder="e.g. 42101-xxxx" class="w-full rounded-xl border-gray-200 focus:border-indigo-500 focus:ring-indigo-500 text-xs font-semibold text-gray-700 py-2">
                    </div>
                    
                    <!-- Plot Number -->
                    <div>
                        <label>Plot Number</label>
                        <input type="text" id="filter_plot" placeholder="e.g. 24356" class="w-full rounded-xl border-gray-200 focus:border-indigo-500 focus:ring-indigo-500 text-xs font-semibold text-gray-700 py-2">
                    </div>
                    
                    <!-- Block Name -->
                    <div>
                        <label>Block / Phase</label>
                        <input type="text" id="filter_block" placeholder="e.g. Block A" class="w-full rounded-xl border-gray-200 focus:border-indigo-500 focus:ring-indigo-500 text-xs font-semibold text-gray-700 py-2">
                    </div>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-2">
                    <!-- Dues Percentage -->
                    <div>
                        <label>Dues Level (Payment Status)</label>
                        <select id="filter_dues" class="w-full rounded-xl border-gray-200 focus:border-indigo-500 focus:ring-indigo-500 text-xs font-semibold text-gray-700 py-2">
                            <option value="">All Clients</option>
                            <option value="fully_paid">Fully Paid (0% Remaining Dues)</option>
                            <option value="low">Low Dues (1% - 30% Dues)</option>
                            <option value="medium">Medium Dues (31% - 70% Dues)</option>
                            <option value="high">High Dues (71% - 99% Dues)</option>
                            <option value="no_payment">No Payments Made (100% Dues)</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Pre-rendered Grid of Cards (Blazing Fast, No Initial Spinner!) -->
            <div class="table-responsive-custom">
                <table id="profiles-table" class="min-w-full">
                    <thead>
                        <tr>
                            <th>Client ID</th>
                            <th>Full Name</th>
                            <th>CNIC</th>
                            <th>Phone</th>
                            <th>Prop Type</th>
                            <th>Plot / Block</th>
                            <th>Deal Value</th>
                            <th>Balance</th>
                            <th>Status</th>
                            <th class="text-right">Profile Link</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Yajra Datatable will render rows here cleanly and accurately -->
                    </tbody>
                </table>
            </div>

        </div>
    </div>

    @push('scripts')
        <!-- Include DataTables stylesheets and scripts directly for 100% stability -->
        <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">
        <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
        <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
        
        <script>
            $(document).ready(function() {
                var table = $('#profiles-table').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: {
                        url: "{{ route('profiles.index') }}",
                        data: function (d) {
                            d.start_date = $('#start_date').val();
                            d.end_date = $('#end_date').val();
                            d.filter_cnic = $('#filter_cnic').val();
                            d.filter_plot = $('#filter_plot').val();
                            d.filter_block = $('#filter_block').val();
                            d.filter_dues = $('#filter_dues').val();
                        }
                    },
                    columns: [
                        { data: 'client_id', name: 'client_id' },
                        { data: 'full_name', name: 'full_name' },
                        { data: 'cnic', name: 'cnic' },
                        { data: 'phone', name: 'phone' },
                        { data: 'property_type', name: 'property_type', orderable: false, searchable: false },
                        { data: 'plot_number', name: 'plot_number', orderable: false, searchable: false },
                        { data: 'total_deal_value', name: 'total_deal_value', orderable: false, searchable: false },
                        { data: 'remaining_balance', name: 'remaining_balance', orderable: false, searchable: false },
                        { data: 'status_badge', name: 'status_badge' },
                        { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-right' }
                    ],
                    order: [[0, 'desc']],
                    pageLength: 10,
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

                // Soft-redraw when custom inputs are modified
                $('#start_date, #end_date, #filter_dues').on('change', function () {
                    table.draw();
                });

                $('#filter_cnic, #filter_plot, #filter_block').on('keyup input', function () {
                    table.draw();
                });

                // Reset search buttons
                $('#reset-filters').on('click', function () {
                    $('#start_date, #end_date, #filter_cnic, #filter_plot, #filter_block, #filter_dues').val('');
                    table.draw();
                });

                // Slide Toggle filters panel
                $('#toggle-filter-btn').on('click', function () {
                    $('#filtration-panel').slideToggle(200);
                });
            });
        </script>
    @endpush
</x-app-layout>
