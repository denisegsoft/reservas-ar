<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Controllers\ReservationController;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\Property;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    public function create(): View
    {
        return view('auth.login');
    }

    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        return $this->processPendingAndRedirect(Auth::user(), $request);
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }

    public static function processPendingAndRedirect($user, Request $request): RedirectResponse
    {
        // Favorito pendiente
        if ($request->session()->has('pending_favorite')) {
            $slug = $request->session()->pull('pending_favorite');
            $property = Property::where('slug', $slug)->first();
            if ($property && !$user->favorites()->where('property_id', $property->id)->exists()) {
                $user->favorites()->attach($property->id);
            }
        }

        // Reserva pendiente
        if ($request->session()->has('pending_reservation')) {
            $data = $request->session()->pull('pending_reservation');
            $slug = $request->session()->pull('pending_reservation_slug');
            $property = Property::where('slug', $slug)->first();

            if ($property) {
                $result = app(ReservationController::class)->buildReservation($data, $property);

                if (!isset($result['error'])) {
                    return redirect()->route('reservations.show', $result['reservation'])
                        ->with('reservation_created', true);
                }

                return redirect()->route('properties.show', $slug)
                    ->withErrors($result['error']);
            }
        }

        return redirect()->intended(route('dashboard', absolute: false));
    }
}
