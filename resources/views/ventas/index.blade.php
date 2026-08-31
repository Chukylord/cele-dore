@extends('layouts.admin')

@section('title', 'Ventas - FN Peluquería')
@section('h1', 'Ventas')
@section('sub', 'Ventas, cobros parciales y saldos pendientes.')

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

<div class="fn-toolbar flex flex-col gap-3 mb-6">
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
                <option value="">Todos</option>
                <option value="efectivo" {{ $metodo === 'efectivo' ? 'selected' : '' }}>Efectivo</option>
                <option value="transferencia" {{ $metodo === 'transferencia' ? 'selected' : '' }}>Transferencia</option>
                <option value="tarjeta" {{ $metodo === 'tarjeta' ? 'selected' : '' }}>Tarjeta</option>
            </select>
        </div>

        <div>
            <label class="text-sm font-semibold text-slate-700">Estado</label>
            <select name="estado"
                    class="mt-1 w-full rounded-xl border-slate-300 focus:border-slate-500 focus:ring-slate-500">
                <option value="">Todos</option>
                <option value="pagado" {{ $estado === 'pagado' ? 'selected' : '' }}>Pagado</option>
                <option value="pendiente" {{ $estado === 'pendiente' ? 'selected' : '' }}>Pendiente / parcial</option>
            </select>
        </div>

        <div>
            <label class="text-sm font-semibold text-slate-700">Colaboradora</label>
            <select name="colaboradora_id"
                    class="mt-1 w-full rounded-xl border-slate-300 focus:border-slate-500 focus:ring-slate-500">
                <option value="">Todas</option>
                @foreach($colaboradoras as $c)
                    <option value="{{ $c->id }}"
                        {{ (string)$colaboradora_id === (string)$c->id ? 'selected' : '' }}>
                        {{ $c->apellido }} {{ $c->nombre }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="flex gap-2">
            <button class="fn-primary-action mt-6 w-full">
                Filtrar
            </button>
            <a href="{{ route('ventas.index') }}"
               class="fn-secondary-action mt-6 w-full text-center">
                Limpiar
            </a>
        </div>
    </form>

    <div class="flex justify-end">
        <a href="{{ route('ventas.create') }}"
           class="fn-primary-action">
            + Nueva venta
        </a>
    </div>
</div>

<div class="fn-table-shell overflow-x-auto">
    <table class="min-w-full bg-white">
        <thead class="bg-slate-50 text-slate-700">
        <tr>
            <th class="text-left px-4 py-3 text-sm font-semibold">Fecha</th>
            <th class="text-left px-4 py-3 text-sm font-semibold">Cliente</th>
            <th class="text-left px-4 py-3 text-sm font-semibold">Vendedora</th>
            <th class="text-left px-4 py-3 text-sm font-semibold">Pagos registrados</th>
            <th class="text-left px-4 py-3 text-sm font-semibold">Estado</th>
            <th class="text-left px-4 py-3 text-sm font-semibold">Importes</th>
            <th class="text-right px-4 py-3 text-sm font-semibold">Acciones</th>
        </tr>
        </thead>

        <tbody>
        @forelse($ventas as $v)
            @php
                $totalBaseVenta = $v->totalBaseReal();
                $pagadoBaseVenta = $v->totalPagadoBase();
                $saldoBaseVenta = $v->saldoPendienteBase();
                $totalCobradoVenta = $v->totalCobrado();
                $tienePagoParcial = $pagadoBaseVenta > 0.01 && $saldoBaseVenta > 0.01;
            @endphp

            <tr class="border-t hover:bg-slate-50 align-top">
                <td class="px-4 py-3 whitespace-nowrap">
                    {{ $v->fecha?->format('d/m/Y H:i') }}
                </td>

                <td class="px-4 py-3">
                    @if($v->cliente)
                        <div class="font-semibold">
                            {{ $v->cliente->apellido }} {{ $v->cliente->nombre }}
                        </div>
                    @elseif($v->clienteColaboradora)
                        <div class="font-semibold">
                            (Colab) {{ $v->clienteColaboradora->apellido }} {{ $v->clienteColaboradora->nombre }}
                        </div>
                    @else
                        -
                    @endif
                </td>

                <td class="px-4 py-3">
                    {{ $v->vendedora ? ($v->vendedora->nombre.' '.$v->vendedora->apellido) : '-' }}
                </td>

                <td class="px-4 py-3">
                    @if($v->pagos->isEmpty())
                        <span class="text-sm text-slate-500">Sin pagos</span>
                    @else
                        <div class="flex flex-col gap-1">
                            @foreach($v->pagos as $pago)
                                @php
                                    $clasePago = match($pago->metodo_pago) {
                                        'efectivo' => 'bg-green-100 text-green-700',
                                        'transferencia' => 'bg-blue-100 text-blue-700',
                                        'tarjeta' => 'bg-purple-100 text-purple-700',
                                        default => 'bg-slate-100 text-slate-700',
                                    };
                                @endphp

                                <span class="inline-flex w-fit rounded-full px-3 py-1 text-xs font-semibold {{ $clasePago }}">
                                    {{ ucfirst($pago->metodo_pago) }} ·
                                    ${{ number_format((float)$pago->monto, 2, ',', '.') }} ·
                                    {{ $pago->fecha_pago?->format('d/m H:i') }}
                                </span>
                            @endforeach
                        </div>
                    @endif
                </td>

                <td class="px-4 py-3">
                    @if($saldoBaseVenta <= 0.01)
                        <span class="inline-flex rounded-full border border-green-200 bg-green-50 px-3 py-1 text-xs font-bold text-green-700">
                            PAGADO
                        </span>
                    @elseif($tienePagoParcial)
                        <span class="inline-flex rounded-full border border-blue-200 bg-blue-50 px-3 py-1 text-xs font-bold text-blue-700">
                            PAGO PARCIAL
                        </span>
                    @else
                        <span class="inline-flex rounded-full border border-yellow-200 bg-yellow-50 px-3 py-1 text-xs font-bold text-yellow-800">
                            PENDIENTE
                        </span>
                    @endif
                </td>

                <td class="px-4 py-3 min-w-44">
                    <div class="text-sm text-slate-500">Total base</div>
                    <div class="font-semibold">
                        ${{ number_format($totalBaseVenta, 2, ',', '.') }}
                    </div>

                    <div class="text-sm text-green-700 mt-2">Cobrado</div>
                    <div class="font-semibold text-green-700">
                        ${{ number_format($totalCobradoVenta, 2, ',', '.') }}
                    </div>

                    @if($saldoBaseVenta > 0.01)
                        <div class="text-sm text-amber-700 mt-2">Saldo base</div>
                        <div class="font-bold text-amber-800">
                            ${{ number_format($saldoBaseVenta, 2, ',', '.') }}
                        </div>
                    @endif
                </td>

                <td class="px-4 py-3">
                    <div class="flex justify-end gap-2 flex-wrap">
                        <a href="{{ route('ventas.show', $v) }}"
                           class="fn-icon-action"
                           title="Ver detalle">
                            🔎
                        </a>

                        @if($saldoBaseVenta > 0.01)
                            <button type="button"
                                    class="fn-icon-action"
                                    data-open-cobro="{{ $v->id }}"
                                    title="Registrar cobro">
                                💵
                            </button>
                        @endif

                        <form method="POST"
                              action="{{ route('ventas.destroy', $v) }}"
                              onsubmit="return confirm('¿Eliminar venta?');">
                            @csrf
                            @method('DELETE')
                            <button class="fn-icon-action fn-icon-action-danger" title="Eliminar">
                                🗑️
                            </button>
                        </form>
                    </div>
                </td>
            </tr>

            @if($saldoBaseVenta > 0.01)
                <tr>
                    <td colspan="7" class="p-0 border-0">
                        <div id="modal-cobro-{{ $v->id }}"
                             class="fixed inset-0 hidden items-center justify-center bg-black/50 p-4 z-50">
                            <div class="fn-modal-card w-full max-w-2xl p-6">
                                <div class="flex items-start justify-between gap-4">
                                    <div>
                                        <div class="text-xl font-bold">Registrar cobro</div>
                                        <div class="text-sm text-slate-600 mt-1">
                                            Venta #{{ $v->id }} · Saldo base actual:
                                            <strong>${{ number_format($saldoBaseVenta, 2, ',', '.') }}</strong>
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
                                      data-total-base="{{ $saldoBaseVenta }}">
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

                                    <div class="fn-soft-panel resumen-cobro mt-4 p-4">
                                        <div class="flex justify-between gap-3">
                                            <span class="text-slate-600">Base que se abona</span>
                                            <strong>${{ number_format($saldoBaseVenta, 2, ',', '.') }}</strong>
                                        </div>
                                        <div class="flex justify-between gap-3 mt-2">
                                            <span class="text-slate-600">Recargo tarjeta</span>
                                            <strong class="recargo-cobro">$0,00</strong>
                                        </div>
                                        <div class="flex justify-between gap-3 mt-2 text-lg">
                                            <span class="font-bold">Total a cobrar ahora</span>
                                            <strong class="total-cobro">
                                                ${{ number_format($saldoBaseVenta, 2, ',', '.') }}
                                            </strong>
                                        </div>
                                    </div>

                                    <div class="box-combinado-cobro hidden mt-4 rounded-xl border p-4">
                                        <div class="font-semibold">Distribución del pago actual</div>
                                        <div class="text-xs text-slate-500 mt-1">
                                            La suma puede cancelar todo el saldo o cubrir solamente una parte.
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
                                            Ingresá los importes que abona ahora.
                                        </div>
                                    </div>

                                    <div class="mt-5 flex justify-end gap-2">
                                        <button type="button"
                                                class="fn-secondary-action"
                                                data-close-cobro="{{ $v->id }}">
                                            Cancelar
                                        </button>
                                        <button class="fn-primary-action">
                                            Registrar cobro
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
                <td colspan="7" class="px-4 py-8 text-center text-slate-500">
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
        <div class="fn-stat-card">
            <div class="text-sm text-slate-500">Efectivo cobrado</div>
            <div class="text-2xl font-bold text-green-700">
                ${{ number_format((float)$totalEfectivo, 2, ',', '.') }}
            </div>
        </div>
        <div class="fn-stat-card">
            <div class="text-sm text-slate-500">Transferencia cobrada</div>
            <div class="text-2xl font-bold text-blue-700">
                ${{ number_format((float)$totalTransferencia, 2, ',', '.') }}
            </div>
        </div>
        <div class="fn-stat-card">
            <div class="text-sm text-slate-500">Tarjeta cobrada</div>
            <div class="text-2xl font-bold text-purple-700">
                ${{ number_format((float)$totalTarjeta, 2, ',', '.') }}
            </div>
        </div>
        <div class="fn-stat-card fn-stat-card-primary">
            <div class="text-sm text-slate-300">Total cobrado</div>
            <div class="text-2xl font-bold">
                ${{ number_format((float)$totalGeneral, 2, ',', '.') }}
            </div>
        </div>
    </div>
@endif

<script>
document.addEventListener('click', function (event) {
    const abrir = event.target.closest('[data-open-cobro]');
    const cerrar = event.target.closest('[data-close-cobro]');

    if (abrir) {
        const modal = document.getElementById('modal-cobro-' + abrir.dataset.openCobro);
        if (modal) {
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }
    }

    if (cerrar) {
        const modal = document.getElementById('modal-cobro-' + cerrar.dataset.closeCobro);
        if (modal) {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }
    }
});
</script>

@endsection
