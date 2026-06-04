<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProveedorPago extends Model
{
    protected $table = 'proveedor_pagos';

    protected $fillable = [
        'proveedor_id',
        'fecha',
        'monto',
        'observacion',
    ];

    protected $casts = [
        'fecha' => 'date',
    ];

    public function proveedor()
    {
        return $this->belongsTo(Proveedor::class);
    }
}