@extends('layouts.admin')

@section('title', 'Liquidaciones - Peluquería TOP')
@section('h1', 'Liquidaciones')
@section('sub', 'Pagos registrados a colaboradoras.')

@section('content')

    @if(session('ok'))
        <div class="mb-4 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-green-800">
            {{ session('ok') }}
        </div>
    @endif

    <div class="flex justify-end mb-6">
        <a href="{{ route('liquidaciones.create') }}"
           class="rounded-xl bg-slate-900 text-white px-4 py-2 hover:bg-slate-800">
            + Nueva liquidación
        </a>
    </div>

    <div class="overflow-x-auto rounded-2xl border">
        <table class="min-w-full bg-white">
            <thead class="bg-slate-50 text-slate-700">
            <tr>
                <th class="text-left px-4 py-3 text-sm font-semibold">Fecha pago</th>
                <th class="text-left px-4 py-3 text-sm font-semibold">Colaboradora</th>
                <th class="text-left px-4 py-3 text-sm font-semibold">Valor hora</th>
                <th class="text-left px-4 py-3 text-sm font-semibold">Horas normales</th>
                <th class="text-left px-4 py-3 text-sm font-semibold">Horas extras</th>
                <th class="text-left px-4 py-3 text-sm font-semibold">Total pagado</th>
                <th class="text-right px-4 py-3 text-sm font-semibold">Acciones</th>
            </tr>
            </thead>
            <tbody>
            @forelse($liquidaciones as $l)
                <tr class="border-t hover:bg-slate-50">
                    <td class="px-4 py-3">{{ $l->fecha_pago?->format('d/m/Y') }}</td>
                    <td class="px-4 py-3">{{ $l->colaboradora?->apellido }} {{ $l->colaboradora?->nombre }}</td>
                    <td class="px-4 py-3">${{ number_format((float)$l->valor_hora, 2, ',', '.') }}</td>
                    <td class="px-4 py-3">{{ number_format($l->minutos_normales / 60, 2, ',', '.') }}</td>
                    <td class="px-4 py-3">{{ number_format($l->minutos_extras / 60, 2, ',', '.') }}</td>
                    <td class="px-4 py-3 font-bold">${{ number_format((float)$l->total_pagado, 2, ',', '.') }}</td>
                    <td class="px-4 py-3 text-right">
                        <a href="{{ route('liquidaciones.show', $l) }}"
                           class="rounded-lg border px-3 py-1 hover:bg-white">
                            🔎
                        </a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="px-4 py-8 text-center text-slate-500">
                        No hay liquidaciones registradas.
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $liquidaciones->links() }}
    </div>

@endsection