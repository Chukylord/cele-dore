<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VentaServicio extends Model
{
    protected $table = 'venta_servicios';

    protected $fillable = [
        'venta_id',
        'servicio_id',
        'precio',
        'detalle',
    ];

    public function servicio()
    {
        return $this->belongsTo(Servicio::class);
    }

    public function venta()
    {
        return $this->belongsTo(Venta::class);
    }
}