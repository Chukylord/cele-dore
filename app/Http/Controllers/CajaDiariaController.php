<?php

namespace App\Http\Controllers;

use App\Models\CajaDiaria;
use App\Models\Venta;
use Illuminate\Http\Request;

class CajaDiariaController extends Controller
{
    private function round2(float $n): float
    {
        return round($n, 2);
    }

    private function totalVentasPorMetodo(string $fecha, string $metodo): float
    {
        return $this->round2(
            (float) Venta::query()
                ->whereDate('fecha', $fecha)
                ->where('metodo_pago', $metodo)
                ->where('pendiente_pago', false)
                ->sum('total')
        );
    }

    private function ventasEfectivoDetalle(string $fecha)
    {
        return Venta::query()
            ->with(['cliente', 'clienteColaboradora', 'vendedora'])
            ->whereDate('fecha', $fecha)
            ->where('metodo_pago', 'efectivo')
            ->where('pendiente_pago', false)
            ->orderBy('fecha')
            ->get();
    }

    public function index(Request $request)
    {
        $fecha = $request->get('fecha', now()->toDateString());

        $caja = CajaDiaria::query()
            ->whereDate('fecha', $fecha)
            ->first();

        $ventasEfectivoActual = $this->totalVentasPorMetodo($fecha, 'efectivo');
        $ventasTransferenciaActual = $this->totalVentasPorMetodo($fecha, 'transferencia');
        $ventasTarjetaActual = $this->totalVentasPorMetodo($fecha, 'tarjeta');

        $ventasEfectivoDetalle = $this->ventasEfectivoDetalle($fecha);

        $cajaInicial = $caja ? (float) $caja->caja_inicial : 0;

        if ($caja && $caja->estaCerrada()) {
            $ventasEfectivo = (float) $caja->ventas_efectivo;
            $ventasTransferencia = (float) $caja->ventas_transferencia;
            $ventasTarjeta = (float) $caja->ventas_tarjeta;
            $efectivoEsperado = (float) $caja->efectivo_esperado;
        } else {
            $ventasEfectivo = $ventasEfectivoActual;
            $ventasTransferencia = $ventasTransferenciaActual;
            $ventasTarjeta = $ventasTarjetaActual;
            $efectivoEsperado = $this->round2($cajaInicial + $ventasEfectivoActual);
        }

        return view('caja-diaria.index', compact(
            'fecha',
            'caja',
            'ventasEfectivo',
            'ventasTransferencia',
            'ventasTarjeta',
            'ventasEfectivoActual',
            'ventasTransferenciaActual',
            'ventasTarjetaActual',
            'ventasEfectivoDetalle',
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

        $ventasEfectivo = $this->totalVentasPorMetodo($fecha, 'efectivo');
        $ventasTransferencia = $this->totalVentasPorMetodo($fecha, 'transferencia');
        $ventasTarjeta = $this->totalVentasPorMetodo($fecha, 'tarjeta');

        $cajaInicial = $this->round2((float) $data['caja_inicial']);
        $efectivoEsperado = $this->round2($cajaInicial + $ventasEfectivo);

        CajaDiaria::create([
            'fecha' => $fecha,
            'caja_inicial' => $cajaInicial,
            'ventas_efectivo' => $ventasEfectivo,
            'ventas_transferencia' => $ventasTransferencia,
            'ventas_tarjeta' => $ventasTarjeta,
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
        if ($caja->estaCerrada()) {
            return redirect()
                ->route('caja-diaria.index', ['fecha' => $caja->fecha->format('Y-m-d')])
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

        $ventasEfectivo = $this->totalVentasPorMetodo($fecha, 'efectivo');
        $ventasTransferencia = $this->totalVentasPorMetodo($fecha, 'transferencia');
        $ventasTarjeta = $this->totalVentasPorMetodo($fecha, 'tarjeta');

        $cajaInicial = (float) $caja->caja_inicial;
        $efectivoEsperado = $this->round2($cajaInicial + $ventasEfectivo);
        $efectivoContado = $this->round2((float) $data['efectivo_contado']);
        $diferencia = $this->round2($efectivoContado - $efectivoEsperado);

        $caja->update([
            'ventas_efectivo' => $ventasEfectivo,
            'ventas_transferencia' => $ventasTransferencia,
            'ventas_tarjeta' => $ventasTarjeta,
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