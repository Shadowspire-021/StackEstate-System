<?php

namespace App\Notifications;

use App\Models\Payment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PaymentReceivedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        protected Payment $payment,
        protected string $message = 'Payment has been recorded successfully.'
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
        $client = $this->payment->client;
        $property = $this->payment->property;

        return (new MailMessage)
            ->subject('Payment Received: Rs. ' . number_format($this->payment->amount))
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('A payment has been recorded in the system.')
            ->line('Amount: Rs. ' . number_format($this->payment->amount))
            ->line('Client: ' . ($client->full_name ?? 'N/A'))
            ->line('Property: ' . ($property->property_type ?? 'N/A') . ' - Plot ' . ($property->plot_number ?? 'N/A'))
            ->line('Payment Method: ' . ucfirst($this->payment->payment_method))
            ->line('Payment Date: ' . $this->payment->payment_date)
            ->action('View Payment', url('/clients/' . $this->payment->client_id))
            ->line('Thank you for using our application!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'payment_id' => $this->payment->id,
            'amount' => $this->payment->amount,
            'client_id' => $this->payment->client_id,
            'client_name' => $this->payment->client?->full_name,
            'property_type' => $this->payment->property?->property_type,
            'plot_number' => $this->payment->property?->plot_number,
            'payment_method' => $this->payment->payment_method,
            'payment_date' => $this->payment->payment_date,
            'message' => $this->message,
            'url' => '/clients/' . $this->payment->client_id,
        ];
    }
}
