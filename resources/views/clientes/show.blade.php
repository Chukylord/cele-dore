@extends('layouts.admin')

@section('title', 'Ver cliente - fn peluqueria')
@section('h1', 'Ver cliente')
@section('sub', 'Ficha e historial de compras.')

@section('content')

@php
    $observacionLimpia = trim((string) $cliente->observacion);
    $lineasObs = $observacionLimpia !== ''
        ? array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', $observacionLimpia))))
        : [];
@endphp

<div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4 mb-6">
    <div>
        <div class="text-2xl font-bold">{{ $cliente->apellido }} {{ $cliente->nombre }}</div>
        <div class="mt-3 grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div class="rounded-xl border bg-slate-50 px-4 py-3">
                <div class="text-xs text-slate-500">DNI</div>
                @if($cliente->dni)
                    <div class="mt-1 flex items-center gap-2">
                        <strong class="font-mono text-lg">{{ $cliente->dni }}</strong>
                        <button type="button"
                                id="copiarDniCliente"
                                data-dni="{{ $cliente->dni }}"
                                class="rounded-lg border bg-white px-3 py-1 text-sm hover:bg-slate-50">
                            Copiar
                        </button>
                    </div>
                @else
                    <div class="mt-1 text-amber-700">Sin DNI cargado</div>
                @endif
            </div>

            <div class="rounded-xl border bg-slate-50 px-4 py-3">
                <div class="text-xs text-slate-500">Teléfono</div>
                <div class="mt-1 font-semibold">{{ $cliente->telefono ?: '-' }}</div>
            </div>
        </div>
    </div>

    <div class="flex gap-2">
        <a href="{{ route('clientes.edit', $cliente) }}"
           class="rounded-xl border px-4 py-2 hover:bg-slate-50">
            ✏️ Editar
        </a>

        <a href="{{ route('clientes.index') }}"
           class="rounded-xl bg-slate-900 text-white px-4 py-2 hover:bg-slate-800">
            Volver
        </a>
    </div>
</div>

<div class="rounded-2xl border bg-slate-50 p-4 mb-6">
    <div class="text-sm font-semibold text-slate-700 mb-3">Observación</div>

    @if(count($lineasObs))
        <div class="rounded-xl border bg-white overflow-hidden">
            @foreach($lineasObs as $linea)
                <div class="px-4 py-3 text-slate-800 leading-relaxed break-words border-b last:border-b-0">
                    {{ $linea }}
                </div>
            @endforeach
        </div>
    @else
        <div class="rounded-xl border bg-white px-4 py-3 text-slate-500">
            Sin observación.
        </div>
    @endif
</div>

<div class="mb-3">
    <div class="text-lg font-bold">Historial de compras</div>
    <div class="text-slate-600 text-sm">
        Cada registro corresponde a una venta de servicios y/o productos.
    </div>
</div>

<div class="rounded-2xl border overflow-x-auto">
    <table class="min-w-full bg-white">
        <thead class="bg-slate-50 text-slate-700">
        <tr>
            <th class="text-left px-4 py-3 text-sm font-semibold">Fecha</th>
            <th class="text-left px-4 py-3 text-sm font-semibold">Método</th>
            <th class="text-left px-4 py-3 text-sm font-semibold">Vendedora</th>
            <th class="text-left px-4 py-3 text-sm font-semibold">Servicios</th>
            <th class="text-left px-4 py-3 text-sm font-semibold">Productos</th>
            <th class="text-left px-4 py-3 text-sm font-semibold">Total</th>
            <th class="text-left px-4 py-3 text-sm font-semibold">Detalle</th>
        </tr>
        </thead>

        <tbody>
        @forelse($historial as $item)
            <tr class="border-t align-top">
                <td class="px-4 py-3 whitespace-nowrap">{{ $item['fecha'] }}</td>
                <td class="px-4 py-3">{{ ucfirst((string)$item['metodo']) }}</td>
                <td class="px-4 py-3">{{ $item['vendedora'] }}</td>
                <td class="px-4 py-3 font-semibold">
                    ${{ number_format((float)$item['servicios'], 2, ',', '.') }}
                </td>
                <td class="px-4 py-3 font-semibold">
                    ${{ number_format((float)$item['productos'], 2, ',', '.') }}
                </td>
                <td class="px-4 py-3 font-bold">
                    ${{ number_format((float)$item['total'], 2, ',', '.') }}
                </td>
                <td class="px-4 py-3 text-slate-700">
                    <div class="text-xs text-slate-500 font-semibold">Servicios</div>
                    <div class="text-sm">{{ $item['detalle_servicios'] }}</div>

                    <div class="mt-2 text-xs text-slate-500 font-semibold">Productos</div>
                    <div class="text-sm">{{ $item['detalle_productos'] }}</div>

                    <div class="mt-2">
                        <a href="{{ route('ventas.show', $item['venta_id']) }}"
                           class="text-sm underline">
                            Ver venta
                        </a>
                    </div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="7" class="px-4 py-8 text-center text-slate-500">
                    Todavía no hay compras registradas para esta clienta.
                </td>
            </tr>
        @endforelse
        </tbody>
    </table>
</div>

@if($cliente->dni)
<script>
document.getElementById('copiarDniCliente')?.addEventListener('click', async function () {
    const dni = this.dataset.dni || '';

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

    const original = this.textContent;
    this.textContent = 'Copiado';
    setTimeout(() => this.textContent = original, 1200);
});
</script>
@endif

@endsection
