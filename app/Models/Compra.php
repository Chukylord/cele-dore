<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Compra extends Model
{
    protected $fillable = [
        'proveedor_id',
        'producto_id',
        'cantidad',
        'precio_unitario',
        'descuento_pct',
        'fecha',
        'lote_id',
    ];

    public function proveedor()
    {
        return $this->belongsTo(Proveedor::class);
    }

    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }

    public function lote()
    {
        return $this->belongsTo(\App\Models\CompraLote::class, 'lote_id');
    }
}