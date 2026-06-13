<x-app-layout>
    <x-slot name="header">
        <h2 class="font-extrabold text-2xl bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-600 bg-clip-text text-transparent leading-tight">
            {{ __('Edit Client Details') }} ({{ $client->client_id }})
        </h2>
    </x-slot>

    <div class="py-12 bg-gray-50/50 min-h-screen">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            @if($errors->any())
                <div class="mb-6 p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-xl shadow-sm">
                    <div class="font-bold mb-2">Please correct the following errors:</div>
                    <ul class="list-disc list-inside text-sm">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('clients.update', $client->id) }}" method="POST" class="space-y-8">
                @csrf
                @method('PUT')

                <!-- Section 1: Buyer Information -->
                <div class="bg-white p-8 rounded-2xl shadow-sm border border-gray-100 space-y-6">
                    <div class="flex justify-between items-center border-b border-gray-100 pb-3">
                        <h3 class="text-lg font-bold text-gray-800 flex items-center">
                            <span class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center mr-3 text-sm font-bold">1</span>
                            Buyer Information
                        </h3>
                        <div>
                            <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Onboarding Status</label>
                            <select name="status" class="rounded-xl border-gray-200 focus:border-indigo-500 focus:ring-indigo-500 text-xs py-1 font-semibold uppercase tracking-wider">
                                <option value="active" {{ old('status', $client->status) == 'active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ old('status', $client->status) == 'inactive' ? 'selected' : '' }}>Inactive</option>
                                <option value="completed" {{ old('status', $client->status) == 'completed' ? 'selected' : '' }}>Completed</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Salutation</label>
                            <select name="salutation" class="w-full rounded-xl border-gray-200 focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                <option value="Mr." {{ old('salutation', $client->salutation) == 'Mr.' ? 'selected' : '' }}>Mr.</option>
                                <option value="Mrs." {{ old('salutation', $client->salutation) == 'Mrs.' ? 'selected' : '' }}>Mrs.</option>
                                <option value="Ms." {{ old('salutation', $client->salutation) == 'Ms.' ? 'selected' : '' }}>Ms.</option>
                                <option value="Dr." {{ old('salutation', $client->salutation) == 'Dr.' ? 'selected' : '' }}>Dr.</option>
                                <option value="Eng." {{ old('salutation', $client->salutation) == 'Eng.' ? 'selected' : '' }}>Eng.</option>
                            </select>
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Full Name</label>
                            <input type="text" name="full_name" value="{{ old('full_name', $client->full_name) }}" class="w-full rounded-xl border-gray-200 focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Relationship</label>
                            <select name="father_husband_salutation" class="w-full rounded-xl border-gray-200 focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                <option value="S/O" {{ old('father_husband_salutation', $client->father_husband_salutation) == 'S/O' ? 'selected' : '' }}>S/O (Son Of)</option>
                                <option value="D/O" {{ old('father_husband_salutation', $client->father_husband_salutation) == 'D/O' ? 'selected' : '' }}>D/O (Daughter Of)</option>
                                <option value="W/O" {{ old('father_husband_salutation', $client->father_husband_salutation) == 'W/O' ? 'selected' : '' }}>W/O (Wife Of)</option>
                            </select>
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Father / Husband Name</label>
                            <input type="text" name="father_husband_name" value="{{ old('father_husband_name', $client->father_husband_name) }}" class="w-full rounded-xl border-gray-200 focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">CNIC Number</label>
                            <input type="text" name="cnic" value="{{ old('cnic', $client->cnic) }}" class="w-full rounded-xl border-gray-200 focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Phone Number</label>
                            <input type="text" name="phone" value="{{ old('phone', $client->phone) }}" class="w-full rounded-xl border-gray-200 focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Residential Address</label>
                        <textarea name="residential_address" rows="3" class="w-full rounded-xl border-gray-200 focus:border-indigo-500 focus:ring-indigo-500 text-sm">{{ old('residential_address', $client->residential_address) }}</textarea>
                    </div>
                </div>

                <!-- Section 2: Property Information -->
                <div class="bg-white p-8 rounded-2xl shadow-sm border border-gray-100 space-y-6">
                    <h3 class="text-lg font-bold text-gray-800 border-b border-gray-100 pb-3 flex items-center">
                        <span class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center mr-3 text-sm font-bold">2</span>
                        Property & Deal Information
                    </h3>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Property Type</label>
                            <select name="property_type" class="w-full rounded-xl border-gray-200 focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                <option value="Residential Plot" {{ old('property_type', $client->property->property_type) == 'Residential Plot' ? 'selected' : '' }}>Residential Plot</option>
                                <option value="Commercial Plot" {{ old('property_type', $client->property->property_type) == 'Commercial Plot' ? 'selected' : '' }}>Commercial Plot</option>
                                <option value="House" {{ old('property_type', $client->property->property_type) == 'House' ? 'selected' : '' }}>House</option>
                                <option value="Flat" {{ old('property_type', $client->property->property_type) == 'Flat' ? 'selected' : '' }}>Flat</option>
                                <option value="Shop" {{ old('property_type', $client->property->property_type) == 'Shop' ? 'selected' : '' }}>Shop</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Plot / Unit Number</label>
                            <input type="text" name="plot_number" value="{{ old('plot_number', $client->property->plot_number) }}" class="w-full rounded-xl border-gray-200 focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                        </div>
                        <div class="relative">
                            <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Block / Phase</label>
                            <input 
                                type="text" 
                                name="block_name" 
                                id="block_input"
                                value="{{ old('block_name', $client->property->block_name) }}" 
                                placeholder="Select Block or Type Manually" 
                                autocomplete="off"
                                class="w-full rounded-xl border-gray-200 focus:border-indigo-500 focus:ring-indigo-500 text-sm font-semibold text-gray-800"
                            >
                            <!-- Custom dropdown box with scrollbar style -->
                            <div 
                                id="block_dropdown" 
                                style="max-height: 200px; overflow-y: auto; display: none;"
                                class="absolute z-50 w-full mt-1 bg-white border border-gray-150 rounded-xl shadow-lg divide-y divide-gray-50 focus:outline-none"
                            >
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="md:col-span-2">
                            <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Location / Project Name</label>
                            <input type="text" name="location" value="{{ old('location', $client->property->location) }}" class="w-full rounded-xl border-gray-200 focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Size (Sq. Yards)</label>
                            <input type="number" step="0.1" name="size_sqyards" value="{{ old('size_sqyards', $client->property->size_sqyards) }}" class="w-full rounded-xl border-gray-200 focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Total Deal Value (Rs.)</label>
                            <input type="number" name="total_deal_value" value="{{ old('total_deal_value', $client->property->total_deal_value) }}" class="w-full rounded-xl border-gray-200 focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Agreement Date</label>
                            <input type="date" name="agreement_date" value="{{ old('agreement_date', $client->property->agreement_date) }}" class="w-full rounded-xl border-gray-200 focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Additional Notes</label>
                        <textarea name="notes" rows="3" class="w-full rounded-xl border-gray-200 focus:border-indigo-500 focus:ring-indigo-500 text-sm">{{ old('notes', $client->property->notes) }}</textarea>
                    </div>
                </div>

                <!-- Section 3: Vendor Information -->
                <div class="bg-white p-8 rounded-2xl shadow-sm border border-gray-100 space-y-6">
                    <h3 class="text-lg font-bold text-gray-800 border-b border-gray-100 pb-3 flex items-center">
                        <span class="w-8 h-8 rounded-lg bg-purple-50 text-purple-600 flex items-center justify-center mr-3 text-sm font-bold">3</span>
                        Vendor Information
                    </h3>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Vendor Profile Type</label>
                            <select id="vendor_type_select" name="vendor_type" class="w-full rounded-xl border-gray-200 focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                <option value="default" {{ old('vendor_type', $client->vendor_type) == 'default' ? 'selected' : '' }}>Default Company Vendor</option>
                                <option value="custom" {{ old('vendor_type', $client->vendor_type) == 'custom' ? 'selected' : '' }}>Custom Vendor for this Client</option>
                            </select>
                        </div>
                        <div id="custom_vendor_name_wrapper" class="md:col-span-1" style="display: none;">
                            <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Custom Vendor Name</label>
                            <input type="text" name="vendor_name" value="{{ old('vendor_name', $client->vendor_name) }}" placeholder="e.g. Mr. Haroon" class="w-full rounded-xl border-gray-200 focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                        </div>
                        <div id="custom_vendor_cnic_wrapper" class="md:col-span-1" style="display: none;">
                            <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Custom Vendor CNIC</label>
                            <input type="text" name="vendor_cnic" id="vendor_cnic_input" value="{{ old('vendor_cnic', $client->vendor_cnic) }}" placeholder="e.g. 42101-xxxxxxx-x" class="w-full rounded-xl border-gray-200 focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                        </div>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="flex justify-end space-x-4">
                    <a href="{{ route('clients.show', $client->id) }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-xl text-sm font-semibold text-gray-700 bg-white hover:bg-gray-50 focus:outline-none transition ease-in-out duration-150">
                        Cancel
                    </a>
                    <button type="submit" class="inline-flex items-center px-6 py-2 bg-indigo-600 hover:bg-indigo-500 text-white font-semibold rounded-xl shadow-md transition ease-in-out duration-150 text-sm">
                        Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // CNIC Formatting & Masking
            const cnicInput = document.querySelector('input[name="cnic"]');
            if (cnicInput) {
                cnicInput.setAttribute('maxlength', '15'); // 5 + 1 + 7 + 1 + 1 = 15 characters
                cnicInput.setAttribute('placeholder', 'e.g. 42101-1234567-8');
                
                // Format initially if there is a value
                formatCNIC(cnicInput);
                
                cnicInput.addEventListener('input', function (e) {
                    formatCNIC(this);
                });
            }

            // Phone/Mobile Formatting & Masking
            const phoneInput = document.querySelector('input[name="phone"]');
            if (phoneInput) {
                phoneInput.setAttribute('maxlength', '12'); // 4 + 1 + 7 = 12 characters
                phoneInput.setAttribute('placeholder', 'e.g. 0300-1234567');
                
                // Format initially if there is a value
                formatPhone(phoneInput);
                
                phoneInput.addEventListener('input', function (e) {
                    formatPhone(this);
                });
            }

            function formatCNIC(input) {
                let value = input.value.replace(/\D/g, ''); // Remove all non-digits
                let formatted = '';
                
                if (value.length > 0) {
                    formatted += value.substring(0, 5);
                }
                if (value.length > 5) {
                    formatted += '-' + value.substring(5, 12);
                }
                if (value.length > 12) {
                    formatted += '-' + value.substring(12, 13);
                }
                
                input.value = formatted;
            }

            function formatPhone(input) {
                let value = input.value.replace(/\D/g, ''); // Remove all non-digits
                let formatted = '';
                
                if (value.length > 0) {
                    formatted += value.substring(0, 4);
                }
                if (value.length > 4) {
                    formatted += '-' + value.substring(4, 11);
                }
                
                input.value = formatted;
            }
            
            // Custom Autocomplete Dropdown for Block / Phase
            const blockInput = document.getElementById('block_input');
            const blockDropdown = document.getElementById('block_dropdown');
            
            if (blockInput && blockDropdown) {
                const blocksList = Array.from({length: 26}, (_, i) => 'Block ' + String.fromCharCode(65 + i)); // Block A to Z
                
                // Show dropdown on click or focus
                blockInput.addEventListener('focus', showDropdown);
                blockInput.addEventListener('click', showDropdown);
                
                // Filter dropdown on input
                blockInput.addEventListener('input', function() {
                    const filter = this.value.toLowerCase().trim();
                    renderDropdown(filter);
                });
                
                // Close dropdown when clicking outside
                document.addEventListener('click', function(e) {
                    if (!blockInput.contains(e.target) && !blockDropdown.contains(e.target)) {
                        blockDropdown.style.display = 'none';
                    }
                });
                
                function showDropdown() {
                    blockDropdown.style.display = 'block';
                    renderDropdown(blockInput.value.toLowerCase().trim());
                }
                
                function renderDropdown(filter = '') {
                    blockDropdown.innerHTML = '';
                    const matched = blocksList.filter(b => b.toLowerCase().includes(filter));
                    
                    if (matched.length > 0) {
                        matched.forEach(block => {
                            const option = document.createElement('div');
                            option.className = 'px-4 py-2.5 hover:bg-indigo-50/50 cursor-pointer text-sm font-semibold text-gray-700 transition duration-150';
                            option.textContent = block;
                            option.addEventListener('click', function() {
                                blockInput.value = block;
                                blockDropdown.style.display = 'none';
                            });
                            blockDropdown.appendChild(option);
                        });
                    } else {
                        const noMatch = document.createElement('div');
                        noMatch.className = 'px-4 py-2.5 text-xs font-semibold text-gray-400 italic';
                        noMatch.textContent = 'Custom block typed';
                        blockDropdown.appendChild(noMatch);
                    }
                }
            }

            // Toggle custom vendor details based on selection
            const vendorTypeSelect = document.getElementById('vendor_type_select');
            const customVendorNameWrapper = document.getElementById('custom_vendor_name_wrapper');
            const customVendorCnicWrapper = document.getElementById('custom_vendor_cnic_wrapper');

            function toggleVendorFields() {
                if (vendorTypeSelect.value === 'custom') {
                    customVendorNameWrapper.style.display = 'block';
                    customVendorCnicWrapper.style.display = 'block';
                } else {
                    customVendorNameWrapper.style.display = 'none';
                    customVendorCnicWrapper.style.display = 'none';
                }
            }

            if (vendorTypeSelect) {
                vendorTypeSelect.addEventListener('change', toggleVendorFields);
                toggleVendorFields(); // Initial run
            }

            // Vendor CNIC Formatting & Masking
            const vendorCnicInput = document.getElementById('vendor_cnic_input');
            if (vendorCnicInput) {
                vendorCnicInput.setAttribute('maxlength', '15');
                formatCNIC(vendorCnicInput);
                vendorCnicInput.addEventListener('input', function (e) {
                    formatCNIC(this);
                });
            }
        });
    </script>
</x-app-layout>
