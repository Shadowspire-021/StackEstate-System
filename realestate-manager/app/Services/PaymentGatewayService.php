<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PaymentGatewayService
{
    public function createPayment(string $gateway, float $amount, string $orderId): array
    {
        Log::info('PaymentGateway: Creating payment', [
            'gateway' => $gateway,
            'amount'  => $amount,
            'order_id' => $orderId,
        ]);

        return match ($gateway) {
            'jazzcash'  => $this->createJazzCashPayment($amount, $orderId),
            'easypaisa' => $this->createEasypaisaPayment($amount, $orderId),
            default     => [
                'success' => false,
                'error'   => "Unsupported gateway: {$gateway}",
            ],
        };
    }

    public function verifyPayment(string $gateway, array $data): array
    {
        Log::info('PaymentGateway: Verifying payment', [
            'gateway' => $gateway,
            'data'    => $data,
        ]);

        return match ($gateway) {
            'jazzcash'  => $this->verifyJazzCashPayment($data),
            'easypaisa' => $this->verifyEasypaisaPayment($data),
            default     => [
                'success' => false,
                'error'   => "Unsupported gateway: {$gateway}",
            ],
        };
    }

    public function handleCallback(string $gateway, array $data): array
    {
        Log::info('PaymentGateway: Processing callback', [
            'gateway' => $gateway,
            'order_id' => $data['order_id'] ?? $data['pp_BillReference'] ?? 'unknown',
        ]);

        $result = $this->verifyPayment($gateway, $data);

        if ($result['success']) {
            Log::info('PaymentGateway: Callback verified', [
                'gateway' => $gateway,
                'order_id' => $result['order_id'] ?? '',
                'transaction_id' => $result['transaction_id'] ?? '',
            ]);
        } else {
            Log::warning('PaymentGateway: Callback verification failed', [
                'gateway' => $gateway,
                'error' => $result['error'] ?? 'Unknown error',
            ]);
        }

        return $result;
    }

    // ---------------------------------------------------------------
    //  JazzCash
    // ---------------------------------------------------------------

    private function createJazzCashPayment(float $amount, string $orderId): array
    {
        $merchantId    = config('payment.jazzcash.merchant_id');
        $password      = config('payment.jazzcash.password');
        $integritySalt = config('payment.jazzcash.integrity_salt');

        if (!$merchantId || !$password || !$integritySalt) {
            Log::error('JazzCash: Missing required configuration');
            return ['success' => false, 'error' => 'JazzCash is not fully configured.'];
        }

        $txnRefNo    = 'JC-' . $orderId . '-' . time();
        $amountPaisa = (int) round($amount * 100);
        $txnDateTime = now()->format('YmdHis');

        $baseUrl = config('payment.jazzcash.sandbox', true)
            ? 'https://sandbox.jazzcash.com.pk/CustomerPortal/transactionmanagement/merchantform'
            : 'https://payments.jazzcash.com.pk/CustomerPortal/transactionmanagement/merchantform';

        $fields = [
            'pp_Version'       => '2.0',
            'pp_TxnType'       => 'MD',
            'pp_Language'      => 'EN',
            'pp_MerchantID'    => $merchantId,
            'pp_Password'      => $password,
            'pp_TxnRefNo'      => $txnRefNo,
            'pp_Amount'        => $amountPaisa,
            'pp_TxnCurrency'   => 'PKR',
            'pp_TxnDateTime'   => $txnDateTime,
            'pp_BillReference' => $orderId,
            'pp_Description'   => "Payment {$orderId}",
            'pp_ReturnURL'     => route('payments.success', ['order_id' => $orderId, 'gateway' => 'jazzcash']),
            'pp_FailureURL'    => route('payments.failure', ['order_id' => $orderId, 'gateway' => 'jazzcash']),
        ];

        $fields['pp_SecureHash'] = $this->generateJazzCashHash($fields, $integritySalt);

        $queryString = http_build_query($fields);
        $paymentUrl = "{$baseUrl}?{$queryString}";

        Log::info('JazzCash: Payment URL generated', [
            'order_id'   => $orderId,
            'txn_ref_no' => $txnRefNo,
            'amount'     => $amountPaisa,
        ]);

        return [
            'success'    => true,
            'order_id'   => $orderId,
            'amount'     => $amount,
            'payment_url'=> $paymentUrl,
            'txn_ref_no' => $txnRefNo,
        ];
    }

    private function generateJazzCashHash(array $fields, string $integritySalt): string
    {
        $hashFields = collect($fields)
            ->except(['pp_SecureHash'])
            ->sortKeys()
            ->values()
            ->implode('&');

        $hashString = $integritySalt . '&' . $hashFields;

        return strtoupper(hash('sha256', $hashString));
    }

    private function verifyJazzCashPayment(array $data): array
    {
        $integritySalt = config('payment.jazzcash.integrity_salt');

        $orderId       = $data['pp_BillReference'] ?? $data['order_id'] ?? '';
        $txnRefNo      = $data['pp_TxnRefNo'] ?? '';
        $responseCode  = $data['pp_ResponseCode'] ?? '';
        $txnId         = $data['pp_RetrevalReferenceNo'] ?? '';
        $receivedHash  = $data['pp_SecureHash'] ?? '';

        if ($responseCode !== '000') {
            Log::warning('JazzCash: Payment not successful', [
                'order_id'      => $orderId,
                'response_code' => $responseCode,
            ]);
            return [
                'success' => false,
                'error'   => "JazzCash returned response code: {$responseCode}",
            ];
        }

        if ($txnId && $integritySalt) {
            $computedHash = $this->generateJazzCashHash($data, $integritySalt);
            if (!hash_equals($computedHash, $receivedHash)) {
                Log::error('JazzCash: Hash mismatch - possible tampering', [
                    'order_id' => $orderId,
                ]);
                return [
                    'success' => false,
                    'error'   => 'Hash verification failed.',
                ];
            }
        }

        Log::info('JazzCash: Payment verified', [
            'order_id' => $orderId,
            'txn_id'   => $txnId,
        ]);

        return [
            'success'        => true,
            'transaction_id' => $txnId,
            'status'         => 'completed',
            'order_id'       => $orderId,
        ];
    }

    // ---------------------------------------------------------------
    //  Easypaisa
    // ---------------------------------------------------------------

    private function createEasypaisaPayment(float $amount, string $orderId): array
    {
        $merchantId = config('payment.easypaisa.merchant_id');
        $secretKey  = config('payment.easypaisa.secret_key');

        if (!$merchantId || !$secretKey) {
            Log::error('Easypaisa: Missing required configuration');
            return ['success' => false, 'error' => 'Easypaisa is not fully configured.'];
        }

        $txnRefNo = 'EP-' . $orderId . '-' . time();

        $apiUrl = config('payment.easypaisa.sandbox', true)
            ? 'https://easypay-stg.easypaisa.com.pk/easypay/OrderEntry'
            : 'https://easypay.easypaisa.com.pk/easypay/OrderEntry';

        $payload = [
            'merchantId'          => $merchantId,
            'orderId'             => $orderId,
            'transactionRefNo'    => $txnRefNo,
            'amount'              => [
                'value'    => (string) number_format($amount, 2, '.', ''),
                'currency' => 'PKR',
            ],
            'description'         => "Payment {$orderId}",
            'successUrl'          => route('payments.success', ['order_id' => $orderId, 'gateway' => 'easypaisa']),
            'failureUrl'          => route('payments.failure', ['order_id' => $orderId, 'gateway' => 'easypaisa']),
            'signature'           => $this->generateEasypaisaSignature($merchantId, $secretKey, $txnRefNo, $amount),
        ];

        try {
            $response = Http::timeout(15)->post($apiUrl, $payload);

            if (!$response->successful()) {
                Log::error('Easypaisa: API responded with error', [
                    'status' => $response->status(),
                    'body'   => $response->body(),
                ]);
                return ['success' => false, 'error' => 'Easypaisa gateway request failed.'];
            }

            $body = $response->json();

            $redirectUrl = $body['redirectUrl']
                        ?? $body['paymentUrl']
                        ?? $body['checkoutUrl']
                        ?? '';

            if (!$redirectUrl) {
                Log::error('Easypaisa: No redirect URL in response', ['response' => $body]);
                return ['success' => false, 'error' => 'Easypaisa did not return a payment URL.'];
            }

            return [
                'success'     => true,
                'order_id'    => $orderId,
                'amount'      => $amount,
                'payment_url' => $redirectUrl,
                'txn_ref_no'  => $txnRefNo,
            ];
        } catch (\Throwable $e) {
            Log::error('Easypaisa: HTTP request failed', [
                'error' => $e->getMessage(),
            ]);
            return ['success' => false, 'error' => 'Could not connect to Easypaisa gateway.'];
        }
    }

    private function generateEasypaisaSignature(string $merchantId, string $secretKey, string $txnRefNo, float $amount): string
    {
        $amountStr = number_format($amount, 2, '.', '');
        $data = "{$merchantId}|{$txnRefNo}|{$amountStr}|PKR|{$secretKey}";
        return strtoupper(hash('sha256', $data));
    }

    private function verifyEasypaisaPayment(array $data): array
    {
        $secretKey = config('payment.easypaisa.secret_key');

        $orderId       = $data['orderId'] ?? $data['order_id'] ?? '';
        $txnRefNo      = $data['transactionRefNo'] ?? $data['txn_ref_no'] ?? '';
        $responseCode  = $data['responseCode'] ?? $data['status'] ?? '';
        $txnId         = $data['transactionId'] ?? $data['transaction_id'] ?? '';
        $receivedSig   = $data['signature'] ?? '';

        $isSuccess = in_array($responseCode, ['000', '00', '0', 'success', 'completed'], true);

        if (!$isSuccess) {
            Log::warning('Easypaisa: Payment not successful', [
                'order_id'      => $orderId,
                'response_code' => $responseCode,
            ]);
            return [
                'success' => false,
                'error'   => "Easypaisa returned response: {$responseCode}",
            ];
        }

        if ($secretKey && $txnId && $receivedSig) {
            $computedSig = strtoupper(hash('sha256', $receivedSig . $secretKey));
            if (!hash_equals($computedSig, $receivedSig)) {
                Log::error('Easypaisa: Signature mismatch', ['order_id' => $orderId]);
                return ['success' => false, 'error' => 'Signature verification failed.'];
            }
        }

        Log::info('Easypaisa: Payment verified', [
            'order_id' => $orderId,
            'txn_id'   => $txnId,
        ]);

        return [
            'success'        => true,
            'transaction_id' => $txnId ?: ('EP-TXN-' . time()),
            'status'         => 'completed',
            'order_id'       => $orderId,
        ];
    }
}
