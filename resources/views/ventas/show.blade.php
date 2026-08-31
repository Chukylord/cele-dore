@extends('layouts.admin')

@section('title', 'Detalle de venta - FN Peluquería')
@section('h1', 'Detalle de venta')
@section('sub', 'Servicios, productos, pagos realizados y saldo pendiente.')

@section('content')

@php
    $totalBaseVenta = $venta->totalBaseReal();
    $pagadoBaseVenta = $venta->totalPagadoBase();
    $totalCobradoVenta = $venta->totalCobrado();
    $recargoCobradoVenta = $venta->totalRecargoCobrado();
    $saldoBaseVenta = $venta->saldoPendienteBase();
@endphp

<div class="fn-toolbar flex flex-col lg:flex-row lg:items-start lg:justify-between gap-6 mb-6">
    <div>
        <div class="text-sm text-slate-600">Fecha de venta</div>
        <div class="text-xl font-bold">{{ $venta->fecha?->format('d/m/Y H:i') }}</div>

        <div class="mt-3 text-sm text-slate-600">Cliente</div>
        <div class="font-semibold">
            @if($venta->cliente)
                {{ $venta->cliente->apellido }} {{ $venta->cliente->nombre }}
            @elseif($venta->clienteColaboradora)
                (Colab) {{ $venta->clienteColaboradora->apellido }} {{ $venta->clienteColaboradora->nombre }}
            @else
                -
            @endif
        </div>

        <div class="mt-3 text-sm text-slate-600">Vendedora</div>
        <div class="font-semibold">
            {{ $venta->vendedora ? ($venta->vendedora->nombre.' '.$venta->vendedora->apellido) : '-' }}
        </div>

        <div class="mt-3 text-sm text-slate-600">Estado de pago</div>
        <div class="font-semibold mt-1">
            @if($saldoBaseVenta <= 0.01)
                <span class="inline-flex rounded-full border border-green-200 bg-green-50 px-3 py-1 text-sm text-green-700">
                    Pagado completamente
                </span>
            @elseif($pagadoBaseVenta > 0.01)
                <span class="inline-flex rounded-full border border-blue-200 bg-blue-50 px-3 py-1 text-sm text-blue-700">
                    Pago parcial
                </span>
            @else
                <span class="inline-flex rounded-full border border-yellow-200 bg-yellow-50 px-3 py-1 text-sm text-yellow-800">
                    Pendiente de pago
                </span>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-1 gap-3 w-full lg:max-w-sm">
        <div class="fn-stat-card">
            <div class="text-sm text-slate-500">Total base de la venta</div>
            <div class="text-2xl font-bold">
                ${{ number_format($totalBaseVenta, 2, ',', '.') }}
            </div>
        </div>

        <div class="fn-stat-card bg-green-50">
            <div class="text-sm text-green-700">Total cobrado hasta ahora</div>
            <div class="text-2xl font-bold text-green-800">
                ${{ number_format($totalCobradoVenta, 2, ',', '.') }}
            </div>
            @if($recargoCobradoVenta > 0)
                <div class="text-xs text-green-700 mt-1">
                    Incluye ${{ number_format($recargoCobradoVenta, 2, ',', '.') }} de recargos de tarjeta.
                </div>
            @endif
        </div>

        <div class="fn-stat-card {{ $saldoBaseVenta > 0.01 ? 'bg-amber-50' : 'fn-stat-card-primary' }}">
            <div class="text-sm {{ $saldoBaseVenta > 0.01 ? 'text-amber-700' : 'text-slate-300' }}">
                Saldo base pendiente
            </div>
            <div class="text-3xl font-bold {{ $saldoBaseVenta > 0.01 ? 'text-amber-800' : '' }}">
                ${{ number_format($saldoBaseVenta, 2, ',', '.') }}
            </div>
        </div>

        <a href="{{ route('ventas.index') }}"
           class="fn-primary-action text-center">
            Volver a ventas
        </a>
    </div>
</div>

@if($venta->notas)
    <div class="fn-section-card mb-6">
        <div class="text-sm font-semibold text-slate-700 mb-1">Notas</div>
        <div class="whitespace-pre-wrap">{{ $venta->notas }}</div>
    </div>
@endif

