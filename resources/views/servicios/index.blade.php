@extends('layouts.admin')

@section('title', 'Servicios - Peluquería TOP')
@section('h1', 'Servicios')
@section('sub', 'Listado de servicios y precios.')

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
        <form class="grid grid-cols-1 md:grid-cols-2 gap-3 w-full md:max-w-xl" method="GET" action="{{ route('servicios.index') }}">
            <div>
                <label class="text-sm font-semibold text-slate-700">Nombre</label>
                <input name="nombre" value="{{ $nombre ?? '' }}"
                       class="mt-1 w-full rounded-xl border-slate-300 focus:border-slate-500 focus:ring-slate-500" />
            </div>

            <div class="flex gap-2">
                <button class="mt-6 w-full rounded-xl bg-slate-900 text-white px-4 py-2 hover:bg-slate-800">
                    Filtrar
                </button>

                <a href="{{ route('servicios.index') }}" class="mt-6 w-full text-center rounded-xl border px-4 py-2 hover:bg-slate-50">
                    Limpiar
                </a>
            </div>
        </form>

        <a href="{{ route('servicios.create') }}"
           class="rounded-xl bg-slate-900 text-white px-4 py-2 hover:bg-slate-800 text-center">
            + Servicio Nuevo
        </a>
    </div>

    <div class="overflow-x-auto rounded-2xl border">
        <table class="min-w-full bg-white">
            <thead class="bg-slate-50 text-slate-700">
            <tr>
                <th class="text-left px-4 py-3 text-sm font-semibold">{!! sort_link('Nombre', 'nombre', $sort ?? 'created_at', $dir ?? 'desc') !!}</th>
                <th class="text-left px-4 py-3 text-sm font-semibold">{!! sort_link('Precio', 'precio', $sort ?? 'created_at', $dir ?? 'desc') !!}</th>
                <th class="text-left px-4 py-3 text-sm font-semibold">{!! sort_link('Fecha', 'created_at', $sort ?? 'created_at', $dir ?? 'desc') !!}</th>
                <th class="text-right px-4 py-3 text-sm font-semibold">Acciones</th>
            </tr>
            </thead>

            <tbody>
            @forelse($servicios as $s)
                <tr class="border-t hover:bg-slate-50">
                    <td class="px-4 py-3">{{ $s->nombre }}</td>
                    <td class="px-4 py-3">${{ number_format((float)$s->precio, 2, ',', '.') }}</td>
                    <td class="px-4 py-3">{{ optional($s->created_at)->format('d/m/Y') }}</td>

                    <td class="px-4 py-3">
                        <div class="flex items-center justify-end gap-2">

                            <a href="{{ route('servicios.edit', $s) }}"
                               class="rounded-lg border px-3 py-1 hover:bg-white"
                               title="Editar">
                                ✏️
                            </a>

                            <form method="POST" action="{{ route('servicios.destroy', $s) }}"
                                  onsubmit="return confirm('¿Eliminar este servicio?');">
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
                    <td colspan="4" class="px-4 py-8 text-center text-slate-500">
                        No hay servicios cargados.
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $servicios->links() }}
    </div>

@endsection