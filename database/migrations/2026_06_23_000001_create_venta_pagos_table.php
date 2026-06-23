<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('venta_pagos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('venta_id')->constrained('ventas')->cascadeOnDelete();
            $table->string('metodo_pago', 30);
            $table->decimal('monto_base', 12, 2)->default(0);
            $table->decimal('recargo', 12, 2)->default(0);
            $table->decimal('monto', 12, 2)->default(0);
            $table->dateTime('fecha_pago');
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['fecha_pago', 'metodo_pago']);
            $table->index(['venta_id', 'metodo_pago']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('venta_pagos');
    }
};
