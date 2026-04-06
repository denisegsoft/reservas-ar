<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Message;
use App\Models\Property;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $propiedadIds = $user->propiedades()->pluck('id');

        $stats = [
            'total_propiedades' => $user->propiedades()->count(),
            'active_propiedades' => $user->propiedades()->where('status', 'active')->count(),
            'total_reservations' => Reservation::whereIn('property_id', $propiedadIds)->count(),
            'pending_reservations' => Reservation::whereIn('property_id', $propiedadIds)->where('status', 'pending')->count(),
            'confirmed_reservations' => Reservation::whereIn('property_id', $propiedadIds)->where('status', 'confirmed')->count(),
            'total_earnings' => Reservation::whereIn('property_id', $propiedadIds)->where('payment_status', 'paid')->sum('total_amount'),
        ];

        $recentReservations = Reservation::whereIn('property_id', $propiedadIds)
            ->with(['property', 'user'])
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        $propiedades = $user->propiedades()->with('images')->get();

        // Datos para la alerta de suscripción (solo si no tiene suscripción)
        $lockedStats = [];
        if (!$user->hasSubscription() && !$user->isAdmin()) {
            $lockedStats = [
                'messages'     => Message::where('receiver_id', $user->id)->count(),
                'reservations' => Reservation::whereIn('property_id', $propiedadIds)->count(),
                'views'        => max((int) $user->propiedades()->sum('views_count'), 10),
            ];
        }

        return view('owner.dashboard', compact('stats', 'recentReservations', 'propiedades', 'lockedStats'));
    }

    private function requiresSubscription(): bool
    {
        $user = Auth::user();
        return !$user->isAdmin() && !$user->hasSubscription();
    }

    public function reservations(Request $request)
    {
        if ($this->requiresSubscription()) {
            return redirect()->route('subscription.payment')
                ->with('info', 'Necesitás activar tu suscripción para ver las reservas recibidas.');
        }

        $user         = Auth::user();
        $propiedadIds = $user->propiedades()->pluck('id');
        $propiedades  = $user->propiedades()->orderBy('name')->get(['id', 'name']);

        $query = Reservation::whereIn('property_id', $propiedadIds)
            ->with(['property', 'user', 'payment'])
            ->orderBy('created_at', 'desc');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }
        if ($request->filled('property_id')) {
            $query->where('property_id', $request->property_id);
        }
        if ($request->filled('date_from')) {
            $query->where('check_in', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->where('check_out', '<=', $request->date_to);
        }

        $reservations = $query->paginate(15)->withQueryString();

        return view('owner.reservations', compact('reservations', 'propiedades'));
    }

    public function createReservation()
    {
        if ($this->requiresSubscription()) {
            return redirect()->route('subscription.payment')
                ->with('info', 'Necesitás activar tu suscripción para crear reservas.');
        }

        $propiedades = Auth::user()->propiedades()->where('status', 'active')->get();
        $clientes = User::where('role', 'user')->orderBy('name')->get();

        // Reservas confirmadas por propiedad para el calendario
        $reservasPorPropiedad = [];
        foreach ($propiedades as $p) {
            $reservasPorPropiedad[$p->id] = $p->reservations()
                ->whereIn('status', ['confirmed', 'pending'])
                ->get(['id', 'check_in', 'check_out', 'status', 'guests', 'total_amount', 'user_id'])
                ->map(fn($r) => [
                    'id'       => $r->id,
                    'check_in' => $r->check_in->format('Y-m-d'),
                    'check_out'=> $r->check_out->format('Y-m-d'),
                    'status'   => $r->status,
                    'guests'   => $r->guests,
                    'total'    => number_format($r->total_amount, 0, ',', '.'),
                    'guest'    => $r->user?->full_name ?? 'Cliente',
                ])
                ->values();
        }

        return view('owner.reservation-create', compact('propiedades', 'clientes', 'reservasPorPropiedad'));
    }

    public function storeReservation(Request $request)
    {
        $propiedadIds = Auth::user()->propiedades()->pluck('id');

        $request->validate([
            'property_id'    => 'required|integer|in:' . $propiedadIds->join(','),
            'user_id'        => 'nullable|exists:users,id',
            'client_name'      => 'required_without:user_id|nullable|string|max:255',
            'client_last_name' => 'nullable|string|max:255',
            'client_email'     => 'nullable|email|max:255',
            'client_phone'     => 'nullable|string|max:30',
            'client_dni'       => 'nullable|string|max:20',
            'check_in'       => 'required|date',
            'check_out'      => 'required|date|after:check_in',
            'check_in_time'  => 'nullable',
            'check_out_time' => 'nullable',
            'guests'         => 'required|integer|min:1',
            'total_amount'   => 'required|numeric|min:0',
            'status'         => 'required|in:pending,confirmed,completed,cancelled',
            'payment_status' => 'required|in:unpaid,paid,refunded',
            'notes'          => 'nullable|string|max:1000',
        ]);

        // Si no hay user_id pero hay nombre, buscar o crear usuario
        $userId = $request->user_id;
        if (!$userId && $request->client_name) {
            $client = User::firstOrCreate(
                ['email' => $request->client_email ?? 'sin-email-' . time() . '@reserva.local'],
                [
                    'name'      => $request->client_name,
                    'last_name' => $request->client_last_name ?? '',
                    'phone'     => $request->client_phone,
                    'dni'       => $request->client_dni,
                    'role'      => 'user',
                    'password'  => bcrypt(\Illuminate\Support\Str::random(16)),
                ]
            );
            $userId = $client->id;
        }

        $property  = Property::find($request->property_id);
        $checkIn   = \Carbon\Carbon::parse($request->check_in);
        $checkOut  = \Carbon\Carbon::parse($request->check_out);
        $totalDays = $checkIn->diffInDays($checkOut);

        $reservation = Reservation::create([
            'property_id'    => $request->property_id,
            'user_id'        => $userId,
            'check_in'       => $request->check_in,
            'check_out'      => $request->check_out,
            'check_in_time'  => $request->check_in_time,
            'check_out_time' => $request->check_out_time,
            'guests'         => $request->guests,
            'price_per_day'  => $property->price_per_day,
            'total_days'     => $totalDays,
            'subtotal'       => $request->total_amount,
            'total_amount'   => $request->total_amount,
            'status'         => $request->status,
            'payment_status' => $request->payment_status,
            'notes'          => $request->notes,
        ]);

        return redirect()->route('owner.reservations.show', $reservation)->with('success', 'Reserva creada correctamente.');
    }

    public function showReservation(Reservation $reservation)
    {
        if ($this->requiresSubscription()) {
            return redirect()->route('subscription.payment')
                ->with('info', 'Necesitás activar tu suscripción para ver los datos de la reserva.');
        }

        $propiedadIds = Auth::user()->propiedades()->pluck('id');
        abort_unless($propiedadIds->contains($reservation->property_id), 403);

        $reservation->load(['property', 'user', 'payment']);

        $reservasPropiedad = $reservation->property->reservations()
            ->whereIn('status', ['confirmed', 'pending'])
            ->where('id', '!=', $reservation->id)
            ->get(['id', 'check_in', 'check_out', 'status', 'guests', 'total_amount', 'user_id'])
            ->map(fn($r) => [
                'id'       => $r->id,
                'check_in' => $r->check_in->format('Y-m-d'),
                'check_out'=> $r->check_out->format('Y-m-d'),
                'status'   => $r->status,
                'guests'   => $r->guests,
                'total'    => number_format($r->total_amount, 0, ',', '.'),
                'guest'    => $r->user?->full_name ?? 'Cliente',
            ])->values();

        return view('owner.reservation-show', compact('reservation', 'reservasPropiedad'));
    }

    public function updateReservation(Reservation $reservation, Request $request)
    {
        $propiedadIds = Auth::user()->propiedades()->pluck('id');
        abort_unless($propiedadIds->contains($reservation->property_id), 403);

        $request->validate([
            'status'              => 'sometimes|in:pending,confirmed,cancelled,completed',
            'payment_status'      => 'sometimes|in:unpaid,paid,refunded',
            'check_in'            => 'sometimes|date',
            'check_out'           => 'sometimes|date|after:check_in',
            'check_in_time'       => 'sometimes|nullable',
            'check_out_time'      => 'sometimes|nullable',
            'guests'              => 'sometimes|integer|min:1',
            'total_amount'        => 'sometimes|numeric|min:0',
            'notes'               => 'sometimes|nullable|string|max:1000',
            'cancellation_reason' => 'sometimes|nullable|string|max:1000',
        ]);

        $reservation->update($request->only([
            'status', 'payment_status',
            'check_in', 'check_out', 'check_in_time', 'check_out_time',
            'guests', 'total_amount', 'notes', 'cancellation_reason',
        ]));

        return back()->with('success', 'Reserva actualizada.');
    }

    public function propiedadesList()
    {
        $propiedades = Auth::user()->propiedades()
            ->with(['images', 'reservations' => function ($q) {
                $q->whereIn('status', ['pending', 'confirmed'])
                  ->with('user')
                  ->orderBy('check_in');
            }])
            ->orderBy('created_at', 'desc')
            ->paginate(10);
        return view('owner.propiedades', compact('propiedades'));
    }
}
