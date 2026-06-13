<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

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
}
