@extends('layouts.admin')

@section('title', 'Productos - fn peluqueria')
@section('h1', 'Productos')
@section('sub', 'Gestión de stock y consumo interno de peluquería.')

@section('content')

@if(session('ok'))
    <div class="mb-4 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-green-800">
        {{ session('ok') }}
    </div>
@endif

@if($errors->any())
    <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-red-800">
        <ul class="list-disc pl-5">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

@php
    function sort_link_productos($label, $field, $sort, $dir) {
        $isActive = $sort === $field;
        $nextDir = ($isActive && $dir === 'asc') ? 'desc' : 'asc';
        $arrow = $isActive ? ($dir === 'asc' ? ' ↑' : ' ↓') : '';
        $url = request()->fullUrlWithQuery(['sort' => $field, 'dir' => $nextDir]);

        return '<a class="hover:underline" href="'.$url.'">'.$label.$arrow.'</a>';
    }

    $proveedoresMap = $proveedores->pluck('id', 'nombre');
    $proveedorTexto = '';

    if (!empty($proveedor_id)) {
        $proveedorEncontrado = $proveedores->firstWhere('id', (int)$proveedor_id);
        $proveedorTexto = $proveedorEncontrado ? $proveedorEncontrado->nombre : '';
    }

    $stock_estado = $stock_estado ?? request('stock_estado', '');
    $scanUrl = route('productos.scan', ['codigo' => '__CODIGO__']);
    $returnTo = request()->getRequestUri();
@endphp

<div class="rounded-2xl border bg-slate-900 text-white p-5 mb-6">
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 items-stretch">
        <div class="lg:col-span-5">
            <div class="text-xl font-bold">Uso interno con código de barras</div>
            <div class="text-sm text-slate-300 mt-1">
                Escaneá un producto y confirmá para pasar una unidad del stock de venta al stock de peluquería.
            </div>

            <label class="block text-sm font-semibold text-slate-200 mt-5">
                Código de barras
            </label>
            <input id="scanner_consumo_producto"
                   type="text"
                   autocomplete="off"
                   placeholder="Hacé click acá y escaneá..."
                   data-scan-url="{{ $scanUrl }}"
                   class="mt-2 w-full rounded-2xl border-slate-700 bg-slate-800 text-white text-lg px-4 py-4 focus:border-white focus:ring-white">

            <button type="button"
                    id="activar_scanner_consumo"
                    class="mt-3 w-full rounded-xl border border-slate-600 px-4 py-3 font-semibold hover:bg-slate-800">
                Activar scanner
            </button>
        </div>

        <div class="lg:col-span-7">
            <div id="resultado_scanner_consumo"
                 class="min-h-[235px] h-full rounded-2xl border border-slate-700 bg-slate-800 p-5 flex items-center justify-center">
                <div class="text-center">
                    <div class="text-5xl mb-3">💇🏻‍♀️</div>
                    <div class="text-2xl font-bold">Esperando producto</div>
                    <div class="text-slate-400 mt-2">
                        El escaneo solamente consulta. El stock se modifica al presionar Confirmar consumo.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="flex flex-col gap-3 mb-6">
    <form class="grid grid-cols-1 md:grid-cols-6 gap-3 w-full"
          method="GET"
          action="{{ route('productos.index') }}">
        <div class="md:col-span-2">
            <label class="text-sm font-semibold text-slate-700">Proveedor</label>
            <input id="proveedor_buscar"
                   list="datalist_proveedores"
                   placeholder="Escribí para buscar..."
                   class="mt-1 w-full rounded-xl border-slate-300 focus:border-slate-500 focus:ring-slate-500"
                   value="{{ old('proveedor_buscar', $proveedorTexto) }}">

            <datalist id="datalist_proveedores">
                @foreach($proveedores as $proveedor)
                    <option value="{{ $proveedor->nombre }}"></option>
                @endforeach
            </datalist>

            <input type="hidden"
                   name="proveedor_id"
                   id="proveedor_id"
                   value="{{ $proveedor_id }}">
        </div>

        <div>
            <label class="text-sm font-semibold text-slate-700">Marca</label>
            <input name="marca"
                   value="{{ $marca }}"
                   class="mt-1 w-full rounded-xl border-slate-300 focus:border-slate-500 focus:ring-slate-500"
                   placeholder="Ej: Rigenol">
        </div>

        <div>
            <label class="text-sm font-semibold text-slate-700">Tipo</label>
            <input name="tipo"
                   value="{{ $tipo }}"
                   class="mt-1 w-full rounded-xl border-slate-300 focus:border-slate-500 focus:ring-slate-500"
                   placeholder="Ej: Shampoo">
        </div>

        <div>
            <label class="text-sm font-semibold text-slate-700">Contenido</label>
            <input name="contenido"
                   value="{{ $contenido }}"
                   class="mt-1 w-full rounded-xl border-slate-300 focus:border-slate-500 focus:ring-slate-500"
                   placeholder="Ej: 1L">
        </div>

        <div>
            <label class="text-sm font-semibold text-slate-700">Estado stock</label>
            <select name="stock_estado"
                    class="mt-1 w-full rounded-xl border-slate-300 focus:border-slate-500 focus:ring-slate-500">
                <option value="" {{ $stock_estado === '' ? 'selected' : '' }}>Todos</option>
                <option value="stock_minimo" {{ $stock_estado === 'stock_minimo' ? 'selected' : '' }}>
                    Stock mínimo
                </option>
                <option value="sin_stock" {{ $stock_estado === 'sin_stock' ? 'selected' : '' }}>
                    Sin stock
                </option>
            </select>
        </div>

        <div class="flex gap-2 md:col-span-6">
            <button class="rounded-xl bg-slate-900 text-white px-4 py-2 hover:bg-slate-800 w-full md:w-auto">
                Filtrar
            </button>

            <a href="{{ route('productos.index') }}"
               class="rounded-xl border px-4 py-2 hover:bg-slate-50 w-full md:w-auto text-center">
                Limpiar
            </a>

            <div class="flex-1"></div>

            <a href="{{ route('productos.create') }}"
               class="rounded-xl bg-slate-900 text-white px-4 py-2 hover:bg-slate-800 w-full md:w-auto text-center">
                + Nuevo producto
            </a>
        </div>
    </form>
</div>

