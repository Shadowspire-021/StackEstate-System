<x-app-layout>
    <x-slot name="header">
        <h2 class="font-extrabold text-2xl bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-600 bg-clip-text text-transparent leading-tight">
            {{ __('Log Payment') }}
        </h2>
    </x-slot>

    <div class="py-12 bg-gray-50/50 min-h-screen" x-data="paymentForm()">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            @if(session('error'))
                <div class="mb-6 p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-xl shadow-sm">
                    {{ session('error') }}
                </div>
            @endif

            @if($errors->any())
                <div class="mb-6 p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-xl shadow-sm">
                    <ul class="list-disc list-inside text-sm">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="bg-white p-8 rounded-2xl shadow-sm border border-gray-100 space-y-6">
                <form action="{{ route('payments.store') }}" method="POST" class="space-y-6">
                    @csrf

                    <!-- Client Selection -->
                    <div>
                        <!-- State A: No Client Selected -->
                        <div x-show="!selectedClientId" class="relative" @click.away="isOpen = false">
                            <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Search & Select Client</label>
                            <div class="relative">
                                <input 
                                    type="text" 
                                    x-model="searchQuery" 
                                    @input="onSearch" 
                                    @focus="isOpen = true; onSearch()"
                                    placeholder="Search by Name, CNIC, Phone, Client ID, Plot #, Receipt #, Block, Address..." 
                                    class="w-full rounded-xl border-gray-200 focus:border-indigo-500 focus:ring-indigo-500 text-sm pl-10 pr-4 py-2.5 font-semibold text-gray-800 shadow-sm transition"
                                >
                                <!-- Search glass icon -->
                                <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                                    <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                                </span>
                            </div>
                            
                            <!-- Real-time dropdown search results -->
                            <div 
                                x-show="isOpen && searchQuery.length >= 1 && filteredClients.length > 0" 
                                class="absolute z-50 w-full mt-2 bg-white border border-gray-100 rounded-2xl shadow-xl max-h-80 overflow-y-auto divide-y divide-gray-50 focus:outline-none"
                                style="display: none;"
                            >
                                <template x-for="c in filteredClients" :key="c.id">
                                    <div 
                                        @click="selectClient(c)" 
                                        class="px-4 py-2.5 hover:bg-indigo-50/40 cursor-pointer transition duration-150 flex justify-between items-center text-xs gap-4"
                                    >
                                        <div class="flex items-center space-x-2 truncate">
                                            <span class="font-bold text-gray-800" x-text="c.full_name"></span>
                                            <span class="text-[10px] font-extrabold text-indigo-600 bg-indigo-50 border border-indigo-100 px-1.5 py-0.5 rounded-md" x-text="c.client_id"></span>
                                        </div>
                                        <div class="text-[10px] text-gray-400 font-semibold flex items-center space-x-2 shrink-0">
                                            <span x-show="c.plot_number">Plot: <strong class="text-gray-600" x-text="c.plot_number + ' (Block ' + (c.block_name || 'N/A') + ')'"></strong></span>
                                            <span x-show="c.phone">| Phone: <strong class="text-gray-600" x-text="c.phone"></strong></span>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <!-- State B: Client Selected (Show Selected Banner with Change/Unselect button) -->
                        <div x-show="selectedClientId" class="space-y-2" style="display: none;">
                            <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Selected Client</label>
                            <div class="flex items-center justify-between p-4 bg-indigo-50/40 border border-indigo-100 rounded-2xl shadow-sm">
                                <div class="flex items-center space-x-3">
                                    <span class="h-8 w-8 rounded-full bg-emerald-50 text-emerald-600 border border-emerald-100 flex items-center justify-center">
                                        <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    </span>
                                    <div>
                                        <div class="text-sm font-bold text-gray-800" x-text="getSelectedClientName()"></div>
                                        <div class="text-[10px] font-bold text-indigo-600 uppercase" x-text="getSelectedClientIdCode()"></div>
                                    </div>
                                </div>
                                <button type="button" @click="clearSelection" class="px-3.5 py-1.5 bg-white hover:bg-gray-50 border border-gray-200 text-rose-600 hover:text-rose-700 font-bold rounded-xl text-xs shadow-sm transition cursor-pointer">
                                    Unselect Client
                                </button>
                            </div>
                        </div>

                        <input type="hidden" name="client_id" :value="selectedClientId">
                    </div>

                    <!-- Property Quick Preview (Live) -->
                    <div x-show="selectedClientPlot" class="p-4 bg-gray-50 border border-gray-100 rounded-xl text-xs text-gray-500 space-y-1" style="display: none;">
                        <span class="font-bold text-gray-700 block mb-1">Active Property:</span>
                        <div>Plot / Unit: <span class="font-semibold text-gray-800" x-text="selectedClientPlot"></span></div>
                        <div>Block / Phase: <span class="font-semibold text-gray-800" x-text="selectedClientBlock"></span></div>
                    </div>

                    <!-- Installment Selection (Dynamic) -->
                    <div x-show="pendingInstallments.length > 0" style="display: none;" class="p-4 bg-indigo-50/20 border border-indigo-100/50 rounded-2xl space-y-2">
                        <label class="block text-xs font-semibold text-indigo-600 uppercase tracking-wider">Link to Pending Installment</label>
                        <select 
                            name="installment_id" 
                            x-model="selectedInstallmentId" 
                            @change="selectInstallment" 
                            class="w-full rounded-xl border-gray-200 focus:border-indigo-500 focus:ring-indigo-500 text-sm font-semibold text-gray-800"
                        >
                            <option value="">-- Do Not Link (Custom Payment) --</option>
                            <template x-for="inst in pendingInstallments" :key="inst.id">
                                <option :value="inst.id" x-text="`Installment # ${inst.number} - Rs. ${Number(inst.amount).toLocaleString()} (Due: ${inst.due_date})`"></option>
                            </template>
                        </select>
                        <span class="block text-[10px] text-gray-400">Selecting an installment automatically pre-fills the amount and particulars.</span>
                    </div>

                    <!-- Hidden input to satisfy backend payment_date validator -->
                    <input type="hidden" name="payment_date" :value="payments[0] ? payments[0].payment_date : '{{ date('Y-m-d') }}'">

                    <!-- Datalist for Pakistan Banks (used by dynamic breakdown inputs) -->
                    <datalist id="pakistan-banks">
                        <option value="Habib Bank Limited (HBL)">
                        <option value="Meezan Bank Limited (MBL)">
                        <option value="National Bank of Pakistan (NBP)">
                        <option value="Bank Alfalah Limited (BAFL)">
                        <option value="MCB Bank Limited (MCB)">
                        <option value="Allied Bank Limited (ABL)">
                        <option value="United Bank Limited (UBL)">
                        <option value="Bank Al Habib Limited (BAHL)">
                        <option value="Askari Bank Limited">
                        <option value="Faysal Bank Limited">
                        <option value="Dubai Islamic Bank (DIB)">
                        <option value="Standard Chartered Bank (SCB)">
                        <option value="JS Bank Limited">
                        <option value="Summit Bank Limited">
                        <option value="Soneri Bank Limited">
                        <option value="Al Baraka Bank (Pakistan) Limited">
                        <option value="The Bank of Punjab (BOP)">
                        <option value="The Bank of Khyber (BOK)">
                        <option value="Habib Metropolitan Bank Limited">
                    </datalist>

                    <!-- Section 3: PAYMENT BREAKDOWN -->
                    <div class="border border-gray-100 rounded-2xl p-6 bg-white space-y-4 shadow-sm">
                        <div class="flex justify-between items-center pb-2 border-b border-gray-50">
                            <div class="flex items-center space-x-2">
                                <span class="bg-indigo-600 text-white font-black text-xs h-5 w-5 rounded-full flex items-center justify-center">3</span>
                                <h3 class="font-extrabold text-sm text-gray-800 uppercase tracking-wider">Payment Breakdown</h3>
                            </div>
                            <button 
                                type="button" 
                                @click="addPayment()" 
                                class="inline-flex items-center px-4 py-1.5 border border-gray-300 rounded-xl text-xs font-semibold text-gray-700 bg-white hover:bg-gray-50 focus:outline-none transition cursor-pointer shadow-sm animate-pulse hover:animate-none"
                            >
                                <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                Add Payment
                            </button>
                        </div>

                        <!-- Dynamic list of payments -->
                        <div class="space-y-4">
                            <template x-for="(payment, index) in payments" :key="index">
                                <div class="border border-gray-100 rounded-2xl shadow-sm bg-gray-50/20 overflow-hidden">
                                    <!-- Payment Header -->
                                    <!-- Payment Header (Accordion Toggle) -->
                                    <div 
                                        class="flex justify-between items-center bg-gray-50/50 px-4 py-3 border-b border-gray-100 cursor-pointer hover:bg-gray-100/50 transition select-none"
                                        @click="togglePayment(index)"
                                    >
                                        <div class="flex items-center space-x-2">
                                            <svg class="w-4 h-4 text-gray-400 transition-transform duration-200" :class="{'rotate-90': payment.is_expanded}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                                            <span class="text-sm font-bold text-gray-700" x-text="`Payment ${index + 1}`"></span>
                                            <span x-show="!payment.is_expanded && payment.amount" class="text-xs font-semibold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-md ml-2 border border-emerald-100" x-text="`Rs. ${Number(payment.amount).toLocaleString()}`" style="display: none;"></span>
                                        </div>
                                        <button 
                                            type="button" 
                                            x-show="payments.length > 1" 
                                            @click.stop="removePayment(index)" 
                                            class="text-xs text-rose-600 hover:text-rose-700 font-bold px-2.5 py-1 rounded-lg hover:bg-rose-50 border border-transparent hover:border-rose-100 transition cursor-pointer"
                                        >
                                            Remove
                                        </button>
                                    </div>

                                    <!-- Payment Grid (5 columns on desktop) -->
                                    <div x-show="payment.is_expanded" x-collapse class="p-4 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4 items-start">
                                        <!-- Amount Column -->
                                        <div class="col-span-1">
                                            <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1.5">Amount</label>
                                            <input 
                                                type="number" 
                                                :name="`payments[${index}][amount]`" 
                                                x-model.number="payment.amount" 
                                                @input="updateWords(index)" 
                                                placeholder="Amount" 
                                                required
                                                class="w-full rounded-xl border-gray-200 focus:border-indigo-500 focus:ring-indigo-500 text-xs font-semibold text-gray-800 shadow-sm"
                                            >
                                        </div>

                                        <!-- Payment Method Column -->
                                        <div class="col-span-1">
                                            <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1.5">Payment Method</label>
                                            <select 
                                                :name="`payments[${index}][payment_method]`" 
                                                x-model="payment.payment_method" 
                                                @change="onMethodChange(index)"
                                                required
                                                class="w-full rounded-xl border-gray-200 focus:border-indigo-500 focus:ring-indigo-500 text-xs font-semibold text-gray-800 shadow-sm"
                                            >
                                                <option value="CASH">CASH</option>
                                                <option value="CHEQUE">Cheque</option>
                                                <option value="BANK_TRANSFER">Online Banking</option>
                                                <option value="PO">Pay Order</option>
                                            </select>
                                        </div>

                                        <!-- Column 3: Method Dependent field (Particulars / Cheque No / Transaction Count / PO No) -->
                                        <div class="col-span-1">
                                            <!-- CASH Mode: Particulars -->
                                            <div x-show="payment.payment_method === 'CASH'">
                                                <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1.5">Particulars</label>
                                                <input 
                                                    type="text" 
                                                    :name="`payments[${index}][particulars]`" 
                                                    x-model="payment.particulars" 
                                                    placeholder="Particulars" 
                                                    class="w-full rounded-xl border-gray-200 focus:border-indigo-500 focus:ring-indigo-500 text-xs font-semibold text-gray-800 shadow-sm"
                                                >
                                            </div>

                                            <!-- CHEQUE Mode: Cheque No -->
                                            <div x-show="payment.payment_method === 'CHEQUE'" style="display: none;">
                                                <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1.5">Cheque No.</label>
                                                <input 
                                                    type="text" 
                                                    :name="`payments[${index}][cheque_number]`" 
                                                    x-model="payment.cheque_number" 
                                                    placeholder="A-85423652" 
                                                    class="w-full rounded-xl border-gray-200 focus:border-indigo-500 focus:ring-indigo-500 text-xs font-semibold text-gray-800 shadow-sm"
                                                >
                                            </div>

                                            <!-- BANK_TRANSFER Mode: Transaction Count -->
                                            <div x-show="payment.payment_method === 'BANK_TRANSFER'" style="display: none;">
                                                <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1.5">Transaction Count</label>
                                                <input 
                                                    type="text" 
                                                    :name="`payments[${index}][cheque_number]`" 
                                                    x-model="payment.cheque_number" 
                                                    placeholder="1" 
                                                    class="w-full rounded-xl border-gray-200 focus:border-indigo-500 focus:ring-indigo-500 text-xs font-semibold text-gray-800 shadow-sm"
                                                >
                                            </div>

                                            <!-- PO Mode: Pay Order No -->
                                            <div x-show="payment.payment_method === 'PO'" style="display: none;">
                                                <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1.5">Pay Order No.</label>
                                                <input 
                                                    type="text" 
                                                    :name="`payments[${index}][cheque_number]`" 
                                                    x-model="payment.cheque_number" 
                                                    placeholder="PO-85423652" 
                                                    class="w-full rounded-xl border-gray-200 focus:border-indigo-500 focus:ring-indigo-500 text-xs font-semibold text-gray-800 shadow-sm"
                                                >
                                            </div>
                                        </div>

                                        <!-- Column 4: Bank -->
                                        <div class="col-span-1">
                                            <div>
                                                <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1.5">Bank</label>
                                                <!-- CASH Mode Bank (disabled) -->
                                                <input 
                                                    type="text" 
                                                    x-show="payment.payment_method === 'CASH'"
                                                    value="Not applicable" 
                                                    disabled
                                                    class="w-full rounded-xl border-gray-200 bg-gray-50 text-gray-400 text-xs font-semibold shadow-sm border"
                                                >
                                                <!-- Non-CASH Mode Bank Selection -->
                                                <input 
                                                    type="text" 
                                                    x-show="payment.payment_method !== 'CASH'"
                                                    style="display: none;"
                                                    :name="`payments[${index}][bank_name]`" 
                                                    x-model="payment.bank_name" 
                                                    list="pakistan-banks" 
                                                    placeholder="Select Bank" 
                                                    class="w-full rounded-xl border-gray-200 focus:border-indigo-500 focus:ring-indigo-500 text-xs font-semibold text-gray-800 shadow-sm"
                                                >
                                            </div>
                                        </div>

                                        <!-- Column 5: Date -->
                                        <div class="col-span-1">
                                            <!-- CASH Mode Date -->
                                            <div x-show="payment.payment_method === 'CASH'">
                                                <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1.5">Cash Date</label>
                                            </div>
                                            <!-- CHEQUE Mode Date -->
                                            <div x-show="payment.payment_method === 'CHEQUE'" style="display: none;">
                                                <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1.5">Cheque Date</label>
                                            </div>
                                            <!-- BANK_TRANSFER Mode Date -->
                                            <div x-show="payment.payment_method === 'BANK_TRANSFER'" style="display: none;">
                                                <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1.5">Transfer Date (optional)</label>
                                            </div>
                                            <!-- PO Mode Date -->
                                            <div x-show="payment.payment_method === 'PO'" style="display: none;">
                                                <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1.5">PO Date</label>
                                            </div>

                                            <input 
                                                type="date" 
                                                :name="`payments[${index}][payment_date]`" 
                                                x-model="payment.payment_date" 
                                                required
                                                class="w-full rounded-xl border-gray-200 focus:border-indigo-500 focus:ring-indigo-500 text-xs font-semibold text-gray-800 shadow-sm"
                                            >
                                        </div>

                                        <!-- Amount In Words Row -->
                                        <div x-show="payment.words" class="col-span-1 md:col-span-5 text-[10px] font-bold text-indigo-600 bg-indigo-50/50 px-3 py-2 rounded-xl border border-indigo-100 flex items-center" style="display: none;">
                                            <svg class="w-4 h-4 mr-1.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                            <span x-text="payment.words"></span>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- Generate Receipt Options -->
                    <div class="p-4 bg-indigo-50/50 rounded-2xl border border-indigo-50 flex items-start space-x-3">
                        <input type="checkbox" name="generate_receipt" value="1" id="generate_receipt" checked class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 mt-1">
                        <div>
                            <label for="generate_receipt" class="block text-sm font-bold text-gray-800 cursor-pointer">Generate Receipt (DOCX)</label>
                            <span class="block text-xs text-gray-400">System will automatically build the Word receipt matching standard design guidelines, upload it to Google Drive and sync with spreadsheet.</span>
                        </div>
                    </div>

                    <!-- Form Buttons -->
                    <div class="flex justify-end space-x-4 pt-4 border-t border-gray-100">
                        <a href="{{ route('dashboard') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-xl text-sm font-semibold text-gray-700 bg-white hover:bg-gray-50 focus:outline-none transition">
                            Cancel
                        </a>
                        <button type="submit" class="inline-flex items-center px-6 py-2 bg-indigo-600 hover:bg-indigo-500 text-white font-semibold rounded-xl shadow-md transition text-sm">
                            Log Payment & Sync
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            function paymentForm() {
                return {
                    clients: @json($clients),
                    selectedClientId: '{{ $client ? $client->id : '' }}',
                    selectedClientPlot: '{{ $client && $client->property ? $client->property->plot_number : '' }}',
                    selectedClientBlock: '{{ $client && $client->property ? $client->property->block_name : '' }}',
                    searchQuery: '{{ $client ? $client->full_name . " (" . $client->client_id . ")" : "" }}',
                    isOpen: false,
                    filteredClients: [],
                    payments: [
                        {
                            amount: '',
                            payment_method: 'CASH',
                            particulars: 'Through Cash',
                            bank_name: '',
                            cheque_number: '',
                            payment_date: '{{ date('Y-m-d') }}',
                            words: '',
                            is_expanded: true
                        }
                    ],
                    pendingInstallments: [],
                    selectedInstallmentId: '',
                    
                    togglePayment(index) {
                        this.payments[index].is_expanded = !this.payments[index].is_expanded;
                    },

                    addPayment() {
                        this.payments.forEach(p => p.is_expanded = false);
                        this.payments.push({
                            amount: '',
                            payment_method: 'CASH',
                            particulars: 'Through Cash',
                            bank_name: '',
                            cheque_number: '',
                            payment_date: '{{ date('Y-m-d') }}',
                            words: '',
                            is_expanded: true
                        });
                    },
                    
                    removePayment(index) {
                        if (this.payments.length > 1) {
                            this.payments.splice(index, 1);
                        }
                    },
                    
                    init() {
                        this.filteredClients = [];
                        if (this.selectedClientId) {
                            const c = this.clients.find(item => item.id == this.selectedClientId);
                            if (c) {
                                this.pendingInstallments = c.pending_installments || [];
                            }
                        }
                    },
                    
                    onSearch() {
                        const q = this.searchQuery.toLowerCase().trim();
                        if (!q) {
                            this.filteredClients = [];
                            return;
                        }
                        this.filteredClients = this.clients.filter(c => {
                            return (c.full_name || '').toLowerCase().includes(q) ||
                                   (c.client_id || '').toLowerCase().includes(q) ||
                                   (c.cnic || '').toLowerCase().includes(q) ||
                                   (c.phone || '').toLowerCase().includes(q) ||
                                   (c.plot_number || '').toLowerCase().includes(q) ||
                                   (c.block_name || '').toLowerCase().includes(q) ||
                                   (c.address || '').toLowerCase().includes(q) ||
                                   (c.receipt_numbers || '').toLowerCase().includes(q);
                        });
                    },
                    
                    selectClient(c) {
                        this.selectedClientId = c.id;
                        this.selectedClientPlot = c.plot_number;
                        this.selectedClientBlock = c.block_name;
                        this.searchQuery = `${c.full_name} (${c.client_id})`;
                        this.isOpen = false;
                        this.pendingInstallments = c.pending_installments || [];
                        this.selectedInstallmentId = '';
                    },
                    
                    clearSelection() {
                        this.selectedClientId = '';
                        this.selectedClientPlot = '';
                        this.selectedClientBlock = '';
                        this.searchQuery = '';
                        this.filteredClients = [];
                        this.pendingInstallments = [];
                        this.selectedInstallmentId = '';
                    },
                    
                    selectInstallment() {
                        if (!this.selectedInstallmentId) return;
                        const inst = this.pendingInstallments.find(i => i.id == this.selectedInstallmentId);
                        if (inst && this.payments.length > 0) {
                            this.payments[0].amount = inst.amount;
                            this.payments[0].particulars = `Installment # ${inst.number}`;
                            this.updateWords(0);
                        }
                    },
                    
                    getSelectedClientName() {
                        const c = this.clients.find(item => item.id == this.selectedClientId);
                        return c ? c.full_name : '';
                    },
                    
                    getSelectedClientIdCode() {
                        const c = this.clients.find(item => item.id == this.selectedClientId);
                        return c ? c.client_id : '';
                    },
                    
                    onMethodChange(index) {
                        const pay = this.payments[index];
                        if (pay.payment_method === 'CASH') {
                            pay.particulars = 'Through Cash';
                            pay.bank_name = '';
                            pay.cheque_number = '';
                        } else {
                            pay.particulars = '';
                            pay.bank_name = '';
                            pay.cheque_number = '';
                        }
                    },
                    
                    updateWords(index) {
                        const pay = this.payments[index];
                        const num = parseInt(pay.amount);
                        if (!num || isNaN(num)) {
                            pay.words = '';
                            return;
                        }
                        pay.words = this.numToWordsPakistani(num);
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
    @endpush
</x-app-layout>
