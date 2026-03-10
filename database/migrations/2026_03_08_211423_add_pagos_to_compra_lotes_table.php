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
        Schema::table('compra_lotes', function (Blueprint $table) {
            $table->decimal('monto_total', 12, 2)->default(0)->after('nota');
            $table->decimal('monto_pagado', 12, 2)->default(0)->after('monto_total');
            $table->string('estado_pago')->default('pendiente')->after('monto_pagado');
        });
    }

    public function down(): void
    {
        Schema::table('compra_lotes', function (Blueprint $table) {
            $table->dropColumn(['monto_total', 'monto_pagado', 'estado_pago']);
        });
    }
};
