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
        Schema::create('productos', function (Blueprint $table) {
            $table->id();

            $table->foreignId('proveedor_id')->constrained('proveedores');

            $table->string('marca');
            $table->string('tipo');          // ej: shampoo, crema, tintura...
            $table->string('contenido')->nullable(); // ej: 250ml, 1L, 60g...

            $table->decimal('precio_venta', 10, 2)->default(0);

            $table->integer('stock_venta')->default(0);      // stock para vender
            $table->integer('stock_peluqueria')->default(0); // stock de uso interno

            $table->integer('stock_minimo')->default(2);

            $table->string('codigo_barra')->nullable()->index();

            $table->timestamps();

            $table->index(['marca', 'tipo']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('productos');
    }
};
