<?php

namespace App\Http\Controllers;

use App\Models\Suggestion;
use Illuminate\Http\Request;

class SuggestionController extends Controller
{
    public function create()
    {
        return view('suggestions.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title'         => 'required|string|max:150',
            'description'   => 'required|string|min:10|max:2000',
            'attachments.*' => 'nullable|file|max:5120|mimes:jpg,jpeg,png,pdf,txt',
        ]);

        $paths = [];
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $paths[] = $file->store('suggestions', 'public');
            }
        }

        Suggestion::create([
            'user_id'     => auth()->id(),
            'title'       => $data['title'],
            'description' => $data['description'],
            'attachments' => $paths ?: null,
        ]);

        return redirect()->route('suggestions.create')
            ->with('success', '¡Gracias! Tu sugerencia fue enviada correctamente.');
    }

    public function requestWebsite(Request $request)
    {
        $user = auth()->user();

        $alreadyRequested = Suggestion::where('user_id', $user->id)
            ->where('title', 'Solicitud: necesito web/redes profesionales')
            ->exists();

        if ($alreadyRequested) {
            return response()->json(['message' => 'Ya enviaste esta solicitud anteriormente.'], 409);
        }

        Suggestion::create([
            'user_id'     => $user->id,
            'title'       => 'Solicitud: necesito web/redes profesionales',
            'description' => "El usuario {$user->full_name} ({$user->email}) solicitó ayuda para crear una web profesional o gestionar sus redes sociales.",
            'attachments' => null,
        ]);

        return response()->json(['message' => '¡Solicitud enviada! Nos pondremos en contacto con vos pronto.']);
    }
}
