@extends('layouts.admin')

@section('title', 'Compras - Peluquería TOP')
@section('h1', 'Compras')
@section('sub', 'Compras agrupadas por lote (carga múltiple).')

@section('content')

    @if(session('ok'))
        <div class="mb-4 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-green-800">
            {{ session('ok') }}
        </div>
    @endif

    @php
        // maps para datalist -> id
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

    <div class="flex flex-col gap-3 mb-6">
        <form class="grid grid-cols-1 md:grid-cols-6 gap-3 w-full" method="GET" action="{{ route('compras.index') }}">

            {{-- Proveedor datalist --}}
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

            {{-- Producto datalist --}}
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
                <button class="rounded-xl bg-slate-900 text-white px-4 py-2 hover:bg-slate-800 w-full md:w-auto">
                    Filtrar
                </button>

                <a href="{{ route('compras.index') }}"
                   class="rounded-xl border px-4 py-2 hover:bg-slate-50 w-full md:w-auto text-center">
                    Limpiar
                </a>

                <div class="flex-1"></div>

                <a href="{{ route('compras.create') }}"
                   class="rounded-xl bg-slate-900 text-white px-4 py-2 hover:bg-slate-800 text-center w-full md:w-auto">
                    + Nueva Compra
                </a>
            </div>
        </form>
    </div>

    <div class="overflow-x-auto rounded-2xl border">
        <table class="min-w-full bg-white">
            <thead class="bg-slate-50 text-slate-700">
            <tr>
                <th class="text-left px-4 py-3 text-sm font-semibold">Lote</th>
                <th class="text-left px-4 py-3 text-sm font-semibold">Fecha</th>
                <th class="text-left px-4 py-3 text-sm font-semibold">Items</th>
                <th class="text-left px-4 py-3 text-sm font-semibold">Proveedores</th>
                <th class="text-left px-4 py-3 text-sm font-semibold">Total</th>
                <th class="text-left px-4 py-3 text-sm font-semibold">Entregado</th>
                <th class="text-left px-4 py-3 text-sm font-semibold">Saldo</th>
                <th class="text-left px-4 py-3 text-sm font-semibold">Estado</th>
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
                    $entregado = (float)$lote->monto_pagado;
                    $saldo = max($totalLote - $entregado, 0);
                @endphp

                <tr class="border-t hover:bg-slate-50">
                    <td class="px-4 py-3 font-semibold">#{{ $lote->id }}</td>
                    <td class="px-4 py-3">{{ \Carbon\Carbon::parse($lote->fecha)->format('d/m/Y') }}</td>
                    <td class="px-4 py-3">{{ $lote->compras_count }}</td>
                    <td class="px-4 py-3">{{ $proveedoresTxt }}</td>
                    <td class="px-4 py-3 font-bold">${{ number_format($totalLote, 2, ',', '.') }}</td>
                    <td class="px-4 py-3">${{ number_format($entregado, 2, ',', '.') }}</td>
                    <td class="px-4 py-3">${{ number_format($saldo, 2, ',', '.') }}</td>

                    <td class="px-4 py-3">
                        @if($lote->estado_pago === 'pagado')
                            <span class="px-2 py-1 rounded-lg bg-green-50 text-green-700 border border-green-200 text-sm">
                                Pagado
                            </span>
                        @elseif($lote->estado_pago === 'parcial')
                            <span class="px-2 py-1 rounded-lg bg-yellow-50 text-yellow-800 border border-yellow-200 text-sm">
                                Parcial
                            </span>
                        @else
                            <span class="px-2 py-1 rounded-lg bg-red-50 text-red-700 border border-red-200 text-sm">
                                Pendiente
                            </span>
                        @endif
                    </td>

                    <td class="px-4 py-3">
                        <div class="flex items-center justify-end gap-2">
                            <a href="{{ route('compras.lotes.show', $lote) }}"
                            class="rounded-lg border px-3 py-1 hover:bg-white" title="Ver detalle">
                                🔎
                            </a>

                            <a href="{{ route('compras.lotes.edit', $lote) }}"
                            class="rounded-lg border px-3 py-1 hover:bg-white" title="Editar lote">
                                ✏️
                            </a>

                            <form method="POST" action="{{ route('compras.lotes.destroy', $lote) }}"
                                onsubmit="return confirm('¿Eliminar este lote? Se revertirá el stock.');">
                                @csrf
                                @method('DELETE')
                                <button class="rounded-lg border px-3 py-1 hover:bg-white" title="Eliminar lote">
                                    🗑️
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" class="px-4 py-8 text-center text-slate-500">
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

            // Por las dudas, setear al cargar
            setProv(); setProd();
        });
    </script>

@endsection