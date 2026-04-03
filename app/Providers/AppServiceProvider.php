<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\Quinta;
use App\Models\Reservation;
use App\Policies\QuintaPolicy;
use App\Policies\ReservationPolicy;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        Gate::policy(Property::class, PropertyPolicy::class);
        Gate::policy(Reservation::class, ReservationPolicy::class);
    }
}
