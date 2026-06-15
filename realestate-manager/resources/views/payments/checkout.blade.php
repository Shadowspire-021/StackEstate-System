<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('clients.show', $client->id) }}" class="p-2 rounded-xl text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </a>
            <h2 class="font-bold text-xl text-gray-800 tracking-tight">Online Payment</h2>
        </div>
    </x-slot>

    <div class="max-w-lg mx-auto py-10 px-4 sm:px-6 lg:px-8">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="bg-gradient-to-r from-indigo-500 to-purple-600 p-6 text-white">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold">Pay Installment #{{ $installment->installment_number }}</h3>
                        <p class="text-sm text-white/70">{{ $client->full_name }}</p>
                    </div>
                </div>
                <div class="flex items-end justify-between">
                    <div>
                        <p class="text-sm text-white/70">Amount Due</p>
                        <p class="text-3xl font-black">Rs. {{ number_format($installment->total_due) }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm text-white/70">Due Date</p>
                        <p class="text-lg font-bold">{{ $installment->due_date->format('M d, Y') }}</p>
                    </div>
                </div>
            </div>

            <form action="{{ route('payments.process', [$client->id, $installment->id]) }}" method="POST" class="p-6 space-y-6">
                @csrf

                <div>
                    <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-3">Select Payment Method</label>
                    <div class="space-y-3">
                        @if(config('payment.jazzcash.enabled'))
                            <label class="flex items-center gap-4 p-4 rounded-xl border-2 cursor-pointer transition {{ old('gateway') === 'jazzcash' ? 'border-indigo-500 bg-indigo-50' : 'border-gray-200 hover:border-gray-300 bg-white' }}">
                                <input type="radio" name="gateway" value="jazzcash" {{ old('gateway', 'jazzcash') === 'jazzcash' ? 'checked' : '' }} class="w-4 h-4 text-indigo-600 focus:ring-indigo-500 border-gray-300">
                                <div class="flex-1">
                                    <div class="font-bold text-gray-800">JazzCash</div>
                                    <div class="text-xs text-gray-500">Pay via JazzCash mobile wallet</div>
                                </div>
                                <span class="text-xs font-bold text-red-600 bg-red-50 px-2 py-1 rounded">JC</span>
                            </label>
                        @endif

                        @if(config('payment.easypaisa.enabled'))
                            <label class="flex items-center gap-4 p-4 rounded-xl border-2 cursor-pointer transition {{ old('gateway') === 'easypaisa' ? 'border-indigo-500 bg-indigo-50' : 'border-gray-200 hover:border-gray-300 bg-white' }}">
                                <input type="radio" name="gateway" value="easypaisa" {{ old('gateway') === 'easypaisa' ? 'checked' : '' }} class="w-4 h-4 text-indigo-600 focus:ring-indigo-500 border-gray-300">
                                <div class="flex-1">
                                    <div class="font-bold text-gray-800">Easypaisa</div>
                                    <div class="text-xs text-gray-500">Pay via Easypaisa mobile wallet</div>
                                </div>
                                <span class="text-xs font-bold text-green-600 bg-green-50 px-2 py-1 rounded">EP</span>
                            </label>
                        @endif
                    </div>
                    @error('gateway')
                        <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit" class="w-full py-4 bg-gradient-to-r from-indigo-500 to-purple-600 text-white font-bold rounded-xl hover:from-indigo-600 hover:to-purple-700 transition shadow-lg shadow-indigo-200 text-sm uppercase tracking-wider cursor-pointer">
                    Pay Rs. {{ number_format($installment->total_due) }} Now
                </button>
            </form>

            <div class="px-6 pb-6 text-center text-xs text-gray-400">
                Secure payment via JazzCash / Easypaisa
            </div>
        </div>
    </div>
</x-app-layout>
