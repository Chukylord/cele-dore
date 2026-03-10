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
        Schema::create('turnos', function (Blueprint $table) {
            $table->id();

            $table->foreignId('cliente_id')->constrained('clientes');
            $table->foreignId('colaboradora_id')->nullable()->constrained('colaboradoras');

            $table->string('titulo')->nullable(); // ej: Corte / Color
            $table->text('detalle')->nullable();

            $table->dateTime('inicio');
            $table->dateTime('fin')->nullable();

            $table->string('estado')->default('pendiente'); // pendiente, confirmado, cancelado, atendido

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('turnos');
    }
};
