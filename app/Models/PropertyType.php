<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PropertyType extends Model
{
    protected $fillable = ['slug', 'name', 'active', 'order'];

    public static function list(): array
    {
        return static::where('active', true)
            ->orderBy('order')
            ->pluck('name', 'slug')
            ->toArray();
    }
}
