<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PropertyAmenityLog extends Model
{
    public $timestamps = false;

    protected $fillable = ['property_id', 'amenity', 'added_at'];
}
