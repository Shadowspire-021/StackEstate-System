<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Installment;
use App\Models\Payment;
use App\Services\PaymentGatewayService;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class OnlinePaymentController extends Controller
{
    public function __construct(
        private PaymentGatewayService $gatewayService
    ) {}

    public function checkout(Client $client, Installment $installment)
    {
        if ($installment->status === 'paid') {
            return redirect()->route('clients.show', $client->id)
                ->with('error', 'This installment is already paid.');
        }

        return view('payments.checkout', compact('client', 'installment'));
    }

    public function process(Request $request, Client $client, Installment $installment)
    {
        $request->validate([
            'gateway' => 'required|in:jazzcash,easypaisa',
        ]);

        if ($installment->status === 'paid') {
            return redirect()->route('clients.show', $client->id)
                ->with('error', 'This installment is already paid.');
        }

        if (!config("payment.{$request->gateway}.enabled")) {
            return back()->with('error', 'Selected payment gateway is not enabled.');
        }

        $orderId = 'ORD-' . $client->client_id . '-' . $installment->installment_number . '-' . time();

        $result = $this->gatewayService->createPayment(
            $request->gateway,
            $installment->total_due,
            $orderId
        );

        if ($result['success']) {
            return redirect($result['payment_url']);
        }

        return back()->with('error', 'Failed to initiate payment. Please try again.');
    }

    public function success(Request $request)
    {
        $orderId = $request->query('order_id');
        $gateway = $request->query('gateway');

        $result = $this->gatewayService->verifyPayment($gateway, $request->all());

        if ($result['success']) {
            $this->recordPayment($orderId, $gateway, $result['transaction_id']);

            return redirect()->route('clients.show', $this->resolveClientId($orderId))
                ->with('success', 'Payment completed successfully!');
        }

        return redirect()->route('clients.show', $this->resolveClientId($orderId))
            ->with('error', 'Payment verification failed.');
    }

    public function failure(Request $request)
    {
        $orderId = $request->query('order_id');

        return redirect()->route('clients.show', $this->resolveClientId($orderId))
            ->with('error', 'Payment was cancelled or failed.');
    }

    public function webhook(Request $request, string $gateway)
    {
        $orderId = $request->input('order_id');

        if (!$orderId) {
            return response()->json(['status' => 'error'], 400);
        }

        $result = $this->gatewayService->verifyPayment($gateway, $request->all());

        if ($result['success']) {
            $this->recordPayment($orderId, $gateway, $result['transaction_id']);
            return response()->json(['status' => 'ok']);
        }

        return response()->json(['status' => 'error'], 400);
    }

    private function recordPayment(string $orderId, string $gateway, string $transactionId): void
    {
        $parts = explode('-', $orderId);
        if (count($parts) < 4) return;

        $clientIdStr = $parts[1] . '-' . $parts[2];
        $client = Client::where('client_id', $clientIdStr)->first();

        if (!$client) {
            Log::error('OnlinePayment: Client not found for order', ['order_id' => $orderId]);
            return;
        }

        $installmentNumber = intval($parts[3]);
        $installment = $client->installments()
            ->where('installment_number', $installmentNumber)
            ->where('status', 'pending')
            ->first();

        if (!$installment) {
            Log::error('OnlinePayment: Installment not found', ['order_id' => $orderId]);
            return;
        }

        $payment = Payment::create([
            'client_id' => $client->id,
            'property_id' => $installment->property_id,
            'installment_id' => $installment->id,
            'payment_number' => $client->payments->count() + 1,
            'amount' => $installment->total_due,
            'payment_method' => 'ONLINE',
            'particulars' => 'Online Payment via ' . ucfirst($gateway),
            'payment_date' => now()->toDateString(),
            'transaction_id' => $transactionId,
            'created_by' => null,
        ]);

        $installment->update(['status' => 'paid']);

        ActivityLogger::logCreate($payment);

        \App\Events\PaymentReceived::dispatch($payment);
    }

    private function resolveClientId(string $orderId): int
    {
        $parts = explode('-', $orderId);
        $clientIdStr = $parts[1] . '-' . $parts[2];
        $client = Client::where('client_id', $clientIdStr)->first();
        return $client?->id ?? 0;
    }
}
