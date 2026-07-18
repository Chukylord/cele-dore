@extends('layouts.admin')

@section('title', 'Clientes - Vir Tisone Studio')
@section('h1', 'Fichas clientes')
@section('sub', 'Alta, búsqueda e historial de clientas.')

@section('content')

@if(session('ok'))
    <div class="mb-4 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-green-800">
        {{ session('ok') }}
    </div>
@endif

@php
    function sort_link_clientes($label, $field, $sort, $dir) {
        $isActive = $sort === $field;
        $nextDir = ($isActive && $dir === 'asc') ? 'desc' : 'asc';
        $arrow = $isActive ? ($dir === 'asc' ? ' ↑' : ' ↓') : '';
        $url = request()->fullUrlWithQuery(['sort' => $field, 'dir' => $nextDir]);

        return '<a class="hover:underline" href="'.$url.'">'.$label.$arrow.'</a>';
    }
@endphp

<div class="flex flex-col md:flex-row md:items-end md:justify-between gap-3 mb-6">
    <form class="grid grid-cols-1 md:grid-cols-4 gap-3 w-full"
          method="GET"
          action="{{ route('clientes.index') }}">
        <div>
            <label class="text-sm font-semibold text-slate-700">Nombre</label>
            <input name="nombre"
                   value="{{ $nombre }}"
                   class="mt-1 w-full rounded-xl border-slate-300 focus:border-slate-500 focus:ring-slate-500">
        </div>

        <div>
            <label class="text-sm font-semibold text-slate-700">Apellido</label>
            <input name="apellido"
                   value="{{ $apellido }}"
                   class="mt-1 w-full rounded-xl border-slate-300 focus:border-slate-500 focus:ring-slate-500">
        </div>

        <div>
            <label class="text-sm font-semibold text-slate-700">DNI</label>
            <input name="dni"
                   inputmode="numeric"
                   value="{{ $dni }}"
                   placeholder="Ej: 34025037"
                   class="mt-1 w-full rounded-xl border-slate-300 focus:border-slate-500 focus:ring-slate-500">
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
       class="rounded-xl bg-slate-900 text-white px-4 py-2 hover:bg-slate-800 text-center whitespace-nowrap">
        + Cliente nueva
    </a>
</div>

