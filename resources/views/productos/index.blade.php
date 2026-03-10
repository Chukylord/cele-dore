@extends('layouts.admin')

@section('title', 'Productos - Peluquería TOP')
@section('h1', 'Productos')
@section('sub', 'Gestión de productos, stock y consumo de peluquería.')

@section('content')

    @if(session('ok'))
        <div class="mb-4 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-green-800">
            {{ session('ok') }}
        </div>
    @endif

    @php
        function sort_link($label, $field, $sort, $dir) {
            $isActive = $sort === $field;
            $nextDir = ($isActive && $dir === 'asc') ? 'desc' : 'asc';
            $arrow = $isActive ? ($dir === 'asc' ? ' ↑' : ' ↓') : '';
            $url = request()->fullUrlWithQuery(['sort' => $field, 'dir' => $nextDir]);
            return '<a class="hover:underline" href="'.$url.'">'.$label.$arrow.'</a>';
        }

        $proveedoresMap = $proveedores->pluck('id','nombre');
        $proveedorTexto = '';
        if (!empty($proveedor_id)) {
            $prov = $proveedores->firstWhere('id', (int)$proveedor_id);
            $proveedorTexto = $prov ? $prov->nombre : '';
        }
    @endphp

    {{-- filtros --}}
    <div class="flex flex-col gap-3 mb-6">
        <form class="grid grid-cols-1 md:grid-cols-5 gap-3 w-full" method="GET" action="{{ route('productos.index') }}">

            <div class="md:col-span-2">
                <label class="text-sm font-semibold text-slate-700">Proveedor</label>

                <input id="proveedor_buscar"
                       list="datalist_proveedores"
                       placeholder="Escribí para buscar..."
                       class="mt-1 w-full rounded-xl border-slate-300 focus:border-slate-500 focus:ring-slate-500"
                       value="{{ old('proveedor_buscar', $proveedorTexto) }}">

                <datalist id="datalist_proveedores">
                    @foreach($proveedores as $pr)
                        <option value="{{ $pr->nombre }}"></option>
                    @endforeach
                </datalist>

                <input type="hidden" name="proveedor_id" id="proveedor_id" value="{{ $proveedor_id }}">
            </div>

            <div>
                <label class="text-sm font-semibold text-slate-700">Marca</label>
                <input name="marca" value="{{ $marca ?? '' }}"
                       class="mt-1 w-full rounded-xl border-slate-300 focus:border-slate-500 focus:ring-slate-500"
                       placeholder="Ej: Rigenol">
            </div>

            <div>
                <label class="text-sm font-semibold text-slate-700">Tipo</label>
                <input name="tipo" value="{{ $tipo ?? '' }}"
                       class="mt-1 w-full rounded-xl border-slate-300 focus:border-slate-500 focus:ring-slate-500"
                       placeholder="Ej: Shampoo">
            </div>

            <div>
                <label class="text-sm font-semibold text-slate-700">Contenido</label>
                <input name="contenido" value="{{ $contenido ?? '' }}"
                       class="mt-1 w-full rounded-xl border-slate-300 focus:border-slate-500 focus:ring-slate-500"
                       placeholder="Ej: 1L">
            </div>

            <div class="flex gap-2 md:col-span-5">
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
                    + Nuevo Producto
                </a>
            </div>
        </form>
    </div>

    <div class="overflow-x-auto rounded-2xl border">
        <table class="min-w-full bg-white">
            <thead class="bg-slate-50 text-slate-700">
            <tr>
                <th class="text-left px-4 py-3 text-sm font-semibold">Proveedor</th>
                <th class="text-left px-4 py-3 text-sm font-semibold">{!! sort_link('Marca', 'marca', $sort ?? 'created_at', $dir ?? 'desc') !!}</th>
                <th class="text-left px-4 py-3 text-sm font-semibold">{!! sort_link('Tipo', 'tipo', $sort ?? 'created_at', $dir ?? 'desc') !!}</th>
                <th class="text-left px-4 py-3 text-sm font-semibold">{!! sort_link('Contenido', 'contenido', $sort ?? 'created_at', $dir ?? 'desc') !!}</th>

                <th class="text-left px-4 py-3 text-sm font-semibold">{!! sort_link('Stock ventas', 'stock_venta', $sort ?? 'created_at', $dir ?? 'desc') !!}</th>
                <th class="text-left px-4 py-3 text-sm font-semibold">{!! sort_link('Stock mínimo', 'stock_minimo', $sort ?? 'created_at', $dir ?? 'desc') !!}</th>
                <th class="text-left px-4 py-3 text-sm font-semibold">{!! sort_link('Stock peluquería', 'stock_peluqueria', $sort ?? 'created_at', $dir ?? 'desc') !!}</th>

                <th class="text-right px-4 py-3 text-sm font-semibold">Acciones</th>
            </tr>
            </thead>

            <tbody>
            @forelse($productos as $p)
                @php
                    $sv = (int)$p->stock_venta;
                    $min = (int)$p->stock_minimo;

                    $rowClass = '';
                    if ($sv <= 0) {
                        $rowClass = 'bg-red-50';
                    } elseif ($sv <= $min) {
                        $rowClass = 'bg-yellow-50';
                    }
                @endphp

                <tr class="border-t hover:bg-slate-50 {{ $rowClass }}">
                    <td class="px-4 py-3">{{ $p->proveedor?->nombre ?? '-' }}</td>
                    <td class="px-4 py-3">{{ $p->marca }}</td>
                    <td class="px-4 py-3">{{ $p->tipo }}</td>
                    <td class="px-4 py-3">{{ $p->contenido }}</td>

                    <td class="px-4 py-3 font-semibold">
                        @if($sv <= 0)
                            <span class="px-2 py-1 rounded-lg bg-red-100 text-red-700 border border-red-200 text-sm">
                                {{ $sv }} (Sin stock)
                            </span>
                        @elseif($sv <= $min)
                            <span class="px-2 py-1 rounded-lg bg-yellow-100 text-yellow-800 border border-yellow-200 text-sm">
                                {{ $sv }} (Al mínimo)
                            </span>
                        @else
                            <span class="px-2 py-1 rounded-lg bg-slate-50 text-slate-700 border border-slate-200 text-sm">
                                {{ $sv }}
                            </span>
                        @endif
                    </td>

                    <td class="px-4 py-3 font-semibold">{{ $min }}</td>

                    {{-- Stock peluquería + botón ⬇️ solo si > 0 --}}
                    <td class="px-4 py-3 font-semibold">
                        <div class="flex items-center gap-2">
                            <span>{{ $p->stock_peluqueria }}</span>

                            @if((int)$p->stock_peluqueria > 0)
                                <form method="POST" action="{{ route('productos.usarPeluqueria', $p) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button class="rounded-lg border px-2 py-1 hover:bg-slate-50"
                                            title="Descontar 1 del stock peluquería">
                                        ⬇️
                                    </button>
                                </form>
                            @endif
                        </div>
                    </td>

                    <td class="px-4 py-3">
                        <div class="flex justify-end gap-2 flex-wrap">

                            {{-- Consumo peluquería (pasa 1 de ventas -> peluquería y registra egreso) --}}
                            <form method="POST" action="{{ route('productos.consumo', $p) }}">
                                @csrf
                                @method('PATCH')
                                <button class="rounded-lg border px-3 py-1 hover:bg-slate-50"
                                        title="Consumo peluquería (pasa 1 a stock peluquería)">
                                    💇🏻‍♀️
                                </button>
                            </form>

                            <a href="{{ route('productos.edit', $p) }}"
                               class="rounded-lg border px-3 py-1 hover:bg-white" title="Editar">
                                ✏️
                            </a>

                            <form method="POST" action="{{ route('productos.destroy', $p) }}"
                                  onsubmit="return confirm('¿Eliminar este producto?');">
                                @csrf
                                @method('DELETE')
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
            const map = @json($proveedoresMap);
            const inp = document.getElementById('proveedor_buscar');
            const hid = document.getElementById('proveedor_id');

            function setId() {
                const v = (inp.value || '').trim();
                hid.value = map[v] ? String(map[v]) : '';
            }

            inp.addEventListener('change', setId);
            inp.addEventListener('blur', setId);
        });
    </script>

@endsection