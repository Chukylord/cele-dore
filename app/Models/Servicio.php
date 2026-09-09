<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Servicio extends Model
{
    protected $fillable = [
        'nombre',
        'precio',
    ];

    public function turnos()
    {
        return $this->belongsToMany(Turno::class, 'turno_servicio')->withTimestamps();
    }
}
