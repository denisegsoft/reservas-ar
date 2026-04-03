<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class GeoController extends Controller
{
    public function partidos(Request $request)
    {
        $provinceId = $request->query('province_id');
        if (!$provinceId) return response()->json([]);

        return \App\Models\Partido::where('province_id', $provinceId)
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    public function localidades(Request $request)
    {
        $partidoId = $request->query('partido_id');
        if (!$partidoId) return response()->json([]);

        return \App\Models\Localidad::where('partido_id', $partidoId)
            ->orderBy('name')
            ->pluck('name');
    }
}
