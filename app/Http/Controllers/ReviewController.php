<?php

namespace App\Http\Controllers;

use App\Models\Review;
use App\Models\Reservation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    public function store(Request $request, Reservation $reservation)
    {
        abort_if($reservation->user_id !== Auth::id(), 403);
        abort_if($reservation->status !== 'completed', 403);
        abort_if($reservation->review()->exists(), 403);

        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'required|string|min:10|max:1000',
        ]);

        Review::create([
            'property_id' => $reservation->property_id,
            'user_id' => Auth::id(),
            'reservation_id' => $reservation->id,
            'rating' => $request->rating,
            'comment' => $request->comment,
            'approved' => false,
        ]);

        return back()->with('success', 'Reseña enviada. Será publicada tras revisión.');
    }
}
