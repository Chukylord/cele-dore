<?php

namespace App\Http\Controllers;

use App\Models\Venta;

class VentaSaldoController extends Controller
{
    public function __invoke(Venta $venta)
    {
        $venta->load('pagos');

        return response()->json([
            'venta_id' => $venta->id,
            'total_base' => $venta->totalBaseReal(),
            'pagado_base' => $venta->totalPagadoBase(),
            'total_cobrado' => $venta->totalCobrado(),
            'recargo_cobrado' => $venta->totalRecargoCobrado(),
            'saldo_base' => $venta->saldoPendienteBase(),
            'pendiente' => $venta->saldoPendienteBase() > 0.01,
        ]);
    }
}
