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
        public bool $isExtension = false,
        public ?string $expiresAt = null,
    ) {}

    public function envelope(): Envelope
    {
        $subject = $this->isExtension
            ? 'Perpanjangan Kelas Murid - ' . $this->subscription->name
            : 'Murid Baru Terdaftar - ' . $this->subscription->name;

        return new Envelope(subject: $subject);
    }

    public function content(): Content
    {
        return new Content(view: 'emails.admin-registration-notification');
    }

    public function attachments(): array
    {
        return [];
    }
}
