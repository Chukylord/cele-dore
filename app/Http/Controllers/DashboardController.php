<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Models\Turno;
use App\Models\Venta;
use App\Models\Fichada;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $hoy = now()->toDateString();

        // Ventas
        $ventasHoy = Venta::whereDate('fecha', $hoy)->count();
        $totalHoy = (float) Venta::whereDate('fecha', $hoy)
            ->where('pendiente_pago', false)
            ->sum('total');

        $pendienteCobrar = (float) Venta::where('pendiente_pago', true)->sum('total');

        // Turnos hoy
        $turnosHoy = Turno::whereDate('inicio', $hoy)->count();

        // Stock bajo
        $stockBajo = Producto::whereColumn('stock_venta', '<=', 'stock_minimo')->count();

        // Fichadas hoy (minutos)
        $minFichadosHoy = (int) Fichada::whereDate('fecha', $hoy)->sum('minutos_trabajados');
        $horasFichadasHoy = $minFichadosHoy / 60;

        return view('dashboard', compact(
            'ventasHoy',
            'totalHoy',
            'pendienteCobrar',
            'turnosHoy',
            'stockBajo',
            'horasFichadasHoy'
        ));
    }
}