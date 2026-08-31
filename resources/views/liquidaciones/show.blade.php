@extends('layouts.admin')

@section('title', 'Detalle de liquidación - FN Peluquería')
@section('h1', 'Detalle de Liquidación')
@section('sub', 'Resumen del pago registrado.')

@section('content')

    @php
        $horasNormales = (float)$liquidacion->minutos_normales / 60;
        $horasExtras = (float)$liquidacion->minutos_extras / 60;
        $totalHoras = (float)$liquidacion->monto_horas_normales + (float)$liquidacion->monto_horas_extras;
        $subtotalSinDescuentos = $totalHoras + (float)$liquidacion->monto_comision;
    @endphp

    <div class="fn-section-card">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <div class="text-sm text-slate-600">Colaboradora</div>
                <div class="text-2xl font-bold">
                    {{ $liquidacion->colaboradora?->apellido }} {{ $liquidacion->colaboradora?->nombre }}
                </div>

                <div class="mt-4 text-sm text-slate-600">Fecha pago</div>
                <div class="font-semibold">{{ $liquidacion->fecha_pago?->format('d/m/Y') }}</div>

                <div class="mt-4 text-sm text-slate-600">Valor hora</div>
                <div class="font-semibold">
                    ${{ number_format((float)$liquidacion->valor_hora, 2, ',', '.') }}
                </div>
            </div>

            <div class="fn-stat-card fn-stat-card-primary">
                <div class="text-sm text-slate-200">Total pagado</div>
                <div class="text-4xl font-extrabold mt-1">
                    ${{ number_format((float)$liquidacion->total_pagado, 2, ',', '.') }}
                </div>

                <div class="mt-4 text-sm text-slate-300">
                    Total antes de productos a costo:
                    <span class="font-bold text-white">
                        ${{ number_format($subtotalSinDescuentos, 2, ',', '.') }}
                    </span>
                </div>

                <div class="text-sm text-slate-300">
                    Productos a costo descontados:
                    <span class="font-bold text-red-300">
                        -${{ number_format((float)$liquidacion->monto_productos_costo, 2, ',', '.') }}
                    </span>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mt-6">
            <div class="fn-stat-card bg-slate-50">
                <div class="text-sm text-slate-600">Horas normales</div>
                <div class="text-xl font-bold mt-1">
                    {{ number_format($horasNormales, 2, ',', '.') }} hs
                </div>
                <div class="text-sm mt-2">
                    ${{ number_format((float)$liquidacion->monto_horas_normales, 2, ',', '.') }}
                </div>
            </div>

            <div class="fn-stat-card bg-slate-50">
                <div class="text-sm text-slate-600">Horas extras</div>
                <div class="text-xl font-bold mt-1">
                    {{ number_format($horasExtras, 2, ',', '.') }} hs
                </div>
                <div class="text-sm mt-2">
                    ${{ number_format((float)$liquidacion->monto_horas_extras, 2, ',', '.') }}
                </div>
            </div>

            <div class="fn-stat-card bg-slate-50">
                <div class="text-sm text-slate-600">Total horas</div>
                <div class="text-xl font-bold mt-1">
                    ${{ number_format($totalHoras, 2, ',', '.') }}
                </div>
                <div class="text-xs text-slate-500 mt-1">
                    Normales + extras
                </div>
            </div>

            <div class="fn-stat-card bg-slate-50">
                <div class="text-sm text-slate-600">Comisión</div>
                <div class="text-xl font-bold mt-1">
                    ${{ number_format((float)$liquidacion->monto_comision, 2, ',', '.') }}
                </div>
            </div>

            <div class="rounded-2xl border bg-red-50 border-red-200 p-4">
                <div class="text-sm text-red-700">Productos a costo</div>
                <div class="text-xl font-bold mt-1 text-red-800">
                    -${{ number_format((float)$liquidacion->monto_productos_costo, 2, ',', '.') }}
                </div>
                <div class="text-xs text-red-600 mt-1">
                    Se descuenta de esta liquidación.
                </div>
            </div>

            <div class="rounded-2xl border bg-green-50 border-green-200 p-4">
                <div class="text-sm text-green-700">Total pagado final</div>
                <div class="text-xl font-bold mt-1 text-green-800">
                    ${{ number_format((float)$liquidacion->total_pagado, 2, ',', '.') }}
                </div>
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
               class="fn-primary-action">
                Volver
            </a>
        </div>
    </div>

@endsection
