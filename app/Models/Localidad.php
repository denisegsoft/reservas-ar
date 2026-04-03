<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Localidad extends Model
{
    protected $table = 'localidades';
    protected $fillable = ['partido_id', 'name'];

    public function partido()
    {
        return $this->belongsTo(Partido::class);
    }
}
