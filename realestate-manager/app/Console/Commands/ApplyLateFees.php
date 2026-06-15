<?php

namespace App\Console\Commands;

use App\Models\Installment;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Console\Command;

class ApplyLateFees extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'installments:apply-late-fees';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Apply late fees to overdue installments based on configured rules';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        // Check if late fees are enabled
        $enabled = Setting::getValue('late_fee_enabled', '0');
        if ($enabled !== '1') {
            $this->info('Late fees are disabled in settings.');
            return Command::SUCCESS;
        }

        // Get late fee configuration
        $rate = (float) Setting::getValue('late_fee_rate', '0');
        $period = Setting::getValue('late_fee_period', 'daily');
        $graceDays = (int) Setting::getValue('late_fee_grace_days', '0');

        if ($rate <= 0) {
            $this->info('Late fee rate is not configured or zero.');
            return Command::SUCCESS;
        }

        $today = Carbon::today();
        $graceCutoff = $today->copy()->subDays($graceDays);

        // Find overdue installments (status === 'pending' AND due_date < today)
        $overdueInstallments = Installment::where('status', 'pending')
            ->where('due_date', '<', $today->toDateString())
            ->with('client')
            ->get();

        if ($overdueInstallments->isEmpty()) {
            $this->info('No overdue installments found.');
            return Command::SUCCESS;
        }

        $this->info("Found {$overdueInstallments->count()} overdue installment(s).");

        $processed = 0;
        $skipped = 0;

        foreach ($overdueInstallments as $installment) {
            $dueDate = Carbon::parse($installment->due_date);

            // Skip if within grace period
            if ($dueDate->greaterThan($graceCutoff)) {
                $this->line("  - Installment #{$installment->installment_number}: Within grace period, skipping.");
                $skipped++;
                continue;
            }

            // Check idempotency: skip if fee already applied for current period
            if ($this->isFeeAlreadyApplied($installment, $period, $today)) {
                $this->line("  - Installment #{$installment->installment_number}: Fee already applied for current period, skipping.");
                $skipped++;
                continue;
            }

            // Calculate days overdue (from grace period end, not from due date)
            $feeStartDate = $graceDays > 0 ? $graceCutoff : $dueDate;
            $daysOverdue = (int) $feeStartDate->diffInDays($today, false);

            if ($daysOverdue <= 0) {
                $this->line("  - Installment #{$installment->installment_number}: Not yet overdue beyond grace period, skipping.");
                $skipped++;
                continue;
            }

            // Calculate late fee from original amount (preserved for audit, never reduced by payments)
            $feeBase = $installment->original_amount ?? $installment->amount;
            $lateFee = $this->calculateLateFee($feeBase, $rate, $period, $daysOverdue);

            if ($lateFee <= 0) {
                $this->line("  - Installment #{$installment->installment_number}: Calculated fee is zero, skipping.");
                $skipped++;
                continue;
            }

            // Apply the late fee (add to existing fee if any)
            $newTotalFee = (float) $installment->late_fee_amount + $lateFee;

            $installment->update([
                'late_fee_amount' => $newTotalFee,
                'late_fee_applied_at' => $today->toDateTimeString(),
            ]);

            $this->line("  - Installment #{$installment->installment_number} (Original: Rs. " . number_format($feeBase) . ", Remaining: Rs. " . number_format($installment->amount) . "): Applied Rs. " . number_format($lateFee) . " late fee (total: Rs. " . number_format($newTotalFee) . ")");
            $processed++;
        }

        $this->info("Completed: {$processed} fee(s) applied, {$skipped} skipped.");

        return Command::SUCCESS;
    }

    /**
     * Check if late fee was already applied for the current period.
     *
     * Idempotency logic:
     * - daily: skip if late_fee_applied_at is today
     * - weekly: skip if late_fee_applied_at is within last 7 days
     * - monthly: skip if late_fee_applied_at is within last 30 days
     */
    private function isFeeAlreadyApplied(Installment $installment, string $period, Carbon $today): bool
    {
        if (!$installment->late_fee_applied_at) {
            return false;
        }

        $appliedAt = Carbon::parse($installment->late_fee_applied_at);

        return match ($period) {
            'daily' => $appliedAt->isSameDay($today),
            'weekly' => $appliedAt->diffInDays($today, false) < 7,
            'monthly' => $appliedAt->diffInDays($today, false) < 30,
            default => false,
        };
    }

    /**
     * Calculate late fee amount based on rate, period, and days overdue.
     *
     * Formula:
     * - daily: (amount * rate / 100) * days
     * - weekly: (amount * rate / 100) * ceil(days / 7)
     * - monthly: (amount * rate / 100) * ceil(days / 30)
     */
    private function calculateLateFee(float $amount, float $rate, string $period, int $daysOverdue): float
    {
        $baseFee = $amount * ($rate / 100);

        $periods = match ($period) {
            'daily' => $daysOverdue,
            'weekly' => ceil($daysOverdue / 7),
            'monthly' => ceil($daysOverdue / 30),
            default => $daysOverdue,
        };

        return round($baseFee * $periods, 2);
    }
}
