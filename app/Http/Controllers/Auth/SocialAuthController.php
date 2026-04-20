<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class SocialAuthController extends Controller
{
    public function redirect(string $provider): RedirectResponse
    {
        abort_unless(in_array($provider, ['google', 'facebook']), 404);

        return Socialite::driver($provider)->redirect();
    }

    public function callback(string $provider, Request $request): RedirectResponse
    {
        abort_unless(in_array($provider, ['google', 'facebook']), 404);

        try {
            $socialUser = Socialite::driver($provider)->user();
        } catch (\Exception) {
            return redirect()->route('login')->withErrors(['social' => 'No se pudo autenticar. Intentá de nuevo.']);
        }

        $field = $provider . '_id';

        $user = User::where($field, $socialUser->getId())
            ->orWhere('email', $socialUser->getEmail())
            ->first();

        if ($user) {
            $update = [$field => $socialUser->getId()];
           /*  if (! $user->avatar) {
                $update['avatar'] = $socialUser->getAvatar();
            } */
            $user->update($update);
        } else {
            $nameParts = explode(' ', trim($socialUser->getName() ?? ''), 2);
            $user = User::create([
                'name'      => $nameParts[0] ?? 'Usuario',
                'last_name' => $nameParts[1] ?? null,
                'email'     => $socialUser->getEmail(),
                'password'  => null,
                'role'      => 'user',
                $field      => $socialUser->getId(),
                'avatar'    => $socialUser->getAvatar(),
            ]);
        }

        Auth::login($user, true);

        if ($request->session()->has('pending_favorite') || $request->session()->has('pending_reservation')) {
            return AuthenticatedSessionController::processPendingAndRedirect($user, $request);
        }

        return redirect()->intended(route('home'));
    }
}
