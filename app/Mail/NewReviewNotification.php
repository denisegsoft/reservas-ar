<?php

namespace App\Mail;

use App\Models\Review;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NewReviewNotification extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Review $review,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Nueva reseña recibida — ' . config('app.name'),
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.new-review',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
