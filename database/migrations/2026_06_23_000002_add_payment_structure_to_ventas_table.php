<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ventas', function (Blueprint $table) {
            $table->decimal('total_base', 12, 2)->default(0)->after('subtotal_productos');
            $table->decimal('recargo_tarjeta', 12, 2)->default(0)->after('total_base');
        });

        DB::statement("ALTER TABLE ventas MODIFY metodo_pago VARCHAR(30) NULL");

        DB::table('ventas')
            ->orderBy('id')
            ->chunkById(100, function ($ventas) {
                foreach ($ventas as $venta) {
                    $totalBase = round((float) $venta->total, 2);

                    DB::table('ventas')
                        ->where('id', $venta->id)
                        ->update([
                            'total_base' => $totalBase,
                            'recargo_tarjeta' => 0,
                        ]);

                    $yaTienePago = DB::table('venta_pagos')
                        ->where('venta_id', $venta->id)
                        ->exists();

                    if ($yaTienePago || (bool) $venta->pendiente_pago || $totalBase <= 0) {
                        continue;
                    }

                    $metodo = in_array($venta->metodo_pago, ['efectivo', 'transferencia', 'tarjeta'], true)
                        ? $venta->metodo_pago
                        : 'efectivo';

                    DB::table('venta_pagos')->insert([
                        'venta_id' => $venta->id,
                        'metodo_pago' => $metodo,
                        'monto_base' => $totalBase,
                        'recargo' => 0,
                        'monto' => $totalBase,
                        'fecha_pago' => $venta->fecha_pago ?? $venta->fecha ?? $venta->created_at ?? now(),
                        'user_id' => null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            });
    }

    public function down(): void
    {
        DB::table('ventas')
            ->whereNull('metodo_pago')
            ->orWhere('metodo_pago', 'combinado')
            ->update(['metodo_pago' => 'efectivo']);

        DB::statement("
            ALTER TABLE ventas
            MODIFY metodo_pago ENUM('efectivo', 'transferencia', 'tarjeta')
            NOT NULL DEFAULT 'efectivo'
        ");

        Schema::table('ventas', function (Blueprint $table) {
            $table->dropColumn(['total_base', 'recargo_tarjeta']);
        });
    }
};
