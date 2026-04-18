<?php

namespace App\Mail;

use App\Http\Controllers\SubscriptionController;
use App\Models\Property;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\URL;

class PropertyPublishedNotification extends Mailable
{
    use Queueable, SerializesModels;

    public int $subscriptionPrice;
    public string $activationUrl;

    public function __construct(
        public Property $property,
    ) {
        $this->subscriptionPrice = SubscriptionController::price();
        $this->activationUrl = URL::temporarySignedRoute(
            'subscription.activate-from-mail',
            now()->addDays(7),
            ['user' => $property->user_id]
        );
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '¡Tu propiedad ya está publicada! — ' . config('app.name'),
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.property-published',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
