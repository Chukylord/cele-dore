<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('productos', function (Blueprint $table) {
            $table->decimal('precio_efectivo_manual', 12, 2)->nullable()->after('precio_venta');
            $table->decimal('precio_tarjeta_manual', 12, 2)->nullable()->after('precio_efectivo_manual');
        });
    }

    public function down(): void
    {
        Schema::table('productos', function (Blueprint $table) {
            $table->dropColumn(['precio_efectivo_manual', 'precio_tarjeta_manual']);
        });
    }
};