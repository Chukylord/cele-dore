<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Liquidacion extends Model
{
    protected $table = 'liquidaciones';

    protected $fillable = [
        'colaboradora_id',
        'fecha_pago',
        'valor_hora',
        'minutos_normales',
        'minutos_extras',
        'monto_horas_normales',
        'monto_horas_extras',
        'monto_comision',
        'monto_productos_costo',
        'total_pagado',
        'observaciones',
    ];

    protected $casts = [
        'fecha_pago' => 'date',
    ];

    public function colaboradora()
    {
        return $this->belongsTo(Colaboradora::class);
    }
}