<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use App\Models\PropertyType;

class Property extends Model
{
    use HasFactory;

    protected $table = 'properties';

    protected $fillable = [
        'user_id', 'name', 'slug', 'description', 'short_description',
        'address', 'street_name', 'street_number', 'locality', 'partido', 'city', 'state', 'country', 'zip_code', 'latitude', 'longitude', 'map_url',
        'price_per_hour', 'price_per_day', 'price_per_week', 'price_per_month', 'price_weekend', 'capacity', 'bedrooms', 'bathrooms',
        'parking_spots', 'amenities', 'cover_image', 'status', 'rating',
        'reviews_count', 'featured', 'rules', 'min_days', 'max_days', 'type', 'available_from', 'available_to', 'deleted', 'views_count',
    ];

    protected function casts(): array
    {
        return [
            'amenities' => 'array',
            'rules' => 'array',
            'featured' => 'boolean',
            'price_per_hour' => 'decimal:2',
            'price_per_day' => 'decimal:2',
            'price_per_week' => 'decimal:2',
            'price_per_month' => 'decimal:2',
            'price_weekend' => 'decimal:2',
            'rating' => 'decimal:2',
            'latitude' => 'decimal:8',
            'longitude' => 'decimal:8',
        ];
    }

    protected static function booted(): void
    {
        static::addGlobalScope('active', fn ($q) => $q->where('deleted', false));

        static::creating(function ($propiedad) {
            if (empty($propiedad->slug)) {
                $propiedad->slug = Str::slug($propiedad->name);
            }
        });
    }

    public function owner() { return $this->belongsTo(User::class, 'user_id'); }
    public function images() { return $this->hasMany(PropertyImage::class)->orderBy('order'); }
    public function primaryImage() { return $this->hasOne(PropertyImage::class)->where('is_primary', true); }
    public function reservations() { return $this->hasMany(Reservation::class); }
    public function reviews() { return $this->hasMany(Review::class)->where('approved', true); }
    public function blockedDates() { return $this->hasMany(BlockedDate::class); }

    public function getCoverImageUrlAttribute(): string
    {
        if ($this->cover_image) {
            return asset('storage/' . $this->cover_image);
        }
        // Fallback to first image (uses eager-loaded relation if available)
        $first = $this->relationLoaded('images')
            ? $this->images->first()
            : $this->images()->first();
        if ($first) {
            return asset('storage/' . $first->path);
        }
        return asset('images/propiedad-placeholder.jpg');
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function isAvailable(string $checkIn, string $checkOut): bool
    {
        $blocked = $this->blockedDates()
            ->whereBetween('date', [$checkIn, $checkOut])
            ->exists();

        if ($blocked) return false;

        return !$this->reservations()
            ->whereIn('status', ['confirmed', 'pending'])
            ->where(function ($q) use ($checkIn, $checkOut) {
                $q->whereBetween('check_in', [$checkIn, $checkOut])
                  ->orWhereBetween('check_out', [$checkIn, $checkOut])
                  ->orWhere(function ($q) use ($checkIn, $checkOut) {
                      $q->where('check_in', '<=', $checkIn)->where('check_out', '>=', $checkOut);
                  });
            })
            ->exists();
    }

    public static function typesList(): array
    {
        return PropertyType::list();
    }

    public static function amenitiesList(): array
    {
        return [
            'pileta' => ['label' => 'Pileta/Piscina', 'icon' => '🏊'],
            'parrilla' => ['label' => 'Parrilla', 'icon' => '🔥'],
            'quincho' => ['label' => 'Quincho', 'icon' => '🏠'],
            'wifi' => ['label' => 'WiFi', 'icon' => '📶'],
            'estacionamiento' => ['label' => 'Estacionamiento', 'icon' => '🚗'],
            'aire_acondicionado' => ['label' => 'Aire Acondicionado', 'icon' => '❄️'],
            'calefaccion' => ['label' => 'Calefacción', 'icon' => '🌡️'],
            'cancha_futbol' => ['label' => 'Cancha de Fútbol', 'icon' => '⚽'],
            'cancha_tenis' => ['label' => 'Cancha de Tenis', 'icon' => '🎾'],
            'juegos_ninos' => ['label' => 'Juegos para Niños', 'icon' => '🛝'],
            'fogon' => ['label' => 'Fogón', 'icon' => '🔥'],
            'jacuzzi' => ['label' => 'Jacuzzi', 'icon' => '🛁'],
            'salon_eventos' => ['label' => 'Salón de Eventos', 'icon' => '🎉'],
            'cocina_equipada' => ['label' => 'Cocina Equipada', 'icon' => '🍳'],
            'lavarropas' => ['label' => 'Lavarropas', 'icon' => '🫧'],
            'tv_smart' => ['label' => 'Smart TV', 'icon' => '📺'],
            'sonido' => ['label' => 'Sistema de Sonido', 'icon' => '🔊'],
            'seguridad' => ['label' => 'Seguridad 24hs', 'icon' => '🔒'],
        ];
    }
}
