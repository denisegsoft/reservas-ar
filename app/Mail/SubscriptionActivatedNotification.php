<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SubscriptionActivatedNotification extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public User $owner) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '¡Tu suscripción en ' . config('app.name') . ' está activa!',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.subscription-activated',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
