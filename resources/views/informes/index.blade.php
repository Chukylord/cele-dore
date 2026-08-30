@extends('layouts.admin')

@section('title', 'Informes - fn peluqueria')
@section('h1', 'Informes')
@section('sub', 'Balance económico y actividad realizada en el período.')

@section('content')

<form class="grid grid-cols-1 md:grid-cols-4 gap-3 mb-6"
      method="GET"
      action="{{ route('informes.index') }}">
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

    <div class="flex gap-2">
        <button class="mt-6 w-full rounded-xl bg-slate-900 text-white px-4 py-2 hover:bg-slate-800">
            Filtrar
        </button>

        <a href="{{ route('informes.index') }}"
           class="mt-6 w-full text-center rounded-xl border px-4 py-2 hover:bg-slate-50">
            Limpiar
        </a>
    </div>
</form>

<div class="mb-6 grid grid-cols-1 lg:grid-cols-2 gap-4">
    <div class="rounded-2xl border border-blue-200 bg-blue-50 p-4 text-blue-900">
        <div class="font-bold">Ingresos y egresos</div>
        <div class="mt-1 text-sm">
            Los ingresos se computan por la fecha real de cada cobro. Un pago parcial aparece el día en que fue abonado.
        </div>
    </div>

    <div class="rounded-2xl border border-indigo-200 bg-indigo-50 p-4 text-indigo-900">
        <div class="font-bold">Actividad del salón</div>
        <div class="mt-1 text-sm">
            Las cantidades de servicios, productos y atenciones se computan por la fecha en que se registró la venta.
        </div>
    </div>
</div>

<div class="mb-3">
    <div class="text-2xl font-bold">Actividad del período</div>
    <div class="text-sm text-slate-600">
        Cantidades reales realizadas o vendidas, sin importar cuándo se cobraron.
    </div>
</div>

