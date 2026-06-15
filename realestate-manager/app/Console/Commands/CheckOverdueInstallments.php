<?php

namespace App\Console\Commands;

use App\Events\InstallmentOverdue;
use App\Models\Installment;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CheckOverdueInstallments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'installments:check-overdue';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check for overdue installments and dispatch notifications';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $today = Carbon::today()->toDateString();

        // Find installments that are overdue (due_date < today and status is pending)
        $overdueInstallments = Installment::where('status', 'pending')
            ->where('due_date', '<', $today)
            ->with('client')
            ->get();

        if ($overdueInstallments->isEmpty()) {
            $this->info('No overdue installments found.');
            return Command::SUCCESS;
        }

        $this->info("Found {$overdueInstallments->count()} overdue installment(s).");

        $dispatched = 0;
        foreach ($overdueInstallments as $installment) {
            // Dispatch notification event
            InstallmentOverdue::dispatch($installment);

            $this->line("  - Installment #{$installment->installment_number} (Rs. " . number_format($installment->amount) . ") for client: {$installment->client->full_name}");
            $dispatched++;
        }

        $this->info("Successfully dispatched {$dispatched} overdue notification(s).");

        return Command::SUCCESS;
    }
}
