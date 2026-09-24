@extends('layouts.admin')

@section('title', 'Gastos - Cele Dore Estilista')
@section('h1', 'Gastos')
@section('sub', 'Registro de egresos manuales.')

@section('content')

    @if(session('ok'))
        <div class="mb-4 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-green-800">
            {{ session('ok') }}
        </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-4 gap-3 mb-6">
        <div class="fn-stat-card md:col-span-1">
            <div class="text-sm text-slate-600">Total del filtro</div>
            <div class="text-2xl font-bold mt-1">${{ number_format((float)$totalFiltro, 2, ',', '.') }}</div>
        </div>
    </div>

    <div class="fn-toolbar flex flex-col gap-3 mb-6">
        <form class="grid grid-cols-1 md:grid-cols-4 gap-3 w-full" method="GET" action="{{ route('gastos.index') }}">
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

            <div>
                <label class="text-sm font-semibold text-slate-700">Categoría</label>
                <input name="categoria" value="{{ $categoria }}"
                       class="mt-1 w-full rounded-xl border-slate-300 focus:border-slate-500 focus:ring-slate-500"
                       placeholder="Ej: Luz">
            </div>

            <div class="flex gap-2">
                <button class="fn-primary-action mt-6 w-full">
                    Filtrar
                </button>
                <a href="{{ route('gastos.index') }}"
                   class="fn-secondary-action mt-6 w-full text-center">
                    Limpiar
                </a>
            </div>
        </form>

        <div class="flex justify-end">
            <a href="{{ route('gastos.create') }}"
               class="fn-primary-action">
                + Ingresar gasto
            </a>
        </div>
    </div>

    <div class="fn-table-shell overflow-x-auto">
        <table class="min-w-full bg-white">
            <thead class="bg-slate-50 text-slate-700">
                <tr>
                    <th class="text-left px-4 py-3 text-sm font-semibold">Fecha</th>
                    <th class="text-left px-4 py-3 text-sm font-semibold">Categoría</th>
                    <th class="text-left px-4 py-3 text-sm font-semibold">Descripción</th>
                    <th class="text-left px-4 py-3 text-sm font-semibold">Monto</th>
                    <th class="text-right px-4 py-3 text-sm font-semibold">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($gastos as $g)
                    <tr class="border-t hover:bg-slate-50">
                        <td class="px-4 py-3">{{ $g->fecha?->format('d/m/Y') }}</td>
                        <td class="px-4 py-3">{{ $g->categoria }}</td>
                        <td class="px-4 py-3">{{ $g->descripcion ?: '-' }}</td>
                        <td class="px-4 py-3 font-semibold">${{ number_format((float)$g->monto, 2, ',', '.') }}</td>
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('gastos.edit', $g) }}"
                                   class="fn-icon-action">✏️</a>

                                <form method="POST" action="{{ route('gastos.destroy', $g) }}"
                                      onsubmit="return confirm('¿Eliminar este gasto?');">
                                    @csrf
                                    @method('DELETE')
                                    <button class="fn-icon-action fn-icon-action-danger">🗑️</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-8 text-center text-slate-500">
                            No hay gastos registrados.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $gastos->links() }}
    </div>

@endsection
