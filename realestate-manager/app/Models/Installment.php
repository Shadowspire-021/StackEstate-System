<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Installment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'client_id',
        'property_id',
        'installment_number',
        'amount',
        'original_amount',
        'late_fee_amount',
        'late_fee_applied_at',
        'due_date',
        'status'
    ];

    protected $casts = [
        'due_date' => 'date',
        'late_fee_applied_at' => 'datetime',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Check if this installment is overdue.
     */
    public function getIsOverdueAttribute(): bool
    {
        return $this->status === 'pending' && $this->due_date->isPast();
    }

    /**
     * Get days until due (negative if overdue).
     */
    public function getDaysUntilDueAttribute(): int
    {
        return (int) now()->diffInDays($this->due_date, false);
    }

    /**
     * Get overdue days count.
     */
    public function getOverdueDaysAttribute(): int
    {
        if (!$this->is_overdue) return 0;
        return (int) $this->due_date->diffInDays(now());
    }

    /**
     * Get original amount (fallback to amount if not set).
     */
    public function getOriginalAmountValueAttribute(): float
    {
        return (float) ($this->original_amount ?? $this->amount);
    }

    /**
     * Get total amount due (remaining balance + late fee).
     *
     * Uses `amount` (remaining after partial payments), not `original_amount`.
     * This is a display helper for the current outstanding balance.
     * Invoice totals use original_amount for consistency.
     */
    public function getTotalDueAttribute(): float
    {
        return (float) $this->amount + (float) $this->late_fee_amount;
    }

    /**
     * Get amount paid toward this installment.
     */
    public function getAmountPaidAttribute(): float
    {
        return (float) $this->payments()->sum('amount');
    }

    /**
     * Check if late fee has been applied.
     */
    public function getHasLateFeeAttribute(): bool
    {
        return (float) $this->late_fee_amount > 0;
    }

    /**
     * Get status badge HTML.
     */
    public function getStatusBadgeAttribute(): string
    {
        if ($this->status === 'paid') {
            return '<span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-extrabold bg-emerald-50 text-emerald-600 uppercase tracking-wider border border-emerald-100">Paid</span>';
        }
        if ($this->is_overdue) {
            return '<span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-extrabold bg-rose-50 text-rose-600 uppercase tracking-wider border border-rose-100">Overdue (' . $this->overdue_days . 'd)</span>';
        }
        return '<span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-extrabold bg-amber-50 text-amber-600 uppercase tracking-wider border border-amber-100">Pending</span>';
    }
}
