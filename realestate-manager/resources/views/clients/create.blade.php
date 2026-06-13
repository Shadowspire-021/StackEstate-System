<x-app-layout>
    <x-slot name="header">
        <h2 class="font-extrabold text-2xl bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-600 bg-clip-text text-transparent leading-tight">
            {{ __('Onboard New Client') }}
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

            <form action="{{ route('clients.store') }}" method="POST" class="space-y-8">
                @csrf

                <!-- Section 1: Buyer Information -->
                <div class="bg-white p-8 rounded-2xl shadow-sm border border-gray-100 space-y-6">
                    <h3 class="text-lg font-bold text-gray-800 border-b border-gray-100 pb-3 flex items-center">
                        <span class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center mr-3 text-sm font-bold">1</span>
                        Buyer Information
                    </h3>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Salutation</label>
                            <select name="salutation" class="w-full rounded-xl border-gray-200 focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                <option value="Mr." {{ old('salutation') == 'Mr.' ? 'selected' : '' }}>Mr.</option>
                                <option value="Mrs." {{ old('salutation') == 'Mrs.' ? 'selected' : '' }}>Mrs.</option>
                                <option value="Ms." {{ old('salutation') == 'Ms.' ? 'selected' : '' }}>Ms.</option>
                                <option value="Dr." {{ old('salutation') == 'Dr.' ? 'selected' : '' }}>Dr.</option>
                                <option value="Eng." {{ old('salutation') == 'Eng.' ? 'selected' : '' }}>Eng.</option>
                            </select>
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Full Name</label>
                            <input type="text" name="full_name" value="{{ old('full_name') }}" placeholder="John Doe" class="w-full rounded-xl border-gray-200 focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Relationship</label>
                            <select name="father_husband_salutation" class="w-full rounded-xl border-gray-200 focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                <option value="S/O" {{ old('father_husband_salutation') == 'S/O' ? 'selected' : '' }}>S/O (Son Of)</option>
                                <option value="D/O" {{ old('father_husband_salutation') == 'D/O' ? 'selected' : '' }}>D/O (Daughter Of)</option>
                                <option value="W/O" {{ old('father_husband_salutation') == 'W/O' ? 'selected' : '' }}>W/O (Wife Of)</option>
                            </select>
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Father / Husband Name</label>
                            <input type="text" name="father_husband_name" value="{{ old('father_husband_name') }}" placeholder="Richard Doe" class="w-full rounded-xl border-gray-200 focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">CNIC Number</label>
                            <input type="text" name="cnic" value="{{ old('cnic') }}" placeholder="42101-1234567-8" class="w-full rounded-xl border-gray-200 focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Phone Number</label>
                            <input type="text" name="phone" value="{{ old('phone') }}" placeholder="+92 300 1234567" class="w-full rounded-xl border-gray-200 focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Residential Address</label>
                        <textarea name="residential_address" rows="3" placeholder="Apartment, Street Name, City, Country" class="w-full rounded-xl border-gray-200 focus:border-indigo-500 focus:ring-indigo-500 text-sm">{{ old('residential_address') }}</textarea>
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
                                <option value="Residential Plot" {{ old('property_type') == 'Residential Plot' ? 'selected' : '' }}>Residential Plot</option>
                                <option value="Commercial Plot" {{ old('property_type') == 'Commercial Plot' ? 'selected' : '' }}>Commercial Plot</option>
                                <option value="House" {{ old('property_type') == 'House' ? 'selected' : '' }}>House</option>
                                <option value="Flat" {{ old('property_type') == 'Flat' ? 'selected' : '' }}>Flat</option>
                                <option value="Shop" {{ old('property_type') == 'Shop' ? 'selected' : '' }}>Shop</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Plot / Unit Number</label>
                            <input type="text" name="plot_number" value="{{ old('plot_number') }}" placeholder="Plot 123" class="w-full rounded-xl border-gray-200 focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                        </div>
                        <div class="relative">
                            <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Block / Phase</label>
                            <input 
                                type="text" 
                                name="block_name" 
                                id="block_input"
                                value="{{ old('block_name') }}" 
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
                            <input type="text" name="location" value="{{ old('location') }}" placeholder="Green Valley Society, Karachi" class="w-full rounded-xl border-gray-200 focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Size (Sq. Yards)</label>
                            <input type="number" step="0.1" name="size_sqyards" value="{{ old('size_sqyards') }}" placeholder="120" class="w-full rounded-xl border-gray-200 focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Total Deal Value (Rs.)</label>
                            <input type="number" name="total_deal_value" value="{{ old('total_deal_value') }}" placeholder="15000000" class="w-full rounded-xl border-gray-200 focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Agreement Date</label>
                            <input type="date" name="agreement_date" value="{{ old('agreement_date') }}" class="w-full rounded-xl border-gray-200 focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Additional Notes</label>
                        <textarea name="notes" rows="3" placeholder="Installment structure, special conditions..." class="w-full rounded-xl border-gray-200 focus:border-indigo-500 focus:ring-indigo-500 text-sm">{{ old('notes') }}</textarea>
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
                                <option value="default" {{ old('vendor_type') == 'default' ? 'selected' : '' }}>Default Company Vendor</option>
                                <option value="custom" {{ old('vendor_type') == 'custom' ? 'selected' : '' }}>Custom Vendor for this Client</option>
                            </select>
                        </div>
                        <div id="custom_vendor_name_wrapper" class="md:col-span-1" style="display: none;">
                            <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Custom Vendor Name</label>
                            <input type="text" name="vendor_name" value="{{ old('vendor_name') }}" placeholder="e.g. Mr. Haroon" class="w-full rounded-xl border-gray-200 focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                        </div>
                        <div id="custom_vendor_cnic_wrapper" class="md:col-span-1" style="display: none;">
                            <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Custom Vendor CNIC</label>
                            <input type="text" name="vendor_cnic" id="vendor_cnic_input" value="{{ old('vendor_cnic') }}" placeholder="e.g. 42101-xxxxxxx-x" class="w-full rounded-xl border-gray-200 focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                        </div>
                    </div>
                </div>

                <!-- Section 4: Installment Schedule Calculator -->
                <div class="bg-white p-8 rounded-2xl shadow-sm border border-gray-100 space-y-6" x-data="installmentCalculator()">
                    <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                        <h3 class="text-lg font-bold text-gray-800 flex items-center">
                            <span class="w-8 h-8 rounded-lg bg-orange-50 text-orange-600 flex items-center justify-center mr-3 text-sm font-bold">4</span>
                            Installment Schedule Calculator
                        </h3>
                        <label class="flex items-center cursor-pointer">
                            <div class="relative">
                                <input type="checkbox" name="apply_installment_plan" value="1" x-model="enabled" @change="calculate" class="sr-only">
                                <div class="block bg-gray-200 w-10 h-6 rounded-full transition" :class="enabled ? 'bg-orange-500' : 'bg-gray-200'"></div>
                                <div class="dot absolute left-1 top-1 bg-white w-4 h-4 rounded-full transition transform" :class="enabled ? 'translate-x-4' : ''"></div>
                            </div>
                            <span class="ml-3 text-sm font-bold text-gray-700" x-text="enabled ? 'Plan Active' : 'Enable Installments'"></span>
                        </label>
                    </div>

                    <div x-show="enabled" style="display: none;" class="space-y-6 mt-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Token / Advance Amount (Rs.)</label>
                                <input type="number" name="advance_amount" x-model.number="advance" @input="calculate" placeholder="0" class="w-full rounded-xl border-gray-200 focus:border-orange-500 focus:ring-orange-500 text-sm font-semibold">
                                <div x-show="advanceWords" class="mt-1 text-xs text-orange-600 font-bold" x-text="advanceWords"></div>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Number of Installments</label>
                                <input type="number" name="installment_count" x-model.number="count" @input="calculate" placeholder="6" class="w-full rounded-xl border-gray-200 focus:border-orange-500 focus:ring-orange-500 text-sm font-semibold">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Interval</label>
                                <select name="installment_interval" x-model="interval" @change="calculate" class="w-full rounded-xl border-gray-200 focus:border-orange-500 focus:ring-orange-500 text-sm">
                                    <option value="monthly">Monthly</option>
                                    <option value="quarterly">Quarterly</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">First Installment Date</label>
                                <input type="date" name="installment_start_date" x-model="startDate" @change="calculate" class="w-full rounded-xl border-gray-200 focus:border-orange-500 focus:ring-orange-500 text-sm">
                            </div>
                        </div>

                        <!-- Advance Payment Details (Only show if Advance > 0) -->
                        <div x-show="advance > 0" class="p-4 bg-orange-50/30 rounded-xl border border-orange-100 space-y-4">
                            <h4 class="text-sm font-bold text-orange-800">Token/Advance Payment Details</h4>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <label class="block text-xs font-semibold text-orange-600 uppercase tracking-wider mb-1">Method</label>
                                    <select name="advance_payment_method" class="w-full rounded-xl border-orange-200 focus:border-orange-500 focus:ring-orange-500 text-sm">
                                        <option value="CASH">CASH</option>
                                        <option value="CHEQUE">CHEQUE</option>
                                        <option value="BANK_TRANSFER">BANK TRANSFER</option>
                                        <option value="ONLINE">ONLINE</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-orange-600 uppercase tracking-wider mb-1">Bank Name</label>
                                    <input type="text" name="advance_bank_name" placeholder="Optional" class="w-full rounded-xl border-orange-200 focus:border-orange-500 focus:ring-orange-500 text-sm">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-orange-600 uppercase tracking-wider mb-1">Cheque/Txn #</label>
                                    <input type="text" name="advance_cheque_number" placeholder="Optional" class="w-full rounded-xl border-orange-200 focus:border-orange-500 focus:ring-orange-500 text-sm">
                                </div>
                            </div>
                            <div class="flex items-center space-x-2">
                                <input type="checkbox" name="generate_advance_receipt" value="1" checked class="rounded border-orange-300 text-orange-600 focus:ring-orange-500">
                                <span class="text-xs font-bold text-orange-700">Generate and Upload Advance Receipt automatically</span>
                            </div>
                        </div>

                        <!-- Live Schedule Preview -->
                        <div x-show="schedule.length > 0" class="mt-6 border border-gray-100 rounded-xl overflow-hidden shadow-sm bg-white">
                            <table class="w-full text-sm text-left">
                                <thead class="bg-gray-50 text-gray-500 font-semibold uppercase text-[10px] tracking-wider border-b border-gray-100">
                                    <tr>
                                        <th class="px-4 py-3">Installment</th>
                                        <th class="px-4 py-3">Due Date</th>
                                        <th class="px-4 py-3 text-right">Amount</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-50">
                                    <template x-for="item in schedule" :key="item.num">
                                        <tr class="hover:bg-orange-50/10 transition">
                                            <td class="px-4 py-3 font-bold text-gray-700" x-text="'#' + item.num"></td>
                                            <td class="px-4 py-3 text-gray-600 font-medium" x-text="item.dateStr"></td>
                                            <td class="px-4 py-3 text-right">
                                                <div class="font-extrabold text-orange-600" x-text="'Rs. ' + item.amount.toLocaleString()"></div>
                                                <div class="text-[10px] text-gray-400 font-medium" x-text="item.words"></div>
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                                <tfoot class="bg-gray-50 border-t border-gray-100">
                                    <tr>
                                        <td colspan="2" class="px-4 py-3 text-right font-semibold text-gray-500 text-xs uppercase tracking-wider">Remaining Balance:</td>
                                        <td class="px-4 py-3 text-right font-extrabold text-gray-800" x-text="'Rs. ' + remainingBalance.toLocaleString()"></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="flex justify-end space-x-4">
                    <a href="{{ route('clients.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-xl text-sm font-semibold text-gray-700 bg-white hover:bg-gray-50 focus:outline-none transition ease-in-out duration-150">
                        Cancel
                    </a>
                    <button type="submit" class="inline-flex items-center px-6 py-2 bg-indigo-600 hover:bg-indigo-500 text-white font-semibold rounded-xl shadow-md transition ease-in-out duration-150 text-sm">
                        Onboard Client & Generate Folder
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

                // Existing Client CNIC Lookup & Autofill
                cnicInput.addEventListener('input', function () {
                    const cnic = this.value;
                    if (cnic.length === 15) {
                        fetch(`/clients/lookup/${cnic}`)
                            .then(response => response.json())
                            .then(data => {
                                if (data.found) {
                                    const salutationSelect = document.querySelector('select[name="salutation"]');
                                    if (salutationSelect) salutationSelect.value = data.salutation;

                                    const fullNameInput = document.querySelector('input[name="full_name"]');
                                    if (fullNameInput) fullNameInput.value = data.full_name;

                                    const relationSelect = document.querySelector('select[name="father_husband_salutation"]');
                                    if (relationSelect) relationSelect.value = data.father_husband_salutation;

                                    const relationNameInput = document.querySelector('input[name="father_husband_name"]');
                                    if (relationNameInput) relationNameInput.value = data.father_husband_name;

                                    const phoneInput = document.querySelector('input[name="phone"]');
                                    if (phoneInput) phoneInput.value = data.phone;

                                    const addressTextarea = document.querySelector('textarea[name="residential_address"]');
                                    if (addressTextarea) addressTextarea.value = data.residential_address;

                                    const vendorSelect = document.getElementById('vendor_type_select');
                                    if (vendorSelect) {
                                        vendorSelect.value = data.vendor_type;
                                        vendorSelect.dispatchEvent(new Event('change'));
                                    }

                                    const vendorNameInput = document.querySelector('input[name="vendor_name"]');
                                    if (vendorNameInput && data.vendor_name) vendorNameInput.value = data.vendor_name;

                                    const vendorCnicInput = document.getElementById('vendor_cnic_input');
                                    if (vendorCnicInput && data.vendor_cnic) vendorCnicInput.value = data.vendor_cnic;

                                    // Display sleek toast notification
                                    showAutofillToast(data.full_name);
                                }
                            })
                            .catch(err => console.error('Error looking up CNIC:', err));
                    }
                });
            }

            function showAutofillToast(name) {
                const existing = document.getElementById('autofill-toast');
                if (existing) existing.remove();

                const toast = document.createElement('div');
                toast.id = 'autofill-toast';
                toast.className = 'fixed bottom-5 right-5 bg-gradient-to-r from-emerald-500 to-teal-600 text-white px-5 py-3 rounded-2xl shadow-2xl flex items-center space-x-3 z-50 transform translate-y-10 opacity-0 transition-all duration-500 border border-emerald-400/20';
                toast.innerHTML = `
                    <div class="bg-white/20 p-1.5 rounded-lg">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div>
                        <p class="text-xs font-semibold text-emerald-100">Existing Client Found!</p>
                        <p class="text-sm font-extrabold">${name}'s details auto-filled.</p>
                    </div>
                `;
                document.body.appendChild(toast);

                setTimeout(() => {
                    toast.classList.remove('translate-y-10', 'opacity-0');
                }, 100);

                setTimeout(() => {
                    toast.classList.add('translate-y-10', 'opacity-0');
                    setTimeout(() => toast.remove(), 500);
                }, 4000);
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

        function installmentCalculator() {
            return {
                enabled: {{ old('apply_installment_plan') ? 'true' : 'false' }},
                advance: {{ old('advance_amount') ?: 'null' }},
                count: {{ old('installment_count') ?: '6' }},
                interval: '{{ old('installment_interval', 'monthly') }}',
                startDate: '{{ old('installment_start_date', date('Y-m-d', strtotime('+1 month'))) }}',
                schedule: [],
                remainingBalance: 0,
                advanceWords: '',
                
                init() {
                    const dealInput = document.querySelector('input[name="total_deal_value"]');
                    if (dealInput) {
                        dealInput.addEventListener('input', () => this.calculate());
                    }
                    this.calculate();
                },

                calculate() {
                    const dealInput = document.querySelector('input[name="total_deal_value"]');
                    const totalDeal = dealInput ? parseFloat(dealInput.value) || 0 : 0;
                    
                    const adv = parseFloat(this.advance) || 0;
                    this.remainingBalance = totalDeal - adv;
                    
                    if (adv > 0) {
                        this.advanceWords = this.numToWordsPakistani(adv);
                    } else {
                        this.advanceWords = '';
                    }

                    this.schedule = [];
                    
                    if (!this.enabled || this.remainingBalance <= 0 || !this.count || this.count < 1 || !this.startDate) {
                        return;
                    }

                    const baseAmount = Math.floor(this.remainingBalance / this.count);
                    const remainder = this.remainingBalance % this.count;
                    
                    let currentDate = new Date(this.startDate);

                    for (let i = 0; i < this.count; i++) {
                        let amt = baseAmount;
                        if (i === this.count - 1) {
                            amt += remainder;
                        }

                        let dateStr = currentDate.toISOString().split('T')[0];
                        
                        this.schedule.push({
                            num: i + 1,
                            amount: amt,
                            dateStr: dateStr,
                            words: this.numToWordsPakistani(amt)
                        });

                        if (this.interval === 'monthly') {
                            currentDate.setMonth(currentDate.getMonth() + 1);
                        } else {
                            currentDate.setMonth(currentDate.getMonth() + 3);
                        }
                    }
                },

                numToWordsPakistani(num) {
                    if (num === 0) return 'Zero Only';
                    
                    let words = '';
                    let crores = Math.floor(num / 10000000);
                    num %= 10000000;
                    let lakhs = Math.floor(num / 100000);
                    num %= 100000;
                    let thousands = Math.floor(num / 1000);
                    num %= 1000;
                    let hundreds = Math.floor(num / 100);
                    num %= 100;
                    
                    const ones = ['', 'One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine', 'Ten', 'Eleven', 'Twelve', 'Thirteen', 'Fourteen', 'Fifteen', 'Sixteen', 'Seventeen', 'Eighteen', 'Nineteen'];
                    const tens = ['', '', 'Twenty', 'Thirty', 'Forty', 'Fifty', 'Sixty', 'Seventy', 'Eighty', 'Ninety'];
                    
                    function convertGroup(n) {
                        if (n < 20) return ones[n];
                        let t = Math.floor(n / 10);
                        let o = n % 10;
                        return tens[t] + (o ? ' ' + ones[o] : '');
                    }
                    
                    if (crores > 0) {
                        words += convertGroup(crores) + ' Crore ';
                    }
                    if (lakhs > 0) {
                        words += convertGroup(lakhs) + ' Lac ';
                    }
                    if (thousands > 0) {
                        words += convertGroup(thousands) + ' Thousand ';
                    }
                    if (hundreds > 0) {
                        words += convertGroup(hundreds) + ' Hundred ';
                    }
                    if (num > 0) {
                        if (words !== '') words += 'and ';
                        words += convertGroup(num);
                    }
                    
                    return words.trim() + ' Only';
                }
            }
        }
    </script>
</x-app-layout>
