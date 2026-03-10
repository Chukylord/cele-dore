@extends('layouts.admin')

@section('title', 'Colaboradoras - Peluquería TOP')
@section('h1', 'Colaboradoras')
@section('sub', 'Alta, filtros y listado de colaboradoras.')

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

    <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-3 mb-6">
        <form class="grid grid-cols-1 md:grid-cols-4 gap-3 w-full" method="GET" action="{{ route('colaboradoras.index') }}">
            <div>
                <label class="text-sm font-semibold text-slate-700">Nombre</label>
                <input name="nombre" value="{{ $nombre ?? '' }}"
                       class="mt-1 w-full rounded-xl border-slate-300 focus:border-slate-500 focus:ring-slate-500" />
            </div>

            <div>
                <label class="text-sm font-semibold text-slate-700">Apellido</label>
                <input name="apellido" value="{{ $apellido ?? '' }}"
                       class="mt-1 w-full rounded-xl border-slate-300 focus:border-slate-500 focus:ring-slate-500" />
            </div>

            <div>
                <label class="text-sm font-semibold text-slate-700">Activa</label>
                <select name="activa" class="mt-1 w-full rounded-xl border-slate-300 focus:border-slate-500 focus:ring-slate-500">
                    <option value=""  {{ ($activa ?? '') === ''  ? 'selected' : '' }}>Todas</option>
                    <option value="1" {{ ($activa ?? '') === '1' ? 'selected' : '' }}>Sí</option>
                    <option value="0" {{ ($activa ?? '') === '0' ? 'selected' : '' }}>No</option>
                </select>
            </div>

            <div class="flex gap-2">
                <button class="mt-6 w-full rounded-xl bg-slate-900 text-white px-4 py-2 hover:bg-slate-800">
                    Filtrar
                </button>

                <a href="{{ route('colaboradoras.index') }}" class="mt-6 w-full text-center rounded-xl border px-4 py-2 hover:bg-slate-50">
                    Limpiar
                </a>
            </div>
        </form>

        <a href="{{ route('colaboradoras.create') }}"
           class="rounded-xl bg-slate-900 text-white px-4 py-2 hover:bg-slate-800 text-center">
            + Colaboradora Nueva
        </a>
    </div>

    <div class="overflow-x-auto rounded-2xl border">
        <table class="min-w-full bg-white">
            <thead class="bg-slate-50 text-slate-700">
            <tr>
                <th class="text-left px-4 py-3 text-sm font-semibold">{!! sort_link('Nombre', 'nombre', $sort ?? 'created_at', $dir ?? 'desc') !!}</th>
                <th class="text-left px-4 py-3 text-sm font-semibold">{!! sort_link('Apellido', 'apellido', $sort ?? 'created_at', $dir ?? 'desc') !!}</th>
                <th class="text-left px-4 py-3 text-sm font-semibold">Teléfono</th>
                <th class="text-left px-4 py-3 text-sm font-semibold">{!! sort_link('Activa', 'activa', $sort ?? 'created_at', $dir ?? 'desc') !!}</th>
                <th class="text-left px-4 py-3 text-sm font-semibold">{!! sort_link('% Comisión', 'comision_pct', $sort ?? 'created_at', $dir ?? 'desc') !!}</th>
                <th class="text-right px-4 py-3 text-sm font-semibold">Acciones</th>
            </tr>
            </thead>

            <tbody>
            @forelse($colaboradoras as $c)
                <tr class="border-t hover:bg-slate-50">
                    <td class="px-4 py-3">{{ $c->nombre }}</td>
                    <td class="px-4 py-3">{{ $c->apellido }}</td>
                    <td class="px-4 py-3">{{ $c->telefono }}</td>

                    <td class="px-4 py-3">
                        @if($c->activa)
                            <span class="px-2 py-1 rounded-lg bg-green-50 text-green-700 border border-green-200 text-sm">Activa</span>
                        @else
                            <span class="px-2 py-1 rounded-lg bg-red-50 text-red-700 border border-red-200 text-sm">Inactiva</span>
                        @endif
                    </td>

                    <td class="px-4 py-3">{{ number_format((float)$c->comision_pct, 2, ',', '.') }}%</td>

                    <td class="px-4 py-3">
                        <div class="flex items-center justify-end gap-2">
                            <a href="{{ route('colaboradoras.edit', $c) }}"
                               class="rounded-lg border px-3 py-1 hover:bg-white"
                               title="Editar">
                                ✏️
                            </a>

                            <form method="POST" action="{{ route('colaboradoras.destroy', $c) }}"
                                  onsubmit="return confirm('¿Eliminar esta colaboradora?');">
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
                    <td colspan="6" class="px-4 py-8 text-center text-slate-500">
                        No hay colaboradoras cargadas.
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $colaboradoras->links() }}
    </div>

@endsection