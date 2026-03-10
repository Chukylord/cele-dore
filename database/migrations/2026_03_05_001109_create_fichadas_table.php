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
        Schema::create('fichadas', function (Blueprint $table) {
            $table->id();

            $table->foreignId('colaboradora_id')->constrained('colaboradoras');

            $table->date('fecha');

            $table->time('hora_inicio');
            $table->time('hora_fin');

            $table->integer('minutos_trabajados')->default(0);

            // normales y extras en minutos
            $table->integer('minutos_normales')->default(0);
            $table->integer('minutos_extras')->default(0);

            // más adelante para liquidación
            // $table->foreignId('liquidacion_id')->nullable()->constrained('liquidaciones');
            $table->unsignedBigInteger('liquidacion_id')->nullable(); //quitar cuando se implemente liquidaciones
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fichadas');
    }
};
