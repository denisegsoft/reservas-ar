<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Partido extends Model
{
    protected $table = 'partidos';
    protected $fillable = ['province_id', 'name'];

    public function province()
    {
        return $this->belongsTo(\App\Models\Province::class);
    }

    public function localidades()
    {
        return $this->hasMany(Localidad::class)->orderBy('name');
    }
}
