<?php

namespace App\Http\Controllers;

use App\Models\Compra;
use App\Models\CompraLote;
use App\Models\CompraPago;
use App\Models\Producto;
use App\Models\Proveedor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CompraController extends Controller
{
    private function estadoPagoCompra(float $total, float $pagado): string
    {
        if ($pagado <= 0) {
            return 'pendiente';
        }

        if ($pagado < $total) {
            return 'parcial';
        }

        return 'pagado';
    }

    public function index(Request $request)
    {
        $proveedor_id = trim((string)$request->get('proveedor_id', ''));
        $producto_id  = trim((string)$request->get('producto_id', ''));
        $desde        = trim((string)$request->get('desde', ''));
        $hasta        = trim((string)$request->get('hasta', ''));

        $query = CompraLote::query()
            ->with(['compras.proveedor', 'compras.producto', 'pagos'])
            ->withCount('compras');

        if ($desde !== '') {
            $query->whereDate('fecha', '>=', $desde);
        }

        if ($hasta !== '') {
            $query->whereDate('fecha', '<=', $hasta);
        }

        if ($proveedor_id !== '' || $producto_id !== '') {
            $query->whereHas('compras', function ($q) use ($proveedor_id, $producto_id) {
                if ($proveedor_id !== '') {
                    $q->where('proveedor_id', (int)$proveedor_id);
                }

                if ($producto_id !== '') {
                    $q->where('producto_id', (int)$producto_id);
                }
            });
        }

        $lotes = $query
            ->orderBy('fecha', 'desc')
            ->paginate(10)
            ->withQueryString();

        $proveedores = Proveedor::orderBy('nombre')->get();

        $productos = Producto::with('proveedor')
            ->orderBy('marca')
            ->orderBy('tipo')
            ->orderBy('contenido')
            ->get();

        return view('compras.index', compact(
            'lotes',
            'proveedores',
            'productos',
            'proveedor_id',
            'producto_id',
            'desde',
            'hasta'
        ));
    }

    public function create()
    {
        $proveedores = Proveedor::orderBy('nombre')->get();

        $productos = Producto::with('proveedor')
            ->orderBy('marca')
            ->orderBy('tipo')
            ->orderBy('contenido')
            ->get();

        return view('compras.create', compact('proveedores', 'productos'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'fecha' => ['required', 'date'],
            'nota' => ['nullable', 'string', 'max:255'],
            'entrega_inicial' => ['nullable', 'numeric', 'min:0'],

            'items' => ['required', 'array', 'min:1'],
            'items.*.proveedor_id' => ['required', 'exists:proveedores,id'],
            'items.*.producto_id' => ['required', 'exists:productos,id'],
            'items.*.cantidad' => ['required', 'integer', 'min:1'],
            'items.*.precio_unitario' => ['required', 'numeric', 'min:0'],
        ]);

        DB::transaction(function () use ($data) {
            $montoTotal = 0;

            foreach ($data['items'] as $it) {
                $montoTotal += ((int)$it['cantidad'] * (float)$it['precio_unitario']);
            }

            $montoTotal = round($montoTotal, 2);
            $montoPagado = min((float)($data['entrega_inicial'] ?? 0), $montoTotal);
            $montoPagado = round($montoPagado, 2);

            $lote = CompraLote::create([
                'fecha' => $data['fecha'],
                'nota' => $data['nota'] ?? null,
                'monto_total' => $montoTotal,
                'monto_pagado' => $montoPagado,
                'estado_pago' => $this->estadoPagoCompra($montoTotal, $montoPagado),
            ]);

            foreach ($data['items'] as $it) {
                Compra::create([
                    'lote_id' => $lote->id,
                    'fecha' => $data['fecha'],
                    'proveedor_id' => (int)$it['proveedor_id'],
                    'producto_id' => (int)$it['producto_id'],
                    'cantidad' => (int)$it['cantidad'],
                    'precio_unitario' => (float)$it['precio_unitario'],
                ]);

                $producto = Producto::lockForUpdate()->find((int)$it['producto_id']);

                if ($producto) {
                    $producto->stock_venta = (int)$producto->stock_venta + (int)$it['cantidad'];
                    $producto->save();
                }
            }

            if ($montoPagado > 0) {
                CompraPago::create([
                    'compra_lote_id' => $lote->id,
                    'fecha' => $data['fecha'],
                    'monto' => $montoPagado,
                    'observacion' => 'Entrega inicial',
                ]);
            }
        });

        return redirect()->route('compras.index')->with('ok', 'Compra registrada correctamente.');
    }

    public function show(Compra $compra)
    {
        return redirect()->route('compras.index');
    }

    public function edit(Compra $compra)
    {
        return redirect()->route('compras.index');
    }

    public function update(Request $request, Compra $compra)
    {
        return redirect()->route('compras.index');
    }

    public function destroy(Compra $compra)
    {
        return redirect()->route('compras.index');
    }

    public function showLote(CompraLote $lote)
    {
        $lote->load(['compras.proveedor', 'compras.producto', 'pagos']);

        return view('compras.lotes.show', compact('lote'));
    }

    public function editLote(CompraLote $lote)
    {
        $lote->load(['compras.proveedor', 'compras.producto']);

        $proveedores = Proveedor::orderBy('nombre')->get();

        $productos = Producto::with('proveedor')
            ->orderBy('marca')
            ->orderBy('tipo')
            ->orderBy('contenido')
            ->get();

        return view('compras.lotes.edit', compact('lote', 'proveedores', 'productos'));
    }

    public function updateLote(Request $request, CompraLote $lote)
    {
        $data = $request->validate([
            'fecha' => ['required', 'date'],
            'nota' => ['nullable', 'string', 'max:255'],

            'items' => ['required', 'array', 'min:1'],
            'items.*.proveedor_id' => ['required', 'exists:proveedores,id'],
            'items.*.producto_id' => ['required', 'exists:productos,id'],
            'items.*.cantidad' => ['required', 'integer', 'min:1'],
            'items.*.precio_unitario' => ['required', 'numeric', 'min:0'],
        ]);

        try {
            DB::transaction(function () use ($data, $lote) {
                $comprasViejas = Compra::where('lote_id', $lote->id)->get();

                // Revertir stock del lote viejo
                foreach ($comprasViejas as $compraVieja) {
                    $producto = Producto::lockForUpdate()->find($compraVieja->producto_id);

                    if (!$producto) {
                        continue;
                    }

                    $nuevoStock = (int)$producto->stock_venta - (int)$compraVieja->cantidad;

                    if ($nuevoStock < 0) {
                        $nombre = trim(($producto->marca . ' - ' . $producto->tipo . ' ' . $producto->contenido));
                        throw new \Exception("No se puede editar el lote: el producto '{$nombre}' ya fue vendido/consumido y no alcanza el stock para revertir.");
                    }

                    $producto->stock_venta = $nuevoStock;
                    $producto->save();
                }

                Compra::where('lote_id', $lote->id)->delete();

                $montoTotal = 0;

                foreach ($data['items'] as $it) {
                    $montoTotal += ((int)$it['cantidad'] * (float)$it['precio_unitario']);
                }

                $montoTotal = round($montoTotal, 2);

                $montoPagado = (float)$lote->pagos()->sum('monto');
                if ($montoPagado > $montoTotal) {
                    $montoPagado = $montoTotal;
                }

                $montoPagado = round($montoPagado, 2);

                $lote->update([
                    'fecha' => $data['fecha'],
                    'nota' => $data['nota'] ?? null,
                    'monto_total' => $montoTotal,
                    'monto_pagado' => $montoPagado,
                    'estado_pago' => $this->estadoPagoCompra($montoTotal, $montoPagado),
                ]);

                foreach ($data['items'] as $it) {
                    Compra::create([
                        'lote_id' => $lote->id,
                        'fecha' => $data['fecha'],
                        'proveedor_id' => (int)$it['proveedor_id'],
                        'producto_id' => (int)$it['producto_id'],
                        'cantidad' => (int)$it['cantidad'],
                        'precio_unitario' => (float)$it['precio_unitario'],
                    ]);

                    $producto = Producto::lockForUpdate()->find((int)$it['producto_id']);

                    if ($producto) {
                        $producto->stock_venta = (int)$producto->stock_venta + (int)$it['cantidad'];
                        $producto->save();
                    }
                }
            });
        } catch (\Exception $e) {
            return back()->withInput()->with('ok', $e->getMessage());
        }

        return redirect()->route('compras.index')->with('ok', 'Lote actualizado correctamente.');
    }

    public function destroyLote(CompraLote $lote)
    {
        try {
            DB::transaction(function () use ($lote) {
                $compras = Compra::where('lote_id', $lote->id)->get();

                foreach ($compras as $compra) {
                    $producto = Producto::lockForUpdate()->find($compra->producto_id);

                    if (!$producto) {
                        continue;
                    }

                    $nuevoStock = (int)$producto->stock_venta - (int)$compra->cantidad;

                    if ($nuevoStock < 0) {
                        $nombre = trim(($producto->marca . ' - ' . $producto->tipo . ' ' . $producto->contenido));
                        throw new \Exception("No se puede eliminar el lote: el producto '{$nombre}' ya fue vendido/consumido y no alcanza el stock para revertir.");
                    }

                    $producto->stock_venta = $nuevoStock;
                    $producto->save();
                }

                Compra::where('lote_id', $lote->id)->delete();
                $lote->delete();
            });
        } catch (\Exception $e) {
            return redirect()->route('compras.index')->with('ok', $e->getMessage());
        }

        return redirect()->route('compras.index')->with('ok', 'Lote eliminado correctamente.');
    }

    public function storePago(Request $request, CompraLote $lote)
    {
        $data = $request->validate([
            'fecha' => ['required', 'date'],
            'monto' => ['required', 'numeric', 'min:0.01'],
            'observacion' => ['nullable', 'string', 'max:255'],
        ]);

        $saldo = (float)$lote->monto_total - (float)$lote->monto_pagado;
        $monto = min((float)$data['monto'], max($saldo, 0));

        if ($monto <= 0) {
            return redirect()->route('compras.lotes.show', $lote)->with('ok', 'Ese lote ya está pagado.');
        }

        DB::transaction(function () use ($data, $lote, $monto) {
            CompraPago::create([
                'compra_lote_id' => $lote->id,
                'fecha' => $data['fecha'],
                'monto' => round($monto, 2),
                'observacion' => $data['observacion'] ?? null,
            ]);

            $nuevoPagado = min(
                (float)$lote->monto_total,
                (float)$lote->monto_pagado + $monto
            );

            $nuevoPagado = round($nuevoPagado, 2);

            $lote->update([
                'monto_pagado' => $nuevoPagado,
                'estado_pago' => $this->estadoPagoCompra((float)$lote->monto_total, $nuevoPagado),
            ]);
        });

        return redirect()->route('compras.lotes.show', $lote)->with('ok', 'Pago registrado correctamente.');
    }
}