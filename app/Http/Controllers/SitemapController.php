<?php

namespace App\Http\Controllers;

use App\Models\Property;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Url;

class SitemapController extends Controller
{
    public function index()
    {
        $sitemap = Sitemap::create();

        // Páginas estáticas
        $sitemap->add(
            Url::create(route('home'))
                ->setChangeFrequency(Url::CHANGE_FREQUENCY_DAILY)
                ->setPriority(1.0)
        );

        $sitemap->add(
            Url::create(route('properties.index'))
                ->setChangeFrequency(Url::CHANGE_FREQUENCY_DAILY)
                ->setPriority(0.9)
        );

        // Propiedades activas
        Property::where('status', 'active')
            ->select(['slug', 'updated_at'])
            ->orderByDesc('updated_at')
            ->each(function (Property $property) use ($sitemap) {
                $sitemap->add(
                    Url::create(route('properties.show', $property->slug))
                        ->setLastModificationDate($property->updated_at)
                        ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)
                        ->setPriority(0.8)
                );
            });

        return $sitemap->toResponse(request());
    }
}
