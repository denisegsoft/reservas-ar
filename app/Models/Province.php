<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Province extends Model
{
    protected $fillable = ['name', 'slug', 'active', 'order'];

    public function cities()
    {
        return $this->hasMany(City::class)->orderBy('order');
    }

    public function properties()
    {
        return $this->hasMany(Property::class, 'state', 'name');
    }
}
