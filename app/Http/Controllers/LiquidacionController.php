<?php

namespace App\Http\Controllers;

use App\Models\Colaboradora;
use App\Models\Fichada;
use App\Models\Liquidacion;
use App\Models\Venta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LiquidacionController extends Controller
{
    private function round2($n): float
    {
        return round((float)$n, 2);
    }

    public function index()
    {
        $liquidaciones = Liquidacion::with('colaboradora')
            ->orderBy('fecha_pago', 'desc')
            ->paginate(10);

        return view('liquidaciones.index', compact('liquidaciones'));
    }

    public function create(Request $request)
    {
        $colaboradoras = Colaboradora::where('activa', true)
            ->orderBy('apellido')
            ->orderBy('nombre')
            ->get();

        $colaboradoraId = (int) $request->get('colaboradora_id', 0);

        $resumen = null;

        if ($colaboradoraId > 0) {
            $resumen = $this->armarResumen($colaboradoraId);
        }

        return view('liquidaciones.create', compact('colaboradoras', 'colaboradoraId', 'resumen'));
    }

    private function armarResumen(int $colaboradoraId): array
    {
        // Fichadas no liquidadas
        $fichadas = Fichada::where('colaboradora_id', $colaboradoraId)
            ->whereNull('liquidacion_id')
            ->get();

        $minNormales = (int) $fichadas->sum('minutos_normales');
        $minExtras   = (int) $fichadas->sum('minutos_extras');

        // Comisión no liquidada:
        // ventas hechas por esta colaboradora, a clientes normales, aún no liquidadas
        $ventasComision = Venta::where('vendedora_id', $colaboradoraId)
            ->whereNull('cliente_colaboradora_id')
            ->whereNull('liquidacion_id')
            ->get();

        $montoComision = (float) $ventasComision->sum('comision_monto');

        // Productos llevados por la colaboradora a costo:
        $ventasCosto = Venta::where('cliente_colaboradora_id', $colaboradoraId)
            ->whereNull('liquidacion_id')
            ->get();

        $montoProductosCosto = (float) $ventasCosto->sum('subtotal_productos');

        return [
            'minutos_normales' => $minNormales,
            'minutos_extras' => $minExtras,
            'horas_normales' => $minNormales / 60,
            'horas_extras' => $minExtras / 60,
            'monto_comision' => $montoComision,
            'monto_productos_costo' => $montoProductosCosto,
            'cant_fichadas' => $fichadas->count(),
            'cant_ventas_comision' => $ventasComision->count(),
            'cant_ventas_costo' => $ventasCosto->count(),
        ];
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'colaboradora_id' => ['required', 'exists:colaboradoras,id'],
            'fecha_pago' => ['required', 'date'],
            'valor_hora' => ['required', 'numeric', 'min:0'],
            'observaciones' => ['nullable', 'string'],
        ]);

        $resumen = $this->armarResumen((int)$data['colaboradora_id']);

        $valorHora = (float) $data['valor_hora'];

        $montoHorasNormales = $this->round2(($resumen['minutos_normales'] / 60) * $valorHora);
        $montoHorasExtras   = $this->round2(($resumen['minutos_extras'] / 60) * ($valorHora * 2));

        $montoComision = $this->round2($resumen['monto_comision']);
        $montoProductosCosto = $this->round2($resumen['monto_productos_costo']);

        $totalPagado = $this->round2(
            $montoHorasNormales + $montoHorasExtras + $montoComision - $montoProductosCosto
        );

        DB::transaction(function () use (
            $data,
            $resumen,
            $valorHora,
            $montoHorasNormales,
            $montoHorasExtras,
            $montoComision,
            $montoProductosCosto,
            $totalPagado
        ) {
            $liquidacion = Liquidacion::create([
                'colaboradora_id' => $data['colaboradora_id'],
                'fecha_pago' => $data['fecha_pago'],
                'valor_hora' => $valorHora,
                'minutos_normales' => $resumen['minutos_normales'],
                'minutos_extras' => $resumen['minutos_extras'],
                'monto_horas_normales' => $montoHorasNormales,
                'monto_horas_extras' => $montoHorasExtras,
                'monto_comision' => $montoComision,
                'monto_productos_costo' => $montoProductosCosto,
                'total_pagado' => $totalPagado,
                'observaciones' => $data['observaciones'] ?? null,
            ]);

            // marcar fichadas como liquidadas
            Fichada::where('colaboradora_id', $data['colaboradora_id'])
                ->whereNull('liquidacion_id')
                ->update(['liquidacion_id' => $liquidacion->id]);

            // marcar ventas de comisión como liquidadas
            Venta::where('vendedora_id', $data['colaboradora_id'])
                ->whereNull('cliente_colaboradora_id')
                ->whereNull('liquidacion_id')
                ->update(['liquidacion_id' => $liquidacion->id]);

            // marcar productos comprados a costo por la colaboradora
            Venta::where('cliente_colaboradora_id', $data['colaboradora_id'])
                ->whereNull('liquidacion_id')
                ->update(['liquidacion_id' => $liquidacion->id]);
        });

        return redirect()->route('liquidaciones.index')->with('ok', 'Liquidación registrada correctamente.');
    }

    public function show(Liquidacion $liquidacion)
    {
        $liquidacion->load('colaboradora');
        return view('liquidaciones.show', compact('liquidacion'));
    }
}