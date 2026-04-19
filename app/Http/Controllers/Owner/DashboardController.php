<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Mail\InvoiceUploadedNotification;
use App\Mail\ReservationCancelledClientNotification;
use App\Mail\ReservationConfirmedNotification;
use App\Models\Property;
use App\Models\Reservation;
use App\Models\ReservationService;
use App\Models\User;
use App\Services\PricingService;
use App\Support\MailHelper;
use App\Support\PropertyCache;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Rule;
use Illuminate\Support\Facades\Storage;

class DashboardController extends Controller
{
    public function __construct(private readonly PricingService $pricing) {}

    // ── Dashboard ──────────────────────────────────────────────────────────────

    public function index()
    {
        $user         = Auth::user();
        $propiedadIds = $user->propiedades()->pluck('id');

        $stats = [
            'total_propiedades'      => $user->propiedades()->count(),
            'active_propiedades'     => $user->propiedades()->active()->count(),
            'total_reservations'     => Reservation::forOwner($user)->count(),
            'pending_reservations'   => Reservation::forOwner($user)->pending()->count(),
            'confirmed_reservations' => Reservation::forOwner($user)->confirmed()->count(),
            'total_earnings'         => Reservation::forOwner($user)->paid()->sum('total_amount'),
        ];

        $recentReservations = Reservation::forOwner($user)
            ->with(['property', 'user'])
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        $propiedades = $user->propiedades()->with('images')->get();

        $lockedStats = [];

        return view('owner.dashboard', compact('stats', 'recentReservations', 'propiedades', 'lockedStats'));
    }

    // ── Reservations list ─────────────────────────────────────────────────────

    public function reservations(Request $request)
    {

        $user        = Auth::user();
        $propiedades = $user->propiedades()->orderBy('name')->get(['id', 'name']);

        $query = Reservation::forOwner($user)
            ->with(['property', 'user', 'payment'])
            ->orderBy('created_at', 'desc');

        if ($request->filled('status'))         $query->where('status', $request->status);
        if ($request->filled('payment_status')) $query->where('payment_status', $request->payment_status);
        if ($request->filled('property_id'))    $query->where('property_id', $request->property_id);
        if ($request->filled('date_from'))      $query->where('check_in', '>=', $request->date_from);
        if ($request->filled('date_to'))        $query->where('check_out', '<=', $request->date_to);

        $reservations = $query->paginate(15)->withQueryString();

        return view('owner.reservations', compact('reservations', 'propiedades'));
    }

