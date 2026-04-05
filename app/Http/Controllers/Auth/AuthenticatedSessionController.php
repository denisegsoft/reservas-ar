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
        // Guardar pendientes ANTES de regenerar la sesión
        $pendingReservation     = $request->session()->get('pending_reservation');
        $pendingReservationSlug = $request->session()->get('pending_reservation_slug');
        $pendingFavorite        = $request->session()->get('pending_favorite');

        $request->authenticate();

        $request->session()->regenerate();

        // Restaurar pendientes si se perdieron tras regenerar
        if ($pendingReservation && !$request->session()->has('pending_reservation')) {
            $request->session()->put('pending_reservation', $pendingReservation);
            $request->session()->put('pending_reservation_slug', $pendingReservationSlug);
        }
        if ($pendingFavorite && !$request->session()->has('pending_favorite')) {
            $request->session()->put('pending_favorite', $pendingFavorite);
        }

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
        \Illuminate\Support\Facades\Log::info('[Pending] Session keys after login', [
            'all' => array_keys($request->session()->all()),
            'has_reservation' => $request->session()->has('pending_reservation'),
            'has_favorite'    => $request->session()->has('pending_favorite'),
        ]);

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
