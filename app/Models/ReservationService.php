<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReservationService extends Model
{
    protected $fillable = ['reservation_id', 'property_service_id', 'quantity', 'price'];

    protected $casts = [
        'price'    => 'decimal:2',
        'quantity' => 'decimal:2',
    ];

    public function reservation()
    {
        return $this->belongsTo(Reservation::class);
    }

    public function propertyService()
    {
        return $this->belongsTo(PropertyService::class);
    }

    public function getSubtotalAttribute(): float
    {
        return (float) $this->price * (float) $this->quantity;
    }
}
