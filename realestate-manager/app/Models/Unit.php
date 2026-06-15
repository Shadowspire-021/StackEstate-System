<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Unit extends Model
{
    use HasFactory;

    protected $fillable = [
        'property_id',
        'unit_number',
        'floor_number',
        'size',
        'price',
        'status',
    ];

    protected $casts = [
        'status' => 'string',
    ];

    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    public function scopeAvailable($query)
    {
        return $query->where('status', 'available');
    }

    /**
     * Scope: general search across unit_number, floor_number, and status.
     */
    public function scopeSearch($query, string $term)
    {
        return $query->where(function ($q) use ($term) {
            $q->where('unit_number', 'like', '%' . $term . '%')
              ->orWhere('floor_number', 'like', '%' . $term . '%')
              ->orWhere('status', 'like', '%' . $term . '%');
        });
    }
}
