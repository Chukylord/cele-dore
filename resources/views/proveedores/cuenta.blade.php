@extends('layouts.admin')

@section('title', 'Cuenta corriente - FN Peluquería')
@section('h1', 'Cuenta corriente')
@section('sub', 'Compras, entregas y saldo del proveedor.')

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

<div class="fn-toolbar flex flex-col md:flex-row md:items-start md:justify-between gap-4 mb-6">
    <div>
        <div class="text-2xl font-bold">{{ $proveedor->nombre }}</div>
        <div class="text-slate-600 mt-1">Detalle de cuenta corriente del proveedor.</div>
    </div>

    <a href="{{ route('proveedores.index') }}"
       class="fn-primary-action text-center">
        Volver
    </a>
</div>

<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
    <div class="fn-stat-card">
        <div class="text-sm text-slate-600">Total comprado</div>
        <div class="text-2xl font-bold mt-1">
            ${{ number_format((float)$totalCompras, 2, ',', '.') }}
        </div>
    </div>

    <div class="fn-stat-card">
        <div class="text-sm text-slate-600">Total entregado</div>
        <div class="text-2xl font-bold mt-1 text-green-700">
            ${{ number_format((float)$totalPagos, 2, ',', '.') }}
        </div>
    </div>

    <div class="rounded-2xl border p-4 {{ (float)$saldoFinal > 0 ? 'bg-red-50 border-red-200' : 'bg-green-50 border-green-200' }}">
        <div class="text-sm {{ (float)$saldoFinal > 0 ? 'text-red-700' : 'text-green-700' }}">
            Debe total a este proveedor
        </div>
        <div class="text-2xl font-bold mt-1 {{ (float)$saldoFinal > 0 ? 'text-red-800' : 'text-green-800' }}">
            ${{ number_format((float)$saldoFinal, 2, ',', '.') }}
        </div>
    </div>
</div>

<div class="fn-section-card mb-6">
    <div class="text-lg font-bold mb-4">Registrar entrega</div>

    <form method="POST" action="{{ route('proveedores.pagos.store', $proveedor) }}"
          class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
        @csrf

        <div>
            <label class="text-sm font-semibold text-slate-700">Fecha *</label>
            <input type="date"
                   name="fecha"
                   value="{{ old('fecha', now()->format('Y-m-d')) }}"
                   class="mt-1 w-full rounded-xl border-slate-300 focus:border-slate-500 focus:ring-slate-500">
        </div>

        <div>
            <label class="text-sm font-semibold text-slate-700">Monto *</label>
            <input type="number"
                   step="0.01"
                   min="0.01"
                   name="monto"
                   value="{{ old('monto') }}"
                   placeholder="Ej: 300000"
                   class="mt-1 w-full rounded-xl border-slate-300 focus:border-slate-500 focus:ring-slate-500">
        </div>

        <div>
            <label class="text-sm font-semibold text-slate-700">Observación</label>
            <input name="observacion"
                   value="{{ old('observacion') }}"
                   placeholder="Ej: transferencia, efectivo..."
                   class="mt-1 w-full rounded-xl border-slate-300 focus:border-slate-500 focus:ring-slate-500">
        </div>

        <div>
            <button class="fn-primary-action w-full">
                Guardar entrega
            </button>
        </div>
    </form>
</div>

