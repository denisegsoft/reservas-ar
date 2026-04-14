<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\Property;
use App\Models\PropertyAmenityLog;
use App\Models\PropertyImage;
use App\Models\PropertyService;
use App\Models\Province;
use App\Services\ContactInfoDetector;
use App\Support\PropertyCache;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PropertyController extends Controller
{
    public function index(Request $request)
    {
        $query = Property::active()->with('images');

        if ($request->filled('state')) {
            $query->where('state', $request->state);
        }

        if ($request->filled('city')) {
            $query->where('city', $request->city);
        }

        if ($request->filled('guests')) {
            $query->where('capacity', '>=', $request->guests);
        }

        if ($request->filled('price_max')) {
            $query->where('price_per_day', '<=', $request->price_max);
        }

        if ($request->filled('price_min')) {
            $query->where('price_per_day', '>=', $request->price_min);
        }

        if ($request->filled('amenities')) {
            foreach ($request->amenities as $amenity) {
                $query->whereJsonContains('amenities', $amenity);
            }
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('bedrooms')) {
            $query->where('bedrooms', '>=', $request->bedrooms);
        }

        if ($request->filled('bathrooms')) {
            $query->where('bathrooms', '>=', $request->bathrooms);
        }

        if ($request->filled('parking')) {
            $query->where('parking_spots', '>=', $request->parking);
        }

        if ($request->filled('rating_min')) {
            $query->where('rating', '>=', $request->rating_min);
        }

        if ($request->filled('hour_from')) {
            $query->whereNotNull('available_from')->where('available_from', '<=', $request->hour_from);
        }

        if ($request->filled('hour_to')) {
            $query->whereNotNull('available_to')->where('available_to', '>=', $request->hour_to);
        }

        if ($request->filled('check_in') && $request->filled('check_out')) {
            $checkIn = $request->check_in;
            $checkOut = $request->check_out;

            $query->whereDoesntHave('reservations', function ($q) use ($checkIn, $checkOut) {
                $q->whereIn('status', ['confirmed', 'pending'])
                  ->where(function ($q) use ($checkIn, $checkOut) {
                      $q->whereBetween('check_in', [$checkIn, $checkOut])
                        ->orWhereBetween('check_out', [$checkIn, $checkOut])
                        ->orWhere(function ($q) use ($checkIn, $checkOut) {
                            $q->where('check_in', '<=', $checkIn)->where('check_out', '>=', $checkOut);
                        });
                  });
            });
        }

        $sortBy = $request->get('sort', 'featured');
        switch ($sortBy) {
            case 'price_asc':   $query->orderBy('price_per_day', 'asc'); break;
            case 'price_desc':  $query->orderBy('price_per_day', 'desc'); break;
            case 'rating_asc':  $query->orderBy('rating', 'asc'); break;
            case 'rating_desc':
            case 'rating':      $query->orderBy('rating', 'desc'); break;
            case 'newest':      $query->orderBy('created_at', 'desc'); break;
            default:            $query->orderBy('featured', 'desc')->orderBy('rating', 'desc');
        }

        $propiedades = $query->paginate(12)->withQueryString();
        $amenitiesList = Property::amenitiesList();
        $typesList = Property::typesList();
        $provinces = Province::where('active', true)->orderBy('order')->pluck('name');
        $cities = $request->filled('state')
            ? City::whereHas('province', fn($q) => $q->where('name', $request->state))
                ->where('active', true)->orderBy('order')->pluck('name')
            : collect();

        return view('propiedades.index', compact('propiedades', 'amenitiesList', 'typesList', 'provinces', 'cities'));
    }

    public function show(Property $propiedad)
    {
        $isAdmin = auth()->check() && auth()->user()->role === 'admin';
        abort_if(!$isAdmin && $propiedad->status !== 'active', 404);

        // Increment view count (not for the owner)
        if (!auth()->check() || auth()->id() !== $propiedad->user_id) {
            Property::withoutGlobalScope('active')->where('id', $propiedad->id)
                ->increment('views_count');
        }

        // Cache the expensive DB-heavy part (property data + unavailable dates + similar).
        // reservaParaReseña is user-specific so it stays dynamic.
        $cached = Cache::remember(PropertyCache::forShow($propiedad->slug), now()->addHour(), function () use ($propiedad) {
            $propiedad->load(['images', 'owner', 'reviews.user', 'blockedDates', 'services']);

            $blockedDates  = $propiedad->blockedDates->pluck('date')->map(fn($d) => $d->format('Y-m-d'));
            $reservedDates = $propiedad->reservations()
                ->where('status', 'confirmed')
                ->get()
                ->flatMap(function ($res) {
                    $dates   = [];
                    $current = $res->check_in->copy();
                    while ($current->lte($res->check_out)) {
                        $dates[] = $current->format('Y-m-d');
                        $current->addDay();
                    }
                    return $dates;
                });

            return [
                'propiedad'         => $propiedad,
                'unavailableDates'  => $blockedDates->merge($reservedDates)->unique()->values(),
                'similarPropiedades' => Property::active()
                    ->where('city', $propiedad->city)
                    ->where('id', '!=', $propiedad->id)
                    ->with('images')
                    ->take(4)
                    ->get(),
            ];
        });

        $propiedad          = $cached['propiedad'];
        $unavailableDates   = $cached['unavailableDates'];
        $similarPropiedades = $cached['similarPropiedades'];

        // Always fresh — depends on the authenticated user
        $reservaParaReseña = null;
        if (auth()->check()) {
            $reservaParaReseña = $propiedad->reservations()
                ->where('user_id', auth()->id())
                ->where('status', 'completed')
                ->whereDoesntHave('review')
                ->latest()
                ->first();
        }

        return view('propiedades.show', compact('propiedad', 'unavailableDates', 'similarPropiedades', 'reservaParaReseña'));
    }

    // Owner CRUD
    public function create()
    {
        $this->authorize('create', Property::class);
        $amenitiesList = Property::amenitiesList();
        $provinces = Province::where('active', true)->orderBy('order')->get(['id', 'name']);
        return view('propiedades.create', compact('amenitiesList', 'provinces'));
    }

    public function store(Request $request)
    {
        $this->authorize('create', Property::class);

        $this->stripEmptyDiscountRows($request);

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'short_description' => 'nullable|string|max:500',
            'street_name' => 'required|string|max:255',
            'street_number' => 'required|string|max:20',
            'locality' => 'required|string|max:255',
            'partido' => 'required|string|max:255',
            'state' => 'required|string',
            'country' => 'nullable|string|max:100',
            'price_per_hour'  => 'nullable|numeric|min:1',
            'price_per_day'   => 'required|numeric|min:1',
            'price_weekend'   => 'nullable|numeric|min:0',
            'day_discounts'              => 'nullable|array',
            'day_discounts.*.days'       => 'required_with:day_discounts.*|integer|min:1',
            'day_discounts.*.discount'   => 'required_with:day_discounts.*|numeric|min:1|max:99',
            'date_discounts'             => 'nullable|array',
            'date_discounts.*.date_from' => 'required_with:date_discounts.*|date',
            'date_discounts.*.date_to'   => 'required_with:date_discounts.*|date',
            'date_discounts.*.discount'  => 'required_with:date_discounts.*|numeric|min:1|max:99',
            'date_discounts.*.label'     => 'nullable|string|max:100',
            'weekday_discounts'          => 'nullable|array',
            'weekday_discounts.*.days'   => 'required_with:weekday_discounts.*|array',
            'weekday_discounts.*.discount' => 'required_with:weekday_discounts.*|numeric|min:1|max:99',
            'capacity' => 'required|integer|min:1',
            'beds'     => 'required|integer|min:0',
            'rooms'    => 'required|integer|min:0',
            'bathrooms' => 'required|integer|min:0',
            'parking_spots' => 'required|integer|min:0',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'map_url' => 'nullable|url|max:500',
            'amenities' => 'nullable|array',
            'rules' => 'nullable|string',
            'min_days' => 'nullable|integer|min:1',
            'max_days' => 'nullable|integer|min:1',
            'type' => 'nullable|string|max:100',
            'available_from' => 'nullable|date_format:H:i',
            'available_to' => 'nullable|date_format:H:i',
            'images.*' => 'nullable|image|mimes:jpeg,jpg,png,webp|max:5120',
        ]);

        $data['address'] = trim($data['street_name'] . ' ' . $data['street_number']);
        $data['city'] = $data['locality'];
        $data['country'] = $data['country'] ?? 'Argentina';
        $data['user_id'] = Auth::id();
        $data['slug'] = Str::slug($data['name']);
        $data['rules'] = $request->filled('rules') ? explode("\n", $request->rules) : null;
        $data['status'] = 'active';
        $this->filterDiscounts($data);

        $propiedad = Property::create($data);

        PropertyCache::clearListings();

        // Services
        $this->syncServices($propiedad, $request->input('services', []));

        // Log custom amenities (not in predefined list)
        $knownKeys = array_keys(Property::amenitiesList());
        foreach (array_diff($data['amenities'] ?? [], $knownKeys) as $custom) {
            PropertyAmenityLog::create(['property_id' => $propiedad->id, 'amenity' => $custom]);
        }

        $uploadedPaths = [];
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $index => $image) {
                $path = $image->store('propiedades', 'public');
                $uploadedPaths[] = $path;
                PropertyImage::create([
                    'property_id' => $propiedad->id,
                    'path' => $path,
                    'is_primary' => $index === 0,
                    'order' => $index,
                ]);
                if ($index === 0) {
                    $propiedad->update(['cover_image' => $path]);
                }
            }
        }

        // Si no tiene suscripción, verificar si hay datos de contacto
        $pendingByContact = false;
        if (Auth::user()->needsSubscription() && $this->hasContactInfo($request, $data, $uploadedPaths)) {
            $propiedad->update(['status' => 'pending']);
            $pendingByContact = true;
        }

        if (Auth::user()->needsSubscription()) {
            return redirect()->route('subscription.payment')
                ->with('success', $pendingByContact
                    ? 'Tu propiedad está pendiente de revisión. Te avisaremos cuando sea aprobada.'
                    : 'Tu propiedad fue publicada. Activá tu suscripción para que los clientes puedan contactarte.')
                ->with('success_property_slug', $propiedad->slug);
        }

        return redirect()->route('owner.properties.index')
            ->with('success', 'Propiedad publicada correctamente.');
    }

    public function edit(Property $propiedad)
    {
        $this->authorize('update', $propiedad);
        $amenitiesList = Property::amenitiesList();
        $typesList = Property::typesList();
        $provinces = Province::where('active', true)->orderBy('order')->get(['id', 'name']);
        $propiedad->load('images', 'services');
        return view('propiedades.edit', compact('propiedad', 'amenitiesList', 'typesList', 'provinces'));
    }

    public function update(Request $request, Property $propiedad)
    {
        $this->authorize('update', $propiedad);

        $this->stripEmptyDiscountRows($request);

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'short_description' => 'nullable|string|max:500',
            'street_name' => 'required|string|max:255',
            'street_number' => 'required|string|max:20',
            'locality' => 'required|string|max:255',
            'partido' => 'required|string|max:255',
            'state' => 'required|string',
            'country' => 'nullable|string|max:100',
            'price_per_hour'  => 'nullable|numeric|min:1',
            'price_per_day'   => 'required|numeric|min:0',
            'price_weekend'   => 'nullable|numeric|min:0',
            'day_discounts'              => 'nullable|array',
            'day_discounts.*.days'       => 'required_with:day_discounts.*|integer|min:1',
            'day_discounts.*.discount'   => 'required_with:day_discounts.*|numeric|min:1|max:99',
            'date_discounts'             => 'nullable|array',
            'date_discounts.*.date_from' => 'required_with:date_discounts.*|date',
            'date_discounts.*.date_to'   => 'required_with:date_discounts.*|date',
            'date_discounts.*.discount'  => 'required_with:date_discounts.*|numeric|min:1|max:99',
            'date_discounts.*.label'     => 'nullable|string|max:100',
            'weekday_discounts'          => 'nullable|array',
            'weekday_discounts.*.days'   => 'required_with:weekday_discounts.*|array',
            'weekday_discounts.*.discount' => 'required_with:weekday_discounts.*|numeric|min:1|max:99',
            'capacity' => 'required|integer|min:1',
            'beds'     => 'required|integer|min:0',
            'rooms'    => 'required|integer|min:0',
            'bathrooms' => 'required|integer|min:0',
            'parking_spots' => 'required|integer|min:0',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'map_url' => 'nullable|url|max:500',
            'amenities' => 'nullable|array',
            'rules' => 'nullable|string',
            'min_days' => 'nullable|integer|min:1',
            'max_days' => 'nullable|integer|min:1',
            'type' => 'nullable|string|max:100',
            'available_from' => 'nullable|date_format:H:i',
            'available_to' => 'nullable|date_format:H:i',
            'images.*' => 'nullable|image|mimes:jpeg,jpg,png,webp|max:5120',
        ]);

        $data['address'] = trim($data['street_name'] . ' ' . $data['street_number']);
        $data['city'] = $data['locality'];
        $data['country'] = $data['country'] ?? 'Argentina';
        $data['rules'] = $request->filled('rules') ? explode("\n", $request->rules) : null;
        $this->filterDiscounts($data);

        // Log newly added custom amenities
        $knownKeys   = array_keys(Property::amenitiesList());
        $oldCustom   = array_diff($propiedad->amenities ?? [], $knownKeys);
        $newCustom   = array_diff($data['amenities'] ?? [], $knownKeys);
        foreach (array_diff($newCustom, $oldCustom) as $custom) {
            PropertyAmenityLog::create(['property_id' => $propiedad->id, 'amenity' => $custom]);
        }

        $propiedad->update($data);

        // Delete marked images
        if ($request->filled('delete_images')) {
            $deleteIds = array_map('intval', (array) $request->delete_images);
            $toDelete = PropertyImage::whereIn('id', $deleteIds)
                ->where('property_id', $propiedad->id)
                ->get();
            foreach ($toDelete as $img) {
                Storage::disk('public')->delete($img->path);
                $img->delete();
            }
        }

        // Upload new images
        $uploadedPaths = [];
        if ($request->hasFile('images')) {
            $maxOrder = $propiedad->images()->max('order') ?? 0;
            foreach ($request->file('images') as $index => $image) {
                $path = $image->store('propiedades', 'public');
                $uploadedPaths[] = $path;
                PropertyImage::create([
                    'property_id' => $propiedad->id,
                    'path' => $path,
                    'is_primary' => false,
                    'order' => $maxOrder + $index + 1,
                ]);
            }
        }

        // Set cover_image to first remaining image
        $firstImage = $propiedad->images()->orderBy('order')->first();
        if ($firstImage) {
            $propiedad->update(['cover_image' => $firstImage->path]);
        }

        // Services
        $this->syncServices($propiedad, $request->input('services', []));

        // Si no tiene suscripción, verificar datos de contacto en textos y todas las imágenes
        if (Auth::user()->needsSubscription()) {
            $allImagePaths = $propiedad->images()->pluck('path')->toArray();
            if ($this->hasContactInfo($request, $data, $allImagePaths)) {
                $propiedad->update(['status' => 'pending']);
                return redirect()->route('owner.properties.index')
                    ->with('warning', 'Tu propiedad está pendiente de revisión. Te avisaremos cuando sea aprobada.');
            }
            // Sin info de contacto: restaurar a active si estaba pendiente por esta razón
            if ($propiedad->status === 'pending') {
                $propiedad->update(['status' => 'active']);
            }
        }

        PropertyCache::clear($propiedad);

        return redirect()->route('owner.properties.index')
            ->with('success', 'Propiedad actualizada correctamente.');
    }

    private function syncServices(Property $propiedad, array $services): void
    {
        $keptIds = [];
        foreach ($services as $s) {
            if (empty($s['name'])) continue;
            $id = !empty($s['id']) ? (int) $s['id'] : null;
            $attrs = [
                'name'     => $s['name'],
                'price'    => (float) ($s['price'] ?? 0),
                'quantity' => (float) ($s['quantity'] ?? 1),
                'unit'     => $s['unit'] ?? 'unidad',
            ];
            if ($id && $propiedad->services()->where('id', $id)->exists()) {
                $propiedad->services()->where('id', $id)->update($attrs);
                $keptIds[] = $id;
            } else {
                $new = $propiedad->services()->create($attrs);
                $keptIds[] = $new->id;
            }
        }
        // Delete removed services (only those without reservations)
        $propiedad->services()->whereNotIn('id', $keptIds)
            ->whereDoesntHave('reservationServices')
            ->delete();
    }

    public function toggleStatus(Property $propiedad)
    {
        $this->authorize('update', $propiedad);
        $propiedad->status = $propiedad->status === 'active' ? 'inactive' : 'active';
        $propiedad->save();
        PropertyCache::clear($propiedad);
        $msg = $propiedad->status === 'active' ? 'Propiedad activada.' : 'Propiedad desactivada.';
        return back()->with('success', $msg);
    }

    public function destroyImage(PropertyImage $image)
    {
        $propiedad = Property::withoutGlobalScope('active')->findOrFail($image->property_id);
        $this->authorize('update', $propiedad);
        Storage::disk('public')->delete($image->path);
        $image->delete();
        return back()->with('success', 'Foto eliminada.');
    }

    public function destroy(Property $propiedad)
    {
        $this->authorize('delete', $propiedad);
        Property::withoutGlobalScope('active')->where('id', $propiedad->id)->update(['deleted' => true]);
        PropertyCache::clear($propiedad);
        return redirect()->route('owner.properties.index')
            ->with('success', 'Propiedad eliminada.');
    }

    public function citiesByProvince(Request $request)
    {
        $cities = City::whereHas('province', fn($q) => $q->where('name', $request->state))
            ->where('active', true)
            ->orderBy('order')
            ->pluck('name');

        return response()->json($cities);
    }

    /**
     * Checks name, descriptions and uploaded images for contact info.
     * Returns true if anything suspicious is found.
     */
    private function hasContactInfo(Request $request, array $data, array $storedImagePaths = []): bool
    {
        $detector = new ContactInfoDetector();

        $texts = array_filter([
            $data['name']              ?? null,
            $data['description']       ?? null,
            $data['short_description'] ?? null,
        ]);

        foreach ($texts as $text) {
            if ($detector->foundInText($text)) {
                return true;
            }
        }

        foreach ($storedImagePaths as $relativePath) {
            $fullPath = Storage::disk('public')->path($relativePath);
            if ($detector->foundInImage($fullPath)) {
                return true;
            }
        }

        return false;
    }

    private function stripEmptyDiscountRows(Request $request): void
    {
        $clean = fn(array $rows, array $keys) => array_values(
            array_filter($rows, fn($row) => collect($keys)->some(fn($k) => !empty($row[$k])))
        );

        $request->merge([
            'day_discounts'     => $clean($request->input('day_discounts', []),     ['days', 'discount']),
            'date_discounts'    => $clean($request->input('date_discounts', []),    ['date_from', 'date_to', 'discount']),
            'weekday_discounts' => $clean($request->input('weekday_discounts', []), ['days', 'discount']),
        ]);
    }

    private function filterDiscounts(array &$data): void
    {
        if (!empty($data['day_discounts'])) {
            $data['day_discounts'] = array_values(array_filter(
                $data['day_discounts'],
                fn($d) => !empty($d['days']) && isset($d['discount']) && $d['discount'] !== ''
            ));
        }
        if (!empty($data['date_discounts'])) {
            $data['date_discounts'] = array_values(array_filter(
                $data['date_discounts'],
                fn($d) => !empty($d['date_from']) && !empty($d['date_to']) && isset($d['discount']) && $d['discount'] !== ''
            ));
        }
        if (!empty($data['weekday_discounts'])) {
            $data['weekday_discounts'] = array_values(array_filter(
                $data['weekday_discounts'],
                fn($d) => !empty($d['days']) && isset($d['discount']) && $d['discount'] !== ''
            ));
            // Convertir días a enteros
            foreach ($data['weekday_discounts'] as &$row) {
                $row['days'] = array_map('intval', (array) $row['days']);
            }
        }
    }
}
