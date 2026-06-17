<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Models\Proveedor;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class ListaPrecioController extends Controller
{
    private function round2(float $n): float
    {
        return round($n, 2);
    }

    private function manualEsMasNuevoQueCompra(Producto $producto): bool
    {
        if ($producto->precio_efectivo_manual === null) {
            return false;
        }

        if (empty($producto->precio_manual_updated_at)) {
            return false;
        }

        if (empty($producto->ultimo_costo_at)) {
            return true;
        }

        return Carbon::parse($producto->precio_manual_updated_at)
            ->greaterThanOrEqualTo(Carbon::parse($producto->ultimo_costo_at));
    }

    private function calcularPrecioEfectivo(Producto $producto): float
    {
        if ($this->manualEsMasNuevoQueCompra($producto)) {
            return $this->round2((float) $producto->precio_efectivo_manual);
        }

        $costo = (float) ($producto->ultimo_costo ?? 0);

        if ($costo > 0) {
            return $this->round2($costo * 1.40);
        }

        if ($producto->precio_efectivo_manual !== null) {
            return $this->round2((float) $producto->precio_efectivo_manual);
        }

        return $this->round2((float) $producto->precio_venta);
    }

    private function calcularPrecioTarjeta(Producto $producto): float
    {
        $precioEfectivo = $this->calcularPrecioEfectivo($producto);

        return $this->round2($precioEfectivo * 1.20);
    }

    private function precioOrigen(Producto $producto): string
    {
        return $this->manualEsMasNuevoQueCompra($producto) ? 'Manual' : 'Automático';
    }

    public function index(Request $request)
    {
        $buscar = trim((string) $request->get('buscar', ''));
        $proveedor_id = trim((string) $request->get('proveedor_id', ''));

        $query = Producto::query()
            ->select('productos.*')

            // Último costo cargado por orden real de carga
            ->selectSub(function ($q) {
                $q->from('compras')
                    ->select('precio_unitario')
                    ->whereColumn('compras.producto_id', 'productos.id')
                    ->orderBy('created_at', 'desc')
                    ->orderBy('id', 'desc')
                    ->limit(1);
            }, 'ultimo_costo')

            // Fecha/hora de la última compra cargada
            ->selectSub(function ($q) {
                $q->from('compras')
                    ->select('created_at')
                    ->whereColumn('compras.producto_id', 'productos.id')
                    ->orderBy('created_at', 'desc')
                    ->orderBy('id', 'desc')
                    ->limit(1);
            }, 'ultimo_costo_at')

            ->with('proveedor');

        if ($buscar !== '') {
            $query->where(function ($q) use ($buscar) {
                $q->where('marca', 'like', '%' . $buscar . '%')
                    ->orWhere('tipo', 'like', '%' . $buscar . '%')
                    ->orWhere('contenido', 'like', '%' . $buscar . '%')
                    ->orWhere('codigo_barra', 'like', '%' . $buscar . '%');
            });
        }

        if ($proveedor_id !== '') {
            $query->where('proveedor_id', (int) $proveedor_id);
        }

        $productos = $query
            ->orderBy('marca')
            ->orderBy('tipo')
            ->orderBy('contenido')
            ->get();

        $productos->each(function ($producto) {
            $producto->precio_efectivo_calculado = $this->calcularPrecioEfectivo($producto);
            $producto->precio_tarjeta_calculado = $this->calcularPrecioTarjeta($producto);
            $producto->precio_origen = $this->precioOrigen($producto);
        });

        $proveedores = Proveedor::orderBy('nombre')->get();

        return view('lista-precios.index', compact(
            'productos',
            'proveedores',
            'buscar',
            'proveedor_id'
        ));
    }

    public function actualizar(Request $request)
    {
        $data = $request->validate([
            'accion' => ['required', 'in:guardar,aumentar'],
            'productos' => ['nullable', 'array'],
            'productos.*.precio_efectivo_manual' => ['nullable', 'numeric', 'min:0'],
            'productos.*.precio_tarjeta_manual' => ['nullable', 'numeric', 'min:0'],
            'seleccionados' => ['nullable', 'array'],
            'seleccionados.*' => ['integer', 'exists:productos,id'],
            'porcentaje_aumento' => ['nullable', 'numeric', 'min:0'],
        ]);

        $accion = $data['accion'];

        if ($accion === 'guardar') {
            foreach (($data['productos'] ?? []) as $productoId => $valores) {
                $producto = Producto::find((int) $productoId);

                if (!$producto) {
                    continue;
                }

                $precioEfectivo = $valores['precio_efectivo_manual'] ?? null;

                if ($precioEfectivo !== null && $precioEfectivo !== '') {
                    $producto->precio_efectivo_manual = round((float) $precioEfectivo, 2);
                    $producto->precio_manual_updated_at = now();
                } else {
                    $producto->precio_efectivo_manual = null;
                    $producto->precio_manual_updated_at = null;
                }

                // Tarjeta ya no se guarda aparte. Siempre se calcula como efectivo + 20%.
                $producto->precio_tarjeta_manual = null;

                $producto->save();
            }

            return redirect()
                ->route('lista-precios.index')
                ->with('ok', 'Lista de precios actualizada correctamente.');
        }

        if ($accion === 'aumentar') {
            $seleccionados = $data['seleccionados'] ?? [];
            $porcentaje = (float) ($data['porcentaje_aumento'] ?? 0);

            if (count($seleccionados) === 0) {
                return back()->with('ok', 'Seleccioná al menos un producto para aumentar.');
            }

            if ($porcentaje <= 0) {
                return back()->with('ok', 'Ingresá un porcentaje mayor a 0.');
            }

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
                ->whereIn('id', $seleccionados)
                ->get();

            foreach ($productos as $producto) {
                $precioEfectivoActual = $this->calcularPrecioEfectivo($producto);

                $producto->precio_efectivo_manual = $this->round2(
                    $precioEfectivoActual * (1 + ($porcentaje / 100))
                );

                $producto->precio_manual_updated_at = now();
                $producto->precio_tarjeta_manual = null;

                $producto->save();
            }

            return redirect()
                ->route('lista-precios.index')
                ->with('ok', 'Se aumentó el ' . $porcentaje . '% a los productos seleccionados.');
        }

        return redirect()->route('lista-precios.index');
    }
}