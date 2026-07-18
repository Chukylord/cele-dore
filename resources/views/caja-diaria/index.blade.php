@extends('layouts.admin')

@section('title', 'Caja diaria - Vir Tisone Studio')
@section('h1', 'Caja diaria')
@section('sub', 'Cobros del día, gastos rápidos y cierre de efectivo.')

@section('content')

@php
    $esAdmin = auth()->user()->esAdmin();
@endphp

@if(session('ok'))
    <div class="mb-4 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-green-800">
        {{ session('ok') }}
    </div>
@endif

@if($errors->any())
    <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-red-800">
        <div class="font-semibold mb-1">Hay errores:</div>
        <ul class="list-disc pl-5">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="rounded-2xl border bg-white p-4 mb-5">
    <form method="GET"
          action="{{ route('caja-diaria.index') }}"
          class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
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
            Ingresá con cuánto efectivo comienza la caja del
            {{ \Carbon\Carbon::parse($fecha)->format('d/m/Y') }}.
        </div>

        <form method="POST"
              action="{{ route('caja-diaria.abrir') }}"
              class="mt-5 grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
            @csrf
            <input type="hidden" name="fecha" value="{{ $fecha }}">

            <div class="md:col-span-2">
                <label class="text-sm font-semibold text-slate-200">Caja inicial *</label>
                <input type="number"
                       step="0.01"
                       min="0"
                       name="caja_inicial"
                       value="{{ old('caja_inicial') }}"
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
    <div class="mb-5 flex flex-col md:flex-row md:items-start md:justify-between gap-4">
        <div>
            <div class="text-lg font-bold">Caja del {{ $caja->fecha->format('d/m/Y') }}</div>
            <div class="mt-2">
                @if($caja->estaAbierta())
                    <span class="rounded-full bg-green-100 text-green-700 px-3 py-1 text-xs font-bold">
                        ABIERTA
                    </span>
                @else
                    <span class="rounded-full bg-slate-900 text-white px-3 py-1 text-xs font-bold">
                        CERRADA
                    </span>
                @endif
            </div>
        </div>

        <div class="text-sm text-slate-500 md:text-right">
            @if($caja->fecha_apertura)
                <div>
                    Apertura: {{ $caja->fecha_apertura->format('d/m/Y H:i') }}
                    @if($caja->usuarioApertura)
                        · {{ $caja->usuarioApertura->name }}
                    @endif
                </div>
            @endif

            @if($caja->fecha_cierre)
                <div class="mt-1">
                    Cierre: {{ $caja->fecha_cierre->format('d/m/Y H:i') }}
                    @if($caja->usuarioCierre)
                        · {{ $caja->usuarioCierre->name }}
                    @endif
                </div>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="rounded-2xl border bg-white p-4">
            <div class="text-sm text-slate-500">Caja inicial</div>
            <div class="text-2xl font-bold">
                ${{ number_format((float)$caja->caja_inicial, 2, ',', '.') }}
            </div>
        </div>

        <div class="rounded-2xl border bg-green-50 p-4">
            <div class="text-sm text-green-700">Cobrado en efectivo</div>
            <div class="text-2xl font-bold text-green-700">
                ${{ number_format((float)$cobrosEfectivo, 2, ',', '.') }}
            </div>
        </div>

        <div class="rounded-2xl border bg-slate-900 p-4 text-white">
            <div class="text-sm text-slate-300">Efectivo esperado</div>
            <div class="text-2xl font-bold">
                ${{ number_format((float)$efectivoEsperado, 2, ',', '.') }}
            </div>
            <div class="text-xs text-slate-400 mt-1">
                Caja inicial + cobros en efectivo - gastos en efectivo.
            </div>
        </div>

        <div class="rounded-2xl border bg-white p-4">
            <div class="text-sm text-slate-500">Diferencia</div>
            @if($caja->estaCerrada())
                @php $diferenciaCaja = (float)$caja->diferencia; @endphp
                <div class="text-2xl font-bold {{ abs($diferenciaCaja) < 0.01 ? 'text-green-700' : 'text-red-700' }}">
                    ${{ number_format($diferenciaCaja, 2, ',', '.') }}
                </div>
            @else
                <div class="text-2xl font-bold text-slate-400">Pendiente</div>
            @endif
        </div>
    </div>
