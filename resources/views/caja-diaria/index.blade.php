@extends('layouts.admin')

@section('title', 'Caja diaria - Vir Tisone Studio')
@section('h1', 'Caja diaria')
@section('sub', 'Inicio, control y cierre del efectivo del día.')

@section('content')

@if(session('ok'))
    <div class="mb-4 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-green-800">
        {{ session('ok') }}
    </div>
@endif

@if($errors->any())
    <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-red-800">
        <div class="font-semibold mb-1">Hay errores:</div>
        <ul class="list-disc pl-5">
            @foreach($errors->all() as $e)
                <li>{{ $e }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="rounded-2xl border bg-white p-4 mb-5">
    <form method="GET" action="{{ route('caja-diaria.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
        <div>
            <label class="text-sm font-semibold text-slate-700">Fecha</label>
            <input type="date"
                   name="fecha"
                   value="{{ $fecha }}"
                   class="mt-1 w-full rounded-xl border-slate-300 focus:border-slate-500 focus:ring-slate-500">
        </div>

        <div>
            <button class="rounded-xl bg-slate-900 text-white px-4 py-2 hover:bg-slate-800">
                Ver caja
            </button>
        </div>
    </form>
</div>

@if(!$caja)
    <div class="rounded-2xl border bg-slate-900 text-white p-6 mb-6">
        <div class="text-2xl font-bold">Iniciar caja del día</div>
        <div class="text-slate-300 mt-1">
            Cargá con cuánto efectivo empieza la caja para el día {{ \Carbon\Carbon::parse($fecha)->format('d/m/Y') }}.
        </div>

        <form method="POST" action="{{ route('caja-diaria.abrir') }}" class="mt-5 grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
            @csrf

            <input type="hidden" name="fecha" value="{{ $fecha }}">

            <div class="md:col-span-2">
                <label class="text-sm font-semibold text-slate-200">Caja inicial *</label>
                <input type="number"
                       step="0.01"
                       min="0"
                       name="caja_inicial"
                       placeholder="Ej: 50000"
                       class="mt-1 w-full rounded-xl border-slate-700 bg-slate-800 text-white focus:border-white focus:ring-white">
            </div>

            <div>
                <button class="w-full rounded-xl bg-white text-slate-900 px-4 py-3 font-bold hover:bg-slate-100">
                    Iniciar caja
                </button>
            </div>
        </form>
    </div>
@else
    <div class="mb-5 flex flex-col md:flex-row md:items-center md:justify-between gap-3">
        <div>
            <div class="text-lg font-bold">
                Caja del {{ $caja->fecha->format('d/m/Y') }}
            </div>

            <div class="text-sm text-slate-600">
                @if($caja->estaAbierta())
                    Estado:
                    <span class="rounded-full bg-green-100 text-green-700 px-3 py-1 text-xs font-bold">
                        ABIERTA
                    </span>
                @else
                    Estado:
                    <span class="rounded-full bg-slate-900 text-white px-3 py-1 text-xs font-bold">
                        CERRADA
                    </span>
                @endif
            </div>
        </div>

        <div class="text-sm text-slate-500">
            @if($caja->fecha_apertura)
                Apertura: {{ $caja->fecha_apertura->format('d/m/Y H:i') }}
            @endif

            @if($caja->fecha_cierre)
                <br>Cierre: {{ $caja->fecha_cierre->format('d/m/Y H:i') }}
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-5">
        <div class="rounded-2xl border bg-white p-4">
            <div class="text-sm text-slate-500">Caja inicial</div>
            <div class="text-2xl font-bold">
                ${{ number_format((float)$caja->caja_inicial, 2, ',', '.') }}
            </div>
        </div>

        <div class="rounded-2xl border bg-green-50 p-4">
            <div class="text-sm text-green-700">Ventas efectivo</div>
            <div class="text-2xl font-bold text-green-700">
                ${{ number_format((float)$ventasEfectivo, 2, ',', '.') }}
            </div>
        </div>

        <div class="rounded-2xl border bg-slate-900 p-4 text-white">
            <div class="text-sm text-slate-300">Efectivo esperado</div>
            <div class="text-2xl font-bold">
                ${{ number_format((float)$efectivoEsperado, 2, ',', '.') }}
            </div>
        </div>

        <div class="rounded-2xl border bg-white p-4">
            <div class="text-sm text-slate-500">Diferencia</div>

            @if($caja->estaCerrada())
                @php
                    $dif = (float)$caja->diferencia;
                @endphp

                <div class="text-2xl font-bold {{ abs($dif) < 0.01 ? 'text-green-700' : 'text-red-700' }}">
                    ${{ number_format($dif, 2, ',', '.') }}
                </div>
            @else
                <div class="text-2xl font-bold text-slate-400">
                    Pendiente
                </div>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
        <div class="rounded-2xl border bg-blue-50 p-4">
            <div class="text-sm text-blue-700">Transferencia del día</div>
            <div class="text-2xl font-bold text-blue-700">
                ${{ number_format((float)$ventasTransferencia, 2, ',', '.') }}
            </div>
            <div class="text-xs text-blue-700 mt-1">
                Informativo. No suma al efectivo físico.
            </div>
        </div>

        <div class="rounded-2xl border bg-purple-50 p-4">
            <div class="text-sm text-purple-700">Tarjeta del día</div>
            <div class="text-2xl font-bold text-purple-700">
                ${{ number_format((float)$ventasTarjeta, 2, ',', '.') }}
            </div>
            <div class="text-xs text-purple-700 mt-1">
                Informativo. No suma al efectivo físico.
            </div>
        </div>
    </div>

    @if($caja->estaAbierta())
        <div class="rounded-2xl border bg-slate-50 p-5 mb-6">
            <div class="text-xl font-bold text-slate-900">Cerrar caja</div>
            <div class="text-sm text-slate-600 mt-1">
                Contá el efectivo real que hay en caja. Si coincide con el esperado, la diferencia queda en $0,00.
            </div>

            <form method="POST" action="{{ route('caja-diaria.cerrar', $caja) }}" class="mt-5">
                @csrf
                @method('PATCH')

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
                    <div>
                        <label class="text-sm font-semibold text-slate-700">Efectivo contado *</label>
                        <input id="efectivo_contado"
                               type="number"
                               step="0.01"
                               min="0"
                               name="efectivo_contado"
                               placeholder="Ej: {{ number_format((float)$efectivoEsperado, 2, '.', '') }}"
                               class="mt-1 w-full rounded-xl border-slate-300 focus:border-slate-500 focus:ring-slate-500">
                    </div>

                    <div class="rounded-xl border bg-white p-3">
                        <div class="text-sm text-slate-500">Diferencia estimada</div>
                        <div id="diferencia_preview" class="text-2xl font-bold text-slate-400">
                            $0,00
                        </div>
                    </div>

                    <div>
                        <button class="w-full rounded-xl bg-slate-900 text-white px-4 py-3 font-bold hover:bg-slate-800"
                                onclick="return confirm('¿Cerrar la caja diaria? Luego quedará guardada.');">
                            Cerrar caja
                        </button>
                    </div>
                </div>

                <div class="mt-4">
                    <label class="text-sm font-semibold text-slate-700">Observaciones</label>
                    <textarea name="observaciones"
                              rows="3"
                              placeholder="Ej: sobró/faltó efectivo, retiro de caja, aclaraciones..."
                              class="mt-1 w-full rounded-xl border-slate-300 focus:border-slate-500 focus:ring-slate-500"></textarea>
                </div>
            </form>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const efectivoEsperado = Number(@json((float)$efectivoEsperado));
                const input = document.getElementById('efectivo_contado');
                const preview = document.getElementById('diferencia_preview');

                function money(n) {
                    n = Number(n || 0);
                    return '$' + n.toLocaleString('es-AR', {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    });
                }

                function calcular() {
                    const contado = Number(input.value || 0);
                    const diferencia = contado - efectivoEsperado;

                    preview.textContent = money(diferencia);

                    preview.classList.remove('text-slate-400', 'text-green-700', 'text-red-700');

                    if (Math.abs(diferencia) < 0.01) {
                        preview.classList.add('text-green-700');
                    } else {
                        preview.classList.add('text-red-700');
                    }
                }

                if (input) {
                    input.addEventListener('input', calcular);
                    calcular();
                }
            });
        </script>
    @else
        <div class="rounded-2xl border bg-slate-50 p-5 mb-6">
            <div class="text-xl font-bold text-slate-900">Caja cerrada</div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
                <div class="rounded-xl border bg-white p-4">
                    <div class="text-sm text-slate-500">Efectivo esperado</div>
                    <div class="text-2xl font-bold">
                        ${{ number_format((float)$caja->efectivo_esperado, 2, ',', '.') }}
                    </div>
                </div>

                <div class="rounded-xl border bg-white p-4">
                    <div class="text-sm text-slate-500">Efectivo contado</div>
                    <div class="text-2xl font-bold">
                        ${{ number_format((float)$caja->efectivo_contado, 2, ',', '.') }}
                    </div>
                </div>

                <div class="rounded-xl border bg-white p-4">
                    <div class="text-sm text-slate-500">Resultado</div>

                    @php
                        $dif = (float)$caja->diferencia;
                    @endphp

                    <div class="text-2xl font-bold {{ abs($dif) < 0.01 ? 'text-green-700' : 'text-red-700' }}">
                        ${{ number_format($dif, 2, ',', '.') }}
                    </div>
                </div>
            </div>

            @if($caja->observaciones)
                <div class="mt-4 rounded-xl border bg-white p-4">
                    <div class="text-sm font-semibold text-slate-700 mb-1">Observaciones</div>
                    <div class="whitespace-pre-wrap text-slate-700">{{ $caja->observaciones }}</div>
                </div>
            @endif
        </div>
    @endif
