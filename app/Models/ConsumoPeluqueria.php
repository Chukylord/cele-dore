<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConsumoPeluqueria extends Model
{
    protected $table = 'consumos_peluqueria';

    protected $fillable = [
        'fecha',
        'producto_id',
        'cantidad',
        'costo_unitario',
        'total',
    ];

    protected $casts = [
        'fecha' => 'datetime',
    ];

    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }
}