<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Client extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'client_id', 'salutation', 'full_name', 'father_husband_salutation',
        'father_husband_name', 'cnic', 'phone', 'residential_address',
        'google_drive_folder_id', 'google_sheet_row', 'status', 'created_by',
        'vendor_type', 'vendor_name', 'vendor_cnic'
    ];

    public function property()
    {
        return $this->hasOne(Property::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function receipts()
    {
        return $this->hasMany(Receipt::class);
    }

    public function installments()
    {
        return $this->hasMany(Installment::class);
    }

    public function documents()
    {
        return $this->hasMany(Document::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function unit()
    {
        return $this->hasOneThrough(Unit::class, Property::class, 'client_id', 'unit_id');
    }

    /**
     * Get total deal value from property.
     */
    public function getDealValueAttribute(): float
    {
        return $this->property ? (float) $this->property->total_deal_value : 0;
    }

    /**
     * Get total amount paid by this client.
     */
    public function getTotalPaidAttribute(): float
    {
        return (float) $this->payments->sum('amount');
    }

    /**
     * Get remaining balance (deal value - total paid).
     */
    public function getRemainingBalanceAttribute(): float
    {
        return $this->deal_value - $this->total_paid;
    }

    /**
     * Get payment completion percentage.
     */
    public function getPaymentPercentageAttribute(): float
    {
        $deal = $this->deal_value;
        return $deal > 0 ? round(($this->total_paid / $deal) * 100, 1) : 0;
    }

    /**
     * Get payment status classification: paid, partial, overdue, unpaid.
     * Uses loaded collection when available to avoid DB queries.
     */
    public function getPaymentStatusAttribute(): string
    {
        if ($this->remaining_balance <= 0) {
            return 'paid';
        }

        // Use loaded collection if installments are preloaded, otherwise query
        if ($this->relationLoaded('installments')) {
            $now = now();
            $hasOverdue = $this->installments->contains(function ($inst) use ($now) {
                return $inst->status === 'pending' && $inst->due_date->isPast();
            });
        } else {
            $hasOverdue = $this->installments()
                ->where('status', 'pending')
                ->where('due_date', '<', now())
                ->exists();
        }

        if ($hasOverdue) {
            return 'overdue';
        }

        if ($this->total_paid > 0) {
            return 'partial';
        }

        return 'unpaid';
    }

    /**
     * Get payment status badge HTML.
     */
    public function getPaymentStatusBadgeAttribute(): string
    {
        $status = $this->payment_status;
        $labels = [
            'paid' => ['label' => 'Fully Paid', 'class' => 'bg-emerald-50 text-emerald-700 border border-emerald-100'],
            'partial' => ['label' => 'Partial', 'class' => 'bg-amber-50 text-amber-700 border border-amber-100'],
            'overdue' => ['label' => 'Overdue', 'class' => 'bg-rose-50 text-rose-700 border border-rose-100'],
            'unpaid' => ['label' => 'Unpaid', 'class' => 'bg-gray-50 text-gray-700 border border-gray-100'],
        ];
        $info = $labels[$status] ?? $labels['unpaid'];
        return '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase tracking-wider ' . $info['class'] . '">' . $info['label'] . '</span>';
    }

    /**
     * Get overdue installment count.
     * Uses loaded collection when available to avoid DB queries.
     */
    public function getOverdueInstallmentsCountAttribute(): int
    {
        if ($this->relationLoaded('installments')) {
            $now = now();
            return $this->installments->filter(function ($inst) use ($now) {
                return $inst->status === 'pending' && $inst->due_date->isPast();
            })->count();
        }

        return $this->installments()
            ->where('status', 'pending')
            ->where('due_date', '<', now())
            ->count();
    }

    /**
     * Get overdue installment total amount.
     * Uses loaded collection when available to avoid DB queries.
     */
    public function getOverdueAmountAttribute(): float
    {
        if ($this->relationLoaded('installments')) {
            $now = now();
            return (float) $this->installments->filter(function ($inst) use ($now) {
                return $inst->status === 'pending' && $inst->due_date->isPast();
            })->sum('amount');
        }

        return (float) $this->installments()
            ->where('status', 'pending')
            ->where('due_date', '<', now())
            ->sum('amount');
    }

    /**
     * Scope: search by name (case-insensitive partial match).
     */
    public function scopeSearchByName($query, string $name)
    {
        return $query->where('full_name', 'like', '%' . $name . '%');
    }

    /**
     * Scope: search by CNIC (case-insensitive partial match).
     */
    public function scopeSearchByCnic($query, string $cnic)
    {
        return $query->where('cnic', 'like', '%' . $cnic . '%');
    }

    /**
     * Scope: search by phone (case-insensitive partial match).
     */
    public function scopeSearchByPhone($query, string $phone)
    {
        return $query->where('phone', 'like', '%' . $phone . '%');
    }

    /**
     * Scope: general search across name, CNIC, phone, and client_id.
     * Matches any of the fields using OR logic.
     */
    public function scopeSearch($query, string $term)
    {
        return $query->where(function ($q) use ($term) {
            $q->where('full_name', 'like', '%' . $term . '%')
              ->orWhere('cnic', 'like', '%' . $term . '%')
              ->orWhere('phone', 'like', '%' . $term . '%')
              ->orWhere('client_id', 'like', '%' . $term . '%');
        });
    }
}
