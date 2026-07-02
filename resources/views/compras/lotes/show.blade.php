@extends('layouts.admin')

@section('title', 'Detalle de Lote - Cele Dore Estilista')
@section('h1', 'Detalle de Lote')
@section('sub', 'Productos incluidos en la compra.')

@section('content')

@if(session('ok'))
    <div class="mb-4 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-green-800">
        {{ session('ok') }}
    </div>
@endif

@php
    $compras = $lote->compras ?? collect();
    $totalLote = (float) $lote->monto_total;

    $cantidadItems = $compras->count();

    $cantidadUnidades = $compras->sum(function ($compra) {
        return (int) $compra->cantidad;
    });

    $proveedoresLote = $compras
        ->map(fn ($compra) => $compra->proveedor?->nombre)
        ->filter()
        ->unique()
        ->values();
@endphp

{{-- Encabezado --}}
<div class="mb-6 flex flex-col gap-4 md:flex-row md:items-start md:justify-between">

    <div>
        <div class="text-2xl font-bold text-slate-900">
            Lote #{{ $lote->id }}
        </div>

        <div class="mt-1 text-slate-600">
            Fecha:
            {{ \Carbon\Carbon::parse($lote->fecha)->format('d/m/Y') }}
        </div>

        @if($lote->nota)
            <div class="mt-1 text-slate-600">
                Nota: {{ $lote->nota }}
            </div>
        @endif
    </div>

    <div class="flex flex-wrap gap-2">

        <a href="{{ route('compras.lotes.edit', $lote) }}"
           class="rounded-xl border bg-white px-4 py-2 hover:bg-slate-50">
            ✏️ Editar lote
        </a>

        <form method="POST"
              action="{{ route('compras.lotes.destroy', $lote) }}"
              onsubmit="return confirm('¿Eliminar este lote? Se revertirá el stock de los productos.');">

            @csrf
            @method('DELETE')

            <button type="submit"
                    class="rounded-xl border bg-white px-4 py-2 hover:bg-red-50 hover:text-red-700">
                🗑️ Eliminar lote
            </button>
        </form>

        <a href="{{ route('compras.index') }}"
           class="rounded-xl bg-slate-900 px-4 py-2 text-white hover:bg-slate-800">
            Volver
        </a>

    </div>
</div>

{{-- Resumen --}}
<div class="mb-6 grid grid-cols-1 gap-4 md:grid-cols-3">

    <div class="rounded-2xl border bg-white p-5">
        <div class="text-sm text-slate-600">
            Total de la compra
        </div>

        <div class="mt-1 text-3xl font-extrabold text-slate-900">
            ${{ number_format($totalLote, 2, ',', '.') }}
        </div>
    </div>

    <div class="rounded-2xl border bg-white p-5">
        <div class="text-sm text-slate-600">
            Productos diferentes
        </div>

        <div class="mt-1 text-3xl font-extrabold text-slate-900">
            {{ $cantidadItems }}
        </div>

        <div class="mt-2 text-xs text-slate-500">
            {{ $cantidadUnidades }}
            {{ $cantidadUnidades === 1 ? 'unidad comprada' : 'unidades compradas' }}
        </div>
    </div>

    <div class="rounded-2xl border bg-white p-5">
        <div class="text-sm text-slate-600">
            {{ $proveedoresLote->count() === 1 ? 'Proveedor' : 'Proveedores' }}
        </div>

        @if($proveedoresLote->isNotEmpty())
            <div class="mt-2 flex flex-wrap gap-2">
                @foreach($proveedoresLote as $proveedor)
                    <span class="rounded-full bg-slate-100 px-3 py-1 text-sm font-semibold text-slate-700">
                        {{ $proveedor }}
                    </span>
                @endforeach
            </div>
        @else
            <div class="mt-1 text-lg font-bold text-slate-400">
                Sin proveedor
            </div>
        @endif
    </div>

</div>

