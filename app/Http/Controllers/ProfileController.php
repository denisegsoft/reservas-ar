<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\Setting;
use App\Models\Suggestion;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        $userId = $request->user()->id;

        $hasWebRequest = Suggestion::where('user_id', $userId)
            ->where('title', 'Solicitud: necesito web/redes profesionales')
            ->exists();

        $hasWaRequest = Suggestion::where('user_id', $userId)
            ->where('title', 'Solicitud: automatización de WhatsApp Business')
            ->exists();

        return view('profile.edit', [
            'user'             => $request->user(),
            'hasWebRequest'    => $hasWebRequest,
            'hasWaRequest'     => $hasWaRequest,
            'subscriptionPrice'    => \App\Http\Controllers\SubscriptionController::price(),
            'subscriptionBasePrice' => \App\Http\Controllers\SubscriptionController::basePrice(),
            'subscriptionDiscount'  => \App\Http\Controllers\SubscriptionController::discountInfo(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse|JsonResponse
    {
        $user = $request->user();
        $validated = $request->validated();
        $validated['newsletter'] = $request->boolean('newsletter');

        $socialBases = [
            'social_instagram' => 'https://instagram.com/',
            'social_facebook'  => 'https://facebook.com/',
            'social_twitter'   => 'https://x.com/',
            'social_tiktok'    => 'https://tiktok.com/@',
            'social_youtube'   => 'https://youtube.com/@',
            'website'          => 'https://',
        ];
        foreach ($socialBases as $field => $base) {
            if (isset($validated[$field]) && rtrim($validated[$field], '/') === rtrim($base, '/')) {
                $validated[$field] = null;
            }
        }

        $user->fill($validated);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        if ($request->hasFile('avatar_file')) {
            if ($user->avatar && !str_starts_with($user->avatar, 'http') && Storage::disk('public')->exists($user->avatar)) {
                Storage::disk('public')->delete($user->avatar);
            }
            $user->avatar = $request->file('avatar_file')->store('avatars', 'public');
        }

        $user->save();

        $user->propiedades()->each(function ($p) { \App\Support\PropertyCache::clear($p); });

        if ($request->expectsJson()) {
            return response()->json([
                'message'    => 'Perfil actualizado correctamente.',
                'avatar_url' => $user->avatar_url,
                'full_name'  => $user->full_name,
            ]);
        }

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    public function setInitialPassword(Request $request): JsonResponse
    {
        $request->validate([
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $request->user()->update([
            'password'              => Hash::make($request->password),
            'needs_password_change' => false,
        ]);

        return response()->json(['ok' => true]);
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse|JsonResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        \App\Models\User::withoutGlobalScope('active')->where('id', $user->id)->update(['deleted' => true]);

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($request->expectsJson()) {
            return response()->json(['redirect' => url('/')]);
        }

        return Redirect::to('/');
    }
}
