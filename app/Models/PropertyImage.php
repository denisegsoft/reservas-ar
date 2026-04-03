<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PropertyImage extends Model
{
    protected $table = 'property_images';

    protected $fillable = ['property_id', 'path', 'caption', 'is_primary', 'order'];

    protected function casts(): array
    {
        return ['is_primary' => 'boolean'];
    }

    public function property() { return $this->belongsTo(Property::class); }

    public function getUrlAttribute(): string
    {
        return asset('storage/' . $this->path);
    }
}
