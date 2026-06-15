<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Property extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id', 'unit_id', 'template_id', 'property_type', 'plot_number', 'block_name',
        'location', 'size_sqyards', 'total_deal_value', 'agreement_date', 'notes'
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function installments()
    {
        return $this->hasMany(Installment::class);
    }

    public function units()
    {
        return $this->hasMany(Unit::class);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function template()
    {
        return $this->belongsTo(InstallmentPlanTemplate::class, 'template_id');
    }

    public function availableUnits()
    {
        return $this->hasMany(Unit::class)->where('status', 'available');
    }

    /**
     * Scope: general search across plot_number, block_name, location, and property_type.
     */
    public function scopeSearch($query, string $term)
    {
        return $query->where(function ($q) use ($term) {
            $q->where('plot_number', 'like', '%' . $term . '%')
              ->orWhere('block_name', 'like', '%' . $term . '%')
              ->orWhere('location', 'like', '%' . $term . '%')
              ->orWhere('property_type', 'like', '%' . $term . '%');
        });
    }
}
