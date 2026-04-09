<?php

namespace App\Mail;

use App\Models\Reservation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class InvoiceUploadedNotification extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Reservation $reservation,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Tu factura está disponible — ' . config('app.name'),
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.invoice-uploaded',
        );
    }

    public function attachments(): array
    {
        if (!$this->reservation->invoice_path) {
            return [];
        }

        return [
            Attachment::fromStorageDisk('public', $this->reservation->invoice_path)
                ->as(basename($this->reservation->invoice_path)),
        ];
    }
}