    public function exportReservations(Request $request)
    {

        $query = Reservation::forOwner(Auth::user())
            ->with(['property', 'user'])
            ->orderBy('created_at', 'desc');

        if ($request->filled('status'))         $query->where('status', $request->status);
        if ($request->filled('payment_status')) $query->where('payment_status', $request->payment_status);
        if ($request->filled('property_id'))    $query->where('property_id', $request->property_id);
        if ($request->filled('date_from'))      $query->where('check_in', '>=', $request->date_from);
        if ($request->filled('date_to'))        $query->where('check_out', '<=', $request->date_to);

        $reservations = $query->get();
        $filename     = 'reservas-' . now()->format('Y-m-d') . '.csv';

        return response()->stream(function () use ($reservations) {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF"); // BOM for Excel

            fputcsv($handle, [
                'ID', 'Cliente', 'Email', 'Teléfono', 'Propiedad',
                'Check-in', 'Check-out', 'Días', 'Huéspedes',
                'Total', 'Estado', 'Pago', 'Fecha de reserva', 'Notas',
            ], ';');

            foreach ($reservations as $r) {
                fputcsv($handle, [
                    $r->id,
                    $r->user->full_name,
                    $r->user->email,
                    $r->user->phone ?? '',
                    $r->property->name,
                    $r->check_in->format('d/m/Y'),
                    $r->check_out->format('d/m/Y'),
                    $r->total_days,
                    $r->guests,
                    number_format($r->total_amount, 2, '.', ''),
                    Reservation::STATUS_LABELS[$r->status]          ?? $r->status,
                    Reservation::PAYMENT_LABELS[$r->payment_status] ?? $r->payment_status,
                    $r->created_at->format('d/m/Y H:i'),
                    $r->notes ?? '',
                ], ';');
            }

            fclose($handle);
        }, 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    // ── Create reservation ─────────────────────────────────────────────────────

    public function createReservation()
    {

        $propiedades = Auth::user()->propiedades()->active()->with('services', 'blockedDates')->get();
        $ownerPropertyIds = Auth::user()->propiedades()->pluck('id');
        $clientes = User::where('role', 'user')
            ->whereHas('reservations', fn($q) => $q->whereIn('property_id', $ownerPropertyIds))
            ->orderBy('name')
            ->get();

        $reservasPorPropiedad = [];
        $blockedPorPropiedad  = [];
        foreach ($propiedades as $p) {
            $reservasPorPropiedad[$p->id] = $this->formatReservationsForCalendar(
                $p->reservations()->active()
                    ->get(['id', 'check_in', 'check_out', 'status', 'guests', 'total_amount', 'user_id'])
            );
            $blockedPorPropiedad[$p->id] = $p->blockedDates->pluck('date')
                ->map(fn($d) => $d->format('Y-m-d'))
                ->values();
        }

        return view('owner.reservation-create', compact('propiedades', 'clientes', 'reservasPorPropiedad', 'blockedPorPropiedad'));
    }

    public function storeReservation(Request $request)
    {
        $user         = Auth::user();
        $propiedadIds = $user->propiedades()->pluck('id');

        $request->validate([
            'property_id'      => ['required', 'integer', Rule::in($propiedadIds)],
            'user_id'          => 'nullable|exists:users,id',
            'client_name'      => 'required_without:user_id|nullable|string|max:255',
            'client_last_name' => 'nullable|string|max:255',
            'client_email'     => 'nullable|email|max:255',
            'client_phone'     => 'nullable|string|max:30',
            'client_dni'       => 'nullable|string|max:20',
            'check_in'         => 'required|date',
            'check_out'        => 'required|date|after:check_in',
            'check_in_time'    => 'nullable',
            'check_out_time'   => 'nullable',
            'guests'           => 'required|integer|min:1',
            'total_amount'     => 'required|numeric|min:0',
            'status'           => 'required|in:pending,confirmed,completed,cancelled',
            'payment_status'   => 'required|in:unpaid,paid,refunded',
            'notes'            => 'nullable|string|max:1000',
        ]);

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
        $totalDays = Carbon::parse($request->check_in)->diffInDays(Carbon::parse($request->check_out));

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

        $this->syncReservationServices($reservation, $request->input('reservation_services', []));

        return redirect()->route('owner.reservations.show', $reservation)
            ->with('success', 'Reserva creada correctamente.');
    }

    // ── Show / edit reservation ────────────────────────────────────────────────

    public function showReservation(Reservation $reservation)
    {

        $this->authorizeOwnerReservation($reservation);

        $reservation->load(['property.services', 'user', 'payment', 'services.propertyService', 'extraCosts', 'discounts']);
        $this->pricing->recalculate($reservation);

        $reservasPropiedad = $this->formatReservationsForCalendar(
            $reservation->property->reservations()
                ->active()
                ->where('id', '!=', $reservation->id)
                ->get(['id', 'check_in', 'check_out', 'status', 'guests', 'total_amount', 'user_id'])
        );

        return view('owner.reservation-show', compact('reservation', 'reservasPropiedad'));
    }

    public function updateReservation(Reservation $reservation, Request $request)
    {
        $this->authorizeOwnerReservation($reservation);

        $request->validate([
            'status'              => 'sometimes|in:pending,confirmed,cancelled,completed',
            'payment_status'      => 'sometimes|in:unpaid,paid,refunded',
            'payment_method'      => 'sometimes|nullable|in:transfer,cash,credit',
            'check_in'            => 'sometimes|date',
            'check_out'           => 'sometimes|date|after:check_in',
            'check_in_time'       => 'sometimes|nullable',
            'check_out_time'      => 'sometimes|nullable',
            'guests'              => 'sometimes|integer|min:1',
            'notes'               => 'sometimes|nullable|string|max:1000',
            'cancellation_reason' => 'sometimes|nullable|string|max:1000',
            'invoice'             => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'extra_costs.*.name'  => 'required|string|max:255',
            'extra_costs.*.price' => 'required|numeric|min:0',
            'discounts.*.name'    => 'required|string|max:255',
            'discounts.*.price'   => 'required|numeric|min:0',
        ], [
            'invoice.mimes'                => 'Solo se permiten PDF, JPG o PNG.',
            'invoice.max'                  => 'El archivo no puede superar 5 MB.',
            'extra_costs.*.name.required'  => 'El nombre del costo es requerido.',
            'extra_costs.*.price.required' => 'El precio del costo es requerido.',
            'discounts.*.name.required'    => 'El nombre del descuento es requerido.',
            'discounts.*.price.required'   => 'El monto del descuento es requerido.',
        ]);

        $previousStatus = $reservation->status;

        $reservation->update($request->only([
            'status', 'payment_status', 'payment_method',
            'check_in', 'check_out', 'check_in_time', 'check_out_time',
            'guests', 'notes', 'cancellation_reason',
        ]));

        if ($request->has('reservation_services')) {
            $this->syncReservationServices($reservation, $request->input('reservation_services', []));
        }

        $this->syncExtraCosts($reservation, $request->input('extra_costs', []));
        $this->syncDiscounts($reservation, $request->input('discounts', []));

        // Recalculate price using PricingService
        $reservation->refresh()->load(['property', 'services', 'extraCosts', 'discounts']);
        $calc = $this->pricing->calculate([
            'check_in'       => $reservation->check_in->format('Y-m-d'),
            'check_out'      => $reservation->check_out->format('Y-m-d'),
            'check_in_time'  => $reservation->check_in_time,
            'check_out_time' => $reservation->check_out_time,
        ], $reservation->property);

        if (!isset($calc['error'])) {
            $serviciosTotal  = $reservation->services->sum(fn($s) => $s->price * $s->quantity);
            $extraCostsTotal = $reservation->extraCosts->sum('price');
            $discountsTotal  = $reservation->discounts->sum('price');
            $total = round($calc['subtotal'] + ($reservation->service_fee ?? 0) + $serviciosTotal + $extraCostsTotal - $discountsTotal, 2);

            $reservation->update([
                'price_breakdown' => $calc['breakdown'],
                'subtotal'        => $calc['subtotal'],
                'total_days'      => $calc['totalDays'],
                'price_per_day'   => $calc['pricePerDay'],
                'total_amount'    => $total,
            ]);
        }

        if ($reservation->fresh()->status !== $previousStatus) {
            $reservation->load(['user', 'property.owner']);
            $this->sendStatusChangeMail($reservation, $reservation->status);
        }

        if ($request->hasFile('invoice')) {
            $this->storeInvoice($reservation, $request);
        }

        // A status change (e.g. confirmed/cancelled) changes unavailable dates on the property page
        if ($reservation->fresh()->status !== $previousStatus) {
            PropertyCache::clear($reservation->property);
        }

        return back()->with('success', 'Reserva actualizada.');
    }

    // ── PDF / Invoice ──────────────────────────────────────────────────────────

    public function downloadPdf(Reservation $reservation)
    {
        $this->authorizeOwnerReservation($reservation);

        $reservation->load(['property', 'user', 'services.propertyService', 'extraCosts', 'discounts']);
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('owner.reservation-pdf', compact('reservation'));
        $pdf->setPaper('a4', 'portrait');

        return $pdf->download("reserva-{$reservation->id}.pdf");
    }

    public function uploadInvoice(Reservation $reservation, Request $request)
    {
        $this->authorizeOwnerReservation($reservation);

        $request->validate([
            'invoice' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ], [
            'invoice.required' => 'Seleccioná un archivo.',
            'invoice.mimes'    => 'Solo se permiten PDF, JPG o PNG.',
            'invoice.max'      => 'El archivo no puede superar 5 MB.',
        ]);

        $this->storeInvoice($reservation, $request);

        return back()->with('success', 'Factura subida y notificación enviada al cliente.');
    }

    public function deleteInvoice(Reservation $reservation)
    {
        $this->authorizeOwnerReservation($reservation);

        if ($reservation->invoice_path) {
            Storage::disk('public')->delete($reservation->invoice_path);
        }

        $reservation->update(['invoice_path' => null, 'invoice_uploaded_at' => null]);

        return back()->with('success', 'Factura eliminada.');
    }

    public function previewPrice(Reservation $reservation, Request $request)
    {
        $this->authorizeOwnerReservation($reservation);

        $reservation->load(['property', 'services.propertyService']);

        $calc = $this->pricing->calculate([
            'check_in'       => $request->input('check_in',       $reservation->check_in->format('Y-m-d')),
            'check_out'      => $request->input('check_out',      $reservation->check_out->format('Y-m-d')),
            'check_in_time'  => $request->input('check_in_time',  $reservation->check_in_time),
            'check_out_time' => $request->input('check_out_time', $reservation->check_out_time),
        ], $reservation->property);

        if (isset($calc['error'])) {
            return response()->json(['error' => array_values($calc['error'])[0]], 422);
        }

        $services       = $request->input('reservation_services', []);
        $serviciosTotal = empty($services)
            ? $reservation->services->sum(fn($s) => $s->price * $s->quantity)
            : array_sum(array_map(fn($s) => (float)($s['price'] ?? 0) * (float)($s['quantity'] ?? 1), $services));

        $total = round($calc['subtotal'] + ($reservation->service_fee ?? 0) + $serviciosTotal, 2);

        $preview                = clone $reservation;
        $preview->price_breakdown = $calc['breakdown'];
        $preview->subtotal        = $calc['subtotal'];
        $preview->total_days      = $calc['totalDays'];
        $preview->price_per_day   = $calc['pricePerDay'];
        $preview->total_amount    = $total;

        $html = view('components.reservation-price-summary', [
            'reservation'     => $preview,
            'showRecalculate' => true,
            'previewUrl'      => route('owner.reservations.preview-price', $reservation),
        ])->render();

        return response()->json(['html' => $html]);
    }

    // ── Properties list ────────────────────────────────────────────────────────

    public function propiedadesList()
    {
        $propiedades = Auth::user()->propiedades()
            ->with(['images', 'reservations' => function ($q) {
                $q->active()->with('user')->orderBy('check_in');
            }])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('owner.propiedades', compact('propiedades'));
    }

    // ── Private helpers ────────────────────────────────────────────────────────

    private function authorizeOwnerReservation(Reservation $reservation): void
    {
        abort_unless(
            Auth::user()->isAdmin() || Auth::user()->propiedades()->where('id', $reservation->property_id)->exists(),
            403
        );
    }

    private function formatReservationsForCalendar($reservations): \Illuminate\Support\Collection
    {
        return $reservations->map(fn($r) => [
            'id'        => $r->id,
            'check_in'  => $r->check_in->format('Y-m-d'),
            'check_out' => $r->check_out->format('Y-m-d'),
            'status'    => $r->status,
            'guests'    => $r->guests,
            'total'     => number_format($r->total_amount, 0, ',', '.'),
            'guest'     => $r->user?->full_name ?? 'Cliente',
        ])->values();
    }

    private function storeInvoice(Reservation $reservation, Request $request): void
    {
        if ($reservation->invoice_path) {
            Storage::disk('public')->delete($reservation->invoice_path);
        }

        // Use extension derived from the detected MIME type, not the client-supplied filename
        $allowedExts = ['application/pdf' => 'pdf', 'image/jpeg' => 'jpg', 'image/png' => 'png'];
        $ext  = $allowedExts[$request->file('invoice')->getMimeType()] ?? 'pdf';
        $date = now()->format('Y-m-d');
        $path = $request->file('invoice')->storeAs(
            'invoices',
            "factura-reserva-{$reservation->id}-{$date}.{$ext}",
            'public'
        );

        $reservation->update(['invoice_path' => $path, 'invoice_uploaded_at' => now()]);

        $reservation->load(['user', 'property']);
        MailHelper::send(
            $reservation->user->email,
            new InvoiceUploadedNotification($reservation),
            '[Invoice]',
            ['reservation_id' => $reservation->id]
        );
    }

    private function sendStatusChangeMail(Reservation $reservation, string $newStatus): void
    {
        if ($newStatus === 'confirmed') {
            MailHelper::send(
                $reservation->user->email,
                new ReservationConfirmedNotification($reservation),
                '[DashboardController]',
                ['reservation_id' => $reservation->id]
            );
        } elseif ($newStatus === 'cancelled') {
            MailHelper::send(
                $reservation->user->email,
                new ReservationCancelledClientNotification($reservation),
                '[DashboardController]',
                ['reservation_id' => $reservation->id]
            );
        }
    }

    private function syncDiscounts(Reservation $reservation, array $discounts): void
    {
        $reservation->discounts()->delete();
        foreach ($discounts as $d) {
            if (empty($d['name']) || !isset($d['price'])) continue;
            \App\Models\ReservationDiscount::create([
                'reservation_id' => $reservation->id,
                'name'           => $d['name'],
                'price'          => (float) $d['price'],
            ]);
        }
    }

    private function syncExtraCosts(Reservation $reservation, array $costs): void
    {
        $reservation->extraCosts()->delete();
        foreach ($costs as $cost) {
            if (empty($cost['name']) || !isset($cost['price'])) continue;
            \App\Models\ReservationExtraCost::create([
                'reservation_id' => $reservation->id,
                'name'           => $cost['name'],
                'price'          => (float) $cost['price'],
            ]);
        }
    }

    private function syncReservationServices(Reservation $reservation, array $services): void
    {
        // Whitelist valid service IDs for this property to prevent IDOR
        $validServiceIds = $reservation->property->services()->pluck('id');

        $reservation->services()->delete();
        foreach ($services as $s) {
            $serviceId = (int) ($s['property_service_id'] ?? 0);
            if (!$serviceId || !$validServiceIds->contains($serviceId)) continue;
            ReservationService::create([
                'reservation_id'      => $reservation->id,
                'property_service_id' => $serviceId,
                'quantity'            => (float) ($s['quantity'] ?? 1),
                'price'               => (float) ($s['price'] ?? 0),
            ]);
        }
    }
}
