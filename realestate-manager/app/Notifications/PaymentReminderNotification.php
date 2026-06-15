<?php

namespace App\Notifications;

use App\Models\Installment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PaymentReminderNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        protected Installment $installment,
        protected int $daysUntilDue
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $client = $this->installment->client;
        $daysText = $this->daysUntilDue === 1 ? 'tomorrow' : "in {$this->daysUntilDue} days";

        return (new MailMessage)
            ->subject('Payment Reminder: Rs. ' . number_format($this->installment->amount) . ' due ' . $daysText)
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('This is a friendly reminder that an installment payment is due ' . $daysText . '.')
            ->line('Client: ' . ($client->full_name ?? 'N/A'))
            ->line('Client ID: ' . ($client->client_id ?? 'N/A'))
            ->line('Installment #' . $this->installment->installment_number)
            ->line('Amount: Rs. ' . number_format($this->installment->amount))
            ->line('Due Date: ' . $this->installment->due_date->format('M d, Y'))
            ->action('View Client', url('/clients/' . $this->installment->client_id))
            ->line('Please ensure timely payment to avoid any penalties.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'payment_reminder',
            'installment_id' => $this->installment->id,
            'amount' => $this->installment->amount,
            'due_date' => $this->installment->due_date->toDateString(),
            'days_until_due' => $this->daysUntilDue,
            'client_id' => $this->installment->client_id,
            'client_name' => $this->installment->client?->full_name,
            'client_identifier' => $this->installment->client?->client_id,
            'message' => "Payment of Rs. " . number_format($this->installment->amount) . " due {$this->daysUntilDue} day(s).",
            'url' => '/clients/' . $this->installment->client_id,
        ];
    }
}
