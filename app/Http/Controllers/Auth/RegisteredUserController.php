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
        $isEmail = filter_var($request->login, FILTER_VALIDATE_EMAIL);

        $request->validate([
            'login'    => [
                'required', 'string', 'max:255',
                $isEmail
                    ? \Illuminate\Validation\Rule::unique('users', 'email')
                    : \Illuminate\Validation\Rule::unique('users', 'phone'),
            ],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name'     => '',
            'email'    => $isEmail ? strtolower($request->login) : null,
            'phone'    => $isEmail ? null : $request->login,
            'password' => Hash::make($request->password),
            'role'     => 'user',
        ]);

        event(new Registered($user));

        Auth::login($user);

        // Si hay pendientes (favorito o reserva), los procesa y redirige
        if ($request->session()->has('pending_favorite') || $request->session()->has('pending_reservation')) {
            return AuthenticatedSessionController::processPendingAndRedirect($user, $request);
        }

        /* if (Setting::get('avatar_required', '1') === '1') {
            return redirect()->route('avatar.setup');
        } */

        return redirect()->route('profile.edit')->with('status', 'registered');
    }
}
