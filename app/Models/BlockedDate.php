<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BlockedDate extends Model
{
    protected $fillable = ['property_id', 'date', 'reason'];

    protected function casts(): array
    {
        return ['date' => 'date'];
    }

    public function property() { return $this->belongsTo(Property::class); }
}
