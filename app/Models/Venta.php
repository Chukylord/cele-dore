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
        'total_base',
        'recargo_tarjeta',
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
        'subtotal_servicios' => 'decimal:2',
        'subtotal_productos' => 'decimal:2',
        'total_base' => 'decimal:2',
        'recargo_tarjeta' => 'decimal:2',
        'comision_monto' => 'decimal:2',
        'total' => 'decimal:2',
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

    public function pagos()
    {
        return $this->hasMany(VentaPago::class);
    }

    public function liquidacion()
    {
        return $this->belongsTo(Liquidacion::class);
    }

    public function totalBaseReal(): float
    {
        $totalBase = (float) $this->total_base;

        if ($totalBase <= 0) {
            $totalBase = (float) $this->subtotal_servicios
                + (float) $this->subtotal_productos;
        }

        if ($totalBase <= 0) {
            $totalBase = (float) $this->total;
        }

        return round(max($totalBase, 0), 2);
    }

    public function totalPagadoBase(): float
    {
        $pagos = $this->relationLoaded('pagos')
            ? $this->pagos
            : $this->pagos()->get();

        return round((float) $pagos->sum('monto_base'), 2);
    }

    public function totalCobrado(): float
    {
        $pagos = $this->relationLoaded('pagos')
            ? $this->pagos
            : $this->pagos()->get();

        return round((float) $pagos->sum('monto'), 2);
    }

    public function totalRecargoCobrado(): float
    {
        $pagos = $this->relationLoaded('pagos')
            ? $this->pagos
            : $this->pagos()->get();

        return round((float) $pagos->sum('recargo'), 2);
    }

    public function saldoPendienteBase(): float
    {
        return round(
            max($this->totalBaseReal() - $this->totalPagadoBase(), 0),
            2
        );
    }
}
