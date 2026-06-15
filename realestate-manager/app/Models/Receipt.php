<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Receipt extends Model
{
    use HasFactory;

    protected $fillable = [
        'receipt_number', 'client_id', 'property_id', 'total_amount_this_receipt',
        'total_received_to_date', 'remaining_balance', 'receipt_date', 'docx_filename',
        'google_drive_file_id', 'google_drive_file_url', 'generated_by'
    ];

    public function client()
    {
        return $this->belongsTo(Client::class)->withTrashed();
    }

    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    public function generator()
    {
        return $this->belongsTo(User::class, 'generated_by');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function getTotalReceivedToDateAttribute()
    {
        return Payment::where('client_id', $this->client_id)
            ->where('created_at', '<=', $this->created_at)
            ->sum('amount');
    }
}
