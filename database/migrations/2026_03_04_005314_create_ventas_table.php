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
        Schema::create('ventas', function (Blueprint $table) {
            $table->id();

            $table->dateTime('fecha');

            // Quién vendió (colaboradora) - puede ser null si vendió la dueña
            $table->foreignId('vendedora_id')->nullable()->constrained('colaboradoras');

            // A quién se le vendió (cliente normal o colaboradora)
            $table->foreignId('cliente_id')->nullable()->constrained('clientes');
            $table->foreignId('cliente_colaboradora_id')->nullable()->constrained('colaboradoras');

            $table->enum('metodo_pago', ['efectivo', 'tarjeta'])->default('efectivo');

            $table->decimal('subtotal_servicios', 10, 2)->default(0);
            $table->decimal('subtotal_productos', 10, 2)->default(0);
            $table->decimal('comision_monto', 10, 2)->default(0);
            $table->decimal('total', 10, 2)->default(0);

            $table->text('notas')->nullable();

            $table->timestamps();

            $table->index(['fecha', 'metodo_pago']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ventas');
    }
};
