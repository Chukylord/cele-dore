@extends('layouts.admin')

@section('title', 'Compras - FN Peluquería')
@section('h1', 'Compras')
@section('sub', 'Compras agrupadas por lote.')

@section('content')

    @if(session('ok'))
        <div class="mb-4 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-green-800">
            {{ session('ok') }}
        </div>
    @endif

    @php
        $provMap = $proveedores->pluck('id','nombre');

        $prodMap = [];
        foreach($productos as $pr){
            $label = trim(($pr->marca.' - '.$pr->tipo.' '.$pr->contenido).' ('.$pr->proveedor?->nombre.')');
            $prodMap[$label] = $pr->id;
        }

        $provTexto = '';
        if(!empty($proveedor_id)){
            $p = $proveedores->firstWhere('id', (int)$proveedor_id);
            $provTexto = $p?->nombre ?? '';
        }

        $prodTexto = '';
        if(!empty($producto_id)){
            $p = $productos->firstWhere('id', (int)$producto_id);
            if($p){
                $prodTexto = trim(($p->marca.' - '.$p->tipo.' '.$p->contenido).' ('.$p->proveedor?->nombre.')');
            }
        }
    @endphp

    <div class="fn-toolbar flex flex-col gap-3 mb-6">
        <form class="grid grid-cols-1 md:grid-cols-6 gap-3 w-full" method="GET" action="{{ route('compras.index') }}">

            <div class="md:col-span-2">
                <label class="text-sm font-semibold text-slate-700">Proveedor</label>

                <input id="proveedor_buscar"
                       list="dl_proveedores"
                       placeholder="Escribí para buscar..."
                       class="mt-1 w-full rounded-xl border-slate-300 focus:border-slate-500 focus:ring-slate-500"
                       value="{{ old('proveedor_buscar', $provTexto) }}">

                <datalist id="dl_proveedores">
                    @foreach($proveedores as $p)
                        <option value="{{ $p->nombre }}"></option>
                    @endforeach
                </datalist>

                <input type="hidden" name="proveedor_id" id="proveedor_id" value="{{ $proveedor_id }}">
            </div>

            <div class="md:col-span-2">
                <label class="text-sm font-semibold text-slate-700">Producto</label>

                <input id="producto_buscar"
                       list="dl_productos"
                       placeholder="Escribí para buscar..."
                       class="mt-1 w-full rounded-xl border-slate-300 focus:border-slate-500 focus:ring-slate-500"
                       value="{{ old('producto_buscar', $prodTexto) }}">

                <datalist id="dl_productos">
                    @foreach($productos as $pr)
                        <option value="{{ trim(($pr->marca.' - '.$pr->tipo.' '.$pr->contenido).' ('.$pr->proveedor?->nombre.')') }}"></option>
                    @endforeach
                </datalist>

                <input type="hidden" name="producto_id" id="producto_id" value="{{ $producto_id }}">
            </div>

            <div>
                <label class="text-sm font-semibold text-slate-700">Desde</label>
                <input type="date" name="desde" value="{{ $desde }}"
                       class="mt-1 w-full rounded-xl border-slate-300 focus:border-slate-500 focus:ring-slate-500" />
            </div>

            <div>
                <label class="text-sm font-semibold text-slate-700">Hasta</label>
                <input type="date" name="hasta" value="{{ $hasta }}"
                       class="mt-1 w-full rounded-xl border-slate-300 focus:border-slate-500 focus:ring-slate-500" />
            </div>

            <div class="flex gap-2 md:col-span-6">
                <button class="fn-primary-action w-full md:w-auto">
                    Filtrar
                </button>

                <a href="{{ route('compras.index') }}"
                   class="fn-secondary-action w-full md:w-auto text-center">
                    Limpiar
                </a>

                <div class="flex-1"></div>

                <a href="{{ route('compras.create') }}"
                   class="fn-primary-action text-center w-full md:w-auto">
                    + Nueva Compra
                </a>
            </div>
        </form>
    </div>

    <div class="fn-table-shell overflow-x-auto">
        <table class="min-w-full bg-white">
            <thead class="bg-slate-50 text-slate-700">
            <tr>
                <th class="text-left px-4 py-3 text-sm font-semibold">Lote</th>
                <th class="text-left px-4 py-3 text-sm font-semibold">Fecha</th>
                <th class="text-left px-4 py-3 text-sm font-semibold">Items</th>
                <th class="text-left px-4 py-3 text-sm font-semibold">Proveedores</th>
                <th class="text-left px-4 py-3 text-sm font-semibold">Total compra</th>
                <th class="text-right px-4 py-3 text-sm font-semibold">Acciones</th>
            </tr>
            </thead>

            <tbody>
            @forelse($lotes as $lote)
                @php
                    $compras = $lote->compras ?? collect();
                    $proveedoresUnicos = $compras->map(fn($c) => $c->proveedor?->nombre)->filter()->unique()->values();
                    $proveedoresTxt = $proveedoresUnicos->count() ? $proveedoresUnicos->implode(', ') : '-';
                    $totalLote = (float)$lote->monto_total;
                @endphp

                <tr class="border-t hover:bg-slate-50">
                    <td class="px-4 py-3 font-semibold">#{{ $lote->id }}</td>
                    <td class="px-4 py-3">{{ \Carbon\Carbon::parse($lote->fecha)->format('d/m/Y') }}</td>
                    <td class="px-4 py-3">{{ $lote->compras_count }}</td>
                    <td class="px-4 py-3">{{ $proveedoresTxt }}</td>
                    <td class="px-4 py-3 font-bold">${{ number_format($totalLote, 2, ',', '.') }}</td>

                    <td class="px-4 py-3">
                        <div class="flex items-center justify-end gap-2">
                            <a href="{{ route('compras.lotes.show', $lote) }}"
                               class="fn-icon-action" title="Ver detalle">
                                🔎
                            </a>

                            <a href="{{ route('compras.lotes.edit', $lote) }}"
                               class="fn-icon-action" title="Editar lote">
                                ✏️
                            </a>

                            <form method="POST" action="{{ route('compras.lotes.destroy', $lote) }}"
                                  onsubmit="return confirm('¿Eliminar este lote? Se revertirá el stock.');">
                                @csrf
                                @method('DELETE')
                                <button class="fn-icon-action fn-icon-action-danger" title="Eliminar lote">
                                    🗑️
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="px-4 py-8 text-center text-slate-500">
                        No hay compras registradas.
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $lotes->links() }}
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const PROV_MAP = @json($provMap);
            const PROD_MAP = @json($prodMap);

            const provBuscar = document.getElementById('proveedor_buscar');
            const provId = document.getElementById('proveedor_id');

            const prodBuscar = document.getElementById('producto_buscar');
            const prodId = document.getElementById('producto_id');

            function setProv(){
                const v = (provBuscar.value || '').trim();
                provId.value = PROV_MAP[v] ? String(PROV_MAP[v]) : '';
            }

            function setProd(){
                const v = (prodBuscar.value || '').trim();
                prodId.value = PROD_MAP[v] ? String(PROD_MAP[v]) : '';
            }

            provBuscar.addEventListener('change', setProv);
            provBuscar.addEventListener('blur', setProv);

            prodBuscar.addEventListener('change', setProd);
            prodBuscar.addEventListener('blur', setProd);

            setProv();
            setProd();
        });
    </script>

@endsection