<div class="overflow-x-auto rounded-2xl border">
    <table class="min-w-full bg-white">
        <thead class="bg-slate-50 text-slate-700">
        <tr>
            <th class="text-left px-4 py-3 text-sm font-semibold">
                {!! sort_link_clientes('Apellido', 'apellido', $sort, $dir) !!}
            </th>
            <th class="text-left px-4 py-3 text-sm font-semibold">
                {!! sort_link_clientes('Nombre', 'nombre', $sort, $dir) !!}
            </th>
            <th class="text-left px-4 py-3 text-sm font-semibold">
                {!! sort_link_clientes('DNI', 'dni', $sort, $dir) !!}
            </th>
            <th class="text-left px-4 py-3 text-sm font-semibold">Teléfono</th>
            <th class="text-left px-4 py-3 text-sm font-semibold">
                {!! sort_link_clientes('Última compra', 'ultima_compra', $sort, $dir) !!}
            </th>
            <th class="text-right px-4 py-3 text-sm font-semibold">Acciones</th>
        </tr>
        </thead>

        <tbody>
        @forelse($clientes as $cliente)
            @php
                $observacionLimpia = trim((string)$cliente->observacion);
                $lineasObs = $observacionLimpia !== ''
                    ? array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', $observacionLimpia))))
                    : [];
                $tieneObs = count($lineasObs) > 0;
            @endphp

            <tr class="border-t hover:bg-slate-50">
                <td class="px-4 py-3 font-semibold">{{ $cliente->apellido }}</td>
                <td class="px-4 py-3">{{ $cliente->nombre }}</td>
                <td class="px-4 py-3">
                    @if($cliente->dni)
                        <div class="flex items-center gap-2">
                            <span class="font-mono">{{ $cliente->dni }}</span>
                            <button type="button"
                                    class="rounded-lg border px-2 py-1 text-xs hover:bg-white"
                                    data-copy-dni="{{ $cliente->dni }}"
                                    title="Copiar DNI">
                                Copiar
                            </button>
                        </div>
                    @else
                        <span class="text-amber-700 text-sm">Sin DNI</span>
                    @endif
                </td>
                <td class="px-4 py-3">{{ $cliente->telefono }}</td>
                <td class="px-4 py-3">
                    @if($cliente->ultima_compra)
                        {{ \Carbon\Carbon::parse($cliente->ultima_compra)->format('d/m/Y') }}
                    @else
                        -
                    @endif
                </td>

                <td class="px-4 py-3">
                    <div class="flex items-center justify-end gap-2">
                        @if($tieneObs)
                            <button type="button"
                                    class="rounded-lg border px-3 py-1 hover:bg-white"
                                    data-open-obs="{{ $cliente->id }}"
                                    title="Ver observación">
                                👁️
                            </button>
                        @endif

                        <a href="{{ route('clientes.show', $cliente) }}"
                           class="rounded-lg border px-3 py-1 hover:bg-white"
                           title="Ver cliente">
                            🔎
                        </a>

                        <a href="{{ route('clientes.edit', $cliente) }}"
                           class="rounded-lg border px-3 py-1 hover:bg-white"
                           title="Editar">
                            ✏️
                        </a>

                        <form method="POST"
                              action="{{ route('clientes.destroy', $cliente) }}"
                              onsubmit="return confirm('¿Eliminar esta clienta?');">
                            @csrf
                            @method('DELETE')
                            <button class="rounded-lg border px-3 py-1 hover:bg-white" title="Eliminar">
                                🗑️
                            </button>
                        </form>
                    </div>

                    @if($tieneObs)
                        <div id="obs-{{ $cliente->id }}"
                             class="fixed inset-0 hidden items-center justify-center bg-black/40 p-4 z-50">
                            <div class="w-full max-w-2xl bg-white rounded-2xl shadow p-5">
                                <div class="flex items-start justify-between gap-4">
                                    <div>
                                        <div class="text-lg font-bold">Observación</div>
                                        <div class="text-sm text-slate-600">
                                            {{ $cliente->apellido }} {{ $cliente->nombre }}
                                        </div>
                                    </div>

                                    <button type="button"
                                            class="text-slate-500 hover:text-slate-900 text-2xl leading-none"
                                            data-close-obs="{{ $cliente->id }}">
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
                            </div>
                        </div>
                    @endif
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="6" class="px-4 py-8 text-center text-slate-500">
                    No hay clientas cargadas.
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
document.addEventListener('click', async function (event) {
    const openBtn = event.target.closest('[data-open-obs]');
    const closeBtn = event.target.closest('[data-close-obs]');
    const copyBtn = event.target.closest('[data-copy-dni]');

    if (openBtn) {
        const modal = document.getElementById('obs-' + openBtn.dataset.openObs);
        if (modal) {
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }
    }

    if (closeBtn) {
        const modal = document.getElementById('obs-' + closeBtn.dataset.closeObs);
        if (modal) {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }
    }

    if (copyBtn) {
        const dni = copyBtn.dataset.copyDni || '';

        try {
            await navigator.clipboard.writeText(dni);
        } catch (error) {
            const textarea = document.createElement('textarea');
            textarea.value = dni;
            document.body.appendChild(textarea);
            textarea.select();
            document.execCommand('copy');
            textarea.remove();
        }

        const textoOriginal = copyBtn.textContent;
        copyBtn.textContent = 'Copiado';
        setTimeout(() => copyBtn.textContent = textoOriginal, 1200);
    }
});
</script>

@endsection
