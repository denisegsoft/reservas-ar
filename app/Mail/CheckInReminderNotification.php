<?php

namespace App\Mail;

use App\Models\Reservation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CheckInReminderNotification extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Reservation $reservation,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Recordatorio: tu estadía es en 2 días — ' . config('app.name'),
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.checkin-reminder',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
