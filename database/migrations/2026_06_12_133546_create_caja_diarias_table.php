<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cajas_diarias', function (Blueprint $table) {
            $table->id();

            $table->date('fecha')->unique();

            $table->decimal('caja_inicial', 12, 2)->default(0);

            $table->decimal('ventas_efectivo', 12, 2)->default(0);
            $table->decimal('ventas_transferencia', 12, 2)->default(0);
            $table->decimal('ventas_tarjeta', 12, 2)->default(0);

            $table->decimal('efectivo_esperado', 12, 2)->default(0);
            $table->decimal('efectivo_contado', 12, 2)->nullable();
            $table->decimal('diferencia', 12, 2)->nullable();

            $table->enum('estado', ['abierta', 'cerrada'])->default('abierta');

            $table->timestamp('fecha_apertura')->nullable();
            $table->timestamp('fecha_cierre')->nullable();

            $table->text('observaciones')->nullable();

            $table->foreignId('abierta_por')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('cerrada_por')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cajas_diarias');
    }
};