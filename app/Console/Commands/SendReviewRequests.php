<?php

namespace App\Console\Commands;

use App\Mail\ReservationCompletedNotification;
use App\Models\Reservation;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendReviewRequests extends Command
{
    protected $signature = 'reservations:send-review-requests';
    protected $description = 'Solicita reseña a clientes cuyo check-out fue hace 2 días';

    public function handle(): void
    {
        $reservations = Reservation::whereIn('status', ['confirmed', 'completed'])
            ->whereDate('check_out', '<=', now()->subDays(2)->toDateString())
            ->whereNull('review_requested_at')
            ->whereDoesntHave('review')
            ->with(['user', 'property'])
            ->get();

        $sent = 0;

        foreach ($reservations as $reservation) {
            try {
                Mail::to($reservation->user->email)
                    ->send(new ReservationCompletedNotification($reservation));

                $reservation->update(['review_requested_at' => now()]);
                $sent++;

                Log::info('[ReviewRequest] Enviado', [
                    'reservation_id' => $reservation->id,
                    'user_email'     => $reservation->user->email,
                    'check_out'      => $reservation->check_out->toDateString(),
                ]);
            } catch (\Throwable $e) {
                Log::error('[ReviewRequest] Fallo al enviar', [
                    'reservation_id' => $reservation->id,
                    'error'          => $e->getMessage(),
                ]);
            }
        }

        $this->info("Solicitudes de reseña enviadas: {$sent}");
    }
}