@endif

<div class="rounded-2xl border bg-white overflow-hidden">
    <div class="px-4 py-3 bg-slate-50 border-b">
        <div class="font-bold text-slate-900">Detalle de ventas en efectivo</div>
        <div class="text-sm text-slate-600">
            Solo ventas pagadas en efectivo de la fecha seleccionada.
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full bg-white">
            <thead class="bg-white text-slate-700">
            <tr>
                <th class="text-left px-4 py-3 text-sm font-semibold">Hora</th>
                <th class="text-left px-4 py-3 text-sm font-semibold">Cliente</th>
                <th class="text-left px-4 py-3 text-sm font-semibold">Vendedora</th>
                <th class="text-left px-4 py-3 text-sm font-semibold">Total</th>
                <th class="text-right px-4 py-3 text-sm font-semibold">Ver</th>
            </tr>
            </thead>

            <tbody>
            @forelse($ventasEfectivoDetalle as $venta)
                <tr class="border-t hover:bg-slate-50">
                    <td class="px-4 py-3 whitespace-nowrap">
                        {{ $venta->fecha?->format('H:i') }}
                    </td>

                    <td class="px-4 py-3">
                        @if($venta->cliente)
                            {{ $venta->cliente->nombre }} {{ $venta->cliente->apellido }}
                        @elseif($venta->clienteColaboradora)
                            (Colab) {{ $venta->clienteColaboradora->nombre }} {{ $venta->clienteColaboradora->apellido }}
                        @else
                            -
                        @endif
                    </td>

                    <td class="px-4 py-3">
                        {{ $venta->vendedora ? ($venta->vendedora->nombre.' '.$venta->vendedora->apellido) : '-' }}
                    </td>

                    <td class="px-4 py-3 font-bold">
                        ${{ number_format((float)$venta->total, 2, ',', '.') }}
                    </td>

                    <td class="px-4 py-3 text-right">
                        <a href="{{ route('ventas.show', $venta) }}"
                           class="rounded-lg border px-3 py-1 hover:bg-white">
                            🔎
                        </a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="px-4 py-8 text-center text-slate-500">
                        No hay ventas en efectivo para esta fecha.
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection