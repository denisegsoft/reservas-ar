<?php

namespace App\Support;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class MailHelper
{
    /**
     * Send a mailable, swallowing and logging any exception.
     *
     * @param string   $to      Recipient email address
     * @param object   $mailable  Any Laravel Mailable instance
     * @param string   $context   Short label for log entries (e.g. '[Reservation]')
     * @param array    $extra     Extra key/value pairs to append to the log entry
     */
    public static function send(string $to, object $mailable, string $context = '[Mail]', array $extra = []): void
    {
        try {
            Mail::to($to)->send($mailable);
        } catch (\Throwable $e) {
            Log::error("{$context} Mail failed", array_merge($extra, ['error' => $e->getMessage()]));
        }
    }
}
