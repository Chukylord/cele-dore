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
        'codigo_barra',
    ];

    public function proveedor()
    {
        return $this->belongsTo(Proveedor::class);
    }
}