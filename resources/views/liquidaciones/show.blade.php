@extends('layouts.admin')

@section('title', 'Detalle Liquidación - Peluquería TOP')
@section('h1', 'Detalle de Liquidación')
@section('sub', 'Resumen del pago registrado.')

@section('content')

    <div class="rounded-2xl border bg-white p-5">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <div class="text-sm text-slate-600">Colaboradora</div>
                <div class="text-2xl font-bold">{{ $liquidacion->colaboradora?->apellido }} {{ $liquidacion->colaboradora?->nombre }}</div>

                <div class="mt-4 text-sm text-slate-600">Fecha pago</div>
                <div class="font-semibold">{{ $liquidacion->fecha_pago?->format('d/m/Y') }}</div>

                <div class="mt-4 text-sm text-slate-600">Valor hora</div>
                <div class="font-semibold">${{ number_format((float)$liquidacion->valor_hora, 2, ',', '.') }}</div>
            </div>

            <div class="rounded-2xl border bg-slate-900 text-white p-4">
                <div class="text-sm text-slate-200">Total pagado</div>
                <div class="text-4xl font-extrabold mt-1">${{ number_format((float)$liquidacion->total_pagado, 2, ',', '.') }}</div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mt-6">
            <div class="rounded-2xl border bg-slate-50 p-4">
                <div class="text-sm text-slate-600">Horas normales</div>
                <div class="text-xl font-bold mt-1">{{ number_format($liquidacion->minutos_normales / 60, 2, ',', '.') }} hs</div>
                <div class="text-sm mt-2">${{ number_format((float)$liquidacion->monto_horas_normales, 2, ',', '.') }}</div>
            </div>

            <div class="rounded-2xl border bg-slate-50 p-4">
                <div class="text-sm text-slate-600">Horas extras</div>
                <div class="text-xl font-bold mt-1">{{ number_format($liquidacion->minutos_extras / 60, 2, ',', '.') }} hs</div>
                <div class="text-sm mt-2">${{ number_format((float)$liquidacion->monto_horas_extras, 2, ',', '.') }}</div>
            </div>

            <div class="rounded-2xl border bg-slate-50 p-4">
                <div class="text-sm text-slate-600">Comisión</div>
                <div class="text-xl font-bold mt-1">${{ number_format((float)$liquidacion->monto_comision, 2, ',', '.') }}</div>
            </div>

            <div class="rounded-2xl border bg-slate-50 p-4">
                <div class="text-sm text-slate-600">Productos a costo</div>
                <div class="text-xl font-bold mt-1">${{ number_format((float)$liquidacion->monto_productos_costo, 2, ',', '.') }}</div>
            </div>
        </div>

        @if($liquidacion->observaciones)
            <div class="mt-6 rounded-2xl border bg-slate-50 p-4">
                <div class="text-sm font-semibold text-slate-700 mb-1">Observaciones</div>
                <div>{{ $liquidacion->observaciones }}</div>
            </div>
        @endif

        <div class="mt-6">
            <a href="{{ route('liquidaciones.index') }}"
               class="rounded-xl bg-slate-900 text-white px-4 py-2 hover:bg-slate-800 inline-block">
                Volver
            </a>
        </div>
    </div>

@endsection