<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Turno extends Model
{
    protected $fillable = [
        'cliente_id',
        'colaboradora_id',
        'titulo',
        'detalle',
        'inicio',
        'fin',
        'estado',
    ];

    protected $casts = [
        'inicio' => 'datetime',
        'fin' => 'datetime',
    ];

    public function venta()
    {
        return $this->hasOne(Venta::class);
    }

    public function servicios()
    {
        return $this->belongsToMany(Servicio::class, 'turno_servicio')->withTimestamps()->orderBy('nombre');
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function colaboradora()
    {
        return $this->belongsTo(Colaboradora::class);
    }
}
