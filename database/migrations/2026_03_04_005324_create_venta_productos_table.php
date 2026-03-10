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
        Schema::create('venta_productos', function (Blueprint $table) {
            $table->id();

            $table->foreignId('venta_id')->constrained('ventas')->onDelete('cascade');
            $table->foreignId('producto_id')->constrained('productos');

            $table->integer('cantidad')->default(1);

            // costo usado como referencia ese día (si existía)
            $table->decimal('costo_unitario_ref', 10, 2)->nullable();

            // precio unitario final cobrado
            $table->decimal('precio_unitario', 10, 2)->default(0);

            $table->decimal('subtotal', 10, 2)->default(0);

            $table->timestamps();

            $table->index(['venta_id', 'producto_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('venta_productos');
    }
};
