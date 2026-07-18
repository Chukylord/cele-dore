<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Gasto extends Model
{
    protected $table = 'gastos';

    protected $fillable = [
        'fecha',
        'categoria',
        'descripcion',
        'monto',
        'medio_pago',
        'user_id',
    ];

    protected $casts = [
        'fecha' => 'date',
        'monto' => 'decimal:2',
    ];

    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