@endif

<div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
    <div class="rounded-2xl border bg-green-50 p-4">
        <div class="text-sm text-green-700">Efectivo cobrado</div>
        <div class="text-2xl font-bold text-green-800">
            ${{ number_format((float)$cobrosEfectivo, 2, ',', '.') }}
        </div>
    </div>

    <div class="rounded-2xl border bg-blue-50 p-4">
        <div class="text-sm text-blue-700">Transferencia cobrada</div>
        <div class="text-2xl font-bold text-blue-800">
            ${{ number_format((float)$cobrosTransferencia, 2, ',', '.') }}
        </div>
    </div>

    <div class="rounded-2xl border bg-purple-50 p-4">
        <div class="text-sm text-purple-700">Tarjeta cobrada</div>
        <div class="text-2xl font-bold text-purple-800">
            ${{ number_format((float)$cobrosTarjeta, 2, ',', '.') }}
        </div>
        <div class="text-xs text-purple-700 mt-1">Incluye recargos cobrados.</div>
    </div>

    <div class="rounded-2xl border bg-slate-900 p-4 text-white">
        <div class="text-sm text-slate-300">Total cobrado</div>
        <div class="text-2xl font-bold">
            ${{ number_format((float)$totalCobrado, 2, ',', '.') }}
        </div>
    </div>
</div>

@if($caja && $caja->estaAbierta())
    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6 mb-6">
        <div class="rounded-2xl border bg-amber-50 p-5">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <div class="text-xl font-bold text-slate-900">Registrar gasto del día</div>
                    <div class="text-sm text-slate-600 mt-1">
                        Las empleadas pueden cargar el gasto desde acá sin acceder al historial general.
                    </div>
                </div>
                <span class="text-3xl">🧾</span>
            </div>

            <form method="POST"
                  action="{{ route('caja-diaria.gastos.store') }}"
                  class="mt-5 grid grid-cols-1 md:grid-cols-2 gap-4">
                @csrf
                <input type="hidden" name="fecha" value="{{ $fecha }}">

                <div>
                    <label class="text-sm font-semibold text-slate-700">Concepto *</label>
                    <input type="text"
                           name="categoria"
                           value="{{ old('categoria') }}"
                           placeholder="Ej: limpieza, librería..."
                           class="mt-1 w-full rounded-xl border-slate-300 focus:border-slate-500 focus:ring-slate-500">
                </div>

                <div>
                    <label class="text-sm font-semibold text-slate-700">Importe *</label>
                    <input type="number"
                           step="0.01"
                           min="0.01"
                           name="monto"
                           value="{{ old('monto') }}"
                           placeholder="Ej: 8000"
                           class="mt-1 w-full rounded-xl border-slate-300 focus:border-slate-500 focus:ring-slate-500">
                </div>

                <div>
                    <label class="text-sm font-semibold text-slate-700">Medio de pago *</label>
                    <select name="medio_pago"
                            class="mt-1 w-full rounded-xl border-slate-300 focus:border-slate-500 focus:ring-slate-500">
                        <option value="efectivo" {{ old('medio_pago', 'efectivo') === 'efectivo' ? 'selected' : '' }}>
                            Efectivo
                        </option>
                        <option value="transferencia" {{ old('medio_pago') === 'transferencia' ? 'selected' : '' }}>
                            Transferencia
                        </option>
                    </select>
                </div>

                <div>
                    <label class="text-sm font-semibold text-slate-700">Detalle</label>
                    <input type="text"
                           name="descripcion"
                           value="{{ old('descripcion') }}"
                           placeholder="Observación opcional"
                           class="mt-1 w-full rounded-xl border-slate-300 focus:border-slate-500 focus:ring-slate-500">
                </div>

                <div class="md:col-span-2 flex justify-end">
                    <button class="rounded-xl bg-amber-600 text-white px-5 py-3 font-bold hover:bg-amber-700">
                        Registrar gasto
                    </button>
                </div>
            </form>
        </div>

        <div class="rounded-2xl border bg-slate-50 p-5">
            <div class="text-xl font-bold text-slate-900">Cerrar caja</div>
            <div class="text-sm text-slate-600 mt-1">
                Puede cerrarla la administradora o cualquiera de las empleadas autorizadas.
            </div>

            <form method="POST"
                  action="{{ route('caja-diaria.cerrar', $caja) }}"
                  class="mt-5">
                @csrf
                @method('PATCH')

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 items-end">
                    <div>
                        <label class="text-sm font-semibold text-slate-700">Efectivo contado *</label>
                        <input id="efectivo_contado"
                               type="number"
                               step="0.01"
                               min="0"
                               name="efectivo_contado"
                               value="{{ old('efectivo_contado') }}"
                               placeholder="Ej: {{ number_format((float)$efectivoEsperado, 2, '.', '') }}"
                               class="mt-1 w-full rounded-xl border-slate-300 focus:border-slate-500 focus:ring-slate-500">
                    </div>

                    <div class="rounded-xl border bg-white p-3">
                        <div class="text-sm text-slate-500">Diferencia estimada</div>
                        <div id="diferencia_preview" class="text-2xl font-bold text-slate-400">
                            Pendiente
                        </div>
                    </div>
                </div>

                <div class="mt-4">
                    <label class="text-sm font-semibold text-slate-700">Observaciones</label>
                    <textarea name="observaciones"
                              rows="3"
                              placeholder="Ej: sobró o faltó efectivo, aclaraciones..."
                              class="mt-1 w-full rounded-xl border-slate-300 focus:border-slate-500 focus:ring-slate-500">{{ old('observaciones') }}</textarea>
                </div>

                <div class="mt-4 flex justify-end">
                    <button class="rounded-xl bg-slate-900 text-white px-5 py-3 font-bold hover:bg-slate-800"
                            onclick="return confirm('¿Cerrar la caja diaria?');">
                        Cerrar caja
                    </button>
                </div>
            </form>
        </div>
    </div>
