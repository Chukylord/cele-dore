<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompraLote extends Model
{
    protected $table = 'compra_lotes';

    protected $fillable = [
        'fecha',
        'nota',
        'monto_total',
        'monto_pagado',
        'estado_pago',
    ];

    protected $casts = [
        'fecha' => 'date',
    ];

    public function compras()
    {
        return $this->hasMany(Compra::class, 'lote_id');
    }

    public function pagos()
    {
        return $this->hasMany(CompraPago::class, 'compra_lote_id');
    }
}