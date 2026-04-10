<?php

namespace App\Support;

use App\Models\Property;
use Illuminate\Support\Facades\Cache;

class PropertyCache
{
    public static function forShow(string $slug): string
    {
        return 'property.show.' . $slug;
    }

    /**
     * Clear all caches related to a specific property, plus the home and index listings.
     */
    public static function clear(Property $propiedad): void
    {
        Cache::forget(self::forShow($propiedad->slug));
        Cache::forget('home');
        Cache::forget('properties.index');
    }

    /**
     * Clear only the home and index listing caches (e.g. when a review is approved
     * and the property rating changes but no property record was directly modified).
     */
    public static function clearListings(): void
    {
        Cache::forget('home');
        Cache::forget('properties.index');
    }
}
