<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Fichada extends Model
{
    protected $fillable = [
        'colaboradora_id',
        'fecha',
        'hora_inicio',
        'hora_fin',
        'minutos_trabajados',
        'minutos_normales',
        'minutos_extras',
        'liquidacion_id',
        'es_extra',
    ];

    protected $casts = [
        'fecha' => 'date',
        'es_extra' => 'boolean',
    ];

    public function colaboradora()
    {
        return $this->belongsTo(Colaboradora::class);
    }

    public function liquidacion()
    {
        return $this->belongsTo(Liquidacion::class);
    }
}