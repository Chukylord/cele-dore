<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Venta extends Model
{
    protected $fillable = [
        'fecha',
        'vendedora_id',
        'cliente_id',
        'cliente_colaboradora_id',
        'metodo_pago',
        'subtotal_servicios',
        'subtotal_productos',
        'comision_monto',
        'total',
        'notas',
        'pendiente_pago',
        'liquidacion_id',
        'fecha_pago',
    ];

    protected $casts = [
        'fecha' => 'datetime',
        'fecha_pago' => 'datetime',
        'pendiente_pago' => 'boolean',
    ];

    public function vendedora()
    {
        return $this->belongsTo(Colaboradora::class, 'vendedora_id');
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function clienteColaboradora()
    {
        return $this->belongsTo(Colaboradora::class, 'cliente_colaboradora_id');
    }

    public function productos()
    {
        return $this->hasMany(VentaProducto::class);
    }

    public function servicios()
    {
        return $this->hasMany(VentaServicio::class);
    }

    public function liquidacion()
    {
        return $this->belongsTo(Liquidacion::class);
    }
}