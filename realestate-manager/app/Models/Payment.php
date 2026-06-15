<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'client_id', 'property_id', 'installment_id', 'payment_number', 'amount', 'payment_method',
        'particulars', 'bank_name', 'cheque_number', 'payment_date', 'receipt_id',
        'synced_to_sheet', 'created_by'
    ];

    public function client()
    {
        return $this->belongsTo(Client::class)->withTrashed();
    }

    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    public function receipt()
    {
        return $this->belongsTo(Receipt::class);
    }

    public function installment()
    {
        return $this->belongsTo(Installment::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get payment method badge color class.
     */
    public function getMethodBadgeAttribute(): string
    {
        $colors = [
            'CASH' => 'bg-emerald-50 text-emerald-700 border border-emerald-100',
            'CHEQUE' => 'bg-blue-50 text-blue-700 border border-blue-100',
            'BANK_TRANSFER' => 'bg-indigo-50 text-indigo-700 border border-indigo-100',
            'ONLINE' => 'bg-purple-50 text-purple-700 border border-purple-100',
            'PO' => 'bg-amber-50 text-amber-700 border border-amber-100',
        ];
        return $colors[$this->payment_method] ?? 'bg-gray-50 text-gray-700 border border-gray-100';
    }
}
