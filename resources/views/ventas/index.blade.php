@extends('layouts.admin')

@section('title', 'Ventas - Peluquería TOP')
@section('h1', 'Ventas')
@section('sub', 'Ventas de servicios y productos.')

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

<div class="flex flex-col gap-3 mb-6">
    <form class="grid grid-cols-1 md:grid-cols-6 gap-3 w-full"
          method="GET"
          action="{{ route('ventas.index') }}">

        <div>
            <label class="text-sm font-semibold text-slate-700">Desde</label>
            <input type="date"
                   name="desde"
                   value="{{ $desde }}"
                   class="mt-1 w-full rounded-xl border-slate-300 focus:border-slate-500 focus:ring-slate-500">
        </div>

        <div>
            <label class="text-sm font-semibold text-slate-700">Hasta</label>
            <input type="date"
                   name="hasta"
                   value="{{ $hasta }}"
                   class="mt-1 w-full rounded-xl border-slate-300 focus:border-slate-500 focus:ring-slate-500">
        </div>

        <div>
            <label class="text-sm font-semibold text-slate-700">Método</label>
            <select name="metodo_pago"
                    class="mt-1 w-full rounded-xl border-slate-300 focus:border-slate-500 focus:ring-slate-500">
                <option value="" {{ ($metodo ?? '') === '' ? 'selected' : '' }}>Todos</option>
                <option value="efectivo" {{ ($metodo ?? '') === 'efectivo' ? 'selected' : '' }}>Efectivo</option>
                <option value="transferencia" {{ ($metodo ?? '') === 'transferencia' ? 'selected' : '' }}>Transferencia</option>
                <option value="tarjeta" {{ ($metodo ?? '') === 'tarjeta' ? 'selected' : '' }}>Tarjeta</option>
            </select>
        </div>

        <div>
            <label class="text-sm font-semibold text-slate-700">Estado</label>
            <select name="estado"
                    class="mt-1 w-full rounded-xl border-slate-300 focus:border-slate-500 focus:ring-slate-500">
                <option value="" {{ ($estado ?? '') === '' ? 'selected' : '' }}>Todos</option>
                <option value="pagado" {{ ($estado ?? '') === 'pagado' ? 'selected' : '' }}>Pagado</option>
                <option value="pendiente" {{ ($estado ?? '') === 'pendiente' ? 'selected' : '' }}>Pendiente</option>
            </select>
        </div>

        <div>
            <label class="text-sm font-semibold text-slate-700">Colaboradora</label>
            <select name="colaboradora_id"
                    class="mt-1 w-full rounded-xl border-slate-300 focus:border-slate-500 focus:ring-slate-500">
                <option value="">Todas</option>
                @foreach($colaboradoras as $c)
                    <option value="{{ $c->id }}"
                        {{ (string)($colaboradora_id ?? '') === (string)$c->id ? 'selected' : '' }}>
                        {{ $c->apellido }} {{ $c->nombre }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="flex gap-2">
            <button class="mt-6 w-full rounded-xl bg-slate-900 text-white px-4 py-2 hover:bg-slate-800">
                Filtrar
            </button>

            <a href="{{ route('ventas.index') }}"
               class="mt-6 w-full text-center rounded-xl border px-4 py-2 hover:bg-slate-50">
                Limpiar
            </a>
        </div>
    </form>

    <div class="flex justify-end">
        <a href="{{ route('ventas.create') }}"
           class="rounded-xl bg-slate-900 text-white px-4 py-2 hover:bg-slate-800">
            + Nueva Venta
        </a>
    </div>
</div>

<div class="overflow-x-auto rounded-2xl border">
    <table class="min-w-full bg-white">
        <thead class="bg-slate-50 text-slate-700">
        <tr>
            <th class="text-left px-4 py-3 text-sm font-semibold">Fecha venta</th>
            <th class="text-left px-4 py-3 text-sm font-semibold">Cliente</th>
            <th class="text-left px-4 py-3 text-sm font-semibold">Vendedora</th>
            <th class="text-left px-4 py-3 text-sm font-semibold">Pago</th>
            <th class="text-left px-4 py-3 text-sm font-semibold">Estado</th>
            <th class="text-left px-4 py-3 text-sm font-semibold">Fecha pago</th>
            <th class="text-left px-4 py-3 text-sm font-semibold">Total</th>
            <th class="text-right px-4 py-3 text-sm font-semibold">Acciones</th>
        </tr>
        </thead>

        <tbody>
        @forelse($ventas as $v)
            <tr class="border-t hover:bg-slate-50 align-top">
                <td class="px-4 py-3 whitespace-nowrap">
                    {{ $v->fecha?->format('d/m/Y H:i') }}
                </td>

                <td class="px-4 py-3">
                    @if($v->cliente)
                        {{ $v->cliente->nombre }} {{ $v->cliente->apellido }}
                    @elseif($v->clienteColaboradora)
                        (Colab) {{ $v->clienteColaboradora->nombre }} {{ $v->clienteColaboradora->apellido }}
                    @else
                        -
                    @endif
                </td>

                <td class="px-4 py-3">
                    {{ $v->vendedora ? ($v->vendedora->nombre.' '.$v->vendedora->apellido) : '-' }}
                </td>

                <td class="px-4 py-3">
                    @if($v->pendiente_pago)
                        <span class="rounded-full bg-slate-100 text-slate-700 px-3 py-1 text-xs font-semibold">
                            Sin definir
                        </span>
                    @elseif($v->pagos->isEmpty())
                        <span class="rounded-full bg-slate-100 text-slate-700 px-3 py-1 text-xs font-semibold">
                            {{ ucfirst((string)$v->metodo_pago) }}
                        </span>
                    @else
                        <div class="flex flex-col gap-1">
                            @foreach($v->pagos as $pago)
                                @php
                                    $clase = match($pago->metodo_pago) {
                                        'efectivo' => 'bg-green-100 text-green-700',
                                        'transferencia' => 'bg-blue-100 text-blue-700',
                                        'tarjeta' => 'bg-purple-100 text-purple-700',
                                        default => 'bg-slate-100 text-slate-700',
                                    };
                                @endphp

                                <span class="inline-flex w-fit rounded-full px-3 py-1 text-xs font-semibold {{ $clase }}">
                                    {{ ucfirst($pago->metodo_pago) }}:
                                    ${{ number_format((float)$pago->monto, 2, ',', '.') }}
                                </span>
                            @endforeach
                        </div>
                    @endif
                </td>

                <td class="px-4 py-3">
                    @if($v->pendiente_pago)
                        <span class="px-2 py-1 rounded-lg bg-yellow-50 text-yellow-800 border border-yellow-200 text-sm">
                            Pendiente
                        </span>
                    @else
                        <span class="px-2 py-1 rounded-lg bg-green-50 text-green-700 border border-green-200 text-sm">
                            Pagado
                        </span>
                    @endif
                </td>

                <td class="px-4 py-3 whitespace-nowrap">
                    {{ $v->fecha_pago ? $v->fecha_pago->format('d/m/Y H:i') : '-' }}
                </td>

                <td class="px-4 py-3">
                    @if((float)$v->recargo_tarjeta > 0)
                        <div class="font-semibold">
                            ${{ number_format((float)$v->total, 2, ',', '.') }}
                        </div>
                        <div class="text-xs text-slate-500">
                            Base: ${{ number_format((float)$v->total_base, 2, ',', '.') }}
                        </div>
                    @else
                        <div class="font-semibold">
                            ${{ number_format((float)$v->total, 2, ',', '.') }}
                        </div>
                    @endif
                </td>

                <td class="px-4 py-3">
                    <div class="flex justify-end gap-2 flex-wrap">
                        <a href="{{ route('ventas.show', $v) }}"
                           class="rounded-lg border px-3 py-1 hover:bg-white"
                           title="Ver">
                            🔎
                        </a>

                        @if($v->pendiente_pago)
                            <button type="button"
                                    class="rounded-lg border px-3 py-1 hover:bg-green-50"
                                    data-open-cobro="{{ $v->id }}"
                                    title="Registrar pago">
                                💵
                            </button>
                        @endif

                        <form method="POST"
                              action="{{ route('ventas.destroy', $v) }}"
                              onsubmit="return confirm('¿Eliminar venta?');">
                            @csrf
                            @method('DELETE')

                            <button class="rounded-lg border px-3 py-1 hover:bg-white" title="Eliminar">
                                🗑️
                            </button>
                        </form>
                    </div>
                </td>
            </tr>

            @if($v->pendiente_pago)
                @php
                    $basePendiente = (float)($v->total_base ?: $v->total);
                @endphp

                <tr>
                    <td colspan="8" class="p-0 border-0">
                        <div id="modal-cobro-{{ $v->id }}"
                             class="fixed inset-0 hidden items-center justify-center bg-black/50 p-4 z-50">

                            <div class="w-full max-w-2xl rounded-2xl bg-white shadow-2xl p-5">
                                <div class="flex items-start justify-between gap-4">
                                    <div>
                                        <div class="text-xl font-bold">Registrar pago pendiente</div>
                                        <div class="text-sm text-slate-600 mt-1">
                                            Venta #{{ $v->id }} · Total base:
                                            <strong>${{ number_format($basePendiente, 2, ',', '.') }}</strong>
                                        </div>
                                    </div>

                                    <button type="button"
                                            class="text-2xl text-slate-500 hover:text-slate-900"
                                            data-close-cobro="{{ $v->id }}">
                                        ✖
                                    </button>
                                </div>

                                <form method="POST"
                                      action="{{ route('ventas.marcarPagado', $v) }}"
                                      class="mt-5 form-cobro"
                                      data-total-base="{{ $basePendiente }}">
                                    @csrf
                                    @method('PATCH')

                                    <div>
                                        <label class="text-sm font-semibold text-slate-700">Forma de pago *</label>
                                        <select name="tipo_pago"
                                                class="tipo-pago-cobro mt-1 w-full rounded-xl border-slate-300 focus:border-slate-500 focus:ring-slate-500">
                                            <option value="efectivo">Efectivo</option>
                                            <option value="transferencia">Transferencia</option>
                                            <option value="tarjeta">Tarjeta</option>
                                            <option value="combinado">Pago combinado</option>
                                        </select>
                                    </div>

                                    <div class="resumen-cobro mt-4 rounded-xl border bg-slate-50 p-4">
                                        <div class="flex justify-between gap-3">
                                            <span class="text-slate-600">Total base</span>
                                            <strong>${{ number_format($basePendiente, 2, ',', '.') }}</strong>
                                        </div>

                                        <div class="flex justify-between gap-3 mt-2">
                                            <span class="text-slate-600">Recargo tarjeta</span>
                                            <strong class="recargo-cobro">$0,00</strong>
                                        </div>

                                        <div class="flex justify-between gap-3 mt-2 text-lg">
                                            <span class="font-bold">Total a cobrar</span>
                                            <strong class="total-cobro">
                                                ${{ number_format($basePendiente, 2, ',', '.') }}
                                            </strong>
                                        </div>
                                    </div>

                                    <div class="box-combinado-cobro hidden mt-4 rounded-xl border p-4">
                                        <div class="font-semibold">Distribución del total base</div>
                                        <div class="text-xs text-slate-500 mt-1">
                                            La suma debe ser ${{ number_format($basePendiente, 2, ',', '.') }}.
                                            Solo tarjeta lleva 20% de recargo.
                                        </div>

                                        <div class="grid grid-cols-1 md:grid-cols-3 gap-3 mt-3">
                                            <div>
                                                <label class="text-sm text-slate-700">Efectivo</label>
                                                <input type="number"
                                                       step="0.01"
                                                       min="0"
                                                       name="pagos[efectivo]"
                                                       value="0"
                                                       class="pago-cobro mt-1 w-full rounded-xl border-slate-300">
                                            </div>

                                            <div>
                                                <label class="text-sm text-slate-700">Transferencia</label>
                                                <input type="number"
                                                       step="0.01"
                                                       min="0"
                                                       name="pagos[transferencia]"
                                                       value="0"
                                                       class="pago-cobro mt-1 w-full rounded-xl border-slate-300">
                                            </div>

                                            <div>
                                                <label class="text-sm text-slate-700">Tarjeta</label>
                                                <input type="number"
                                                       step="0.01"
                                                       min="0"
                                                       name="pagos[tarjeta]"
                                                       value="0"
                                                       class="pago-cobro mt-1 w-full rounded-xl border-slate-300">
                                            </div>
                                        </div>

                                        <div class="estado-cobro mt-3 rounded-xl border px-3 py-2 text-sm text-slate-600">
                                            Falta asignar:
                                            ${{ number_format($basePendiente, 2, ',', '.') }}
                                        </div>
                                    </div>

                                    <div class="mt-5 flex justify-end gap-2">
                                        <button type="button"
                                                class="rounded-xl border px-4 py-2 hover:bg-slate-50"
                                                data-close-cobro="{{ $v->id }}">
                                            Cancelar
                                        </button>

                                        <button class="rounded-xl bg-slate-900 text-white px-4 py-2 hover:bg-slate-800">
                                            Registrar pago
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </td>
                </tr>
            @endif
        @empty
            <tr>
                <td colspan="8" class="px-4 py-8 text-center text-slate-500">
                    No hay ventas.
                </td>
            </tr>
        @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">
    {{ $ventas->links() }}
</div>

@if(auth()->user()->esAdmin())
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mt-6">
        <div class="rounded-2xl border bg-white p-4">
            <div class="text-sm text-slate-500">Efectivo cobrado</div>
            <div class="text-2xl font-bold text-green-700">
                ${{ number_format((float)($totalEfectivo ?? 0), 2, ',', '.') }}
            </div>
        </div>

        <div class="rounded-2xl border bg-white p-4">
            <div class="text-sm text-slate-500">Transferencia cobrada</div>
            <div class="text-2xl font-bold text-blue-700">
                ${{ number_format((float)($totalTransferencia ?? 0), 2, ',', '.') }}
            </div>
        </div>

        <div class="rounded-2xl border bg-white p-4">
            <div class="text-sm text-slate-500">Tarjeta cobrada</div>
            <div class="text-2xl font-bold text-purple-700">
                ${{ number_format((float)($totalTarjeta ?? 0), 2, ',', '.') }}
            </div>
        </div>

        <div class="rounded-2xl border bg-slate-900 p-4 text-white">
            <div class="text-sm text-slate-300">Total cobrado</div>
            <div class="text-2xl font-bold">
                ${{ number_format((float)($totalGeneral ?? 0), 2, ',', '.') }}
            </div>
        </div>
    </div>
@endif

<script>
function moneyCobro(n){
    n = Math.round((Number(n || 0) + Number.EPSILON) * 100) / 100;

    return '$' + n.toLocaleString('es-AR', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
}

function actualizarFormularioCobro(form){
    const totalBase = Number(form.dataset.totalBase || 0);
    const tipo = form.querySelector('.tipo-pago-cobro').value;
    const box = form.querySelector('.box-combinado-cobro');
    const inputs = form.querySelectorAll('.pago-cobro');
    const recargoEl = form.querySelector('.recargo-cobro');
    const totalEl = form.querySelector('.total-cobro');
    const estadoEl = form.querySelector('.estado-cobro');

    if(tipo !== 'combinado'){
        box.classList.add('hidden');
        inputs.forEach(i => i.disabled = true);

        const recargo = tipo === 'tarjeta' ? totalBase * 0.20 : 0;

        recargoEl.textContent = moneyCobro(recargo);
        totalEl.textContent = moneyCobro(totalBase + recargo);
        return;
    }

    box.classList.remove('hidden');
    inputs.forEach(i => i.disabled = false);

    const valores = Array.from(inputs).map(i => Number(i.value || 0));
    const suma = valores.reduce((a, b) => a + b, 0);
    const tarjeta = Number(form.querySelector('input[name="pagos[tarjeta]"]').value || 0);
    const recargo = tarjeta * 0.20;
    const diferencia = totalBase - suma;

    recargoEl.textContent = moneyCobro(recargo);
    totalEl.textContent = moneyCobro(totalBase + recargo);

    if(Math.abs(diferencia) < 0.01){
        estadoEl.textContent = 'Distribución correcta.';
        estadoEl.className = 'estado-cobro mt-3 rounded-xl border border-green-300 bg-green-50 px-3 py-2 text-sm text-green-700';
    } else if(diferencia > 0){
        estadoEl.textContent = 'Falta asignar: ' + moneyCobro(diferencia);
        estadoEl.className = 'estado-cobro mt-3 rounded-xl border px-3 py-2 text-sm text-slate-600';
    } else {
        estadoEl.textContent = 'Se excede por: ' + moneyCobro(Math.abs(diferencia));
        estadoEl.className = 'estado-cobro mt-3 rounded-xl border border-red-300 bg-red-50 px-3 py-2 text-sm text-red-700';
    }
}

document.addEventListener('click', function(e){
    const abrir = e.target.closest('[data-open-cobro]');
    const cerrar = e.target.closest('[data-close-cobro]');

    if(abrir){
        const modal = document.getElementById('modal-cobro-' + abrir.dataset.openCobro);

        if(modal){
            modal.classList.remove('hidden');
            modal.classList.add('flex');

            const form = modal.querySelector('.form-cobro');
            actualizarFormularioCobro(form);
        }
    }

    if(cerrar){
        const modal = document.getElementById('modal-cobro-' + cerrar.dataset.closeCobro);

        if(modal){
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }
    }
});

document.querySelectorAll('.form-cobro').forEach(form => {
    form.querySelector('.tipo-pago-cobro').addEventListener('change', () => {
        actualizarFormularioCobro(form);
    });

    form.querySelectorAll('.pago-cobro').forEach(input => {
        input.addEventListener('input', () => actualizarFormularioCobro(form));
    });

    form.addEventListener('submit', function(e){
        if(form.querySelector('.tipo-pago-cobro').value !== 'combinado'){
            return;
        }

        const totalBase = Number(form.dataset.totalBase || 0);
        const valores = Array.from(form.querySelectorAll('.pago-cobro'))
            .map(i => Number(i.value || 0));

        const suma = valores.reduce((a, b) => a + b, 0);
        const cantidadMetodos = valores.filter(v => v > 0).length;

        if(Math.abs(suma - totalBase) > 0.01){
            e.preventDefault();
            alert('La distribución del pago debe coincidir con el total base.');
            return;
        }

        if(cantidadMetodos < 2){
            e.preventDefault();
            alert('Para pago combinado tenés que usar al menos dos formas de pago.');
        }
    });
});
</script>

@endsection
