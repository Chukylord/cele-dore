<?php

namespace App\Http\Controllers;

use App\Models\ConsumoPeluqueria;
use App\Models\Gasto;
use App\Models\Liquidacion;
use App\Models\ProveedorPago;
use App\Models\Venta;
use App\Models\VentaPago;
use Illuminate\Http\Request;

class InformeController extends Controller
{
    private function round2(float $valor): float
    {
        return round($valor, 2);
    }

    public function index(Request $request)
    {
        $desde = trim((string) $request->get('desde', ''));
        $hasta = trim((string) $request->get('hasta', ''));

        /*
        |--------------------------------------------------------------------------
        | INGRESOS COBRADOS CON LA NUEVA TABLA venta_pagos
        |--------------------------------------------------------------------------
        |
        | Los ingresos se computan por la fecha real en que se realizó el pago.
        | Una venta puede tener efectivo, transferencia y tarjeta combinados.
        |
        */

        $pagosQuery = VentaPago::query()
            ->with([
                'venta:id,subtotal_productos,subtotal_servicios,total_base,total',
            ]);

        if ($desde !== '') {
            $pagosQuery->whereDate('fecha_pago', '>=', $desde);
        }

        if ($hasta !== '') {
            $pagosQuery->whereDate('fecha_pago', '<=', $hasta);
        }

        $pagos = $pagosQuery->get();

        /*
        |--------------------------------------------------------------------------
        | VENTAS ANTERIORES A venta_pagos
        |--------------------------------------------------------------------------
        |
        | Este bloque mantiene el historial viejo. Solamente toma ventas pagadas
        | que no tengan registros en venta_pagos, para evitar duplicarlas.
        |
        */

        $ventasAnterioresQuery = Venta::query()
            ->whereDoesntHave('pagos')
            ->where('pendiente_pago', false)
            ->whereNotNull('fecha_pago');

        if ($desde !== '') {
            $ventasAnterioresQuery->whereDate('fecha_pago', '>=', $desde);
        }

        if ($hasta !== '') {
            $ventasAnterioresQuery->whereDate('fecha_pago', '<=', $hasta);
        }

        $ventasAnteriores = $ventasAnterioresQuery->get();

        /*
        |--------------------------------------------------------------------------
        | TOTAL COBRADO POR MÉTODO
        |--------------------------------------------------------------------------
        */

        $ingresoEfectivo = $this->round2(
            (float) $pagos->where('metodo_pago', 'efectivo')->sum('monto')
            + (float) $ventasAnteriores->where('metodo_pago', 'efectivo')->sum('total')
        );

        $ingresoTransferencia = $this->round2(
            (float) $pagos->where('metodo_pago', 'transferencia')->sum('monto')
            + (float) $ventasAnteriores->where('metodo_pago', 'transferencia')->sum('total')
        );

        $ingresoTarjeta = $this->round2(
            (float) $pagos->where('metodo_pago', 'tarjeta')->sum('monto')
            + (float) $ventasAnteriores->where('metodo_pago', 'tarjeta')->sum('total')
        );

        $ingresoRecargoTarjeta = $this->round2(
            (float) $pagos->where('metodo_pago', 'tarjeta')->sum('recargo')
        );

        /*
        |--------------------------------------------------------------------------
        | DISTRIBUCIÓN ENTRE PRODUCTOS Y SERVICIOS
        |--------------------------------------------------------------------------
        |
        | Cada pago guarda cuánto corresponde al importe base. Ese importe se
        | distribuye proporcionalmente entre productos y servicios de la venta.
        | El recargo de tarjeta se muestra aparte.
        |
        */

        $ingresoProductos = 0.0;
        $ingresoServicios = 0.0;

        foreach ($pagos as $pago) {
            $venta = $pago->venta;

            if (!$venta) {
                continue;
            }

            $subtotalProductos = (float) $venta->subtotal_productos;
            $subtotalServicios = (float) $venta->subtotal_servicios;

            $baseVenta = (float) $venta->total_base;

            if ($baseVenta <= 0) {
                $baseVenta = $subtotalProductos + $subtotalServicios;
            }

            if ($baseVenta <= 0) {
                continue;
            }

            $basePago = (float) $pago->monto_base;

            if ($basePago <= 0) {
                $basePago = max(
                    (float) $pago->monto - (float) $pago->recargo,
                    0
                );
            }

            $parteProductos = $basePago * ($subtotalProductos / $baseVenta);
            $parteServicios = $basePago - $parteProductos;

            $ingresoProductos += $parteProductos;
            $ingresoServicios += $parteServicios;
        }

        /*
         * Se incorporan las ventas históricas que todavía no tenían venta_pagos.
         */
        $ingresoProductos += (float) $ventasAnteriores->sum('subtotal_productos');
        $ingresoServicios += (float) $ventasAnteriores->sum('subtotal_servicios');

        $ingresoProductos = $this->round2($ingresoProductos);
        $ingresoServicios = $this->round2($ingresoServicios);

        /*
         * El total real cobrado sale del monto de los pagos.
         * Incluye el recargo aplicado sobre la parte abonada con tarjeta.
         */
        $totalIngresos = $this->round2(
            (float) $pagos->sum('monto')
            + (float) $ventasAnteriores->sum('total')
        );

        /*
        |--------------------------------------------------------------------------
        | PENDIENTE DE COBRAR
        |--------------------------------------------------------------------------
        |
        | Se calcula sobre el importe base porque todavía no sabemos qué método
        | utilizará la clienta cuando pague. Por eso no se anticipa el recargo.
        |
        */

        $pendientesQuery = Venta::query()
            ->withSum('pagos as total_pagado_base', 'monto_base')
            ->where('pendiente_pago', true);

        if ($desde !== '') {
            $pendientesQuery->whereDate('fecha', '>=', $desde);
        }

        if ($hasta !== '') {
            $pendientesQuery->whereDate('fecha', '<=', $hasta);
        }

        $ventasPendientes = $pendientesQuery->get();

        $pendienteProductos = 0.0;
        $pendienteServicios = 0.0;

        foreach ($ventasPendientes as $venta) {
            $subtotalProductos = (float) $venta->subtotal_productos;
            $subtotalServicios = (float) $venta->subtotal_servicios;

            $baseVenta = (float) $venta->total_base;

            if ($baseVenta <= 0) {
                $baseVenta = $subtotalProductos + $subtotalServicios;
            }

            if ($baseVenta <= 0) {
                continue;
            }

            $pagadoBase = (float) ($venta->total_pagado_base ?? 0);
            $saldoBase = max($baseVenta - $pagadoBase, 0);

            $parteProductos = $saldoBase * ($subtotalProductos / $baseVenta);
            $parteServicios = $saldoBase - $parteProductos;

            $pendienteProductos += $parteProductos;
            $pendienteServicios += $parteServicios;
        }

        $pendienteProductos = $this->round2($pendienteProductos);
        $pendienteServicios = $this->round2($pendienteServicios);
        $pendienteTotal = $this->round2(
            $pendienteProductos + $pendienteServicios
        );

        /*
        |--------------------------------------------------------------------------
        | EGRESOS REALES
        |--------------------------------------------------------------------------
        */

        $proveedorPagosQuery = ProveedorPago::query();

        if ($desde !== '') {
            $proveedorPagosQuery->whereDate('fecha', '>=', $desde);
        }

        if ($hasta !== '') {
            $proveedorPagosQuery->whereDate('fecha', '<=', $hasta);
        }

        $egresoCompras = $this->round2(
            (float) $proveedorPagosQuery->sum('monto')
        );

        $liquidacionesQuery = Liquidacion::query();

        if ($desde !== '') {
            $liquidacionesQuery->whereDate('fecha_pago', '>=', $desde);
        }

        if ($hasta !== '') {
            $liquidacionesQuery->whereDate('fecha_pago', '<=', $hasta);
        }

        $egresoLiquidaciones = $this->round2(
            (float) $liquidacionesQuery->sum('total_pagado')
        );

        $gastosQuery = Gasto::query();

        if ($desde !== '') {
            $gastosQuery->whereDate('fecha', '>=', $desde);
        }

        if ($hasta !== '') {
            $gastosQuery->whereDate('fecha', '<=', $hasta);
        }

        $egresoGastos = $this->round2(
            (float) $gastosQuery->sum('monto')
        );

        $consumosQuery = ConsumoPeluqueria::query();

        if ($desde !== '') {
            $consumosQuery->whereDate('fecha', '>=', $desde);
        }

        if ($hasta !== '') {
            $consumosQuery->whereDate('fecha', '<=', $hasta);
        }

        $egresoConsumoPeluqueria = $this->round2(
            (float) $consumosQuery->sum('total')
        );

        $totalEgresos = $this->round2(
            $egresoCompras
            + $egresoLiquidaciones
            + $egresoGastos
            + $egresoConsumoPeluqueria
        );

        /*
        |--------------------------------------------------------------------------
        | BALANCE
        |--------------------------------------------------------------------------
        */

        $ganancia = $this->round2($totalIngresos - $totalEgresos);

        return view('informes.index', compact(
            'desde',
            'hasta',
            'ingresoProductos',
            'ingresoServicios',
            'ingresoRecargoTarjeta',
            'totalIngresos',
            'ingresoEfectivo',
            'ingresoTransferencia',
            'ingresoTarjeta',
            'egresoCompras',
            'egresoLiquidaciones',
            'egresoGastos',
            'egresoConsumoPeluqueria',
            'totalEgresos',
            'ganancia',
            'pendienteProductos',
            'pendienteServicios',
            'pendienteTotal'
        ));
    }
}