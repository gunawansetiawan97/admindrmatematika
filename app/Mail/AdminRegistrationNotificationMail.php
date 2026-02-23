<?php

namespace App\Mail;

use App\Models\Order;
use App\Models\Subscription;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AdminRegistrationNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Order $order,
        public Subscription $subscription,
        public string $startsAt,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Murid Baru Terdaftar - ' . $this->subscription->name,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.admin-registration-notification',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
