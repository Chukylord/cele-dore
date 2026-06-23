<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Producto extends Model
{
    protected $fillable = [
        'proveedor_id',
        'marca',
        'tipo',
        'contenido',
        'precio_venta',
        'stock_venta',
        'stock_peluqueria',
        'stock_minimo',
        'precio_efectivo_manual',
        'precio_tarjeta_manual',
        'precio_manual_updated_at',
        'codigo_barra',
    ];

    protected $casts = [
        'precio_venta' => 'decimal:2',
        'precio_efectivo_manual' => 'decimal:2',
        'precio_tarjeta_manual' => 'decimal:2',
        'precio_manual_updated_at' => 'datetime',
    ];

    public function proveedor()
    {
        return $this->belongsTo(Proveedor::class);
    }
}
