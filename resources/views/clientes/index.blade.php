@extends('layouts.admin')

@section('title', 'Clientes - Cele Dore Estilista')
@section('h1', 'Fichas Clientes')
@section('sub', 'Alta, filtros y listado de clientes.')

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

            $arrow = '';
            if ($isActive) $arrow = $dir === 'asc' ? ' ↑' : ' ↓';

            $url = request()->fullUrlWithQuery(['sort' => $field, 'dir' => $nextDir]);

            return '<a class="hover:underline" href="'.$url.'">'.$label.$arrow.'</a>';
        }
    @endphp

    <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-3 mb-6">
        <form class="grid grid-cols-1 md:grid-cols-3 gap-3 w-full md:max-w-3xl" method="GET" action="{{ route('clientes.index') }}">
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

            <div class="flex gap-2">
                <button class="mt-6 w-full rounded-xl bg-slate-900 text-white px-4 py-2 hover:bg-slate-800">
                    Filtrar
                </button>

                <a href="{{ route('clientes.index') }}"
                   class="mt-6 w-full text-center rounded-xl border px-4 py-2 hover:bg-slate-50">
                    Limpiar
                </a>
            </div>
        </form>

        <a href="{{ route('clientes.create') }}"
           class="rounded-xl bg-slate-900 text-white px-4 py-2 hover:bg-slate-800 text-center">
            + Cliente Nuevo
        </a>
    </div>

    <div class="overflow-x-auto rounded-2xl border">
        <table class="min-w-full bg-white">
            <thead class="bg-slate-50 text-slate-700">
            <tr>
                <th class="text-left px-4 py-3 text-sm font-semibold">
                    {!! sort_link('Nombre', 'nombre', $sort ?? 'ultima_compra', $dir ?? 'desc') !!}
                </th>

                <th class="text-left px-4 py-3 text-sm font-semibold">
                    {!! sort_link('Apellido', 'apellido', $sort ?? 'ultima_compra', $dir ?? 'desc') !!}
                </th>

                <th class="text-left px-4 py-3 text-sm font-semibold">Teléfono</th>

                <th class="text-left px-4 py-3 text-sm font-semibold">
                    {!! sort_link('Última compra', 'ultima_compra', $sort ?? 'ultima_compra', $dir ?? 'desc') !!}
                </th>

                <th class="text-right px-4 py-3 text-sm font-semibold">Acciones</th>
            </tr>
            </thead>

            <tbody>
            @forelse($clientes as $c)
                @php
                    $observacionLimpia = trim((string) $c->observacion);
                    $lineasObs = $observacionLimpia !== ''
                        ? array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', $observacionLimpia))))
                        : [];
                    $tieneObs = count($lineasObs) > 0;
                @endphp

                <tr class="border-t hover:bg-slate-50">
                    <td class="px-4 py-3">{{ $c->nombre }}</td>

                    <td class="px-4 py-3">{{ $c->apellido }}</td>

                    <td class="px-4 py-3">{{ $c->telefono }}</td>

                    <td class="px-4 py-3">
                        @if($c->ultima_compra)
                            {{ \Carbon\Carbon::parse($c->ultima_compra)->format('d/m/Y') }}
                        @else
                            -
                        @endif
                    </td>

                    <td class="px-4 py-3">
                        <div class="flex items-center justify-end gap-2">

                            @if($tieneObs)
                                <button type="button"
                                        class="rounded-lg border px-3 py-1 hover:bg-white"
                                        data-open-obs="{{ $c->id }}"
                                        title="Ver observación">
                                    👁️
                                </button>
                            @endif

                            <a href="{{ route('clientes.show', $c) }}"
                               class="rounded-lg border px-3 py-1 hover:bg-white"
                               title="Ver cliente">
                                🔎
                            </a>

                            <a href="{{ route('clientes.edit', $c) }}"
                               class="rounded-lg border px-3 py-1 hover:bg-white"
                               title="Editar">
                                ✏️
                            </a>

                            <form method="POST" action="{{ route('clientes.destroy', $c) }}"
                                  onsubmit="return confirm('¿Eliminar este cliente?');">
                                @csrf
                                @method('DELETE')
                                <button class="rounded-lg border px-3 py-1 hover:bg-white" title="Eliminar">
                                    🗑️
                                </button>
                            </form>
                        </div>

                        @if($tieneObs)
                            <div id="obs-{{ $c->id }}" class="fixed inset-0 hidden items-center justify-center bg-black/40 p-4 z-50">
                                <div class="w-full max-w-2xl bg-white rounded-2xl shadow p-5">
                                    <div class="flex items-start justify-between gap-4">
                                        <div>
                                            <div class="text-lg font-bold">Observación</div>
                                            <div class="text-sm text-slate-600">{{ $c->nombre }} {{ $c->apellido }}</div>
                                        </div>

                                        <button type="button"
                                                class="text-slate-500 hover:text-slate-900 text-2xl leading-none"
                                                data-close-obs="{{ $c->id }}">
                                            ✖
                                        </button>
                                    </div>

                                    <div class="mt-4 rounded-xl border bg-slate-50 overflow-hidden">
                                        @foreach($lineasObs as $linea)
                                            <div class="px-4 py-3 text-slate-800 leading-relaxed break-words border-b last:border-b-0">
                                                {{ $linea }}
                                            </div>
                                        @endforeach
                                    </div>

                                    <div class="mt-4 flex justify-end">
                                        <button type="button"
                                                class="rounded-xl bg-slate-900 text-white px-4 py-2 hover:bg-slate-800"
                                                data-close-obs="{{ $c->id }}">
                                            Cerrar
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @endif

                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="px-4 py-8 text-center text-slate-500">
                        No hay clientes cargados.
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $clientes->links() }}
    </div>

    <script>
        document.addEventListener('click', function(e) {
            const openBtn = e.target.closest('[data-open-obs]');
            const closeBtn = e.target.closest('[data-close-obs]');

            if (openBtn) {
                const id = openBtn.getAttribute('data-open-obs');
                const modal = document.getElementById('obs-' + id);

                if (modal) {
                    modal.classList.remove('hidden');
                    modal.classList.add('flex');
                }
            }

            if (closeBtn) {
                const id = closeBtn.getAttribute('data-close-obs');
                const modal = document.getElementById('obs-' + id);

                if (modal) {
                    modal.classList.add('hidden');
                    modal.classList.remove('flex');
                }
            }

            if (e.target.classList.contains('bg-black/40') && e.target.id && e.target.id.startsWith('obs-')) {
                e.target.classList.add('hidden');
                e.target.classList.remove('flex');
            }
        });
    </script>

@endsection