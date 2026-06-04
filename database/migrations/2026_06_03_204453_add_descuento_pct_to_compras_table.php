<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('compras', function (Blueprint $table) {
            $table->decimal('descuento_pct', 5, 2)->default(0)->after('precio_unitario');
        });
    }

    public function down(): void
    {
        Schema::table('compras', function (Blueprint $table) {
            $table->dropColumn('descuento_pct');
        });
    }
};