{{-- Productos --}}
<div class="rounded-2xl border bg-white">

    <div class="border-b px-5 py-4">
        <div class="text-lg font-bold text-slate-900">
            Productos del lote
        </div>

        <div class="mt-1 text-sm text-slate-600">
            Detalle de cantidades, costos y descuentos de la compra.
        </div>
    </div>

    <div class="overflow-x-auto">

        <table class="min-w-full bg-white">

            <thead class="bg-slate-50 text-slate-700">
                <tr>
                    <th class="px-4 py-3 text-left text-sm font-semibold">
                        Proveedor
                    </th>

                    <th class="px-4 py-3 text-left text-sm font-semibold">
                        Producto
                    </th>

                    <th class="px-4 py-3 text-left text-sm font-semibold">
                        Cantidad
                    </th>

                    <th class="px-4 py-3 text-left text-sm font-semibold">
                        Costo unitario
                    </th>

                    <th class="px-4 py-3 text-left text-sm font-semibold">
                        Descuento
                    </th>

                    <th class="px-4 py-3 text-right text-sm font-semibold">
                        Subtotal
                    </th>
                </tr>
            </thead>

            <tbody>
                @forelse($compras as $compra)

                    @php
                        $cantidad = (int) $compra->cantidad;
                        $precioUnitario = (float) $compra->precio_unitario;
                        $descuento = (float) ($compra->descuento_pct ?? 0);

                        $precioConDescuento = $precioUnitario
                            * (1 - ($descuento / 100));

                        $subtotal = $cantidad * $precioConDescuento;

                        $nombreProducto = trim(
                            ($compra->producto?->marca ?? '')
                            . ' - '
                            . ($compra->producto?->tipo ?? '')
                            . ' '
                            . ($compra->producto?->contenido ?? '')
                        );
                    @endphp

                    <tr class="border-t hover:bg-slate-50">

                        <td class="px-4 py-4 align-top">
                            {{ $compra->proveedor?->nombre ?? '-' }}
                        </td>

                        <td class="px-4 py-4 align-top">
                            <div class="font-semibold text-slate-800">
                                {{ $nombreProducto !== '-' ? $nombreProducto : 'Producto eliminado' }}
                            </div>
                        </td>

                        <td class="px-4 py-4 align-top">
                            {{ $cantidad }}
                        </td>

                        <td class="px-4 py-4 align-top whitespace-nowrap">
                            ${{ number_format($precioUnitario, 2, ',', '.') }}

                            <div class="mt-1 text-xs text-slate-500">
                                Costo sin descuento
                            </div>
                        </td>

                        <td class="px-4 py-4 align-top">

                            @if($descuento > 0)
                                <span class="inline-flex rounded-full bg-green-100 px-3 py-1 text-xs font-semibold text-green-700">
                                    {{ number_format($descuento, 2, ',', '.') }}%
                                </span>

                                <div class="mt-2 text-xs text-slate-500">
                                    Unitario final:
                                    ${{ number_format($precioConDescuento, 2, ',', '.') }}
                                </div>
                            @else
                                <span class="text-sm text-slate-400">
                                    Sin descuento
                                </span>
                            @endif

                        </td>

                        <td class="px-4 py-4 text-right align-top font-bold whitespace-nowrap">
                            ${{ number_format($subtotal, 2, ',', '.') }}
                        </td>

                    </tr>

                @empty
                    <tr>
                        <td colspan="6"
                            class="px-4 py-10 text-center text-slate-500">
                            Este lote no tiene productos asociados.
                        </td>
                    </tr>
                @endforelse
            </tbody>

            @if($compras->isNotEmpty())
                <tfoot class="border-t bg-slate-50">
                    <tr>
                        <td colspan="5"
                            class="px-4 py-4 text-right text-lg font-bold text-slate-800">
                            Total
                        </td>

                        <td class="px-4 py-4 text-right text-xl font-extrabold text-slate-950">
                            ${{ number_format($totalLote, 2, ',', '.') }}
                        </td>
                    </tr>
                </tfoot>
            @endif

        </table>

    </div>
</div>

@endsection