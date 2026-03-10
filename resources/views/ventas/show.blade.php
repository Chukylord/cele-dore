@extends('layouts.admin')

@section('title', 'Detalle Venta - Peluquería TOP')
@section('h1', 'Detalle de venta')
@section('sub', 'Servicios, productos y totales.')

@section('content')

<div class="flex items-start justify-between gap-4 mb-6">
    <div>
        <div class="text-sm text-slate-600">Fecha</div>
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

        <div class="mt-2 text-sm text-slate-600">Método</div>
        <div class="font-semibold">{{ ucfirst($venta->metodo_pago) }}</div>
    </div>

    <div class="text-right">
        <div class="text-sm text-slate-600">Total</div>
        <div class="text-3xl font-bold">${{ number_format((float)$venta->total, 2, ',', '.') }}</div>
        <div class="text-sm text-slate-600 mt-2">Comisión</div>
        <div class="font-semibold">${{ number_format((float)$venta->comision_monto, 2, ',', '.') }}</div>

        <a href="{{ route('ventas.index') }}" class="inline-block mt-4 rounded-xl bg-slate-900 text-white px-4 py-2 hover:bg-slate-800">
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

<div class="grid grid-cols-1 md:grid-cols-2 gap-6">

    <div class="rounded-2xl border overflow-x-auto">
        <div class="px-4 py-3 bg-slate-50 font-semibold">Servicios</div>
        <table class="min-w-full bg-white">
            <thead class="bg-white text-slate-700">
            <tr class="border-t">
                <th class="text-left px-4 py-3 text-sm font-semibold">Servicio</th>
                <th class="text-left px-4 py-3 text-sm font-semibold">Precio</th>
                <th class="text-left px-4 py-3 text-sm font-semibold">Detalle</th>
            </tr>
            </thead>
            <tbody>
            @forelse($venta->servicios as $s)
                <tr class="border-t">
                    <td class="px-4 py-3">{{ $s->servicio?->nombre }}</td>
                    <td class="px-4 py-3">${{ number_format((float)$s->precio, 2, ',', '.') }}</td>
                    <td class="px-4 py-3 text-slate-600">{{ $s->detalle ?: '-' }}</td>
                </tr>
            @empty
                <tr><td colspan="3" class="px-4 py-6 text-center text-slate-500">Sin servicios.</td></tr>
            @endforelse
            </tbody>
        </table>
        <div class="px-4 py-3 bg-slate-50 text-right font-semibold">
            Subtotal servicios: ${{ number_format((float)$venta->subtotal_servicios, 2, ',', '.') }}
        </div>
    </div>

    <div class="rounded-2xl border overflow-x-auto">
        <div class="px-4 py-3 bg-slate-50 font-semibold">Productos</div>
        <table class="min-w-full bg-white">
            <thead class="bg-white text-slate-700">
            <tr class="border-t">
                <th class="text-left px-4 py-3 text-sm font-semibold">Producto</th>
                <th class="text-left px-4 py-3 text-sm font-semibold">Cant.</th>
                <th class="text-left px-4 py-3 text-sm font-semibold">Unit.</th>
                <th class="text-left px-4 py-3 text-sm font-semibold">Subtotal</th>
            </tr>
            </thead>
            <tbody>
            @forelse($venta->productos as $p)
                <tr class="border-t">
                    <td class="px-4 py-3">{{ $p->producto?->marca }} - {{ $p->producto?->tipo }} {{ $p->producto?->contenido }}</td>
                    <td class="px-4 py-3">{{ $p->cantidad }}</td>
                    <td class="px-4 py-3">${{ number_format((float)$p->precio_unitario, 2, ',', '.') }}</td>
                    <td class="px-4 py-3 font-semibold">${{ number_format((float)$p->subtotal, 2, ',', '.') }}</td>
                </tr>
            @empty
                <tr><td colspan="4" class="px-4 py-6 text-center text-slate-500">Sin productos.</td></tr>
            @endforelse
            </tbody>
        </table>
        <div class="px-4 py-3 bg-slate-50 text-right font-semibold">
            Subtotal productos: ${{ number_format((float)$venta->subtotal_productos, 2, ',', '.') }}
        </div>
    </div>

</div>

@endsection