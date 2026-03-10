<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompraPago extends Model
{
    protected $table = 'compra_pagos';

    protected $fillable = [
        'compra_lote_id',
        'fecha',
        'monto',
        'observacion',
    ];

    protected $casts = [
        'fecha' => 'date',
    ];

    public function lote()
    {
        return $this->belongsTo(CompraLote::class, 'compra_lote_id');
    }
}