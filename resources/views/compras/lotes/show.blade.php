@extends('layouts.admin')

@section('title', 'Detalle de Lote - Compras')
@section('h1', 'Detalle de Lote')
@section('sub', 'Productos incluidos y pagos del proveedor.')

@section('content')

@if(session('ok'))
    <div class="mb-4 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-green-800">
        {{ session('ok') }}
    </div>
@endif

@php
    $compras = $lote->compras ?? collect();
    $totalLote = (float)$lote->monto_total;
    $entregado = (float)$lote->monto_pagado;
    $saldo = max($totalLote - $entregado, 0);
@endphp

<div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4 mb-6">
    <div>
        <div class="text-2xl font-bold">Lote #{{ $lote->id }}</div>
        <div class="text-slate-600 mt-1">Fecha: {{ \Carbon\Carbon::parse($lote->fecha)->format('d/m/Y') }}</div>
        @if($lote->nota)
            <div class="text-slate-600 mt-1">Nota: {{ $lote->nota }}</div>
        @endif
    </div>

    <div class="flex flex-wrap gap-2">
        <a href="{{ route('compras.lotes.edit', $lote) }}"
           class="rounded-xl border px-4 py-2 hover:bg-slate-50">
            ✏️ Editar lote
        </a>

        <form method="POST" action="{{ route('compras.lotes.destroy', $lote) }}"
              onsubmit="return confirm('¿Eliminar este lote? Se revertirá el stock.');">
            @csrf
            @method('DELETE')
            <button class="rounded-xl border px-4 py-2 hover:bg-slate-50">
                🗑️ Eliminar lote
            </button>
        </form>

        <a href="{{ route('compras.index') }}"
           class="rounded-xl bg-slate-900 text-white px-4 py-2 hover:bg-slate-800">
            Volver
        </a>
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
    <div class="rounded-2xl border bg-white p-4">
        <div class="text-sm text-slate-600">Total lote</div>
        <div class="text-2xl font-bold mt-1">${{ number_format($totalLote, 2, ',', '.') }}</div>
    </div>

    <div class="rounded-2xl border bg-white p-4">
        <div class="text-sm text-slate-600">Entregado</div>
        <div class="text-2xl font-bold mt-1">${{ number_format($entregado, 2, ',', '.') }}</div>
    </div>

    <div class="rounded-2xl border bg-white p-4">
        <div class="text-sm text-slate-600">Saldo</div>
        <div class="text-2xl font-bold mt-1">${{ number_format($saldo, 2, ',', '.') }}</div>
    </div>

    <div class="rounded-2xl border p-4
        {{ $lote->estado_pago === 'pagado'
            ? 'bg-green-600 text-white'
            : ($lote->estado_pago === 'parcial' ? 'bg-yellow-100 text-yellow-900' : 'bg-red-100 text-red-900') }}">
        <div class="text-sm">Estado</div>
        <div class="text-2xl font-bold mt-1 capitalize">{{ $lote->estado_pago }}</div>
    </div>
</div>

@if($saldo > 0)
    <div class="rounded-2xl border bg-white p-4 mb-6">
        <div class="flex items-center justify-between mb-4">
            <div>
                <div class="text-lg font-bold">Registrar pago</div>
                <div class="text-sm text-slate-600">Se suma a lo entregado y actualiza el estado automáticamente.</div>
            </div>
            <div class="text-sm text-slate-600">
                Saldo actual:
                <span class="font-bold">${{ number_format($saldo, 2, ',', '.') }}</span>
            </div>
        </div>

        <form method="POST" action="{{ route('compras.lotes.pagos.store', $lote) }}" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
            @csrf

            <div>
                <label class="text-sm font-semibold text-slate-700">Fecha</label>
                <input type="date" name="fecha" value="{{ now()->format('Y-m-d') }}"
                       class="mt-1 w-full rounded-xl border-slate-300 focus:border-slate-500 focus:ring-slate-500">
            </div>

            <div>
                <label class="text-sm font-semibold text-slate-700">Monto</label>
                <input type="number" step="0.01" min="0.01" name="monto"
                       class="mt-1 w-full rounded-xl border-slate-300 focus:border-slate-500 focus:ring-slate-500">
            </div>

            <div>
                <label class="text-sm font-semibold text-slate-700">Observación</label>
                <input name="observacion"
                       class="mt-1 w-full rounded-xl border-slate-300 focus:border-slate-500 focus:ring-slate-500"
                       placeholder="Ej: segunda entrega">
            </div>

            <div>
                <button class="w-full rounded-xl bg-slate-900 text-white px-4 py-2 hover:bg-slate-800">
                    Registrar pago
                </button>
            </div>
        </form>
    </div>
@endif

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <div>
        <div class="text-lg font-bold mb-3">Productos del lote</div>
        <div class="overflow-x-auto rounded-2xl border">
            <table class="min-w-full bg-white">
                <thead class="bg-slate-50 text-slate-700">
                <tr>
                    <th class="text-left px-4 py-3 text-sm font-semibold">Proveedor</th>
                    <th class="text-left px-4 py-3 text-sm font-semibold">Producto</th>
                    <th class="text-left px-4 py-3 text-sm font-semibold">Cantidad</th>
                    <th class="text-left px-4 py-3 text-sm font-semibold">Costo unit.</th>
                    <th class="text-left px-4 py-3 text-sm font-semibold">Subtotal</th>
                </tr>
                </thead>
                <tbody>
                @forelse($compras as $c)
                    @php $sub = (float)$c->cantidad * (float)$c->precio_unitario; @endphp
                    <tr class="border-t hover:bg-slate-50">
                        <td class="px-4 py-3">{{ $c->proveedor?->nombre ?? '-' }}</td>
                        <td class="px-4 py-3">{{ $c->producto?->marca }} - {{ $c->producto?->tipo }} {{ $c->producto?->contenido }}</td>
                        <td class="px-4 py-3">{{ $c->cantidad }}</td>
                        <td class="px-4 py-3">${{ number_format((float)$c->precio_unitario, 2, ',', '.') }}</td>
                        <td class="px-4 py-3 font-semibold">${{ number_format($sub, 2, ',', '.') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-8 text-center text-slate-500">
                            Este lote no tiene compras asociadas.
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div>
        <div class="text-lg font-bold mb-3">Pagos del lote</div>
        <div class="overflow-x-auto rounded-2xl border">
            <table class="min-w-full bg-white">
                <thead class="bg-slate-50 text-slate-700">
                <tr>
                    <th class="text-left px-4 py-3 text-sm font-semibold">Fecha</th>
                    <th class="text-left px-4 py-3 text-sm font-semibold">Monto</th>
                    <th class="text-left px-4 py-3 text-sm font-semibold">Observación</th>
                </tr>
                </thead>
                <tbody>
                @forelse($lote->pagos as $pago)
                    <tr class="border-t hover:bg-slate-50">
                        <td class="px-4 py-3">{{ $pago->fecha?->format('d/m/Y') }}</td>
                        <td class="px-4 py-3 font-semibold">${{ number_format((float)$pago->monto, 2, ',', '.') }}</td>
                        <td class="px-4 py-3">{{ $pago->observacion ?: '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="px-4 py-8 text-center text-slate-500">
                            No hay pagos registrados.
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection