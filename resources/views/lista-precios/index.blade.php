@extends('layouts.admin')

@section('title', 'Lista de precios')
@section('h1', 'Lista de precios')
@section('sub', 'Consultar, escanear y actualizar precios de productos.')

@section('content')

@if(session('ok'))
    <div class="mb-4 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-green-800">
        {{ session('ok') }}
    </div>
@endif

@if($errors->any())
    <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-red-800">
        <div class="font-semibold mb-1">Hay errores:</div>
        <ul class="list-disc pl-5">
            @foreach($errors->all() as $e)
                <li>{{ $e }}</li>
            @endforeach
        </ul>
    </div>
@endif

@php
    $productosScanner = [];

    foreach ($productos as $p) {
        $nombre = trim(($p->marca ?? '') . ' - ' . ($p->tipo ?? '') . ' ' . ($p->contenido ?? ''));

        if (!empty($p->codigo_barra)) {
            $productosScanner[(string)$p->codigo_barra] = [
                'id' => $p->id,
                'nombre' => $nombre,
                'proveedor' => optional($p->proveedor)->nombre,
                'stock' => (int)$p->stock_venta,
                'costo' => (float)($p->ultimo_costo ?? 0),
                'efectivo' => (float)$p->precio_efectivo_calculado,
                'tarjeta' => (float)$p->precio_tarjeta_calculado,
                'codigo_barra' => $p->codigo_barra,
            ];
        }
    }
@endphp

