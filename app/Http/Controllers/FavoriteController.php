<?php

namespace App\Http\Controllers;

use App\Models\Property;
use Illuminate\Http\Request;

class FavoriteController extends Controller
{
    public function index()
    {
        $favorites = auth()->user()->favorites()->with('images')->paginate(12);
        return view('favorites.index', compact('favorites'));
    }

    public function loginAndSave(Property $propiedad)
    {
        session([
            'pending_favorite' => $propiedad->slug,
            'url.intended'     => route('properties.show', $propiedad->slug),
        ]);
        return redirect()->route('login');
    }

    public function toggle(Property $propiedad)
    {
        $user = auth()->user();
        $exists = $user->favorites()->where('property_id', $propiedad->id)->exists();

        if ($exists) {
            $user->favorites()->detach($propiedad->id);
            $isFavorite = false;
        } else {
            $user->favorites()->attach($propiedad->id);
            $isFavorite = true;
        }

        if (request()->expectsJson()) {
            return response()->json(['favorite' => $isFavorite]);
        }

        return back();
    }
}
