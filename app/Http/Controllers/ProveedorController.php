<?php

namespace App\Http\Controllers;

use App\Models\Compra;
use App\Models\Proveedor;
use App\Models\ProveedorPago;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ProveedorController extends Controller
{
    private function totalCompradoProveedor(int $proveedorId): float
    {
        return round((float) Compra::where('proveedor_id', $proveedorId)
            ->selectRaw('COALESCE(SUM(cantidad * precio_unitario * (1 - (COALESCE(descuento_pct, 0) / 100))), 0) as total')
            ->value('total'), 2);
    }

    private function totalPagadoProveedor(int $proveedorId): float
    {
        return round((float) ProveedorPago::where('proveedor_id', $proveedorId)->sum('monto'), 2);
    }

    public function index(Request $request)
    {
        $nombre = trim((string) $request->get('nombre', ''));

        $sort = $request->get('sort', 'created_at');
        $dir  = $request->get('dir', 'desc');

        $allowedSorts = ['nombre', 'created_at'];
        if (!in_array($sort, $allowedSorts, true)) {
            $sort = 'created_at';
        }

        $dir = $dir === 'asc' ? 'asc' : 'desc';

        $proveedores = Proveedor::query()
            ->when($nombre !== '', function ($q) use ($nombre) {
                $q->where('nombre', 'like', "%{$nombre}%");
            })
            ->orderBy($sort, $dir)
            ->paginate(10)
            ->withQueryString();

        foreach ($proveedores as $proveedor) {
            $totalCompras = $this->totalCompradoProveedor($proveedor->id);
            $totalPagos = $this->totalPagadoProveedor($proveedor->id);

            $proveedor->total_compras_cc = $totalCompras;
            $proveedor->total_pagos_cc = $totalPagos;
            $proveedor->saldo_cc = round($totalCompras - $totalPagos, 2);
        }

        return view('proveedores.index', compact('proveedores', 'nombre', 'sort', 'dir'));
    }

    public function create()
    {
        return view('proveedores.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:255', 'unique:proveedores,nombre'],
            'telefono' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'string', 'max:255'],
        ], [
            'nombre.unique' => 'Ese proveedor ya existe.',
        ]);

        $data['nombre'] = trim($data['nombre']);

        Proveedor::create($data);

        return redirect()->route('proveedores.index')->with('ok', 'Proveedor creado correctamente.');
    }

    public function edit(Proveedor $proveedore)
    {
        $proveedor = $proveedore;

        return view('proveedores.edit', compact('proveedor'));
    }

    public function update(Request $request, Proveedor $proveedore)
    {
        $proveedor = $proveedore;

        $data = $request->validate([
            'nombre' => [
                'required',
                'string',
                'max:255',
                Rule::unique('proveedores', 'nombre')->ignore($proveedor->id),
            ],
            'telefono' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'string', 'max:255'],
        ], [
            'nombre.unique' => 'Ese proveedor ya existe.',
        ]);

        $data['nombre'] = trim($data['nombre']);

        $proveedor->update($data);

        return redirect()->route('proveedores.index')->with('ok', 'Proveedor actualizado correctamente.');
    }

    public function destroy(Proveedor $proveedore)
    {
        $tieneCompras = Compra::where('proveedor_id', $proveedore->id)->exists();
        $tienePagos = ProveedorPago::where('proveedor_id', $proveedore->id)->exists();

        if ($tieneCompras || $tienePagos) {
            return redirect()
                ->route('proveedores.index')
                ->with('ok', 'No se puede eliminar el proveedor porque tiene compras o entregas registradas.');
        }

        $proveedore->delete();

        return redirect()->route('proveedores.index')->with('ok', 'Proveedor eliminado.');
    }

    public function cuenta(Proveedor $proveedor)
    {
        $compras = Compra::query()
            ->with(['producto', 'lote'])
            ->where('proveedor_id', $proveedor->id)
            ->orderBy('fecha')
            ->orderBy('id')
            ->get()
            ->map(function ($compra) {
                $descuento = (float)($compra->descuento_pct ?? 0);

                if ($descuento < 0) {
                    $descuento = 0;
                }

                if ($descuento > 100) {
                    $descuento = 100;
                }

                $subtotal = round(
                    (int)$compra->cantidad * (float)$compra->precio_unitario * (1 - ($descuento / 100)),
                    2
                );

                $productoNombre = trim(
                    ($compra->producto?->marca ?? '') . ' - ' .
                    ($compra->producto?->tipo ?? '') . ' ' .
                    ($compra->producto?->contenido ?? '')
                );

                return [
                    'fecha' => $compra->fecha,
                    'tipo' => 'compra',
                    'detalle' => 'Compra lote #' . $compra->lote_id . ' - ' . $productoNombre,
                    'monto' => $subtotal,
                    'compra_id' => $compra->id,
                    'lote_id' => $compra->lote_id,
                ];
            });

        $pagos = ProveedorPago::query()
            ->where('proveedor_id', $proveedor->id)
            ->orderBy('fecha')
            ->orderBy('id')
            ->get()
            ->map(function ($pago) {
                return [
                    'fecha' => $pago->fecha,
                    'tipo' => 'pago',
                    'detalle' => $pago->observacion ?: 'Entrega al proveedor',
                    'monto' => -abs((float)$pago->monto),
                    'pago_id' => $pago->id,
                    'lote_id' => null,
                ];
            });

        $movimientos = $compras
            ->concat($pagos)
            ->sortBy([
                ['fecha', 'asc'],
                ['tipo', 'asc'],
            ])
            ->values();

        $saldo = 0;

        $movimientos = $movimientos->map(function ($mov) use (&$saldo) {
            $saldo += (float)$mov['monto'];
            $mov['saldo'] = round($saldo, 2);
            return $mov;
        });

        $totalCompras = round((float)$compras->sum('monto'), 2);
        $totalPagos = round(abs((float)$pagos->sum('monto')), 2);
        $saldoFinal = round($totalCompras - $totalPagos, 2);

        return view('proveedores.cuenta', compact(
            'proveedor',
            'movimientos',
            'totalCompras',
            'totalPagos',
            'saldoFinal'
        ));
    }

    public function storePago(Request $request, Proveedor $proveedor)
    {
        $data = $request->validate([
            'fecha' => ['required', 'date'],
            'monto' => ['required', 'numeric', 'min:0.01'],
            'observacion' => ['nullable', 'string', 'max:255'],
        ]);

        ProveedorPago::create([
            'proveedor_id' => $proveedor->id,
            'fecha' => $data['fecha'],
            'monto' => round((float)$data['monto'], 2),
            'observacion' => $data['observacion'] ?? null,
        ]);

        return redirect()
            ->route('proveedores.cuenta', $proveedor)
            ->with('ok', 'Entrega registrada correctamente.');
    }

    public function updatePago(Request $request, Proveedor $proveedor, ProveedorPago $pago)
    {
        if ((int)$pago->proveedor_id !== (int)$proveedor->id) {
            abort(404);
        }

        $data = $request->validate([
            'fecha' => ['required', 'date'],
            'monto' => ['required', 'numeric', 'min:0.01'],
            'observacion' => ['nullable', 'string', 'max:255'],
        ]);

        $pago->update([
            'fecha' => $data['fecha'],
            'monto' => round((float)$data['monto'], 2),
            'observacion' => $data['observacion'] ?? null,
        ]);

        return redirect()
            ->route('proveedores.cuenta', $proveedor)
            ->with('ok', 'Entrega actualizada correctamente.');
    }

    public function destroyPago(Proveedor $proveedor, ProveedorPago $pago)
    {
        if ((int)$pago->proveedor_id !== (int)$proveedor->id) {
            abort(404);
        }

        $pago->delete();

        return redirect()
            ->route('proveedores.cuenta', $proveedor)
            ->with('ok', 'Entrega eliminada correctamente.');
    }
}