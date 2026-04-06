<?php

namespace App\Policies;

use App\Models\Reservation;
use App\Models\User;

class ReservationPolicy
{
    public function view(User $user, Reservation $reservation): bool
    {
        return $user->isAdmin()
            || $reservation->user_id === $user->id
            || $reservation->property->user_id === $user->id;
    }

    public function viewOwn(User $user, Reservation $reservation): bool
    {
        return $reservation->user_id === $user->id;
    }
}
