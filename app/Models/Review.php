<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    protected $fillable = [
        'property_id', 'user_id', 'reservation_id', 'rating', 'comment', 'approved',
    ];

    protected function casts(): array
    {
        return ['approved' => 'boolean'];
    }

    public function property() { return $this->belongsTo(Property::class); }
    public function user() { return $this->belongsTo(User::class); }
    public function reservation() { return $this->belongsTo(Reservation::class); }
}
