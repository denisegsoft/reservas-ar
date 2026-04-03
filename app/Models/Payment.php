<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'reservation_id', 'mp_preference_id', 'mp_payment_id', 'mp_merchant_order_id',
        'amount', 'currency', 'status', 'payment_method', 'payment_type', 'mp_response', 'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'mp_response' => 'array',
            'paid_at' => 'datetime',
            'amount' => 'decimal:2',
        ];
    }

    public function reservation() { return $this->belongsTo(Reservation::class); }

    public function isApproved(): bool { return $this->status === 'approved'; }
}
