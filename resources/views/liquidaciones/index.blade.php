@extends('layouts.admin')

@section('title', 'Liquidaciones - Peluquería TOP')
@section('h1', 'Liquidaciones')
@section('sub', 'Pagos registrados a colaboradoras.')

@section('content')

    @if(session('ok'))
        <div class="mb-4 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-green-800">
            {{ session('ok') }}
        </div>
    @endif

    <div class="flex flex-col gap-3 mb-6">
        <form class="grid grid-cols-1 md:grid-cols-5 gap-3 w-full" method="GET" action="{{ route('liquidaciones.index') }}">
            <div>
                <label class="text-sm font-semibold text-slate-700">Desde</label>
                <input type="date" name="desde" value="{{ $desde }}"
                       class="mt-1 w-full rounded-xl border-slate-300 focus:border-slate-500 focus:ring-slate-500">
            </div>

            <div>
                <label class="text-sm font-semibold text-slate-700">Hasta</label>
                <input type="date" name="hasta" value="{{ $hasta }}"
                       class="mt-1 w-full rounded-xl border-slate-300 focus:border-slate-500 focus:ring-slate-500">
            </div>

            <div class="md:col-span-2">
                <label class="text-sm font-semibold text-slate-700">Colaboradora</label>
                <select name="colaboradora_id"
                        class="mt-1 w-full rounded-xl border-slate-300 focus:border-slate-500 focus:ring-slate-500">
                    <option value="">Todas</option>
                    @foreach($colaboradoras as $c)
                        <option value="{{ $c->id }}" {{ (string)$colaboradora_id === (string)$c->id ? 'selected' : '' }}>
                            {{ $c->apellido }} {{ $c->nombre }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="flex gap-2">
                <button class="mt-6 w-full rounded-xl bg-slate-900 text-white px-4 py-2 hover:bg-slate-800">
                    Filtrar
                </button>

                <a href="{{ route('liquidaciones.index') }}"
                   class="mt-6 w-full text-center rounded-xl border px-4 py-2 hover:bg-slate-50">
                    Limpiar
                </a>
            </div>
        </form>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="rounded-2xl border bg-slate-900 text-white p-4">
                <div class="text-sm text-slate-300">Total de liquidaciones del filtro</div>
                <div class="text-3xl font-extrabold mt-1">
                    ${{ number_format((float)$totalFiltro, 2, ',', '.') }}
                </div>
                <div class="text-xs text-slate-300 mt-1">
                    Este total contempla todas las liquidaciones del filtro, no solo las de esta página.
                </div>
            </div>

            <div class="flex justify-end items-center">
                <a href="{{ route('liquidaciones.create') }}"
                   class="rounded-xl bg-slate-900 text-white px-4 py-2 hover:bg-slate-800">
                    + Nueva Liquidación
                </a>
            </div>
        </div>
    </div>

    <div class="overflow-x-auto rounded-2xl border">
        <table class="min-w-full bg-white">
            <thead class="bg-slate-50 text-slate-700">
            <tr>
                <th class="text-left px-4 py-3 text-sm font-semibold">Fecha pago</th>
                <th class="text-left px-4 py-3 text-sm font-semibold">Colaboradora</th>
                <th class="text-left px-4 py-3 text-sm font-semibold">Horas normales</th>
                <th class="text-left px-4 py-3 text-sm font-semibold">Horas extras</th>
                <th class="text-left px-4 py-3 text-sm font-semibold">Valor hora</th>
                <th class="text-left px-4 py-3 text-sm font-semibold">Comisión</th>
                <th class="text-left px-4 py-3 text-sm font-semibold">Prod. a costo</th>
                <th class="text-left px-4 py-3 text-sm font-semibold">Total pagado</th>
                <th class="text-left px-4 py-3 text-sm font-semibold">Observaciones</th>
                <th class="text-right px-4 py-3 text-sm font-semibold">Acciones</th>
            </tr>
            </thead>

            <tbody>
            @forelse($liquidaciones as $l)
                <tr class="border-t hover:bg-slate-50">
                    <td class="px-4 py-3">
                        {{ $l->fecha_pago ? \Carbon\Carbon::parse($l->fecha_pago)->format('d/m/Y') : '-' }}
                    </td>

                    <td class="px-4 py-3">
                        {{ $l->colaboradora ? ($l->colaboradora->apellido . ' ' . $l->colaboradora->nombre) : '-' }}
                    </td>

                    <td class="px-4 py-3">
                        {{ number_format((float)$l->horas_normales, 2, ',', '.') }}
                    </td>

                    <td class="px-4 py-3">
                        {{ number_format((float)$l->horas_extras, 2, ',', '.') }}
                    </td>

                    <td class="px-4 py-3">
                        ${{ number_format((float)$l->valor_hora, 2, ',', '.') }}
                    </td>

                    <td class="px-4 py-3">
                        ${{ number_format((float)$l->monto_comision, 2, ',', '.') }}
                    </td>

                    <td class="px-4 py-3">
                        ${{ number_format((float)$l->monto_productos_costo, 2, ',', '.') }}
                    </td>

                    <td class="px-4 py-3 font-semibold">
                        ${{ number_format((float)$l->total_pagado, 2, ',', '.') }}
                    </td>

                    <td class="px-4 py-3">
                        {{ $l->observaciones ?: '-' }}
                    </td>

                    <td class="px-4 py-3">
                        <div class="flex justify-end gap-2">
                            <a href="{{ route('liquidaciones.show', $l) }}"
                               class="rounded-lg border px-3 py-1 hover:bg-white"
                               title="Ver">
                                🔎
                            </a>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="10" class="px-4 py-8 text-center text-slate-500">
                        No hay liquidaciones registradas.
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4 rounded-2xl border bg-white p-4 flex flex-col md:flex-row md:items-center md:justify-between gap-3">
        <div>
            <div class="text-sm text-slate-600">Total general del filtro</div>
            <div class="text-2xl font-extrabold text-slate-900">
                ${{ number_format((float)$totalFiltro, 2, ',', '.') }}
            </div>
        </div>

        <div class="text-sm text-slate-500">
            Este total incluye todas las liquidaciones encontradas, aunque estén en otras páginas.
        </div>
    </div>

    <div class="mt-4">
        {{ $liquidaciones->links() }}
    </div>

@endsection