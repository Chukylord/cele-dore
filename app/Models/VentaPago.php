<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VentaPago extends Model
{
    protected $table = 'venta_pagos';

    protected $fillable = [
        'venta_id',
        'metodo_pago',
        'monto_base',
        'recargo',
        'monto',
        'fecha_pago',
        'user_id',
    ];

    protected $casts = [
        'monto_base' => 'decimal:2',
        'recargo' => 'decimal:2',
        'monto' => 'decimal:2',
        'fecha_pago' => 'datetime',
    ];

    public function venta()
    {
        return $this->belongsTo(Venta::class);
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
