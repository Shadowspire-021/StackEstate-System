<?php

namespace App\Console\Commands;

use App\Events\InstallmentUpcomingDue;
use App\Models\Installment;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CheckUpcomingDueInstallments extends Command
{
    protected $signature = 'installments:check-upcoming-due {--days=7 : Number of days before due date to send reminder}';

    protected $description = 'Check for installments due soon and dispatch payment reminder notifications';

    public function handle(): int
    {
        $days = (int) $this->option('days');
        $today = Carbon::today()->toDateString();
        $reminderDate = Carbon::today()->addDays($days)->toDateString();

        // Find installments due within the next N days that are still pending
        $upcomingInstallments = Installment::where('status', 'pending')
            ->where('due_date', '>', $today)
            ->where('due_date', '<=', $reminderDate)
            ->with('client')
            ->get();

        if ($upcomingInstallments->isEmpty()) {
            $this->info("No installments due within the next {$days} day(s).");
            return Command::SUCCESS;
        }

        $this->info("Found {$upcomingInstallments->count()} installment(s) due within the next {$days} day(s).");

        $dispatched = 0;
        foreach ($upcomingInstallments as $installment) {
            $daysUntilDue = (int) now()->diffInDays($installment->due_date, false);

            InstallmentUpcomingDue::dispatch($installment);

            $this->line("  - Installment #{$installment->installment_number} (Rs. " . number_format($installment->amount) . ") for client: {$installment->client->full_name} — due in {$daysUntilDue} day(s)");
            $dispatched++;
        }

        $this->info("Successfully dispatched {$dispatched} reminder(s).");

        return Command::SUCCESS;
    }
}
