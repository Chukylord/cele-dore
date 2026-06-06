<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE ventas MODIFY metodo_pago ENUM('efectivo', 'transferencia', 'tarjeta') NOT NULL DEFAULT 'efectivo'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE ventas MODIFY metodo_pago ENUM('efectivo', 'tarjeta') NOT NULL DEFAULT 'efectivo'");
    }
};