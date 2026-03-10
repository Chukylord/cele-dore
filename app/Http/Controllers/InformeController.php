<?php

namespace App\Http\Controllers;

use App\Models\Compra;
use App\Models\Gasto;
use App\Models\Liquidacion;
use App\Models\Venta;
use App\Models\ConsumoPeluqueria;
use App\Models\CompraPago;
use Illuminate\Http\Request;

class InformeController extends Controller
{
    public function index(Request $request)
    {
        $desde = trim((string) $request->get('desde', ''));
        $hasta = trim((string) $request->get('hasta', ''));
        $categoriaIngreso = trim((string) $request->get('categoria_ingreso', ''));

        // -------------------------
        // INGRESOS
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

        $ingresoProductos = 0;
        $ingresoServicios = 0;

        if ($categoriaIngreso === '' || $categoriaIngreso === 'productos') {
            $ingresoProductos = (float) $ventas->sum('subtotal_productos');
        }

        if ($categoriaIngreso === '' || $categoriaIngreso === 'servicios') {
            $ingresoServicios = (float) $ventas->sum('subtotal_servicios');
        }

        $totalIngresos = $ingresoProductos + $ingresoServicios;

        // -------------------------
        // PENDIENTE DE COBRAR
        // (ventas pendientes por fecha de venta)
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
        // EGRESOS
        // -------------------------

        // Compras
        $comprasPagosQuery = CompraPago::query();

        if ($desde !== '') {
            $comprasPagosQuery->whereDate('fecha', '>=', $desde);
        }
        if ($hasta !== '') {
            $comprasPagosQuery->whereDate('fecha', '<=', $hasta);
        }

        $egresoCompras = (float) $comprasPagosQuery->sum('monto');

        // Liquidaciones
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

        
        $consQuery = ConsumoPeluqueria::query();
        
        if ($desde !== '') {
            $consQuery->whereDate('fecha', '>=', $desde);
            }
            if ($hasta !== '') {
                $consQuery->whereDate('fecha', '<=', $hasta);
                }
                
                $egresoConsumoPeluqueria = (float) $consQuery->sum('total');
            
                
        $totalEgresos = $egresoCompras + $egresoLiquidaciones + $egresoGastos; + $egresoConsumoPeluqueria;

        // -------------------------
        // BALANCE
        // -------------------------
        $ganancia = $totalIngresos - $totalEgresos;

        return view('informes.index', compact(
            'desde',
            'hasta',
            'categoriaIngreso',
            'ingresoProductos',
            'ingresoServicios',
            'totalIngresos',
            'egresoCompras',
            'egresoLiquidaciones',
            'egresoGastos',
            'totalEgresos',
            'ganancia',
            'pendienteProductos',
            'pendienteServicios',
            'pendienteTotal',
            'egresoConsumoPeluqueria',
        ));
    }
}