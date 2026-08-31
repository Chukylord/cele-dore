@extends('layouts.admin')

@section('title', 'Liquidaciones - FN Peluquería')
@section('h1', 'Liquidaciones')
@section('sub', 'Pagos registrados a colaboradoras y cálculo de mejor mes para aguinaldo.')

@section('content')

    @if(session('ok'))
        <div class="mb-4 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-green-800">
            {{ session('ok') }}
        </div>
    @endif

    {{-- FILTROS --}}
    <div class="fn-toolbar flex flex-col gap-3 mb-6">
        <form class="grid grid-cols-1 md:grid-cols-5 gap-3 w-full" method="GET" action="{{ route('liquidaciones.index') }}">
            <input type="hidden" name="sac_anio" value="{{ $sacAnio }}">
            <input type="hidden" name="sac_semestre" value="{{ $sacSemestre }}">
            <input type="hidden" name="sac_colaboradora_id" value="{{ $sacColaboradoraId }}">

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
                <button class="fn-primary-action mt-6 w-full">
                    Filtrar
                </button>

                <a href="{{ route('liquidaciones.index') }}"
                   class="fn-secondary-action mt-6 w-full text-center">
                    Limpiar
                </a>
            </div>
        </form>

        <div class="flex justify-end">
            <a href="{{ route('liquidaciones.create') }}"
               class="fn-primary-action text-center">
                + Nueva Liquidación
            </a>
        </div>
    </div>

    {{-- MEJOR MES / AGUINALDO --}}
    <div class="fn-section-card mb-6">
        <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4 mb-4">
            <div>
                <div class="text-xl font-bold text-slate-900">Mejor mes / Aguinaldo</div>
                <div class="text-sm text-slate-600 mt-1">
                    Para el aguinaldo se toma horas + comisión. No se descuentan productos a costo.
                </div>
            </div>
        </div>

        <form method="GET" action="{{ route('liquidaciones.index') }}" class="grid grid-cols-1 md:grid-cols-5 gap-3 mb-5">
            <input type="hidden" name="desde" value="{{ $desde }}">
            <input type="hidden" name="hasta" value="{{ $hasta }}">
            <input type="hidden" name="colaboradora_id" value="{{ $colaboradora_id }}">

            <div>
                <label class="text-sm font-semibold text-slate-700">Año</label>
                <input type="number"
                       name="sac_anio"
                       value="{{ $sacAnio }}"
                       min="2000"
                       max="2100"
                       class="mt-1 w-full rounded-xl border-slate-300 focus:border-slate-500 focus:ring-slate-500">
            </div>

            <div>
                <label class="text-sm font-semibold text-slate-700">Semestre</label>
                <select name="sac_semestre"
                        class="mt-1 w-full rounded-xl border-slate-300 focus:border-slate-500 focus:ring-slate-500">
                    <option value="1" {{ (int)$sacSemestre === 1 ? 'selected' : '' }}>Enero - Junio</option>
                    <option value="2" {{ (int)$sacSemestre === 2 ? 'selected' : '' }}>Julio - Diciembre</option>
                </select>
            </div>

            <div class="md:col-span-2">
                <label class="text-sm font-semibold text-slate-700">Colaboradora</label>
                <select name="sac_colaboradora_id"
                        class="mt-1 w-full rounded-xl border-slate-300 focus:border-slate-500 focus:ring-slate-500">
                    <option value="">Todas</option>
                    @foreach($colaboradoras as $c)
                        <option value="{{ $c->id }}" {{ (string)$sacColaboradoraId === (string)$c->id ? 'selected' : '' }}>
                            {{ $c->apellido }} {{ $c->nombre }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <button class="fn-primary-action mt-6 w-full">
                    Calcular
                </button>
            </div>
        </form>

        <div class="fn-table-shell overflow-x-auto">
            <table class="min-w-full bg-white">
                <thead class="bg-slate-50 text-slate-700">
                <tr>
                    <th class="text-left px-4 py-3 text-sm font-semibold">Colaboradora</th>

                    @if(!empty($aguinaldoResumen[0]['meses']))
                        @foreach($aguinaldoResumen[0]['meses'] as $mes)
                            <th class="text-right px-4 py-3 text-sm font-semibold">
                                {{ $mes['nombre'] }}
                            </th>
                        @endforeach
                    @endif

                    <th class="text-left px-4 py-3 text-sm font-semibold">Mejor mes</th>
                    <th class="text-right px-4 py-3 text-sm font-semibold">Base aguinaldo</th>
                    <th class="text-right px-4 py-3 text-sm font-semibold">Aguinaldo sugerido</th>
                </tr>
                </thead>

                <tbody>
                @forelse($aguinaldoResumen as $fila)
                    <tr class="border-t hover:bg-slate-50">
                        <td class="px-4 py-3 font-semibold whitespace-nowrap">
                            {{ $fila['colaboradora']->apellido }} {{ $fila['colaboradora']->nombre }}
                        </td>

                        @foreach($fila['meses'] as $mes)
                            <td class="px-4 py-3 text-right {{ $fila['mejor_mes_numero'] === $mes['mes'] && $mes['total'] > 0 ? 'bg-green-50 text-green-800 font-bold' : '' }}">
                                ${{ number_format((float)$mes['total'], 2, ',', '.') }}
                            </td>
                        @endforeach

                        <td class="px-4 py-3">
                            @if((float)$fila['mejor_mes_total'] > 0)
                                <span class="rounded-full bg-green-100 text-green-700 px-3 py-1 text-xs font-semibold">
                                    {{ $fila['mejor_mes_nombre'] }}
                                </span>
                            @else
                                -
                            @endif
                        </td>

                        <td class="px-4 py-3 text-right font-bold">
                            ${{ number_format((float)$fila['mejor_mes_total'], 2, ',', '.') }}
                        </td>

                        <td class="px-4 py-3 text-right font-extrabold text-slate-900">
                            ${{ number_format((float)$fila['aguinaldo_sugerido'], 2, ',', '.') }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="px-4 py-8 text-center text-slate-500">
                            No hay colaboradoras para calcular.
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3 text-sm text-slate-500">
            Esta tabla suma todas las liquidaciones del mes. Si una colaboradora cobra semanal, agrupa todas las semanas dentro del mismo mes.
        </div>
    </div>

    {{-- LISTADO DE LIQUIDACIONES --}}
    <div class="fn-table-shell overflow-x-auto">
        <table class="min-w-full bg-white">
            <thead class="bg-slate-50 text-slate-700">
            <tr>
                <th class="text-left px-4 py-3 text-sm font-semibold">Fecha pago</th>
                <th class="text-left px-4 py-3 text-sm font-semibold">Colaboradora</th>
                <th class="text-right px-4 py-3 text-sm font-semibold">Hs normales</th>
                <th class="text-right px-4 py-3 text-sm font-semibold">Hs extras</th>
                <th class="text-right px-4 py-3 text-sm font-semibold">Valor hora</th>
                <th class="text-right px-4 py-3 text-sm font-semibold">Total horas</th>
                <th class="text-right px-4 py-3 text-sm font-semibold">Comisión</th>
                <th class="text-right px-4 py-3 text-sm font-semibold">Prod. a costo</th>
                <th class="text-right px-4 py-3 text-sm font-semibold">Total pagado</th>
                <th class="text-right px-4 py-3 text-sm font-semibold">Acciones</th>
            </tr>
            </thead>

            <tbody>
            @forelse($liquidaciones as $l)
                @php
                    $horasNormales = (float)$l->minutos_normales / 60;
                    $horasExtras = (float)$l->minutos_extras / 60;
                    $totalHoras = (float)$l->monto_horas_normales + (float)$l->monto_horas_extras;
                @endphp

                <tr class="border-t hover:bg-slate-50">
                    <td class="px-4 py-3">
                        {{ $l->fecha_pago?->format('d/m/Y') }}
                    </td>

                    <td class="px-4 py-3 font-semibold whitespace-nowrap">
                        {{ $l->colaboradora?->apellido }} {{ $l->colaboradora?->nombre }}
                    </td>

                    <td class="px-4 py-3 text-right">
                        {{ number_format($horasNormales, 2, ',', '.') }} hs
                    </td>

                    <td class="px-4 py-3 text-right">
                        {{ number_format($horasExtras, 2, ',', '.') }} hs
                    </td>

                    <td class="px-4 py-3 text-right">
                        ${{ number_format((float)$l->valor_hora, 2, ',', '.') }}
                    </td>

                    <td class="px-4 py-3 text-right font-semibold">
                        ${{ number_format($totalHoras, 2, ',', '.') }}
                    </td>

                    <td class="px-4 py-3 text-right">
                        ${{ number_format((float)$l->monto_comision, 2, ',', '.') }}
                    </td>

                    <td class="px-4 py-3 text-right text-red-700">
                        -${{ number_format((float)$l->monto_productos_costo, 2, ',', '.') }}
                    </td>

                    <td class="px-4 py-3 text-right font-bold">
                        ${{ number_format((float)$l->total_pagado, 2, ',', '.') }}
                    </td>

                    <td class="px-4 py-3">
                        <div class="flex justify-end gap-2">
                            <a href="{{ route('liquidaciones.show', $l) }}"
                               class="fn-icon-action"
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

    <div class="fn-stat-card mt-4 flex flex-col md:flex-row md:items-center md:justify-between gap-3">
        <div>
            <div class="text-sm text-slate-600">Total pagado del filtro</div>
            <div class="text-2xl font-extrabold text-slate-900">
                ${{ number_format((float)$totalFiltro, 2, ',', '.') }}
            </div>
        </div>
    </div>

    <div class="mt-4">
        {{ $liquidaciones->links() }}
    </div>

@endsection
