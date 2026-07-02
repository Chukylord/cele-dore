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

    private function subtotalConDescuento(array $item): float
    {
        $cantidad = (int) $item['cantidad'];
        $precioUnitario = (float) $item['precio_unitario']; // precio sin descuento
        $descuentoPct = isset($item['descuento_pct']) ? (float) $item['descuento_pct'] : 0;

        if ($descuentoPct < 0) {
            $descuentoPct = 0;
        }

        if ($descuentoPct > 100) {
            $descuentoPct = 100;
        }

        $precioConDescuento = $precioUnitario * (1 - ($descuentoPct / 100));

        return $cantidad * $precioConDescuento;
    }


    private function normalizarItemsCompra(Request $request): void
    {
        $items = collect($request->input('items', []))
            ->filter(function ($item) {
                if (!is_array($item)) {
                    return false;
                }

                $productoId = trim((string) ($item['producto_id'] ?? ''));
                $productoTexto = trim((string) ($item['producto_texto'] ?? ''));
                $precioTexto = trim((string) ($item['precio_unitario'] ?? ''));
                $descuento = (float) ($item['descuento_pct'] ?? 0);

                /*
                 * Una línea nueva puede tener cantidad 1 y un proveedor copiado
                 * de la fila anterior. Se considera vacía mientras no tenga
                 * producto, precio ni descuento cargados.
                 */
                $precioVacio = $precioTexto === '' || abs((float) $precioTexto) < 0.00001;
                $descuentoVacio = abs($descuento) < 0.00001;

                return !(
                    $productoId === ''
                    && $productoTexto === ''
                    && $precioVacio
                    && $descuentoVacio
                );
            })
            ->values()
            ->all();

        $request->merge(['items' => $items]);
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
            ->orderByDesc('fecha')
            ->orderByDesc('id')
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
        $this->normalizarItemsCompra($request);

        $data = $request->validate([
            'fecha' => ['required', 'date'],
            'nota' => ['nullable', 'string', 'max:255'],

            'items' => ['required', 'array', 'min:1'],
            'items.*.proveedor_id' => ['required', 'exists:proveedores,id'],
            'items.*.producto_id' => ['required', 'exists:productos,id'],
            'items.*.cantidad' => ['required', 'integer', 'min:1'],
            'items.*.precio_unitario' => ['required', 'numeric', 'min:0'],
            'items.*.descuento_pct' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'items.*.proveedor_texto' => ['nullable', 'string', 'max:255'],
            'items.*.producto_texto' => ['nullable', 'string', 'max:255'],
        ], [
            'items.required' => 'Agregá al menos un producto a la compra.',
            'items.min' => 'Agregá al menos un producto a la compra.',
            'items.*.proveedor_id.required' => 'Hay una línea sin un proveedor válido.',
            'items.*.proveedor_id.exists' => 'Hay una línea con un proveedor que no existe.',
            'items.*.producto_id.required' => 'Hay una línea sin un producto válido.',
            'items.*.producto_id.exists' => 'Hay una línea con un producto que no existe.',
            'items.*.cantidad.required' => 'Hay una línea sin cantidad.',
            'items.*.cantidad.integer' => 'La cantidad debe ser un número entero.',
            'items.*.cantidad.min' => 'La cantidad debe ser de al menos 1.',
            'items.*.precio_unitario.required' => 'Hay una línea sin precio unitario.',
            'items.*.precio_unitario.numeric' => 'El precio unitario debe ser numérico.',
            'items.*.precio_unitario.min' => 'El precio unitario no puede ser negativo.',
            'items.*.descuento_pct.numeric' => 'El descuento debe ser numérico.',
            'items.*.descuento_pct.min' => 'El descuento no puede ser negativo.',
            'items.*.descuento_pct.max' => 'El descuento no puede ser mayor al 100%.',
        ]);

        DB::transaction(function () use ($data) {
            $montoTotal = 0;

            foreach ($data['items'] as $item) {
                $montoTotal += $this->subtotalConDescuento($item);
            }

            $montoTotal = round($montoTotal, 2);

            $lote = CompraLote::create([
                'fecha' => $data['fecha'],
                'nota' => $data['nota'] ?? null,
                'monto_total' => $montoTotal,

                /*
                * Los pagos al proveedor se administran desde
                * la cuenta corriente del proveedor.
                */
                'monto_pagado' => 0,
                'estado_pago' => 'pendiente',
            ]);

            foreach ($data['items'] as $item) {
                Compra::create([
                    'lote_id' => $lote->id,
                    'fecha' => $data['fecha'],
                    'proveedor_id' => (int) $item['proveedor_id'],
                    'producto_id' => (int) $item['producto_id'],
                    'cantidad' => (int) $item['cantidad'],
                    'precio_unitario' => (float) $item['precio_unitario'],
                    'descuento_pct' => isset($item['descuento_pct'])
                        ? (float) $item['descuento_pct']
                        : 0,
                ]);

                $producto = Producto::lockForUpdate()
                    ->find((int) $item['producto_id']);

                if ($producto) {
                    $producto->stock_venta =
                        (int) $producto->stock_venta +
                        (int) $item['cantidad'];

                    $producto->save();
                }
            }
        });

        return redirect()
            ->route('compras.index')
            ->with('ok', 'Compra registrada correctamente.');
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
        $lote->load(['compras.proveedor', 'compras.producto']);

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

        return view('compras.lotes.edit', compact(
            'lote',
            'proveedores',
            'productos'
        ));
    }

    public function updateLote(Request $request, CompraLote $lote)
    {
        $this->normalizarItemsCompra($request);

        $data = $request->validate([
            'fecha' => ['required', 'date'],
            'nota' => ['nullable', 'string', 'max:255'],

            'items' => ['required', 'array', 'min:1'],
            'items.*.proveedor_id' => ['required', 'exists:proveedores,id'],
            'items.*.producto_id' => ['required', 'exists:productos,id'],
            'items.*.cantidad' => ['required', 'integer', 'min:1'],
            'items.*.precio_unitario' => ['required', 'numeric', 'min:0'],
            'items.*.descuento_pct' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'items.*.proveedor_texto' => ['nullable', 'string', 'max:255'],
            'items.*.producto_texto' => ['nullable', 'string', 'max:255'],
        ], [
            'items.required' => 'Agregá al menos un producto al lote.',
            'items.min' => 'Agregá al menos un producto al lote.',
            'items.*.proveedor_id.required' => 'Hay una línea sin un proveedor válido.',
            'items.*.proveedor_id.exists' => 'Hay una línea con un proveedor que no existe.',
            'items.*.producto_id.required' => 'Hay una línea sin un producto válido.',
            'items.*.producto_id.exists' => 'Hay una línea con un producto que no existe.',
            'items.*.cantidad.required' => 'Hay una línea sin cantidad.',
            'items.*.cantidad.integer' => 'La cantidad debe ser un número entero.',
            'items.*.cantidad.min' => 'La cantidad debe ser de al menos 1.',
            'items.*.precio_unitario.required' => 'Hay una línea sin precio unitario.',
            'items.*.precio_unitario.numeric' => 'El precio unitario debe ser numérico.',
            'items.*.precio_unitario.min' => 'El precio unitario no puede ser negativo.',
            'items.*.descuento_pct.numeric' => 'El descuento debe ser numérico.',
            'items.*.descuento_pct.min' => 'El descuento no puede ser negativo.',
            'items.*.descuento_pct.max' => 'El descuento no puede ser mayor al 100%.',
        ]);

        try {
            DB::transaction(function () use ($data, $lote) {
                $comprasViejas = Compra::where('lote_id', $lote->id)->get();

                foreach ($comprasViejas as $compraVieja) {
                    $producto = Producto::lockForUpdate()
                        ->find($compraVieja->producto_id);

                    if (!$producto) {
                        continue;
                    }

                    $nuevoStock = (int) $producto->stock_venta
                        - (int) $compraVieja->cantidad;

                    if ($nuevoStock < 0) {
                        $nombre = trim(
                            ($producto->marca ?? '')
                            . ' - '
                            . ($producto->tipo ?? '')
                            . ' '
                            . ($producto->contenido ?? '')
                        );

                        throw new \Exception(
                            "No se puede editar el lote: el producto '{$nombre}' "
                            . 'ya fue vendido o consumido y no alcanza el stock para revertir.'
                        );
                    }

                    $producto->stock_venta = $nuevoStock;
                    $producto->save();
                }

                Compra::where('lote_id', $lote->id)->delete();

                $montoTotal = 0;

                foreach ($data['items'] as $item) {
                    $montoTotal += $this->subtotalConDescuento($item);
                }

                $montoTotal = round($montoTotal, 2);

                /*
                 * Las entregas ya no se registran desde Compras.
                 * Los movimientos se administran desde la cuenta corriente
                 * del proveedor. Se preservan pagos históricos, si existieran.
                 */
                $montoPagadoHistorico = round(
                    (float) $lote->pagos()->sum('monto'),
                    2
                );

                if ($montoPagadoHistorico > $montoTotal) {
                    throw new \Exception(
                        'No se puede guardar porque los pagos históricos del lote '
                        . 'superan el nuevo total. Revisá primero esos movimientos.'
                    );
                }

                $lote->update([
                    'fecha' => $data['fecha'],
                    'nota' => $data['nota'] ?? null,
                    'monto_total' => $montoTotal,
                    'monto_pagado' => $montoPagadoHistorico,
                    'estado_pago' => $this->estadoPagoCompra(
                        $montoTotal,
                        $montoPagadoHistorico
                    ),
                ]);

                foreach ($data['items'] as $item) {
                    Compra::create([
                        'lote_id' => $lote->id,
                        'fecha' => $data['fecha'],
                        'proveedor_id' => (int) $item['proveedor_id'],
                        'producto_id' => (int) $item['producto_id'],
                        'cantidad' => (int) $item['cantidad'],
                        'precio_unitario' => (float) $item['precio_unitario'],
                        'descuento_pct' => isset($item['descuento_pct'])
                            ? (float) $item['descuento_pct']
                            : 0,
                    ]);

                    $producto = Producto::lockForUpdate()
                        ->find((int) $item['producto_id']);

                    if ($producto) {
                        $producto->stock_venta =
                            (int) $producto->stock_venta
                            + (int) $item['cantidad'];

                        $producto->save();
                    }
                }
            });
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->withErrors(['compra' => $e->getMessage()]);
        }

        return redirect()
            ->route('compras.index')
            ->with('ok', 'Lote actualizado correctamente.');
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
        $saldo = round((float)$lote->monto_total - (float)$lote->monto_pagado, 2);

        $data = $request->validate([
            'fecha' => ['required', 'date'],
            'monto' => ['required', 'numeric', 'min:0.01', 'max:' . $saldo],
            'observacion' => ['nullable', 'string', 'max:255'],
        ], [
            'monto.max' => 'El monto ingresado no puede ser mayor al saldo pendiente ($' . number_format($saldo, 2, ',', '.') . ').',
        ]);

        if ($saldo <= 0) {
            return redirect()->route('compras.lotes.show', $lote)
                ->with('ok', 'Ese lote ya está pagado.');
        }

        DB::transaction(function () use ($data, $lote) {
            CompraPago::create([
                'compra_lote_id' => $lote->id,
                'fecha' => $data['fecha'],
                'monto' => round((float)$data['monto'], 2),
                'observacion' => $data['observacion'] ?? null,
            ]);

            $nuevoPagado = round((float)$lote->pagos()->sum('monto'), 2);

            $lote->update([
                'monto_pagado' => $nuevoPagado,
                'estado_pago' => $this->estadoPagoCompra((float)$lote->monto_total, $nuevoPagado),
            ]);
        });

        return redirect()->route('compras.lotes.show', $lote)
            ->with('ok', 'Pago registrado correctamente.');
    }
}