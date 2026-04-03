<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AvatarSetupController extends Controller
{
    public function show()
    {
        if (Auth::user()->avatar || Setting::get('avatar_required', '1') !== '1') {
            return redirect()->route('dashboard');
        }

        return view('auth.setup-avatar');
    }

    public function store(Request $request)
    {
        $request->validate([
            'avatar_type'   => ['required', 'in:preset,upload'],
            'avatar_preset' => ['required_if:avatar_type,preset', 'nullable', 'string'],
            'avatar_file'   => ['required_if:avatar_type,upload', 'nullable', 'image', 'max:3072'],
        ], [
            'avatar_preset.required_if' => 'Seleccioná un avatar.',
            'avatar_file.required_if'   => 'Subí una foto de perfil.',
            'avatar_file.image'         => 'El archivo debe ser una imagen.',
            'avatar_file.max'           => 'La imagen no puede superar 3 MB.',
        ]);

        $avatar = null;
        if ($request->avatar_type === 'upload' && $request->hasFile('avatar_file')) {
            $avatar = $request->file('avatar_file')->store('avatars', 'public');
        } elseif ($request->avatar_type === 'preset') {
            $avatar = $request->avatar_preset;
        }

        Auth::user()->update(['avatar' => $avatar]);

        return redirect()->route('dashboard');
    }
}
