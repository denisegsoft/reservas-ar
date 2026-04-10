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

    /**
     * Whether an owner (or admin) can manage a reservation via the owner dashboard.
     */
    public function manageAsOwner(User $user, Reservation $reservation): bool
    {
        if ($user->isAdmin()) return true;
        return $user->propiedades()->where('id', $reservation->property_id)->exists();
    }
}
