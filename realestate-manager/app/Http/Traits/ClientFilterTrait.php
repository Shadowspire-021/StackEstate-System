<?php

namespace App\Http\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

trait ClientFilterTrait
{
    /**
     * Apply all client filters to a query builder.
     *
     * Supports: filter_name, filter_phone, filter_cnic, filter_plot,
     *           filter_block, filter_unit, filter_dues, start_date, end_date
     *
     * @param  Builder  $query
     * @param  Request  $request
     * @return Builder
     */
    protected function applyClientFilters(Builder $query, Request $request): Builder
    {
        if ($request->filled('start_date')) {
            $query->whereDate('clients.created_at', '>=', $request->get('start_date'));
        }
        if ($request->filled('end_date')) {
            $query->whereDate('clients.created_at', '<=', $request->get('end_date'));
        }

        if ($request->filled('filter_name')) {
            $query->where('clients.full_name', 'like', '%' . $request->get('filter_name') . '%');
        }

        if ($request->filled('filter_phone')) {
            $query->where('clients.phone', 'like', '%' . $request->get('filter_phone') . '%');
        }

        if ($request->filled('filter_cnic')) {
            $query->where('clients.cnic', 'like', '%' . $request->get('filter_cnic') . '%');
        }

        if ($request->filled('filter_plot')) {
            $query->whereHas('property', function ($q) use ($request) {
                $q->where('plot_number', 'like', '%' . $request->get('filter_plot') . '%');
            });
        }

        if ($request->filled('filter_block')) {
            $query->whereHas('property', function ($q) use ($request) {
                $q->where('block_name', 'like', '%' . $request->get('filter_block') . '%');
            });
        }

        if ($request->filled('filter_unit')) {
            $query->whereHas('property.unit', function ($q) use ($request) {
                $q->where('unit_number', 'like', '%' . $request->get('filter_unit') . '%');
            });
        }

        if ($request->filled('filter_dues')) {
            $this->applyDuesFilter($query, $request->get('filter_dues'));
        }

        return $query;
    }

    /**
     * Apply dues percentage filter to a client query.
     *
     * @param  Builder  $query
     * @param  string   $duesRange
     * @return void
     */
    protected function applyDuesFilter(Builder $query, string $duesRange): void
    {
        $paymentSubquery = '(select coalesce(sum(amount), 0) from payments where payments.client_id = properties.client_id)';

        switch ($duesRange) {
            case 'fully_paid':
                $query->whereHas('property', function ($q) use ($paymentSubquery) {
                    $q->whereRaw("(total_deal_value - {$paymentSubquery}) <= 0");
                });
                break;

            case 'low':
                $query->whereHas('property', function ($q) use ($paymentSubquery) {
                    $q->whereRaw("(total_deal_value - {$paymentSubquery}) / total_deal_value > 0")
                      ->whereRaw("(total_deal_value - {$paymentSubquery}) / total_deal_value <= 0.30");
                });
                break;

            case 'medium':
                $query->whereHas('property', function ($q) use ($paymentSubquery) {
                    $q->whereRaw("(total_deal_value - {$paymentSubquery}) / total_deal_value > 0.30")
                      ->whereRaw("(total_deal_value - {$paymentSubquery}) / total_deal_value <= 0.70");
                });
                break;

            case 'high':
                $query->whereHas('property', function ($q) use ($paymentSubquery) {
                    $q->whereRaw("(total_deal_value - {$paymentSubquery}) / total_deal_value > 0.70")
                      ->whereRaw("(total_deal_value - {$paymentSubquery}) / total_deal_value < 1.00");
                });
                break;

            case 'no_payment':
                $query->whereHas('property', function ($q) use ($paymentSubquery) {
                    $q->whereRaw("{$paymentSubquery} = 0");
                });
                break;
        }
    }
}