<div class="fn-table-shell overflow-x-auto">
    <table class="min-w-full bg-white">
        <thead class="bg-slate-50 text-slate-700">
        <tr>
            <th class="text-left px-4 py-3 text-sm font-semibold">Fecha</th>
            <th class="text-left px-4 py-3 text-sm font-semibold">Movimiento</th>
            <th class="text-left px-4 py-3 text-sm font-semibold">Detalle</th>
            <th class="text-right px-4 py-3 text-sm font-semibold">Debe</th>
            <th class="text-right px-4 py-3 text-sm font-semibold">Entrega</th>
            <th class="text-right px-4 py-3 text-sm font-semibold">Saldo</th>
            <th class="text-right px-4 py-3 text-sm font-semibold">Acciones</th>
        </tr>
        </thead>

        <tbody>
        @forelse($movimientos as $mov)
            <tr class="border-t hover:bg-slate-50">
                <td class="px-4 py-3">
                    {{ \Carbon\Carbon::parse($mov['fecha'])->format('d/m/Y') }}
                </td>

                <td class="px-4 py-3">
                    @if($mov['tipo'] === 'compra')
                        <span class="rounded-full bg-red-100 text-red-700 px-3 py-1 text-xs font-semibold">
                            Compra
                        </span>
                    @else
                        <span class="rounded-full bg-green-100 text-green-700 px-3 py-1 text-xs font-semibold">
                            Entrega
                        </span>
                    @endif
                </td>

                <td class="px-4 py-3">
                    {{ $mov['detalle'] }}
                </td>

                <td class="px-4 py-3 text-right font-semibold">
                    @if($mov['monto'] > 0)
                        ${{ number_format((float)$mov['monto'], 2, ',', '.') }}
                    @else
                        -
                    @endif
                </td>

                <td class="px-4 py-3 text-right font-semibold text-green-700">
                    @if($mov['monto'] < 0)
                        ${{ number_format(abs((float)$mov['monto']), 2, ',', '.') }}
                    @else
                        -
                    @endif
                </td>

                <td class="px-4 py-3 text-right font-bold">
                    ${{ number_format((float)$mov['saldo'], 2, ',', '.') }}
                </td>

                <td class="px-4 py-3">
                    <div class="flex justify-end gap-2">
                        @if($mov['tipo'] === 'pago' && !empty($mov['pago_id']))
                            <button type="button"
                                    class="fn-icon-action"
                                    title="Editar entrega"
                                    data-open-pago="{{ $mov['pago_id'] }}">
                                ✏️
                            </button>

                            <form method="POST"
                                  action="{{ route('proveedores.pagos.destroy', [$proveedor, $mov['pago_id']]) }}"
                                  onsubmit="return confirm('¿Eliminar esta entrega?');">
                                @csrf
                                @method('DELETE')
                                <button class="fn-icon-action fn-icon-action-danger"
                                        title="Eliminar entrega">
                                    🗑️
                                </button>
                            </form>
                        @else
                            -
                        @endif
                    </div>
                </td>
            </tr>

            @if($mov['tipo'] === 'pago' && !empty($mov['pago_id']))
                <div id="modal-pago-{{ $mov['pago_id'] }}"
                     class="fixed inset-0 hidden items-center justify-center bg-black/40 p-4 z-50">
                    <div class="fn-modal-card w-full max-w-xl p-6">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <div class="text-lg font-bold">Editar entrega</div>
                                <div class="text-sm text-slate-600">{{ $proveedor->nombre }}</div>
                            </div>

                            <button type="button"
                                    class="text-slate-500 hover:text-slate-900"
                                    data-close-pago="{{ $mov['pago_id'] }}">
                                ✖
                            </button>
                        </div>

                        <form method="POST"
                              action="{{ route('proveedores.pagos.update', [$proveedor, $mov['pago_id']]) }}"
                              class="mt-4 space-y-4">
                            @csrf
                            @method('PUT')

                            <div>
                                <label class="text-sm font-semibold text-slate-700">Fecha *</label>
                                <input type="date"
                                       name="fecha"
                                       value="{{ \Carbon\Carbon::parse($mov['fecha'])->format('Y-m-d') }}"
                                       class="mt-1 w-full rounded-xl border-slate-300 focus:border-slate-500 focus:ring-slate-500">
                            </div>

                            <div>
                                <label class="text-sm font-semibold text-slate-700">Monto *</label>
                                <input type="number"
                                       step="0.01"
                                       min="0.01"
                                       name="monto"
                                       value="{{ number_format(abs((float)$mov['monto']), 2, '.', '') }}"
                                       class="mt-1 w-full rounded-xl border-slate-300 focus:border-slate-500 focus:ring-slate-500">
                            </div>

                            <div>
                                <label class="text-sm font-semibold text-slate-700">Observación</label>
                                <input name="observacion"
                                       value="{{ $mov['detalle'] }}"
                                       class="mt-1 w-full rounded-xl border-slate-300 focus:border-slate-500 focus:ring-slate-500">
                            </div>

                            <div class="flex justify-end gap-2">
                                <button type="button"
                                        class="fn-secondary-action"
                                        data-close-pago="{{ $mov['pago_id'] }}">
                                    Cancelar
                                </button>

                                <button class="fn-primary-action">
                                    Guardar cambios
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            @endif
        @empty
            <tr>
                <td colspan="7" class="px-4 py-8 text-center text-slate-500">
                    No hay movimientos para este proveedor.
                </td>
            </tr>
        @endforelse
        </tbody>
    </table>
</div>

<script>
    document.addEventListener('click', function(e) {
        const openBtn = e.target.closest('[data-open-pago]');
        const closeBtn = e.target.closest('[data-close-pago]');

        if (openBtn) {
            const id = openBtn.getAttribute('data-open-pago');
            const modal = document.getElementById('modal-pago-' + id);

            if (modal) {
                modal.classList.remove('hidden');
                modal.classList.add('flex');
            }
        }

        if (closeBtn) {
            const id = closeBtn.getAttribute('data-close-pago');
            const modal = document.getElementById('modal-pago-' + id);

            if (modal) {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            }
        }

        if (e.target.classList.contains('bg-black/40') && e.target.id && e.target.id.startsWith('modal-pago-')) {
            e.target.classList.add('hidden');
            e.target.classList.remove('flex');
        }
    });
</script>

@endsection
