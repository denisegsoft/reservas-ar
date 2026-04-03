<?php

namespace App\Policies;

use App\Models\Property;
use App\Models\User;

class PropertyPolicy
{
    public function create(User $user): bool
    {
        return $user->isOwner() || $user->isAdmin();
    }

    public function update(User $user, Property $propiedad): bool
    {
        return $user->isAdmin() || $propiedad->user_id === $user->id;
    }

    public function delete(User $user, Property $propiedad): bool
    {
        return $user->isAdmin() || $propiedad->user_id === $user->id;
    }
}
