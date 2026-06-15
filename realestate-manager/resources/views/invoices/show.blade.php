<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-xl text-gray-800">Invoice {{ $invoice->invoice_number }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow rounded-lg p-6">
                <!-- Header -->
                <div class="flex justify-between items-start mb-8">
                    <div>
                        <h3 class="text-2xl font-bold text-gray-900">INVOICE</h3>
                        <p class="text-gray-600 mt-1">{{ $invoice->invoice_number }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm text-gray-500">Issued</p>
                        <p class="font-semibold">{{ $invoice->issued_at?->format('M d, Y') ?? 'N/A' }}</p>
                    </div>
                </div>

                <!-- Client & Installment Info -->
                <div class="grid grid-cols-2 gap-6 mb-8 border-t border-b border-gray-200 py-4">
                    <div>
                        <p class="text-sm font-medium text-gray-500">Client</p>
                        <p class="font-semibold text-gray-900">{{ $invoice->client?->full_name ?? 'N/A' }}</p>
                        @if($invoice->client)
                            <p class="text-sm text-gray-500">{{ $invoice->client->cnic }}</p>
                        @endif
                    </div>
                    <div class="text-right">
                        <p class="text-sm font-medium text-gray-500">Installment</p>
                        <p class="font-semibold text-gray-900">{{ $invoice->installment ? '#' . $invoice->installment->installment_number : 'N/A' }}</p>
                        @if($invoice->installment)
                            <p class="text-sm text-gray-500">Due: {{ $invoice->installment->due_date->format('M d, Y') }}</p>
                        @endif
                    </div>
                </div>

                <!-- Amount Breakdown -->
                <div class="mb-8">
                    <table class="w-full border-collapse">
                        <thead>
                            <tr class="border-b-2 border-gray-200">
                                <th class="text-left py-3 px-4 font-medium text-gray-500">Description</th>
                                <th class="text-right py-3 px-4 font-medium text-gray-500">Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="border-b border-gray-100">
                                <td class="py-3 px-4 text-gray-900">Installment Amount</td>
                                <td class="py-3 px-4 text-right font-medium text-gray-900">Rs. {{ number_format($invoice->amount, 2) }}</td>
                            </tr>
                            @if($invoice->late_fee > 0)
                                <tr class="border-b border-gray-100">
                                    <td class="py-3 px-4 text-gray-900">Late Fee</td>
                                    <td class="py-3 px-4 text-right font-medium text-red-600">Rs. {{ number_format($invoice->late_fee, 2) }}</td>
                                </tr>
                            @endif
                        </tbody>
                        <tfoot>
                            <tr class="border-t-2 border-gray-200">
                                <td class="py-3 px-4 font-bold text-gray-900 text-lg">Total</td>
                                <td class="py-3 px-4 text-right font-bold text-gray-900 text-lg">Rs. {{ number_format($invoice->total_amount, 2) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <!-- Status & Actions -->
                <div class="flex justify-between items-center pt-4 border-t border-gray-200">
                    <div>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold
                            {{ $invoice->status === 'paid' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                            {{ ucfirst($invoice->status) }}
                        </span>
                        @if($invoice->paid_at)
                            <span class="ml-2 text-sm text-gray-500">Paid on {{ $invoice->paid_at->format('M d, Y') }}</span>
                        @endif
                    </div>
                    <a href="{{ route('invoices.download', $invoice->id) }}" 
                       class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg transition">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                        </svg>
                        Download PDF
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>