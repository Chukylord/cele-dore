@extends('layouts.admin')

@section('title', 'Detalle Venta - Peluquería TOP')
@section('h1', 'Detalle de venta')
@section('sub', 'Servicios, productos, pagos y totales.')

@section('content')

<div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4 mb-6">
    <div>
        <div class="text-sm text-slate-600">Fecha de venta</div>
        <div class="text-xl font-bold">{{ $venta->fecha?->format('d/m/Y H:i') }}</div>

        <div class="mt-2 text-sm text-slate-600">Cliente</div>
        <div class="font-semibold">
            @if($venta->cliente)
                {{ $venta->cliente->nombre }} {{ $venta->cliente->apellido }}
            @elseif($venta->clienteColaboradora)
                (Colab) {{ $venta->clienteColaboradora->nombre }} {{ $venta->clienteColaboradora->apellido }}
            @else
                -
            @endif
        </div>

        <div class="mt-2 text-sm text-slate-600">Estado de pago</div>
        <div class="font-semibold">
            @if($venta->pendiente_pago)
                <span class="inline-flex rounded-full border border-yellow-200 bg-yellow-50 px-3 py-1 text-sm text-yellow-800">
                    Pendiente
                </span>
            @else
                <span class="inline-flex rounded-full border border-green-200 bg-green-50 px-3 py-1 text-sm text-green-700">
                    Pagado
                </span>
            @endif
        </div>
    </div>

    <div class="md:text-right">
        <div class="text-sm text-slate-600">Total base</div>
        <div class="text-xl font-bold">
            ${{ number_format((float)($venta->total_base ?: $venta->subtotal_servicios + $venta->subtotal_productos), 2, ',', '.') }}
        </div>

        <div class="text-sm text-slate-600 mt-2">Recargo tarjeta</div>
        <div class="text-xl font-semibold text-purple-700">
            ${{ number_format((float)$venta->recargo_tarjeta, 2, ',', '.') }}
        </div>

        <div class="text-sm text-slate-600 mt-2">Total final</div>
        <div class="text-3xl font-bold">
            ${{ number_format((float)$venta->total, 2, ',', '.') }}
        </div>

        <div class="text-sm text-slate-600 mt-2">Comisión</div>
        <div class="font-semibold">
            ${{ number_format((float)$venta->comision_monto, 2, ',', '.') }}
        </div>

        <a href="{{ route('ventas.index') }}"
           class="inline-block mt-4 rounded-xl bg-slate-900 text-white px-4 py-2 hover:bg-slate-800">
            Volver
        </a>
    </div>
</div>

@if($venta->notas)
    <div class="mb-6 rounded-2xl border bg-slate-50 p-4">
        <div class="text-sm font-semibold text-slate-700 mb-1">Notas</div>
        <div class="whitespace-pre-wrap">{{ $venta->notas }}</div>
    </div>
@endif

<div class="rounded-2xl border overflow-hidden mb-6">
    <div class="px-4 py-3 bg-slate-50 border-b">
        <div class="font-semibold">Pagos</div>
        <div class="text-sm text-slate-600">
            Se registran en la fecha en que realmente se cobran.
        </div>
    </div>

    @if($venta->pagos->isNotEmpty())
        <div class="overflow-x-auto">
            <table class="min-w-full bg-white">
                <thead class="bg-white text-slate-700">
                <tr>
                    <th class="text-left px-4 py-3 text-sm font-semibold">Fecha pago</th>
                    <th class="text-left px-4 py-3 text-sm font-semibold">Método</th>
                    <th class="text-left px-4 py-3 text-sm font-semibold">Base</th>
                    <th class="text-left px-4 py-3 text-sm font-semibold">Recargo</th>
                    <th class="text-left px-4 py-3 text-sm font-semibold">Cobrado</th>
                    <th class="text-left px-4 py-3 text-sm font-semibold">Registró</th>
                </tr>
                </thead>

                <tbody>
                @foreach($venta->pagos as $pago)
                    <tr class="border-t">
                        <td class="px-4 py-3 whitespace-nowrap">
                            {{ $pago->fecha_pago?->format('d/m/Y H:i') }}
                        </td>

                        <td class="px-4 py-3">
                            @php
                                $clase = match($pago->metodo_pago) {
                                    'efectivo' => 'bg-green-100 text-green-700',
                                    'transferencia' => 'bg-blue-100 text-blue-700',
                                    'tarjeta' => 'bg-purple-100 text-purple-700',
                                    default => 'bg-slate-100 text-slate-700',
                                };
                            @endphp

                            <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $clase }}">
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
                    <td colspan="4" class="px-4 py-3 text-right font-semibold">
                        Total cobrado:
                    </td>
                    <td class="px-4 py-3 font-bold">
                        ${{ number_format((float)$venta->pagos->sum('monto'), 2, ',', '.') }}
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
    <div class="rounded-2xl border overflow-x-auto">
        <div class="px-4 py-3 bg-slate-50 font-semibold">Servicios</div>

        <table class="min-w-full bg-white">
            <thead class="bg-white text-slate-700">
            <tr class="border-t">
                <th class="text-left px-4 py-3 text-sm font-semibold">Servicio</th>
                <th class="text-left px-4 py-3 text-sm font-semibold">Precio base</th>
                <th class="text-left px-4 py-3 text-sm font-semibold">Detalle</th>
            </tr>
            </thead>

            <tbody>
            @forelse($venta->servicios as $s)
                <tr class="border-t">
                    <td class="px-4 py-3">{{ $s->servicio?->nombre }}</td>
                    <td class="px-4 py-3">
                        ${{ number_format((float)$s->precio, 2, ',', '.') }}
                    </td>
                    <td class="px-4 py-3 text-slate-600">{{ $s->detalle ?: '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="3" class="px-4 py-6 text-center text-slate-500">
                        Sin servicios.
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>

        <div class="px-4 py-3 bg-slate-50 text-right font-semibold">
            Subtotal servicios:
            ${{ number_format((float)$venta->subtotal_servicios, 2, ',', '.') }}
        </div>
    </div>

    <div class="rounded-2xl border overflow-x-auto">
        <div class="px-4 py-3 bg-slate-50 font-semibold">Productos</div>

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
            @forelse($venta->productos as $p)
                <tr class="border-t">
                    <td class="px-4 py-3">
                        {{ $p->producto?->marca }} -
                        {{ $p->producto?->tipo }}
                        {{ $p->producto?->contenido }}
                    </td>

                    <td class="px-4 py-3">{{ $p->cantidad }}</td>

                    <td class="px-4 py-3">
                        ${{ number_format((float)$p->precio_unitario, 2, ',', '.') }}
                    </td>

                    <td class="px-4 py-3 font-semibold">
                        ${{ number_format((float)$p->subtotal, 2, ',', '.') }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="px-4 py-6 text-center text-slate-500">
                        Sin productos.
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>

        <div class="px-4 py-3 bg-slate-50 text-right font-semibold">
            Subtotal productos:
            ${{ number_format((float)$venta->subtotal_productos, 2, ',', '.') }}
        </div>
    </div>
</div>

@endsection