<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <div class="rounded-2xl border bg-slate-900 p-4 text-white">
        <div class="text-sm text-slate-300">Atenciones / ventas</div>
        <div class="text-3xl font-extrabold mt-1">{{ number_format((int)$cantidadAtenciones, 0, ',', '.') }}</div>
    </div>

    <div class="rounded-2xl border bg-pink-50 p-4">
        <div class="text-sm text-pink-700">Clientas diferentes</div>
        <div class="text-3xl font-extrabold text-pink-800 mt-1">
            {{ number_format((int)$clientasUnicas, 0, ',', '.') }}
        </div>
    </div>

    <div class="rounded-2xl border bg-indigo-50 p-4">
        <div class="text-sm text-indigo-700">Servicios realizados</div>
        <div class="text-3xl font-extrabold text-indigo-800 mt-1">
            {{ number_format((int)$totalServiciosRealizados, 0, ',', '.') }}
        </div>
    </div>

    <div class="rounded-2xl border bg-cyan-50 p-4">
        <div class="text-sm text-cyan-700">Productos vendidos</div>
        <div class="text-3xl font-extrabold text-cyan-800 mt-1">
            {{ number_format((int)$totalProductosVendidos, 0, ',', '.') }}
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
    <div class="rounded-2xl border bg-white overflow-hidden">
        <div class="px-4 py-3 bg-slate-50 border-b">
            <div class="text-lg font-bold">Servicios realizados</div>
            <div class="text-sm text-slate-600">Cantidad de veces que se registró cada servicio.</div>
        </div>

        <div class="overflow-x-auto max-h-[430px]">
            <table class="min-w-full bg-white">
                <thead class="bg-white sticky top-0 text-slate-700">
                <tr>
                    <th class="text-left px-4 py-3 text-sm font-semibold">Servicio</th>
                    <th class="text-right px-4 py-3 text-sm font-semibold">Cantidad</th>
                </tr>
                </thead>
                <tbody>
                @forelse($serviciosDetalle as $detalleServicio)
                    <tr class="border-t hover:bg-slate-50">
                        <td class="px-4 py-3 font-semibold">
                            {{ $detalleServicio->servicio?->nombre ?? 'Servicio eliminado' }}
                        </td>
                        <td class="px-4 py-3 text-right">
                            <span class="inline-flex min-w-12 justify-center rounded-full bg-indigo-100 px-3 py-1 font-bold text-indigo-800">
                                {{ number_format((int)$detalleServicio->cantidad, 0, ',', '.') }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="2" class="px-4 py-8 text-center text-slate-500">
                            No hay servicios registrados en el período.
                        </td>
                    </tr>
                @endforelse
                </tbody>
                <tfoot class="bg-slate-50 sticky bottom-0">
                <tr class="border-t">
                    <td class="px-4 py-3 font-bold">Total de servicios</td>
                    <td class="px-4 py-3 text-right font-extrabold">
                        {{ number_format((int)$totalServiciosRealizados, 0, ',', '.') }}
                    </td>
                </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <div class="rounded-2xl border bg-white overflow-hidden">
        <div class="px-4 py-3 bg-slate-50 border-b">
            <div class="text-lg font-bold">Productos vendidos</div>
            <div class="text-sm text-slate-600">Unidades vendidas de cada producto.</div>
        </div>

        <div class="overflow-x-auto max-h-[430px]">
            <table class="min-w-full bg-white">
                <thead class="bg-white sticky top-0 text-slate-700">
                <tr>
                    <th class="text-left px-4 py-3 text-sm font-semibold">Producto</th>
                    <th class="text-right px-4 py-3 text-sm font-semibold">Cantidad</th>
                </tr>
                </thead>
                <tbody>
                @forelse($productosDetalle as $detalleProducto)
                    <tr class="border-t hover:bg-slate-50">
                        <td class="px-4 py-3 font-semibold">
                            @if($detalleProducto->producto)
                                {{ $detalleProducto->producto->marca }} -
                                {{ $detalleProducto->producto->tipo }}
                                {{ $detalleProducto->producto->contenido }}
                            @else
                                Producto eliminado
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right">
                            <span class="inline-flex min-w-12 justify-center rounded-full bg-cyan-100 px-3 py-1 font-bold text-cyan-800">
                                {{ number_format((int)$detalleProducto->cantidad, 0, ',', '.') }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="2" class="px-4 py-8 text-center text-slate-500">
                            No hay productos vendidos en el período.
                        </td>
                    </tr>
                @endforelse
                </tbody>
                <tfoot class="bg-slate-50 sticky bottom-0">
                <tr class="border-t">
                    <td class="px-4 py-3 font-bold">Total de unidades</td>
                    <td class="px-4 py-3 text-right font-extrabold">
                        {{ number_format((int)$totalProductosVendidos, 0, ',', '.') }}
                    </td>
                </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

<div class="mb-3">
    <div class="text-2xl font-bold">Resumen económico</div>
    <div class="text-sm text-slate-600">Importes cobrados, pendientes y egresos del período.</div>
</div>

<div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
    <div class="rounded-2xl border bg-white p-4">
        <div class="text-sm text-slate-600">Total ingresos cobrados</div>
        <div class="text-3xl font-bold mt-1">
            ${{ number_format((float)$totalIngresos, 2, ',', '.') }}
        </div>
    </div>

    <div class="rounded-2xl border bg-yellow-100 p-4">
        <div class="text-sm text-yellow-900">Pendiente de cobrar</div>
        <div class="text-3xl font-bold mt-1 text-yellow-900">
            ${{ number_format((float)$pendienteTotal, 2, ',', '.') }}
        </div>
        <div class="text-xs text-yellow-900 mt-2">
            Productos: ${{ number_format((float)$pendienteProductos, 2, ',', '.') }} ·
            Servicios: ${{ number_format((float)$pendienteServicios, 2, ',', '.') }}
        </div>
        <div class="text-xs text-yellow-800 mt-1">No anticipa un futuro recargo de tarjeta.</div>
    </div>

    <div class="rounded-2xl border bg-white p-4">
        <div class="text-sm text-slate-600">Total egresos</div>
        <div class="text-3xl font-bold mt-1">
            ${{ number_format((float)$totalEgresos, 2, ',', '.') }}
        </div>
    </div>

    <div class="rounded-2xl border {{ $ganancia >= 0 ? 'bg-green-600' : 'bg-red-600' }} text-white p-4">
        <div class="text-sm {{ $ganancia >= 0 ? 'text-green-100' : 'text-red-100' }}">
            Ganancia / Balance
        </div>
        <div class="text-3xl font-extrabold mt-1">
            ${{ number_format((float)$ganancia, 2, ',', '.') }}
        </div>
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
    <div class="rounded-2xl border bg-green-50 p-4">
        <div class="text-sm text-green-700">Cobrado en efectivo</div>
        <div class="text-2xl font-bold text-green-800 mt-1">
            ${{ number_format((float)$ingresoEfectivo, 2, ',', '.') }}
        </div>
    </div>

    <div class="rounded-2xl border bg-blue-50 p-4">
        <div class="text-sm text-blue-700">Cobrado por transferencia</div>
        <div class="text-2xl font-bold text-blue-800 mt-1">
            ${{ number_format((float)$ingresoTransferencia, 2, ',', '.') }}
        </div>
    </div>

    <div class="rounded-2xl border bg-purple-50 p-4">
        <div class="text-sm text-purple-700">Cobrado con tarjeta</div>
        <div class="text-2xl font-bold text-purple-800 mt-1">
            ${{ number_format((float)$ingresoTarjeta, 2, ',', '.') }}
        </div>
        <div class="text-xs text-purple-700 mt-2">
            Incluye recargos: ${{ number_format((float)$ingresoRecargoTarjeta, 2, ',', '.') }}
        </div>
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <div class="rounded-2xl border bg-white p-4">
        <div class="text-lg font-bold mb-1">Ingresos cobrados</div>
        <div class="text-sm text-slate-500 mb-4">
            Distribuidos proporcionalmente entre productos y servicios de cada pago.
        </div>

        <div class="space-y-3">
            <div class="flex items-center justify-between border rounded-xl p-3">
                <span>Venta de productos</span>
                <strong>${{ number_format((float)$ingresoProductos, 2, ',', '.') }}</strong>
            </div>
            <div class="flex items-center justify-between border rounded-xl p-3">
                <span>Venta de servicios</span>
                <strong>${{ number_format((float)$ingresoServicios, 2, ',', '.') }}</strong>
            </div>
            <div class="flex items-center justify-between border border-purple-200 bg-purple-50 rounded-xl p-3">
                <span class="text-purple-800">Recargo cobrado por tarjeta</span>
                <strong class="text-purple-800">
                    ${{ number_format((float)$ingresoRecargoTarjeta, 2, ',', '.') }}
                </strong>
            </div>
            <div class="flex items-center justify-between rounded-xl bg-slate-900 text-white p-3">
                <span>Total ingresos cobrados</span>
                <strong>${{ number_format((float)$totalIngresos, 2, ',', '.') }}</strong>
            </div>
        </div>
    </div>

    <div class="rounded-2xl border bg-white p-4">
        <div class="text-lg font-bold mb-4">Egresos</div>

        <div class="space-y-3">
            <div class="flex items-center justify-between border rounded-xl p-3">
                <span>Entregas a proveedores</span>
                <strong>${{ number_format((float)$egresoCompras, 2, ',', '.') }}</strong>
            </div>
            <div class="flex items-center justify-between border rounded-xl p-3">
                <span>Liquidaciones / sueldos</span>
                <strong>${{ number_format((float)$egresoLiquidaciones, 2, ',', '.') }}</strong>
            </div>
            <div class="flex items-center justify-between border rounded-xl p-3">
                <span>Consumo peluquería</span>
                <strong>${{ number_format((float)$egresoConsumoPeluqueria, 2, ',', '.') }}</strong>
            </div>
            <div class="flex items-center justify-between border rounded-xl p-3">
                <span>Gastos</span>
                <strong>${{ number_format((float)$egresoGastos, 2, ',', '.') }}</strong>
            </div>
            <div class="flex items-center justify-between rounded-xl bg-slate-900 text-white p-3">
                <span>Total egresos</span>
                <strong>${{ number_format((float)$totalEgresos, 2, ',', '.') }}</strong>
            </div>
        </div>
    </div>
</div>

@endsection
