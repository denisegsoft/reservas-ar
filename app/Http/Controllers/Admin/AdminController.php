<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Property;
use App\Support\PropertyCache;
use App\Models\Reservation;
use App\Models\Review;
use App\Models\Setting;
use App\Models\Suggestion;
use App\Models\SupportTicket;
use App\Models\SubscriptionPayment;
use App\Models\User;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function dashboard()
    {
        $stats = [
            'total_users'         => User::count(),
            'total_propiedades'   => Property::count(),
            'pending_propiedades' => Property::pendingReview()->count(),
            'total_reservations'  => Reservation::count(),
            'total_payments'      => Reservation::paid()->sum('total_amount'),
            'pending_reviews'     => Review::where('approved', false)->count(),
        ];

        $pendingPropiedades = Property::pendingReview()->with('owner')->latest()->take(10)->get();
        $recentReservations = Reservation::with(['property', 'user'])->latest()->take(10)->get();

        return view('admin.dashboard', compact('stats', 'pendingPropiedades', 'recentReservations'));
    }

    public function propiedades(Request $request)
    {
        $propiedades = Property::with('owner')
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->latest()
            ->paginate(15);

        return view('admin.propiedades', compact('propiedades'));
    }

    public function approvePropiedad(Property $propiedad)
    {
        $propiedad->update(['status' => 'active']);
        PropertyCache::clear($propiedad);
        return back()->with('success', "Propiedad '{$propiedad->name}' aprobada y publicada.");
    }

    public function rejectPropiedad(Property $propiedad)
    {
        $propiedad->update(['status' => 'inactive']);
        PropertyCache::clear($propiedad);
        return back()->with('success', "Propiedad '{$propiedad->name}' rechazada.");
    }

    public function users()
    {
        $users = User::withCount(['propiedades', 'reservations'])->latest()->paginate(15);
        return view('admin.users', compact('users'));
    }

    public function reviews()
    {
        $reviews = Review::with(['property', 'user'])->latest()->paginate(15);
        return view('admin.reviews', compact('reviews'));
    }

    public function approveReview(Review $review)
    {
        $review->update(['approved' => true]);

        $property  = $review->property;
        $avgRating = $property->reviews()->avg('rating');
        $count     = $property->reviews()->count();
        $property->update(['rating' => round($avgRating, 2), 'reviews_count' => $count]);

        PropertyCache::clear($property);

        return back()->with('success', 'Reseña aprobada.');
    }

    public function destroyReview(Review $review)
    {
        $property = $review->property;
        $review->delete();

        $avgRating = $property->reviews()->avg('rating') ?? 0;
        $count     = $property->reviews()->count();
        $property->update(['rating' => round($avgRating, 2), 'reviews_count' => $count]);

        PropertyCache::clear($property);

        return back()->with('success', 'Reseña eliminada.');
    }

    public function settings()
    {
        $settings = [
            'avatar_required'       => Setting::get('avatar_required', '1'),
            'reviews_enabled'       => Setting::get('reviews_enabled', '1'),
            'subscription_enabled'  => Setting::get('subscription_enabled', '1'),
            'subscription_price'    => Setting::get('subscription_price', '3000'),
        ];

        return view('admin.settings', compact('settings'));
    }

    public function updateSettings(Request $request)
    {
        Setting::set('avatar_required',      $request->boolean('avatar_required')     ? '1' : '0');
        Setting::set('reviews_enabled',      $request->boolean('reviews_enabled')      ? '1' : '0');
        Setting::set('subscription_enabled', $request->boolean('subscription_enabled') ? '1' : '0');
        Setting::set('subscription_price',   (string) max(1, (int) $request->input('subscription_price', 3000)));

        return back()->with('success', 'Configuración guardada.');
    }

    public function subscriptionPayments()
    {
        $payments = SubscriptionPayment::with('user')
            ->latest()
            ->paginate(30);

        $stats = [
            'total'    => SubscriptionPayment::count(),
            'approved' => SubscriptionPayment::where('status', 'approved')->count(),
            'pending'  => SubscriptionPayment::whereIn('status', ['initiated', 'pending'])->count(),
            'rejected' => SubscriptionPayment::whereIn('status', ['rejected', 'cancelled'])->count(),
            'revenue'  => SubscriptionPayment::where('status', 'approved')->sum('amount'),
        ];

        return view('admin.subscription-payments', compact('payments', 'stats'));
    }

    public function supportTickets()
    {
        $tickets = SupportTicket::with('user')->latest()->paginate(20);
        return view('admin.support', compact('tickets'));
    }

    public function updateTicketStatus(Request $request, SupportTicket $ticket)
    {
        $request->validate(['status' => 'required|in:open,in_progress,closed']);
        $ticket->update(['status' => $request->status]);
        return back()->with('success', 'Estado actualizado.');
    }

    public function suggestions()
    {
        $suggestions = Suggestion::with('user')->latest()->paginate(20);
        return view('admin.suggestions', compact('suggestions'));
    }

    public function updateSuggestionStatus(Request $request, Suggestion $suggestion)
    {
        $request->validate(['status' => 'required|in:pending,reviewed,done']);
        $suggestion->update(['status' => $request->status]);
        return back()->with('success', 'Estado actualizado.');
    }
}
