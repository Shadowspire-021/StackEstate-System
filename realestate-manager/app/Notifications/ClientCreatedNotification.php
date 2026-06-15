<?php

namespace App\Notifications;

use App\Models\Client;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ClientCreatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        protected Client $client,
        protected string $message = 'A new client has been onboarded.'
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
        return (new MailMessage)
            ->subject('New Client Onboarded: ' . $this->client->full_name)
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('A new client has been successfully onboarded to the system.')
            ->line('Client Name: ' . $this->client->full_name)
            ->line('Client ID: ' . $this->client->client_id)
            ->line('CNIC: ' . ($this->client->cnic ?? 'N/A'))
            ->line('Status: ' . ucfirst($this->client->status))
            ->action('View Client', url('/clients/' . $this->client->id))
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
            'client_id' => $this->client->id,
            'client_identifier' => $this->client->client_id,
            'client_name' => $this->client->full_name,
            'cnic' => $this->client->cnic,
            'status' => $this->client->status,
            'message' => $this->message,
            'url' => '/clients/' . $this->client->id,
        ];
    }
}
