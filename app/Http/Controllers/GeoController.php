<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class GeoController extends Controller
{
    public function partidos(Request $request)
    {
        $request->validate(['province_id' => 'required|integer|min:1']);

        return \App\Models\Partido::where('province_id', (int) $request->province_id)
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    public function localidades(Request $request)
    {
        $request->validate(['partido_id' => 'required|integer|min:1']);

        return \App\Models\Localidad::where('partido_id', (int) $request->partido_id)
            ->orderBy('name')
            ->pluck('name');
    }
}
