<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'client_id', 'action', 'loggable_type',
        'loggable_id', 'old_values', 'new_values'
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class)->withTrashed();
    }

    public function loggable()
    {
        return $this->morphTo();
    }
}
