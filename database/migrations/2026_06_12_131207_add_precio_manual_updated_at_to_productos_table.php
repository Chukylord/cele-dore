<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('productos', function (Blueprint $table) {
            $table->timestamp('precio_manual_updated_at')->nullable()->after('precio_tarjeta_manual');
        });

        DB::table('productos')
            ->whereNotNull('precio_efectivo_manual')
            ->update([
                'precio_manual_updated_at' => DB::raw('updated_at')
            ]);
    }

    public function down(): void
    {
        Schema::table('productos', function (Blueprint $table) {
            $table->dropColumn('precio_manual_updated_at');
        });
    }
};