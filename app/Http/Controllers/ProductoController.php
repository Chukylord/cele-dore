<?php

namespace App\Http\Controllers;

use App\Models\Compra;
use App\Models\ConsumoPeluqueria;
use App\Models\Producto;
use App\Models\Proveedor;
use App\Models\VentaProducto;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ProductoController extends Controller
{
    public function index(Request $request)
    {
        $proveedor_id = trim((string) $request->get('proveedor_id', ''));
        $marca = trim((string) $request->get('marca', ''));
        $tipo = trim((string) $request->get('tipo', ''));
        $contenido = trim((string) $request->get('contenido', ''));
        $stock_estado = trim((string) $request->get('stock_estado', ''));

        $sort = $request->get('sort', 'created_at');
        $dir = $request->get('dir', 'desc');
        $dir = $dir === 'asc' ? 'asc' : 'desc';

        $sortPermitidos = [
            'created_at',
            'proveedor_id',
            'marca',
            'tipo',
            'contenido',
            'stock_venta',
            'stock_minimo',
            'stock_peluqueria',
        ];

        if (! in_array($sort, $sortPermitidos, true)) {
            $sort = 'created_at';
        }

        $query = Producto::query()->with('proveedor');

        if ($proveedor_id !== '') {
            $query->where('proveedor_id', (int) $proveedor_id);
        }

        if ($marca !== '') {
            $query->where('marca', 'like', "%{$marca}%");
        }

        if ($tipo !== '') {
            $query->where('tipo', 'like', "%{$tipo}%");
        }

        if ($contenido !== '') {
            $query->where('contenido', 'like', "%{$contenido}%");
        }

        if ($stock_estado === 'sin_stock') {
            $query->where('stock_venta', '<=', 0);
        }

        if ($stock_estado === 'stock_minimo') {
            $query->where('stock_venta', '>', 0)
                ->whereColumn('stock_venta', '<=', 'stock_minimo');
        }

        $productos = $query
            ->orderBy($sort, $dir)
            ->paginate(10)
            ->withQueryString();

        $proveedores = Proveedor::orderBy('nombre')->get();

        return view('productos.index', compact(
            'productos',
            'proveedores',
            'proveedor_id',
            'marca',
            'tipo',
            'contenido',
            'stock_estado',
            'sort',
            'dir'
        ));
    }

    public function create()
    {
        $proveedores = Proveedor::orderBy('nombre')->get();

        return view('productos.create', compact('proveedores'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'proveedor_id' => ['required', 'exists:proveedores,id'],
            'marca' => ['required', 'string', 'max:255'],
            'tipo' => ['required', 'string', 'max:255'],
            'contenido' => ['required', 'string', 'max:255'],
            'codigo_barra' => ['nullable', 'string', 'max:255'],
            'precio_venta' => ['required', 'numeric', 'min:0'],
            'stock_venta' => ['required', 'integer', 'min:0'],
            'stock_minimo' => ['required', 'integer', 'min:0'],
            'force_create' => ['nullable'],
        ]);

        $data['marca'] = trim($data['marca']);
        $data['tipo'] = trim($data['tipo']);
        $data['contenido'] = trim($data['contenido']);

        $exists = Producto::where('proveedor_id', $data['proveedor_id'])
            ->where('marca', $data['marca'])
            ->where('tipo', $data['tipo'])
            ->where('contenido', $data['contenido'])
            ->exists();

        if ($exists && ! $request->has('force_create')) {
            return back()
                ->withInput()
                ->with('dup_producto', true);
        }

        $data['stock_peluqueria'] = 0;

        Producto::create($data);

        return redirect()->route('productos.index')->with('ok', 'Producto creado correctamente.');
    }

    public function edit(Request $request, Producto $producto)
    {
        $proveedores = Proveedor::orderBy('nombre')->get();
        $returnTo = $this->urlRetornoSegura($request);

        return view('productos.edit', compact('producto', 'proveedores', 'returnTo'));
    }

    public function update(Request $request, Producto $producto)
    {
        $data = $request->validate([
            'proveedor_id' => ['required', 'exists:proveedores,id'],
            'marca' => ['required', 'string', 'max:255'],
            'tipo' => ['required', 'string', 'max:255'],
            'contenido' => ['required', 'string', 'max:255'],
            'precio_venta' => ['required', 'numeric', 'min:0'],
            'stock_venta' => ['required', 'integer', 'min:0'],
            'stock_minimo' => ['required', 'integer', 'min:0'],
            'codigo_barra' => ['nullable', 'string', 'max:100'],
        ]);

        $data['marca'] = trim($data['marca']);
        $data['tipo'] = trim($data['tipo']);
        $data['contenido'] = trim($data['contenido']);

        $exists = Producto::where('proveedor_id', $data['proveedor_id'])
            ->where('marca', $data['marca'])
            ->where('tipo', $data['tipo'])
            ->where('contenido', $data['contenido'])
            ->where('id', '!=', $producto->id)
            ->exists();

        if ($exists) {
            return back()
                ->withInput()
                ->with('ok', 'Ya existe otro producto con ese proveedor, marca, tipo y contenido.');
        }

        $producto->update($data);

        return $this->redirectToListado($request, 'Producto actualizado correctamente.');
    }

    public function destroy(Request $request, Producto $producto)
    {
        $tieneVentas = VentaProducto::where('producto_id', $producto->id)->exists();
        $tieneCompras = Compra::where('producto_id', $producto->id)->exists();
        $tieneConsumos = ConsumoPeluqueria::where('producto_id', $producto->id)->exists();

        if ($tieneVentas || $tieneCompras || $tieneConsumos) {
            return $this->redirectToListado(
                $request,
                'No se puede eliminar el producto porque tiene ventas, compras o consumos asociados.'
            );
        }

        $producto->delete();

        return $this->redirectToListado($request, 'Producto eliminado.');
    }

    private function costoVigente(Producto $producto): float
    {
        $ultimaCompra = Compra::where('producto_id', $producto->id)
            ->orderBy('created_at', 'desc')
            ->orderBy('id', 'desc')
            ->first();

        $manualMasNuevo = $producto->precio_efectivo_manual !== null
            && ! empty($producto->precio_manual_updated_at)
            && (
                ! $ultimaCompra
                || Carbon::parse($producto->precio_manual_updated_at)
                    ->greaterThanOrEqualTo($ultimaCompra->created_at)
            );

        if ($manualMasNuevo) {
            return round((float) $producto->precio_efectivo_manual / 1.40, 2);
        }

        if ($ultimaCompra) {
            return round((float) $ultimaCompra->precio_unitario, 2);
        }

        if ($producto->precio_efectivo_manual !== null) {
            return round((float) $producto->precio_efectivo_manual / 1.40, 2);
        }

        return round((float) $producto->precio_venta / 1.40, 2);
    }

    public function consumo(Request $request, Producto $producto)
    {
        try {
            DB::transaction(function () use ($producto) {
                $p = Producto::lockForUpdate()->find($producto->id);

                if ((int) $p->stock_venta <= 0) {
                    throw new \Exception('No hay stock de venta disponible para consumir.');
                }

                $costo = $this->costoVigente($p);

                if ($costo <= 0) {
                    throw new \Exception(
                        'El producto no tiene un precio válido. Completalo primero en Lista de precios.'
                    );
                }

                $p->stock_venta = (int) $p->stock_venta - 1;
                $p->stock_peluqueria = (int) $p->stock_peluqueria + 1;
                $p->save();

                ConsumoPeluqueria::create([
                    'fecha' => now(),
                    'producto_id' => $p->id,
                    'cantidad' => 1,
                    'costo_unitario' => $costo,
                    'total' => $costo,
                ]);
            });
        } catch (\Exception $e) {
            return $this->redirectToListado($request, $e->getMessage());
        }

        return $this->redirectToListado($request, 'Consumo registrado: 1 unidad pas? a stock peluquer?a.');
    }

    public function usarPeluqueria(Request $request, Producto $producto)
    {
        try {
            DB::transaction(function () use ($producto) {
                $p = Producto::lockForUpdate()->find($producto->id);

                if ((int) $p->stock_peluqueria <= 0) {
                    throw new \Exception('No hay stock de peluquería para descontar.');
                }

                $p->stock_peluqueria = (int) $p->stock_peluqueria - 1;
                $p->save();
            });
        } catch (\Exception $e) {
            return $this->redirectToListado($request, $e->getMessage());
        }

        return $this->redirectToListado($request, 'Se descont? 1 unidad del stock de peluquer?a.');
    }

    private function redirectToListado(Request $request, string $mensaje): RedirectResponse
    {
        $returnTo = $this->urlRetornoSegura($request);

        return ($returnTo ? redirect()->to($returnTo) : redirect()->route('productos.index'))
            ->with('ok', $mensaje);
    }

    private function urlRetornoSegura(Request $request): ?string
    {
        $returnTo = trim((string) $request->input('return_to', $request->query('return_to', '')));

        if ($returnTo === '' || ! str_starts_with($returnTo, '/') || str_starts_with($returnTo, '//') || str_contains($returnTo, '\\') || preg_match('/[\x00-\x1F\x7F]/', $returnTo)) {
            return null;
        }

        $partes = parse_url($returnTo);
        $rutaProductos = parse_url(route('productos.index', absolute: false), PHP_URL_PATH);

        if ($partes === false || isset($partes['scheme'], $partes['host'], $partes['user'], $partes['pass']) || isset($partes['fragment']) || ($partes['path'] ?? null) !== $rutaProductos) {
            return null;
        }

        return $returnTo;
    }
}
