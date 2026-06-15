<?php

namespace App\Events;

use App\Models\Installment;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class InstallmentUpcomingDue
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Installment $installment
    ) {}
}
