@extends('layouts.admin')

@section('title', 'Proveedores - FN Peluquería')
@section('h1', 'Proveedores')
@section('sub', 'Listado de proveedores y cuenta corriente.')

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
    @endphp

    <div class="fn-toolbar flex flex-col md:flex-row md:items-end md:justify-between gap-3 mb-6">
        <form class="grid grid-cols-1 md:grid-cols-2 gap-3 w-full md:max-w-xl" method="GET" action="{{ route('proveedores.index') }}">
            <div>
                <label class="text-sm font-semibold text-slate-700">Nombre</label>
                <input name="nombre" value="{{ $nombre ?? '' }}"
                       class="mt-1 w-full rounded-xl border-slate-300 focus:border-slate-500 focus:ring-slate-500" />
            </div>

            <div class="flex gap-2">
                <button class="fn-primary-action mt-6 w-full">
                    Filtrar
                </button>

                <a href="{{ route('proveedores.index') }}" class="fn-secondary-action mt-6 w-full text-center">
                    Limpiar
                </a>
            </div>
        </form>

        <a href="{{ route('proveedores.create') }}"
           class="fn-primary-action text-center">
            + Proveedor Nuevo
        </a>
    </div>

    <div class="fn-table-shell overflow-x-auto">
        <table class="min-w-full bg-white">
            <thead class="bg-slate-50 text-slate-700">
            <tr>
                <th class="text-left px-4 py-3 text-sm font-semibold">{!! sort_link('Nombre', 'nombre', $sort ?? 'created_at', $dir ?? 'desc') !!}</th>
                <th class="text-left px-4 py-3 text-sm font-semibold">Total compras</th>
                <th class="text-left px-4 py-3 text-sm font-semibold">Entregado</th>
                <th class="text-left px-4 py-3 text-sm font-semibold">Debe</th>
                <th class="text-left px-4 py-3 text-sm font-semibold">{!! sort_link('Fecha', 'created_at', $sort ?? 'created_at', $dir ?? 'desc') !!}</th>
                <th class="text-right px-4 py-3 text-sm font-semibold">Acciones</th>
            </tr>
            </thead>

            <tbody>
            @forelse($proveedores as $p)
                <tr class="border-t hover:bg-slate-50">
                    <td class="px-4 py-3 font-semibold">{{ $p->nombre }}</td>

                    <td class="px-4 py-3">
                        ${{ number_format((float)($p->total_compras_cc ?? 0), 2, ',', '.') }}
                    </td>

                    <td class="px-4 py-3">
                        ${{ number_format((float)($p->total_pagos_cc ?? 0), 2, ',', '.') }}
                    </td>

                    <td class="px-4 py-3 font-bold {{ (float)($p->saldo_cc ?? 0) > 0 ? 'text-red-700' : 'text-green-700' }}">
                        ${{ number_format((float)($p->saldo_cc ?? 0), 2, ',', '.') }}
                    </td>

                    <td class="px-4 py-3">{{ optional($p->created_at)->format('d/m/Y') }}</td>

                    <td class="px-4 py-3">
                        <div class="flex items-center justify-end gap-2">
                            <a href="{{ route('proveedores.cuenta', $p) }}"
                               class="fn-icon-action"
                               title="Cuenta corriente">
                                🔎
                            </a>

                            <a href="{{ route('proveedores.edit', $p) }}"
                               class="fn-icon-action"
                               title="Editar">
                                ✏️
                            </a>

                            <form method="POST" action="{{ route('proveedores.destroy', $p) }}"
                                  onsubmit="return confirm('¿Eliminar este proveedor?');">
                                @csrf
                                @method('DELETE')
                                <button class="fn-icon-action fn-icon-action-danger" title="Eliminar">
                                    🗑️
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="px-4 py-8 text-center text-slate-500">
                        No hay proveedores cargados.
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $proveedores->links() }}
    </div>

@endsection
