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
}
