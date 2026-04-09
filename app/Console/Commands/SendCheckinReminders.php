<?php

namespace App\Console\Commands;

use App\Mail\CheckInReminderNotification;
use App\Models\Reservation;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendCheckinReminders extends Command
{
    protected $signature = 'reservations:send-checkin-reminders';
    protected $description = 'Envía recordatorio de check-in a clientes con estadía en 2 días';

    public function handle(): void
    {
        $target = now()->addDays(2)->toDateString();

        $reservations = Reservation::where('status', 'confirmed')
            ->whereDate('check_in', $target)
            ->whereNull('checkin_reminder_sent_at')
            ->with(['user', 'property.owner'])
            ->get();

        $sent = 0;

        foreach ($reservations as $reservation) {
            try {
                Mail::to($reservation->user->email)
                    ->send(new CheckInReminderNotification($reservation));

                $reservation->update(['checkin_reminder_sent_at' => now()]);
                $sent++;

                Log::info('[CheckInReminder] Enviado', [
                    'reservation_id' => $reservation->id,
                    'user_email'     => $reservation->user->email,
                    'check_in'       => $reservation->check_in->toDateString(),
                ]);
            } catch (\Throwable $e) {
                Log::error('[CheckInReminder] Fallo al enviar', [
                    'reservation_id' => $reservation->id,
                    'error'          => $e->getMessage(),
                ]);
            }
        }

        $this->info("Recordatorios de check-in enviados: {$sent}");
    }
}
