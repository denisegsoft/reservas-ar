<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\ReservationService;

class Reservation extends Model
{
    use HasFactory;

    protected $fillable = [
        'property_id', 'user_id', 'check_in', 'check_in_time', 'check_out', 'check_out_time', 'guests',
        'price_per_day', 'total_days', 'subtotal', 'price_breakdown', 'service_fee', 'total_amount',
        'status', 'payment_status', 'payment_method', 'notes', 'cancellation_reason', 'cancelled_at',
        'checkin_reminder_sent_at', 'review_requested_at',
        'invoice_path', 'invoice_uploaded_at',
    ];

    protected function casts(): array
    {
        return [
            'check_in' => 'date',
            'check_out' => 'date',
            'cancelled_at' => 'datetime',
            'checkin_reminder_sent_at' => 'datetime',
            'review_requested_at' => 'datetime',
            'invoice_uploaded_at' => 'datetime',
            'price_per_day' => 'decimal:2',
            'subtotal' => 'decimal:2',
            'price_breakdown' => 'array',
            'service_fee' => 'decimal:2',
            'total_amount' => 'decimal:2',
        ];
    }

    public function property() { return $this->belongsTo(Property::class); }
    public function user() { return $this->belongsTo(User::class); }
    public function payment() { return $this->hasOne(Payment::class); }
    public function review() { return $this->hasOne(Review::class); }
    public function services() { return $this->hasMany(ReservationService::class)->with('propertyService'); }
    public function extraCosts() { return $this->hasMany(ReservationExtraCost::class); }
    public function discounts() { return $this->hasMany(ReservationDiscount::class); }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'pending' => 'Pendiente',
            'confirmed' => 'Confirmada',
            'cancelled' => 'Cancelada',
            'completed' => 'Completada',
            default => $this->status,
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'pending' => 'yellow',
            'confirmed' => 'green',
            'cancelled' => 'red',
            'completed' => 'blue',
            default => 'gray',
        };
    }

    public function isPending(): bool { return $this->status === 'pending'; }
    public function isConfirmed(): bool { return $this->status === 'confirmed'; }
    public function isCancelled(): bool { return $this->status === 'cancelled'; }
    public function isPaid(): bool { return $this->payment_status === 'paid'; }
}
