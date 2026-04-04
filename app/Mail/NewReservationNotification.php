<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NewReservationNotification extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $owner,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Nueva reserva recibida en ' . config('app.name'),
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.new-reservation',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
