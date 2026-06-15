<?php

namespace App\Services;

use App\Models\Client;
use App\Models\Payment;
use App\Models\Installment;
use Illuminate\Support\Facades\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportService
{
    public function clientsCsv(): StreamedResponse
    {
        $headers = ['Client ID', 'Salutation', 'Full Name', 'Father/Husband Name', 'CNIC', 'Phone', 'Address', 'Status', 'Created At'];

        return $this->streamCsv('clients.csv', $headers, function () {
            Client::with('property')->chunk(200, function ($clients) {
                foreach ($clients as $c) {
                    yield [
                        $c->client_id,
                        $c->salutation,
                        $c->full_name,
                        ($c->father_husband_salutation ?? '') . ' ' . ($c->father_husband_name ?? ''),
                        $c->cnic,
                        $c->phone,
                        $c->residential_address,
                        $c->status,
                        $c->created_at ? $c->created_at->format('Y-m-d') : '',
                    ];
                }
            });
        });
    }

    public function paymentsCsv(?int $clientId = null): StreamedResponse
    {
        $headers = ['Receipt #', 'Client', 'CNIC', 'Amount', 'Method', 'Bank', 'Cheque #', 'Date', 'Particulars'];

        $query = Payment::with('client');
        if ($clientId) {
            $query->where('client_id', $clientId);
        }

        return $this->streamCsv('payments.csv', $headers, function () use ($query) {
            $query->chunk(200, function ($payments) {
                foreach ($payments as $p) {
                    yield [
                        $p->receipt_id ? 'RCP-' . str_replace('-', '', $p->client->client_id) . '-' . str_pad($p->payment_number, 3, '0', STR_PAD_LEFT) : 'N/A',
                        $p->client->full_name ?? 'N/A',
                        $p->client->cnic ?? 'N/A',
                        number_format($p->amount, 2),
                        $p->payment_method,
                        $p->bank_name ?? '',
                        $p->cheque_number ?? '',
                        $p->payment_date ? date('Y-m-d', strtotime($p->payment_date)) : '',
                        $p->particulars,
                    ];
                }
            });
        });
    }

    public function installmentsCsv(?int $clientId = null): StreamedResponse
    {
        $headers = ['Client', 'Installment #', 'Amount', 'Original Amount', 'Due Date', 'Status', 'Late Fee', 'Late Fee Applied At'];

        $query = Installment::with('client');
        if ($clientId) {
            $query->where('client_id', $clientId);
        }

        return $this->streamCsv('installments.csv', $headers, function () use ($query) {
            $query->orderBy('client_id')->orderBy('installment_number')->chunk(200, function ($installments) {
                foreach ($installments as $i) {
                    yield [
                        $i->client->full_name ?? 'N/A',
                        (string) $i->installment_number,
                        number_format($i->amount, 2),
                        number_format($i->original_amount ?? $i->amount, 2),
                        $i->due_date ? $i->due_date->format('Y-m-d') : '',
                        $i->status,
                        $i->late_fee_amount ? number_format($i->late_fee_amount, 2) : '0.00',
                        $i->late_fee_applied_at ? $i->late_fee_applied_at->format('Y-m-d H:i') : '',
                    ];
                }
            });
        });
    }

    private function streamCsv(string $filename, array $headers, callable $rowGenerator): StreamedResponse
    {
        return Response::stream(function () use ($headers, $rowGenerator) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, $headers);
            foreach ($rowGenerator() as $row) {
                fputcsv($handle, $row);
            }
            fclose($handle);
        }, 200, [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }
}
