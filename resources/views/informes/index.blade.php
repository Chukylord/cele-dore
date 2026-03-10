@extends('layouts.admin')

@section('title', 'Informes - Peluquería TOP')
@section('h1', 'Informes')
@section('sub', 'Balance de ingresos y egresos.')

@section('content')

    <form class="grid grid-cols-1 md:grid-cols-4 gap-3 mb-6" method="GET" action="{{ route('informes.index') }}">
        <div>
            <label class="text-sm font-semibold text-slate-700">Desde</label>
            <input type="date" name="desde" value="{{ $desde }}"
                   class="mt-1 w-full rounded-xl border-slate-300 focus:border-slate-500 focus:ring-slate-500">
        </div>

        <div>
            <label class="text-sm font-semibold text-slate-700">Hasta</label>
            <input type="date" name="hasta" value="{{ $hasta }}"
                   class="mt-1 w-full rounded-xl border-slate-300 focus:border-slate-500 focus:ring-slate-500">
        </div>

        <div>
            <label class="text-sm font-semibold text-slate-700">Categoría ingreso</label>
            <select name="categoria_ingreso"
                    class="mt-1 w-full rounded-xl border-slate-300 focus:border-slate-500 focus:ring-slate-500">
                <option value="" {{ $categoriaIngreso === '' ? 'selected' : '' }}>Todos</option>
                <option value="productos" {{ $categoriaIngreso === 'productos' ? 'selected' : '' }}>Venta productos</option>
                <option value="servicios" {{ $categoriaIngreso === 'servicios' ? 'selected' : '' }}>Venta servicios</option>
            </select>
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

    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="rounded-2xl border bg-white p-4">
            <div class="text-sm text-slate-600">Total ingresos</div>
            <div class="text-3xl font-bold mt-1">${{ number_format((float)$totalIngresos, 2, ',', '.') }}</div>
        </div>

        <div class="rounded-2xl border bg-yellow-100 p-4">
            <div class="text-sm text-yellow-900">Pendiente de cobrar</div>
            <div class="text-3xl font-bold mt-1 text-yellow-900">
                ${{ number_format((float)$pendienteTotal, 2, ',', '.') }}
            </div>
            <div class="text-xs text-yellow-900 mt-2">
                Prod: ${{ number_format((float)$pendienteProductos, 2, ',', '.') }} |
                Serv: ${{ number_format((float)$pendienteServicios, 2, ',', '.') }}
            </div>
        </div>

        <div class="rounded-2xl border bg-white p-4">
            <div class="text-sm text-slate-600">Total egresos</div>
            <div class="text-3xl font-bold mt-1">${{ number_format((float)$totalEgresos, 2, ',', '.') }}</div>
        </div>
        
        <div class="rounded-2xl border {{ $ganancia >= 0 ? 'bg-green-600' : 'bg-red-600' }} text-white p-4">
            <div class="text-sm {{ $ganancia >= 0 ? 'text-green-100' : 'text-red-100' }}">Ganancia / Balance</div>
            <div class="text-3xl font-extrabold mt-1">${{ number_format((float)$ganancia, 2, ',', '.') }}</div>
        </div>
    </div>
    
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        
        <div class="rounded-2xl border bg-white p-4">
            <div class="text-lg font-bold mb-4">Ingresos</div>
            
            <div class="space-y-3">
                <div class="flex items-center justify-between border rounded-xl p-3">
                    <span>Venta productos</span>
                    <strong>${{ number_format((float)$ingresoProductos, 2, ',', '.') }}</strong>
                </div>
                
                <div class="flex items-center justify-between border rounded-xl p-3">
                    <span>Venta servicios</span>
                    <strong>${{ number_format((float)$ingresoServicios, 2, ',', '.') }}</strong>
                </div>
                
                <div class="flex items-center justify-between rounded-xl bg-slate-900 text-white p-3">
                    <span>Total ingresos</span>
                    <strong>${{ number_format((float)$totalIngresos, 2, ',', '.') }}</strong>
                </div>
            </div>
        </div>
        
        <div class="rounded-2xl border bg-white p-4">
            <div class="text-lg font-bold mb-4">Egresos</div>

            <div class="space-y-3">
                <div class="flex items-center justify-between border rounded-xl p-3">
                    <span>Compras</span>
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
                    <span>Gastos manuales</span>
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