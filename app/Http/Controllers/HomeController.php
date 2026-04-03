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
        $featured = Property::where('status', 'active')
            ->where('featured', true)
            ->with('images')
            ->withCount('reviews')
            ->take(6)
            ->get();

        $latest = Property::where('status', 'active')
            ->with('images')
            ->orderBy('created_at', 'desc')
            ->take(6)
            ->get();

        $provinces = Province::where('active', true)->orderBy('order')->pluck('name');

        $stats = [
            'properties' => Property::where('status', 'active')->count(),
            'reservations' => Reservation::count(),
            'rating' => round(Property::where('status', 'active')->where('rating', '>', 0)->avg('rating'), 1),
        ];

        return view('home', compact('featured', 'latest', 'provinces', 'stats'));
    }
}
