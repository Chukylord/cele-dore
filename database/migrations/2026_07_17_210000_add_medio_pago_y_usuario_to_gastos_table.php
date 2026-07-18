<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('gastos', function (Blueprint $table) {
            $table->string('medio_pago', 30)
                ->default('efectivo')
                ->after('monto');

            $table->boolean('impacta_caja')
                ->default(false)
                ->after('medio_pago');

            $table->foreignId('user_id')
                ->nullable()
                ->after('impacta_caja')
                ->constrained('users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('gastos', function (Blueprint $table) {
            $table->dropConstrainedForeignId('user_id');
            $table->dropColumn(['medio_pago', 'impacta_caja']);
        });
    }
};
