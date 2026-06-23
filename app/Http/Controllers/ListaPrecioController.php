<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Models\Proveedor;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ListaPrecioController extends Controller
{
    private function round2(float $n): float
    {
        return round($n, 2);
    }

    private function queryConUltimoCosto(): Builder
    {
        return Producto::query()
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
            }, 'ultimo_costo_at');
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

        $ultimoCosto = (float) ($producto->ultimo_costo ?? 0);

        if ($ultimoCosto > 0) {
            return $this->round2($ultimoCosto * 1.40);
        }

        if ($producto->precio_efectivo_manual !== null) {
            return $this->round2((float) $producto->precio_efectivo_manual);
        }

        return $this->round2((float) $producto->precio_venta);
    }

    private function calcularPrecioTarjeta(Producto $producto): float
    {
        return $this->round2($this->calcularPrecioEfectivo($producto) * 1.20);
    }

    private function calcularCostoColaboradora(Producto $producto): float
    {
        /*
         * Si el precio manual es más nuevo que la última compra, se considera
         * que la clienta actualizó el valor vigente del producto. En ese caso,
         * el costo estimado se obtiene quitando el 40% de margen.
         */
        if ($this->manualEsMasNuevoQueCompra($producto)) {
            return $this->round2((float) $producto->precio_efectivo_manual / 1.40);
        }

        $ultimoCosto = (float) ($producto->ultimo_costo ?? 0);

        if ($ultimoCosto > 0) {
            return $this->round2($ultimoCosto);
        }

        if ($producto->precio_efectivo_manual !== null) {
            return $this->round2((float) $producto->precio_efectivo_manual / 1.40);
        }

        $precioInicial = (float) $producto->precio_venta;

        return $precioInicial > 0
            ? $this->round2($precioInicial / 1.40)
            : 0.0;
    }

    private function precioOrigen(Producto $producto): string
    {
        if ($this->manualEsMasNuevoQueCompra($producto)) {
            return 'Manual';
        }

        if ((float) ($producto->ultimo_costo ?? 0) > 0) {
            return 'Automático';
        }

        if ($producto->precio_efectivo_manual !== null) {
            return 'Manual';
        }

        if ((float) $producto->precio_venta > 0) {
            return 'Inicial';
        }

        return 'Sin precio';
    }

    private function prepararProducto(Producto $producto): void
    {
        $producto->precio_efectivo_calculado = $this->calcularPrecioEfectivo($producto);
        $producto->precio_tarjeta_calculado = $this->calcularPrecioTarjeta($producto);
        $producto->costo_colaboradora_calculado = $this->calcularCostoColaboradora($producto);
        $producto->precio_origen = $this->precioOrigen($producto);
    }

    public function index(Request $request)
    {
        $buscar = trim((string) $request->get('buscar', ''));
        $proveedor_id = trim((string) $request->get('proveedor_id', ''));

        $query = $this->queryConUltimoCosto()->with('proveedor');

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

        $productos->each(fn (Producto $producto) => $this->prepararProducto($producto));

        /*
         * El scanner consulta todos los productos, aunque haya filtros activos
         * en la tabla.
         */
        $productosScanner = $this->queryConUltimoCosto()
            ->with('proveedor')
            ->orderBy('marca')
            ->orderBy('tipo')
            ->get();

        $productosScanner->each(fn (Producto $producto) => $this->prepararProducto($producto));

        $proveedores = Proveedor::orderBy('nombre')->get();

        return view('lista-precios.index', compact(
            'productos',
            'productosScanner',
            'proveedores',
            'buscar',
            'proveedor_id'
        ));
    }

    public function actualizar(Request $request)
    {
        $data = $request->validate([
            'accion' => ['required', 'in:guardar,aumentar,restablecer'],
            'productos' => ['nullable', 'array'],
            'productos.*.precio_efectivo_manual' => ['nullable', 'numeric', 'min:0'],
            'modificados' => ['nullable', 'array'],
            'modificados.*' => ['integer', 'exists:productos,id'],
            'seleccionados' => ['nullable', 'array'],
            'seleccionados.*' => ['integer', 'exists:productos,id'],
            'porcentaje_aumento' => ['nullable', 'numeric', 'min:0'],
            'restablecer_id' => ['nullable', 'integer', 'exists:productos,id'],
            'buscar_actual' => ['nullable', 'string'],
            'proveedor_actual' => ['nullable', 'string'],
        ]);

        $redirectParams = array_filter([
            'buscar' => trim((string) ($data['buscar_actual'] ?? '')),
            'proveedor_id' => trim((string) ($data['proveedor_actual'] ?? '')),
        ], fn ($valor) => $valor !== '');

        if ($data['accion'] === 'guardar') {
            $modificados = array_values(array_unique(array_map(
                'intval',
                $data['modificados'] ?? []
            )));

            if (count($modificados) === 0) {
                return redirect()
                    ->route('lista-precios.index', $redirectParams)
                    ->with('ok', 'No había cambios para guardar.');
            }

            DB::transaction(function () use ($modificados, $data) {
                foreach ($modificados as $productoId) {
                    $producto = Producto::lockForUpdate()->find($productoId);

                    if (!$producto) {
                        continue;
                    }

                    $precio = $data['productos'][$productoId]['precio_efectivo_manual'] ?? null;

                    if ($precio === null || $precio === '' || (float) $precio <= 0) {
                        throw ValidationException::withMessages([
                            "productos.$productoId.precio_efectivo_manual" =>
                                'El precio efectivo debe ser mayor a $0.',
                        ]);
                    }

                    $producto->precio_efectivo_manual = $this->round2((float) $precio);
                    $producto->precio_manual_updated_at = now();
                    $producto->precio_tarjeta_manual = null;
                    $producto->save();
                }
            });

            return redirect()
                ->route('lista-precios.index', $redirectParams)
                ->with('ok', count($modificados) === 1
                    ? 'Se actualizó 1 precio correctamente.'
                    : 'Se actualizaron ' . count($modificados) . ' precios correctamente.');
        }

        if ($data['accion'] === 'restablecer') {
            $producto = Producto::findOrFail((int) $data['restablecer_id']);

            $producto->precio_efectivo_manual = null;
            $producto->precio_tarjeta_manual = null;
            $producto->precio_manual_updated_at = null;
            $producto->save();

            return redirect()
                ->route('lista-precios.index', $redirectParams)
                ->with('ok', 'El producto volvió al precio automático.');
        }

        $seleccionados = array_values(array_unique(array_map(
            'intval',
            $data['seleccionados'] ?? []
        )));

        $porcentaje = (float) ($data['porcentaje_aumento'] ?? 0);

        if (count($seleccionados) === 0) {
            return redirect()
                ->route('lista-precios.index', $redirectParams)
                ->with('ok', 'Seleccioná al menos un producto para aumentar.');
        }

        if ($porcentaje <= 0) {
            return redirect()
                ->route('lista-precios.index', $redirectParams)
                ->with('ok', 'Ingresá un porcentaje mayor a 0.');
        }

        $productos = $this->queryConUltimoCosto()
            ->whereIn('productos.id', $seleccionados)
            ->get();

        $actualizados = 0;
        $omitidos = 0;

        DB::transaction(function () use ($productos, $porcentaje, &$actualizados, &$omitidos) {
            foreach ($productos as $producto) {
                $precioEfectivoActual = $this->calcularPrecioEfectivo($producto);

                if ($precioEfectivoActual <= 0) {
                    $omitidos++;
                    continue;
                }

                $productoReal = Producto::lockForUpdate()->find($producto->id);

                if (!$productoReal) {
                    continue;
                }

                $productoReal->precio_efectivo_manual = $this->round2(
                    $precioEfectivoActual * (1 + ($porcentaje / 100))
                );

                $productoReal->precio_manual_updated_at = now();
                $productoReal->precio_tarjeta_manual = null;
                $productoReal->save();

                $actualizados++;
            }
        });

        $mensaje = 'Se aumentó el ' . rtrim(rtrim(number_format($porcentaje, 2, '.', ''), '0'), '.')
            . '% a ' . $actualizados . ' producto(s).';

        if ($omitidos > 0) {
            $mensaje .= ' Se omitieron ' . $omitidos . ' porque no tenían precio cargado.';
        }

        return redirect()
            ->route('lista-precios.index', $redirectParams)
            ->with('ok', $mensaje);
    }
}
