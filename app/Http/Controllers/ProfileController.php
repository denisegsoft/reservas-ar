<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\Setting;
use App\Models\Suggestion;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
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
            'subscriptionPrice' => (int) Setting::get('subscription_price', '3000'),
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

        if ($request->expectsJson()) {
            return response()->json([
                'message'    => 'Perfil actualizado correctamente.',
                'avatar_url' => $user->avatar_url,
            ]);
        }

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
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
