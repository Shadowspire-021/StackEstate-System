<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InstallmentPlanTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'type',
        'duration_months',
        'config',
    ];

    protected $casts = [
        'config' => 'array',
        'duration_months' => 'integer',
    ];

    /**
     * Generate installment amounts based on template type and total amount.
     *
     * @param float $totalAmount Total deal value
     * @return array<int, array{amount: float, due_months: int}>
     */
    public function generateInstallments(float $totalAmount): array
    {
        return match ($this->type) {
            'equal_split' => $this->generateEqualSplit($totalAmount),
            'graduated' => $this->generateGraduated($totalAmount),
            'balloon' => $this->generateBalloon($totalAmount),
            default => $this->generateEqualSplit($totalAmount),
        };
    }

    /**
     * Equal split: divide total amount equally across all months.
     */
    private function generateEqualSplit(float $totalAmount): array
    {
        $count = $this->duration_months;
        $baseAmount = floor($totalAmount / $count);
        $remainder = $totalAmount - ($baseAmount * $count);

        $installments = [];
        for ($i = 0; $i < $count; $i++) {
            $amount = $baseAmount;
            // Add remainder to last installment
            if ($i === $count - 1) {
                $amount += $remainder;
            }
            $installments[] = [
                'amount' => round($amount, 2),
                'due_months' => $i + 1,
            ];
        }

        return $installments;
    }

    /**
     * Graduated: payments increase over time.
     * Config: start_percentage (e.g., 50 means first installment is 50% of equal split)
     *         increment_percentage (e.g., 10 means each next installment increases by 10%)
     */
    private function generateGraduated(float $totalAmount): array
    {
        $config = $this->config ?? [];
        $count = $this->duration_months;
        $startPercentage = $config['start_percentage'] ?? 50;
        $incrementPercentage = $config['increment_percentage'] ?? 10;

        $baseAmount = $totalAmount / $count;
        $installments = [];
        $totalAssigned = 0;

        for ($i = 0; $i < $count; $i++) {
            $factor = ($startPercentage + ($incrementPercentage * $i)) / 100;
            $amount = $baseAmount * $factor;

            if ($i === $count - 1) {
                // Last installment gets remainder
                $amount = $totalAmount - $totalAssigned;
            } else {
                $totalAssigned += $amount;
            }

            $installments[] = [
                'amount' => round($amount, 2),
                'due_months' => $i + 1,
            ];
        }

        return $installments;
    }

    /**
     * Balloon: smaller payments for most months, large final payment.
     * Config: balloon_percentage (e.g., 40 means last installment is 40% of total)
     */
    private function generateBalloon(float $totalAmount): array
    {
        $config = $this->config ?? [];
        $count = $this->duration_months;
        $balloonPercentage = $config['balloon_percentage'] ?? 40;

        $balloonAmount = $totalAmount * ($balloonPercentage / 100);
        $regularAmount = ($totalAmount - $balloonAmount) / ($count - 1);

        $installments = [];
        for ($i = 0; $i < $count; $i++) {
            $amount = ($i === $count - 1) ? $balloonAmount : $regularAmount;
            $installments[] = [
                'amount' => round($amount, 2),
                'due_months' => $i + 1,
            ];
        }

        return $installments;
    }

    /**
     * Get human-readable type label.
     */
    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            'equal_split' => 'Equal Split',
            'graduated' => 'Graduated',
            'balloon' => 'Balloon Payment',
            default => ucfirst($this->type),
        };
    }
}
