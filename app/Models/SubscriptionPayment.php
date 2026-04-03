<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubscriptionPayment extends Model
{
    protected $fillable = [
        'user_id',
        'mp_preference_id',
        'mp_payment_id',
        'amount',
        'status',
        'mp_status_detail',
        'payment_method',
        'payment_type',
        'mp_response',
        'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'mp_response' => 'array',
            'paid_at'     => 'datetime',
            'amount'      => 'decimal:2',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }
}