<div class="rounded-2xl border bg-white p-4 mb-5">
    <form method="GET" action="{{ route('lista-precios.index') }}" class="grid grid-cols-1 md:grid-cols-5 gap-4 items-end">
        <div class="md:col-span-2">
            <label class="text-sm font-semibold text-slate-700">Buscar producto</label>
            <input type="text"
                   name="buscar"
                   value="{{ $buscar }}"
                   placeholder="Marca, tipo, contenido o código..."
                   class="mt-1 w-full rounded-xl border-slate-300 focus:border-slate-500 focus:ring-slate-500">
        </div>

        <div class="md:col-span-2">
            <label class="text-sm font-semibold text-slate-700">Proveedor</label>
            <select name="proveedor_id"
                    class="mt-1 w-full rounded-xl border-slate-300 focus:border-slate-500 focus:ring-slate-500">
                <option value="">Todos</option>
                @foreach($proveedores as $proveedor)
                    <option value="{{ $proveedor->id }}" {{ (string)$proveedor_id === (string)$proveedor->id ? 'selected' : '' }}>
                        {{ $proveedor->nombre }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="flex gap-2">
            <button class="rounded-xl bg-slate-900 text-white px-4 py-2 hover:bg-slate-800">
                Filtrar
            </button>

            <a href="{{ route('lista-precios.index') }}"
               class="rounded-xl border px-4 py-2 hover:bg-slate-50">
                Limpiar
            </a>
        </div>
    </form>
</div>

<div class="rounded-2xl border bg-slate-900 text-white p-4 mb-5">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
        <div class="md:col-span-2">
            <label class="text-sm font-semibold text-slate-200">Consultar precio con scanner</label>
            <input id="scanner_precio"
                   type="text"
                   autocomplete="off"
                   placeholder="Hacé click acá y escaneá el producto..."
                   class="mt-1 w-full rounded-xl border-slate-700 bg-slate-800 text-white focus:border-white focus:ring-white">
            <div class="text-xs text-slate-400 mt-1">
                El lector funciona como teclado. Al escanear, se muestra el precio automáticamente.
            </div>
        </div>

        <div id="resultadoScanner"
             class="rounded-xl border border-slate-700 bg-slate-800 p-3 text-sm text-slate-300">
            Esperando código...
        </div>
    </div>
</div>

<form method="POST" action="{{ route('lista-precios.actualizar') }}" id="formListaPrecios">
    @csrf

    <div class="rounded-2xl border bg-white p-4 mb-5">
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4 items-end">
            <div class="md:col-span-2">
                <div class="text-lg font-bold text-slate-800">Aumento masivo</div>
                <div class="text-sm text-slate-600">
                    Seleccioná productos de la tabla y aplicá el porcentaje que quieras.
                </div>
            </div>

            <div>
                <label class="text-sm font-semibold text-slate-700">% aumento</label>
                <input type="number"
                       step="0.01"
                       min="0"
                       name="porcentaje_aumento"
                       placeholder="Ej: 10"
                       class="mt-1 w-full rounded-xl border-slate-300 focus:border-slate-500 focus:ring-slate-500">
            </div>

            <div class="md:col-span-2 flex flex-wrap gap-2 md:justify-end">
                <button type="submit"
                        name="accion"
                        value="aumentar"
                        class="rounded-xl bg-indigo-600 text-white px-4 py-2 hover:bg-indigo-700">
                    Aumentar seleccionados
                </button>

                <button type="submit"
                        name="accion"
                        value="guardar"
                        class="rounded-xl bg-slate-900 text-white px-4 py-2 hover:bg-slate-800">
                    Guardar precios manuales
                </button>
            </div>
        </div>
    </div>

    <div class="rounded-2xl border bg-white overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full bg-white">
                <thead class="bg-slate-50 text-slate-700">
                <tr>
                    <th class="px-4 py-3 text-left">
                        <input type="checkbox" id="checkTodos" class="rounded border-slate-300">
                    </th>
                    <th class="px-4 py-3 text-left text-sm font-semibold">Producto</th>
                    <th class="px-4 py-3 text-left text-sm font-semibold">Proveedor</th>
                    <th class="px-4 py-3 text-left text-sm font-semibold">Código</th>
                    <th class="px-4 py-3 text-left text-sm font-semibold">Stock</th>
                    <th class="px-4 py-3 text-left text-sm font-semibold">Costo</th>
                    <th class="px-4 py-3 text-left text-sm font-semibold">Efectivo / Transferencia</th>
                    <th class="px-4 py-3 text-left text-sm font-semibold">Tarjeta</th>
                    <th class="px-4 py-3 text-left text-sm font-semibold">Tipo</th>
                </tr>
                </thead>

                <tbody>
                @forelse($productos as $producto)
                    @php
                        $nombre = trim(($producto->marca ?? '') . ' - ' . ($producto->tipo ?? '') . ' ' . ($producto->contenido ?? ''));
                        $esManual = $producto->precio_efectivo_manual !== null || $producto->precio_tarjeta_manual !== null;
                    @endphp

                    <tr class="border-t hover:bg-slate-50">
                        <td class="px-4 py-3">
                            <input type="checkbox"
                                   name="seleccionados[]"
                                   value="{{ $producto->id }}"
                                   class="checkProducto rounded border-slate-300">
                        </td>

                        <td class="px-4 py-3">
                            <div class="font-semibold text-slate-800">{{ $nombre }}</div>
                        </td>

                        <td class="px-4 py-3 text-sm text-slate-600">
                            {{ optional($producto->proveedor)->nombre ?? '-' }}
                        </td>

                        <td class="px-4 py-3 text-sm text-slate-600">
                            {{ $producto->codigo_barra ?: '-' }}
                        </td>

                        <td class="px-4 py-3 text-sm">
                            {{ (int)$producto->stock_venta }}
                        </td>

                        <td class="px-4 py-3 text-sm">
                            ${{ number_format((float)($producto->ultimo_costo ?? 0), 2, ',', '.') }}
                        </td>

                        <td class="px-4 py-3">
                            <input type="number"
                                   step="0.01"
                                   min="0"
                                   name="productos[{{ $producto->id }}][precio_efectivo_manual]"
                                   value="{{ number_format((float)$producto->precio_efectivo_calculado, 2, '.', '') }}"
                                   class="w-32 rounded-xl border-slate-300 focus:border-slate-500 focus:ring-slate-500">
                        </td>

                        <td class="px-4 py-3">
                            <input type="number"
                                   step="0.01"
                                   min="0"
                                   name="productos[{{ $producto->id }}][precio_tarjeta_manual]"
                                   value="{{ number_format((float)$producto->precio_tarjeta_calculado, 2, '.', '') }}"
                                   class="w-32 rounded-xl border-slate-300 focus:border-slate-500 focus:ring-slate-500">
                        </td>

                        <td class="px-4 py-3">
                            @if($esManual)
                                <span class="rounded-full bg-amber-100 text-amber-700 px-3 py-1 text-xs font-semibold">
                                    Manual
                                </span>
                            @else
                                <span class="rounded-full bg-green-100 text-green-700 px-3 py-1 text-xs font-semibold">
                                    Automático
                                </span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="px-4 py-6 text-center text-slate-500">
                            No hay productos para mostrar.
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</form>

<script>
const PRODUCTOS_SCANNER = @json($productosScanner);

function money(n){
    n = Number(n || 0);
    return '$' + n.toLocaleString('es-AR', {minimumFractionDigits:2, maximumFractionDigits:2});
}

document.addEventListener('DOMContentLoaded', function(){
    const scanner = document.getElementById('scanner_precio');
    const resultado = document.getElementById('resultadoScanner');
    const checkTodos = document.getElementById('checkTodos');

    function consultarCodigo(codigo){
        const codigoLimpio = String(codigo || '').trim();

        if (!codigoLimpio) {
            return;
        }

        const producto = PRODUCTOS_SCANNER[codigoLimpio];

        if (!producto) {
            resultado.innerHTML = `
                <div class="font-bold text-red-300">Producto no encontrado</div>
                <div class="text-slate-400">Código: ${codigoLimpio}</div>
            `;
            return;
        }

        resultado.innerHTML = `
            <div class="font-bold text-white">${producto.nombre}</div>
            <div class="text-slate-300">Proveedor: ${producto.proveedor || '-'}</div>
            <div class="text-slate-300">Stock: ${producto.stock}</div>
            <div class="mt-2 text-green-300 font-bold">Efectivo / Transferencia: ${money(producto.efectivo)}</div>
            <div class="text-blue-300 font-bold">Tarjeta: ${money(producto.tarjeta)}</div>
        `;
    }

    if (scanner) {
        scanner.addEventListener('keydown', function(e){
            if (e.key === 'Enter') {
                e.preventDefault();
                consultarCodigo(this.value);
                this.value = '';
            }
        });

        scanner.addEventListener('change', function(){
            consultarCodigo(this.value);
            this.value = '';
        });
    }

    if (checkTodos) {
        checkTodos.addEventListener('change', function(){
            document.querySelectorAll('.checkProducto').forEach(chk => {
                chk.checked = checkTodos.checked;
            });
        });
    }
});
</script>

@endsection