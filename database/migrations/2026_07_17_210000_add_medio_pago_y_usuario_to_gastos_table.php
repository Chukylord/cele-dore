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

            $table->foreignId('user_id')
                ->nullable()
                ->after('medio_pago')
                ->constrained('users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('gastos', function (Blueprint $table) {
            $table->dropConstrainedForeignId('user_id');
            $table->dropColumn('medio_pago');
        });
    }
};
