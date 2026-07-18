<?php

namespace App\Http\Controllers;

use App\Models\Producto;

class ProductoScannerController extends Controller
{
    public function __invoke(string $codigo)
    {
        $codigo = trim($codigo);

        $producto = Producto::query()
            ->with('proveedor')
            ->where('codigo_barra', $codigo)
            ->first();

        if (!$producto) {
            return response()->json([
                'message' => 'No se encontró un producto con ese código de barras.',
            ], 404);
        }

        return response()->json([
            'id' => $producto->id,
            'nombre' => trim($producto->marca . ' - ' . $producto->tipo . ' ' . $producto->contenido),
            'proveedor' => $producto->proveedor?->nombre,
            'codigo_barra' => $producto->codigo_barra,
            'stock_venta' => (int) $producto->stock_venta,
            'stock_peluqueria' => (int) $producto->stock_peluqueria,
            'puede_consumir' => (int) $producto->stock_venta > 0,
            'consumo_url' => route('productos.consumo', $producto),
        ]);
    }
}
