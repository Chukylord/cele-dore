<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Colaboradora extends Model
{
    protected $fillable = [
        'nombre',
        'apellido',
        'telefono',
        'activa',
        'comision_pct',
    ];
}