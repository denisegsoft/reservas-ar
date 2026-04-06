<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PropertyService extends Model
{
    protected $fillable = ['property_id', 'name', 'price', 'quantity', 'unit'];

    protected $casts = [
        'price'    => 'decimal:2',
        'quantity' => 'decimal:2',
    ];

    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    public function reservationServices()
    {
        return $this->hasMany(ReservationService::class);
    }
}
