<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupportTicket extends Model
{
    protected $fillable = ['user_id', 'email', 'phone', 'subject', 'message', 'status'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
