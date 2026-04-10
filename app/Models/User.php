<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name', 'last_name', 'email', 'password', 'role', 'phone', 'dni', 'avatar', 'newsletter',
        'whatsapp_link', 'website',
        'bank_holder', 'bank_cbu', 'bank_alias',
        'social_instagram', 'social_facebook', 'social_twitter', 'social_tiktok', 'social_youtube',
        'deleted', 'subscription_paid', 'subscription_paid_at',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope('active', fn ($q) => $q->where('deleted', false));
    }

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at'    => 'datetime',
            'password'             => 'hashed',
            'subscription_paid'    => 'boolean',
            'subscription_paid_at' => 'datetime',
        ];
    }

    public function isAdmin(): bool { return $this->role === 'admin'; }

    // Un usuario es vendedor si tiene al menos una propiedad publicada
    public function isOwner(): bool { return $this->isAdmin() || $this->propiedades()->exists(); }

    // Cualquier usuario autenticado puede reservar
    public function isClient(): bool { return true; }

    public function subscriptionPayments() { return $this->hasMany(SubscriptionPayment::class); }

    public function hasSubscription(): bool
    {
        return $this->subscription_paid === true
            && $this->subscriptionPayments()->where('status', 'approved')->exists();
    }

    /**
     * Returns true when this owner must activate their subscription to access gated features.
     * Admins are always exempt.
     */
    public function needsSubscription(): bool
    {
        return !$this->isAdmin() && !$this->hasSubscription();
    }

    public function propiedades() { return $this->hasMany(Property::class); }
    public function reservations() { return $this->hasMany(Reservation::class); }
    public function reviews() { return $this->hasMany(Review::class); }
    public function favorites() { return $this->belongsToMany(Property::class, 'favorites'); }
    public function sentMessages() { return $this->hasMany(Message::class, 'sender_id'); }
    public function receivedMessages() { return $this->hasMany(Message::class, 'receiver_id'); }

    public function unreadMessagesCount(): int
    {
        return Message::where('receiver_id', $this->id)->whereNull('read_at')->count();
    }

    public function getFullNameAttribute(): string
    {
        return trim($this->name . ' ' . $this->last_name);
    }

    public function getAvatarUrlAttribute(): string
    {
        if ($this->avatar) {
            if (str_starts_with($this->avatar, 'http')) {
                return $this->avatar;
            }
            return asset('storage/' . $this->avatar);
        }
        return "https://ui-avatars.com/api/?name=" . urlencode($this->full_name) . "&background=6366f1&color=fff&size=128";
    }
}
