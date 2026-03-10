@extends('layouts.admin')

@section('title', 'Ver Cliente - Peluquería TOP')
@section('h1', 'Ver Cliente')
@section('sub', 'Ficha e historial de compras.')

@section('content')

<div class="flex items-start justify-between gap-4 mb-6">
    <div>
        <div class="text-2xl font-bold">{{ $cliente->nombre }} {{ $cliente->apellido }}</div>
        <div class="text-slate-600 mt-1">Teléfono: {{ $cliente->telefono ?: '-' }}</div>
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
    <div class="text-sm font-semibold text-slate-700 mb-1">Observación</div>
    <div class="whitespace-pre-wrap text-slate-800">
        {{ trim((string)$cliente->observacion) !== '' ? $cliente->observacion : 'Sin observación.' }}
    </div>
</div>

<div class="mb-3">
    <div class="text-lg font-bold">Historial de compras</div>
    <div class="text-slate-600 text-sm">Cada registro corresponde a una venta (servicios y/o productos).</div>
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
        @forelse($historial as $h)
            <tr class="border-t align-top">
                <td class="px-4 py-3 whitespace-nowrap">{{ $h['fecha'] }}</td>
                <td class="px-4 py-3">{{ ucfirst($h['metodo']) }}</td>
                <td class="px-4 py-3">{{ $h['vendedora'] }}</td>

                <td class="px-4 py-3 font-semibold">
                    ${{ number_format((float)$h['servicios'], 2, ',', '.') }}
                </td>

                <td class="px-4 py-3 font-semibold">
                    ${{ number_format((float)$h['productos'], 2, ',', '.') }}
                </td>

                <td class="px-4 py-3 font-bold">
                    ${{ number_format((float)$h['total'], 2, ',', '.') }}
                </td>

                <td class="px-4 py-3 text-slate-700">
                    <div class="text-xs text-slate-500 font-semibold">Servicios</div>
                    <div class="text-sm">{{ $h['detalle_servicios'] }}</div>

                    <div class="mt-2 text-xs text-slate-500 font-semibold">Productos</div>
                    <div class="text-sm">{{ $h['detalle_productos'] }}</div>

                    <div class="mt-2">
                        <a href="{{ route('ventas.show', $h['venta_id']) }}" class="text-sm underline">
                            Ver venta
                        </a>
                    </div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="7" class="px-4 py-8 text-center text-slate-500">
                    Todavía no hay compras registradas para este cliente.
                </td>
            </tr>
        @endforelse
        </tbody>
    </table>
</div>

@endsection