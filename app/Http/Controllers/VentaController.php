<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Colaboradora;
use App\Models\Compra;
use App\Models\Producto;
use App\Models\Servicio;
use App\Models\Venta;
use App\Models\VentaProducto;
use App\Models\VentaServicio;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
            ->with(['cliente', 'clienteColaboradora', 'vendedora']);

        if ($desde !== '') {
            $query->whereDate('fecha', '>=', $desde);
        }

        if ($hasta !== '') {
            $query->whereDate('fecha', '<=', $hasta);
        }

        if ($metodo !== '') {
            $query->where('metodo_pago', $metodo);
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

        $queryTotales = clone $query;

        $totalEfectivo = (clone $queryTotales)
            ->where('metodo_pago', 'efectivo')
            ->sum('total');

        $totalTransferencia = (clone $queryTotales)
            ->where('metodo_pago', 'transferencia')
            ->sum('total');

        $totalTarjeta = (clone $queryTotales)
            ->where('metodo_pago', 'tarjeta')
            ->sum('total');

        $totalGeneral = (clone $queryTotales)->sum('total');

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
                    ->orderBy('fecha', 'desc')
                    ->orderBy('id', 'desc')
                    ->limit(1);
            }, 'ultimo_costo')
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
            'servicios.servicio'
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
        return round((float)$n, 2);
    }

    private function clampPct($pct): float
    {
        $pct = (float)$pct;

        if ($pct < 0) {
            $pct = 0;
        }

        if ($pct > 100) {
            $pct = 100;
        }

        return $pct;
    }

    private function applyDiscount(float $amount, float $pct): float
    {
        $pct = $this->clampPct($pct);

        return $this->round2($amount * (1 - ($pct / 100)));
    }

    private function getUltimoCosto(int $productoId): ?float
    {
        $costo = Compra::where('producto_id', $productoId)
            ->orderBy('fecha', 'desc')
            ->orderBy('id', 'desc')
            ->value('precio_unitario');

        return $costo !== null ? (float)$costo : null;
    }

    private function precioUnitarioVentaNormal(Producto $producto, ?float $ultimoCosto, string $metodoPago): float
    {
        /*
            Prioridad:
            1) Precio manual de Lista de precios.
            2) Último costo + 40% efectivo/transferencia o +60% tarjeta.
            3) precio_venta como respaldo si nunca tuvo compras.
        */

        if ($metodoPago === 'tarjeta' && $producto->precio_tarjeta_manual !== null) {
            return $this->round2((float)$producto->precio_tarjeta_manual);
        }

        if ($metodoPago !== 'tarjeta' && $producto->precio_efectivo_manual !== null) {
            return $this->round2((float)$producto->precio_efectivo_manual);
        }

        if ($ultimoCosto !== null) {
            $costo = (float)$ultimoCosto;
            $precio = $metodoPago === 'tarjeta' ? $costo * 1.60 : $costo * 1.40;

            return $this->round2($precio);
        }

        if ($metodoPago === 'tarjeta') {
            return $this->round2(((float)$producto->precio_venta / 1.40) * 1.60);
        }

        return $this->round2((float)$producto->precio_venta);
    }

    private function precioUnitarioCostoColab(float $precioManual, ?float $ultimoCosto): float
    {
        if ($ultimoCosto !== null) {
            return $this->round2((float)$ultimoCosto);
        }

        return $this->round2($precioManual / 1.40);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'fecha' => ['required', 'date'],
            'metodo_pago' => ['required', 'in:efectivo,transferencia,tarjeta'],
            'vendedora_id' => ['nullable', 'exists:colaboradoras,id'],

            'tipo_cliente' => ['required', 'in:cliente,colaboradora'],
            'cliente_id' => ['nullable', 'exists:clientes,id'],
            'cliente_colaboradora_id' => ['nullable', 'exists:colaboradoras,id'],

            'pendiente_pago' => ['nullable'],
            'notas' => ['nullable', 'string'],

            'productos' => ['array'],
            'productos.*.producto_id' => ['nullable', 'exists:productos,id'],
            'productos.*.cantidad' => ['nullable', 'integer', 'min:1'],
            'productos.*.descuento_pct' => ['nullable', 'numeric', 'min:0', 'max:100'],

            'servicios' => ['array'],
            'servicios.*.servicio_id' => ['nullable', 'exists:servicios,id'],
            'servicios.*.precio' => ['nullable', 'numeric', 'min:0'],
            'servicios.*.detalle' => ['nullable', 'string'],
            'servicios.*.descuento_pct' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ]);

        $aColaboradora = $data['tipo_cliente'] === 'colaboradora';

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

        $hayAlgo = false;

        foreach ($productosIn as $r) {
            if (!empty($r['producto_id'])) {
                $hayAlgo = true;
                break;
            }
        }

        if (!$hayAlgo) {
            foreach ($serviciosIn as $r) {
                if (!empty($r['servicio_id'])) {
                    $hayAlgo = true;
                    break;
                }
            }
        }

        if (!$hayAlgo) {
            return back()
                ->withErrors(['general' => 'Tenés que agregar al menos un producto o un servicio.'])
                ->withInput();
        }

        try {
            DB::transaction(function () use ($data, $clienteId, $clienteColabId, $productosIn, $serviciosIn, $aColaboradora) {
                $venta = Venta::create([
                    'fecha' => $data['fecha'],
                    'metodo_pago' => $data['metodo_pago'],
                    'pendiente_pago' => isset($data['pendiente_pago']),
                    'fecha_pago' => isset($data['pendiente_pago']) ? null : now(),
                    'vendedora_id' => $data['vendedora_id'] ?? null,
                    'cliente_id' => $clienteId,
                    'cliente_colaboradora_id' => $clienteColabId,
                    'notas' => $data['notas'] ?? null,
                ]);

                $subtotalServicios = 0.0;
                $subtotalProductos = 0.0;

                foreach ($serviciosIn as $row) {
                    $sid = (int)($row['servicio_id'] ?? 0);

                    if (!$sid) {
                        continue;
                    }

                    $servicio = Servicio::find($sid);

                    if (!$servicio) {
                        continue;
                    }

                    $precioBase = $row['precio'] !== null && $row['precio'] !== ''
                        ? (float)$row['precio']
                        : (float)$servicio->precio;

                    $descPct = isset($row['descuento_pct']) ? (float)$row['descuento_pct'] : 0;
                    $precioFinal = $this->applyDiscount($precioBase, $descPct);

                    VentaServicio::create([
                        'venta_id' => $venta->id,
                        'servicio_id' => $sid,
                        'precio' => $precioFinal,
                        'detalle' => $row['detalle'] ?? null,
                    ]);

                    $subtotalServicios += $precioFinal;
                }

                foreach ($productosIn as $row) {
                    $pid = (int)($row['producto_id'] ?? 0);
                    $cant = (int)($row['cantidad'] ?? 0);

                    if (!$pid || $cant <= 0) {
                        continue;
                    }

                    $producto = Producto::lockForUpdate()->find($pid);

                    if (!$producto) {
                        continue;
                    }

                    $stockActual = (int)$producto->stock_venta;

                    if ($cant > $stockActual) {
                        throw new \Exception("Stock insuficiente para {$producto->marca} - {$producto->tipo} {$producto->contenido}. Stock: {$stockActual}, Cantidad: {$cant}");
                    }

                    $ultimoCosto = $this->getUltimoCosto($pid);
                    $costoRef = $ultimoCosto !== null ? $this->round2($ultimoCosto) : null;

                    if ($aColaboradora) {
                        $precioUnit = $this->precioUnitarioCostoColab((float)$producto->precio_venta, $ultimoCosto);
                    } else {
                        $precioUnit = $this->precioUnitarioVentaNormal($producto, $ultimoCosto, $data['metodo_pago']);
                    }

                    $descPct = isset($row['descuento_pct']) ? (float)$row['descuento_pct'] : 0;
                    $precioUnitFinal = $this->applyDiscount($precioUnit, $descPct);
                    $subtotal = $this->round2($precioUnitFinal * $cant);

                    VentaProducto::create([
                        'venta_id' => $venta->id,
                        'producto_id' => $pid,
                        'cantidad' => $cant,
                        'costo_unitario_ref' => $costoRef,
                        'precio_unitario' => $precioUnitFinal,
                        'subtotal' => $subtotal,
                    ]);

                    $producto->stock_venta = $stockActual - $cant;
                    $producto->save();

                    $subtotalProductos += $subtotal;
                }

                $subtotalServicios = $this->round2($subtotalServicios);
                $subtotalProductos = $this->round2($subtotalProductos);

                $baseComisionProductos = 0.0;

                if (!$aColaboradora && $venta->vendedora_id) {
                    foreach ($productosIn as $row) {
                        $pid = (int)($row['producto_id'] ?? 0);
                        $cant = (int)($row['cantidad'] ?? 0);

                        if (!$pid || $cant <= 0) {
                            continue;
                        }

                        $producto = Producto::find($pid);

                        if (!$producto) {
                            continue;
                        }

                        $ultimoCosto = $this->getUltimoCosto($pid);

                        $precioUnitEfectivo = $this->precioUnitarioVentaNormal(
                            $producto,
                            $ultimoCosto,
                            'efectivo'
                        );

                        $descPct = isset($row['descuento_pct']) ? (float)$row['descuento_pct'] : 0;
                        $precioUnitEfectivoFinal = $this->applyDiscount($precioUnitEfectivo, $descPct);

                        $baseComisionProductos += $this->round2($precioUnitEfectivoFinal * $cant);
                    }
                }

                $baseComisionProductos = $this->round2($baseComisionProductos);

                $comisionMonto = 0.0;

                if (!$aColaboradora && $venta->vendedora_id) {
                    $vend = Colaboradora::find($venta->vendedora_id);
                    $pct = $vend ? (float)$vend->comision_pct : 0.0;
                    $comisionMonto = $this->round2($baseComisionProductos * ($pct / 100));
                }

                $total = $this->round2($subtotalServicios + $subtotalProductos);

                $venta->update([
                    'subtotal_servicios' => $subtotalServicios,
                    'subtotal_productos' => $subtotalProductos,
                    'comision_monto' => $comisionMonto,
                    'total' => $total,
                ]);
            });
        } catch (\Exception $e) {
            return back()
                ->withErrors(['general' => $e->getMessage()])
                ->withInput();
        }

        return redirect()->route('ventas.index')->with('ok', 'Venta registrada correctamente.');
    }

    public function marcarPagado(Venta $venta)
    {
        $venta->update([
            'pendiente_pago' => false,
            'fecha_pago' => now(),
        ]);

        return redirect()->route('ventas.index')->with('ok', 'Venta marcada como pagada.');
    }
}