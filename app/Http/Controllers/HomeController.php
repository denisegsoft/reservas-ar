<?php

namespace App\Http\Controllers;

use App\Models\Property;
use App\Models\Province;
use App\Models\Reservation;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        $featured = Property::active()
            ->with('images')
            ->withCount('reviews')
            ->orderBy('views_count', 'desc')
            ->take(6)
            ->get();

        $latest = Property::active()
            ->with('images')
            ->orderBy('created_at', 'desc')
            ->take(6)
            ->get();

        $provinces = Province::where('active', true)->orderBy('order')->pluck('name');

        $stats = [
            'properties'  => Property::active()->count(),
            'reservations' => Reservation::count(),
            'rating'      => round(Property::active()->where('rating', '>', 0)->avg('rating'), 1),
        ];

        return view('home', compact('featured', 'latest', 'provinces', 'stats'));
    }
}
