<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    public function create(): View
    {
        return view('auth.register');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name'      => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email'     => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password'  => ['required', 'confirmed', Rules\Password::defaults()],
            'phone'     => ['nullable', 'string', 'max:20'],
        ]);

        $user = User::create([
            'name'      => $request->name,
            'last_name' => $request->last_name,
            'email'     => $request->email,
            'password'  => Hash::make($request->password),
            'role'      => 'user',
            'phone'     => $request->phone,
        ]);

        event(new Registered($user));

        Auth::login($user);

        // Si hay pendientes (favorito o reserva), los procesa y redirige
        if ($request->session()->has('pending_favorite') || $request->session()->has('pending_reservation')) {
            return AuthenticatedSessionController::processPendingAndRedirect($user, $request);
        }

        if (Setting::get('avatar_required', '1') === '1') {
            return redirect()->route('avatar.setup');
        }

        return redirect()->route('dashboard');
    }
}
