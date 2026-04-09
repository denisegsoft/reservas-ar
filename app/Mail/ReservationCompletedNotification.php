<?php

namespace App\Mail;

use App\Models\Reservation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ReservationCompletedNotification extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Reservation $reservation,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '¿Cómo fue tu estadía? Dejá tu reseña — ' . config('app.name'),
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.reservation-completed',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
