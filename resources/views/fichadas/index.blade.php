@extends('layouts.admin')

@section('title', 'Fichadas - Cele Dore Estilista')
@section('h1', 'Fichadas')
@section('sub', 'Registro de horas trabajadas de colaboradoras.')

@section('content')

    @if(session('ok'))
        <div class="mb-4 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-green-800">
            {{ session('ok') }}
        </div>
    @endif

    @php
        $pageNormalesMin = $fichadas->sum('minutos_normales');
        $pageExtrasMin   = $fichadas->sum('minutos_extras');
        $pageTotalMin    = $pageNormalesMin + $pageExtrasMin;
    @endphp

    {{-- Tarjetas con totales del filtro --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-3 mb-6">
        <div class="fn-stat-card">
            <div class="text-sm text-slate-600">Normales (filtro)</div>
            <div class="text-2xl font-bold mt-1">
                {{ number_format(($totalNormalesMin ?? 0) / 60, 2, ',', '.') }} hs
            </div>
        </div>

        <div class="fn-stat-card">
            <div class="text-sm text-slate-600">Extras (filtro)</div>
            <div class="text-2xl font-bold mt-1">
                {{ number_format(($totalExtrasMin ?? 0) / 60, 2, ',', '.') }} hs
            </div>
        </div>

        <div class="fn-stat-card fn-stat-card-primary">
            <div class="text-sm text-slate-200">Total (filtro)</div>
            <div class="text-3xl font-extrabold mt-1">
                {{ number_format(($totalGeneralMin ?? 0) / 60, 2, ',', '.') }} hs
            </div>
        </div>
    </div>

    <div class="fn-toolbar flex flex-col gap-3 mb-6">
        <form class="grid grid-cols-1 md:grid-cols-5 gap-3 w-full" method="GET" action="{{ route('fichadas.index') }}">
            <div class="md:col-span-2">
                <label class="text-sm font-semibold text-slate-700">Colaboradora</label>
                <select name="colaboradora_id" class="mt-1 w-full rounded-xl border-slate-300 focus:border-slate-500 focus:ring-slate-500">
                    <option value="">Todas</option>
                    @foreach($colaboradoras as $c)
                        <option value="{{ $c->id }}" {{ (string)$colaboradora_id === (string)$c->id ? 'selected' : '' }}>
                            {{ $c->apellido }} {{ $c->nombre }}
                        </option>
                    @endforeach
                </select>
            </div>

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

            <div class="flex gap-2">
                <button class="fn-primary-action mt-6 w-full">
                    Filtrar
                </button>

                <a href="{{ route('fichadas.index') }}"
                   class="fn-secondary-action mt-6 w-full text-center">
                    Limpiar
                </a>
            </div>
        </form>

        <div class="flex justify-end">
            <a href="{{ route('fichadas.create') }}"
               class="fn-primary-action">
                + Nueva fichada
            </a>
        </div>
    </div>

    <div class="fn-table-shell overflow-x-auto">
        <table class="min-w-full bg-white">
            <thead class="bg-slate-50 text-slate-700">
            <tr>
                <th class="text-left px-4 py-3 text-sm font-semibold">Colaboradora</th>
                <th class="text-left px-4 py-3 text-sm font-semibold">Fecha</th>
                <th class="text-left px-4 py-3 text-sm font-semibold">Inicio</th>
                <th class="text-left px-4 py-3 text-sm font-semibold">Fin</th>
                <th class="text-left px-4 py-3 text-sm font-semibold">Tipo</th>
                <th class="text-left px-4 py-3 text-sm font-semibold">Horas normales</th>
                <th class="text-left px-4 py-3 text-sm font-semibold">Horas extras</th>
                <th class="text-right px-4 py-3 text-sm font-semibold">Acciones</th>
            </tr>
            </thead>
            <tbody>
            @forelse($fichadas as $f)
                <tr class="border-t hover:bg-slate-50">
                    <td class="px-4 py-3">{{ $f->colaboradora?->apellido }} {{ $f->colaboradora?->nombre }}</td>
                    <td class="px-4 py-3">{{ $f->fecha?->format('d/m/Y') }}</td>
                    <td class="px-4 py-3">{{ substr($f->hora_inicio, 0, 5) }}</td>
                    <td class="px-4 py-3">{{ substr($f->hora_fin, 0, 5) }}</td>

                    <td class="px-4 py-3">
                        @if($f->es_extra)
                            <span class="px-2 py-1 rounded-lg bg-red-50 text-red-700 border border-red-200 text-sm">
                                Extra
                            </span>
                        @else
                            <span class="px-2 py-1 rounded-lg bg-slate-50 text-slate-700 border border-slate-200 text-sm">
                                Normal
                            </span>
                        @endif
                    </td>

                    <td class="px-4 py-3">{{ number_format($f->minutos_normales / 60, 2, ',', '.') }}</td>
                    <td class="px-4 py-3">{{ number_format($f->minutos_extras / 60, 2, ',', '.') }}</td>

                    <td class="px-4 py-3">
                        <div class="flex items-center justify-end gap-2">
                            <a href="{{ route('fichadas.edit', $f) }}"
                               class="fn-icon-action">
                                ✏️
                            </a>

                            <form method="POST" action="{{ route('fichadas.destroy', $f) }}"
                                  onsubmit="return confirm('¿Eliminar esta fichada?');">
                                @csrf
                                @method('DELETE')
                                <button class="fn-icon-action fn-icon-action-danger">
                                    🗑️
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="px-4 py-8 text-center text-slate-500">
                        No hay fichadas registradas.
                    </td>
                </tr>
            @endforelse
            </tbody>

            @if($fichadas->count() > 0)
                <tfoot class="bg-slate-100 border-t-2">
                <tr>
                    <td colspan="5" class="px-4 py-3 text-right font-bold text-slate-800">
                        Totales de esta página:
                    </td>
                    <td class="px-4 py-3 font-bold text-slate-900">
                        {{ number_format($pageNormalesMin / 60, 2, ',', '.') }}
                    </td>
                    <td class="px-4 py-3 font-bold text-slate-900">
                        {{ number_format($pageExtrasMin / 60, 2, ',', '.') }}
                    </td>
                </tr>
                </tfoot>
            @endif
        </table>
    </div>

    <div class="mt-4">
        {{ $fichadas->links() }}
    </div>

@endsection