<div class="overflow-x-auto rounded-2xl border">
    <table class="min-w-full bg-white">
        <thead class="bg-slate-50 text-slate-700">
        <tr>
            <th class="text-left px-4 py-3 text-sm font-semibold">Proveedor</th>
            <th class="text-left px-4 py-3 text-sm font-semibold">
                {!! sort_link_productos('Marca', 'marca', $sort, $dir) !!}
            </th>
            <th class="text-left px-4 py-3 text-sm font-semibold">
                {!! sort_link_productos('Tipo', 'tipo', $sort, $dir) !!}
            </th>
            <th class="text-left px-4 py-3 text-sm font-semibold">
                {!! sort_link_productos('Contenido', 'contenido', $sort, $dir) !!}
            </th>
            <th class="text-left px-4 py-3 text-sm font-semibold">Código</th>
            <th class="text-left px-4 py-3 text-sm font-semibold">
                {!! sort_link_productos('Stock ventas', 'stock_venta', $sort, $dir) !!}
            </th>
            <th class="text-left px-4 py-3 text-sm font-semibold">
                {!! sort_link_productos('Stock peluquería', 'stock_peluqueria', $sort, $dir) !!}
            </th>
            <th class="text-right px-4 py-3 text-sm font-semibold">Acciones</th>
        </tr>
        </thead>

        <tbody>
        @forelse($productos as $producto)
            @php
                $stockVenta = (int)$producto->stock_venta;
                $stockMinimo = (int)$producto->stock_minimo;
                $rowClass = $stockVenta <= 0
                    ? 'bg-red-50'
                    : ($stockVenta <= $stockMinimo ? 'bg-yellow-50' : '');
            @endphp

            <tr class="border-t hover:bg-slate-50 {{ $rowClass }}">
                <td class="px-4 py-3">{{ $producto->proveedor?->nombre ?? '-' }}</td>
                <td class="px-4 py-3 font-semibold">{{ $producto->marca }}</td>
                <td class="px-4 py-3">{{ $producto->tipo }}</td>
                <td class="px-4 py-3">{{ $producto->contenido }}</td>
                <td class="px-4 py-3 text-sm text-slate-600">
                    {{ $producto->codigo_barra ?: '-' }}
                </td>
                <td class="px-4 py-3 font-semibold">
                    @if($stockVenta <= 0)
                        <span class="inline-flex whitespace-nowrap px-2 py-1 rounded-lg bg-red-100 text-red-700 border border-red-200 text-sm">
                            {{ $stockVenta }} · Sin stock
                        </span>
                    @elseif($stockVenta <= $stockMinimo)
                        <span class="inline-flex whitespace-nowrap px-2 py-1 rounded-lg bg-yellow-100 text-yellow-800 border border-yellow-200 text-sm">
                            {{ $stockVenta }} · Al mínimo
                        </span>
                    @else
                        <span class="inline-flex whitespace-nowrap px-2 py-1 rounded-lg bg-slate-50 text-slate-700 border border-slate-200 text-sm">
                            {{ $stockVenta }}
                        </span>
                    @endif
                </td>
                <td class="px-4 py-3 font-semibold">
                    <div class="flex items-center gap-2">
                        <span>{{ (int)$producto->stock_peluqueria }}</span>

                        @if((int)$producto->stock_peluqueria > 0)
                            <form method="POST" action="{{ route('productos.usarPeluqueria', $producto) }}">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="return_to" value="{{ $returnTo }}">
                                <button class="rounded-lg border px-2 py-1 hover:bg-slate-50"
                                        title="Descontar 1 del stock de peluquería">
                                    ⬇️
                                </button>
                            </form>
                        @endif
                    </div>
                </td>
                <td class="px-4 py-3">
                    <div class="flex justify-end gap-2 flex-wrap">
                        <form method="POST"
                              action="{{ route('productos.consumo', $producto) }}"
                              onsubmit="return confirm('¿Registrar una unidad para uso de la peluquería?');">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="return_to" value="{{ $returnTo }}">
                            <button class="rounded-lg border px-3 py-1 hover:bg-slate-50"
                                    title="Pasar 1 unidad a stock peluquería">
                                💇🏻‍♀️
                            </button>
                        </form>

                        <a href="{{ route('productos.edit', ['producto' => $producto, 'return_to' => $returnTo]) }}"
                           class="rounded-lg border px-3 py-1 hover:bg-white"
                           title="Editar">
                            ✏️
                        </a>

                        <form method="POST"
                              action="{{ route('productos.destroy', $producto) }}"
                              onsubmit="return confirm('¿Eliminar este producto?');">
                            @csrf
                            @method('DELETE')
                            <input type="hidden" name="return_to" value="{{ $returnTo }}">
                            <button class="rounded-lg border px-3 py-1 hover:bg-white" title="Eliminar">
                                🗑️
                            </button>
                        </form>
                    </div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="8" class="px-4 py-8 text-center text-slate-500">
                    No hay productos cargados.
                </td>
            </tr>
        @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">
    {{ $productos->links() }}
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const proveedoresMap = @json($proveedoresMap);
    const proveedorInput = document.getElementById('proveedor_buscar');
    const proveedorHidden = document.getElementById('proveedor_id');

    const setProveedorId = () => {
        const valor = (proveedorInput.value || '').trim();
        proveedorHidden.value = proveedoresMap[valor] ? String(proveedoresMap[valor]) : '';
    };

    proveedorInput.addEventListener('change', setProveedorId);
    proveedorInput.addEventListener('blur', setProveedorId);

    const scanner = document.getElementById('scanner_consumo_producto');
    const activarScanner = document.getElementById('activar_scanner_consumo');
    const resultado = document.getElementById('resultado_scanner_consumo');
    const returnTo = @json($returnTo);

    const escapeHtml = texto => String(texto ?? '')
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;');

    const consultar = async codigo => {
        const codigoLimpio = String(codigo || '').trim();
        if (!codigoLimpio) return;

        resultado.innerHTML = `
            <div class="text-center">
                <div class="text-4xl mb-3">⏳</div>
                <div class="text-xl font-bold">Buscando producto...</div>
            </div>
        `;

        const url = scanner.dataset.scanUrl.replace('__CODIGO__', encodeURIComponent(codigoLimpio));

        try {
            const response = await fetch(url, {
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });

            const data = await response.json();

            if (!response.ok) {
                throw new Error(data.message || 'Producto no encontrado.');
            }

            resultado.innerHTML = `
                <div class="w-full">
                    <div class="text-sm uppercase tracking-[0.20em] text-slate-400 font-semibold">
                        Producto encontrado
                    </div>
                    <div class="mt-2 text-3xl font-extrabold text-white">
                        ${escapeHtml(data.nombre)}
                    </div>
                    <div class="mt-3 flex flex-wrap gap-2 text-sm">
                        <span class="rounded-full bg-slate-900 border border-slate-700 px-3 py-1 text-slate-300">
                            Proveedor: ${escapeHtml(data.proveedor || '-')}
                        </span>
                        <span class="rounded-full bg-slate-900 border border-slate-700 px-3 py-1 text-slate-300">
                            Stock venta: ${escapeHtml(data.stock_venta)}
                        </span>
                        <span class="rounded-full bg-slate-900 border border-slate-700 px-3 py-1 text-slate-300">
                            Stock peluquería: ${escapeHtml(data.stock_peluqueria)}
                        </span>
                    </div>

                    ${data.puede_consumir ? `
                        <form method="POST"
                              action="${escapeHtml(data.consumo_url)}"
                              class="mt-6"
                              onsubmit="return confirm('¿Confirmar el consumo de una unidad para la peluquería?');">
                            <input type="hidden" name="_token" value="{{ csrf_token() }}">
                            <input type="hidden" name="_method" value="PATCH">
                            <input type="hidden" name="return_to" value="${escapeHtml(returnTo)}">
                            <button class="w-full rounded-xl bg-white text-slate-900 px-4 py-3 font-bold hover:bg-slate-100">
                                Confirmar consumo de 1 unidad
                            </button>
                        </form>
                    ` : `
                        <div class="mt-6 rounded-xl border border-red-400/40 bg-red-400/10 px-4 py-3 text-red-200">
                            Este producto no tiene stock de venta disponible.
                        </div>
                    `}
                </div>
            `;
        } catch (error) {
            resultado.innerHTML = `
                <div class="text-center">
                    <div class="text-5xl mb-3">⚠️</div>
                    <div class="text-2xl font-bold text-red-300">Producto no encontrado</div>
                    <div class="text-slate-300 mt-2">${escapeHtml(error.message)}</div>
                    <div class="text-slate-400 mt-1">Código: ${escapeHtml(codigoLimpio)}</div>
                </div>
            `;
        }
    };

    scanner.addEventListener('keydown', function (event) {
        if (event.key === 'Enter') {
            event.preventDefault();
            consultar(this.value);
            this.value = '';
        }
    });

    scanner.addEventListener('change', function () {
        consultar(this.value);
        this.value = '';
    });

    activarScanner.addEventListener('click', () => scanner.focus());
    scanner.focus();
});
</script>

@endsection
