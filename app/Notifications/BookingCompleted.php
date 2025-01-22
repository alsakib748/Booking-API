<?php

namespace App\Notifications;

use Illuminate\Support\Str;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class BookingCompleted extends Notification
{
    use Queueable;

    protected $booking;

    /**
     * Create a new notification instance.
     */
    public function __construct($booking)
    {
        $this->booking = $booking;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
                    ->greeting('Hello '.$notifiable->name.' !')
                    ->line('Booking '.Str::ucfirst($this->booking->status).' !')
                    ->line('Your booking has been '.$this->booking->status.'.')
                    ->line('Booking Details:')
                    ->line('Listing: '.$this->booking->listing->title)
                    ->line('Date: '.$this->booking->check_in.' to '.$this->booking->check_out)
                    ->line('Total Price: '.$this->booking->total_price)
                    ->action('View Booking', url('/bookings/'.$this->booking->id.'?read=1'))
                    ->line('Thank you for using our service!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            // 'id' => $this->booking->id,
            // 'title' => $this->booking->listing->title,
            // 'check_in' => $this->booking->check_in,
            // 'check_out' => $this->booking->check_out,
            // 'total_price' => $this->booking->total_price,
        ];
    }
}
