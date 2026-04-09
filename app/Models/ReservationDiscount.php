<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReservationDiscount extends Model
{
    protected $fillable = ['reservation_id', 'name', 'price'];

    protected function casts(): array
    {
        return ['price' => 'decimal:2'];
    }

    public function reservation()
    {
        return $this->belongsTo(Reservation::class);
    }
}
