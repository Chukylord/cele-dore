<?php

namespace App\Http\Controllers;

use App\Models\CajaDiaria;
use App\Models\Venta;
use App\Models\VentaPago;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class CajaDiariaController extends Controller
{
    private function round2(float $n): float
    {
        return round($n, 2);
    }

    /**
     * Obtiene todos los cobros registrados en una fecha.
     *
     * Incluye:
     * - Pagos nuevos guardados en venta_pagos.
     * - Ventas anteriores al nuevo módulo que todavía no tienen venta_pagos.
     */
    private function movimientosDelDia(string $fecha): Collection
    {
        $movimientos = collect();

        $pagos = VentaPago::query()
            ->with([
                'venta.cliente',
                'venta.clienteColaboradora',
                'venta.vendedora',
            ])
            ->whereDate('fecha_pago', $fecha)
            ->orderBy('fecha_pago')
            ->orderBy('id')
            ->get();

        foreach ($pagos as $pago) {
            $monto = (float) $pago->monto;
            $recargo = (float) $pago->recargo;
            $montoBase = (float) $pago->monto_base;

            if ($montoBase <= 0) {
                $montoBase = max($monto - $recargo, 0);
            }

            $movimientos->push([
                'id' => 'pago-' . $pago->id,
                'fecha_pago' => $pago->fecha_pago,
                'metodo_pago' => $pago->metodo_pago,
                'monto_base' => $this->round2($montoBase),
                'recargo' => $this->round2($recargo),
                'monto' => $this->round2($monto),
                'venta' => $pago->venta,
                'es_legacy' => false,
            ]);
        }

        /*
         * Compatibilidad con ventas antiguas:
         * toma solamente las ventas pagadas que no tienen registros
         * en la tabla venta_pagos, para evitar duplicarlas.
         */
        $ventasAnteriores = Venta::query()
            ->with([
                'cliente',
                'clienteColaboradora',
                'vendedora',
            ])
            ->whereDoesntHave('pagos')
            ->where('pendiente_pago', false)
            ->whereNotNull('fecha_pago')
            ->whereDate('fecha_pago', $fecha)
            ->orderBy('fecha_pago')
            ->orderBy('id')
            ->get();

        foreach ($ventasAnteriores as $venta) {
            $monto = (float) $venta->total;

            $montoBase = (float) $venta->total_base;

            if ($montoBase <= 0) {
                $montoBase =
                    (float) $venta->subtotal_productos
                    + (float) $venta->subtotal_servicios;
            }

            $recargo = max($monto - $montoBase, 0);

            $movimientos->push([
                'id' => 'venta-anterior-' . $venta->id,
                'fecha_pago' => $venta->fecha_pago,
                'metodo_pago' => $venta->metodo_pago,
                'monto_base' => $this->round2($montoBase),
                'recargo' => $this->round2($recargo),
                'monto' => $this->round2($monto),
                'venta' => $venta,
                'es_legacy' => true,
            ]);
        }

        return $movimientos
            ->sortBy(function (array $movimiento) {
                return $movimiento['fecha_pago']?->timestamp ?? 0;
            })
            ->values();
    }

    private function totalPorMetodo(Collection $movimientos, string $metodo): float
    {
        return $this->round2(
            (float) $movimientos
                ->where('metodo_pago', $metodo)
                ->sum('monto')
        );
    }

    private function totalesDelDia(string $fecha): array
    {
        $movimientos = $this->movimientosDelDia($fecha);

        $efectivo = $this->totalPorMetodo($movimientos, 'efectivo');
        $transferencia = $this->totalPorMetodo($movimientos, 'transferencia');
        $tarjeta = $this->totalPorMetodo($movimientos, 'tarjeta');

        return [
            'movimientos' => $movimientos,
            'efectivo' => $efectivo,
            'transferencia' => $transferencia,
            'tarjeta' => $tarjeta,
            'total' => $this->round2(
                $efectivo + $transferencia + $tarjeta
            ),
        ];
    }

    public function index(Request $request)
    {
        $request->validate([
            'fecha' => ['nullable', 'date'],
        ]);

        $fecha = (string) $request->get(
            'fecha',
            now()->toDateString()
        );

        $caja = CajaDiaria::query()
            ->whereDate('fecha', $fecha)
            ->first();

        $resumenActual = $this->totalesDelDia($fecha);

        $movimientos = $resumenActual['movimientos'];

        $cobrosEfectivoActual = $resumenActual['efectivo'];
        $cobrosTransferenciaActual = $resumenActual['transferencia'];
        $cobrosTarjetaActual = $resumenActual['tarjeta'];
        $totalCobradoActual = $resumenActual['total'];

        $cajaInicial = $caja
            ? (float) $caja->caja_inicial
            : 0;

        /*
         * Si la caja está cerrada, mostramos los importes guardados
         * al momento del cierre.
         *
         * Si está abierta, mostramos los cobros actualizados.
         */
        if ($caja && $caja->estaCerrada()) {
            $cobrosEfectivo = (float) $caja->ventas_efectivo;
            $cobrosTransferencia = (float) $caja->ventas_transferencia;
            $cobrosTarjeta = (float) $caja->ventas_tarjeta;
            $efectivoEsperado = (float) $caja->efectivo_esperado;

            $totalCobrado = $this->round2(
                $cobrosEfectivo
                + $cobrosTransferencia
                + $cobrosTarjeta
            );
        } else {
            $cobrosEfectivo = $cobrosEfectivoActual;
            $cobrosTransferencia = $cobrosTransferenciaActual;
            $cobrosTarjeta = $cobrosTarjetaActual;
            $totalCobrado = $totalCobradoActual;

            $efectivoEsperado = $this->round2(
                $cajaInicial + $cobrosEfectivoActual
            );
        }

        return view('caja-diaria.index', compact(
            'fecha',
            'caja',
            'movimientos',
            'cobrosEfectivo',
            'cobrosTransferencia',
            'cobrosTarjeta',
            'totalCobrado',
            'cobrosEfectivoActual',
            'cobrosTransferenciaActual',
            'cobrosTarjetaActual',
            'totalCobradoActual',
            'efectivoEsperado'
        ));
    }

    public function abrir(Request $request)
    {
        $data = $request->validate([
            'fecha' => ['required', 'date'],
            'caja_inicial' => ['required', 'numeric', 'min:0'],
        ], [
            'fecha.required' => 'La fecha es obligatoria.',
            'caja_inicial.required' => 'Ingresá con cuánto efectivo inicia la caja.',
            'caja_inicial.numeric' => 'La caja inicial debe ser un número.',
            'caja_inicial.min' => 'La caja inicial no puede ser negativa.',
        ]);

        $fecha = $data['fecha'];

        $cajaExistente = CajaDiaria::query()
            ->whereDate('fecha', $fecha)
            ->first();

        if ($cajaExistente) {
            return redirect()
                ->route('caja-diaria.index', ['fecha' => $fecha])
                ->with('ok', 'Ya existe una caja para esa fecha.');
        }

        $resumen = $this->totalesDelDia($fecha);

        $cajaInicial = $this->round2(
            (float) $data['caja_inicial']
        );

        $efectivoEsperado = $this->round2(
            $cajaInicial + $resumen['efectivo']
        );

        CajaDiaria::create([
            'fecha' => $fecha,
            'caja_inicial' => $cajaInicial,
            'ventas_efectivo' => $resumen['efectivo'],
            'ventas_transferencia' => $resumen['transferencia'],
            'ventas_tarjeta' => $resumen['tarjeta'],
            'efectivo_esperado' => $efectivoEsperado,
            'estado' => 'abierta',
            'fecha_apertura' => now(),
            'abierta_por' => auth()->id(),
        ]);

        return redirect()
            ->route('caja-diaria.index', ['fecha' => $fecha])
            ->with('ok', 'Caja diaria iniciada correctamente.');
    }

    public function cerrar(Request $request, CajaDiaria $caja)
    {
        /*
         * Seguridad extra.
         * Además de esta comprobación, la ruta debe conservar
         * el middleware admin.
         */
        abort_unless(
            auth()->user()?->esAdmin(),
            403
        );

        if ($caja->estaCerrada()) {
            return redirect()
                ->route('caja-diaria.index', [
                    'fecha' => $caja->fecha->format('Y-m-d'),
                ])
                ->with('ok', 'La caja ya estaba cerrada.');
        }

        $data = $request->validate([
            'efectivo_contado' => ['required', 'numeric', 'min:0'],
            'observaciones' => ['nullable', 'string'],
        ], [
            'efectivo_contado.required' => 'Ingresá cuánto efectivo hay realmente en caja.',
            'efectivo_contado.numeric' => 'El efectivo contado debe ser un número.',
            'efectivo_contado.min' => 'El efectivo contado no puede ser negativo.',
        ]);

        $fecha = $caja->fecha->format('Y-m-d');

        $resumen = $this->totalesDelDia($fecha);

        $cajaInicial = (float) $caja->caja_inicial;

        $efectivoEsperado = $this->round2(
            $cajaInicial + $resumen['efectivo']
        );

        $efectivoContado = $this->round2(
            (float) $data['efectivo_contado']
        );

        $diferencia = $this->round2(
            $efectivoContado - $efectivoEsperado
        );

        $caja->update([
            'ventas_efectivo' => $resumen['efectivo'],
            'ventas_transferencia' => $resumen['transferencia'],
            'ventas_tarjeta' => $resumen['tarjeta'],
            'efectivo_esperado' => $efectivoEsperado,
            'efectivo_contado' => $efectivoContado,
            'diferencia' => $diferencia,
            'estado' => 'cerrada',
            'fecha_cierre' => now(),
            'observaciones' => $data['observaciones'] ?? null,
            'cerrada_por' => auth()->id(),
        ]);

        return redirect()
            ->route('caja-diaria.index', ['fecha' => $fecha])
            ->with('ok', 'Caja diaria cerrada correctamente.');
    }
}