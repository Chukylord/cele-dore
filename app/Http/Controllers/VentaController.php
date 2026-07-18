<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Colaboradora;
use App\Models\Compra;
use App\Models\Producto;
use App\Models\Servicio;
use App\Models\Venta;
use App\Models\VentaPago;
use App\Models\VentaProducto;
use App\Models\VentaServicio;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class VentaController extends Controller
{
    public function index(Request $request)
    {
        $desde = trim((string) $request->get('desde', ''));
        $hasta = trim((string) $request->get('hasta', ''));
        $metodo = trim((string) $request->get('metodo_pago', ''));
        $estado = trim((string) $request->get('estado', ''));
        $colaboradora_id = trim((string) $request->get('colaboradora_id', ''));

        $query = Venta::query()
            ->with([
                'cliente',
                'clienteColaboradora',
                'vendedora',
                'pagos' => fn ($q) => $q->orderBy('fecha_pago')->orderBy('id'),
            ]);

        if ($desde !== '') {
            $query->whereDate('fecha', '>=', $desde);
        }

        if ($hasta !== '') {
            $query->whereDate('fecha', '<=', $hasta);
        }

        if ($metodo !== '') {
            $query->whereHas('pagos', function ($q) use ($metodo) {
                $q->where('metodo_pago', $metodo);
            });
        }

        if ($estado === 'pendiente') {
            $query->where('pendiente_pago', true);
        }

        if ($estado === 'pagado') {
            $query->where('pendiente_pago', false);
        }

        if ($colaboradora_id !== '') {
            $query->where('vendedora_id', (int) $colaboradora_id);
        }

        $ventasIds = (clone $query)->select('ventas.id');

        $totalEfectivo = (float) VentaPago::query()
            ->whereIn('venta_id', clone $ventasIds)
            ->where('metodo_pago', 'efectivo')
            ->sum('monto');

        $totalTransferencia = (float) VentaPago::query()
            ->whereIn('venta_id', clone $ventasIds)
            ->where('metodo_pago', 'transferencia')
            ->sum('monto');

        $totalTarjeta = (float) VentaPago::query()
            ->whereIn('venta_id', clone $ventasIds)
            ->where('metodo_pago', 'tarjeta')
            ->sum('monto');

        $totalGeneral = $this->round2($totalEfectivo + $totalTransferencia + $totalTarjeta);

        $ventas = $query
            ->orderBy('fecha', 'desc')
            ->paginate(10)
            ->withQueryString();

        $colaboradoras = Colaboradora::where('activa', true)
            ->orderBy('apellido')
            ->orderBy('nombre')
            ->get();

        return view('ventas.index', compact(
            'ventas',
            'desde',
            'hasta',
            'metodo',
            'estado',
            'colaboradora_id',
            'colaboradoras',
            'totalEfectivo',
            'totalTransferencia',
            'totalTarjeta',
            'totalGeneral'
        ));
    }

    public function create()
    {
        $clientes = Cliente::orderBy('apellido')->orderBy('nombre')->get();

        $colaboradoras = Colaboradora::where('activa', true)
            ->orderBy('apellido')
            ->orderBy('nombre')
            ->get();

        $servicios = Servicio::orderBy('nombre')->get();

        $productos = Producto::query()
            ->select('productos.*')
            ->selectSub(function ($q) {
                $q->from('compras')
                    ->select('precio_unitario')
                    ->whereColumn('compras.producto_id', 'productos.id')
                    ->orderBy('created_at', 'desc')
                    ->orderBy('id', 'desc')
                    ->limit(1);
            }, 'ultimo_costo')
            ->selectSub(function ($q) {
                $q->from('compras')
                    ->select('created_at')
                    ->whereColumn('compras.producto_id', 'productos.id')
                    ->orderBy('created_at', 'desc')
                    ->orderBy('id', 'desc')
                    ->limit(1);
            }, 'ultimo_costo_at')
            ->with('proveedor')
            ->orderBy('marca')
            ->orderBy('tipo')
            ->get();

        return view('ventas.create', compact('clientes', 'colaboradoras', 'servicios', 'productos'));
    }

    public function show(Venta $venta)
    {
        $venta->load([
            'cliente',
            'clienteColaboradora',
            'vendedora',
            'productos.producto',
            'servicios.servicio',
            'pagos.usuario',
        ]);

        return view('ventas.show', compact('venta'));
    }

    public function destroy(Venta $venta)
    {
        $venta->delete();

        return redirect()->route('ventas.index')->with('ok', 'Venta eliminada.');
    }

    private function round2($n): float
    {
        return round((float) $n, 2);
    }

    private function clampPct($pct): float
    {
        return min(100, max(0, (float) $pct));
    }

    private function applyDiscount(float $amount, float $pct): float
    {
        return $this->round2($amount * (1 - ($this->clampPct($pct) / 100)));
    }

    private function getUltimoCosto(int $productoId): ?float
    {
        $costo = Compra::where('producto_id', $productoId)
            ->orderBy('created_at', 'desc')
            ->orderBy('id', 'desc')
            ->value('precio_unitario');

        return $costo !== null ? (float) $costo : null;
    }

    private function manualEsMasNuevoQueCompra(Producto $producto): bool
    {
        if ($producto->precio_efectivo_manual === null || empty($producto->precio_manual_updated_at)) {
            return false;
        }

        $ultimaCompra = Compra::where('producto_id', $producto->id)
            ->orderBy('created_at', 'desc')
            ->orderBy('id', 'desc')
            ->first();

        if (!$ultimaCompra) {
            return true;
        }

        return \Illuminate\Support\Carbon::parse($producto->precio_manual_updated_at)
            ->greaterThanOrEqualTo($ultimaCompra->created_at);
    }

    private function precioUnitarioBaseNormal(Producto $producto, ?float $ultimoCosto): float
    {
        if ($this->manualEsMasNuevoQueCompra($producto)) {
            return $this->round2((float) $producto->precio_efectivo_manual);
        }

        if ($ultimoCosto !== null) {
            return $this->round2($ultimoCosto * 1.40);
        }

        if ($producto->precio_efectivo_manual !== null) {
            return $this->round2((float) $producto->precio_efectivo_manual);
        }

        return $this->round2((float) $producto->precio_venta);
    }

    private function precioUnitarioCostoColab(Producto $producto, ?float $ultimoCosto): float
    {
        if ($this->manualEsMasNuevoQueCompra($producto)) {
            return $this->round2((float) $producto->precio_efectivo_manual / 1.40);
        }

        if ($ultimoCosto !== null) {
            return $this->round2($ultimoCosto);
        }

        if ($producto->precio_efectivo_manual !== null) {
            return $this->round2((float) $producto->precio_efectivo_manual / 1.40);
        }

        return $this->round2((float) $producto->precio_venta / 1.40);
    }

    /**
     * Arma uno o varios pagos sobre un importe base disponible.
     *
     * - En pago completo, la base debe cubrir todo el importe disponible.
     * - En pago parcial, puede cubrir una parte, pero nunca exceder el saldo.
     * - El 20% se aplica solamente sobre la base abonada con tarjeta.
     */
    private function resolverPagos(
        float $limiteBase,
        string $tipoPago,
        array $pagosIngresados = [],
        ?float $montoSimple = null,
        bool $debeCompletar = false
    ): array {
        $limiteBase = $this->round2($limiteBase);

        if ($limiteBase <= 0) {
            throw ValidationException::withMessages([
                'tipo_pago' => 'No existe saldo pendiente para registrar.',
            ]);
        }

        $bases = [
            'efectivo' => 0.0,
            'transferencia' => 0.0,
            'tarjeta' => 0.0,
        ];

        if (in_array($tipoPago, ['efectivo', 'transferencia', 'tarjeta'], true)) {
            $baseSimple = $montoSimple !== null && $montoSimple > 0
                ? $this->round2($montoSimple)
                : $limiteBase;

            $bases[$tipoPago] = $baseSimple;
        } elseif ($tipoPago === 'combinado') {
            foreach (array_keys($bases) as $metodo) {
                $bases[$metodo] = $this->round2(
                    max(0, (float) ($pagosIngresados[$metodo] ?? 0))
                );
            }

            $cantidadMetodos = count(array_filter($bases, fn ($monto) => $monto > 0));

            if ($cantidadMetodos < 2) {
                throw ValidationException::withMessages([
                    'pagos' => 'Para usar pago combinado tenés que ingresar al menos dos formas de pago.',
                ]);
            }
        } else {
            throw ValidationException::withMessages([
                'tipo_pago' => 'Seleccioná una forma de pago válida.',
            ]);
        }

        $sumaBase = $this->round2(array_sum($bases));

        if ($sumaBase <= 0) {
            throw ValidationException::withMessages([
                'monto_pago' => 'Ingresá un importe mayor a $0.',
            ]);
        }

        if ($sumaBase - $limiteBase > 0.01) {
            throw ValidationException::withMessages([
                'monto_pago' => 'El pago no puede superar el saldo pendiente de la venta.',
            ]);
        }

        if ($debeCompletar && abs($sumaBase - $limiteBase) > 0.01) {
            throw ValidationException::withMessages([
                'pagos' => 'El pago completo debe cubrir todo el total base de la venta.',
            ]);
        }

        $detalle = [];
        $recargoTarjeta = 0.0;
        $totalFinal = 0.0;

        foreach ($bases as $metodo => $montoBase) {
            if ($montoBase <= 0) {
                continue;
            }

            $recargo = $metodo === 'tarjeta'
                ? $this->round2($montoBase * 0.20)
                : 0.0;

            $montoFinal = $this->round2($montoBase + $recargo);

            $detalle[] = [
                'metodo_pago' => $metodo,
                'monto_base' => $montoBase,
                'recargo' => $recargo,
                'monto' => $montoFinal,
            ];

            $recargoTarjeta += $recargo;
            $totalFinal += $montoFinal;
        }

        return [
            'metodo_resumen' => count($detalle) > 1 ? 'combinado' : $detalle[0]['metodo_pago'],
            'base_pagada' => $this->round2($sumaBase),
            'recargo_tarjeta' => $this->round2($recargoTarjeta),
            'total_final' => $this->round2($totalFinal),
            'detalle' => $detalle,
        ];
    }

    private function guardarPagos(Venta $venta, array $planPago, $fechaPago): void
    {
        foreach ($planPago['detalle'] as $pago) {
            VentaPago::create([
                'venta_id' => $venta->id,
                'metodo_pago' => $pago['metodo_pago'],
                'monto_base' => $pago['monto_base'],
                'recargo' => $pago['recargo'],
                'monto' => $pago['monto'],
                'fecha_pago' => $fechaPago,
                'user_id' => auth()->id(),
            ]);
        }
    }

    private function actualizarResumenPagos(Venta $venta): void
    {
        $venta->load('pagos');

        $totalBase = $venta->totalBaseReal();
        $pagadoBase = $venta->totalPagadoBase();
        $recargoTotal = $venta->totalRecargoCobrado();
        $saldo = $this->round2(max($totalBase - $pagadoBase, 0));
        $estaPagada = $saldo <= 0.01;

        $metodos = $venta->pagos
            ->pluck('metodo_pago')
            ->filter()
            ->unique()
            ->values();

        $metodoResumen = null;

        if ($metodos->count() === 1) {
            $metodoResumen = (string) $metodos->first();
        } elseif ($metodos->count() > 1) {
            $metodoResumen = 'combinado';
        }

        $ultimaFechaPago = $estaPagada
            ? $venta->pagos->max('fecha_pago')
            : null;

        $venta->update([
            'metodo_pago' => $metodoResumen,
            'total_base' => $totalBase,
            'recargo_tarjeta' => $recargoTotal,
            'total' => $this->round2($totalBase + $recargoTotal),
            'pendiente_pago' => !$estaPagada,
            'fecha_pago' => $ultimaFechaPago,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'fecha' => ['required', 'date'],
            'condicion_pago' => ['nullable', 'in:completo,parcial,pendiente'],
            'tipo_pago' => ['nullable', 'in:efectivo,transferencia,tarjeta,combinado'],
            'monto_pago' => ['nullable', 'numeric', 'min:0'],
            'pagos' => ['nullable', 'array'],
            'pagos.efectivo' => ['nullable', 'numeric', 'min:0'],
            'pagos.transferencia' => ['nullable', 'numeric', 'min:0'],
            'pagos.tarjeta' => ['nullable', 'numeric', 'min:0'],

            'vendedora_id' => ['nullable', 'exists:colaboradoras,id'],
            'tipo_cliente' => ['required', 'in:cliente,colaboradora'],
            'cliente_id' => ['nullable', 'exists:clientes,id'],
            'cliente_colaboradora_id' => ['nullable', 'exists:colaboradoras,id'],

            'pendiente_pago' => ['nullable', 'boolean'],
            'notas' => ['nullable', 'string'],

            'productos' => ['nullable', 'array'],
            'productos.*.producto_id' => ['nullable', 'exists:productos,id'],
            'productos.*.cantidad' => ['nullable', 'integer', 'min:1'],
            'productos.*.descuento_pct' => ['nullable', 'numeric', 'min:0', 'max:100'],

            'servicios' => ['nullable', 'array'],
            'servicios.*.servicio_id' => ['nullable', 'exists:servicios,id'],
            'servicios.*.precio' => ['nullable', 'numeric', 'min:0'],
            'servicios.*.detalle' => ['nullable', 'string'],
            'servicios.*.descuento_pct' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ]);

        $condicionPago = (string) ($data['condicion_pago'] ?? '');

        if ($condicionPago === '') {
            $condicionPago = $request->boolean('pendiente_pago')
                ? 'pendiente'
                : 'completo';
        }

        $aColaboradora = $data['tipo_cliente'] === 'colaboradora';

        if ($condicionPago !== 'pendiente' && empty($data['tipo_pago'])) {
            return back()
                ->withErrors(['tipo_pago' => 'Seleccioná la forma de pago.'])
                ->withInput();
        }

        if ($condicionPago === 'parcial'
            && ($data['tipo_pago'] ?? '') !== 'combinado'
            && (float) ($data['monto_pago'] ?? 0) <= 0) {
            return back()
                ->withErrors(['monto_pago' => 'Ingresá cuánto abona la clienta en este momento.'])
                ->withInput();
        }

        $clienteId = $aColaboradora ? null : ($data['cliente_id'] ?? null);
        $clienteColabId = $aColaboradora ? ($data['cliente_colaboradora_id'] ?? null) : null;

        if (!$aColaboradora && !$clienteId) {
            return back()
                ->withErrors(['cliente_id' => 'Seleccioná un cliente válido de la lista.'])
                ->withInput();
        }

        if ($aColaboradora && !$clienteColabId) {
            return back()
                ->withErrors(['cliente_colaboradora_id' => 'Seleccioná una colaboradora válida de la lista.'])
                ->withInput();
        }

        $productosIn = $request->input('productos', []);
        $serviciosIn = $request->input('servicios', []);

        $hayAlgo = collect($productosIn)->contains(fn ($r) => !empty($r['producto_id']))
            || collect($serviciosIn)->contains(fn ($r) => !empty($r['servicio_id']));

        if (!$hayAlgo) {
            return back()
                ->withErrors(['general' => 'Tenés que agregar al menos un producto o un servicio.'])
                ->withInput();
        }

        try {
            DB::transaction(function () use (
                $data,
                $clienteId,
                $clienteColabId,
                $productosIn,
                $serviciosIn,
                $aColaboradora,
                $condicionPago
            ) {
                $venta = Venta::create([
                    'fecha' => $data['fecha'],
                    'metodo_pago' => null,
                    'pendiente_pago' => true,
                    'fecha_pago' => null,
                    'vendedora_id' => $data['vendedora_id'] ?? null,
                    'cliente_id' => $clienteId,
                    'cliente_colaboradora_id' => $clienteColabId,
                    'notas' => $data['notas'] ?? null,
                    'subtotal_servicios' => 0,
                    'subtotal_productos' => 0,
                    'total_base' => 0,
                    'recargo_tarjeta' => 0,
                    'comision_monto' => 0,
                    'total' => 0,
                ]);

                $subtotalServicios = 0.0;
                $subtotalProductos = 0.0;

                foreach ($serviciosIn as $row) {
                    $sid = (int) ($row['servicio_id'] ?? 0);

                    if (!$sid) {
                        continue;
                    }

                    $servicio = Servicio::find($sid);

                    if (!$servicio) {
                        continue;
                    }

                    $precioBase = isset($row['precio']) && $row['precio'] !== ''
                        ? (float) $row['precio']
                        : (float) $servicio->precio;

                    $descPct = (float) ($row['descuento_pct'] ?? 0);
                    $precioFinalBase = $this->applyDiscount($precioBase, $descPct);

                    VentaServicio::create([
                        'venta_id' => $venta->id,
                        'servicio_id' => $sid,
                        'precio' => $precioFinalBase,
                        'detalle' => $row['detalle'] ?? null,
                    ]);

                    $subtotalServicios += $precioFinalBase;
                }

                foreach ($productosIn as $row) {
                    $pid = (int) ($row['producto_id'] ?? 0);
                    $cant = (int) ($row['cantidad'] ?? 0);

                    if (!$pid || $cant <= 0) {
                        continue;
                    }

                    $producto = Producto::lockForUpdate()->find($pid);

                    if (!$producto) {
                        continue;
                    }

                    $stockActual = (int) $producto->stock_venta;

                    if ($cant > $stockActual) {
                        throw new \Exception(
                            "Stock insuficiente para {$producto->marca} - {$producto->tipo} {$producto->contenido}. " .
                            "Stock: {$stockActual}, Cantidad: {$cant}"
                        );
                    }

                    $ultimoCosto = $this->getUltimoCosto($pid);
                    $costoRef = $ultimoCosto !== null ? $this->round2($ultimoCosto) : null;

                    $precioUnitBase = $aColaboradora
                        ? $this->precioUnitarioCostoColab($producto, $ultimoCosto)
                        : $this->precioUnitarioBaseNormal($producto, $ultimoCosto);

                    if ($aColaboradora && $precioUnitBase <= 0) {
                        throw new \Exception(
                            "El producto {$producto->marca} - {$producto->tipo} {$producto->contenido} " .
                            'no tiene un precio válido. Completalo primero en Lista de precios.'
                        );
                    }

                    $descPct = (float) ($row['descuento_pct'] ?? 0);
                    $precioUnitFinalBase = $this->applyDiscount($precioUnitBase, $descPct);
                    $subtotal = $this->round2($precioUnitFinalBase * $cant);

                    VentaProducto::create([
                        'venta_id' => $venta->id,
                        'producto_id' => $pid,
                        'cantidad' => $cant,
                        'costo_unitario_ref' => $costoRef,
                        'precio_unitario' => $precioUnitFinalBase,
                        'subtotal' => $subtotal,
                    ]);

                    $producto->stock_venta = $stockActual - $cant;
                    $producto->save();

                    $subtotalProductos += $subtotal;
                }

                $subtotalServicios = $this->round2($subtotalServicios);
                $subtotalProductos = $this->round2($subtotalProductos);
                $totalBase = $this->round2($subtotalServicios + $subtotalProductos);

                if ($totalBase <= 0) {
                    throw new \Exception('El total de la venta debe ser mayor a $0.');
                }

                $baseComisionProductos = 0.0;

                if (!$aColaboradora && $venta->vendedora_id) {
                    foreach ($productosIn as $row) {
                        $pid = (int) ($row['producto_id'] ?? 0);
                        $cant = (int) ($row['cantidad'] ?? 0);

                        if (!$pid || $cant <= 0) {
                            continue;
                        }

                        $producto = Producto::find($pid);

                        if (!$producto) {
                            continue;
                        }

                        $ultimoCosto = $this->getUltimoCosto($pid);
                        $precioUnitBase = $this->precioUnitarioBaseNormal($producto, $ultimoCosto);
                        $descPct = (float) ($row['descuento_pct'] ?? 0);
                        $precioUnitFinalBase = $this->applyDiscount($precioUnitBase, $descPct);

                        $baseComisionProductos += $this->round2($precioUnitFinalBase * $cant);
                    }
                }

                $baseComisionProductos = $this->round2($baseComisionProductos);
                $comisionMonto = 0.0;

                if (!$aColaboradora && $venta->vendedora_id) {
                    $vend = Colaboradora::find($venta->vendedora_id);
                    $pct = $vend ? (float) $vend->comision_pct : 0.0;
                    $comisionMonto = $this->round2($baseComisionProductos * ($pct / 100));
                }

                $venta->update([
                    'subtotal_servicios' => $subtotalServicios,
                    'subtotal_productos' => $subtotalProductos,
                    'total_base' => $totalBase,
                    'recargo_tarjeta' => 0,
                    'comision_monto' => $comisionMonto,
                    'total' => $totalBase,
                    'pendiente_pago' => true,
                    'fecha_pago' => null,
                ]);

                if ($condicionPago === 'pendiente') {
                    return;
                }

                $esPagoCompleto = $condicionPago === 'completo';
                $montoSimple = $esPagoCompleto
                    ? $totalBase
                    : (float) ($data['monto_pago'] ?? 0);

                $planPago = $this->resolverPagos(
                    $totalBase,
                    (string) $data['tipo_pago'],
                    $data['pagos'] ?? [],
                    $montoSimple,
                    $esPagoCompleto
                );

                $this->guardarPagos($venta, $planPago, now());
                $this->actualizarResumenPagos($venta);
            });
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            return back()
                ->withErrors(['general' => $e->getMessage()])
                ->withInput();
        }

        return redirect()->route('ventas.index')->with('ok', 'Venta registrada correctamente.');
    }

    public function marcarPagado(Request $request, Venta $venta)
    {
        $data = $request->validate([
            'tipo_pago' => ['required', 'in:efectivo,transferencia,tarjeta,combinado'],
            'monto_pago' => ['nullable', 'numeric', 'min:0'],
            'pagos' => ['nullable', 'array'],
            'pagos.efectivo' => ['nullable', 'numeric', 'min:0'],
            'pagos.transferencia' => ['nullable', 'numeric', 'min:0'],
            'pagos.tarjeta' => ['nullable', 'numeric', 'min:0'],
        ]);

        $mensaje = DB::transaction(function () use ($venta, $data) {
            $venta = Venta::query()
                ->lockForUpdate()
                ->findOrFail($venta->id);

            $venta->load('pagos');
            $saldoBase = $venta->saldoPendienteBase();

            if ($saldoBase <= 0.01) {
                $venta->update([
                    'pendiente_pago' => false,
                    'fecha_pago' => $venta->pagos->max('fecha_pago'),
                ]);

                return 'La venta ya estaba pagada.';
            }

            $montoSimple = isset($data['monto_pago']) && (float) $data['monto_pago'] > 0
                ? (float) $data['monto_pago']
                : $saldoBase;

            $planPago = $this->resolverPagos(
                $saldoBase,
                (string) $data['tipo_pago'],
                $data['pagos'] ?? [],
                $montoSimple,
                false
            );

            $this->guardarPagos($venta, $planPago, now());
            $this->actualizarResumenPagos($venta);

            $venta->refresh()->load('pagos');
            $saldoRestante = $venta->saldoPendienteBase();

            if ($saldoRestante <= 0.01) {
                return 'Pago registrado. La venta quedó cancelada completamente.';
            }

            return 'Pago registrado. Saldo pendiente: $' . number_format($saldoRestante, 2, ',', '.');
        });

        return redirect()->route('ventas.index')->with('ok', $mensaje);
    }
}
