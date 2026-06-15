<?php

namespace App\Notifications;

use App\Models\Installment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class InstallmentOverdueNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        protected Installment $installment,
        protected string $message = 'An installment payment is overdue.'
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $client = $this->installment->client;

        return (new MailMessage)
            ->subject('Overdue Installment: Rs. ' . number_format($this->installment->amount))
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('An installment payment is now overdue.')
            ->line('Client: ' . ($client->full_name ?? 'N/A'))
            ->line('Client ID: ' . ($client->client_id ?? 'N/A'))
            ->line('Installment Amount: Rs. ' . number_format($this->installment->amount))
            ->line('Due Date: ' . $this->installment->due_date)
            ->line('Status: ' . ucfirst($this->installment->status))
            ->action('View Installment', url('/clients/' . $this->installment->client_id))
            ->line('Please take appropriate action to collect the payment.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'installment_id' => $this->installment->id,
            'amount' => $this->installment->amount,
            'due_date' => $this->installment->due_date,
            'status' => $this->installment->status,
            'client_id' => $this->installment->client_id,
            'client_name' => $this->installment->client?->full_name,
            'client_identifier' => $this->installment->client?->client_id,
            'message' => $this->message,
            'url' => '/clients/' . $this->installment->client_id,
        ];
    }
}
