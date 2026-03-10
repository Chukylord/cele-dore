<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('liquidaciones', function (Blueprint $table) {
            $table->id();

            $table->foreignId('colaboradora_id')->constrained('colaboradoras');

            $table->date('fecha_pago');

            $table->decimal('valor_hora', 10, 2)->default(0);

            $table->integer('minutos_normales')->default(0);
            $table->integer('minutos_extras')->default(0);

            $table->decimal('monto_horas_normales', 10, 2)->default(0);
            $table->decimal('monto_horas_extras', 10, 2)->default(0);

            $table->decimal('monto_comision', 10, 2)->default(0);
            $table->decimal('monto_productos_costo', 10, 2)->default(0);

            $table->decimal('total_pagado', 10, 2)->default(0);

            $table->text('observaciones')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('liquidacions');
    }
};