<div class="fn-table-shell mb-6">
    <div class="fn-card-heading px-4 py-3">
        <div class="font-semibold">Historial de pagos</div>
        <div class="text-sm text-slate-600">
            Cada pago figura en la fecha y medio en que fue realmente cobrado.
        </div>
    </div>

    @if($venta->pagos->isNotEmpty())
        <div class="overflow-x-auto">
            <table class="min-w-full bg-white">
                <thead class="bg-white text-slate-700">
                <tr>
                    <th class="text-left px-4 py-3 text-sm font-semibold">Fecha pago</th>
                    <th class="text-left px-4 py-3 text-sm font-semibold">Método</th>
                    <th class="text-left px-4 py-3 text-sm font-semibold">Base abonada</th>
                    <th class="text-left px-4 py-3 text-sm font-semibold">Recargo</th>
                    <th class="text-left px-4 py-3 text-sm font-semibold">Total cobrado</th>
                    <th class="text-left px-4 py-3 text-sm font-semibold">Registró</th>
                </tr>
                </thead>

                <tbody>
                @foreach($venta->pagos as $pago)
                    @php
                        $clasePago = match($pago->metodo_pago) {
                            'efectivo' => 'bg-green-100 text-green-700',
                            'transferencia' => 'bg-blue-100 text-blue-700',
                            'tarjeta' => 'bg-purple-100 text-purple-700',
                            default => 'bg-slate-100 text-slate-700',
                        };
                    @endphp

                    <tr class="border-t">
                        <td class="px-4 py-3 whitespace-nowrap">
                            {{ $pago->fecha_pago?->format('d/m/Y H:i') }}
                        </td>
                        <td class="px-4 py-3">
                            <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $clasePago }}">
                                {{ ucfirst($pago->metodo_pago) }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            ${{ number_format((float)$pago->monto_base, 2, ',', '.') }}
                        </td>
                        <td class="px-4 py-3">
                            ${{ number_format((float)$pago->recargo, 2, ',', '.') }}
                        </td>
                        <td class="px-4 py-3 font-bold">
                            ${{ number_format((float)$pago->monto, 2, ',', '.') }}
                        </td>
                        <td class="px-4 py-3">
                            {{ $pago->usuario?->name ?? '-' }}
                        </td>
                    </tr>
                @endforeach
                </tbody>

                <tfoot class="bg-slate-50">
                <tr class="border-t">
                    <td colspan="2" class="px-4 py-3 text-right font-semibold">Totales:</td>
                    <td class="px-4 py-3 font-bold">
                        ${{ number_format($pagadoBaseVenta, 2, ',', '.') }}
                    </td>
                    <td class="px-4 py-3 font-bold">
                        ${{ number_format($recargoCobradoVenta, 2, ',', '.') }}
                    </td>
                    <td class="px-4 py-3 font-bold">
                        ${{ number_format($totalCobradoVenta, 2, ',', '.') }}
                    </td>
                    <td></td>
                </tr>
                </tfoot>
            </table>
        </div>
    @else
        <div class="px-4 py-6 text-center text-slate-500">
            Esta venta todavía no tiene pagos registrados.
        </div>
    @endif
</div>

<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <div class="fn-table-shell overflow-x-auto">
        <div class="fn-card-heading px-4 py-3 font-semibold">Servicios</div>
        <table class="min-w-full bg-white">
            <thead class="bg-white text-slate-700">
            <tr class="border-t">
                <th class="text-left px-4 py-3 text-sm font-semibold">Servicio</th>
                <th class="text-left px-4 py-3 text-sm font-semibold">Precio base</th>
                <th class="text-left px-4 py-3 text-sm font-semibold">Detalle</th>
            </tr>
            </thead>
            <tbody>
            @forelse($venta->servicios as $servicioVenta)
                <tr class="border-t">
                    <td class="px-4 py-3">{{ $servicioVenta->servicio?->nombre }}</td>
                    <td class="px-4 py-3">
                        ${{ number_format((float)$servicioVenta->precio, 2, ',', '.') }}
                    </td>
                    <td class="px-4 py-3 text-slate-600">{{ $servicioVenta->detalle ?: '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="3" class="px-4 py-6 text-center text-slate-500">Sin servicios.</td>
                </tr>
            @endforelse
            </tbody>
        </table>
        <div class="px-4 py-3 bg-slate-50 text-right font-semibold">
            Subtotal servicios: ${{ number_format((float)$venta->subtotal_servicios, 2, ',', '.') }}
        </div>
    </div>

    <div class="fn-table-shell overflow-x-auto">
        <div class="fn-card-heading px-4 py-3 font-semibold">Productos</div>
        <table class="min-w-full bg-white">
            <thead class="bg-white text-slate-700">
            <tr class="border-t">
                <th class="text-left px-4 py-3 text-sm font-semibold">Producto</th>
                <th class="text-left px-4 py-3 text-sm font-semibold">Cant.</th>
                <th class="text-left px-4 py-3 text-sm font-semibold">Unit. base</th>
                <th class="text-left px-4 py-3 text-sm font-semibold">Subtotal</th>
            </tr>
            </thead>
            <tbody>
            @forelse($venta->productos as $productoVenta)
                <tr class="border-t">
                    <td class="px-4 py-3">
                        {{ $productoVenta->producto?->marca }} -
                        {{ $productoVenta->producto?->tipo }}
                        {{ $productoVenta->producto?->contenido }}
                    </td>
                    <td class="px-4 py-3">{{ $productoVenta->cantidad }}</td>
                    <td class="px-4 py-3">
                        ${{ number_format((float)$productoVenta->precio_unitario, 2, ',', '.') }}
                    </td>
                    <td class="px-4 py-3 font-semibold">
                        ${{ number_format((float)$productoVenta->subtotal, 2, ',', '.') }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="px-4 py-6 text-center text-slate-500">Sin productos.</td>
                </tr>
            @endforelse
            </tbody>
        </table>
        <div class="px-4 py-3 bg-slate-50 text-right font-semibold">
            Subtotal productos: ${{ number_format((float)$venta->subtotal_productos, 2, ',', '.') }}
        </div>
    </div>
</div>

@endsection
