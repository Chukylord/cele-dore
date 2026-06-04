<?php

namespace App\Http\Controllers;

use App\Models\Gasto;
use App\Models\Liquidacion;
use App\Models\Venta;
use App\Models\ConsumoPeluqueria;
use App\Models\ProveedorPago;
use Illuminate\Http\Request;

class InformeController extends Controller
{
    public function index(Request $request)
    {
        $desde = trim((string) $request->get('desde', ''));
        $hasta = trim((string) $request->get('hasta', ''));

        // -------------------------
        // INGRESOS COBRADOS
        // -------------------------
        $ventasQuery = Venta::query()
            ->where('pendiente_pago', false)
            ->whereNotNull('fecha_pago');

        if ($desde !== '') {
            $ventasQuery->whereDate('fecha_pago', '>=', $desde);
        }

        if ($hasta !== '') {
            $ventasQuery->whereDate('fecha_pago', '<=', $hasta);
        }

        $ventas = (clone $ventasQuery)->get();

        $ingresoProductos = (float) $ventas->sum('subtotal_productos');
        $ingresoServicios = (float) $ventas->sum('subtotal_servicios');
        $totalIngresos = $ingresoProductos + $ingresoServicios;

        $ingresoEfectivo = (float) $ventas->where('metodo_pago', 'efectivo')->sum('total');
        $ingresoTransferencia = (float) $ventas->where('metodo_pago', 'transferencia')->sum('total');
        $ingresoTarjeta = (float) $ventas->where('metodo_pago', 'tarjeta')->sum('total');

        // -------------------------
        // PENDIENTE DE COBRAR
        // Ventas pendientes por fecha de venta
        // -------------------------
        $pendQuery = Venta::query()
            ->where('pendiente_pago', true);

        if ($desde !== '') {
            $pendQuery->whereDate('fecha', '>=', $desde);
        }

        if ($hasta !== '') {
            $pendQuery->whereDate('fecha', '<=', $hasta);
        }

        $ventasPendientes = (clone $pendQuery)->get();

        $pendienteProductos = (float) $ventasPendientes->sum('subtotal_productos');
        $pendienteServicios = (float) $ventasPendientes->sum('subtotal_servicios');
        $pendienteTotal = $pendienteProductos + $pendienteServicios;

        // -------------------------
        // EGRESOS REALES
        // -------------------------

        // Entregas / pagos reales a proveedores
        $proveedorPagosQuery = ProveedorPago::query();

        if ($desde !== '') {
            $proveedorPagosQuery->whereDate('fecha', '>=', $desde);
        }

        if ($hasta !== '') {
            $proveedorPagosQuery->whereDate('fecha', '<=', $hasta);
        }

        $egresoCompras = (float) $proveedorPagosQuery->sum('monto');

        // Liquidaciones / sueldos reales pagados
        $liqQuery = Liquidacion::query();

        if ($desde !== '') {
            $liqQuery->whereDate('fecha_pago', '>=', $desde);
        }

        if ($hasta !== '') {
            $liqQuery->whereDate('fecha_pago', '<=', $hasta);
        }

        $egresoLiquidaciones = (float) $liqQuery->sum('total_pagado');

        // Gastos manuales
        $gastosQuery = Gasto::query();

        if ($desde !== '') {
            $gastosQuery->whereDate('fecha', '>=', $desde);
        }

        if ($hasta !== '') {
            $gastosQuery->whereDate('fecha', '<=', $hasta);
        }

        $egresoGastos = (float) $gastosQuery->sum('monto');

        // Consumo interno de peluquería
        $consQuery = ConsumoPeluqueria::query();

        if ($desde !== '') {
            $consQuery->whereDate('fecha', '>=', $desde);
        }

        if ($hasta !== '') {
            $consQuery->whereDate('fecha', '<=', $hasta);
        }

        $egresoConsumoPeluqueria = (float) $consQuery->sum('total');

        /*
            OJO:
            Si querés un informe de "plata que salió de caja", no deberías sumar consumo peluquería,
            porque la compra ya se pagó al proveedor.

            Si querés un informe de "rentabilidad", sí tiene sentido mostrar el consumo peluquería como costo.
            Acá lo dejamos visible y también sumado porque ya lo venías mostrando como egreso.
        */
        $totalEgresos = $egresoCompras
            + $egresoLiquidaciones
            + $egresoGastos
            + $egresoConsumoPeluqueria;

        // -------------------------
        // BALANCE
        // -------------------------
        $ganancia = $totalIngresos - $totalEgresos;

        return view('informes.index', compact(
            'desde',
            'hasta',
            'ingresoProductos',
            'ingresoServicios',
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