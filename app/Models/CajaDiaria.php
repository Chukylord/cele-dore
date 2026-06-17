<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CajaDiaria extends Model
{
    protected $table = 'cajas_diarias';

    protected $fillable = [
        'fecha',
        'caja_inicial',
        'ventas_efectivo',
        'ventas_transferencia',
        'ventas_tarjeta',
        'efectivo_esperado',
        'efectivo_contado',
        'diferencia',
        'estado',
        'fecha_apertura',
        'fecha_cierre',
        'observaciones',
        'abierta_por',
        'cerrada_por',
    ];

    protected $casts = [
        'fecha' => 'date',
        'caja_inicial' => 'decimal:2',
        'ventas_efectivo' => 'decimal:2',
        'ventas_transferencia' => 'decimal:2',
        'ventas_tarjeta' => 'decimal:2',
        'efectivo_esperado' => 'decimal:2',
        'efectivo_contado' => 'decimal:2',
        'diferencia' => 'decimal:2',
        'fecha_apertura' => 'datetime',
        'fecha_cierre' => 'datetime',
    ];

    public function estaAbierta(): bool
    {
        return $this->estado === 'abierta';
    }

    public function estaCerrada(): bool
    {
        return $this->estado === 'cerrada';
    }

    public function usuarioApertura()
    {
        return $this->belongsTo(User::class, 'abierta_por');
    }

    public function usuarioCierre()
    {
        return $this->belongsTo(User::class, 'cerrada_por');
    }
}