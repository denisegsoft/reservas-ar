<?php

namespace App\Http\Controllers;

use App\Mail\NewReviewNotification;
use App\Models\Review;
use App\Models\Reservation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class ReviewController extends Controller
{
    public function store(Request $request, Reservation $reservation)
    {
        abort_if($reservation->user_id !== Auth::id(), 403);
        abort_if($reservation->status !== 'confirmed', 403);
        abort_if($reservation->review()->exists(), 403);

        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'required|string|min:10|max:1000',
        ]);

        $review = Review::create([
            'property_id' => $reservation->property_id,
            'user_id' => Auth::id(),
            'reservation_id' => $reservation->id,
            'rating' => $request->rating,
            'comment' => $request->comment,
            'approved' => false,
        ]);

        try {
            $review->load(['user', 'property.owner']);
            Mail::to($review->property->owner->email)->send(new NewReviewNotification($review));
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('[Review] Mail failed', [
                'review_id' => $review->id,
                'error' => $e->getMessage(),
            ]);
        }

        return back()->with('success', 'Reseña enviada. Será publicada tras revisión.');
    }
}
