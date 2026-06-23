@extends('layouts.admin')

@section('title', 'Informes - Vir Tisone Studio')
@section('h1', 'Informes')
@section('sub', 'Balance de ingresos y egresos según la fecha real de cobro.')

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

    <div class="mb-6 rounded-2xl border border-blue-200 bg-blue-50 p-4 text-blue-900">
        <div class="font-bold">Ingresos por fecha real de cobro</div>
        <div class="mt-1 text-sm">
            Los pagos pendientes cobrados otro día y los pagos combinados se registran en la fecha en que fueron abonados.
        </div>
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
                Productos:
                ${{ number_format((float)$pendienteProductos, 2, ',', '.') }}

                <span class="mx-1">|</span>

                Servicios:
                ${{ number_format((float)$pendienteServicios, 2, ',', '.') }}
            </div>

            <div class="text-xs text-yellow-800 mt-1">
                No incluye un futuro recargo de tarjeta.
            </div>
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
                Incluye recargos:
                ${{ number_format((float)$ingresoRecargoTarjeta, 2, ',', '.') }}
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

        <div class="rounded-2xl border bg-white p-4">
            <div class="text-lg font-bold mb-1">Ingresos cobrados</div>

            <div class="text-sm text-slate-500 mb-4">
                Distribuidos según los productos y servicios incluidos en las ventas cobradas.
            </div>

            <div class="space-y-3">
                <div class="flex items-center justify-between border rounded-xl p-3">
                    <span>Venta de productos</span>
                    <strong>
                        ${{ number_format((float)$ingresoProductos, 2, ',', '.') }}
                    </strong>
                </div>

                <div class="flex items-center justify-between border rounded-xl p-3">
                    <span>Venta de servicios</span>
                    <strong>
                        ${{ number_format((float)$ingresoServicios, 2, ',', '.') }}
                    </strong>
                </div>

                <div class="flex items-center justify-between border border-purple-200 bg-purple-50 rounded-xl p-3">
                    <span class="text-purple-800">Recargo cobrado por tarjeta</span>
                    <strong class="text-purple-800">
                        ${{ number_format((float)$ingresoRecargoTarjeta, 2, ',', '.') }}
                    </strong>
                </div>

                <div class="flex items-center justify-between rounded-xl bg-slate-900 text-white p-3">
                    <span>Total ingresos cobrados</span>
                    <strong>
                        ${{ number_format((float)$totalIngresos, 2, ',', '.') }}
                    </strong>
                </div>
            </div>
        </div>

        <div class="rounded-2xl border bg-white p-4">
            <div class="text-lg font-bold mb-4">Egresos</div>

            <div class="space-y-3">
                <div class="flex items-center justify-between border rounded-xl p-3">
                    <span>Entregas a proveedores</span>
                    <strong>
                        ${{ number_format((float)$egresoCompras, 2, ',', '.') }}
                    </strong>
                </div>

                <div class="flex items-center justify-between border rounded-xl p-3">
                    <span>Liquidaciones / sueldos</span>
                    <strong>
                        ${{ number_format((float)$egresoLiquidaciones, 2, ',', '.') }}
                    </strong>
                </div>

                <div class="flex items-center justify-between border rounded-xl p-3">
                    <span>Consumo peluquería</span>
                    <strong>
                        ${{ number_format((float)$egresoConsumoPeluqueria, 2, ',', '.') }}
                    </strong>
                </div>

                <div class="flex items-center justify-between border rounded-xl p-3">
                    <span>Gastos manuales</span>
                    <strong>
                        ${{ number_format((float)$egresoGastos, 2, ',', '.') }}
                    </strong>
                </div>

                <div class="flex items-center justify-between rounded-xl bg-slate-900 text-white p-3">
                    <span>Total egresos</span>
                    <strong>
                        ${{ number_format((float)$totalEgresos, 2, ',', '.') }}
                    </strong>
                </div>
            </div>
        </div>

    </div>

    <div class="mt-6 rounded-2xl border bg-amber-50 border-amber-200 p-4 text-amber-900">
        <div class="font-bold">Aclaración</div>

        <div class="text-sm mt-1">
            Las entregas a proveedores representan plata efectivamente pagada.
            El consumo de peluquería representa el costo interno de productos utilizados.
        </div>
    </div>

@endsection