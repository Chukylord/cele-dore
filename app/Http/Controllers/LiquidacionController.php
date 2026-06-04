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

    private function mesesDelSemestre(int $semestre): array
    {
        if ($semestre === 2) {
            return [7, 8, 9, 10, 11, 12];
        }

        return [1, 2, 3, 4, 5, 6];
    }

    private function nombreMes(int $mes): string
    {
        $meses = [
            1 => 'Enero',
            2 => 'Febrero',
            3 => 'Marzo',
            4 => 'Abril',
            5 => 'Mayo',
            6 => 'Junio',
            7 => 'Julio',
            8 => 'Agosto',
            9 => 'Septiembre',
            10 => 'Octubre',
            11 => 'Noviembre',
            12 => 'Diciembre',
        ];

        return $meses[$mes] ?? '-';
    }

    private function armarResumenAguinaldo(int $anio, int $semestre, string $colaboradoraId = ''): array
    {
        $meses = $this->mesesDelSemestre($semestre);
        $mesDesde = min($meses);
        $mesHasta = max($meses);

        $colaboradorasQuery = Colaboradora::query()
            ->orderBy('apellido')
            ->orderBy('nombre');

        if ($colaboradoraId !== '') {
            $colaboradorasQuery->where('id', (int)$colaboradoraId);
        }

        $colaboradoras = $colaboradorasQuery->get();

        /*
            Para aguinaldo NO se descuentan productos a costo.
            Se toma:
            horas normales + horas extras + comisión
        */
        $totalesPorMes = Liquidacion::query()
            ->selectRaw('
                colaboradora_id,
                MONTH(fecha_pago) as mes,
                COALESCE(SUM(monto_horas_normales + monto_horas_extras + monto_comision), 0) as total
            ')
            ->whereYear('fecha_pago', $anio)
            ->whereMonth('fecha_pago', '>=', $mesDesde)
            ->whereMonth('fecha_pago', '<=', $mesHasta)
            ->when($colaboradoraId !== '', function ($q) use ($colaboradoraId) {
                $q->where('colaboradora_id', (int)$colaboradoraId);
            })
            ->groupBy('colaboradora_id', DB::raw('MONTH(fecha_pago)'))
            ->get()
            ->groupBy('colaboradora_id');

        $resumen = [];

        foreach ($colaboradoras as $colaboradora) {
            $filasColab = $totalesPorMes->get($colaboradora->id, collect());

            $mesesDetalle = [];
            $mejorMesNumero = null;
            $mejorMesNombre = '-';
            $mejorMesTotal = 0;

            foreach ($meses as $mes) {
                $filaMes = $filasColab->firstWhere('mes', $mes);
                $totalMes = $filaMes ? (float)$filaMes->total : 0;

                $mesesDetalle[] = [
                    'mes' => $mes,
                    'nombre' => $this->nombreMes($mes),
                    'total' => $this->round2($totalMes),
                ];

                if ($totalMes > $mejorMesTotal) {
                    $mejorMesTotal = $totalMes;
                    $mejorMesNumero = $mes;
                    $mejorMesNombre = $this->nombreMes($mes);
                }
            }

            $resumen[] = [
                'colaboradora' => $colaboradora,
                'meses' => $mesesDetalle,
                'mejor_mes_numero' => $mejorMesNumero,
                'mejor_mes_nombre' => $mejorMesNombre,
                'mejor_mes_total' => $this->round2($mejorMesTotal),
                'aguinaldo_sugerido' => $this->round2($mejorMesTotal / 2),
            ];
        }

        return $resumen;
    }

    public function index(Request $request)
    {
        $desde = trim((string) $request->get('desde', ''));
        $hasta = trim((string) $request->get('hasta', ''));
        $colaboradora_id = trim((string) $request->get('colaboradora_id', ''));

        $sacAnio = (int) $request->get('sac_anio', now()->year);
        $sacSemestre = (int) $request->get('sac_semestre', now()->month <= 6 ? 1 : 2);
        $sacColaboradoraId = trim((string) $request->get('sac_colaboradora_id', ''));

        if (!in_array($sacSemestre, [1, 2], true)) {
            $sacSemestre = 1;
        }

        if ($sacAnio < 2000 || $sacAnio > 2100) {
            $sacAnio = now()->year;
        }

        $baseQuery = Liquidacion::query()
            ->with('colaboradora');

        if ($desde !== '') {
            $baseQuery->whereDate('fecha_pago', '>=', $desde);
        }

        if ($hasta !== '') {
            $baseQuery->whereDate('fecha_pago', '<=', $hasta);
        }

        if ($colaboradora_id !== '') {
            $baseQuery->where('colaboradora_id', (int) $colaboradora_id);
        }

        /*
            Total del filtro = total pagado real.
            En la liquidación real SÍ se restan productos a costo.
        */
        $totalFiltro = (clone $baseQuery)->sum('total_pagado');

        $liquidaciones = (clone $baseQuery)
            ->orderBy('fecha_pago', 'desc')
            ->paginate(10)
            ->withQueryString();

        $colaboradoras = Colaboradora::where('activa', true)
            ->orderBy('apellido')
            ->orderBy('nombre')
            ->get();

        $aguinaldoResumen = $this->armarResumenAguinaldo(
            $sacAnio,
            $sacSemestre,
            $sacColaboradoraId
        );

        return view('liquidaciones.index', compact(
            'liquidaciones',
            'colaboradoras',
            'desde',
            'hasta',
            'colaboradora_id',
            'totalFiltro',
            'sacAnio',
            'sacSemestre',
            'sacColaboradoraId',
            'aguinaldoResumen'
        ));
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
        $fichadas = Fichada::where('colaboradora_id', $colaboradoraId)
            ->whereNull('liquidacion_id')
            ->get();

        $minNormales = (int) $fichadas->sum('minutos_normales');
        $minExtras   = (int) $fichadas->sum('minutos_extras');

        $ventasComision = Venta::where('vendedora_id', $colaboradoraId)
            ->whereNull('cliente_colaboradora_id')
            ->whereNull('liquidacion_id')
            ->get();

        $montoComision = (float) $ventasComision->sum('comision_monto');

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

        /*
            Liquidación real:
            horas + comisión - productos a costo.
        */
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

            Fichada::where('colaboradora_id', $data['colaboradora_id'])
                ->whereNull('liquidacion_id')
                ->update(['liquidacion_id' => $liquidacion->id]);

            Venta::where('vendedora_id', $data['colaboradora_id'])
                ->whereNull('cliente_colaboradora_id')
                ->whereNull('liquidacion_id')
                ->update(['liquidacion_id' => $liquidacion->id]);

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