@endif

@if($esAdmin)
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="rounded-2xl border bg-red-50 p-4">
            <div class="text-sm text-red-700">Gastos en efectivo</div>
            <div class="text-2xl font-bold text-red-800">
                ${{ number_format((float)$gastosEfectivoActual, 2, ',', '.') }}
            </div>
        </div>

        <div class="rounded-2xl border bg-orange-50 p-4">
            <div class="text-sm text-orange-700">Gastos por transferencia</div>
            <div class="text-2xl font-bold text-orange-800">
                ${{ number_format((float)$gastosTransferenciaActual, 2, ',', '.') }}
            </div>
        </div>

        <div class="rounded-2xl border bg-slate-900 p-4 text-white">
            <div class="text-sm text-slate-300">Gastos del día</div>
            <div class="text-2xl font-bold">
                ${{ number_format((float)$gastosTotalActual, 2, ',', '.') }}
            </div>
        </div>
    </div>
@endif

<div class="rounded-2xl border bg-white overflow-hidden mb-6">
    <div class="px-4 py-3 border-b bg-slate-50">
        <div class="font-bold">Cobros registrados</div>
        <div class="text-sm text-slate-600">
            Cada pago parcial aparece en la fecha y medio en que fue realmente cobrado.
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full bg-white">
            <thead class="bg-white text-slate-700">
            <tr>
                <th class="text-left px-4 py-3 text-sm font-semibold">Hora</th>
                <th class="text-left px-4 py-3 text-sm font-semibold">Venta</th>
                <th class="text-left px-4 py-3 text-sm font-semibold">Cliente</th>
                <th class="text-left px-4 py-3 text-sm font-semibold">Método</th>
                <th class="text-left px-4 py-3 text-sm font-semibold">Base</th>
                <th class="text-left px-4 py-3 text-sm font-semibold">Recargo</th>
                <th class="text-left px-4 py-3 text-sm font-semibold">Cobrado</th>
            </tr>
            </thead>

            <tbody>
            @forelse($movimientos as $movimiento)
                @php
                    $ventaMovimiento = $movimiento['venta'];
                    $clienteMovimiento = $ventaMovimiento?->cliente
                        ? trim($ventaMovimiento->cliente->nombre . ' ' . $ventaMovimiento->cliente->apellido)
                        : ($ventaMovimiento?->clienteColaboradora
                            ? '(Colab) ' . trim($ventaMovimiento->clienteColaboradora->nombre . ' ' . $ventaMovimiento->clienteColaboradora->apellido)
                            : '-');
                    $claseMetodo = match($movimiento['metodo_pago']) {
                        'efectivo' => 'bg-green-100 text-green-700',
                        'transferencia' => 'bg-blue-100 text-blue-700',
                        'tarjeta' => 'bg-purple-100 text-purple-700',
                        default => 'bg-slate-100 text-slate-700',
                    };
                @endphp

                <tr class="border-t hover:bg-slate-50">
                    <td class="px-4 py-3 whitespace-nowrap">
                        {{ $movimiento['fecha_pago']?->format('H:i') }}
                    </td>
                    <td class="px-4 py-3">
                        @if($ventaMovimiento)
                            <a href="{{ route('ventas.show', $ventaMovimiento) }}"
                               class="font-semibold text-blue-700 hover:underline">
                                #{{ $ventaMovimiento->id }}
                            </a>
                        @else
                            -
                        @endif
                    </td>
                    <td class="px-4 py-3">{{ $clienteMovimiento }}</td>
                    <td class="px-4 py-3">
                        <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $claseMetodo }}">
                            {{ ucfirst((string)$movimiento['metodo_pago']) }}
                        </span>
                    </td>
                    <td class="px-4 py-3">
                        ${{ number_format((float)$movimiento['monto_base'], 2, ',', '.') }}
                    </td>
                    <td class="px-4 py-3">
                        ${{ number_format((float)$movimiento['recargo'], 2, ',', '.') }}
                    </td>
                    <td class="px-4 py-3 font-bold">
                        ${{ number_format((float)$movimiento['monto'], 2, ',', '.') }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="px-4 py-8 text-center text-slate-500">
                        No hay cobros registrados para esta fecha.
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

@if($esAdmin)
    <div class="rounded-2xl border bg-white overflow-hidden">
        <div class="px-4 py-3 border-b bg-slate-50">
            <div class="font-bold">Gastos registrados en la caja</div>
            <div class="text-sm text-slate-600">Esta información solo es visible para administradoras.</div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full bg-white">
                <thead class="bg-white text-slate-700">
                <tr>
                    <th class="text-left px-4 py-3 text-sm font-semibold">Hora</th>
                    <th class="text-left px-4 py-3 text-sm font-semibold">Concepto</th>
                    <th class="text-left px-4 py-3 text-sm font-semibold">Detalle</th>
                    <th class="text-left px-4 py-3 text-sm font-semibold">Medio</th>
                    <th class="text-left px-4 py-3 text-sm font-semibold">Registró</th>
                    <th class="text-left px-4 py-3 text-sm font-semibold">Importe</th>
                </tr>
                </thead>

                <tbody>
                @forelse($gastos as $gasto)
                    <tr class="border-t hover:bg-slate-50">
                        <td class="px-4 py-3 whitespace-nowrap">
                            {{ $gasto->created_at?->format('H:i') }}
                        </td>
                        <td class="px-4 py-3 font-semibold">{{ $gasto->categoria }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ $gasto->descripcion ?: '-' }}</td>
                        <td class="px-4 py-3">{{ ucfirst((string)$gasto->medio_pago) }}</td>
                        <td class="px-4 py-3">{{ $gasto->usuario?->name ?? '-' }}</td>
                        <td class="px-4 py-3 font-bold text-red-700">
                            - ${{ number_format((float)$gasto->monto, 2, ',', '.') }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-8 text-center text-slate-500">
                            No hay gastos registrados para esta fecha.
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endif

@if($caja && $caja->estaAbierta())
<script>
document.addEventListener('DOMContentLoaded', function () {
    const esperado = Number(@json((float)$efectivoEsperado));
    const input = document.getElementById('efectivo_contado');
    const preview = document.getElementById('diferencia_preview');

    if (!input || !preview) return;

    const money = numero => '$' + Number(numero || 0).toLocaleString('es-AR', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    });

    const actualizar = () => {
        if (input.value === '') {
            preview.textContent = 'Pendiente';
            preview.className = 'text-2xl font-bold text-slate-400';
            return;
        }

        const diferencia = Number(input.value || 0) - esperado;
        preview.textContent = money(diferencia);
        preview.className = 'text-2xl font-bold ' + (Math.abs(diferencia) < 0.01
            ? 'text-green-700'
            : 'text-red-700');
    };

    input.addEventListener('input', actualizar);
    actualizar();
});
</script>
@endif

@endsection
