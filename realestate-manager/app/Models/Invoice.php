<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'installment_id',
        'payment_id',
        'invoice_number',
        'amount',
        'late_fee',
        'total_amount',
        'status',
        'issued_at',
        'paid_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'late_fee' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'issued_at' => 'datetime',
        'paid_at' => 'datetime',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class)->withTrashed();
    }

    public function installment()
    {
        return $this->belongsTo(Installment::class);
    }

    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }
}