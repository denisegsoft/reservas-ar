<?php

namespace App\Mail;

use App\Models\Reservation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ReservationCancelledClientNotification extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Reservation $reservation,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Tu reserva fue cancelada — ' . config('app.name'),
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.reservation-cancelled-client',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
